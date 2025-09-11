<?php

namespace App\Livewire\Catalogos\Produccion;

use App\Models\Caracteristica;
use App\Models\FiltroProduccion;
use App\Models\HojaFiltroProduccion;
use App\Models\Producto;
use App\Models\EstadoPedido;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;


use Illuminate\Support\Facades\Log;


class HojaFiltrosCrud extends Component
{
    use WithPagination;

    #[Url(history:true)]
    public string $search = '';
    public int $perPage = 10;

    public bool $modalOpen = false;
    public ?int $editId = null;

    public array $form = [
        'nombre' => '',
        'slug'   => '',
        'descripcion' => '',
        'role_id' => null,
        'estados_permitidos' => [],
        'base_columnas' => [],
        'visible' => true,
        'orden' => null,
    ];

    /** @var array<int> */
    public array $filtro_ids = []; // filtros incluidos en la hoja (ordenados)
    public array $roles = [];      // para selector (id=>name)
    public array $estados = [];    // para multiselect (distinct pedidos.estado)

    /* ===============================
     * MODAL INLINE: CREAR NUEVO FILTRO
     * =============================== */
    public bool $modalFiltroOpen = false;
    public string $modalFiltroTab = 'datos'; // datos|productos|columnas

    public array $filtroForm = [
        'nombre'      => '',
        'slug'        => '',
        'descripcion' => '',
        'visible'     => true,
        'orden'       => null,
    ];

    /** @var array<int> */
    public array $filtro_producto_ids = [];

    /**
     * Columnas (características) del filtro a crear.
     * Cada item: [
     *   caracteristica_id, nombre, orden, label, visible, ancho,
     *   render, multivalor_modo, max_items, fallback
     * ]
     */
    public array $filtro_columnas = [];

    public string $productoSearchFiltro = '';
    public string $caracteristicaSearchFiltro = '';

    public bool $confirmDeleteOpen = false;
    public ?int $deleteId = null;

    protected function rules(): array
    {
        return [
            'form.nombre' => ['required','string','max:150'],
            'form.slug'   => ['nullable','string','max:191',
                Rule::unique('hojas_filtros_produccion','slug')->ignore($this->editId)
            ],
            'form.descripcion' => ['nullable','string','max:2000'],
            'form.role_id' => ['nullable','integer','exists:roles,id'],
            'form.estados_permitidos'   => ['array'],
            'form.estados_permitidos.*' => ['integer','exists:estados_pedido,id'],
            'form.base_columnas' => ['array'],
            'form.visible' => ['boolean'],
            'form.orden' => ['nullable','integer','min:0'],
            'filtro_ids' => ['array'],
            'filtro_ids.*' => ['integer','exists:filtros_produccion,id'],
        ];
    }

    protected function rulesFiltro(): array
    {
        return [
            'filtroForm.nombre'            => ['required','string','max:150'],
            'filtroForm.slug'              => ['nullable','string','max:191'],
            'filtroForm.descripcion'       => ['nullable','string','max:1000'],
            'filtroForm.visible'           => ['boolean'],
            'filtroForm.orden'             => ['nullable','integer','min:0'],
            'filtro_producto_ids'          => ['array'],
            'filtro_producto_ids.*'        => ['integer','exists:productos,id'],
            'filtro_columnas'              => ['array'],
            'filtro_columnas.*.caracteristica_id' => ['required','integer','exists:caracteristicas,id'],
            'filtro_columnas.*.orden'      => ['nullable','integer','min:0'],
            'filtro_columnas.*.label'      => ['nullable','string','max:100'],
            'filtro_columnas.*.visible'    => ['boolean'],
            'filtro_columnas.*.ancho'      => ['nullable','string','max:50'],
            'filtro_columnas.*.render'     => ['required','in:texto,badges,chips,iconos,count'],
            'filtro_columnas.*.multivalor_modo' => ['required','in:inline,badges,count'],
            'filtro_columnas.*.max_items'  => ['required','integer','min:1','max:99'],
            'filtro_columnas.*.fallback'   => ['nullable','string','max:50'],
        ];
    }

    public function mount(): void
    {
        $this->form['base_columnas'] = \App\Models\HojaFiltroProduccion::defaultBaseColumnas();
        $this->ensureEstadoBaseCol();
        $this->ensureFechasBaseCols();
        // roles...
        $this->roles = \Spatie\Permission\Models\Role::query()->orderBy('name')->pluck('name','id')->toArray();

        
        $this->hydrateEstadosPermitidos();
        $this->ensureClienteBaseCol();
    }

    public function updatedFormNombre($v): void
    {
       // if (blank($this->form['slug'])) $this->form['slug'] = Str::slug($v);
    }

    /* ===========================
     * CRUD HOJA
     * =========================== */

    // public function openCreate(): void
    // {
    //     $this->resetErrorBag(); $this->resetValidation();
    //     $this->editId = null;
    //     $this->form = [
    //         'nombre'=>'','slug'=>'','descripcion'=>'','role_id'=>null,
    //         'estados_permitidos'=>[], 'base_columnas'=>HojaFiltroProduccion::defaultBaseColumnas(),
    //         'visible'=>true, 'orden'=>null,
    //     ];
    //     $this->filtro_ids = [];
    //     $this->modalOpen = true;
    //     $this->dispatch('hojas-notify', message: 'Creando hoja…');
    // }

    public function openCreate(): void
    {
        $this->resetErrorBag(); $this->resetValidation();
        $this->editId = null;
        $this->form = [
            'nombre'=>'','slug'=>'','descripcion'=>'','role_id'=>null,
            'estados_permitidos'=>[],
            'base_columnas'=>\App\Models\HojaFiltroProduccion::defaultBaseColumnas(),
            'visible'=>true, 'orden'=>null,
        ];
        $this->ensureEstadoBaseCol();
        $this->ensureFechasBaseCols(); 
        $this->ensureClienteBaseCol();
        $this->filtro_ids = [];
        $this->modalOpen = true;
        $this->dispatch('hojas-notify', message: 'Creando hoja…');
    }

    // public function openEdit(int $id): void
    // {
    //     $this->resetErrorBag(); $this->resetValidation();
    //     $hoja = HojaFiltroProduccion::with('filtros:id')->findOrFail($id);
    //     $this->editId = $hoja->id;
    //     $this->form = [
    //         'nombre'=>$hoja->nombre, 'slug'=>$hoja->slug, 'descripcion'=>$hoja->descripcion,
    //         'role_id'=>$hoja->role_id, 'estados_permitidos'=>$hoja->estados_permitidos ?? [],
    //         'base_columnas'=>$hoja->base_columnas ?: HojaFiltroProduccion::defaultBaseColumnas(),
    //         'visible'=>(bool)$hoja->visible, 'orden'=>$hoja->orden,
    //     ];
    //     $this->filtro_ids = $hoja->filtros()->pluck('filtros_produccion.id')->all();
    //     $this->modalOpen = true;
    //     $this->dispatch('hojas-notify', message: 'Editando hoja.');
    // }

    public function openEdit(int $id): void
    {
        $this->resetErrorBag(); $this->resetValidation();
        $hoja = HojaFiltroProduccion::with('filtros:id')->findOrFail($id);
        $this->editId = $hoja->id;

        $this->form = [
            'nombre'=>$hoja->nombre, 'slug'=>$hoja->slug, 'descripcion'=>$hoja->descripcion,
            'role_id'=>$hoja->role_id, 'estados_permitidos'=>$hoja->estados_permitidos ?? [],
            'base_columnas'=>$hoja->base_columnas ?: HojaFiltroProduccion::defaultBaseColumnas(),
            'visible'=>(bool)$hoja->visible, 'orden'=>$hoja->orden,
        ];

        $this->ensureEstadoBaseCol();
        $this->ensureFechasBaseCols(); // ← NUEVO
        $this->ensureClienteBaseCol();

        $this->filtro_ids = $hoja->filtros()->pluck('filtros_produccion.id')->all();
        $this->modalOpen = true;
        $this->dispatch('hojas-notify', message: 'Editando hoja.');
    }

    public function save(): void
    {
        
        $this->form['estados_permitidos'] = array_values(array_unique(
            array_map('intval', $this->form['estados_permitidos'] ?? [])
        ));
        
        Log::debug('316 inicio de save');

   

        $this->validate();

        Log::debug('316 des pues de validate');
        

        $base = filled($this->form['slug']) ? Str::slug($this->form['slug']) : Str::slug($this->form['nombre']);
        $slug = $base; $i=1;
        while (HojaFiltroProduccion::where('slug',$slug)->when($this->editId,fn($q)=>$q->where('id','!=',$this->editId))->exists()) {
            $slug = $base.'-'.(++$i);
        }

        \DB::transaction(function () use ($slug) {
            $hoja = $this->editId
                ? HojaFiltroProduccion::lockForUpdate()->findOrFail($this->editId)
                : new HojaFiltroProduccion();

            $hoja->fill([
                'nombre'=>$this->form['nombre'],
                'slug'=>$slug,
                'descripcion'=>$this->form['descripcion'] ?: null,
                'role_id'=>$this->form['role_id'],
                'estados_permitidos'=>$this->form['estados_permitidos'] ?: [],
                'base_columnas'=>$this->form['base_columnas'] ?: HojaFiltroProduccion::defaultBaseColumnas(),
                'visible'=>(bool)$this->form['visible'],
                'orden'=>$this->form['orden'],
            ])->save();

            // sync filtros con orden
            $sync = [];
            foreach (array_values($this->filtro_ids) as $idx => $fid) {
                $sync[$fid] = ['orden' => $idx+1];
            }
            $hoja->filtros()->sync($sync);

            $this->editId = $hoja->id;
        });

        $this->dispatch('hoja-actualizada');
        $this->modalOpen = false;
        $this->resetPage();
    }

    /* Reordenar pestañas asignadas */
    public function moveUpAssign(int $index): void
    {
        if (!isset($this->filtro_ids[$index]) || $index === 0) return;
        [$this->filtro_ids[$index-1], $this->filtro_ids[$index]] = [$this->filtro_ids[$index], $this->filtro_ids[$index-1]];
    }
    public function moveDownAssign(int $index): void
    {
        if (!isset($this->filtro_ids[$index]) || $index === count($this->filtro_ids)-1) return;
        [$this->filtro_ids[$index+1], $this->filtro_ids[$index]] = [$this->filtro_ids[$index], $this->filtro_ids[$index+1]];
    }

    /* ===========================
     * INLINE NUEVO FILTRO
     * =========================== */

    /** Botón "Crear filtro…" */
    public function openModalFiltro(): void
    {
        $this->resetErrorBag(); $this->resetValidation();
        $this->filtroForm = [
            'nombre'      => '',
            'slug'        => '',
            'descripcion' => '',
            'visible'     => true,
            'orden'       => null,
        ];
        $this->filtro_producto_ids = [];
        $this->filtro_columnas = [];
        $this->modalFiltroTab = 'datos';
        $this->modalFiltroOpen = true;
        $this->dispatch('hojas-notify', message: 'Creando nuevo filtro…');
    }

    public function updatedFiltroFormNombre(string $v): void
    {
        if (blank($this->filtroForm['slug'])) {
        //    $this->filtroForm['slug'] = Str::slug($v);
        }
    }

    public function filtroAddCaracteristica(int $caracteristicaId): void
    {
        $car = Caracteristica::find($caracteristicaId);
        if (!$car) return;

        foreach ($this->filtro_columnas as $c) {
            if ((int)$c['caracteristica_id'] === (int)$caracteristicaId) {
                $this->dispatch('hojas-notify', message: 'La característica ya está añadida.');
                return;
            }
        }

        $this->filtro_columnas[] = [
            'caracteristica_id' => $car->id,
            'nombre'            => $car->nombre,
            'orden'             => count($this->filtro_columnas) + 1,
            'label'             => $car->nombre,
            'visible'           => true,
            'ancho'             => null,
            'render'            => 'texto',
            'multivalor_modo'   => 'inline',
            'max_items'         => 4,
            'fallback'          => '—',
        ];
        $this->dispatch('hojas-notify', message: 'Columna añadida.');
    }

    public function filtroRemoveColumna(int $index): void
    {
        if (!isset($this->filtro_columnas[$index])) return;
        unset($this->filtro_columnas[$index]);
        $this->filtro_columnas = array_values($this->filtro_columnas);
        $this->dispatch('hojas-notify', message: 'Columna removida.');
    }

    public function filtroReorderColumna(int $from, int $to): void
    {
        if (!isset($this->filtro_columnas[$from]) || $to < 0 || $to >= count($this->filtro_columnas)) return;
        $item = $this->filtro_columnas[$from];
        array_splice($this->filtro_columnas, $from, 1);
        array_splice($this->filtro_columnas, $to, 0, [$item]);
        foreach ($this->filtro_columnas as $i => &$col) { $col['orden'] = $i + 1; } unset($col);
        $this->dispatch('hojas-notify', message: 'Columnas reordenadas.');
    }

    public function saveFiltro(): void
    {

        $this->validate($this->rulesFiltro());

        // resolver slug único
        $base = filled($this->filtroForm['slug']) ? Str::slug($this->filtroForm['slug']) : Str::slug($this->filtroForm['nombre']);
        $slug = $base; $i=1;
        while (FiltroProduccion::where('slug',$slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        \DB::transaction(function () use ($slug) {
            $filtro = new FiltroProduccion();
            $filtro->fill([
                'nombre'      => $this->filtroForm['nombre'],
                'slug'        => $slug,
                'descripcion' => $this->filtroForm['descripcion'] ?: null,
                'visible'     => (bool)$this->filtroForm['visible'],
                'orden'       => $this->filtroForm['orden'],
            ])->save();

            // Productos
            $filtro->productos()->sync($this->filtro_producto_ids);

            // Columnas (características)
            $sync = [];
            foreach ($this->filtro_columnas as $col) {
                $sync[$col['caracteristica_id']] = [
                    'orden'           => $col['orden'],
                    'label'           => $col['label'],
                    'visible'         => (bool)$col['visible'],
                    'ancho'           => $col['ancho'],
                    'render'          => $col['render'],
                    'multivalor_modo' => $col['multivalor_modo'],
                    'max_items'       => (int)$col['max_items'],
                    'fallback'        => $col['fallback'],
                ];
            }
            $filtro->caracteristicas()->sync($sync);

            // Asignar automáticamente a la Hoja abierta
            $this->dispatch('filtro-creado', filtroId: $filtro->id)->self(); // listener -> assignFiltro
        });

        $this->dispatch('hojas-notify', message: 'Filtro creado correctamente.');
        $this->modalFiltroOpen = false;
    }

    /* Eventos que llegan desde la vista (botón x-on) */
    protected $listeners = [
        'filtro-creado'       => 'assignFiltro',
        'abrir-modal-filtro'  => 'openModalFiltro',
    ];

    public function assignFiltro(int $filtroId): void
    {
        if (!in_array($filtroId, $this->filtro_ids)) {
            $this->filtro_ids[] = $filtroId;
            $this->dispatch('hojas-notify', message: 'Filtro asignado.');
        }
    }


    private function hydrateEstadosPermitidos(): void
    {
        // Simplemente traemos todos los estados válidos desde el catálogo
        $this->estados = \App\Models\EstadoPedido::query()
            ->where('ind_activo', true) // opcional: solo activos
            ->orderByRaw('COALESCE(orden, 999999), nombre')
            ->get(['id', 'nombre'])
            ->map(fn($e) => [
                'id'     => (int) $e->id,
                'nombre' => $e->nombre,
            ])
            ->all();
    }


    private function ensureFechasBaseCols(): void
    {
        $keys = ['fecha_produccion' => 'F. Producción', 'fecha_embarque' => 'F. Embarque', 'fecha_entrega' => 'F. Entrega'];

        $cols = $this->form['base_columnas'] ?? [];
        $maxOrden = (int) (collect($cols)->max('orden') ?? 0);

        foreach ($keys as $key => $label) {
            $existe = collect($cols)->contains(fn($c) => ($c['key'] ?? null) === $key);
            if (!$existe) {
                $cols[] = [
                    'key'     => $key,
                    'label'   => $label,
                    'visible' => true,   // visibles por defecto
                    'fixed'   => false,  // no fijas por defecto (el usuario puede fijarlas)
                    'orden'   => ++$maxOrden,
                ];
            }
        }

        // normaliza índices
        $this->form['base_columnas'] = array_values($cols);
    }

    private function ensureEstadoBaseCol(): void
    {
        $cols = $this->form['base_columnas'] ?? [];
        $hasEstado = collect($cols)->contains(fn($c) => ($c['key'] ?? null) === 'estado');

        if (!$hasEstado) {
            $orden = (int) (collect($cols)->max('orden') ?? 4) + 1;
            $cols[] = [
                'key'     => 'estado',   
                'label'   => 'Estado',
                'visible' => true,
                'fixed'   => false,
                'orden'   => $orden,
            ];
            $this->form['base_columnas'] = array_values($cols);
        }
    }


    public function render()
    {
        $q = HojaFiltroProduccion::query()
            ->withCount('filtros')
            ->when($this->search, fn($qq)=>$qq->where('nombre','like',"%{$this->search}%"))
            ->orderByRaw('COALESCE(orden, 999999), id desc');

        $hojas = $q->paginate($this->perPage);

        $filtros = FiltroProduccion::orderByRaw('COALESCE(orden,999999), id desc')
            ->get(['id','nombre']);

        // Listas para el modal de filtro inline
        $productos = Producto::query()
            ->when($this->productoSearchFiltro, fn($qq)=>$qq->where('nombre','like',"%{$this->productoSearchFiltro}%"))
            ->orderBy('nombre')->limit(200)->get(['id','nombre']);

        $caracteristicas = Caracteristica::query()
            ->when($this->caracteristicaSearchFiltro, fn($qq)=>$qq->where('nombre','like',"%{$this->caracteristicaSearchFiltro}%"))
            ->orderBy('nombre')->limit(200)->get(['id','nombre']);

        return view('livewire.catalogos.produccion.hoja-filtros-crud', compact(
            'hojas','filtros','productos','caracteristicas'
        ));
    }

    // Abrir modal de confirmación
    public function openDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmDeleteOpen = true;
    }

    // Eliminar definitivamente
    public function deleteHoja(): void
    {
        if (!$this->deleteId) return;

        \DB::transaction(function () {
            $hoja = HojaFiltroProduccion::lockForUpdate()->find($this->deleteId);
            if (!$hoja) return;

            // Rompe relación con filtros (si aplica)
            $hoja->filtros()->detach();

            $hoja->delete();
        });

        $this->confirmDeleteOpen = false;
        $this->deleteId = null;

        $this->dispatch('hojas-notify', message: 'Hoja eliminada.');
        $this->resetPage(); // refresca la lista en la página 1
    }

    private function ensureClienteBaseCol(): void
    {
        $cols = $this->form['base_columnas'] ?? [];

        $ix = collect($cols)->search(fn($c) => ($c['key'] ?? null) === 'cliente');
        if ($ix === false) {
            $orden = (int) (collect($cols)->max('orden') ?? 0) + 1;
            // Configurable: visible=true por defecto, fixed=false (usuario decide)
            $cols[] = [
                'key'     => 'cliente',
                'label'   => 'Cliente',
                'visible' => true,
                'fixed'   => false,
                'orden'   => $orden,
            ];
            $this->form['base_columnas'] = array_values($cols);
            return;
        }

        // Normaliza si ya existía (sin forzarla como fija)
        $cols[$ix]['label']   = $cols[$ix]['label']   ?? 'Cliente';
        $cols[$ix]['visible'] = $cols[$ix]['visible'] ?? true;
        $cols[$ix]['fixed']   = (bool)($cols[$ix]['fixed'] ?? false);

        $this->form['base_columnas'] = array_values($cols);
    }
}


// namespace App\Livewire\Catalogos\Produccion;

// use Livewire\Component;

// class HojaFiltrosCrud extends Component
// {
//     public function render()
//     {
//         return view('livewire.catalogos.produccion.hoja-filtros-crud');
//     }
// }

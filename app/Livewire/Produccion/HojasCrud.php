<?php

namespace App\Livewire\Produccion;

use App\Models\FiltroProduccion;
use App\Models\HojaFiltroProduccion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Features\SupportPageComponents\PageView;
class HojasCrud extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    public int $perPage = 10;

    /** Modal + estado de edición */
    public bool $modalOpen = false;
    public ?int $editId = null;

    /**
     * Formulario de Hoja
     * - base_columnas y estados_permitidos se guardan como JSON (casts en el modelo).
     * - role_id es opcional (null => pública para autenticados).
     */
    public array $form = [
        'nombre'             => '',
        'slug'               => '',
        'descripcion'        => '',
        'role_id'            => null,
        'estados_permitidos' => [],   // array<string>
        'base_columnas'      => [],   // array<key,label,visible,fixed,orden>
        'visible'            => true,
        'orden'              => null,
    ];

    /** IDs de filtros asignados a la Hoja (en orden) */
    public array $filtro_ids = [];

    /** Listeners para coordinar con el CRUD de Filtros (crear en línea) */
    protected $listeners = [
        'filtro-creado' => 'onFiltroCreado', // recibe id del filtro recién creado
    ];


    public array $estadosCatalog = [
    'POR APROBAR',
    'APROBADO',
    'ENTREGADO',
    'RECHAZADO',
    'ARCHIVADO',
    'POR REPROGRAMAR',
];

    /** Reglas de validación */
    protected function rules(): array
    {
        return [
            'form.nombre'             => ['required', 'string', 'max:150'],
            'form.slug'               => [
                'nullable', 'string', 'max:191',
                Rule::unique('hojas_filtros_produccion', 'slug')->ignore($this->editId)
            ],
            'form.descripcion'        => ['nullable', 'string', 'max:1000'],
            'form.role_id'            => ['nullable', 'integer'],
            'form.estados_permitidos' => ['array'],
            'form.estados_permitidos.*' => ['string', 'max:255'],
            'form.base_columnas'      => ['array'],
            'form.base_columnas.*.key'     => ['required', 'string', 'max:50'],
            'form.base_columnas.*.label'   => ['required', 'string', 'max:50'],
            'form.base_columnas.*.visible' => ['boolean'],
            'form.base_columnas.*.fixed'   => ['boolean'],
            'form.base_columnas.*.orden'   => ['integer', 'min:1'],
            'form.visible'            => ['boolean'],
            'form.orden'              => ['nullable', 'integer', 'min:0'],
            'filtro_ids'              => ['array'],
            'filtro_ids.*'            => ['integer', 'exists:filtros_produccion,id'],
        ];
    }

    /** Autogenera slug si el usuario deja el campo vacío */
    public function updatedFormNombre(string $value): void
    {
        if (blank($this->form['slug'])) {
            $this->form['slug'] = Str::slug($value);
        }
    }

    /** Reset de página al cambiar búsqueda */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /** Abrir modal para crear */
    public function openCreate(): void
    {
        Gate::authorize('manage', HojaFiltroProduccion::class);

        $this->resetErrorBag();
        $this->resetValidation();

        $this->editId = null;
        $this->form = [
            'nombre'             => '',
            'slug'               => '',
            'descripcion'        => '',
            'role_id'            => null,
            'estados_permitidos' => [],
            'base_columnas'      => HojaFiltroProduccion::defaultBaseColumnas(),
            'visible'            => true,
            'orden'              => null,
        ];
        $this->filtro_ids = [];

        $this->modalOpen = true;
        $this->dispatch('hojas-notify', message: 'Creando nueva Hoja…');
    }

    /** Abrir modal para editar */
    public function openEdit(int $id): void
    {
        Gate::authorize('manage', HojaFiltroProduccion::class);

        $this->resetErrorBag();
        $this->resetValidation();

        /** @var HojaFiltroProduccion $hoja */
        $hoja = HojaFiltroProduccion::with('filtros:id')->findOrFail($id);

        $this->editId = $hoja->id;
        $this->form = [
            'nombre'             => $hoja->nombre,
            'slug'               => $hoja->slug,
            'descripcion'        => $hoja->descripcion,
            'role_id'            => $hoja->role_id,
            'estados_permitidos' => $hoja->estados_permitidos ?: [],
            'base_columnas'      => $hoja->base_columnas ?: HojaFiltroProduccion::defaultBaseColumnas(),
            'visible'            => (bool) $hoja->visible,
            'orden'              => $hoja->orden,
        ];
        $this->filtro_ids = $hoja->filtros()->pluck('filtros_produccion.id')->all();

        $this->modalOpen = true;
        $this->dispatch('hojas-notify', message: 'Editando Hoja.');
    }

    /** Guardar (crear/actualizar) */
    public function save(): void
    {
        Gate::authorize('manage', HojaFiltroProduccion::class);

        $this->validate();

        // Normaliza slug único
        $base = filled($this->form['slug'])
            ? Str::slug($this->form['slug'])
            : Str::slug($this->form['nombre']);

        $slug = $base;
        $i = 1;
        while (
            HojaFiltroProduccion::where('slug', $slug)
                ->when($this->editId, fn($q) => $q->where('id', '!=', $this->editId))
                ->exists()
        ) {
            $slug = $base.'-'.(++$i);
        }

        // Normaliza columnas base
        $cols = collect($this->form['base_columnas'])
            ->map(function ($c) {
                return [
                    'key'     => (string) ($c['key'] ?? ''),
                    'label'   => (string) ($c['label'] ?? Str::title($c['key'] ?? '')),
                    'visible' => (bool) ($c['visible'] ?? true),
                    'fixed'   => (bool) ($c['fixed'] ?? false),
                    'orden'   => (int)  ($c['orden'] ?? 0),
                ];
            })
            ->filter(fn($c) => $c['key'] !== '')
            ->sortBy('orden')->values()
            ->map(function ($c, $index) {
                // Fuerza ID siempre visible; el checkbox de selección no necesita JSON
                if ($c['key'] === 'id') {
                    $c['fixed'] = true;
                    $c['visible'] = true;
                }
                $c['orden'] = $index + 1;
                return $c;
            })
            ->all();

        DB::transaction(function () use ($slug, $cols) {
            if ($this->editId) {
                $hoja = HojaFiltroProduccion::lockForUpdate()->findOrFail($this->editId);
            } else {
                $hoja = new HojaFiltroProduccion();
            }

            $hoja->fill([
                'nombre'             => $this->form['nombre'],
                'slug'               => $slug,
                'descripcion'        => $this->form['descripcion'] ?: null,
                'role_id'            => $this->form['role_id'] ?: null,
                'estados_permitidos' => $this->form['estados_permitidos'] ?: [],
                'base_columnas'      => $cols,
                'visible'            => (bool) $this->form['visible'],
                'orden'              => $this->form['orden'],
            ])->save();

            // Sincroniza filtros asignados con orden
            $sync = [];
            foreach (array_values($this->filtro_ids) as $idx => $fid) {
                $sync[$fid] = ['orden' => $idx + 1];
            }
            $hoja->filtros()->sync($sync);

            $this->editId = $hoja->id;
        });

        $this->modalOpen = false;
        $this->resetPage();
        $this->dispatch('hojas-notify', message: 'Hoja guardada correctamente.');
        $this->dispatch('hoja-actualizada');
    }

    /** Reordenar filtros (arriba/abajo) en el modal */
    public function moveUpAssign(int $index): void
    {
        if ($index <= 0 || $index >= count($this->filtro_ids)) return;
        [$this->filtro_ids[$index-1], $this->filtro_ids[$index]] = [$this->filtro_ids[$index], $this->filtro_ids[$index-1]];
        $this->filtro_ids = array_values($this->filtro_ids);
    }

    public function moveDownAssign(int $index): void
    {
        if ($index < 0 || $index >= count($this->filtro_ids) - 1) return;
        [$this->filtro_ids[$index+1], $this->filtro_ids[$index]] = [$this->filtro_ids[$index], $this->filtro_ids[$index+1]];
        $this->filtro_ids = array_values($this->filtro_ids);
    }

    /** Al crear un filtro desde el modal de Filtros, lo añadimos a la Hoja en edición */
    public function onFiltroCreado(int $id): void
    {
        // Evita duplicados
        if (!in_array($id, $this->filtro_ids, true)) {
            $this->filtro_ids[] = $id;
        }
        $this->dispatch('hojas-notify', message: 'Filtro agregado a la Hoja.');
    }

    /** Render */
    public function render()
    {
        // Listado de hojas con conteos
        $q = HojaFiltroProduccion::query()
            ->with('rol:id,name')
            ->withCount('filtros')
            ->when($this->search, function ($qq) {
                $term = "%{$this->search}%";
                $qq->where(function ($s) use ($term) {
                    $s->where('nombre', 'like', $term)
                      ->orWhere('slug', 'like', $term)
                      ->orWhere('descripcion', 'like', $term);
                });
            })
            ->orderByRaw('COALESCE(orden, 999999), id DESC');

        $hojas = $q->paginate($this->perPage);

        // Roles (Spatie)
        $roles = \Spatie\Permission\Models\Role::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        // Estatus disponibles (de la tabla pedido)
        $estados = DB::table('pedido')
            ->distinct()
            ->whereNotNull('estado')
            ->orderBy('estado')
            ->pluck('estado')
            ->toArray();

        // Filtros disponibles para asignar
        $filtros = FiltroProduccion::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return view('livewire.produccion.hojas-crud', [
            'hojas'   => $hojas,
            'roles'   => $roles,
            'estados' => $this->$estados,
            'filtros' => $filtros,
        ]);


    }
}


// class HojasCrud extends Component
// {
//     public function render()
//     {
//         return view('livewire.produccion.hojas-crud');
//     }
// }

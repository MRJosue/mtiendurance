<?php
// app/Livewire/Produccion/HojasCrud.php
namespace App\Livewire\Catalogos\Produccion;

use App\Models\FiltroProduccion;
use App\Models\HojaFiltroProduccion;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

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

    protected function rules(): array
    {
        return [
            'form.nombre' => ['required','string','max:150'],
            'form.slug'   => ['nullable','string','max:191',
                Rule::unique('hojas_filtros_produccion','slug')->ignore($this->editId)
            ],
            'form.descripcion' => ['nullable','string','max:2000'],
            'form.role_id' => ['nullable','integer','exists:roles,id'],
            'form.estados_permitidos' => ['array'],
            'form.base_columnas' => ['array'],
            'form.visible' => ['boolean'],
            'form.orden' => ['nullable','integer','min:0'],
            'filtro_ids' => ['array'],
            'filtro_ids.*' => ['integer','exists:filtros_produccion,id'],
        ];
    }

    public function mount(): void
    {
        $this->form['base_columnas'] = \App\Models\HojaFiltroProduccion::defaultBaseColumnas();
        // carga roles
        $this->roles = \Spatie\Permission\Models\Role::query()->orderBy('name')->pluck('name','id')->toArray();
        // estados distinct
        $this->estados = \DB::table('pedido')->distinct()->whereNotNull('estado')->orderBy('estado')->pluck('estado')->toArray();
    }

    public function updatedFormNombre($v): void
    {
        if (blank($this->form['slug'])) $this->form['slug'] = Str::slug($v);
    }

    public function openCreate(): void
    {
        $this->resetErrorBag(); $this->resetValidation();
        $this->editId = null;
        $this->form = [
            'nombre'=>'','slug'=>'','descripcion'=>'','role_id'=>null,
            'estados_permitidos'=>[], 'base_columnas'=>\App\Models\HojaFiltroProduccion::defaultBaseColumnas(),
            'visible'=>true, 'orden'=>null,
        ];
        $this->filtro_ids = [];
        $this->modalOpen = true;
        $this->dispatch('hojas-notify', message: 'Creando hoja…');
    }

    public function openEdit(int $id): void
    {
        $this->resetErrorBag(); $this->resetValidation();
        $hoja = HojaFiltroProduccion::with('filtros:id')->findOrFail($id);
        $this->editId = $hoja->id;
        $this->form = [
            'nombre'=>$hoja->nombre, 'slug'=>$hoja->slug, 'descripcion'=>$hoja->descripcion,
            'role_id'=>$hoja->role_id, 'estados_permitidos'=>$hoja->estados_permitidos ?? [],
            'base_columnas'=>$hoja->base_columnas ?: \App\Models\HojaFiltroProduccion::defaultBaseColumnas(),
            'visible'=>(bool)$hoja->visible, 'orden'=>$hoja->orden,
        ];
        $this->filtro_ids = $hoja->filtros()->pluck('filtros_produccion.id')->all();
        $this->modalOpen = true;
        $this->dispatch('hojas-notify', message: 'Editando hoja.');
    }

    public function save(): void
    {
        $this->validate();

        // slug único
        $base = filled($this->form['slug']) ? Str::slug($this->form['slug']) : Str::slug($this->form['nombre']);
        $slug = $base; $i=1;
        while (HojaFiltroProduccion::where('slug',$slug)->when($this->editId,fn($q)=>$q->where('id','!=',$this->editId))->exists()) {
            $slug = $base.'-'.(++$i);
        }

        \DB::transaction(function () use ($slug) {
            if ($this->editId) {
                $hoja = HojaFiltroProduccion::lockForUpdate()->findOrFail($this->editId);
            } else {
                $hoja = new HojaFiltroProduccion();
            }

            $hoja->fill([
                'nombre'=>$this->form['nombre'],
                'slug'=>$slug,
                'descripcion'=>$this->form['descripcion'] ?: null,
                'role_id'=>$this->form['role_id'],
                'estados_permitidos'=>$this->form['estados_permitidos'] ?: [],
                'base_columnas'=>$this->form['base_columnas'] ?: \App\Models\HojaFiltroProduccion::defaultBaseColumnas(),
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

    public function assignFiltro(int $filtroId): void
    {
        if (!in_array($filtroId, $this->filtro_ids)) {
            $this->filtro_ids[] = $filtroId;
            $this->dispatch('hojas-notify', message: 'Filtro asignado.');
        }
    }

    // escuchar cuando el CRUD de filtros crea uno nuevo
    protected $listeners = ['filtro-creado' => 'assignFiltro'];

    public function render()
    {
        $q = HojaFiltroProduccion::query()
            ->withCount('filtros')
            ->when($this->search, fn($qq)=>$qq->where('nombre','like',"%{$this->search}%"))
            ->orderByRaw('COALESCE(orden, 999999), id desc');

        $hojas = $q->paginate($this->perPage);

        $filtros = FiltroProduccion::orderByRaw('COALESCE(orden,999999), id desc')
            ->get(['id','nombre']);

        return view('livewire.catalogos.produccion.hoja-filtros-crud', compact('hojas','filtros'));
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

<?php

namespace App\Livewire\Catalogos\Produccion;

use App\Models\Caracteristica;
use App\Models\FiltroProduccion;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

class FiltrosProduccionCrud extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    public int $perPage = 10;

    /** @var array<int> */
    public array $selected = [];

    // Modal & modo edición
    public bool $modalOpen = false;
    public ?int $editId = null;

    // Tabs internas del modal
    public string $modalTab = 'datos'; // datos|productos|columnas

    // Formulario base del filtro
    public array $form = [
        'nombre'      => '',
        'slug'        => '',
        'descripcion' => '',
        'visible'     => true,
        'orden'       => null,
    ];

    /** @var array<int> Lista de IDs de productos seleccionados para el filtro */
    public array $producto_ids = [];

    /**
     * Columnas (características) configuradas para el filtro.
     * Cada item: [
     *   caracteristica_id, nombre, orden, label, visible, ancho, render, multivalor_modo, max_items, fallback
     * ]
     */
    public array $columnas = [];

    // Auxiliares de búsqueda en selectores (opcional)
    public string $productoSearch = '';
    public string $caracteristicaSearch = '';

    protected function rules(): array
    {
        return [
            'form.nombre'            => ['required', 'string', 'max:150'],
            'form.slug'              => ['nullable', 'string', 'max:191'],
            'form.descripcion'       => ['nullable', 'string', 'max:1000'],
            'form.visible'           => ['boolean'],
            'form.orden'             => ['nullable', 'integer', 'min:0'],
            'producto_ids'           => ['array'],
            'producto_ids.*'         => ['integer', 'exists:productos,id'],
            'columnas'               => ['array'],
            'columnas.*.caracteristica_id' => ['required', 'integer', 'exists:caracteristicas,id'],
            'columnas.*.orden'       => ['nullable', 'integer', 'min:0'],
            'columnas.*.label'       => ['nullable', 'string', 'max:100'],
            'columnas.*.visible'     => ['boolean'],
            'columnas.*.ancho'       => ['nullable', 'string', 'max:50'],
            'columnas.*.render'      => ['required', 'in:texto,badges,chips,iconos,count'],
            'columnas.*.multivalor_modo' => ['required', 'in:inline,badges,count'],
            'columnas.*.max_items'   => ['required', 'integer', 'min:1', 'max:99'],
            'columnas.*.fallback'    => ['nullable', 'string', 'max:50'],
        ];
    }

    public function updatedFormNombre(string $value): void
    {
        if (blank($this->form['slug'])) {
            //$this->form['slug'] = Str::slug($value);
        }
    }

    public function render()
    {
        $q = FiltroProduccion::query()
            ->withCount('productos')
            ->withCount(['caracteristicas as columnas_count'])
            ->when($this->search, function ($qq) {
                $term = "%{$this->search}%";
                $qq->where(function ($sub) use ($term) {
                    $sub->where('nombre', 'like', $term)
                        ->orWhere('slug', 'like', $term)
                        ->orWhere('descripcion', 'like', $term);
                });
            })
            ->orderByRaw('COALESCE(orden, 999999), id DESC');

        
         

        $filtros = $q->paginate($this->perPage);

        // Listas para selects (ligeramente filtradas)
        $productos = Producto::query()
            ->when($this->productoSearch, fn($qq) => $qq->where('nombre', 'like', "%{$this->productoSearch}%"))
            ->orderBy('nombre')
            ->limit(200)
            ->get(['id','nombre']);

        $caracteristicas = Caracteristica::query()
            ->when($this->caracteristicaSearch, fn($qq) => $qq->where('nombre', 'like', "%{$this->caracteristicaSearch}%"))
            ->orderBy('nombre')
            ->limit(200)
            ->get(['id','nombre']);

        return view('livewire.catalogos.produccion.filtros-produccion-crud', [
            'filtros'         => $filtros,
            'productos'       => $productos,
            'caracteristicas' => $caracteristicas,
        ]);
    }

    public function openCreate(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->editId = null;
        $this->form = [
            'nombre'      => '',
            'slug'        => '',
            'descripcion' => '',
            'visible'     => true,
            'orden'       => null,
        ];
        $this->producto_ids = [];
        $this->columnas = [];
        $this->modalTab = 'datos';
        $this->modalOpen = true;

        $this->dispatch('filtro-notify', message: 'Creando nuevo filtro de producción…');
    }

    public function openEdit(int $id): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $filtro = FiltroProduccion::with(['productos:id', 'caracteristicas'])->findOrFail($id);

        $this->editId = $filtro->id;
        $this->form = [
            'nombre'      => $filtro->nombre,
            'slug'        => $filtro->slug,
            'descripcion' => $filtro->descripcion,
            'visible'     => (bool) $filtro->visible,
            'orden'       => $filtro->orden,
        ];

        $this->producto_ids = $filtro->productos()->pluck('productos.id')->all();

        $this->columnas = $filtro->caracteristicas->map(function ($car) {
            return [
                'caracteristica_id' => $car->id,
                'nombre'            => $car->nombre,
                'orden'             => $car->pivot->orden,
                'label'             => $car->pivot->label,
                'visible'           => (bool) $car->pivot->visible,
                'ancho'             => $car->pivot->ancho,
                'render'            => $car->pivot->render ?? 'texto',
                'multivalor_modo'   => $car->pivot->multivalor_modo ?? 'inline',
                'max_items'         => (int) ($car->pivot->max_items ?? 4),
                'fallback'          => $car->pivot->fallback,
            ];
        })->sortBy('orden')->values()->all();

        $this->modalTab = 'datos';
        $this->modalOpen = true;

        $this->dispatch('filtro-notify', message: 'Editando filtro de producción.');
    }

    public function save(): void
    {
        $this->validate();

        // Garantiza slug único si se proporciona
        if (filled($this->form['slug'])) {
            $base = Str::slug($this->form['slug']);
        } else {
            $base = Str::slug($this->form['nombre']);
        }

        DB::transaction(function () use ($base) {
            if ($this->editId) {
                $filtro = FiltroProduccion::lockForUpdate()->findOrFail($this->editId);
            } else {
                $filtro = new FiltroProduccion();
                $filtro->created_by = auth()->id();
            }

            // Resolver slug único si cambió o si es nuevo
            $slug = $base;
            $i = 1;
            while (
                FiltroProduccion::where('slug', $slug)
                    ->when($this->editId, fn($q) => $q->where('id', '!=', $this->editId))
                    ->exists()
            ) {
                $slug = $base . '-' . (++$i);
            }

            $filtro->fill([
                'nombre'      => $this->form['nombre'],
                'slug'        => $slug,
                'descripcion' => $this->form['descripcion'] ?: null,
                'visible'     => (bool) $this->form['visible'],
                'orden'       => $this->form['orden'],
            ])->save();

            // Sincronizar productos (estático)
            $filtro->productos()->sync($this->producto_ids);

            // Sincronizar columnas (características con metadatos)
            // Armamos arreglo [caracteristica_id => pivotData]
            $syncData = [];
            foreach ($this->columnas as $col) {
                $syncData[$col['caracteristica_id']] = [
                    'orden'           => $col['orden'],
                    'label'           => $col['label'],
                    'visible'         => (bool) $col['visible'],
                    'ancho'           => $col['ancho'],
                    'render'          => $col['render'],
                    'multivalor_modo' => $col['multivalor_modo'],
                    'max_items'       => $col['max_items'],
                    'fallback'        => $col['fallback'],
                ];
            }
            $filtro->caracteristicas()->sync($syncData);

            $this->editId = $filtro->id;
        });

        $this->dispatch('filtro-notify', message: 'Filtro guardado correctamente.');
        $this->dispatch('filtro-produccion-actualizado'); // para otros componentes
        $this->modalOpen = false;
        $this->resetPage();
    }

    public function deleteSelected(): void
    {
        if (empty($this->selected)) return;

        FiltroProduccion::whereIn('id', $this->selected)->delete();

        $this->selected = [];
        $this->dispatch('filtro-notify', message: 'Filtro(s) eliminado(s).');
        $this->dispatch('filtro-produccion-actualizado');
        $this->resetPage();
    }

    public function toggleVisibleSelected(): void
    {
        if (empty($this->selected)) return;

        $items = FiltroProduccion::whereIn('id', $this->selected)->get();
        foreach ($items as $it) {
            $it->visible = ! $it->visible;
            $it->save();
        }

        $this->dispatch('filtro-notify', message: 'Visibilidad alternada.');
        $this->dispatch('filtro-produccion-actualizado');
    }

    public function duplicate(int $id): void
    {
        DB::transaction(function () use ($id) {
            $orig = FiltroProduccion::with(['productos:id', 'caracteristicas'])->findOrFail($id);

            $copy = $orig->replicate(['slug', 'created_by', 'orden']);
            $copy->nombre = $orig->nombre . ' (Copia)';
            $copy->slug   = null; // se reconstruye en save()
            $copy->created_by = auth()->id();
            $copy->orden = ($orig->orden ?? 0) + 1;
            $copy->save();

            $copy->productos()->sync($orig->productos()->pluck('productos.id')->all());

            $syncData = [];
            foreach ($orig->caracteristicas as $car) {
                $syncData[$car->id] = [
                    'orden'           => $car->pivot->orden,
                    'label'           => $car->pivot->label,
                    'visible'         => (bool) $car->pivot->visible,
                    'ancho'           => $car->pivot->ancho,
                    'render'          => $car->pivot->render ?? 'texto',
                    'multivalor_modo' => $car->pivot->multivalor_modo ?? 'inline',
                    'max_items'       => (int) ($car->pivot->max_items ?? 4),
                    'fallback'        => $car->pivot->fallback,
                ];
            }
            $copy->caracteristicas()->sync($syncData);
        });

        $this->dispatch('filtro-notify', message: 'Filtro duplicado.');
        $this->dispatch('filtro-produccion-actualizado');
        $this->resetPage();
    }

    // ---- Manejo de columnas (características) ----

    public function addCaracteristica(int $caracteristicaId): void
    {
        $car = Caracteristica::find($caracteristicaId);
        if (!$car) return;

        // Evitar duplicado
        foreach ($this->columnas as $c) {
            if ((int) $c['caracteristica_id'] === (int) $caracteristicaId) {
                $this->dispatch('filtro-notify', message: 'La característica ya está en columnas.');
                return;
            }
        }

        $this->columnas[] = [
            'caracteristica_id' => $car->id,
            'nombre'            => $car->nombre,
            'orden'             => count($this->columnas) + 1,
            'label'             => $car->nombre,
            'visible'           => true,
            'ancho'             => null,
            'render'            => 'texto',
            'multivalor_modo'   => 'inline',
            'max_items'         => 4,
            'fallback'          => '—',
        ];

        $this->dispatch('filtro-notify', message: 'Columna añadida.');
    }

    public function removeColumna(int $index): void
    {
        if (! isset($this->columnas[$index])) return;
        unset($this->columnas[$index]);
        $this->columnas = array_values($this->columnas);
        $this->dispatch('filtro-notify', message: 'Columna removida.');
    }

    public function reorderColumna(int $fromIndex, int $toIndex): void
    {
        if (! isset($this->columnas[$fromIndex]) || $toIndex < 0 || $toIndex >= count($this->columnas)) return;

        $item = $this->columnas[$fromIndex];
        array_splice($this->columnas, $fromIndex, 1);
        array_splice($this->columnas, $toIndex, 0, [$item]);

        // Reasignar orden secuencial
        foreach ($this->columnas as $i => &$col) {
            $col['orden'] = $i + 1;
        }
        unset($col);

        $this->dispatch('filtro-notify', message: 'Columnas reordenadas.');
    }
}

// namespace App\Livewire\Catalogos\Produccion;

// use Livewire\Component;

// class FiltrosProduccionCrud extends Component
// {
//     public function render()
//     {
//         return view('livewire.catalogos.produccion.filtros-produccion-crud');
//     }
// }

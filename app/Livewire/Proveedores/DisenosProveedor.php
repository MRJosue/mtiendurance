<?php

namespace App\Livewire\Proveedores;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Proyecto;
use Illuminate\Support\Facades\Auth;

class DisenosProveedor extends Component
{
    use WithPagination;

    public int $perPage = 20;
    public array $perPageOptions = [10, 20, 30, 50, 100];

    // Tabs de estado de diseño
    public array $tabs = [
        'TODOS',
        'PENDIENTE',
        'ASIGNADO',
        'EN PROCESO',
        'REVISION',
        'DISEÑO RECHAZADO',
        'DISEÑO APROBADO',
        'CANCELADO',
        'REPROGRAMAR',
    ];

    public string $activeTab = 'TODOS';

    // Ordenamiento
    public string $sortField = 'id';
    public string $sortDir   = 'desc';
    protected array $sortable = ['id', 'nombre', 'estado'];

    // Filtros simples (sin modales)
    public array $filters = [
        'id'     => null,
        'nombre' => null,
        'estado' => null,
    ];

    public function updatedPerPage($value): void
    {
        $value = (int) $value;
        if (! in_array($value, $this->perPageOptions, true)) {
            $value = 20;
        }
        $this->perPage = $value;
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, $this->sortable, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir   = 'asc';
        }

        $this->resetPage();
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filters = [
            'id'     => null,
            'nombre' => null,
            'estado' => null,
        ];

        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function render()
    {
        $user   = Auth::user();
        $userId = $user->id ?? null;

        $query = Proyecto::query()
            ->with([
                'user:id,name',
                 'proveedor:id,name',
            ])
            // Solo proyectos activos
            ->where('ind_activo', 1);

        // 🔐 LÓGICA DE PERMISO
        // Si tiene el permiso, ve TODOS los proyectos que requieren proveedor (cualquier proveedor_id).
        // Si NO lo tiene, solo ve los que le están asignados a él.
        if ($user && $user->can('proveedor.ver-todos-disenos')) {
           $query->where('flag_requiere_proveedor',1);
        } else {
            $query->where('proveedor_id', $userId);
        }

        // Filtro por ID (soporta 1 o varios separados por coma/espacio)
        $query->when($this->filters['id'], function ($q, $v) {
            $ids = collect(preg_split('/[,;\s]+/', (string) $v, -1, PREG_SPLIT_NO_EMPTY))
                ->map(fn ($i) => (int) trim($i))
                ->filter();

            if ($ids->count() === 1) {
                $q->where('id', $ids->first());
            } elseif ($ids->isNotEmpty()) {
                $q->whereIn('id', $ids->all());
            }
        });

        // Filtro por nombre
        $query->when($this->filters['nombre'], fn ($q, $v) =>
            $q->where('nombre', 'like', '%' . $v . '%')
        );

        // Filtro por estado (select)
        $query->when($this->filters['estado'], fn ($q, $v) =>
            $q->where('estado', $v)
        );

        // Tabs
        if ($this->activeTab === 'REPROGRAMAR') {
            $query->where('estado', 'DISEÑO APROBADO')
                  ->where('flag_reconfigurar', 1);
        } elseif (in_array($this->activeTab, [
            'PENDIENTE',
            'ASIGNADO',
            'EN PROCESO',
            'REVISION',
            'DISEÑO APROBADO',
            'DISEÑO RECHAZADO',
            'CANCELADO',
        ], true)) {
            $query->where('estado', $this->activeTab);
        }

        // Orden
        if (! in_array($this->sortField, $this->sortable, true)) {
            $this->sortField = 'id';
        }
        $query->orderBy($this->sortField, $this->sortDir);

        $projects = $query->paginate($this->perPage);

        return view('livewire.proveedores.disenos-proveedor', compact('projects'));
    }
}

// namespace App\Livewire\Proveedores;

// use Livewire\Component;

// class DisenosProveedor extends Component
// {
//     public function render()
//     {
//         return view('livewire.proveedores.disenos-proveedor');
//     }
// }

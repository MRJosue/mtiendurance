<?php

namespace App\Livewire\Dashboard\ProveedorPanel;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Pedido;
use App\Models\Proyecto;

class PedidosProveedorDashboard extends Component
{
    use WithPagination;

    public array  $tabs      = ['PEDIDOS', 'MUESTRAS'];
    public string $activeTab = 'PEDIDOS';

    public int $perPage = 20;
    public array $perPageOptions = [10, 20, 30, 50, 100];

    public bool $mostrarFiltros = false;

    public string $sortField = 'id';
    public string $sortDir   = 'desc';

    public array $sortable = [
        'id','created_at','total','fecha_produccion','fecha_embarque','fecha_entrega',
        'estatus_proveedor','proyecto_nombre','cliente_nombre',
    ];

    public array $filters = [
        'id'               => null,   // ID pedido o proyecto (soporta múltiples)
        'proyecto'         => null,   // nombre proyecto
        'cliente'          => null,   // nombre/email cliente
        'inactivos'        => false,
        'estatus_proveedor'=> '',
        'solo_no_vistos'   => false,
        'fecha_desde'      => null,
        'fecha_hasta'      => null,
    ];

    // Modal proveedor
    public bool $modalProveedor = false;
    public ?int $pedidoId = null;
    public ?string $estatus_proveedor = null;
    public ?string $nota_proveedor = null;

    public array $estatusProveedorOptions = ['PENDIENTE','EN PROCESO','LISTO','BLOQUEADO'];

    public function setTab(string $tab): void
    {
        if ($this->activeTab !== $tab) {
            $this->activeTab = $tab;
            $this->resetPage();
        }
    }

    public function updatedPerPage($value): void
    {
        $value = (int) $value;
        if (!in_array($value, $this->perPageOptions, true)) $value = 20;
        $this->perPage = $value;
        $this->resetPage();
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
    }

    public function buscarPorFiltros(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filters = [
            'id'               => null,
            'proyecto'         => null,
            'cliente'          => null,
            'inactivos'        => false,
            'estatus_proveedor'=> '',
            'solo_no_vistos'   => false,
            'fecha_desde'      => null,
            'fecha_hasta'      => null,
        ];

        $this->resetPage();
        $this->dispatch('filters-cleared');
    }

    public function sortBy(string $field): void
    {
        if (!in_array($field, $this->sortable, true)) return;

        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir   = 'asc';
        }

        $this->resetPage();
    }

    /**
     * ✅ LÓGICA IGUAL A DisenosProveedor:
     * - con permiso: proyectos flag_requiere_proveedor = 1 (todos)
     * - sin permiso: proyectos proveedor_id = auth()->id()
     */
    private function proyectosPermitidosQuery()
    {
        $user = Auth::user();

        $q = Proyecto::query()
            ->where('ind_activo', 1);

        if ($user && $user->can('proveedor.ver-todos-disenos')) {
            $q->where('flag_requiere_proveedor', 1);
        } else {
            $q->where('proveedor_id', $user?->id);
        }

        return $q;
    }

    private function baseQuery()
    {
        // Filtrar pedidos por proyectos permitidos (subquery)
        return Pedido::query()
            ->whereIn('proyecto_id', $this->proyectosPermitidosQuery()->select('id'));
    }

    public function abrirModalProveedor(int $pedidoId): void
    {
        $this->pedidoId = $pedidoId;

        $pedido = $this->baseQuery()
            ->where('id', $pedidoId)
            ->firstOrFail();

        $pedido->update([
            'proveedor_visto_at'  => now(),
            'proveedor_visto_por' => Auth::id(),
        ]);

        $this->estatus_proveedor = $pedido->estatus_proveedor ?? 'PENDIENTE';
        $this->nota_proveedor    = $pedido->nota_proveedor;

        $this->modalProveedor = true;
    }

    public function guardarProveedor(): void
    {
        $this->validate([
            'pedidoId'          => 'required|integer',
            'estatus_proveedor' => 'required|string|in:' . implode(',', $this->estatusProveedorOptions),
            'nota_proveedor'    => 'nullable|string|max:2000',
        ]);

        $pedido = $this->baseQuery()
            ->where('id', $this->pedidoId)
            ->firstOrFail();

        $pedido->update([
            'estatus_proveedor' => $this->estatus_proveedor,
            'nota_proveedor'    => $this->nota_proveedor,
            'proveedor_visto_at'  => now(),
            'proveedor_visto_por' => Auth::id(),
        ]);

        $this->modalProveedor = false;
        session()->flash('message', '✅ Estatus de proveedor actualizado.');
        $this->dispatch('refresh-pedidos-proveedor');
    }

    public function render()
    {
        $query = $this->baseQuery()
            ->with([
                'producto.categoria',
                'proyecto.user',
                'usuario:id,name,email,empresa_id,sucursal_id',
                'usuario.empresa:id,nombre',
                'usuario.sucursal:id,nombre,empresa_id',
                'usuario.sucursal.empresa:id,nombre',
            ])
            ->where('tipo', $this->activeTab === 'MUESTRAS' ? 'MUESTRA' : 'PEDIDO');

        // activos / inactivos
        $query->where('ind_activo', $this->filters['inactivos'] ? 0 : 1);

        // ID pedido o proyecto (multi)
        $query->when($this->filters['id'], function ($q, $v) {
            $ids = collect(preg_split('/[,;\s]+/', (string)$v, -1, PREG_SPLIT_NO_EMPTY))
                ->map(fn ($i) => (int) trim($i))
                ->filter();

            if ($ids->isNotEmpty()) {
                $q->where(function ($w) use ($ids) {
                    $w->whereIn('id', $ids->all())
                      ->orWhereIn('proyecto_id', $ids->all());
                });
            }
        });

        // proyecto nombre
        $query->when($this->filters['proyecto'], function ($q, $v) {
            $v = trim((string)$v);
            $q->whereHas('proyecto', fn($p) => $p->where('nombre', 'like', "%{$v}%"));
        });

        // cliente nombre/email
        $query->when($this->filters['cliente'], function ($q, $v) {
            $v = trim((string)$v);
            $q->whereHas('proyecto.user', fn($u) =>
                $u->where('name', 'like', "%{$v}%")
                  ->orWhere('email', 'like', "%{$v}%")
            );
        });

        // estatus proveedor
        if (!empty($this->filters['estatus_proveedor'])) {
            $query->where('estatus_proveedor', $this->filters['estatus_proveedor']);
        }

        // solo no vistos
        if (!empty($this->filters['solo_no_vistos'])) {
            $query->whereNull('proveedor_visto_at');
        }

        // rango fecha created_at
        if ($this->filters['fecha_desde'] || $this->filters['fecha_hasta']) {
            $desde = $this->filters['fecha_desde'] ? Carbon::parse($this->filters['fecha_desde'])->startOfDay() : null;
            $hasta = $this->filters['fecha_hasta'] ? Carbon::parse($this->filters['fecha_hasta'])->endOfDay() : null;

            if ($desde && $hasta) $query->whereBetween('created_at', [$desde, $hasta]);
            elseif ($desde)       $query->where('created_at', '>=', $desde);
            elseif ($hasta)       $query->where('created_at', '<=', $hasta);
        }

        // ordenamiento con subselects
        $dir = $this->sortDir === 'asc' ? 'asc' : 'desc';
        $pedidoTable   = (new Pedido)->getTable();
        $proyectoTable = (new Proyecto)->getTable();

        switch ($this->sortField) {
            case 'id':
            case 'created_at':
            case 'total':
            case 'fecha_produccion':
            case 'fecha_embarque':
            case 'fecha_entrega':
            case 'estatus_proveedor':
                $query->orderBy($pedidoTable.'.'.$this->sortField, $dir);
                break;

            case 'proyecto_nombre':
                $query->orderBy(
                    Proyecto::select('nombre')
                        ->whereColumn($proyectoTable.'.id', $pedidoTable.'.proyecto_id')
                        ->limit(1),
                    $dir
                );
                break;

            case 'cliente_nombre':
                $query->orderByRaw(
                    "(SELECT {$proyectoTable}.usuario_id
                      FROM {$proyectoTable}
                      WHERE {$proyectoTable}.id = {$pedidoTable}.proyecto_id
                      LIMIT 1) {$dir}"
                );
                break;

            default:
                $query->orderBy($pedidoTable.'.id', 'desc');
        }

        return view('livewire.dashboard.proveedor-panel.pedidos-proveedor-dashboard', [
            'pedidos' => $query->paginate($this->perPage),
        ]);
    }
}

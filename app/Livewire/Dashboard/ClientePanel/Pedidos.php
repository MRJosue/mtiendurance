<?php

namespace App\Livewire\Dashboard\ClientePanel;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Pedido;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Proyecto;
use App\Models\User;

class Pedidos extends Component
{
    use WithPagination;

    /* ---- UI: pestañas PEDIDOS | MUESTRAS ---- */
    public array  $tabs      = ['PEDIDOS', 'MUESTRAS'];
    public string $activeTab = 'PEDIDOS';

    /* ---- Filtros extra que ya tenías ---- */
    public bool $mostrarFiltros          = false;
    public bool $mostrarSoloNoAprobados  = true;

    public $modalVerInfo = false;
    public $infoProyecto = null;

    public string $sortField = 'created_at';
public string $sortDir   = 'desc';

    public array $filters = [
        'id'            => null,   
        'cliente'       => null,   
        'estado_pedido' => null,   
        'estado_diseno' => null,   
        'fecha_desde'   => null,   
        'fecha_hasta'   => null,   
    ];

    public array $sortable = [
    'id','created_at','total','estado','fecha_produccion','fecha_entrega',
    'estado_diseno','proyecto_nombre','cliente_nombre',
    ];




    /* ---- Manipuladores de UI ---- */
    public function setTab(string $tab): void
    {
        if ($this->activeTab !== $tab) {
            $this->activeTab = $tab;
            $this->resetPage();   // reinicia paginación al cambiar de pestaña
        }
    }

    public function buscarPorFiltros(): void
    {
        $this->resetPage();
    }

    /** Cambia campo/dirección de orden y reinicia la página */
    public function sortBy(string $field): void
    {
        if (! in_array($field, $this->sortable, true)) {
            return; // ignorar campos no permitidos
        }

        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir   = 'asc';
        }

        $this->resetPage();
    }


    public function abrirModalVerInfo($proyectoId)
    {
        Log::debug('Datos del usuario procesados Open modal');

$proyecto = \App\Models\Proyecto::with([
    'user',
    'categoria',
])->findOrFail($proyectoId);

        // Asegúrate de que los campos sean arrays
        $proyecto->caracteristicas_sel = is_array($proyecto->caracteristicas_sel) ? $proyecto->caracteristicas_sel : json_decode($proyecto->caracteristicas_sel, true);
        $proyecto->producto_sel        = is_array($proyecto->producto_sel)        ? $proyecto->producto_sel        : json_decode($proyecto->producto_sel, true);
        $proyecto->categoria_sel       = is_array($proyecto->categoria_sel)       ? $proyecto->categoria_sel       : json_decode($proyecto->categoria_sel, true);
        
        $this->infoProyecto = $proyecto;
        $this->modalVerInfo = true;
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filters = [
            'id'            => null,
            'cliente'       => null,
            'estado_pedido' => null,
            'estado_diseno' => null,
            'fecha_desde'   => null,
            'fecha_hasta'   => null,
        ];
        $this->resetPage();
        $this->dispatch('filters-cleared');
    }

    

    /* ---- Render ---- */

    public function render()
    {
        $user = auth()->user();

        $query = Pedido::with([
            'producto.categoria',
            'proyecto.user',
                'usuario:id,name,empresa_id,sucursal_id',
                'usuario.empresa:id,nombre',
                'usuario.sucursal:id,nombre,empresa_id',
                'usuario.sucursal.empresa:id,nombre',

            'pedidoCaracteristicas.caracteristica',
            'pedidoOpciones.opcion.caracteristicas',
            'usuario:id,name',
        ])->where('tipo', $this->activeTab === 'MUESTRAS' ? 'MUESTRA' : 'PEDIDO');

        // ---------- Filtros ----------
        $query
            // ID: pedido.id o pedido.proyecto_id
            ->when($this->filters['id'], function ($q, $v) {
                $ids = collect(preg_split('/[,;\s]+/', (string) $v, -1, PREG_SPLIT_NO_EMPTY))
                    ->map(fn($i) => (int) trim($i))
                    ->filter();
                if ($ids->isNotEmpty()) {
                    $q->where(function($w) use ($ids) {
                        $w->whereIn('id', $ids->all())
                        ->orWhereIn('proyecto_id', $ids->all());
                    });
                }
            })
            // Cliente: nombre o email del usuario dueño del proyecto
            ->when($this->filters['cliente'], function ($q, $v) {
                $v = trim((string) $v);
                $q->whereHas('proyecto.user', fn($u) =>
                    $u->where('name', 'like', "%{$v}%")
                    ->orWhere('email', 'like', "%{$v}%")
                );
            })
            // Estado del pedido
            ->when($this->filters['estado_pedido'], fn($q, $v) =>
                $q->where('estado', $v)
            )
            // Estado del diseño (en proyectos)
            ->when($this->filters['estado_diseno'], fn($q, $v) =>
                $q->whereHas('proyecto', fn($p) => $p->where('estado', $v))
            )
            // Rango de fechas por created_at del pedido
            ->when($this->filters['fecha_desde'] || $this->filters['fecha_hasta'], function ($q) {
                $desde = $this->filters['fecha_desde'] ? Carbon::parse($this->filters['fecha_desde'])->startOfDay() : null;
                $hasta = $this->filters['fecha_hasta'] ? Carbon::parse($this->filters['fecha_hasta'])->endOfDay()   : null;
                if ($desde && $hasta)      $q->whereBetween('created_at', [$desde, $hasta]);
                elseif ($desde)            $q->where('created_at', '>=', $desde);
                elseif ($hasta)            $q->where('created_at', '<=', $hasta);
            });

        // ---------- Restricción por rol (igual que ya tienes) ----------
        if ($user->hasRole('admin') || $user->can('tablaPedidos-ver-todos-los-pedidos')) {
            // ve todo
        } elseif ($user->hasRole('cliente_principal')) {
            $idsUsuarios = collect($user->subordinados ?? [])
                ->map(fn ($id) => (int) $id)->filter(fn ($id) => $id > 0)
                ->prepend($user->id)->unique()->values()->all();
            $query->whereHas('proyecto', fn($q) => $q->whereIn('usuario_id', $idsUsuarios));
        } elseif ($user->hasAnyRole(['cliente_subordinado','estaf'])) {
            $query->whereHas('proyecto', fn($q) => $q->where('usuario_id', $user->id));
        } else {
            $query->whereHas('proyecto', fn($q) => $q->where('usuario_id', $user->id));
        }

        $pedidoTable   = (new Pedido)->getTable();    // 'pedido' o 'pedidos' según tu modelo
        $proyectoTable = (new Proyecto)->getTable();  // normalmente 'proyectos'
        $userTable     = (new User)->getTable();      // normalmente 'users'

        // ---------- Ordenamiento ----------
        $dir = $this->sortDir === 'asc' ? 'asc' : 'desc';

        switch ($this->sortField) {
            case 'id':
            case 'created_at':
            case 'total':
            case 'estado':
            case 'fecha_produccion':
            case 'fecha_entrega':
                $query->orderBy($pedidoTable.'.'.$this->sortField, $dir);
                break;

            case 'estado_diseno':
                $query->orderBy(
                    Proyecto::select('estado')
                        ->whereColumn($proyectoTable.'.id', $pedidoTable.'.proyecto_id')
                        ->limit(1),
                    $dir
                );
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
                    // ⇩ Ordenar por ID del cliente/usuario (dueño del proyecto) con subselect crudo
                    $query->orderByRaw(
                        "(SELECT {$proyectoTable}.usuario_id
                        FROM {$proyectoTable}
                        WHERE {$proyectoTable}.id = {$pedidoTable}.proyecto_id
                        LIMIT 1) {$dir}"
                    );
                break;

            default:
                $query->orderBy($pedidoTable.'.created_at', 'desc');
        }

        return view('livewire.dashboard.cliente-panel.pedidos', [
            'pedidos' => $query->paginate(100),
        ]);
    }
}

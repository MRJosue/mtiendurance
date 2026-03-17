<?php

namespace App\Livewire\Dashboard\ClientePanel;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Pedido;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Proyecto;
use App\Models\User;
use App\Exports\PedidosFilteredExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Models\PedidoTalla;
use App\Models\GrupoTalla;

class Pedidos extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public array $tabsEstado = [
        'TODOS',
        'PENDIENTE',
        'APROBADO',
        'POR PROGRAMAR',
        'PROGRAMADO',
        'ENTREGADO',
        'RECHAZADO',
        'CANCELADO',
        'ARCHIVADO',
    ];

    public string $activeEstadoTab = 'TODOS';

    /**
     * Si el valor es null, no requiere permiso.
     * Cambia los nombres de permisos por los reales de tu sistema.
     */
    protected array $tabsEstadoPermisos = [
        'TODOS'          => null,
        'PENDIENTE'      => 'pedidos-tab-pendiente',
        'APROBADO'       => 'pedidos-tab-aprobado',
        'POR PROGRAMAR'  => 'pedidos-tab-por-programar',
        'PROGRAMADO'     => 'pedidos-tab-programado',
        'ENTREGADO'      => 'pedidos-tab-entregado',
        'RECHAZADO'      => 'pedidos-tab-rechazado',
        'CANCELADO'      => 'pedidos-tab-cancelado',
        'ARCHIVADO'      => 'pedidos-tab-archivado',
    ];

    public int $perPage = 100;

    public bool $mostrarFiltros = false;
    public bool $mostrarSoloNoAprobados = true;

    public $modalVerInfo = false;
    public $infoProyecto = null;

    public string $sortField = 'created_at';
    public string $sortDir   = 'desc';

    public array $filters = [
        'id'                    => null,
        'cliente'               => null,
        'proyecto'              => null,
        'total'                 => null,
        'estado_pedido'         => null,
        'estado_diseno'         => null,
        'fecha_desde'           => null,
        'fecha_hasta'           => null,
        'fecha_produccion_from' => null,
        'fecha_produccion_to'   => null,
        'fecha_entrega_from'    => null,
        'fecha_entrega_to'      => null,
        'inactivos'             => false,
    ];

    public array $sortable = [
        'id',
        'created_at',
        'total',
        'estado',
        'fecha_produccion',
        'fecha_entrega',
        'estado_diseno',
        'proyecto_nombre',
        'cliente_nombre',
    ];

    public bool $modalTallas = false;
    public ?int $tallasPedidoId = null;
    public array $tallasDistribucionPorGrupo = [];
    public int $tallasTotal = 0;

    public function mount(): void
    {
        if (!in_array($this->activeEstadoTab, $this->tabsEstadoVisibles, true)) {
            $this->activeEstadoTab = $this->tabsEstadoVisibles[0] ?? 'TODOS';
        }
    }

    public function getTabsEstadoVisiblesProperty(): array
    {
        $user = auth()->user();

        return collect($this->tabsEstado)
            ->filter(function (string $tab) use ($user) {
                $permiso = $this->tabsEstadoPermisos[$tab] ?? null;

                if (blank($permiso)) {
                    return true;
                }

                return $user?->hasRole('admin') || $user?->can($permiso);
            })
            ->values()
            ->all();
    }

    public function setEstadoTab(string $tab): void
    {
        if (!in_array($tab, $this->tabsEstadoVisibles, true)) {
            return;
        }

        $this->activeEstadoTab = $tab;
        $this->resetPage();
    }

    public function buscarPorFiltros(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (!in_array($field, $this->sortable, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir = 'asc';
        }

        $this->resetPage();
    }

    public function abrirModalVerInfo($proyectoId): void
    {
        $proyecto = \App\Models\Proyecto::with([
            'user',
            'categoria',
        ])->findOrFail($proyectoId);

        $proyecto->caracteristicas_sel = is_array($proyecto->caracteristicas_sel)
            ? $proyecto->caracteristicas_sel
            : json_decode($proyecto->caracteristicas_sel, true);

        $proyecto->producto_sel = is_array($proyecto->producto_sel)
            ? $proyecto->producto_sel
            : json_decode($proyecto->producto_sel, true);

        $proyecto->categoria_sel = is_array($proyecto->categoria_sel)
            ? $proyecto->categoria_sel
            : json_decode($proyecto->categoria_sel, true);

        $this->infoProyecto = $proyecto;
        $this->modalVerInfo = true;
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filters = [
            'id'                    => null,
            'cliente'               => null,
            'proyecto'              => null,
            'total'                 => null,
            'estado_pedido'         => null,
            'estado_diseno'         => null,
            'fecha_desde'           => null,
            'fecha_hasta'           => null,
            'fecha_produccion_from' => null,
            'fecha_produccion_to'   => null,
            'fecha_entrega_from'    => null,
            'fecha_entrega_to'      => null,
            'inactivos'             => false,
        ];

        $this->activeEstadoTab = in_array('TODOS', $this->tabsEstadoVisibles, true)
            ? 'TODOS'
            : ($this->tabsEstadoVisibles[0] ?? 'TODOS');

        $this->resetPage();
        $this->dispatch('filters-cleared');
    }

    public function getHasFiltersProperty(): bool
    {
        if (!empty($this->filters['inactivos'])) return true;
        if ($this->activeEstadoTab !== 'TODOS') return true;

        foreach ($this->filters as $value) {
            if (is_string($value) && trim($value) !== '') return true;
            if (!is_string($value) && !is_null($value) && $value !== false) return true;
        }

        return false;
    }

    private function buildPedidosQuery(): Builder
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
        ])->where('tipo', 'PEDIDO');

        if ($this->filters['inactivos']) {
            $query->where('ind_activo', 0);
        } else {
            $query->where('ind_activo', 1);
        }

        if (
            $this->activeEstadoTab !== 'TODOS' &&
            in_array($this->activeEstadoTab, $this->tabsEstadoVisibles, true)
        ) {
            $query->where('estado', $this->activeEstadoTab);
        }

        $query
            ->when($this->filters['id'], function ($q, $v) {
                $ids = collect(preg_split('/[,;\s]+/', (string) $v, -1, PREG_SPLIT_NO_EMPTY))
                    ->map(fn($i) => (int) trim($i))
                    ->filter();

                if ($ids->isNotEmpty()) {
                    $q->where(function ($w) use ($ids) {
                        $w->whereIn('id', $ids->all())
                          ->orWhereIn('proyecto_id', $ids->all());
                    });
                }
            })
            ->when($this->filters['cliente'], function ($q, $v) {
                $v = trim((string) $v);
                $q->whereHas('proyecto.user', fn($u) =>
                    $u->where('name', 'like', "%{$v}%")
                      ->orWhere('email', 'like', "%{$v}%")
                );
            })
            ->when($this->filters['proyecto'], function ($q, $v) {
                $v = trim((string) $v);
                $q->whereHas('proyecto', fn($p) =>
                    $p->where('nombre', 'like', "%{$v}%")
                );
            })
            ->when($this->filters['total'], function ($q, $v) {
                $q->where('total', 'like', '%' . trim((string) $v) . '%');
            })
            ->when($this->filters['estado_pedido'], fn($q, $v) => $q->where('estado', $v))
            ->when($this->filters['estado_diseno'], fn($q, $v) =>
                $q->whereHas('proyecto', fn($p) => $p->where('estado', $v))
            )
            ->when($this->filters['fecha_desde'] || $this->filters['fecha_hasta'], function ($q) {
                $desde = $this->filters['fecha_desde']
                    ? Carbon::parse($this->filters['fecha_desde'])->startOfDay()
                    : null;

                $hasta = $this->filters['fecha_hasta']
                    ? Carbon::parse($this->filters['fecha_hasta'])->endOfDay()
                    : null;

                if ($desde && $hasta) {
                    $q->whereBetween('created_at', [$desde, $hasta]);
                } elseif ($desde) {
                    $q->where('created_at', '>=', $desde);
                } elseif ($hasta) {
                    $q->where('created_at', '<=', $hasta);
                }
            })
            ->when($this->filters['fecha_produccion_from'], fn($q, $v) => $q->whereDate('fecha_produccion', '>=', $v))
            ->when($this->filters['fecha_produccion_to'], fn($q, $v) => $q->whereDate('fecha_produccion', '<=', $v))
            ->when($this->filters['fecha_entrega_from'], fn($q, $v) => $q->whereDate('fecha_entrega', '>=', $v))
            ->when($this->filters['fecha_entrega_to'], fn($q, $v) => $q->whereDate('fecha_entrega', '<=', $v));

        if ($user->hasRole('admin') || $user->can('tablaPedidos-ver-todos-los-pedidos')) {
            // ve todo
        } elseif ($user->hasRole('cliente_principal')) {
            $idsUsuarios = collect($user->subordinados ?? [])
                ->map(fn($id) => (int) $id)
                ->filter(fn($id) => $id > 0)
                ->prepend($user->id)
                ->unique()
                ->values()
                ->all();

            $query->whereHas('proyecto', fn($q) => $q->whereIn('usuario_id', $idsUsuarios));
        } else {
            $query->whereHas('proyecto', fn($q) => $q->where('usuario_id', $user->id));
        }

        $pedidoTable   = (new Pedido)->getTable();
        $proyectoTable = (new Proyecto)->getTable();
        $dir = $this->sortDir === 'asc' ? 'asc' : 'desc';

        switch ($this->sortField) {
            case 'id':
            case 'created_at':
            case 'total':
            case 'estado':
            case 'fecha_produccion':
            case 'fecha_entrega':
                $query->orderBy($pedidoTable . '.' . $this->sortField, $dir);
                break;

            case 'estado_diseno':
                $query->orderBy(
                    Proyecto::select('estado')
                        ->whereColumn($proyectoTable . '.id', $pedidoTable . '.proyecto_id')
                        ->limit(1),
                    $dir
                );
                break;

            case 'proyecto_nombre':
                $query->orderBy(
                    Proyecto::select('nombre')
                        ->whereColumn($proyectoTable . '.id', $pedidoTable . '.proyecto_id')
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
                $query->orderBy($pedidoTable . '.created_at', 'desc');
                break;
        }

        return $query;
    }

    public function exportExcel()
    {
        if (!$this->hasFilters) {
            $this->dispatch('notify', message: 'Para exportar primero aplica al menos 1 filtro.');
            return;
        }

        $query = $this->buildPedidosQuery();
        $fecha = now()->format('Y-m-d_His');

        return Excel::download(
            new PedidosFilteredExport($query),
            "pedidos_{$fecha}.xlsx"
        );
    }

    public function abrirModalTallas(int $pedidoId): void
    {
        $pedido = Pedido::query()
            ->select('id', 'flag_tallas', 'total')
            ->where('id', $pedidoId)
            ->firstOrFail();

        if ((int) ($pedido->flag_tallas ?? 0) !== 1) {
            $this->dispatch('notify', message: 'Este pedido no maneja tallas.');
            return;
        }

        $ptTable = (new PedidoTalla)->getTable();
        $gTable  = (new GrupoTalla)->getTable();
        $tTable  = class_exists(\App\Models\Talla::class) ? (new \App\Models\Talla)->getTable() : 'tallas';

        $rows = DB::table("$ptTable as pt")
            ->join("$gTable as g", 'g.id', '=', 'pt.grupo_talla_id')
            ->join("$tTable as t", 't.id', '=', 'pt.talla_id')
            ->where('pt.pedido_id', $pedidoId)
            ->selectRaw('g.id as grupo_id, g.nombre as grupo, t.id as talla_id, t.nombre as talla, SUM(COALESCE(pt.cantidad,0)) as cantidad')
            ->groupBy('g.id', 'g.nombre', 't.id', 't.nombre')
            ->orderBy('g.nombre')
            ->orderBy('t.nombre')
            ->get();

        $grupos = [];

        foreach ($rows as $r) {
            $gid = (int) $r->grupo_id;

            if (!isset($grupos[$gid])) {
                $grupos[$gid] = [
                    'grupo_id' => $gid,
                    'grupo'    => (string) $r->grupo,
                    'items'    => [],
                    'subtotal' => 0,
                ];
            }

            $cant = (int) $r->cantidad;

            $grupos[$gid]['items'][] = [
                'talla_id' => (int) $r->talla_id,
                'talla'    => (string) $r->talla,
                'cantidad' => $cant,
            ];

            $grupos[$gid]['subtotal'] += $cant;
        }

        $this->tallasDistribucionPorGrupo = array_values($grupos);
        $this->tallasTotal = array_sum(array_column($this->tallasDistribucionPorGrupo, 'subtotal'));
        $this->tallasPedidoId = $pedidoId;
        $this->modalTallas = true;
    }

    public function cerrarModalTallas(): void
    {
        $this->modalTallas = false;
        $this->tallasPedidoId = null;
        $this->tallasDistribucionPorGrupo = [];
        $this->tallasTotal = 0;
    }

    public function render()
    {
        return view('livewire.dashboard.cliente-panel.pedidos', [
            'pedidos' => $this->buildPedidosQuery()->paginate($this->perPage),
        ]);
    }
}
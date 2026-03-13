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
use App\Models\Talla;

class Muestras extends Component
{
    use WithPagination;

    /* ---- UI ---- */
    public array $tabs = ['MUESTRAS'];
    public string $activeTab = 'MUESTRAS';

    public array $tabsEstado = [
        'TODOS',
        'PENDIENTE',
        'SOLICITADA',
        'MUESTRA LISTA',
        'ENTREGADA',
        'CANCELADA',
    ];

    public array $estadoTabsCounts = [
        'TODOS'         => 0,
        'PENDIENTE'     => 0,
        'SOLICITADA'    => 0,
        'MUESTRA LISTA' => 0,
        'ENTREGADA'     => 0,
        'CANCELADA'     => 0,
    ];

    public array $tabPermissions = [
    'TODOS'         => null,
    'PENDIENTE'     => 'muestras-tab-pendiente',
    'SOLICITADA'    => 'muestras-tab-solicitada',
    'MUESTRA LISTA' => 'muestras-tab-muestra-lista',
    'ENTREGADA'     => 'muestras-tab-entregada',
    'CANCELADA'     => 'muestras-tab-cancelada',
    ];

    public string $activeEstadoTab = 'TODOS';

    public int $perPage = 100;

    /* ---- Filtros extra ---- */
    public bool $mostrarFiltros = false;
    public bool $mostrarSoloNoAprobados = true;

    public $modalVerInfo = false;
    public $infoProyecto = null;

    public string $sortField = 'created_at';
    public string $sortDir = 'desc';

    public array $filters = [
        'id'            => null,
        'cliente'       => null,
        'estado_pedido' => null,
        'estado_diseno' => null,
        'fecha_desde'   => null,
        'fecha_hasta'   => null,
        'inactivos'     => false,
    ];

    public array $sortable = [
        'id','created_at','total','estado','fecha_produccion','fecha_entrega',
        'estado_diseno','proyecto_nombre','cliente_nombre',
    ];

    public bool $modalTallas = false;
    public ?int $tallasPedidoId = null;
    public array $tallasDistribucionPorGrupo = [];
    public int $tallasTotal = 0;

    public function mount(): void
    {
        $this->loadEstadoTabsCounts();

        if (!in_array($this->activeEstadoTab, $this->tabsEstadoVisibles, true)) {
            $this->activeEstadoTab = $this->tabsEstadoVisibles[0] ?? 'TODOS';
        }
    }

    public function setEstadoTab(string $tab): void
    {
        $tab = strtoupper(trim($tab));

        if (! in_array($tab, $this->tabsEstadoVisibles, true)) {
            return;
        }

        if ($this->activeEstadoTab !== $tab) {
            $this->activeEstadoTab = $tab;
            $this->resetPage();
        }
    }

    public function loadEstadoTabsCounts(): void
    {
        $user = auth()->user();

        $query = Pedido::query()->where('tipo', 'MUESTRA');

        if ($this->filters['inactivos']) {
            $query->where('ind_activo', 0);
        } else {
            $query->where('ind_activo', 1);
        }

        // Restricción por rol
        if ($user->hasRole('admin') || $user->can('tablaPedidos-ver-todas-las-muestras')) {
            // ve todo
        } elseif ($user->hasRole('cliente_principal')) {
            $idsUsuarios = collect($user->subordinados ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->prepend($user->id)
                ->unique()
                ->values()
                ->all();

            $query->whereHas('proyecto', fn($q) => $q->whereIn('usuario_id', $idsUsuarios));
        } else {
            $query->whereHas('proyecto', fn($q) => $q->where('usuario_id', $user->id));
        }

        $rows = (clone $query)
            ->selectRaw('COALESCE(estatus_muestra, "PENDIENTE") as estatus_muestra, COUNT(*) as total')
            ->groupBy('estatus_muestra')
            ->pluck('total', 'estatus_muestra')
            ->toArray();

            $counts = [
                'TODOS'         => (int) array_sum($rows),
                'PENDIENTE'     => (int) ($rows['PENDIENTE'] ?? 0),
                'SOLICITADA'    => (int) ($rows['SOLICITADA'] ?? 0),
                'MUESTRA LISTA' => (int) ($rows['MUESTRA LISTA'] ?? 0),
                'ENTREGADA'     => (int) ($rows['ENTREGADA'] ?? 0),
                'CANCELADA'     => (int) ($rows['CANCELADA'] ?? 0),
            ];

            $this->estadoTabsCounts = collect($counts)
                ->mapWithKeys(function ($value, $key) {
                    return [$key => $this->canViewTab($key) ? $value : 0];
                })
                ->toArray();
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

    public function abrirModalVerInfo($proyectoId)
    {
        Log::debug('Datos del usuario procesados Open modal');

        $proyecto = \App\Models\Proyecto::with([
            'user',
            'categoria',
        ])->findOrFail($proyectoId);

        $proyecto->caracteristicas_sel = is_array($proyecto->caracteristicas_sel) ? $proyecto->caracteristicas_sel : json_decode($proyecto->caracteristicas_sel, true);
        $proyecto->producto_sel = is_array($proyecto->producto_sel) ? $proyecto->producto_sel : json_decode($proyecto->producto_sel, true);
        $proyecto->categoria_sel = is_array($proyecto->categoria_sel) ? $proyecto->categoria_sel : json_decode($proyecto->categoria_sel, true);

        $this->infoProyecto = $proyecto;
        $this->modalVerInfo = true;
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
        $this->loadEstadoTabsCounts();
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
            'inactivos'     => false,
        ];

        $this->activeEstadoTab = in_array('TODOS', $this->tabsEstadoVisibles, true)
        ? 'TODOS'
        : ($this->tabsEstadoVisibles[0] ?? 'TODOS');

        $this->resetPage();
        $this->loadEstadoTabsCounts();
        $this->dispatch('filters-cleared');
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function getHasFiltersProperty(): bool
    {
        if (!empty($this->filters['inactivos'])) return true;

        $keys = [
            'id','cliente','estado_pedido','estado_diseno',
            'fecha_desde','fecha_hasta',
        ];

        foreach ($keys as $k) {
            $v = $this->filters[$k] ?? null;

            if (is_string($v) && trim($v) !== '') return true;
            if (!is_string($v) && !is_null($v)) return true;
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
        ])->where('tipo', 'MUESTRA');

        if ($this->filters['inactivos']) {
            $query->where('ind_activo', 0);
        } else {
            $query->where('ind_activo', 1);
        }

        $query
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
            ->when($this->filters['cliente'], function ($q, $v) {
                $v = trim((string) $v);
                $q->whereHas('proyecto.user', fn($u) =>
                    $u->where('name', 'like', "%{$v}%")
                      ->orWhere('email', 'like', "%{$v}%")
                );
            })
            ->when($this->filters['estado_pedido'], fn($q, $v) =>
                    $q->where('estatus_muestra', $v)
                )
            ->when($this->activeEstadoTab !== 'TODOS', fn($q) => $q->where('estatus_muestra', $this->activeEstadoTab))
            ->when($this->filters['estado_diseno'], fn($q, $v) =>
                $q->whereHas('proyecto', fn($p) => $p->where('estado', $v))
            )
            ->when($this->filters['fecha_desde'] || $this->filters['fecha_hasta'], function ($q) {
                $desde = $this->filters['fecha_desde'] ? Carbon::parse($this->filters['fecha_desde'])->startOfDay() : null;
                $hasta = $this->filters['fecha_hasta'] ? Carbon::parse($this->filters['fecha_hasta'])->endOfDay() : null;

                if ($desde && $hasta) $q->whereBetween('created_at', [$desde, $hasta]);
                elseif ($desde) $q->where('created_at', '>=', $desde);
                elseif ($hasta) $q->where('created_at', '<=', $hasta);
            });

        if ($user->hasRole('admin') || $user->can('tablaPedidos-ver-todas-las-muestras')) {
            // ve todo
        } elseif ($user->hasRole('cliente_principal')) {
            $idsUsuarios = collect($user->subordinados ?? [])
                ->map(fn ($id) => (int) $id)->filter(fn ($id) => $id > 0)
                ->prepend($user->id)->unique()->values()->all();

            $query->whereHas('proyecto', fn($q) => $q->whereIn('usuario_id', $idsUsuarios));
        } else {
            $query->whereHas('proyecto', fn($q) => $q->where('usuario_id', $user->id));
        }

        $pedidoTable = (new Pedido)->getTable();
        $proyectoTable = (new Proyecto)->getTable();
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

        return $query;
    }

    public function exportExcel()
    {
        if (! $this->hasFilters) {
            $this->dispatch('notify', message: 'Para exportar primero aplica al menos 1 filtro.');
            return;
        }

        $query = $this->buildPedidosQuery();
        $tipo = 'MUESTRAS';
        $fecha = now()->format('Y-m-d_His');

        return Excel::download(
            new PedidosFilteredExport($query),
            "pedidos_{$tipo}_{$fecha}.xlsx"
        );
    }

    public function abrirModalTallas(int $pedidoId): void
    {
        $pedido = Pedido::query()
            ->select('id', 'flag_tallas', 'total')
            ->where('id', $pedidoId)
            ->firstOrFail();

        if ((int)($pedido->flag_tallas ?? 0) !== 1) {
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
            $gid = (int)$r->grupo_id;

            if (!isset($grupos[$gid])) {
                $grupos[$gid] = [
                    'grupo_id' => $gid,
                    'grupo'    => (string)$r->grupo,
                    'items'    => [],
                    'subtotal' => 0,
                ];
            }

            $cant = (int)$r->cantidad;

            $grupos[$gid]['items'][] = [
                'talla_id' => (int)$r->talla_id,
                'talla'    => (string)$r->talla,
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

    public function getTabsEstadoVisiblesProperty(): array
    {
        return collect($this->tabsEstado)
            ->filter(fn ($tab) => $this->canViewTab($tab))
            ->values()
            ->all();
    }

    protected function canViewTab(string $tab): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // Admin ve todos los tabs
        if ($user->hasRole('admin')) {
            return true;
        }

        $permission = $this->tabPermissions[$tab] ?? null;

        // Si no requiere permiso explícito, se permite
        if (!$permission) {
            return true;
        }

        return $user->can($permission);
    }

    public function render()
    {
        $this->loadEstadoTabsCounts();

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
        ])->where('tipo', 'MUESTRA');

        if ($this->filters['inactivos']) {
            $query->where('ind_activo', 0);
        } else {
            $query->where('ind_activo', 1);
        }

        $query
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
            ->when($this->filters['cliente'], function ($q, $v) {
                $v = trim((string) $v);
                $q->whereHas('proyecto.user', fn($u) =>
                    $u->where('name', 'like', "%{$v}%")
                      ->orWhere('email', 'like', "%{$v}%")
                );
            })
            ->when($this->filters['estado_pedido'], fn($q, $v) =>
                $q->where('estatus_muestra', $v)
            )
            ->when($this->activeEstadoTab !== 'TODOS', fn($q) => $q->where('estatus_muestra', $this->activeEstadoTab))
            ->when($this->filters['estado_diseno'], fn($q, $v) =>
                $q->whereHas('proyecto', fn($p) => $p->where('estado', $v))
            )
            ->when($this->filters['fecha_desde'] || $this->filters['fecha_hasta'], function ($q) {
                $desde = $this->filters['fecha_desde'] ? Carbon::parse($this->filters['fecha_desde'])->startOfDay() : null;
                $hasta = $this->filters['fecha_hasta'] ? Carbon::parse($this->filters['fecha_hasta'])->endOfDay() : null;

                if ($desde && $hasta) $q->whereBetween('created_at', [$desde, $hasta]);
                elseif ($desde) $q->where('created_at', '>=', $desde);
                elseif ($hasta) $q->where('created_at', '<=', $hasta);
            });

        if ($user->hasRole('admin') || $user->can('tablaPedidos-ver-todas-las-muestras')) {
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

        $pedidoTable = (new Pedido)->getTable();
        $proyectoTable = (new Proyecto)->getTable();
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

        return view('livewire.dashboard.cliente-panel.muestras', [
            'pedidos' => $query->paginate($this->perPage),
        ]);
    }
}
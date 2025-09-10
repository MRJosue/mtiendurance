<?php

namespace App\Livewire\Produccion;

use App\Models\FiltroProduccion;
use App\Models\HojaFiltroProduccion;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Arr;

class HojaViewer extends Component
{
    use WithPagination;

    /** Recibido desde la vista contenedor */
    public int $hojaId;

    /** Pestaña activa (filtro_id) */
    #[Url(history: true)]
    public ?int $activeFiltroId = null;

    public int $perPage = 15;
    public string $search = '';

    /** Filtros por columna (base) */
    public array $filters = [
        'id'         => null,
        'proyecto'   => '',
        'producto'   => '',
        'estado_id'  => null,
        'total'      => '',
    ];

    /** Filtros por característica (dinámicos del filtro) */
    public array $filtersCar = [];

    /** Nombres de estados para el chip */
    public array $chipEstados = [];

    protected $listeners = [
        'hoja-actualizada' => '$refresh',
        'filtro-produccion-actualizado' => '$refresh',
    ];

    /** Computed: catálogo de estados para el select */
    public function getEstadosProperty()
    {
        // Si tienes un modelo EstadoPedido, puedes usarlo en lugar de DB::
        return DB::table('estados_pedido')
            ->select('id', 'nombre')
            ->orderByRaw('COALESCE(orden, 999999), nombre')
            ->get();
    }

    /** Helper para acceder a la hoja actual */
    public function getHojaProperty(): HojaFiltroProduccion
    {
        $hoja = HojaFiltroProduccion::findOrFail($this->hojaId);

        if (is_string($hoja->estados_permitidos)) {
            $hoja->estados_permitidos = json_decode($hoja->estados_permitidos, true) ?: [];
        }
        if (is_string($hoja->base_columnas)) {
            $hoja->base_columnas = json_decode($hoja->base_columnas, true) ?: [];
        }

        return $hoja;
    }

    public function mount(int $hojaId): void
    {
        $this->hojaId = $hojaId;

        // Prepara chip de estados (IDs -> nombres)
        $ids = is_array($this->hoja->estados_permitidos) ? $this->hoja->estados_permitidos : [];
        $this->chipEstados = empty($ids)
            ? []
            : DB::table('estados_pedido')->whereIn('id', $ids)->pluck('nombre')->all();
    }

    public function updatingActiveFiltroId(): void { $this->resetPage(); }
    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilters(): void { $this->resetPage(); }
    public function updatingFiltersCar(): void { $this->resetPage(); }

    public function render()
    {
        $hoja = $this->hoja;

        // Tabs
        $filtros = $hoja->filtros()->get(['filtros_produccion.id','filtros_produccion.nombre']);
        if (!$this->activeFiltroId && $filtros->isNotEmpty()) {
            $this->activeFiltroId = (int) $filtros->first()->id;
        }

        // Columnas base
        $columnasBase = collect($hoja->base_columnas ?: HojaFiltroProduccion::defaultBaseColumnas())
            ->sortBy('orden')->values();

        // Columnas dinámicas del filtro activo
        $columnasFiltro = collect();
        $productoIds = collect();

        if ($this->activeFiltroId) {
            $filtro = FiltroProduccion::with('caracteristicas')->find($this->activeFiltroId);

            $columnasFiltro = $filtro?->columnas()
                ->filter(fn($c) => $c['visible'] ?? true)
                ->sortBy('orden')
                ->values() ?? collect();

            $productoIds = $filtro?->productoIds() ?? collect();
        }

        // ==============================
        // Query de pedidos + filtros
        // ==============================
        $q = Pedido::query()
            ->with([
                'producto:id,nombre',
                'proyecto:id,nombre',
                'estadoPedido:id,nombre',
            ])
            ->soloPedidos()
            ->when($productoIds->isNotEmpty(), fn($qq) => $qq->whereIn('producto_id', $productoIds))
            ->when(!empty($hoja->estados_permitidos), fn($qq) => $qq->whereIn('estado_id', $hoja->estados_permitidos))
            ->when($this->search, function ($qq) {
                $term = "%{$this->search}%";
                $qq->where(function ($s) use ($term) {
                    $s->whereHas('proyecto', fn($q) => $q->where('nombre', 'like', $term))
                      ->orWhereHas('producto', fn($q) => $q->where('nombre', 'like', $term))
                      ->orWhereHas('estadoPedido', fn($q) => $q->where('nombre', 'like', $term));
                });
            })
            // ---- filtros por columna base ----
            ->when(Arr::get($this->filters, 'id'), fn($qq, $id) => $qq->where('id', (int)$id))
            ->when(
                ($p = trim((string)Arr::get($this->filters, 'proyecto', ''))) !== '',
                fn($qq) => $qq->whereHas('proyecto', fn($q) => $q->where('nombre', 'like', "%{$this->filters['proyecto']}%"))
            )
            ->when(
                ($p = trim((string)Arr::get($this->filters, 'producto', ''))) !== '',
                fn($qq) => $qq->whereHas('producto', fn($q) => $q->where('nombre', 'like', "%{$this->filters['producto']}%"))
            )
            ->when(Arr::get($this->filters, 'estado_id'), fn($qq, $eid) => $qq->where('estado_id', (int)$eid))
            ->when(
                ($t = trim((string)Arr::get($this->filters, 'total', ''))) !== '',
                fn($qq) => $qq->where('total', $t) // ajusta a >= si lo prefieres
            );

        // (Opcional) aplicar filtersCar aquí si quieres filtrar por características específicas
        // ...

        if (!empty($this->filtersCar)) {
                foreach ($this->filtersCar as $carId => $val) {
                        $val = trim((string)$val);
                        if ($val !== '') {
                            $like = '%'.$val.'%';
                            $q->whereExists(function ($sub) use ($carId, $like) {
                                $sub->from('pedido_opciones as po')
                                    ->join('caracteristica_opcion as co', 'co.opcion_id', '=', 'po.opcion_id')
                                    ->join('opciones as o', 'o.id', '=', 'po.opcion_id')
                                    ->whereColumn('po.pedido_id', 'pedido.id')
                                    ->where('co.caracteristica_id', (int)$carId)
                                    ->where('o.nombre', 'like', $like);
                            });
                        }
                    }
                }

        $pedidos = $q->latest('id')->paginate($this->perPage);

        // Precalcular valores de características del filtro
        $valoresPorPedidoYCar = [];
        if ($pedidos->count() > 0 && $columnasFiltro->count() > 0) {
            $pedidoIds = $pedidos->pluck('id')->all();
            $carIds    = $columnasFiltro->pluck('id')->all();

            $rows = DB::table('pedido_opciones as po')
                ->join('caracteristica_opcion as co', 'co.opcion_id', '=', 'po.opcion_id')
                ->join('opciones as o', 'o.id', '=', 'po.opcion_id')
                ->whereIn('po.pedido_id', $pedidoIds)
                ->whereIn('co.caracteristica_id', $carIds)
                ->get(['po.pedido_id', 'co.caracteristica_id', 'o.nombre']);

            foreach ($rows as $r) {
                $valoresPorPedidoYCar[$r->pedido_id][$r->caracteristica_id][] = $r->nombre;
            }
        }

        return view('livewire.produccion.hoja-viewer', [
            'hoja'                 => $hoja,
            'filtros'              => $filtros,
            'baseCols'             => $columnasBase,
            'columnasFiltro'       => $columnasFiltro,
            'pedidos'              => $pedidos,
            'valoresPorPedidoYCar' => $valoresPorPedidoYCar,
            'chipEstados'          => $this->chipEstados,
        ]);
    }
}

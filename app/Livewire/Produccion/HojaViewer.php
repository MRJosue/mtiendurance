<?php

namespace App\Livewire\Produccion;

use App\Models\FiltroProduccion;
use App\Models\HojaFiltroProduccion;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Proyecto;
use Illuminate\Support\Arr;
use Carbon\Carbon;


class HojaViewer extends Component
{
    use WithPagination;

    /** Recibido desde la vista contenedor */
    public int $hojaId;

    /** Pestaña activa (filtro_id) */
    #[Url(history: true)]

    public ?int $activeFiltroId = null;
    /** Tamaño de página (persistido en la URL como ?pp=25) */
    #[Url(as: 'pp', history: true)]
    public int $perPage = 15;
    /** Opciones permitidas para el tamaño de página */
    public array $perPageOptions = [10, 15, 25, 50, 100];


    public string $search = '';

    /** Filtros por columna (base) */
    public array $filters = [
        'id'               => null,
        'proyecto'         => '',
        'producto'         => '',
        'cliente'          => '',
        'estado_id'        => null,
        'estado_disenio'   => '',
        'total'            => '',
        'fecha_produccion_from' => null,
        'fecha_produccion_to'   => null,
        'fecha_embarque_from'   => null,
        'fecha_embarque_to'     => null,
        'fecha_entrega_from'    => null,
        'fecha_entrega_to'      => null,
    ];

    /** Filtros por característica (dinámicos del filtro) */
    public array $filtersCar = [];

    /** Nombres de estados para el chip */
    public array $chipEstados = [];


    public array $estadosDiseno = [];     // catálogo (strings) para selects
    public array $chipEstadosDiseno = []; // para el chip informativo

    /** Ordenamiento */
    public ?string $sortColumn = null;   // ej. 'id','proyecto','producto','cliente','estado','estado_disenio','total','fecha_*'
    public string $sortDirection = 'asc'; // 'asc'|'desc'

    public array $selectedIds = [];

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

    public function getEstadosDisenoProperty()
    {
        return $this->estadosDiseno;
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

        if (is_string($hoja->estados_diseno_permitidos ?? null)) {
            $hoja->estados_diseno_permitidos = json_decode($hoja->estados_diseno_permitidos, true) ?: [];
        }

        return $hoja;
    }



    public function mount(int $hojaId): void
    {
        $this->hojaId = $hojaId;


                // Normaliza perPage si viene “raro” en la URL
        if (!in_array($this->perPage, $this->perPageOptions, true)) {
            $this->perPage = 15;
        }

        $this->estadosDiseno = method_exists(Proyecto::class, 'estadosDiseno')
            ? Proyecto::estadosDiseno()
            : ['PENDIENTE','ASIGNADO','EN PROCESO','REVISION','DISEÑO APROBADO','DISEÑO RECHAZADO','CANCELADO'];

        $ids = is_array($this->hoja->estados_permitidos) ? $this->hoja->estados_permitidos : [];
        $this->chipEstados = empty($ids)
            ? []
            : DB::table('estados_pedido')->whereIn('id', $ids)->pluck('nombre')->all();

        $this->chipEstadosDiseno = is_array($this->hoja->estados_diseno_permitidos) && !empty($this->hoja->estados_diseno_permitidos)
            ? array_values($this->hoja->estados_diseno_permitidos)
            : [];
    }

    public function updatingActiveFiltroId(): void { $this->resetPage(); }
    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilters(): void { $this->resetPage(); }
    public function updatingFiltersCar(): void { $this->resetPage(); }


    /** Toggle orden por columna (una activa a la vez) */
    public function sortBy(string $key): void
    {
        if ($this->sortColumn === $key) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $key;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    /** Reset y normaliza perPage cuando cambie desde el select */
    public function updatingPerPage($value): void
    {
        // Forzamos a una de las opciones válidas
        $value = (int) $value;
        if (!in_array($value, $this->perPageOptions, true)) {
            $this->perPage = 15;
        }
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset([
            'search',
            'filters',
            'filtersCar',
            'sortColumn',
            'sortDirection',
            'selectedIds',
        ]);
        $this->dispatch('toast', message: 'Filtros limpiados', type: 'info');
    }

    public function render()
    {
        $hoja = $this->hoja;

        $filtros = $hoja->filtros()->get(['filtros_produccion.id','filtros_produccion.nombre']);
        if (!$this->activeFiltroId && $filtros->isNotEmpty()) {
            $this->activeFiltroId = (int) $filtros->first()->id;
        }

        $columnasBase = $hoja->columnasBase();

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

        $q = Pedido::query()
            ->with([
                'producto:id,nombre',
                'proyecto:id,nombre,estado',
                'estadoPedido:id,nombre,color',
                'usuario:id,name',
            ])
            ->from('pedido') // aseguramos alias correcto
            ->soloPedidos()
            ->when($productoIds->isNotEmpty(), fn($qq) => $qq->whereIn('producto_id', $productoIds))
            ->when(!empty($hoja->estados_permitidos), fn($qq) => $qq->whereIn('estado_id', $hoja->estados_permitidos))
            ->when(!empty($hoja->estados_diseno_permitidos), function ($qq) use ($hoja) {
                $permitidos = array_map(fn ($s) => $s === 'RECHAZADO' ? 'DISEÑO RECHAZADO' : $s, $hoja->estados_diseno_permitidos);
                $qq->whereHas('proyecto', fn($q) => $q->whereIn('estado', $permitidos));
            })
            ->when($this->search, function ($qq) {
                $term = "%{$this->search}%";
                $qq->where(function ($s) use ($term) {
                    $s->whereHas('proyecto', fn($q) => $q->where('nombre', 'like', $term))
                      ->orWhereHas('producto', fn($q) => $q->where('nombre', 'like', $term))
                      ->orWhereHas('estadoPedido', fn($q) => $q->where('nombre', 'like', $term))
                      ->orWhereHas('usuario',  fn($q) => $q->where('name',   'like', $term));
                });
            })
            // filtros base
            ->when(Arr::get($this->filters, 'id'), fn($qq, $id) => $qq->where('pedido.id', (int)$id))
            ->when(($p = trim((string)Arr::get($this->filters, 'proyecto', ''))) !== '',
                fn($qq) => $qq->whereHas('proyecto', fn($q) => $q->where('nombre', 'like', "%{$this->filters['proyecto']}%")))
            ->when(($p = trim((string)Arr::get($this->filters, 'producto', ''))) !== '',
                fn($qq) => $qq->whereHas('producto', fn($q) => $q->where('nombre', 'like', "%{$this->filters['producto']}%")))
            ->when(($c = trim((string)Arr::get($this->filters, 'cliente', ''))) !== '',
                fn($qq) => $qq->whereHas('usuario', fn($q) => $q->where('name', 'like', "%{$this->filters['cliente']}%")))
            ->when(Arr::get($this->filters, 'estado_id'), fn($qq, $eid) => $qq->where('estado_id', (int)$eid))
            ->when(($ed = trim((string) Arr::get($this->filters, 'estado_disenio', ''))) !== '',
                fn($qq) => $qq->whereHas('proyecto', fn($q) => $q->where('estado', $ed)))
            ->when(($t = trim((string)Arr::get($this->filters, 'total', ''))) !== '',
                fn($qq) => $qq->where('total', $t))

            // fecha_produccion
            ->when(Arr::get($this->filters,'fecha_produccion_from'),
                fn($qq,$d)=>$qq->whereDate('fecha_produccion','>=',$d))
            ->when(Arr::get($this->filters,'fecha_produccion_to'),
                fn($qq,$d)=>$qq->whereDate('fecha_produccion','<=',$d))

            // fecha_embarque
            ->when(Arr::get($this->filters,'fecha_embarque_from'),
                fn($qq,$d)=>$qq->whereDate('fecha_embarque','>=',$d))
            ->when(Arr::get($this->filters,'fecha_embarque_to'),
                fn($qq,$d)=>$qq->whereDate('fecha_embarque','<=',$d))

            // fecha_entrega
            ->when(Arr::get($this->filters,'fecha_entrega_from'),
                fn($qq,$d)=>$qq->whereDate('fecha_entrega','>=',$d))
            ->when(Arr::get($this->filters,'fecha_entrega_to'),
                fn($qq,$d)=>$qq->whereDate('fecha_entrega','<=',$d));

        // Filtros por características
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

        // ---------- ORDENAMIENTO ----------
        $dir = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        if ($this->sortColumn) {
            switch ($this->sortColumn) {
                case 'id':
                    $q->orderBy('pedido.id', $dir);
                    break;
                case 'proyecto':
                        $q->orderBy('pedido.proyecto_id', $dir)
                        ->orderBy('pedido.id', 'desc'); 
                    break;
                case 'producto':
                    $q->leftJoin('productos as pd', 'pd.id', '=', 'pedido.producto_id')
                      ->orderBy('pd.nombre', $dir)
                      ->select('pedido.*');
                    break;
                case 'cliente':
                    $q->leftJoin('users as us', 'us.id', '=', 'pedido.user_id')
                      ->orderBy('us.name', $dir)
                      ->select('pedido.*');
                    break;
                case 'estado':
                    $q->leftJoin('estados_pedido as ep', 'ep.id', '=', 'pedido.estado_id')
                      ->orderBy('ep.nombre', $dir)
                      ->select('pedido.*');
                    break;
                case 'estado_disenio':
                            $q->orderByRaw(
                                "(SELECT pr2.estado FROM proyectos pr2 WHERE pr2.id = pedido.proyecto_id) " . ($dir === 'desc' ? 'DESC' : 'ASC')
                            )->orderBy('pedido.id', 'desc'); // desempate estable
                    break;
                case 'total':
                    $q->orderBy('pedido.total', $dir);
                    break;
                case 'fecha_produccion':
                case 'fecha_embarque':
                case 'fecha_entrega':
                    $q->orderBy('pedido.'.$this->sortColumn, $dir);
                    break;
                default:
                    // fallback: por id desc si la columna no es reconocida
                    $q->orderBy('pedido.id', 'desc');
            }
        } else {
            // default
            $q->orderBy('pedido.id', 'desc');
        }

        // Asegura que perPage sea válido al paginar
        $perPage = in_array($this->perPage, $this->perPageOptions, true) ? $this->perPage : 15;
        $pedidos = $q->paginate($perPage);

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



        public function updateField(int $pedidoId, string $field, $value): void
    {
        $permitidos = ['total','fecha_produccion','fecha_embarque','fecha_entrega','estado_id'];
        if (!in_array($field, $permitidos, true)) return;

        $pedido = \App\Models\Pedido::query()->find($pedidoId);
        if (!$pedido) return;

        switch ($field) {
            case 'total':
                $value = is_numeric($value) ? round((float)$value, 2) : 0.0;
                break;
            case 'fecha_produccion':
            case 'fecha_embarque':
            case 'fecha_entrega':
                $value = $value ? Carbon::parse($value)->toDateString() : null;
                break;
            case 'estado_id':
                $value = (int) $value;
                break;
        }

        $pedido->{$field} = $value;

        // Si cambias estado_id y manejas también columna de apoyo 'estado' (string), sincronízala:
        if ($field === 'estado_id') {
            $nombre = \DB::table('estados_pedido')->where('id', $value)->value('nombre');
            if ($nombre) $pedido->estado = $nombre;
        }

        $pedido->save();

        $this->dispatch('toast', message: 'Guardado', type: 'success');
    }

    /**
     * Acción en grupo: cambiar a CANCELADO/CANCELADA.
     */


    public function cambiarEstadoRechazado(array $ids): void
    {
        if (empty($ids)) return;

        // Forzamos al catálogo conocido: id 7 = RECHAZADO
        \App\Models\Pedido::query()
            ->whereIn('id', $ids)
            ->update([
                'estado_id' => 7,
                'estado'    => 'RECHAZADO',
            ]);

        $this->selectedIds = [];
        $this->dispatch('toast', message: 'Pedidos marcados como RECHAZADO', type: 'success');
    }
}

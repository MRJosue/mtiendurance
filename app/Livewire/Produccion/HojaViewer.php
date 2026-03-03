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
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\HojaPedidosSelectedExport;

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
        'estado_produccion'=> '',
        'total'            => '',
        'fecha_produccion_from' => null,
        'fecha_produccion_to'   => null,
        'fecha_embarque_from'   => null,
        'fecha_embarque_to'     => null,
        'fecha_entrega_from'    => null,
        'fecha_entrega_to'      => null,

        // 👇 NUEVO: campo “proyecto-pedido” (formato 12-345)
        'pp'               => '',
    ];

    public static function accionesDefaults(): array
    {
        return [
            'abrir_chat' => false,
            'crear_tarea' => false,
            'ver_detalle' => true,
            'bulk_aprobar' => false,
            'crear_pedido' => false,
            'editar_tarea' => false,
            'bulk_eliminar' => false,
            'bulk_exportar' => false,
            'editar_pedido' => false,
            'aprobar_diseno' => false,
            'aprobar_pedido' => false,
            'bulk_programar' => false,
            'eliminar_tarea' => false,
            'exportar_excel' => false,
            'subir_archivos' => false,
            'bulk_edit_total' => false,
            'cancelar_pedido' => false,
            'duplicar_pedido' => false,
            'eliminar_pedido' => false,
            'entregar_pedido' => false,
            'rechazar_diseno' => false,
            'bulk_edit_estado_produccion' => false,
            'bulk_edit_estado' => false,
            'programar_pedido' => false,
            'seleccion_multiple' => true,
            'bulk_edit_fecha_entrega' => false,
            'bulk_edit_fecha_embarque' => false,
            'bulk_edit_fecha_produccion' => false,
        ];
    }

    /** Filtros por característica (dinámicos del filtro) */
    public array $filtersCar = [];

    /** Nombres de estados para el chip */
    public array $chipEstados = [];


    public array $allEstadosDiseno = [];

    public array $chipEstadosDiseno = []; // para el chip informativo

    public array $chipEstadosProduccion = [];

    /** Ordenamiento */
    public ?string $sortColumn = null;   // ej. 'id','proyecto','producto','cliente','estado','estado_disenio','total','fecha_*'
    public string $sortDirection = 'asc'; // 'asc'|'desc'

    public array $selectedIds = [];

    protected array $estadoIdCache = [];

    protected $listeners = [
        'hoja-actualizada' => '$refresh',
        'filtro-produccion-actualizado' => '$refresh',
    ];

    public array $idsPagina   = []; // <- nuevos IDs de la página actual



    // Datos de modales para cambio de estado 

    public bool $showProduccionModal = false;
    public ?int $pedidoProduccionId = null;

    public ?string $prodCurrent = null;
    public ?string $prodNext = null;

    public array $prodNextOptions = []; // por si un step tiene varios next
    public array $prodStepMeta = [];    // name/descripcion/grupo...



    // Modales

        // Modal Programar (individual)
        public bool $showProgramarModal = false;
        public ?int $programarPedidoId = null;

        public ?string $programarFechaProduccion = null; // Y-m-d
        public ?string $programarFechaEmbarque   = null; // Y-m-d

// Modal Programar (selección)
public bool $showProgramarSeleccionModal = false;
public array $programarSeleccionIds = [];

public ?string $programarSeleccionFechaProduccion = null; // Y-m-d
public ?string $programarSeleccionFechaEmbarque   = null; // Y-m-d

public ?int $programarSeleccionProductoId = null;
public ?string $programarSeleccionProductoNombre = null;




    // Modal tallas (ver/editar)
    public bool $showTallasModal = false;
    public ?int $tallasPedidoId = null;
    public bool $tallasReadOnly = true;

    public array $tallasLayout = [];   // grupos/tallas
    public array $tallasInputs = [];   // [talla_id => qty]
    public int $tallasTotal = 0;

    // ✅ Modal ver tallas (solo lectura)
    public bool $modal_tallas = false;
    public ?int $tallas_pedido_id = null;

    public array $tallas_grupos = [];
    public int $tallas_total = 0;

    // ✅ Modal edición tallas
    public bool $modal_tallas_edit = false;
    public ?int $tallas_edit_pedido_id = null;

    public array $tallas_disponibles = []; // layout (grupos y tallas)
    public array $inputsTallas = [];       // ["grupoId_tallaId" => cantidad]
    public array $cantidades_tallas = [];  // [grupoId => [tallaId => cantidad]]

    public ?string $error_tallas = null;   // mensaje opcional


    /** Computed: catálogo de estados para el select */
    public function getEstadosProperty()
    {
        // ids permitidos configurados en la hoja (si vienen vacíos => mostrar todos)
        $permitidos = is_array($this->hoja->estados_permitidos)
            ? array_filter($this->hoja->estados_permitidos)
            : [];

        $q = \DB::table('estados_pedido')
            ->select('id', 'nombre')
            ->when(!empty($permitidos), fn($qq) => $qq->whereIn('id', $permitidos))
            ->orderByRaw('COALESCE(orden, 999999), nombre');

        return $q->get();
    }


    protected function estadoId(string $nombre): ?int
    {
        $nombre = trim(mb_strtoupper($nombre));
        if (!isset($this->estadoIdCache[$nombre])) {
            $this->estadoIdCache[$nombre] = (int) DB::table('estados_pedido')
                ->whereRaw('UPPER(nombre) = ?', [$nombre])
                ->value('id');
            if (!$this->estadoIdCache[$nombre]) {
                $this->estadoIdCache[$nombre] = null;
            }
        }
        return $this->estadoIdCache[$nombre];
    }

    public function getEstadosAllProperty()
    {
        // Catálogo completo (sin filtrar por hoja)
        return \DB::table('estados_pedido')
            ->select('id', 'nombre')
            ->orderByRaw('COALESCE(orden, 999999), nombre')
            ->get();
    }

    public function getAllEstadosProduccionProperty(): array
    {
        $db = DB::table('pedido')
            ->whereNotNull('estado_produccion')
            ->where('estado_produccion', '!=', '')
            ->distinct()
            ->orderBy('estado_produccion')
            ->pluck('estado_produccion')
            ->all();

        // Mantén tus “base” al inicio y añade los nuevos de BD
        $base = [
            'POR APROBAR','POR PROGRAMAR','PROGRAMADO','IMPRESIÓN','CORTE','COSTURA',
            'ENTREGA','FACTURACIÓN','COMPLETADO','RECHAZADO'
        ];

        return array_values(array_unique(array_merge($base, $db)));
    }

    public function getEstadosProduccionProperty(): array
    {
        $todos = $this->allEstadosProduccion;

        $permitidos = is_array($this->hoja->estado_produccion_permitidos ?? null)
            ? array_values(array_filter($this->hoja->estado_produccion_permitidos))
            : [];

        // si no hay configurados => todos
        if (empty($permitidos)) return $todos;

        // devuelve solo los permitidos, preservando orden del arreglo configurado
        return array_values(array_intersect($permitidos, $todos));
    }


    public function getAccionesAttribute(): array
    {
        $cfg = $this->acciones_config ?? [];
        if (is_string($cfg)) {
            $cfg = json_decode($cfg, true) ?: [];
        }

        // Normaliza a boolean donde aplique (opcional, pero útil si vienen '1'/'0')
        if (is_array($cfg)) {
            foreach ($cfg as $k => $v) {
                if (is_string($v) && ($v === '0' || $v === '1')) {
                    $cfg[$k] = $v === '1';
                }
            }
        }

        return array_replace(static::accionesDefaults(), is_array($cfg) ? $cfg : []);
    }
    /** (Opcional) Helper de modelo para verificar una acción */
    public function canAccion(string $key): bool
    {
        return (bool) data_get($this->acciones, $key, false);
    }


    public function getEstadosDisenoProperty(): array
    {
        $todos = $this->allEstadosDiseno;

        $permitidos = is_array($this->hoja->estados_diseno_permitidos)
            ? array_values($this->hoja->estados_diseno_permitidos)
            : [];

        // Si no hay configurados en la hoja, regresa todos
        if (empty($permitidos)) {
            return $todos;
        }

        // Devuelve SOLO los configurados, preservando orden y validando que existan en el catálogo base
        return array_values(array_intersect($permitidos, $todos));
    }

    /** Acceso normalizado a acciones_config de la hoja */
    public function getAccionesProperty(): array
    {
        $cfg = $this->hoja->acciones_config ?? [];
        if (is_string($cfg)) {
            $cfg = json_decode($cfg, true) ?: [];
        }
        return array_merge($this->accionesDefaults(), is_array($cfg) ? $cfg : []);
    }

    /** Helper rápido */
    public function can(string $key): bool
    {
        return (bool) Arr::get($this->acciones, $key, false);
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

        if (is_string($hoja->estado_produccion_permitidos ?? null)) {
            $hoja->estado_produccion_permitidos = json_decode($hoja->estado_produccion_permitidos, true) ?: [];
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



        $this->allEstadosDiseno = method_exists(Proyecto::class, 'estadosDiseno')
            ? Proyecto::estadosDiseno()
            : ['PENDIENTE','ASIGNADO','EN PROCESO','REVISION','DISEÑO APROBADO','DISEÑO RECHAZADO','CANCELADO'];

        


        $ids = is_array($this->hoja->estados_permitidos) ? $this->hoja->estados_permitidos : [];
        $this->chipEstados = empty($ids)
            ? []
            : DB::table('estados_pedido')->whereIn('id', $ids)->pluck('nombre')->all();

        $this->chipEstadosDiseno = is_array($this->hoja->estados_diseno_permitidos) && !empty($this->hoja->estados_diseno_permitidos)
            ? array_values($this->hoja->estados_diseno_permitidos)
            : [];

        $this->chipEstadosProduccion = is_array($this->hoja->estado_produccion_permitidos) && !empty($this->hoja->estado_produccion_permitidos)
            ? array_values($this->hoja->estado_produccion_permitidos)
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

         $this->syncChips();

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
    ->from('pedido')
    // JOINs tempranos y reutilizables para filtros/orden
    ->leftJoin('proyectos as pr', 'pr.id', '=', 'pedido.proyecto_id')
    ->leftJoin('productos as pd', 'pd.id', '=', 'pedido.producto_id')
    ->leftJoin('users as us', 'us.id', '=', 'pedido.user_id')
    ->leftJoin('estados_pedido as ep', 'ep.id', '=', 'pedido.estado_id')
    ->select('pedido.*')
    ->with([
        'producto:id,nombre',
        'proyecto:id,nombre,estado',
        'estadoPedido:id,nombre,color',
        'usuario:id,name',
    ])
     ->soloPedidos()

    // filtros por hoja/filtro
    ->when($productoIds->isNotEmpty(), fn($qq) => $qq->whereIn('pedido.producto_id', $productoIds))
    ->when(!empty($hoja->estados_permitidos), fn($qq) => $qq->whereIn('pedido.estado_id', $hoja->estados_permitidos))
    ->when(!empty($hoja->estados_diseno_permitidos), function ($qq) use ($hoja) {
        $permitidos = array_map(fn ($s) => $s === 'RECHAZADO' ? 'DISEÑO RECHAZADO' : $s, $hoja->estados_diseno_permitidos);
        $qq->whereIn('pr.estado', $permitidos);
    })
    ->when(!empty($hoja->estado_produccion_permitidos), function ($qq) use ($hoja) {
        $permitidos = array_values(array_filter($hoja->estado_produccion_permitidos));

        if (!empty($permitidos)) {
            // incluye variantes canonical + legacy sin acento (IMPRESIÓN/IMPRESION)
            $permitidosSQL = [];
            foreach ($permitidos as $p) {
                $permitidosSQL = array_merge($permitidosSQL, $this->estadoProduccionVariants($p));
            }
            $permitidosSQL = array_values(array_unique(array_filter($permitidosSQL)));

            $qq->whereIn('pedido.estado_produccion', $permitidosSQL);
        }
    })
    // búsqueda global (prefijo indexable en columnas unidas)
->when(($term = trim((string)$this->search)) !== '', function ($qq) use ($term) {
    $prefix   = $term.'%';      // empieza con
    $contains = '%'.$term.'%';  // contiene

    $qq->where(function ($s) use ($prefix, $contains, $term) {

        // 1) Texto en columnas de los JOINs (case-insensitive en MySQL por defecto)
        $s->where('pr.nombre', 'like', $prefix)   // Proyecto
          ->orWhere('pd.nombre', 'like', $prefix) // Producto
          ->orWhere('us.name',   'like', $prefix) // Cliente/Usuario
          ->orWhere('ep.nombre', 'like', $prefix);// Estado (nombre del catálogo)

        // 2) Si escriben "12-345", busca proyecto_id=12 y pedido.id=345
        if (preg_match('/^\s*(\d+)\s*-\s*(\d+)\s*$/', $term, $m)) {
            $proyectoId = (int)$m[1];
            $pedidoId   = (int)$m[2];
            $s->orWhere(function ($w) use ($proyectoId, $pedidoId) {
                $w->where('pedido.proyecto_id', $proyectoId)
                  ->where('pedido.id', $pedidoId);
            });
        }

        // 3) Si el término es numérico, permite buscar por id del pedido o del proyecto
        if (ctype_digit($term)) {
            $n = (int)$term;
            $s->orWhere('pedido.id', $n)
              ->orWhere('pedido.proyecto_id', $n);
        }
    });
})  
    

    // filtros base
    ->when(($idRaw = trim((string) Arr::get($this->filters, 'id', ''))) !== '', function ($qq) use ($idRaw) {
        // Formato "proyecto-pedido" (e.g. "12-345")
        if (preg_match('/^\s*(\d+)\s*-\s*(\d+)\s*$/', $idRaw, $m)) {
            $proyectoId = (int) $m[1];
            $pedidoId   = (int) $m[2];
            $qq->where('pedido.proyecto_id', $proyectoId)
            ->where('pedido.id', $pedidoId);
            return;
        }

        // Solo dígitos: buscar por pedido.id O por pedido.proyecto_id
        if (ctype_digit($idRaw)) {
            $n = (int) $idRaw;
            $qq->where(function ($w) use ($n) {
                $w->where('pedido.id', $n)
                ->orWhere('pedido.proyecto_id', $n);
            });
        }
    })
    ->when(($p = trim((string)Arr::get($this->filters, 'proyecto', ''))) !== '',
        fn($qq) => $qq->where('pr.nombre', 'like', $p.'%'))

    ->when(($p = trim((string)Arr::get($this->filters, 'producto', ''))) !== '',
        fn($qq) => $qq->where('pd.nombre', 'like', $p.'%'))

    ->when(($c = trim((string)Arr::get($this->filters, 'cliente', ''))) !== '',
        fn($qq) => $qq->where('us.name', 'like', $c.'%'))

    ->when(Arr::get($this->filters, 'estado_id'),
        fn($qq, $eid) => $qq->where('pedido.estado_id', (int)$eid))

    ->when(($ed = trim((string)Arr::get($this->filters, 'estado_disenio', ''))) !== '',
        fn($qq) => $qq->where('pr.estado', $ed))

    ->when(($sp = trim((string)Arr::get($this->filters, 'estado_produccion', ''))) !== '', function ($qq) use ($sp) {
        $variants = $this->estadoProduccionVariants($sp);
        if (empty($variants)) return;

        $qq->whereIn('pedido.estado_produccion', $variants);
    })

    ->when(($t = trim((string)Arr::get($this->filters, 'total', ''))) !== '',
        fn($qq) => $qq->where('pedido.total', $t))

    // fecha_produccion
    ->when(($fpFrom = Arr::get($this->filters, 'fecha_produccion_from')),
        fn($qq) => $qq->where('pedido.fecha_produccion', '>=', $fpFrom.' 00:00:00'))
    ->when(($fpTo = Arr::get($this->filters, 'fecha_produccion_to')),
        fn($qq) => $qq->where('pedido.fecha_produccion', '<=', $fpTo.' 23:59:59'))

    // fecha_embarque (¡ojo! variables propias)
    ->when(($feFrom = Arr::get($this->filters, 'fecha_embarque_from')),
        fn($qq) => $qq->where('pedido.fecha_embarque', '>=', $feFrom.' 00:00:00'))
    ->when(($feTo = Arr::get($this->filters, 'fecha_embarque_to')),
        fn($qq) => $qq->where('pedido.fecha_embarque', '<=', $feTo.' 23:59:59'))

    // fecha_entrega (¡ojo! variables propias)
    ->when(($fentFrom = Arr::get($this->filters, 'fecha_entrega_from')),
        fn($qq) => $qq->where('pedido.fecha_entrega', '>=', $fentFrom.' 00:00:00'))
    ->when(($fentTo = Arr::get($this->filters, 'fecha_entrega_to')),
        fn($qq) => $qq->where('pedido.fecha_entrega', '<=', $fentTo.' 23:59:59'));

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
                    $q->orderBy('pd.nombre', $dir)->orderBy('pedido.id', 'desc');
            
                    // $q->leftJoin('productos as pd', 'pd.id', '=', 'pedido.producto_id')
                    //   ->orderBy('pd.nombre', $dir)
                    //   ->select('pedido.*');
                    break;
                case 'cliente':
                    $q->orderBy('us.name', $dir)->orderBy('pedido.id', 'desc');
            
                    // $q->leftJoin('users as us', 'us.id', '=', 'pedido.user_id')
                    //   ->orderBy('us.name', $dir)
                    //   ->select('pedido.*');
                    break;
                case 'estado':
                                // Usa el alias ya unido 'ep'
                        $q->orderBy('ep.nombre', $dir)->orderBy('pedido.id', 'desc');
                  
                        // $q->leftJoin('estados_pedido as ep', 'ep.id', '=', 'pedido.estado_id')
                        //   ->orderBy('ep.nombre', $dir)
                        //   ->select('pedido.*');
                    break;
                case 'estado_disenio':
                    $q->orderBy('pr.estado', $dir)->orderBy('pedido.id', 'desc');
                break;
                
                case 'estado_produccion':
                    $q->orderBy('pedido.estado_produccion', $dir)->orderBy('pedido.id', 'desc');
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
        $pedidos = $q->simplePaginate($perPage);  

                // 🔹 NUEVO: recalcula SIEMPRE los IDs de la página visible
        $this->idsPagina = $pedidos
            ->getCollection()               // del LengthAwarePaginator/AbstractPaginator
            ->pluck('id')
            ->map(fn ($i) => (int) $i)      // normaliza a enteros (evita mismatch num/string)
            ->values()
            ->all();
        
        $this->selectedIds = array_values(array_unique(array_map('intval', $this->selectedIds)));


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
            'acciones'             => $this->acciones,
        ]);
    }



        public function updateField(int $pedidoId, string $field, $value): void
    {
        $permitidos = ['total','fecha_produccion','fecha_embarque','fecha_entrega','estado_id','estado_produccion'];
        if (!in_array($field, $permitidos, true)) return;
        
        // Reglas por campo según permisos
        $puedeEditar = $this->can('editar_pedido')
            || ($field === 'total'             && $this->can('bulk_edit_total'))
            || ($field === 'estado_id'         && $this->can('bulk_edit_estado'))
            || ($field === 'estado_produccion' && $this->can('bulk_edit_estado_produccion'))
            || ($field === 'fecha_produccion'  && $this->can('bulk_edit_fecha_produccion'))
            || ($field === 'fecha_embarque'    && $this->can('bulk_edit_fecha_embarque'))
            || ($field === 'fecha_entrega'     && $this->can('bulk_edit_fecha_entrega'));

        if (!$puedeEditar) {
            $this->dispatch('toast', message: 'No tienes permiso para editar este campo', type: 'error');
            return;
        }
        
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
            case 'estado_produccion':
                $value = $this->canonicalEstadoProduccion($value); // <- canonicaliza

                // permite vacío como null
                if ($value === null) {
                    $pedido->{$field} = null;
                    $pedido->save();
                    $this->dispatch('toast', message: 'Guardado', type: 'success');
                    return;
                }

                // valida contra catálogo permitido (tu select ya sale de estadosProduccion)
                if (!in_array($value, $this->estadosProduccion, true)) {
                    $this->dispatch('toast', message: 'Estado de producción inválido', type: 'error');
                    return;
                }
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

        if (!$this->can('bulk_edit_estado')) {
            $this->dispatch('toast', message: 'No tienes permiso para cambiar estado en lote', type: 'error');
            return;
        }

        \App\Models\Pedido::query()
            ->whereIn('id', $ids)
            ->update([
                'estado_id' => 7,
                'estado'    => 'RECHAZADO',
            ]);

        $this->selectedIds = [];
        $this->dispatch('toast', message: 'Pedidos marcados como RECHAZADO', type: 'success');
    }

            // ✅ Aprobar en lote
        public function aprobarSeleccion(array $ids): void
        {
            if (empty($ids)) return;

            if (!$this->can('bulk_aprobar')) {
                $this->dispatch('toast', message: 'No tienes permiso para aprobar en lote', type: 'error');
                return;
            }

            $estadoId = $this->estadoId('APROBADO') ?? null;
            if (!$estadoId) {
                $this->dispatch('toast', message: 'No existe el estado "APROBADO" en el catálogo', type: 'error');
                return;
            }

            \App\Models\Pedido::query()
                ->whereIn('id', $ids)
                ->update([
                    'estado_id' => $estadoId,
                    'estado'    => 'APROBADO',
                ]);

            $this->selectedIds = [];
            $this->dispatch('toast', message: 'Pedidos aprobados', type: 'success');
            $this->resetPage();
        }

        // ✅ Programar en lote
        public function programarSeleccion(array $ids): void
        {
            if (empty($ids)) return;

            if (!$this->can('bulk_programar')) {
                $this->dispatch('toast', message: 'No tienes permiso para programar en lote', type: 'error');
                return;
            }

            $estadoId = $this->estadoId('EN PRODUCCION') ?? null;
            if (!$estadoId) {
                $this->dispatch('toast', message: 'No existe el estado "EN PRODUCCION" en el catálogo', type: 'error');
                return;
            }

            

            \App\Models\Pedido::query()
                ->whereIn('id', $ids)
                ->update([
                    'estado_id' => $estadoId,
                    'estado'    => 'EN PRODUCCION',
                ]);

                

            $this->selectedIds = [];
            $this->dispatch('toast', message: 'Pedidos programados', type: 'success');
            $this->resetPage();
        }

        // ✅ Aprobar individual
        public function aprobarPedido(int $pedidoId): void
        {
            if (!$this->can('aprobar_pedido')) {
                $this->dispatch('toast', message: 'No tienes permiso para aprobar pedidos', type: 'error');
                return;
            }

            $estadoId = $this->estadoId('APROBADO') ?? null;
            if (!$estadoId) {
                $this->dispatch('toast', message: 'No existe el estado "APROBADO" en el catálogo', type: 'error');
                return;
            }

            $pedido = \App\Models\Pedido::query()->find($pedidoId);
            if (!$pedido) return;

            $pedido->estado_id = $estadoId;
            $pedido->estado    = 'APROBADO';
            $pedido->save();

            $this->dispatch('toast', message: "Pedido #{$pedidoId} aprobado", type: 'success');
            $this->resetPage();
        }

        // ✅ Programar individual
        public function programarPedido(int $pedidoId): void
        {
            if (!$this->can('programar_pedido')) {
                $this->dispatch('toast', message: 'No tienes permiso para programar pedidos', type: 'error');
                return;
            }

            $estadoId = $this->estadoId('EN PRODUCCION') ?? null;
            if (!$estadoId) {
                $this->dispatch('toast', message: 'No existe el estado "PROGRAMADO" en el catálogo', type: 'error');
                return;
            }

            $pedido = \App\Models\Pedido::query()->find($pedidoId);
            if (!$pedido) return;

            $pedido->estado_id = $estadoId;
            $pedido->estado    = 'EN PRODUCCION';
            $pedido->save();

            $this->dispatch('toast', message: "Pedido #{$pedidoId} programado", type: 'success');
            $this->resetPage();
        }




            public function toggleSelectAllOnPage(bool $checked): void
            {
                // Normaliza a enteros
                $pagina   = array_map('intval', $this->idsPagina ?? []);
                $selected = array_map('intval', $this->selectedIds ?? []);

                if ($checked) {
                    // Unir y quitar duplicados
                    $this->selectedIds = array_values(array_unique(array_merge($selected, $pagina)));
                } else {
                    // Quitar los de la página actual
                    $this->selectedIds = array_values(array_diff($selected, $pagina));
                }
            }


            public function openProduccionModal(int $pedidoId): void
            {
                $pedido = \App\Models\Pedido::query()
                    ->with(['producto.flujoProduccion'])
                    ->find($pedidoId);

                if (!$pedido) {
                    $this->dispatch('toast', message: 'Pedido no encontrado', type: 'error');
                    return;
                }

                $this->pedidoProduccionId = $pedidoId;

                $flujo = $this->getFlujoForPedido($pedido);
                $steps = $this->stepsFromFlujo($flujo);

                if (empty($steps)) {
                    $this->prodCurrent     = $pedido->estado_produccion ?: '—';
                    $this->prodNextOptions = [];
                    $this->prodNext        = null;
                    $this->prodStepMeta    = [];
                    $this->showProduccionModal = true;

                    $this->dispatch('toast', message: 'Este producto no tiene flujo de producción asignado', type: 'info');
                    return;
                }

                // Estado actual: si está vacío, usa el primer step del flujo
                $actual = trim((string)($pedido->estado_produccion ?? ''));
                if ($actual === '') $actual = (string)($steps[0]['name'] ?? '');

                // Busca step meta
               $step = $this->findStepNormalized($steps, $actual);
                if (!$step) {
                    // Si el estado guardado no existe en el flujo, forzamos al primer paso
                    $actual = (string)($steps[0]['name'] ?? '');
                    $step = $this->findStepNormalized($steps, $actual);
                }

                $this->prodCurrent = $actual;
                $this->prodStepMeta = is_array($step) ? $step : [];

                $nexts = $this->prodStepMeta['next'] ?? [];
                $nexts = is_array($nexts) ? array_values(array_filter($nexts)) : [];

                $this->prodNextOptions = $nexts;
                $this->prodNext        = $nexts[0] ?? null;

                $this->showProduccionModal = true;
            }




            protected function getFlujoForPedido(\App\Models\Pedido $pedido): ?\App\Models\FlujoProduccion
            {
                return $pedido->producto?->flujoProduccion ?? null;
            }

            protected function stepsFromFlujo(?\App\Models\FlujoProduccion $flujo): array
            {
                if (!$flujo) return [];

                $config = $flujo->config;

                if (is_string($config)) {
                    $config = json_decode($config, true) ?: [];
                }

                $steps = $config['steps'] ?? [];
                return is_array($steps) ? $steps : [];
            }

            protected function findStep(array $steps, string $name): ?array
            {
                foreach ($steps as $s) {
                    if (($s['name'] ?? null) === $name) return $s;
                }
                return null;
            }


            public function confirmarSiguienteProduccion(): void
            {

                
                if (!$this->pedidoProduccionId) return;

                $pedido = \App\Models\Pedido::query()
                    ->with(['producto.flujoProduccion'])
                    ->find($this->pedidoProduccionId);

                if (!$pedido) return;

                $flujo = $this->getFlujoForPedido($pedido);
                $steps = $this->stepsFromFlujo($flujo);

                Log::debug('entramos al proceso ', ['steps' => $steps, 'pedido' => $pedido->toArray()]);

                if (empty($steps)) {
                    $this->dispatch('toast', message: 'No hay flujo configurado para este producto.', type: 'error');
                    return;
                }

                Log::debug('ok if steps');
                
                Log::warning('Mismatch estado_produccion vs flujo', [
                    'pedido_id' => $pedido->id,
                    'db' => $pedido->estado_produccion,
                    'steps' => collect($steps)->pluck('name')->all(),
                ]);

                $actual = trim((string)($pedido->estado_produccion ?? ''));
                if ($actual === '') $actual = (string)($steps[0]['name'] ?? '');


                Log::warning('Mismatch estado_produccion vs flujo B', [
                    'pedido_id' => $pedido->id,
                    'db' => $pedido->estado_produccion,
                    'steps' => collect($steps)->pluck('name')->all(),
                ]);

                $step = $this->findStepNormalized($steps, $actual);
                if (!$step) {

                    Log::warning('El estado actual no existe en el flujo', [
                        'pedido_id' => $pedido->id,
                        'db' => $pedido->estado_produccion,
                        'steps' => collect($steps)->pluck('name')->all(),
                    ]);
                    
                    $this->dispatch('toast', message: 'El estado actual no existe en el flujo.', type: 'error');
                    return;
                }

                
                Log::debug('ok if steps 2');

                $nexts = $step['next'] ?? [];
                $nexts = is_array($nexts) ? array_values($nexts) : [];

                if (empty($nexts)) {
                    $this->dispatch('toast', message: 'Este estado no tiene siguiente paso.', type: 'info');
                    return;
                }

                Log::debug('ok if nexts');


                $siguiente = $this->prodNext ?: $nexts[0];

                if (!in_array($siguiente, $nexts, true)) {
                    $this->dispatch('toast', message: 'El siguiente estado no es válido para este flujo.', type: 'error');
                    return;
                }

                

                Log::debug('Datos del usuario procesados:', [
                    'pedido_id' => $pedido->id,
                    'estado_actual' => $actual,
                    'estado_siguiente' => $siguiente,
                ]);

                $pedido->estado_produccion = $siguiente;
                $pedido->save();


                Log::debug('se guarda ', ['pedido_id' => $pedido->id, 'nuevo_estado_produccion' => $pedido->estado_produccion]);

                $this->dispatch('toast', message: "Estado de producción actualizado a {$siguiente}", type: 'success');

                $this->closeProduccionModal();
                $this->resetPage();
            }


            public function closeProduccionModal(): void
            {
                $this->showProduccionModal = false;

                $this->pedidoProduccionId = null;
                $this->prodCurrent = null;
                $this->prodNext = null;
                $this->prodNextOptions = [];
                $this->prodStepMeta = [];
            }


                /** Normaliza string: trim, UPPER, sin acentos */
            protected function normalizeKey(?string $value): string
            {
                $value = trim((string) $value);
                if ($value === '') return '';

                $upper = mb_strtoupper($value, 'UTF-8');

                // quita acentos
                $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $upper);
                $ascii = $ascii !== false ? $ascii : $upper;

                // colapsa espacios
                $ascii = preg_replace('/\s+/', ' ', $ascii);

                return trim($ascii);
            }

            /** Mapa de alias legacy -> canonical (con acentos) */
            protected function canonicalEstadoProduccion(?string $value): ?string
            {
                $v = trim((string)$value);
                if ($v === '') return null;

                $k = $this->normalizeKey($v);

                $map = [
                    'IMPRESION'   => 'IMPRESIÓN',
                    'FACTURACION' => 'FACTURACIÓN',
                    // agrega aquí más alias si aparecen en tu BD/legacy
                ];

                // si ya viene canonical, respétalo
                if (in_array($v, $this->allEstadosProduccion, true)) {
                    return $v;
                }

                return $map[$k] ?? $v;
            }

            /** Devuelve variantes para filtrar en SQL (canonical + legacy sin acento) */
            protected function estadoProduccionVariants(?string $value): array
            {
                $canonical = $this->canonicalEstadoProduccion($value);
                if (!$canonical) return [];

                $variants = [$canonical];

                // legacy sin acento (ej: IMPRESIÓN -> IMPRESION)
                $legacy = $this->normalizeKey($canonical); // ya regresa sin acento y UPPER
                // pero queremos mantenerlo “humano”, no con UPPER raro (ya lo es)
                $variants[] = $legacy;

                return array_values(array_unique(array_filter($variants)));
            }

            /** Comparación de steps ignorando acentos/espacios/case */
            protected function findStepNormalized(array $steps, string $name): ?array
            {
                $target = $this->normalizeKey($name);
                foreach ($steps as $s) {
                    $n = $this->normalizeKey($s['name'] ?? '');
                    if ($n !== '' && $n === $target) return $s;
                }
                return null;
            }


            protected function syncChips(): void
            {
                $hoja = $this->hoja; // <- siempre trae lo último y ya decodificado

                $ids = is_array($hoja->estados_permitidos) ? array_filter($hoja->estados_permitidos) : [];
                $this->chipEstados = empty($ids)
                    ? []
                    : DB::table('estados_pedido')->whereIn('id', $ids)->pluck('nombre')->all();

                $this->chipEstadosDiseno = is_array($hoja->estados_diseno_permitidos ?? null)
                    ? array_values(array_filter($hoja->estados_diseno_permitidos))
                    : [];

                $this->chipEstadosProduccion = is_array($hoja->estado_produccion_permitidos ?? null)
                    ? array_values(array_filter($hoja->estado_produccion_permitidos))
                    : [];
            }



    /** Útil si quieres consultarlo desde Blade (opcional) */
    public function isPageFullySelected(): bool
    {
        $pagina   = array_map('intval', $this->idsPagina ?? []);
        if (empty($pagina)) return false;

        $selected = array_map('intval', $this->selectedIds ?? []);
        // ¿Todos los de la página están en selected?
        return empty(array_diff($pagina, $selected));
    }



    

public function openProgramarModal(int $pedidoId): void
{
    try {
        if (!$this->can('programar_pedido')) {
            $this->dispatch('toast', message: 'No tienes permiso para programar pedidos', type: 'error');
        Log::debug('Validando pedido para programar', ['pedido_id' => $pedidoId]);

            return;
        }


        $pedido = \App\Models\Pedido::query()
            ->with(['proyecto:id,estado'])   // 👈 IMPORTANTÍSIMO
            ->find($pedidoId);

        if (!$pedido) {
            $this->dispatch('toast', message: 'Pedido no encontrado', type: 'error');
        Log::debug('Validando Pedido no encontrado', ['pedido_id' => $pedidoId]);

            return;
        }


        
        if (!$pedido->proyecto) {
            $this->dispatch('toast', message: 'Este pedido no tiene proyecto asociado', type: 'error');
            Log::debug('Validando Este pedido no tiene proyecto asociado', ['pedido_id' => $pedidoId]);

            return;
        }


        // ✅ Validación: solo APROBADO
        $aprobadoId = $this->estadoId('APROBADO');
        if (!$aprobadoId) {
            $this->dispatch('toast', message: 'No existe el estado "APROBADO" en el catálogo', type: 'error');
                    Log::debug('Validando No existe el estado "APROBADO" en el catálogo', ['pedido_id' => $pedidoId]);

            return;
        }
        

        $estadoDiseno = trim((string)($pedido->proyecto->estado ?? ''));
        if ($estadoDiseno !== 'DISEÑO APROBADO') {
            $this->dispatch('toast', message: 'Para programar, el diseño debe estar en "DISEÑO APROBADO"', type: 'error');
                   Log::debug('Para programar, el diseño debe estar en "DISEÑO APROBADO"', ['pedido_id' => $pedidoId]);

            return;
        }

 

        if ((int)$pedido->estado_id !== (int)$aprobadoId) {
            
             Log::debug('Solo puedes programar pedidos en estado APROBADO', ['pedido_id' => $pedidoId]);
            $this->dispatch('toast', message: 'Solo puedes programar pedidos en estado APROBADO', type: 'error');
            return;
        }

        $this->resetValidation();

        $this->programarPedidoId = $pedidoId;
        $this->programarFechaProduccion = $pedido->fecha_produccion?->format('Y-m-d');
        $this->programarFechaEmbarque   = $pedido->fecha_embarque?->format('Y-m-d');

        $this->showProgramarModal = true;

    } catch (\Throwable $e) {
        \Log::error('openProgramarModal error', [
            'pedido_id' => $pedidoId,
            'msg' => $e->getMessage(),
        ]);

        $this->dispatch('toast', message: 'Ocurrió un error al validar el pedido.', type: 'error');
    }
}

    public function closeProgramarModal(): void
    {
        $this->showProgramarModal = false;
        $this->programarPedidoId = null;
        $this->programarFechaProduccion = null;
        $this->programarFechaEmbarque = null;
        $this->resetValidation();
    }

    public function confirmarProgramacion(): void
    {
        if (!$this->programarPedidoId) return;

        if (!$this->can('programar_pedido')) {
            $this->dispatch('toast', message: 'No tienes permiso para programar pedidos', type: 'error');
            return;
        }

        $pedido = \App\Models\Pedido::query()->find($this->programarPedidoId);
        if (!$pedido) {
            $this->dispatch('toast', message: 'Pedido no encontrado', type: 'error');
            return;
        }

        // ✅ Validación: sigue siendo APROBADO al confirmar
        $aprobadoId = $this->estadoId('APROBADO');
        if (!$aprobadoId || (int)$pedido->estado_id !== (int)$aprobadoId) {
            $this->dispatch('toast', message: 'El pedido ya no está en APROBADO, no se puede programar', type: 'error');
            return;
        }

        // ✅ Validación fechas (requeridas por tu requerimiento)
        $this->validate([
            'programarFechaProduccion' => ['required', 'date'],
            'programarFechaEmbarque'   => ['required', 'date'],
        ], [
            'programarFechaProduccion.required' => 'La fecha de producción es obligatoria.',
            'programarFechaEmarque.required'    => 'La fecha de embarque es obligatoria.',
        ]);

        $enProduccionId = $this->estadoId('EN PRODUCCION');
        if (!$enProduccionId) {
            $this->dispatch('toast', message: 'No existe el estado "EN PRODUCCION" en el catálogo', type: 'error');
            return;
        }

        $pedido->fecha_produccion  = Carbon::parse($this->programarFechaProduccion)->toDateString();
        $pedido->fecha_embarque    = Carbon::parse($this->programarFechaEmbarque)->toDateString();

        $pedido->estado_id         = $enProduccionId;
        $pedido->estado            = 'EN PRODUCCION';

        // ✅ estado_produccion a PROGRAMADO
        $pedido->estado_produccion = 'PROGRAMADO';

        $pedido->save();

        $this->dispatch('toast', message: "Pedido #{$pedido->id} programado", type: 'success');

        $this->closeProgramarModal();
        $this->resetPage();
    }



    public function openProgramarSeleccionModal(array $ids): void
{
    $ids = array_values(array_unique(array_map('intval', $ids)));

    if (empty($ids)) {
        $this->dispatch('toast', message: 'Selecciona al menos un pedido.', type: 'info');
        return;
    }

    if (!$this->can('bulk_programar')) {
        $this->dispatch('toast', message: 'No tienes permiso para programar en lote', type: 'error');
        return;
    }

    $aprobadoId = $this->estadoId('APROBADO');
    if (!$aprobadoId) {
        $this->dispatch('toast', message: 'No existe el estado "APROBADO" en el catálogo', type: 'error');
        return;
    }

    $pedidos = \App\Models\Pedido::query()
        ->whereIn('id', $ids)
        ->with([
            'proyecto:id,estado',
            'producto:id,nombre',
        ])
        ->get(['id','estado_id','proyecto_id','producto_id','fecha_produccion','fecha_embarque']);

    if ($pedidos->isEmpty()) {
        $this->dispatch('toast', message: 'Pedidos no encontrados.', type: 'error');
        return;
    }

    // ✅ 1) Validar mismo producto
    $productoId = (int) $pedidos->first()->producto_id;
    $mismatchProducto = $pedidos->firstWhere(fn($p) => (int)$p->producto_id !== $productoId);

    if ($mismatchProducto) {
        $this->dispatch('toast', message: 'Para programar en lote, todos los pedidos deben ser del mismo producto.', type: 'error');
        return;
    }

    // ✅ 2) Validar diseño aprobado
    $badDiseno = $pedidos->firstWhere(function ($p) {
        $estadoDiseno = trim((string)($p->proyecto->estado ?? ''));
        return $estadoDiseno !== 'DISEÑO APROBADO';
    });

    if ($badDiseno) {
        $this->dispatch('toast', message: 'Para programar, todos los pedidos deben tener el proyecto en "DISEÑO APROBADO".', type: 'error');
        return;
    }

    // ✅ 3) Validar estado APROBADO
    $badEstado = $pedidos->firstWhere(fn($p) => (int)$p->estado_id !== (int)$aprobadoId);
    if ($badEstado) {
        $this->dispatch('toast', message: 'Solo puedes programar pedidos en estado APROBADO.', type: 'error');
        return;
    }

    $this->resetValidation();

    $this->programarSeleccionIds = $ids;
    $this->programarSeleccionProductoId = $productoId;
    $this->programarSeleccionProductoNombre = $pedidos->first()->producto->nombre ?? null;

    // si quieres “autollenar” con la primera fecha existente (opcional)
    $first = $pedidos->first();
    $this->programarSeleccionFechaProduccion = $first->fecha_produccion?->format('Y-m-d');
    $this->programarSeleccionFechaEmbarque   = $first->fecha_embarque?->format('Y-m-d');

    $this->showProgramarSeleccionModal = true;
}

public function closeProgramarSeleccionModal(): void
{
    $this->showProgramarSeleccionModal = false;

    $this->programarSeleccionIds = [];
    $this->programarSeleccionProductoId = null;
    $this->programarSeleccionProductoNombre = null;

    $this->programarSeleccionFechaProduccion = null;
    $this->programarSeleccionFechaEmbarque = null;

    $this->resetValidation();
}


public function confirmarProgramacionSeleccion(): void
{
    if (empty($this->programarSeleccionIds)) return;

    if (!$this->can('bulk_programar')) {
        $this->dispatch('toast', message: 'No tienes permiso para programar en lote', type: 'error');
        return;
    }

    $this->validate([
        'programarSeleccionFechaProduccion' => ['required', 'date'],
        'programarSeleccionFechaEmbarque'   => ['required', 'date'],
    ], [
        'programarSeleccionFechaProduccion.required' => 'La fecha de producción es obligatoria.',
        'programarSeleccionFechaEmbarque.required'   => 'La fecha de embarque es obligatoria.',
    ]);

    $aprobadoId = $this->estadoId('APROBADO');
    $enProduccionId = $this->estadoId('EN PRODUCCION');

    if (!$aprobadoId || !$enProduccionId) {
        $this->dispatch('toast', message: 'Faltan estados en catálogo (APROBADO / EN PRODUCCION).', type: 'error');
        return;
    }

    $ids = array_values(array_unique(array_map('intval', $this->programarSeleccionIds)));

    // Revalidación server-side (por seguridad)
    $pedidos = \App\Models\Pedido::query()
        ->whereIn('id', $ids)
        ->with(['proyecto:id,estado'])
        ->get(['id','estado_id','producto_id','proyecto_id']);

    if ($pedidos->isEmpty()) {
        $this->dispatch('toast', message: 'Pedidos no encontrados.', type: 'error');
        return;
    }

    $productoId = (int) ($pedidos->first()->producto_id ?? 0);

    if ($pedidos->contains(fn($p) => (int)$p->producto_id !== $productoId)) {
        $this->dispatch('toast', message: 'Los pedidos ya no son del mismo producto. Vuelve a intentar.', type: 'error');
        return;
    }

    if ($pedidos->contains(fn($p) => (int)$p->estado_id !== (int)$aprobadoId)) {
        $this->dispatch('toast', message: 'Algunos pedidos ya no están en APROBADO.', type: 'error');
        return;
    }

    if ($pedidos->contains(function ($p) {
        return trim((string)($p->proyecto->estado ?? '')) !== 'DISEÑO APROBADO';
    })) {
        $this->dispatch('toast', message: 'Algunos pedidos ya no tienen el diseño aprobado.', type: 'error');
        return;
    }

    $fp = Carbon::parse($this->programarSeleccionFechaProduccion)->toDateString();
    $fe = Carbon::parse($this->programarSeleccionFechaEmbarque)->toDateString();

    \App\Models\Pedido::query()
        ->whereIn('id', $ids)
        ->update([
            'fecha_produccion'  => $fp,
            'fecha_embarque'    => $fe,
            'estado_id'         => $enProduccionId,
            'estado'            => 'EN PRODUCCION',
            'estado_produccion' => 'PROGRAMADO',
        ]);

    $this->selectedIds = []; // limpia selección
    $this->dispatch('toast', message: 'Pedidos programados (misma referencia de producto).', type: 'success');

    $this->closeProgramarSeleccionModal();
    $this->resetPage();
}

#[On('exportar-seleccion')]
public function exportarSeleccion(array $ids)
{
    $ids = array_values(array_unique(array_map('intval', $ids)));

    if (empty($ids)) {
        $this->dispatch('toast', message: 'Selecciona al menos un pedido.', type: 'info');
        return;
    }

    if (!$this->can('bulk_exportar')) {
        $this->dispatch('toast', message: 'No tienes permiso para exportar en lote', type: 'error');
        return;
    }

    $hoja = $this->hoja;

    // Columnas base visibles (las mismas que en tabla)
    $baseCols = collect($hoja->columnasBase() ?? [])
        ->filter(fn($c) => ($c['visible'] ?? true) && (($c['key'] ?? '') !== 'id'))
        ->values()
        ->all();

    // Columnas dinámicas del filtro activo
    $columnasFiltro = collect();
    if ($this->activeFiltroId) {
        $filtro = \App\Models\FiltroProduccion::with('caracteristicas')->find($this->activeFiltroId);
        $columnasFiltro = $filtro?->columnas()
            ->filter(fn($c) => $c['visible'] ?? true)
            ->sortBy('orden')
            ->values() ?? collect();
    }

    $filename = 'hoja_'.$hoja->id.'_seleccionados_'.now()->format('Ymd_His').'.xlsx';

    // (Opcional) limpiar selección al exportar
    // $this->selectedIds = [];
    // $this->dispatch('toast', message: 'Exportación generada', type: 'success');

    return Excel::download(
        new HojaPedidosSelectedExport($ids, $baseCols, $columnasFiltro->values()->all()),
        $filename
    );
}


    public function openTallasModal(int $pedidoId, string $mode = 'view'): void
    {
        $pedido = \App\Models\Pedido::query()
            ->with(['proyecto', 'producto']) // ajusta si ocupas más
            ->find($pedidoId);

        if (!$pedido) {
            $this->dispatch('toast', message: 'Pedido no encontrado', type: 'error');
            return;
        }

        // Ajusta este flag a tu realidad
        $tieneTallas = (bool)($pedido->flag_tallas ?? false);
        if (!$tieneTallas) {
            $this->dispatch('toast', message: 'Este pedido no maneja tallas.', type: 'info');
            return;
        }

        // ✅ Reusa el mismo permiso de edición en línea (ej: bulk_edit_total / editar_pedido)
        $puedeEditarInline = $this->can('editar_pedido') || $this->can('bulk_edit_total');

        if ($mode === 'edit' && !$puedeEditarInline) {
            // si intentan abrir edit sin permiso, lo mandamos a vista o bloqueamos
            $this->dispatch('toast', message: 'No tienes permiso para editar tallas', type: 'error');
            return;
        }

        // ====== Cargar layout + cantidades (conecta a tu modelo real) ======
        $layout  = $this->getLayoutTallasForPedido($pedido);      // <-- tú lo conectas
        $current = $this->getCantidadesTallasForPedido($pedido);  // <-- tú lo conectas

        $inputs = [];
        $total  = 0;

        foreach ($layout as $g) {
            foreach (($g['tallas'] ?? []) as $t) {
                $tid = (int)($t['id'] ?? 0);
                if (!$tid) continue;

                $qty = (int)($current[$tid] ?? 0);
                $inputs[$tid] = $qty;
                $total += $qty;
            }
        }

        $this->resetValidation();

        $this->tallasPedidoId  = $pedidoId;
        $this->tallasReadOnly  = ($mode !== 'edit');
        $this->tallasLayout    = $layout;
        $this->tallasInputs    = $inputs;
        $this->tallasTotal     = $total;

        $this->showTallasModal = true;
    }

    public function closeTallasModal(): void
    {
        $this->showTallasModal = false;
        $this->tallasPedidoId = null;
        $this->tallasReadOnly = true;
        $this->tallasLayout = [];
        $this->tallasInputs = [];
        $this->tallasTotal = 0;
        $this->resetValidation();
    }


public function guardarTallasModal(): void
{
    if (!$this->tallasPedidoId) return;

    if ($this->tallasReadOnly) {
        $this->dispatch('toast', message: 'Solo lectura', type: 'error');
        return;
    }

    // Reusa permiso inline
    $puedeEditarInline = $this->can('editar_pedido') || $this->can('bulk_edit_total');
    if (!$puedeEditarInline) {
        $this->dispatch('toast', message: 'No tienes permiso para guardar tallas', type: 'error');
        return;
    }

    foreach ($this->tallasInputs as $tid => $qty) {
        if (!is_numeric($qty) || (int)$qty < 0) {
            $this->dispatch('toast', message: 'Hay cantidades inválidas', type: 'error');
            return;
        }
    }

    $pedido = \App\Models\Pedido::query()->find($this->tallasPedidoId);
    if (!$pedido) {
        $this->dispatch('toast', message: 'Pedido no encontrado', type: 'error');
        return;
    }

    $this->saveCantidadesTallasForPedido($pedido, $this->tallasInputs); // <-- tú lo conectas
    $this->dispatch('toast', message: 'Tallas guardadas', type: 'success');

    $this->closeTallasModal();
    $this->resetPage();
}


/**
 * Devuelve el layout de tallas agrupadas para renderizar el modal.
 * Usa el accessor $pedido->tallas_agrupadas (ya lo tienes en el modelo Pedido).
 *
 * Formato esperado:
 * [
 *   ['grupo' => 'PLAYERA', 'tallas' => [ ['id'=>1,'nombre'=>'CH'], ... ]],
 *   ...
 * ]
 */
protected function getLayoutTallasForPedido(\App\Models\Pedido $pedido): array
{
    // OJO: asegúrate que el accesor exista como "tallas_agrupadas"
    // y que devuelva un arreglo consistente.
    $layout = $pedido->tallas_agrupadas ?? [];

    // Normaliza por si viene como Collection o string
    if ($layout instanceof \Illuminate\Support\Collection) {
        $layout = $layout->toArray();
    }
    if (is_string($layout)) {
        $layout = json_decode($layout, true) ?: [];
    }

    return is_array($layout) ? $layout : [];
}

/**
 * Devuelve cantidades actuales por talla_id => cantidad.
 * Idealmente sale de la relación/pivote del pedido (o del accessor si ya trae qty).
 *
 * AJUSTA AQUÍ según tu modelo real:
 * - si tu accessor ya trae cantidad por talla, úsalo
 * - si tienes relación, mejor leer de ahí
 */
protected function getCantidadesTallasForPedido(\App\Models\Pedido $pedido): array
{
    return DB::table('pedido_tallas')
        ->where('pedido_id', $pedido->id)
        ->pluck('cantidad', 'talla_id')
        ->map(fn($v) => (int)$v)
        ->all();
}

protected function saveCantidadesTallasForPedido(\App\Models\Pedido $pedido, array $inputs): void
{
    DB::transaction(function () use ($pedido, $inputs) {
        DB::table('pedido_tallas')->where('pedido_id', $pedido->id)->delete();

        $rows = [];
        foreach ($inputs as $tallaId => $qty) {
            $qty = (int)$qty;
            if ($qty <= 0) continue;

            $rows[] = [
                'pedido_id' => $pedido->id,
                'talla_id'  => (int)$tallaId,
                'cantidad'  => $qty,
                'created_at'=> now(),
                'updated_at'=> now(),
            ];
        }

        if (!empty($rows)) {
            DB::table('pedido_tallas')->insert($rows);
        }
    });
}


public function abrirModalTallas(int $pedidoId): void
{
    $pedido = \App\Models\Pedido::query()
        ->select('id', 'flag_tallas')
        ->where('id', $pedidoId)
        ->firstOrFail();

    if ((int)($pedido->flag_tallas ?? 0) !== 1) {
        $this->dispatch('toast', message: 'Este pedido no maneja tallas.', type: 'info');
        return;
    }

    $ptTable = (new \App\Models\PedidoTalla)->getTable();
    $gTable  = (new \App\Models\GrupoTalla)->getTable();
    $tTable  = class_exists(\App\Models\Talla::class) ? (new \App\Models\Talla)->getTable() : 'tallas';

    $rows = DB::table("$ptTable as pt")
        ->join("$gTable as g", 'g.id', '=', 'pt.grupo_talla_id')
        ->join("$tTable as t", 't.id', '=', 'pt.talla_id')
        ->where('pt.pedido_id', $pedidoId)
        ->selectRaw('
            g.id as grupo_id,
            g.nombre as grupo,
            t.id as talla_id,
            t.nombre as talla,
            SUM(COALESCE(pt.cantidad,0)) as cantidad
        ')
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

    $this->tallas_grupos    = array_values($grupos);
    $this->tallas_total     = array_sum(array_column($this->tallas_grupos, 'subtotal'));
    $this->tallas_pedido_id = $pedidoId;

    $this->modal_tallas = true;
}

public function cerrarModalTallas(): void
{
    $this->modal_tallas = false;
    $this->tallas_pedido_id = null;
    $this->tallas_grupos = [];
    $this->tallas_total = 0;
}

public function cargarTallas(int $productoId): void
{
    $this->tallas_disponibles = [];

    $gruposTallas = \App\Models\ProductoGrupoTalla::where('producto_id', $productoId)
        ->pluck('grupo_talla_id');

    if ($gruposTallas->isEmpty()) return;

    $this->tallas_disponibles = \App\Models\GrupoTalla::whereIn('id', $gruposTallas)
        ->with('tallas')
        ->get()
        ->map(function ($grupo) {
            return [
                'id'     => $grupo->id,
                'nombre' => $grupo->nombre,
                'tallas' => $grupo->tallas->map(fn($t) => [
                    'id'     => $t->id,
                    'nombre' => $t->nombre,
                ])->toArray(),
            ];
        })->toArray();
}

public function abrirModalEditarTallas(int $pedidoId): void
{
    $this->resetTallasEdit();

    $pedido = \App\Models\Pedido::query()
        ->with(['pedidoTallas:id,pedido_id,grupo_talla_id,talla_id,cantidad'])
        ->select('id', 'producto_id', 'flag_tallas', 'total')
        ->findOrFail($pedidoId);

    // Validación mínima
    if (empty($pedido->producto_id)) {
        $this->dispatch('toast', message: 'Este pedido no tiene producto asignado.', type: 'error');
        return;
    }

    // Layout por producto
    $this->cargarTallas((int)$pedido->producto_id);

    if (empty($this->tallas_disponibles)) {
        $this->dispatch('toast', message: 'Este producto no maneja tallas (no tiene grupos).', type: 'info');
        return;
    }

    // Cargar cantidades existentes a inputsTallas
    foreach ($pedido->pedidoTallas as $pt) {
        $key = $pt->grupo_talla_id . '_' . $pt->talla_id;
        $this->inputsTallas[$key] = (int)$pt->cantidad;
    }

    $this->tallas_edit_pedido_id = $pedidoId;
    $this->modal_tallas_edit = true;
}
public function cerrarModalEditarTallas(): void
{
    $this->modal_tallas_edit = false;
    $this->resetTallasEdit();
}

private function resetTallasEdit(): void
{
    $this->tallas_edit_pedido_id = null;
    $this->tallas_disponibles = [];
    $this->inputsTallas = [];
    $this->cantidades_tallas = [];
    $this->error_tallas = null;
}

public function recopilarCantidadesTallas(): void
{
    $this->cantidades_tallas = [];

    foreach ($this->inputsTallas as $clave => $cantidad) {
        if (!is_numeric($cantidad) || (int)$cantidad <= 0) continue;

        [$grupoId, $tallaId] = explode('_', (string)$clave);
        $this->cantidades_tallas[(int)$grupoId][(int)$tallaId] = (int)$cantidad;
    }
}


public function guardarTallasEdit(): void
{
    if (!$this->tallas_edit_pedido_id) return;

    $this->recopilarCantidadesTallas();

    // total de tallas
    $totalTallas = 0;
    foreach ($this->cantidades_tallas as $tallas) {
        foreach ($tallas as $cantidad) {
            $totalTallas += (int)$cantidad;
        }
    }

    if ($totalTallas <= 0) {
        $this->error_tallas = 'Debes capturar al menos una talla con cantidad mayor a 0.';
        return;
    }

    DB::transaction(function () use ($totalTallas) {

        $pedido = \App\Models\Pedido::query()
            ->select('id', 'producto_id', 'total')
            ->findOrFail($this->tallas_edit_pedido_id);

        // Limpia anteriores
        \App\Models\PedidoTalla::where('pedido_id', $pedido->id)->delete();

        // Inserta nuevas
        foreach ($this->cantidades_tallas as $grupoId => $tallas) {
            foreach ($tallas as $tallaId => $cantidad) {
                if ((int)$cantidad <= 0) continue;

                \App\Models\PedidoTalla::create([
                    'pedido_id'      => $pedido->id,
                    'grupo_talla_id' => (int)$grupoId,
                    'talla_id'       => (int)$tallaId,
                    'cantidad'       => (int)$cantidad,
                ]);
            }
        }

        // ✅ si el producto tiene grupos, forzamos total = suma tallas
        $pedido->update([
            'total'      => $totalTallas,
            'flag_tallas'=> 1,
        ]);
    });

    $this->dispatch('toast', message: '✅ Tallas guardadas correctamente.', type: 'success');

    // refresca tabla si tienes evento
    $this->dispatch('ActualizarTablaPedido'); // o el evento que use tu viewer
    $this->cerrarModalEditarTallas();
}



}

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
        $permitidos = ['total','fecha_produccion','fecha_embarque','fecha_entrega','estado_id'];
        if (!in_array($field, $permitidos, true)) return;
        
        // Reglas por campo según permisos
        $puedeEditar = $this->can('editar_pedido')
            || ($field === 'total'           && $this->can('bulk_edit_total'))
            || ($field === 'estado_id'       && $this->can('bulk_edit_estado'))
            || ($field === 'fecha_produccion'&& $this->can('bulk_edit_fecha_produccion'))
            || ($field === 'fecha_embarque'  && $this->can('bulk_edit_fecha_embarque'))
            || ($field === 'fecha_entrega'   && $this->can('bulk_edit_fecha_entrega'));

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

    /** Útil si quieres consultarlo desde Blade (opcional) */
    public function isPageFullySelected(): bool
    {
        $pagina   = array_map('intval', $this->idsPagina ?? []);
        if (empty($pagina)) return false;

        $selected = array_map('intval', $this->selectedIds ?? []);
        // ¿Todos los de la página están en selected?
        return empty(array_diff($pagina, $selected));
    }

}

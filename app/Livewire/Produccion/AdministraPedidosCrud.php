<?php

namespace App\Livewire\Produccion;

use Livewire\Component;

use Livewire\WithPagination;
use App\Models\Pedido;
use App\Models\DireccionEntrega;
use App\Models\DireccionFiscal;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\TipoEnvio;
use App\Models\PedidoTalla;
use App\Models\GrupoTalla;
use App\Models\User;
use App\Models\ProductoGrupoTalla;
use App\Models\TareaProduccion;
use App\Models\OrdenProduccion;
use App\Models\OrdenCorte;
use App\Models\FlujoProduccion;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelIgnition\Recorders\LogRecorder\LogMessage;

class AdministraPedidosCrud extends Component
{
    use WithPagination;
    public $modal = false;
    public $pedidoId, $total, $estatus, $tipo, $estado;
    public $fecha_produccion, $fecha_embarque, $fecha_entrega;
    public $direccion_fiscal_id, $direccion_entrega_id, $id_tipo_envio;
    public $direccion_fiscal, $direccion_entrega, $nombre_tipo_envio;
    public $producto_id, $cliente_id;
    public $mensaje_produccion, $error_total;
    public $tallas_disponibles = [];
    public $cantidades_tallas = [];
    public $inputsTallas = [];
    public $mostrar_total = true;
    public $clientes = [], $tipos_envio = [];
    public $direccionesFiscales = [], $direccionesEntrega = [];
    public $productos = [], $categorias = [];
    protected $listeners = ['abrirModalEdicion' => 'abrirModal',
                            'guardarOrden' => 'guardarOrden'];
    public $estado_produccion = 'POR APROBAR';
    public $proyecto_nombre;
    public $producto_nombre;
    public $categoria_nombre;
    public $proyecto_id_pedido;
    public $filtro_usuario = '';
    public $filtro_producto = '';
    public $filtro_categoria = '';
    public $filtro_total = '';
    public $filtro_estado = '';
    public $filtro_estado_produccion = '';
    public $productos_activos;
    public $categorias_activas;
    public $selectedPedidos = [];
    public $selectAll = false;
    public $modal_aprobar_sin_fechas = false;
    public $modalCrearTarea = false;
    public $nuevoTareaPedidoId;
    public $nuevoTareaStaffId;
    public $nuevoTareaTipo = 'INDEFINIDA';
    public $usuarios = [];
    public $nuevoTareaDescripcion = '';
    public $modalCrearTareaConPedidos = false;
    public $modalCrearOrdenCorte = false;
    public $ordenCorte_fecha_inicio;
    public $ordenCorte_total;
    public $ordenCorte_caracteristicas = '';
    public $ordenCorte_tallas = [];
    public $ordenCorte_tallas_json = [];
    public $modalOrdenes = false;
    public $pedidoOrdenes = [];
    
    public $modalCrearOrdenProduccion = false;

    public $ordenProd_fecha_inicio;
    public $ordenProd_tipo = 'CORTE';
    public $ordenProd_usuario_asignado_id = '';


    public $modalCrearOrden = false;
    public $tipo_modal_orden = ''; // 'CORTE', 'SUBLIMADO', etc.

    public $ordenProd_flujo_id = null;
    public $flujosProduccion = [];

    public function mount()
    {
        $this->tipos_envio = TipoEnvio::all();
        $this->clientes = Cliente::all();
        $this->direccionesFiscales = DireccionFiscal::with('ciudad.estado')->get();
        $this->direccionesEntrega = DireccionEntrega::with('ciudad.estado')->get();
        $this->usuarios = User::query()->role('estaf')->get();
        $this->flujosProduccion = FlujoProduccion::all();
        $this->productos_activos = Producto::where('ind_activo', 1)->get();
        $this->categorias_activas = Categoria::where('ind_activo', 1)->get();
    }

    protected function rules()
    {
        return [


            'total' => function ($attribute, $value, $fail) {
                if (empty(array_filter($this->cantidades_tallas)) && empty($value)) {
                    $fail('Debe ingresar un total o capturar cantidades por tallas.');
                }
            },
            'estatus' => 'required|string',
            'tipo' => 'required|in:PEDIDO,MUESTRA',
            'estado' => 'required|in:POR APROBAR,APROBADO,ENTREGADO,RECHAZADO,ARCHIVADO,POR REPROGRAMAR',
            'estado_produccion' => 'required|in:POR APROBAR,POR PROGRAMAR,PROGRAMADO,IMPRESIÓN,CORTE,COSTURA,ENTREGA,FACTURACIÓN,COMPLETADO,RECHAZADO',
            'fecha_produccion' => 'nullable|date',
            'fecha_embarque' => 'nullable|date',
            'fecha_entrega' => 'nullable|date',
          
        ];
    }

    public function abrirModal($pedidoId = null)
    {
        if ($pedidoId) {

          
            $pedido = Pedido::with(['pedidoTallas.talla', 'pedidoTallas.grupoTalla'])->findOrFail($pedidoId);
            $this->cargarInputsDesdePedido($pedido);
            $this->fill([
                'pedidoId' => $pedido->id,
                'total' => $pedido->total,
                'estatus' => $pedido->estatus,
                'tipo' => $pedido->tipo,
                'estado' => $pedido->estado,
                'estado_produccion'=>  $pedido->estado_produccion,
                'fecha_produccion' => $pedido->fecha_produccion,
                'fecha_embarque' => $pedido->fecha_embarque,
                'fecha_entrega' => $pedido->fecha_entrega,
                'direccion_fiscal_id' => $pedido->direccion_fiscal_id,
                'direccion_entrega_id' => $pedido->direccion_entrega_id,
                'id_tipo_envio' => $pedido->id_tipo_envio,
                'cliente_id' => $pedido->cliente_id,
                'producto_id' => $pedido->producto_id,
            ]);


            $this->proyecto_id_pedido = $pedido->proyecto_id;
            $this->proyecto_nombre = $pedido->proyecto->nombre ?? 'Sin proyecto';
            $this->producto_nombre = $pedido->producto->nombre ?? 'Sin producto';
            $this->categoria_nombre = $pedido->producto->categoria->nombre ?? 'Sin categoría';


            $this->cargarTiposEnvio();
            $this->cargarTallas($pedido->producto_id);
            $this->cantidades_tallas = [];


            foreach ($pedido->pedidoTallas as $pt) {
                $this->cantidades_tallas[$pt->grupo_talla_id][$pt->talla_id] = $pt->cantidad;
            }

            

            $this->on_Calcula_Fechas_Entrega();
        } else {
            $this->reset([
                'pedidoId', 'total', 'estatus', 'tipo', 'estado',
                'fecha_produccion', 'fecha_embarque', 'fecha_entrega',
                'direccion_fiscal_id', 'direccion_entrega_id', 'id_tipo_envio',
                'tallas_disponibles', 'cantidades_tallas', 'producto_id', 'cliente_id'
            ]);
            $this->estatus = 'PENDIENTE';
            $this->tipo = 'PEDIDO';
            $this->estado = 'POR APROBAR';
            $this->estado_produccion = $pedido->estado_produccion ?? 'POR APROBAR';
        }

        $this->modal = true;
    }

    public function guardar()
    {
        $this->recopilarCantidadesTallas();
        $this->validate();
        $this->error_total = null;

        $totalTallas = 0;
        foreach ($this->cantidades_tallas as $grupoId => $tallas) {
            foreach ($tallas as $cantidad) {
                $totalTallas += (int) $cantidad;
            }
        }

        if ($totalTallas > 0 && $totalTallas != $this->total) {
            $this->error_total = "El total de las tallas ($totalTallas) no coincide con el total general ($this->total).";
            return;
        }

        $direccionEntrega = DireccionEntrega::find($this->direccion_entrega_id);
        $direccionFiscal = DireccionFiscal::find($this->direccion_fiscal_id);

        $entrega_txt = $direccionEntrega?->ciudad?->nombre.', '.$direccionEntrega?->ciudad?->estado?->nombre.', '.$direccionEntrega?->ciudad?->estado?->pais?->nombre;
        $fiscal_txt = $direccionFiscal?->ciudad?->nombre.', '.$direccionFiscal?->ciudad?->estado?->nombre.', '.$direccionFiscal?->ciudad?->estado?->pais?->nombre;

        $data = [
            'cliente_id' => $this->cliente_id,
            'total' => $this->total,
            'estatus' => $this->estatus,
            'tipo' => $this->tipo,
            'estado' => $this->estado,
            'estado_produccion' => $this->estado_produccion,
            'fecha_produccion' => $this->fecha_produccion,
            'fecha_embarque' => $this->fecha_embarque,
            'fecha_entrega' => $this->fecha_entrega,
            'direccion_fiscal_id' => $this->direccion_fiscal_id,
            'direccion_fiscal' => $fiscal_txt,
            'direccion_entrega_id' => $this->direccion_entrega_id,
            'direccion_entrega' => $entrega_txt,
            'id_tipo_envio' => $this->id_tipo_envio,
        ];

        if ($this->pedidoId) {
            Pedido::find($this->pedidoId)->update($data);
            PedidoTalla::where('pedido_id', $this->pedidoId)->delete();
            $pedido_id = $this->pedidoId;
        } else {
            $nuevo = Pedido::create($data);
            $pedido_id = $nuevo->id;
        }

        foreach ($this->cantidades_tallas as $grupoId => $tallas) {
            foreach ($tallas as $tallaId => $cantidad) {
                if ($cantidad > 0) {
                    PedidoTalla::create([
                        'pedido_id' => $pedido_id,
                        'grupo_talla_id' => $grupoId,
                        'talla_id' => $tallaId,
                        'cantidad' => $cantidad,
                    ]);
                }
            }
        }

        session()->flash('message', 'Pedido guardado correctamente.');
        $this->modal = false;
    }

    public function cargarTallas($productoId)
    {
        $this->tallas_disponibles = [];

        $gruposTallas = ProductoGrupoTalla::where('producto_id', $productoId)->pluck('grupo_talla_id');

        $this->tallas_disponibles = GrupoTalla::whereIn('id', $gruposTallas)->with('tallas')->get()->map(function ($grupo) {
            return [
                'id' => $grupo->id,
                'nombre' => $grupo->nombre,
                'tallas' => $grupo->tallas->map(fn($t) => ['id' => $t->id, 'nombre' => $t->nombre])->toArray(),
            ];
        })->toArray();
    }

    public function updatedCantidadesTallas()
    {
        $this->mostrar_total = !ProductoGrupoTalla::where('producto_id', $this->producto_id)->exists();
    }

    public function cargarTiposEnvio()
    {
        $this->tipos_envio = $this->direccion_entrega_id
            ? DireccionEntrega::find($this->direccion_entrega_id)?->ciudad?->tipoEnvios ?? collect()
            : collect();
    }

    public function on_Calcula_Fechas_Entrega()
    {
        if (!$this->fecha_entrega) return;

        $fecha_entrega = Carbon::parse($this->fecha_entrega);
        $ahora = Carbon::now();

        $dias_produccion = Producto::find($this->producto_id)?->dias_produccion ?? 6;
        $dias_envio = TipoEnvio::find($this->id_tipo_envio)?->dias_envio ?? 2;

        $fecha_embarque = $this->ajustarFechaSinFinesDeSemana($fecha_entrega->copy()->subDays($dias_envio));
        $fecha_produccion = $this->ajustarFechaSinFinesDeSemana($fecha_embarque->copy()->subDays($dias_produccion));

        $this->fecha_embarque = $fecha_embarque->format('Y-m-d');
        $this->fecha_produccion = $fecha_produccion->format('Y-m-d');

        $this->mensaje_produccion = $fecha_produccion->lt($ahora)
            ? '⚠️ La fecha de producción está pasada. Este proyecto requiere autorización adicional para producción.'
            : null;
    }

    public function ajustarFechaSinFinesDeSemana($fecha)
    {
        $fecha = Carbon::parse($fecha);
        if ($fecha->isSaturday()) return $fecha->addDays(2);
        if ($fecha->isSunday()) return $fecha->addDay();
        return $fecha;
    }


    public function recopilarCantidadesTallas()
    {
        $this->cantidades_tallas = []; // Limpiar antes

        foreach ($this->inputsTallas as $clave => $cantidad) {
            if (!is_numeric($cantidad) || (int)$cantidad <= 0) continue;

            [$grupoId, $tallaId] = explode('_', $clave);
            $this->cantidades_tallas[$grupoId][$tallaId] = (int)$cantidad;
        }
    }

    protected function cargarInputsDesdePedido($pedido)
    {
        $this->inputsTallas = [];
    
        foreach ($pedido->pedidoTallas as $pedidoTalla) {
            $clave = $pedidoTalla->grupo_talla_id . '_' . $pedidoTalla->talla_id;
            $this->inputsTallas[$clave] = $pedidoTalla->cantidad;
        }
    }

    public function limpiarFiltros()
    {
        $this->reset([
            'filtro_usuario',
            'filtro_producto',
            'filtro_categoria',
            'filtro_total',
            'filtro_estado',
            'filtro_estado_produccion',
        ]);
    
        $this->resetPage();
    }
    

    public function render()
    {

        $query = Pedido::with([
            'cliente',
            'producto.categoria',
            'tipoEnvio',
            'proyecto.user',
            'tareasProduccion.usuario',
            'pedidoCaracteristicas.caracteristica',
            'pedidoOpciones.opcion.caracteristicas'
        ]);

        if ($this->filtro_usuario) {
            $query->whereHas('proyecto.user', function ($q) {
                $q->where('name', 'like', '%' . $this->filtro_usuario . '%');
            });
        }
        
        if ($this->filtro_producto) {
            $query->where('producto_id', $this->filtro_producto);
        }
        
        if ($this->filtro_categoria) {
            $query->whereHas('producto.categoria', function ($q) {
                $q->where('id', $this->filtro_categoria);
            });
        }
        
        if ($this->filtro_total) {
            $query->where('total', $this->filtro_total);
        }
        
        // if ($this->filtro_estado) {
        //     $query->where('estado', $this->filtro_estado);
        // }

            $query->where('estado', 'APROBADO');
        
        if ($this->filtro_estado_produccion) {
            $query->where('estado_produccion', $this->filtro_estado_produccion);
        }


        $query->where('tipo', 'PEDIDO');

        return view('livewire.produccion.administra-pedidos-crud', [
            
            'pedidos' => $query->orderByDesc(column: 'created_at')->paginate(10),
        ]);
        // return view('livewire.programacion.pedidos-crud-general', [
        //     'pedidos' => $query->orderByDesc('created_at')->paginate(10),
        //     'productos_activos' => Producto::where('ind_activo', 1)->get(),
        //     'categorias_activas' => Categoria::where('ind_activo', 1)->get(),
        // ]);
    }

    public function deleteSelected()
    {
        Pedido::whereIn('id', $this->selectedPedidos)->delete();
        $this->selectedPedidos = [];
        $this->selectAll = false;
        session()->flash('message', 'Pedidos eliminados correctamente.');
    }
    
    public function exportSelected()
    {
        // Implementa la lógica de exportación aquí
        session()->flash('message', 'Exportación completada.');
    }

    public function confirmarAprobarSinFechas($pedidoId)
    {
        $this->pedidoId = $pedidoId;
        $this->modal_aprobar_sin_fechas = true;
    }
    
    public function aprobarSinFechas()
    {
        $pedido = Pedido::findOrFail($this->pedidoId);
        
        Log::debug('Mensaje', ['data' => $this->pedidoId]);

        Log::debug('Pedido encontrado', ['pedido' => $pedido]);

        $pedido->update([
            'flag_aprobar_sin_fechas' => 1,
            'estado' => 'POR APROBAR',
            'estado_produccion' => 'POR PROGRAMAR',
        ]);
        
        Log::debug('LOG update');

        $this->modal_aprobar_sin_fechas = false;
        $this->dispatch('ActualizarTablaPedido');
        session()->flash('message', '✅ Pedido aprobado sin validar fechas.');
    }

    public function aplicarFiltros()
    {
        $this->resetPage();
    }


    public function abrirModalCrearTarea($pedidoId)
    {
        $this->nuevoTareaPedidoId = $pedidoId;
        $this->nuevoTareaTipo = 'INDEFINIDA';
        $this->nuevoTareaStaffId = null;
        $this->modalCrearTarea = true;
    }

    public function guardarTarea()
    {
        $this->validate([
            'nuevoTareaPedidoId' => 'required|exists:pedido,id',
            'nuevoTareaStaffId' => 'required|exists:users,id',
            'nuevoTareaTipo' => 'required|in:DISEÑO,PRODUCCION,CORTE,PINTURA,FACTURACION,INDEFINIDA',
        ]);

         Log::debug('nuevoTareaTipo:', [ $this->nuevoTareaTipo]);
        
        TareaProduccion::create([
            'pedido_id' => $this->nuevoTareaPedidoId,
            'usuario_id'=> $this->nuevoTareaStaffId,
            'crete_user'=> auth()->id(),
            'tipo' => $this->nuevoTareaTipo,
            'estado' => 'PENDIENTE',
            'descripcion' => 'Asignada manualmente desde programación',
        ]);
    
        $this->modalCrearTarea = false;
        session()->flash('message', '✅ Tarea creada correctamente.');
    }


    public function crearTareaConPedidos()
    {
        $this->validate([
          
            'nuevoTareaTipo' => 'required|in:DISEÑO,CORTE,BORDADO,PINTURA,FACTURACION,INDEFINIDA',
            'nuevoTareaStaffId' => 'required|exists:users,id',
            'selectedPedidos' => 'required|array|min:1',
        ]);

        // 1️⃣ Crear la tarea
        $tarea = TareaProduccion::create([
            
            'usuario_id' => $this->nuevoTareaStaffId,
            'crete_user' => auth()->id(),
            'tipo' => $this->nuevoTareaTipo,
            'descripcion' => $this->nuevoTareaDescripcion ?? 'Asignada desde programación',
            'estado' => 'PENDIENTE',
            'fecha_inicio' => now(),
        ]);

        // Añadimos un campo json para tallas 

        // Añadimos un campo de resumen de tallas 

        

        // 2️⃣ Relacionar los pedidos con la tarea (pedido_tarea)
        $tarea->pedidos()->sync($this->selectedPedidos);

        // 3️⃣ Mensaje de confirmación
        session()->flash('message', '✅ Tarea creada y pedidos asignados correctamente.');

        // 4️⃣ Opcional: limpiar selección
        $this->reset(['selectedPedidos',  'nuevoTareaTipo', 'nuevoTareaStaffId', 'nuevoTareaDescripcion']);
        $this->modalCrearTarea = false;
    }


    public function abrirModalCrearOrdenCorte()
    {
        if (empty($this->selectedPedidos)) {
            session()->flash('error', 'Debes seleccionar al menos un pedido para crear una Orden de Corte.');
            return;
        }


       
        $this->reset([
            'ordenCorte_fecha_inicio',
            'ordenCorte_caracteristicas',
            'ordenCorte_tallas',
            'ordenCorte_tallas_json',
        ]);

        // ✅ Cargar tallas de los pedidos seleccionados
        $tallasAgrupadas = [];

        foreach ($this->selectedPedidos as $pedidoId) {
            $pedido = \App\Models\Pedido::with(['pedidoTallas.talla', 'pedidoTallas.grupoTalla'])->find($pedidoId);
        
            if (!$pedido) continue;
        
            foreach ($pedido->tallas_agrupadas as $grupoId => $grupo) {
                $grupoNombre = $grupo['grupo_nombre'];
        
                foreach ($grupo['tallas'] as $talla) {
                    $clave = $grupoNombre . '-' . $talla['nombre'];
        
                    if (!isset($tallasAgrupadas[$clave])) {
                        $tallasAgrupadas[$clave] = [
                            'grupo' => $grupoNombre,
                            'talla' => $talla['nombre'],
                            'cantidad' => 0,
                            'stock' => 0,
                        ];
                    }
        
                    $tallasAgrupadas[$clave]['cantidad'] += $talla['cantidad'];
                }
            }
        }
        
        $this->ordenCorte_tallas_json = $tallasAgrupadas;

        $this->modalCrearOrdenCorte = true;
    }



    public function guardarOrden()
    {
        Log::debug('Iniciamos el proceso guardarOrden');

        if (empty($this->selectedPedidos)) {
            session()->flash('error', 'No hay pedidos seleccionados para generar la orden.');
            return;
        }


        // 🔄 Verificar si hay tallas disponibles
        $tallasAgrupadas = [];

        foreach ($this->selectedPedidos as $pedidoId) {
            $pedido = Pedido::with('pedidoTallas.talla', 'pedidoTallas.grupoTalla')->find($pedidoId);

            if (!$pedido) continue;

            foreach ($pedido->tallas_agrupadas as $grupoId => $grupo) {
                foreach ($grupo['tallas'] as $talla) {
                    $clave = $grupo['grupo_nombre'] . '-' . $talla['nombre'];
                    if (!isset($tallasAgrupadas[$clave])) {
                        $tallasAgrupadas[$clave] = [
                            'grupo' => $grupo['grupo_nombre'],
                            'talla' => $talla['nombre'],
                            'cantidad' => 0,
                            'stock' => 0,
                        ];
                    }
                    $tallasAgrupadas[$clave]['cantidad'] += $talla['cantidad'];
                }
            }
        }

        // 👉 Guardar según presencia de tallas y tipo
        if ($this->tipo_modal_orden === 'CORTE' && count($tallasAgrupadas) > 0) {
            Log::debug('Ejecutamos guardarOrdenCorte');
            $this->ordenCorte_tallas_json = $tallasAgrupadas; // sincronizamos por si no se precargó
            $this->guardarOrdenCorte();
        } else {
            Log::debug('Ejecutamos guardarOrdenProduccion');
            $this->guardarOrdenProduccion();
        }


        
    }


    

    public function abrirModalCrearTareaConPedidos()
    {
        if (empty($this->selectedPedidos)) {
            session()->flash('error', 'Debes seleccionar al menos un pedido.');
            return;
        }
    
        $this->reset([ 'nuevoTareaTipo', 'nuevoTareaStaffId', 'nuevoTareaDescripcion']);
        $this->modalCrearTareaConPedidos = true;
    }

    public function verOrdenesDePedido($pedidoId)
    {
        $pedido = Pedido::find($pedidoId);

        $this->pedidoOrdenes = OrdenProduccion::with(['pedidos', 'ordenCorte'])
            ->whereHas('pedidos', fn($q) => $q->where('pedido.id', $pedidoId))
            ->get()
            ->map(function ($orden) {
                // Duración en formato legible (ej: "2 días 4 horas")
                $inicio = $orden->fecha_en_proceso ? \Carbon\Carbon::parse($orden->fecha_en_proceso) : null;
                $fin = $orden->fecha_terminado ? \Carbon\Carbon::parse($orden->fecha_terminado) : null;
                $duracion = ($inicio && $fin) ? $inicio->diffForHumans($fin, true) : null;
                return [
                    'id'         => $orden->id,
                    'tipo'       => $orden->tipo,
                    'estado'       => $orden->estado,
                    'creado'     => $orden->created_at->format('Y-m-d H:i'),
                    'pedidos'    => $orden->pedidos->map(fn($p) => [
                        'id' => $p->id,
                        'producto' => $p->producto->nombre ?? 'Sin producto',
                    ]),
                    'orden_corte'=> $orden->ordenCorte ? [
                        'fecha_inicio' => $orden->ordenCorte->fecha_inicio?->format('Y-m-d'),
                        'total' => $orden->ordenCorte->total,
                    ] : null,
                    'fecha_sin_iniciar' => $orden->fecha_sin_iniciar,
                    'fecha_en_proceso'  => $orden->fecha_en_proceso,
                    'fecha_terminado'   => $orden->fecha_terminado,
                    'duracion'          => $duracion,
                ];
            })->toArray();

        $this->modalOrdenes = true;
    }


    public function abrirModalCrearOrdenProduccion()
    {
            if (empty($this->selectedPedidos)) {
                session()->flash('error', 'Debes seleccionar al menos un pedido.');
                return;
            }

            $categorias = [];
            $productos = [];

            foreach ($this->selectedPedidos as $pedidoId) {
                $pedido = Pedido::with('producto.categoria')->find($pedidoId);

                if (!$pedido || !$pedido->producto || !$pedido->producto->categoria) {
                    session()->flash('error', "El pedido #{$pedidoId} tiene datos incompletos.");
                    return;
                }

                $categorias[] = $pedido->producto->categoria->id;
                $productos[] = $pedido->producto->id;
            }


            Log::debug('Validar que todos los productos y categorías sean iguales', ['categorias' => $categorias, 'productos' => $productos]);
            // Validar que todos los productos y categorías sean iguales
            if (count(array_unique($categorias)) > 1 || count(array_unique($productos)) > 1) {

                Log::debug('Despliega error');
                session()->flash('error', 'Todos los pedidos seleccionados deben tener el mismo producto y categoría para crear una orden de producción.');
                return;
            }

            // ✅ Si pasa validación, continuar con el proceso
            $this->reset([
                'modalCrearOrden',
                'tipo_modal_orden',
                'ordenCorte_fecha_inicio',
                'ordenCorte_caracteristicas',
                'ordenProd_fecha_inicio',
                'ordenProd_usuario_asignado_id'
            ]);

            $this->modalCrearOrden = true;

            // Precargar tallas para orden de corte
            $this->ordenCorte_tallas_json = [];
            foreach ($this->selectedPedidos as $pedidoId) {
                $pedido = \App\Models\Pedido::with('pedidoTallas.talla', 'pedidoTallas.grupoTalla')->find($pedidoId);
                if (!$pedido) continue;

                foreach ($pedido->tallas_agrupadas as $grupoId => $grupo) {
                    foreach ($grupo['tallas'] as $talla) {
                        $clave = $grupo['grupo_nombre'] . '-' . $talla['nombre'];
                        if (!isset($this->ordenCorte_tallas_json[$clave])) {
                            $this->ordenCorte_tallas_json[$clave] = [
                                'grupo' => $grupo['grupo_nombre'],
                                'talla' => $talla['nombre'],
                                'cantidad' => 0,
                                'stock' => 0,
                            ];
                        }
                        $this->ordenCorte_tallas_json[$clave]['cantidad'] += $talla['cantidad'];
                    }
                }
            }
    }

    /**
     * Guarda la nueva Orden de Producción (sin sub-orden de corte).
    */
    // public function guardarOrdenProduccion()
    // {

    //     Log::debug('Pre validacion guardarOrdenProduccion');

        

    //     $this->validate([
    //         'ordenProd_fecha_inicio' => 'required|date',
    //         'ordenProd_usuario_asignado_id' => 'required|exists:users,id',
    //         'ordenProd_tipo'         => 'required|in:CORTE,SUBLIMADO,COSTURA,MAQUILA,FACTURACION,ENVIO,OTRO,RECHAZADO',
    //         // eliminamos validación de flujo dinámico; usaremos id fijo
    //     ]);

    //     DB::beginTransaction();

    //     try {


    //          Log::debug('try  guardarOrdenProduccion');

    //         // 1️⃣ Crear la orden de producción general, con flujo_id = 1
    //         $orden = OrdenProduccion::create([
    //             'create_user' => auth()->id(),
    //             'tipo'       => $this->ordenProd_tipo,
    //             'fecha_sin_iniciar' => $this->ordenProd_fecha_inicio,
    //             'flujo_id'   => 1, // uso fijo del flujo 1
    //             'assigned_user_id' => $this->ordenProd_usuario_asignado_id,
    //         ]);

    //         // 2️⃣ Relacionar los pedidos seleccionados
    //         foreach ($this->selectedPedidos as $pedidoId) {
    //             DB::table('pedido_orden_produccion')->insert([
    //                 'pedido_id'            => $pedidoId,
    //                 'orden_produccion_id'  => $orden->id,
    //                 'created_at'           => now(),
    //                 'updated_at'           => now(),
    //             ]);
    //         }

    //         // 3️⃣ Inyección de pasos según el JSON del flujo #1
    //         // $steps = data_get($orden->flujo->config, 'steps', []);
    //         // foreach ($steps as $step) {
    //         //     $paso = $orden->ordenPasos()->create([
    //         //         'nombre'         => $step['name'],
    //         //         'grupo_paralelo' => $step['grupo'],
    //         //         'estado'         => $step['grupo'] === 1 ? 'EN_PROCESO' : 'PENDIENTE',
    //         //         'fecha_inicio'   => $step['grupo'] === 1 ? now() : null,
    //         //     ]);

    //         //     // 4️⃣ Crear tarea inicial sólo para el primer grupo
    //         //     if ($step['grupo'] === 1) {
    //         //         $paso->tareasProduccion()->create([
    //         //             'usuario_id'  => auth()->id(),
    //         //             'crete_user'  => auth()->id(),   
    //         //             'descripcion' => "Tarea inicial: {$step['name']}",
    //         //             // estado por defecto PENDIENTE
    //         //         ]);
    //         //     }
    //         // }

    //         DB::commit();

    //         session()->flash('message', '✅ Orden de Producción creada exitosamente.');
    //         $this->dispatch('ActualizarTablaPedido');
    //         $this->reset(['modalCrearOrdenProduccion', 'selectedPedidos', 'ordenProd_tipo']);

    //         $this ->modalCrearOrden = false;
            

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Error al crear Orden de Producción', ['error' => $e->getMessage()]);
    //         session()->flash('error', 'Error al crear la orden de producción.');
    //     }

    //           Log::debug('Fin de guardarOrdenProduccion');
    // }

    public function guardarOrdenProduccion()
    {
        $rules = [
            'ordenProd_fecha_inicio' => 'required|date',
            'ordenProd_usuario_asignado_id' => 'required|exists:users,id',
        ];

        if (!$this->ordenProd_flujo_id) {
            $rules['ordenProd_tipo'] = 'required|string';
        }

        $this->validate($rules);

        DB::beginTransaction();

        try {
            if ($this->ordenProd_flujo_id) {
                $flujo = \App\Models\FlujoProduccion::find($this->ordenProd_flujo_id);
                $steps = data_get($flujo->config, 'steps', []);

                if (empty($steps)) {
                    throw new \Exception("El flujo seleccionado no tiene pasos definidos.");
                }

                foreach ($steps as $step) {
                    $orden = OrdenProduccion::create([
                        'create_user' => auth()->id(),
                        'tipo' => $step['name'], // Tipo igual al paso del flujo
                        'fecha_sin_iniciar' => $this->ordenProd_fecha_inicio,
                        'flujo_id' => $flujo->id,
                        'assigned_user_id' => $this->ordenProd_usuario_asignado_id,
                    ]);

                    foreach ($this->selectedPedidos as $pedidoId) {
                        DB::table('pedido_orden_produccion')->insert([
                            'pedido_id' => $pedidoId,
                            'orden_produccion_id' => $orden->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            } else {
                // Si no hay flujo, crear una sola orden con el tipo seleccionado manualmente
                $orden = OrdenProduccion::create([
                    'create_user' => auth()->id(),
                    'tipo' => $this->ordenProd_tipo,
                    'fecha_sin_iniciar' => $this->ordenProd_fecha_inicio,
                    'flujo_id' => null,
                    'assigned_user_id' => $this->ordenProd_usuario_asignado_id,
                ]);

                foreach ($this->selectedPedidos as $pedidoId) {
                    DB::table('pedido_orden_produccion')->insert([
                        'pedido_id' => $pedidoId,
                        'orden_produccion_id' => $orden->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            session()->flash('message', '✅ Orden(es) de Producción creadas exitosamente.');
            $this->dispatch('ActualizarTablaPedido');
            $this->reset(['modalCrearOrdenProduccion', 'selectedPedidos', 'ordenProd_tipo', 'ordenProd_flujo_id']);
            $this->modalCrearOrdenProduccion = false;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear Orden de Producción', ['error' => $e->getMessage()]);
            session()->flash('error', 'Error al crear la orden de producción: ' . $e->getMessage());
        }
    }



    public function guardarOrdenCorte()
    {
        Log::debug('Pre validacion guardarOrdenCorte');

        $this->validate([
            'ordenProd_fecha_inicio' => 'required|date',
          
        ]);
        
        Log::debug('Pasa validacion');
        DB::beginTransaction();
        try {
            // 1️⃣ Crear la orden de producción general

            Log::debug('Crear la orden de Corte');

            Log::debug('Crear OrdenProduccion');

            $orden = OrdenProduccion::create([
                'create_user' => auth()->id(),
                'tipo'       => $this->ordenProd_tipo,
                'fecha_sin_iniciar' => $this->ordenProd_fecha_inicio,
                'flujo_id'   => 1, // uso fijo del flujo 1
                'assigned_user_id' => $this->ordenProd_usuario_asignado_id,
            ]);

            Log::debug('calculo del total ');

            // calculo del total 
            $totalCorte = collect($this->ordenCorte_tallas_json)->sum(function ($item) {
                return isset($item['cantidad']) ? (int) $item['cantidad'] : 0;
            });


            Log::debug(' OrdenCorte::create');
    
            // 2️⃣ Crear la orden de corte específica
           OrdenCorte::create([
                'orden_produccion_id' => $orden->id,
                'fecha_inicio' => $this->ordenCorte_fecha_inicio,
                
                'total' => $totalCorte,
                'caracteristicas' => $this->ordenCorte_caracteristicas ? json_encode($this->ordenCorte_caracteristicas) : null,
                'tallas' => json_encode($this->ordenCorte_tallas_json),
            ]);
    
            // 3️⃣ Relacionar los pedidos seleccionados
            foreach ($this->selectedPedidos as $pedidoId) {
                \DB::table('pedido_orden_produccion')->insert([
                    'pedido_id' => $pedidoId,
                    'orden_produccion_id' => $orden->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
    
            DB::commit();
    
            session()->flash('message', '✅ Orden de Corte creada exitosamente.');
            $this->reset(['modalCrearOrdenCorte', 'selectedPedidos']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear Orden de Corte', ['error' => $e->getMessage()]);
            session()->flash('error', 'Error al crear la orden de corte.');
        }
    }

    
    public function cancelarOrden($ordenId)
    {
        $orden = OrdenProduccion::find($ordenId);

        if (!$orden) {
            session()->flash('error', 'Orden no encontrada.');
            return;
        }

        $orden->cancelar();

        if ($this->pedidoOrdenes) {
            $this->verOrdenesDePedido($this->pedidoId);
        }

        session()->flash('message', "Orden #{$ordenId} cancelada correctamente.");
    }


}

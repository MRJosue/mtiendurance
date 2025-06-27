<?php

namespace App\Livewire\Pedidos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Pedido;
use App\Models\proyecto;
use App\Models\Producto;
use App\Models\DireccionEntrega;
use App\Models\DireccionFiscal;
use App\Models\Cliente;
use App\Models\Categoria;
use App\Models\Ciudad;
use App\Models\Pais;
use App\Models\Estado;
use App\Models\TipoEnvio;
use App\Models\PedidoCaracteristica;
use App\Models\Caracteristica;
use App\Models\PedidoOpcion;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\PedidoTalla;
use App\Models\GrupoTalla;
use App\Models\ProductoGrupoTalla;


class PedidosCrudProyecto extends Component
{
    use WithPagination;

    public $proyectoId;
    public $modal = false;
    public $modal_confirmar_aprobacion = false;
    public $modal_reconfigurar_proyecto = false;

    public $pedidoId, $total, $estatus, $tipo, $estado, $fecha_produccion, $fecha_embarque, $fecha_entrega;
    public $direccion_fiscal_id;
    public $direccion_fiscal;
    public $direccion_entrega_id;
    public $direccion_entrega;
    public $id_tipo_envio;
  
    public $nombre_tipo_envio;
    public $tipos_envio = [];
    public $mensaje_produccion;

    public $tallas_disponibles = [];
    public $cantidades_tallas = [];
    public $inputsTallas = [];

    public $mostrar_total = true;
    public $producto_id;
    public $error_total;

    public bool $mostrarFiltros  = false;


    public $clientes = []; // Lista de clientes relacionados con el usuario
    public $cliente_id; // Cliente seleccionado en el formulario
    
    protected $listeners = ['abrirModalEdicion' => 'abrirModal',
                            'ActualizarTablaPedido' => 'actualizarTabla',];

    protected function rules()
    {
        return [
            'total' => [
                function ($attribute, $value, $fail) {
                    // Si no hay tallas seleccionadas, el campo total es obligatorio
                    if (empty(array_filter($this->cantidades_tallas)) && empty($value)) {
                        $fail('Debe ingresar un total o capturar cantidades por tallas.');
                    }
                }
            ],
            'estatus' => 'required|string',
            'tipo' => 'required|in:PEDIDO,MUESTRA',
            'estado' => 'required|in:POR APROBAR,APROBADO,ENTREGADO,RECHAZADO,ARCHIVADO',
            'fecha_produccion' => 'nullable|date',
            'fecha_embarque' => 'nullable|date',
            'fecha_entrega' => 'nullable|date',
        ];
    }


    public function abrirModal($pedidoId = null)
    {
        if ($pedidoId) {

            $pedido = Pedido::with(['pedidoTallas.talla', 'pedidoTallas.grupoTalla'])->findOrFail($pedidoId);

            // Limpio el error 
            $this->error_total = null;

            $this->pedidoId = $pedido->id;
            $this->total = $pedido->total;
            $this->estatus = $pedido->estatus;
            $this->tipo = $pedido->tipo;
            $this->estado = $pedido->estado;
            $this->fecha_produccion = $pedido->fecha_produccion;
            $this->fecha_embarque = $pedido->fecha_embarque;
            $this->fecha_entrega = $pedido->fecha_entrega;
            
            // Establecer valores en los selects
            $this->direccion_fiscal_id = $pedido->direccion_fiscal_id;
            $this->direccion_entrega_id = $pedido->direccion_entrega_id;
            $this->id_tipo_envio = $pedido->id_tipo_envio;

            $this->cliente_id = $pedido->cliente_id;

            // Disparar la carga de tipos de envío si hay una dirección de entrega
            if (!empty($this->direccion_entrega_id)) {
                $this->cargarTiposEnvio();
            }

            $this->producto_id = $pedido->producto_id;
            $this->updatedCantidadesTallas();
            
            // Obtener tallas disponibles para el producto
            $this->cargarTallas($pedido->producto_id);

            // Reiniciar el array de cantidades
            $this->cantidades_tallas = [];

            // Cargar cantidades actuales organizadas por grupo de tallas y talla
            foreach ($pedido->pedidoTallas as $pedidoTalla) {
                // $grupoId = $pedidoTalla->grupo_talla_id;
                // $tallaId = $pedidoTalla->talla_id;
                // $this->cantidades_tallas[$grupoId][$tallaId] = $pedidoTalla->cantidad;

                $clave = $pedidoTalla->grupo_talla_id . '_' . $pedidoTalla->talla_id;
                $this->inputsTallas[$clave] = $pedidoTalla->cantidad;
            }


                // Obtener el usuario asociado al proyecto
            $proyecto = Proyecto::find($this->proyectoId);
            if ($proyecto) {
                // Cargar los clientes asociados al usuario del proyecto
                $this->clientes = Cliente::where('usuario_id', $proyecto->usuario_id)->get();
            } else {
                $this->clientes = collect(); // Si no hay proyecto, dejar vacío
            }

            // valiamos fechas solo si es edicion
            $this -> on_Calcula_Fechas_Entrega();

        } else {
            $this->reset([
                'pedidoId', 'total', 'estatus', 'tipo', 'estado',
                'fecha_produccion', 'fecha_embarque', 'fecha_entrega',
                'direccion_fiscal_id', 'direccion_entrega_id', 'id_tipo_envio',
                'tallas_disponibles', 'cantidades_tallas','cliente_id'
            ]);

            // Obtener el producto desde `producto_sel` del proyecto
            $proyecto = Proyecto::findOrFail($this->proyectoId);
            $producto = is_string($proyecto->producto_sel) 
                ? json_decode($proyecto->producto_sel, true) 
                : $proyecto->producto_sel;

            if (isset($producto['id'])) {
                $this->producto_id = $producto['id']; // Guardamos el ID del producto
                $this->cargarTallas($this->producto_id); // Cargar tallas del producto
            } else {
                $this->producto_id = null;
            }

            
                // Obtener el usuario asociado al proyecto
            $proyecto = Proyecto::find($this->proyectoId);
            if ($proyecto) {
                // Cargar los clientes asociados al usuario del proyecto
                $this->clientes = Cliente::where('usuario_id', $proyecto->usuario_id)->get();
            } else {
                $this->clientes = collect(); // Si no hay proyecto, dejar vacío
            }

            $this->estatus = 'PENDIENTE';
            $this->tipo = 'PEDIDO';
            $this->estado = 'POR APROBAR';
        }

        $this->modal = true;
    }
    

    public function guardar()
    {

        $this->recopilarCantidadesTallas();
        Log::debug('Pre validate');
        $this->validate();
        Log::debug('Pre Guardado');

        // Resetear error antes de validar
        $this->error_total = null;

        // Calcular el total de tallas ingresadas
        $totalTallas = 0;
        foreach ($this->cantidades_tallas as $grupoId => $tallas) {
            foreach ($tallas as $tallaId => $cantidad) {
                $totalTallas += (int) $cantidad;
            }
        }

        // Validación: Si hay tallas ingresadas, el total de tallas debe coincidir con el total general
        if ($totalTallas > 0 && $totalTallas != $this->total) {
            Log::debug('Show Error');
            $this->error_total = "El total de las tallas ($totalTallas) no coincide con el total general ($this->total).";
            return;
        }
    
        // Obtener los nombres de país, estado y ciudad para la dirección de entrega
        $direccionEntrega = DireccionEntrega::find($this->direccion_entrega_id);
        $pais_name = $direccionEntrega->ciudad->estado->pais->nombre ?? '';
        $estado_name = $direccionEntrega->ciudad->estado->nombre ?? '';
        $ciudades_name = $direccionEntrega->ciudad->nombre ?? '';
    
        // Obtener los nombres de país, estado y ciudad para la dirección fiscal
        $direccionFiscal = DireccionFiscal::find($this->direccion_fiscal_id);
        $fiscal_pais_name = $direccionFiscal->ciudad->estado->pais->nombre ?? '';
        $fiscal_estado_name = $direccionFiscal->ciudad->estado->nombre ?? '';
        $fiscal_ciudades_name = $direccionFiscal->ciudad->nombre ?? '';
    
        // Construcción de dirección como texto
        $Auxiliar_direccion_entrega = trim("$ciudades_name, $estado_name, $pais_name");
        $Auxiliar_direccion_fiscal = trim("$fiscal_ciudades_name, $fiscal_estado_name, $fiscal_pais_name");
    
        if ($this->pedidoId) {
            // Actualizar un pedido existente
            Pedido::where('id', $this->pedidoId)->update([
                'cliente_id' => $this->cliente_id,
                'total' => $this->total,
                'estatus' => $this->estatus,
                'tipo' => $this->tipo,
                'estado' => $this->estado,
                'fecha_produccion' => $this->fecha_produccion,
                'fecha_embarque' => $this->fecha_embarque,
                'fecha_entrega' => $this->fecha_entrega,
                'direccion_fiscal_id' => $this->direccion_fiscal_id,
                'direccion_fiscal' => $Auxiliar_direccion_fiscal,
                'direccion_entrega_id' => $this->direccion_entrega_id,
                'direccion_entrega' => $Auxiliar_direccion_entrega,
                'id_tipo_envio' => $this->id_tipo_envio,
                
            ]);
    
            // Eliminar tallas anteriores y guardar nuevas
            PedidoTalla::where('pedido_id', $this->pedidoId)->delete();
    
            // Guardar tallas organizadas por grupos
            foreach ($this->cantidades_tallas as $grupoId => $tallas) {
                foreach ($tallas as $tallaId => $cantidad) {
                    if ($cantidad > 0) {
                        PedidoTalla::create([
                            'pedido_id' => $this->pedidoId,
                            'grupo_talla_id' => $grupoId,
                            'talla_id' => $tallaId,
                            'cantidad' => $cantidad,
                        ]);
                    }
                }
            }
        } else {
            Log::debug('Pre crearDesdeProyecto');
            
            // Crear un nuevo pedido
            $nuevoPedido = Pedido::crearDesdeProyecto($this->proyectoId, [
                'cliente_id' => $this->cliente_id,
                'total' => $this->total,
                'estatus' => $this->estatus,
                'tipo' => $this->tipo,
                'estado' => $this->estado,
                'fecha_produccion' => $this->fecha_produccion,
                'fecha_embarque' => $this->fecha_embarque,
                'fecha_entrega' => $this->fecha_entrega,
                'direccion_fiscal_id' => $this->direccion_fiscal_id,
                'direccion_fiscal' => $Auxiliar_direccion_fiscal,
                'direccion_entrega_id' => $this->direccion_entrega_id,
                'direccion_entrega' => $Auxiliar_direccion_entrega,
                'id_tipo_envio' => $this->id_tipo_envio,
            ]);
    
            // Guardar tallas organizadas por grupos
            foreach ($this->cantidades_tallas as $grupoId => $tallas) {
                foreach ($tallas as $tallaId => $cantidad) {
                    if ($cantidad > 0) {
                        PedidoTalla::create([
                            'pedido_id' => $nuevoPedido->id,
                            'grupo_talla_id' => $grupoId,
                            'talla_id' => $tallaId,
                            'cantidad' => $cantidad,
                        ]);
                    }
                }
            }


            $proyecto = Proyecto::find($this->proyectoId);

            $caracteristicas = is_string($proyecto->caracteristicas_sel)
                ? json_decode($proyecto->caracteristicas_sel, true)
                : $proyecto->caracteristicas_sel;
            
            if (!empty($caracteristicas)) {
                foreach ($caracteristicas as $caracteristica) {
                    // Guardar relación con la característica
                   PedidoCaracteristica::create([
                        'pedido_id' => $nuevoPedido->id,
                        'caracteristica_id' => $caracteristica['id'],
                    ]);
            
                    // Guardar opciones si existen
                    if (!empty($caracteristica['opciones'])) {
                        foreach ($caracteristica['opciones'] as $opcion) {
                           PedidoOpcion::create([
                                'pedido_id' => $nuevoPedido->id,
                                'opcion_id' => $opcion['id'],
                                'valor' => $opcion['valoru'] ?? null,
                            ]);
                        }
                    }
                }
            }

        }
    
        session()->flash('message', $this->pedidoId ? 'Pedido actualizado correctamente.' : 'Pedido creado correctamente.');
        $this->modal = false;
    }

    public function cargarTallas($productoId)
    {
        $this->tallas_disponibles = [];

        $gruposTallas = ProductoGrupoTalla::where('producto_id', $productoId)
            ->pluck('grupo_talla_id');
    
        if ($gruposTallas->isNotEmpty()) {
            $this->tallas_disponibles = GrupoTalla::whereIn('id', $gruposTallas)
                ->with('tallas')
                ->get()
                ->map(function ($grupo) {
                    return [
                        'id' => $grupo->id,
                        'nombre' => $grupo->nombre,
                        'tallas' => $grupo->tallas->map(function ($talla) {
                            return [
                                'id' => $talla->id,
                                'nombre' => $talla->nombre
                            ];
                        })->toArray(),
                    ];
                })->toArray();
        }
    }

    public function updatedCantidadesTallas()
    {
        // // Si hay al menos una cantidad en las tallas, ocultar el campo "Total"
        // if (!empty(array_filter($this->cantidades_tallas))) {
        //     $this->mostrar_total = false;
        //     return;
        // }
    
        // Revisar si el producto tiene grupos de tallas asignados
        if (!empty($this->producto_id)) {
            $gruposTallas = ProductoGrupoTalla::where('producto_id', $this->producto_id)->exists();
            $this->mostrar_total = !$gruposTallas;
        } else {
            $this->mostrar_total = true;
        }
    }

    public function cargarTiposEnvio()
    {
        if ($this->direccion_entrega_id) {
            $direccion = DireccionEntrega::find($this->direccion_entrega_id);
            if ($direccion && $direccion->ciudad) {
                $this->tipos_envio = $direccion->ciudad->tipoEnvios()->get();
            } else {
                $this->tipos_envio = [];
            }
        } else {
            $this->tipos_envio = [];
        }
    }

    public function on_Calcula_Fechas_Entrega()
    {
        if ($this->fecha_entrega) {
            // Registrar la fecha ingresada
            Log::debug('Fecha de entrega ingresada', ['fecha_entrega' => $this->fecha_entrega]);
    
            // Convertir la fecha ingresada a un objeto Carbon
            $fecha_entrega = Carbon::parse($this->fecha_entrega);
            $ahora = Carbon::now();
    
            // Definir los días requeridos por defecto
            $dias_produccion_producto = 6;
            $dias_envio = 2;
    
            // Consultar el tipo de envío seleccionado
            if (!empty($this->id_tipo_envio)) {
                $tipoEnvio = TipoEnvio::find($this->id_tipo_envio);
    
                if ($tipoEnvio) {
                    $dias_envio = $tipoEnvio->dias_envio;
                    Log::debug('Días de envío obtenidos de la BD', ['dias_envio' => $dias_envio]);
                } else {
                    Log::warning('No se encontró el tipo de envío en la BD', ['id_tipo_envio' => $this->id_tipo_envio]);
                }
            }
    
            // Consultar el producto seleccionado almacenado en dias_produccion
            if (!empty($this->producto_id)) {
                $producto = Producto::find($this->producto_id);
    
                if ($producto) {
                    $dias_produccion_producto = $producto->dias_produccion;
                    Log::debug('Días de producción obtenidos de la BD', ['dias_produccion' => $dias_produccion_producto]);
                } else {
                    Log::warning('No se encontró el producto en la BD', ['producto_id' => $this->producto_id]);
                }
            }
    
            // Calcular fechas
            $fecha_embarque = $fecha_entrega->copy()->subDays($dias_envio);
            $fecha_produccion = $fecha_embarque->copy()->subDays($dias_produccion_producto);
    
            // Ajustar las fechas para que no caigan en sábado o domingo
            $fecha_embarque = Carbon::parse($this->ajustarFechaSinFinesDeSemana($fecha_embarque));
            $fecha_produccion = Carbon::parse($this->ajustarFechaSinFinesDeSemana($fecha_produccion));
    
            // Guardar las fechas en el formato adecuado para los inputs de tipo "date"
            $this->fecha_produccion = $fecha_produccion->format('Y-m-d'); // Correcto para input date
            $this->fecha_embarque = $fecha_embarque->format('Y-m-d');
    
            // Evaluamos si la fecha de producción está en tiempo de producción
            if ($fecha_produccion->lt($ahora)) {
                $this->mensaje_produccion = '⚠️ La fecha de producción está pasada. Este proyecto requiere autorización adicional para producción.';
                Log::warning('Este proyecto requiere autorización adicional para producción.');
            }else {
                $this->mensaje_produccion =null;
            }
    
            // Log para depuración
            Log::debug('Fechas calculadas', [
                'fecha_produccion' => $this->fecha_produccion,
                'fecha_embarque' => $this->fecha_embarque,
            ]);
        }
    }

    public function validarFechaEntrega()
    {
        if ($this->fecha_entrega) {
            $fecha = Carbon::parse($this->fecha_entrega);
            $diaSemana = $fecha->dayOfWeek; // 0 = Domingo, 6 = Sábado
    
            if ($diaSemana === 6) {
                // Si es sábado, mover al lunes siguiente
                $fecha->addDays(2);
            } elseif ($diaSemana === 0) {
                // Si es domingo, mover al lunes siguiente
                $fecha->addDay();
            }
    
            // Asignar la nueva fecha corregida
            $this->fecha_entrega = $fecha->format('Y-m-d');
    
            $this->on_Calcula_Fechas_Entrega();
        }
    }
    
    public function ajustarFechaSinFinesDeSemana($fecha)
    {
        $fecha = Carbon::parse($fecha);
        $diaSemana = $fecha->dayOfWeek; // 0 = Domingo, 6 = Sábado
    
        if ($diaSemana === 6) {
            // Si es sábado, mover al lunes siguiente
            $fecha->addDays(2);
        } elseif ($diaSemana === 0) {
            // Si es domingo, mover al lunes siguiente
            $fecha->addDay();
        }
    
        return $fecha->format('Y-m-d');
    }

    public function actualizarTabla()
    {
        $this->resetPage(); // Reinicia a la primera página si estás paginando
    }

    public function recopilarCantidadesTallas()
    {
        $this->cantidades_tallas = [];

        foreach ($this->inputsTallas as $clave => $cantidad) {
            if (!is_numeric($cantidad) || (int)$cantidad <= 0) continue;

            [$grupoId, $tallaId] = explode('_', $clave);
            $this->cantidades_tallas[$grupoId][$tallaId] = (int)$cantidad;
        }
    }

    public function confirmarAprobacion($id_pedido)
    {
        $this->pedidoId = $id_pedido;
        $this->modal_confirmar_aprobacion = true;
    }

    public function aprobar_pedido()
    {
        $pedido = Pedido::findOrFail($this->pedidoId);


        if (!$this->validarConfiguracionProyecto()) {
            $this->modal_confirmar_aprobacion = false;
            $this->modal_reconfigurar_proyecto = true;
            return;
        }


        // Validaciones del pedido 
        $ahora = now();
        $fechaProduccion = \Carbon\Carbon::parse($pedido->fecha_produccion);


        // Validar fechas si el flag indica que NO se puede aprobar sin ellas
        if ($pedido->flag_aprobar_sin_fechas == 0) {

            if ($fechaProduccion->lt($ahora)) {
                $this->modal_confirmar_aprobacion = false;
    
                // Redirigir a edición si la fecha es inválida
                $this->dispatch('abrirModalEdicion', pedidoId: $pedido->id);
                return;
            }
        }


        // Validar si la configuracion del proyecto esta coorectamente configurado 
        // Tomamos el proyecto
        // categoria_sel, Producto_sel, caracterisiticas_sel, total_piezas_sel
        
        // tomamos cada id y evaluamos Ind_activo
        // si no hay errores regresar true si hay errores regresa false
        // Si hay error en esta parte cierra el modal de aprobacion 
        // Muestra un nuevo modal donde indicamos que el proyecto necesita reconfiguracion
        // mostrar si decea solicitar la reconfiguracion
        // al seleccionar si. enviar notificacion  

        // Aprobar el pedido
        $pedido->update([
            'estado' => 'APROBADO',
            'estado_produccion' => 'POR PROGRAMAR',
        ]);

        $this->modal_confirmar_aprobacion = false;
        $this->dispatch('ActualizarTablaPedido');
        session()->flash('message', '✅ Pedido aprobado correctamente.');
    }


    public function validarConfiguracionProyecto(): bool
    {
        $proyecto = Proyecto::find($this->proyectoId);

        if (!$proyecto || !$proyecto->producto_sel || !$proyecto->categoria_sel || !$proyecto->caracteristicas_sel || !$proyecto->total_piezas_sel) {
            return false;
        }

        $producto = is_string($proyecto->producto_sel) ? json_decode($proyecto->producto_sel, true) : $proyecto->producto_sel;
        $categoria = is_string($proyecto->categoria_sel) ? json_decode($proyecto->categoria_sel, true) : $proyecto->categoria_sel;
        $caracteristicas = is_string($proyecto->caracteristicas_sel) ? json_decode($proyecto->caracteristicas_sel, true) : $proyecto->caracteristicas_sel;

        if (empty($producto['id']) || empty($categoria['id']) || empty($caracteristicas)) {
            return false;
        }

        if (!Producto::where('id', $producto['id'])->where('ind_activo', 1)->exists()) return false;
        if (!Categoria::where('id', $categoria['id'])->where('ind_activo', 1)->exists()) return false;

        foreach ($caracteristicas as $caracteristica) {
            if (isset($caracteristica['id']) && !Caracteristica::where('id', $caracteristica['id'])->where('ind_activo', 1)->exists()) {
                return false;
            }
        }

        return true;
    }

    public function solicitarReconfiguracion( $id_pedido)
    {
        $proyecto = Proyecto::find($this->proyectoId);
        $this->pedidoId = $id_pedido;
        $pedido = Pedido::findOrFail($this->pedidoId);

        if ($pedido) {
            // Aquí puedes usar una notificación, email, log, o evento personalizado
            Log::warning("🛠 Se ha solicitado reconfiguración para el proyecto ID {$proyecto->id}");
    
            // Ejemplo con una notificación a admin:
            // Notification::route('mail', 'admin@example.com')->notify(new SolicitudReconfiguracion($proyecto));

            $pedido->update([
                'estado' => 'POR REPROGRAMAR',
                'estado_produccion' => 'POR PROGRAMAR',
            ]);


    
            session()->flash('message', '🔧 Se ha solicitado la reconfiguración del proyecto.');

            // Añadir flag_solicitud_reconfiguracion 
            // una vez este este flag en programacion mostrar un boton que al accionar nos envie a la pantalla de reconfiguracion
            // Añadir estatus 'Por Reconfigurar'

        
        }
    
        $this->modal_reconfigurar_proyecto = false;
    }
    
    public function render()
    {
        $proyecto = Proyecto::find($this->proyectoId);
    
        return view('livewire.pedidos.pedidos-crud-proyecto', [
            'tiposEnvio' => TipoEnvio::all(),
            'direccionesFiscales' => DireccionFiscal::where('usuario_id', $proyecto->usuario_id)->get(),
            'direccionesEntrega' => DireccionEntrega::where('usuario_id', $proyecto->usuario_id)->get(),
            'pedidos' => Pedido::where('proyecto_id', $this->proyectoId)
                ->where('tipo', 'PEDIDO')
                ->with([
                    'pedidoTallas.talla' => function ($query) {
                        $query->with('gruposTallas'); // Cargar los grupos de talla correctamente
                    },
                    'tipoEnvio',
                    'usuario'
                ])
                ->paginate(6),
        ]);
    }
}

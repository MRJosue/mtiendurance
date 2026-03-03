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
use App\Models\EstadoPedido;

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

use Illuminate\Support\Facades\DB;



class PedidosCrudProyecto extends Component
{
    use WithPagination;

    public $proyectoId;
    public $modal = false;
    public $modal_confirmar_aprobacion = false;
    public $modal_confirmar_aprobacion_especial = false;
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


    public ?int $estado_id = null;    
    public array $estados = [];      

       
    public array $filters = [
        'inactivos' => false, // false = solo activos, true = solo inactivos
    ];



    // ✅ Modal ver tallas (solo lectura)
    public bool $modal_tallas = false;
    public ?int $tallas_pedido_id = null;

    public array $tallas_grupos = []; // estructura lista para Blade
    public int $tallas_total = 0;


    

    
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
            //'estado' => 'required|in:POR APROBAR,APROBADO,ENTREGADO,RECHAZADO,ARCHIVADO',
            'estado_id' => 'nullable|exists:estados_pedido,id', 
            'fecha_produccion' => 'nullable|date',
            'fecha_embarque' => 'nullable|date',
            'fecha_entrega' => 'nullable|date',
        ];
    }


    public function abrirModal($pedidoId = null)
    {

        if (is_null($pedidoId) && $this->proyectoIncompleto()) {
            $this->modal_reconfigurar_proyecto = true;
            $this->modal = false; // asegurar cerrado el modal de pedido
            return;
        }

            // catálogo (solo activos, ordenados)
            $this->estados = EstadoPedido::where('ind_activo', 1)
            ->orderByRaw('COALESCE(orden, 999999), id')
            ->get(['id', 'nombre', 'color'])
            ->toArray();



        if ($pedidoId) {

        $pedido = Pedido::with(['pedidoTallas.talla','pedidoTallas.grupoTalla','estadoPedido'])->findOrFail($pedidoId);
        // ...
        $this->tipo = $pedido->tipo;

        // ✅ preferimos estado_id; si viene null tratamos de inferirlo por nombre (legacy)
        $this->estado_id = $pedido->estado_id
            ?? EstadoPedido::idPorNombre($pedido->estado ?? '') // puede ser null y está bien
            ?? null;

        // (opcional) mantén el campo legacy en memoria si lo usas en otro lado
        $this->estado = $pedido->estado;

        // ...
        $this->on_Calcula_Fechas_Entrega();




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
                'pedidoId','total','estatus','tipo','estado','estado_id',
                'fecha_produccion','fecha_embarque','fecha_entrega',
                'direccion_fiscal_id','direccion_entrega_id','id_tipo_envio',
                'tallas_disponibles','cantidades_tallas','cliente_id'
            ]);

            // defaults
            $this->estatus = 'PENDIENTE';
            $this->tipo = 'PEDIDO';

            // ✅ default “POR APROBAR” si existe
            $this->estado_id = EstadoPedido::idPorNombre('POR APROBAR');

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


        $this->recopilarCantidadesTallas();
        $this->error_total = null;
        $this->validate();

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

        // ✅ Detectar si el producto requiere tallas (tiene grupos asignados)
        $requiereTallas = false;
        if (!empty($this->producto_id)) {
            $requiereTallas = ProductoGrupoTalla::where('producto_id', $this->producto_id)->exists();
        }

        // ✅ Si requiere tallas, fuerza total = suma tallas
        if ($requiereTallas) {
            $this->total = $totalTallas; // <-- aquí está el fix clave (evita que se vaya 0)
        }


        $flagTallas = (bool) $requiereTallas;

    
        // Obtener los nombres de país, estado y ciudad para la dirección de entrega
        $direccionEntrega = DireccionEntrega::find($this->direccion_entrega_id);
        $pais_name    = $direccionEntrega?->estado?->pais?->nombre ?? '';
        $estado_name  = $direccionEntrega?->estado?->nombre ?? '';
        $ciudades_name= $direccionEntrega?->ciudad ?? '';

        // Obtener los nombres de país, estado y ciudad para la dirección fiscal
        $direccionFiscal = DireccionFiscal::find($this->direccion_fiscal_id);
        $fiscal_pais_name     = $direccionFiscal?->estado?->pais?->nombre ?? '';
        $fiscal_estado_name   = $direccionFiscal?->estado?->nombre ?? '';
        $fiscal_ciudades_name = $direccionFiscal?->ciudad ?? '';
    
        // Construcción de dirección como texto
        $Auxiliar_direccion_entrega = trim("$ciudades_name, $estado_name, $pais_name");
        $Auxiliar_direccion_fiscal = trim("$fiscal_ciudades_name, $fiscal_estado_name, $fiscal_pais_name");

        $estadoNombre = $this->estado_id ? EstadoPedido::find($this->estado_id)?->nombre : null;

    
        if ($this->pedidoId) {
            // Actualizar un pedido existente
            Pedido::where('id', $this->pedidoId)->update([
                'cliente_id' => $this->cliente_id,
                'total' => $this->total,
                 'flag_tallas' => (int) $flagTallas,
                'estatus' => $this->estatus,
                'tipo' => $this->tipo,
                'estado_id' => $this->estado_id,        
                'estado' => $estadoNombre,     
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
                'cantidades_tallas' => $this->cantidades_tallas,
                'flag_tallas' => (int) $flagTallas,
                'estatus' => $this->estatus,
                'tipo' => $this->tipo,
                'estado_id' => $this->estado_id,        
                'estado' => $estadoNombre,              
                'fecha_produccion' => $this->fecha_produccion,
                'fecha_embarque' => $this->fecha_embarque,
                'fecha_entrega' => $this->fecha_entrega,
                'direccion_fiscal_id' => $this->direccion_fiscal_id,
                'direccion_fiscal' => $Auxiliar_direccion_fiscal,
                'direccion_entrega_id' => $this->direccion_entrega_id,
                'direccion_entrega' => $Auxiliar_direccion_entrega,
                'id_tipo_envio' => $this->id_tipo_envio,
            ]);

            LOG::debug('Pedido creado desde proyecto', ['flag_tallas' => $flagTallas]);
    
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

    public function cargarTiposEnvio(): void
    {
        $this->tipos_envio = [];
        // (opcional) no resetees si estás editando y el id sigue siendo válido
        // $this->id_tipo_envio = null;

        if (!$this->direccion_entrega_id) {
            return;
        }

        $direccion = DireccionEntrega::find($this->direccion_entrega_id);
        if (!$direccion) {
            return;
        }

        // 1) estado_id directo en la dirección (recomendado)
        $estadoId = $direccion->estado_id ?? null;

        // 2) fallback: si NO tienes estado_id en dirección, intenta obtenerlo vía ciudad_id
        if (!$estadoId && !empty($direccion->ciudad_id)) {
            $ciudad = \App\Models\Ciudad::find($direccion->ciudad_id);
            $estadoId = $ciudad?->estado_id;
        }

        if (!$estadoId) {
            $this->tipos_envio = [];
            $this->id_tipo_envio = null;
            return;
        }

        $estado = \App\Models\Estado::find($estadoId);
        $this->tipos_envio = $estado
            ? $estado->tipoEnvios()->orderBy('nombre')->get()
            : [];

        // Si solo hay 1, autoselecciona
        if (count($this->tipos_envio) === 1) {
            $this->id_tipo_envio = $this->tipos_envio[0]->id;
        }

        // Si ya había uno seleccionado pero no existe en la nueva lista, lo limpias
        if (!empty($this->id_tipo_envio)) {
            $existe = collect($this->tipos_envio)->pluck('id')->contains((int)$this->id_tipo_envio);
            if (!$existe) {
                $this->id_tipo_envio = null;
            }
        }

        // Recalcular si aplica
        $this->on_Calcula_Fechas_Entrega();
    }

    private function validarFechasParaAprobar(Pedido $pedido): array
    {
        // Devuelve: [ok(bool), msg(string), requiereEdicion(bool)]

        // Si NO se permite aprobar sin fechas, entonces deben existir
        if ((int)$pedido->flag_aprobar_sin_fechas === 0) {

            if (empty($pedido->fecha_entrega) || empty($pedido->fecha_embarque) || empty($pedido->fecha_produccion)) {
                return [false, 'Faltan fechas (producción/embarque/entrega). Debes completar las fechas antes de aprobar.', true];
            }
        } else {
            // Si sí se permite aprobar sin fechas, no bloqueamos por null
            // (pero si existen, validamos consistencia mínima)
            if (empty($pedido->fecha_entrega) && empty($pedido->fecha_embarque) && empty($pedido->fecha_produccion)) {
                return [true, '', false];
            }
        }

        // Si hay fechas, validamos orden y calendario
        $fp = $pedido->fecha_produccion ? Carbon::parse($pedido->fecha_produccion)->startOfDay() : null;
        $fe = $pedido->fecha_embarque   ? Carbon::parse($pedido->fecha_embarque)->startOfDay()   : null;
        $fd = $pedido->fecha_entrega    ? Carbon::parse($pedido->fecha_entrega)->startOfDay()    : null;

        // Orden lógico si existen
        if ($fp && $fe && $fp->gt($fe)) {
            return [false, 'La fecha de producción no puede ser posterior a la fecha de embarque.', true];
        }
        if ($fe && $fd && $fe->gt($fd)) {
            return [false, 'La fecha de embarque no puede ser posterior a la fecha de entrega.', true];
        }
        if ($fp && $fd && $fp->gt($fd)) {
            return [false, 'La fecha de producción no puede ser posterior a la fecha de entrega.', true];
        }

        // Evitar fines de semana (si el usuario editó manual)
        foreach (['producción' => $fp, 'embarque' => $fe, 'entrega' => $fd] as $label => $date) {
            if ($date && ($date->isSaturday() || $date->isSunday())) {
                return [false, "La fecha de {$label} cae en fin de semana. Ajusta a un día hábil.", true];
            }
        }

        // Regla principal: producción no puede estar en el pasado (salvo aprobación especial)
        if ((int)$pedido->flag_aprobar_sin_fechas === 0 && $fp) {
            $hoy = now()->startOfDay();
            if ($fp->lt($hoy)) {
                // Aquí NO aprobamos normal: requiere edición / autorización
                return [false, 'La fecha de producción ya pasó. Requiere autorización o reprogramación.', true];
            }
        }

        return [true, '', false];
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

        // 1) Validar configuración del proyecto
        if (!$this->validarConfiguracionProyecto()) {
            $this->modal_confirmar_aprobacion = false;
            $this->modal_reconfigurar_proyecto = true;
            return;
        }

        // 2) Validar estado de diseño (extra recomendado)
        if (($pedido->proyecto?->estado ?? null) !== 'DISEÑO APROBADO') {
            $this->modal_confirmar_aprobacion = false;
            session()->flash('error', 'No puedes aprobar el pedido si el diseño no está aprobado.');
            return;
        }

        // 3) Validar fechas
        [$ok, $msg, $requiereEdicion] = $this->validarFechasParaAprobar($pedido);

        if (!$ok) {
            $this->modal_confirmar_aprobacion = false;

            // Si requiere edición, abre modal edición
            if ($requiereEdicion) {
                session()->flash('error', $msg);
                $this->dispatch('abrirModalEdicion', pedidoId: $pedido->id);
                return;
            }

            session()->flash('error', $msg);
            return;
        }

        // 4) Aprobar
        // OJO: NO hardcodees estado_id=3, mejor búscalo por nombre si puedes:
        $estadoAprobadoId = \App\Models\EstadoPedido::idPorNombre('APROBADO') ?? 3;

        $pedido->update([
            'estado'            => 'APROBADO',
            'estado_id'         => $estadoAprobadoId,
            'estado_produccion' => 'POR PROGRAMAR',
            'flag_solicitud_aprobar_sin_fechas' => 0, // limpia solicitud si existía
        ]);

        $this->modal_confirmar_aprobacion = false;
        $this->dispatch('ActualizarTablaPedido');
        session()->flash('message', '✅ Pedido aprobado correctamente.');
    }

    public function confirmarAprobacionEspecial($id_pedido)
    {
        $this->pedidoId = $id_pedido;
        $this->modal_confirmar_aprobacion_especial = true;
    }

    public function Crea_Solicitud_Aprobacion_Especial()
    {
        $pedido = Pedido::findOrFail($this->pedidoId);

        if (!$this->validarConfiguracionProyecto()) {
            $this->modal_confirmar_aprobacion_especial = false;
            $this->modal_reconfigurar_proyecto = true;
            return;
        }

        // Si ya es aprobable normal, avisa y no marques solicitud
        [$ok, $msg] = $this->validarFechasParaAprobar($pedido);

        if ($ok) {
            $this->modal_confirmar_aprobacion_especial = false;
            session()->flash('message', 'Este pedido puede aprobarse de forma normal.');
            return;
        }

        // Marca solicitud especial
        $pedido->update([
            'flag_solicitud_aprobar_sin_fechas' => 1,
        ]);

        $this->modal_confirmar_aprobacion_especial = false;
        $this->dispatch('ActualizarTablaPedido');
        session()->flash('message', '✅ Solicitud de aprobación especial enviada correctamente.');
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
  

    public function solicitarReconfiguracion(?int $id_pedido = null)
    {
        $proyecto = \App\Models\Proyecto::find($this->proyectoId);
        if (!$proyecto) {
            session()->flash('error', 'Proyecto no encontrado.');
            $this->modal_reconfigurar_proyecto = false;
            return;
        }

        // Si viene un pedido (cuando NO es “nuevo”), puedes marcarlo POR REPROGRAMAR
        if (!is_null($id_pedido)) {
            if ($pedido = \App\Models\Pedido::find($id_pedido)) {
                $pedido->update([
                    'estado' => 'POR REPROGRAMAR',
                    'estado_produccion' => 'POR PROGRAMAR',
                ]);
            }
        }

        // Marca la solicitud en el proyecto
        $proyecto->update(['flag_solicitud_reconfigurar' => 1]);

        // 🔔 Notifica a otros componentes Livewire (v3 -> dispatch)
        $this->dispatch('reconfiguracionSolicitada', proyectoId: $this->proyectoId);
        // (opcional) también puedes mantener tu evento genérico
        $this->dispatch('estadoActualizado');

        $this->modal_reconfigurar_proyecto = false;
        session()->flash('message', '🔧 Se ha solicitado la reconfiguración del proyecto.');
        $this->dispatch('ActualizarTablaPedido');
    }


    private function proyectoIncompleto(): bool
    {
        $proyecto = \App\Models\Proyecto::find($this->proyectoId);
        if (!$proyecto) return true;

        $producto  = is_string($proyecto->producto_sel)  ? json_decode($proyecto->producto_sel,  true) : ($proyecto->producto_sel  ?? []);
        $categoria = is_string($proyecto->categoria_sel) ? json_decode($proyecto->categoria_sel, true) : ($proyecto->categoria_sel ?? []);

        return empty($producto['id']) || empty($categoria['id']);
    }



    
    public function render()
    {
        $proyecto = Proyecto::find($this->proyectoId);

        $query = Pedido::where('proyecto_id', $this->proyectoId)
            ->where('tipo', 'PEDIDO');

        // 👇 Filtro base: activos / inactivos
        if ($this->filters['inactivos']) {
            // Check marcado → solo inactivos
            $query->where('ind_activo', 0);
        } else {
            // Sin check → solo activos
            $query->where('ind_activo', 1);
        }

        $query->with([
            'pedidoTallas.talla' => function ($query) {
                $query->with('gruposTallas');
            },
            'tipoEnvio',
            'usuario',
        ]);

        return view('livewire.pedidos.pedidos-crud-proyecto', [
            'tiposEnvio'         => TipoEnvio::all(),
            'direccionesFiscales' => DireccionFiscal::with(['estado','pais'])->where('usuario_id', $proyecto->usuario_id)->get(),

            'direccionesEntrega' => DireccionEntrega::with(['estado','pais'])->where('usuario_id', $proyecto->usuario_id)->get(),

            'pedidos'            => $query->paginate(6),
        ]);
    }


    public function buscarPorFiltros()
{
    $this->resetPage();
}

public function updatedFilters()
{
    $this->resetPage();
}




public function abrirModalTallas(int $pedidoId): void
{
    $pedido = Pedido::query()
        ->select('id', 'flag_tallas')
        ->where('id', $pedidoId)
        ->firstOrFail();

    if ((int)($pedido->flag_tallas ?? 0) !== 1) {
        $this->dispatch('notify', message: 'Este pedido no maneja tallas.');
        return;
    }

    // ✅ Tablas reales por modelo (evita grupo_tallas vs grupo_talla)
    $ptTable = (new \App\Models\PedidoTalla)->getTable();
    $gTable  = (new \App\Models\GrupoTalla)->getTable();

    // Si tienes modelo Talla úsalo, si no, usa 'tallas'
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

    $this->tallas_grupos   = array_values($grupos);
    $this->tallas_total    = array_sum(array_column($this->tallas_grupos, 'subtotal'));
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




}

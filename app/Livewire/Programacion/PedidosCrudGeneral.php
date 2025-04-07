<?php

namespace App\Livewire\Programacion;

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
use App\Models\ProductoGrupoTalla;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;



class PedidosCrudGeneral extends Component
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

    protected $listeners = ['abrirModalEdicion' => 'abrirModal'];

    public $estado_produccion = 'POR APROBAR';

    public $proyecto_nombre;
    public $producto_nombre;
    public $categoria_nombre;
    public $proyecto_id_pedido; // por claridad, separando del ID del pedido

    
    // Filtros
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


    public function mount()
    {
        $this->tipos_envio = TipoEnvio::all();
        $this->clientes = Cliente::all();
        $this->direccionesFiscales = DireccionFiscal::with('ciudad.estado')->get();
        $this->direccionesEntrega = DireccionEntrega::with('ciudad.estado')->get();
    
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
            'estado' => 'required|in:POR APROBAR,APROBADO,ENTREGADO,RECHAZADO,ARCHIVADO',
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
        $query = Pedido::with(['cliente', 'producto.categoria', 'tipoEnvio', 'proyecto.user']);

        $query = Pedido::with(['cliente', 'producto.categoria', 'tipoEnvio', 'proyecto.user']);

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
        
        if ($this->filtro_estado) {
            $query->where('estado', $this->filtro_estado);
        }
        
        if ($this->filtro_estado_produccion) {
            $query->where('estado_produccion', $this->filtro_estado_produccion);
        }
        
        return view('livewire.programacion.pedidos-crud-general', [
            'pedidos' => $query->orderByDesc('created_at')->paginate(10),
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

    public function aplicarFiltros()
    {
        $this->resetPage();
    }

}



//  return view('livewire.programacion.pedidos-crud-general');
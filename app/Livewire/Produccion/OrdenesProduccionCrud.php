<?php

namespace App\Livewire\Produccion;

use Livewire\Component;
use App\Models\OrdenProduccion;
use App\Models\User;
use App\Models\Pedido;
use Illuminate\Support\Facades\Auth;

class OrdenesProduccionCrud extends Component
{
    public $ordenId;
    public $modalOpen = false;
    public $assigned_user_id;
    public $tipo;
    public $estado;
    public $descripcion;
    public $flujo_id;
    public $ordenEdit;
    public $modalCaracteristicas = false;
    public $ordenCaracteristicas = null;


    public $modalEntrega = false;
    public $ordenEntrega;
    public $piezasEntregadas = [];

    public $cantidadesTallasEntregadas = [];

    public $prioridad = 3;


    // Define los posibles estados (puedes ajustarlos)
    public $estadosDisponibles = [
        'SIN INICIAR',
        'EN PROCESO',
        'TERMINADO',
        'CANCELADO',
    ];
    


    protected $rules = [
        'assigned_user_id' => 'nullable|exists:users,id',
        'tipo'             => 'required|string',
        'estado'           => 'required|string',
        'flujo_id'         => 'nullable|exists:flujos_produccion,id',
        'prioridad'        => 'required|integer|min:1|max:4', 
    ];


    public function mount($tipo = null)
    {
        $this->tipo = $tipo;
    }

    public function abrirModal($id = null)
    {
        $this->reset(['ordenId', 'assigned_user_id', 'tipo', 'estado', 'flujo_id', 'descripcion', 'prioridad']);
        if ($id) {
            $orden = OrdenProduccion::findOrFail($id);
            $this->ordenEdit = $orden;
            $this->ordenId = $orden->id;
            $this->assigned_user_id = $orden->assigned_user_id;
            $this->tipo = $orden->tipo;
            $this->estado = $orden->estado;
            $this->flujo_id = $orden->flujo_id;
            $this->descripcion = $orden->descripcion ?? '';
            $this->prioridad = $orden->prioridad ?? 3;
        } else {
            $this->prioridad = 3; // default al crear
        }
        $this->modalOpen = true;
    }

    public function guardar()
    {
        $this->validate();

        OrdenProduccion::updateOrCreate(
            ['id' => $this->ordenId],
            [
                'assigned_user_id' => $this->assigned_user_id,
                'tipo'             => $this->tipo,
                'estado'           => $this->estado,
                'flujo_id'         => $this->flujo_id,
                'create_user'      => Auth::id(),
                'descripcion'      => $this->descripcion,
                'prioridad'        => $this->prioridad,
            ]
        );

        $this->modalOpen = false;
        session()->flash('message', 'Orden guardada correctamente.');
    }
    // Avanzar el estatus
    public function avanzarEstado($ordenId)
    {
        $orden = OrdenProduccion::findOrFail($ordenId);
        if ($orden->estado === 'EN PROCESO') {
            $this->abrirModalEntrega($ordenId);
            return;
        }

        $estados = ['SIN INICIAR', 'EN PROCESO', 'TERMINADO'];
        $idx = array_search($orden->estado, $estados);
        if ($idx !== false && $idx < count($estados) - 1) {
            $nuevoEstado = $estados[$idx + 1];
            $orden->update([
                'estado' => $nuevoEstado,
                'fecha_' . strtolower(str_replace(' ', '_', $nuevoEstado)) => now(),
            ]);
            session()->flash('message', "Orden #$ordenId actualizada a '$nuevoEstado'.");
        }
    }

    public function cancelarOrden($ordenId)
    {
        $orden = OrdenProduccion::findOrFail($ordenId);
        $orden->update([
            'estado' => 'CANCELADO',
            'fecha_cancelado' => now(),
        ]);
        session()->flash('message', "Orden #$ordenId cancelada.");
    }

    public function render()
    {
        $query = OrdenProduccion::with(['creador', 'usuarioAsignado', 'flujo', 'pedidos']);

        if ($this->tipo) {
            $query->where('tipo', $this->tipo);
        }

        $ordenes = $query->orderByDesc('created_at')->paginate(10);

        return view('livewire.produccion.ordenes-produccion-crud', [
            'ordenes'  => $ordenes,
            'usuarios' => User::all(),
            'flujos'   => \App\Models\FlujoProduccion::all(),
        ]);
    }

    public function verCaracteristicas($ordenId)
    {
        $this->ordenCaracteristicas = OrdenProduccion::with([
            'pedidos.pedidoCaracteristicas.caracteristica',
            'pedidos.pedidoOpciones.opcion.caracteristicas',
            'pedidos.pedidoTallas.talla',
            'pedidos.pedidoTallas.grupoTalla',
            'pedidos.proyecto',
        ])->findOrFail($ordenId);

        // 🔁 Evaluar tallas_agrupadas para cada pedido (esto es clave)
        foreach ($this->ordenCaracteristicas->pedidos as $pedido) {
            // Esta línea es necesaria para forzar la evaluación del accesor
            $pedido->tallas_agrupadas;
        }

        $this->modalCaracteristicas = true;
    }


    public function abrirModalEntrega($ordenId)
    {
        $this->ordenEntrega = OrdenProduccion::with(['pedidos.proyecto', 'pedidos.pedidoCaracteristicas.caracteristica'])->findOrFail($ordenId);

        // Inicializar cantidades entregadas si no existen
        foreach ($this->ordenEntrega->pedidos as $pedido) {
            $this->piezasEntregadas[$pedido->id] = $pedido->piezas_entregadas ?? 0;

            foreach ($pedido->tallas_agrupadas as $grupo) {
                foreach ($grupo['tallas'] as $talla) {
                    $nombre = $talla['nombre'];
                    $this->cantidadesTallasEntregadas[$pedido->id][$grupo['grupo_nombre']][$nombre] = 0;
                }
            }
        }




        $this->modalEntrega = true;
    }

    public function confirmarEntrega()
    {
        foreach ($this->ordenEntrega->pedidos as $pedido) {
            $entregadas = $this->piezasEntregadas[$pedido->id] ?? 0;

            // Validación por tallas si las hay
            if ($pedido->tallas_agrupadas->isNotEmpty()) {
                $tallas = $pedido->tallas_agrupadas;
                foreach ($tallas as $grupo) {
                    foreach ($grupo['tallas'] as $talla) {
                        $nombre = $talla['nombre'];
                        $esperadas = $talla['cantidad'];
                        $entregadasTalla = $this->cantidadesTallasEntregadas[$pedido->id][$grupo['grupo_nombre']][$nombre] ?? 0;

                        if ((int) $entregadasTalla < (int) $esperadas) {
                            session()->flash('message', "Faltan piezas para la talla {$nombre} del pedido #{$pedido->id}.");
                            return;
                        }
                    }
                }
            } elseif ($entregadas < $pedido->total) {
                session()->flash('message', "Faltan piezas por entregar en el pedido #{$pedido->id}.");
                return;
            }

            $pedido->update(['piezas_entregadas' => $entregadas]);
        }

        $this->ordenEntrega->update([
            'estado' => 'TERMINADO',
            'fecha_terminado' => now(),
        ]);

        $this->modalEntrega = false;
        session()->flash('message', "Orden #{$this->ordenEntrega->id} marcada como TERMINADA.");
    }

    
}

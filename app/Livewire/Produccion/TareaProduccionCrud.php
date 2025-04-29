<?php

namespace App\Livewire\Produccion;

use Livewire\Component;
use App\Models\TareaProduccion;
use App\Models\Pedido;
use App\Models\User;

class TareaProduccionCrud extends Component
{
    public $tareaId;
    public $pedido_id;
    public $staff_id;
    public $descripcion;
    public $estado = 'PENDIENTE';
    public $tipo = 'INDEFINIDA';
    public $disenio_flag_first_proceso = false;

    public $modalOpen = false;

    protected $rules = [
        'pedido_id' => 'required|exists:pedido,id',
        'staff_id' => 'required|exists:users,id',
        'descripcion' => 'nullable|string',
        'estado' => 'required|in:PENDIENTE,EN PROCESO,COMPLETADA,RECHAZADO,CANCELADO',
        'tipo' => 'required|in:DISEÑO,PRODUCCION,CORTE,PINTURA,FACTURACION,INDEFINIDA,REVISION',
        'disenio_flag_first_proceso' => 'boolean',
    ];

    public function abrirModal($id = null)
    {
        $this->reset(['tareaId', 'pedido_id', 'staff_id', 'descripcion', 'estado', 'tipo', 'disenio_flag_first_proceso']);

        if ($id) {
            $tarea = TareaProduccion::findOrFail($id);
            $this->tareaId = $tarea->id;
            $this->pedido_id = $tarea->pedido_id;
            $this->staff_id = $tarea->staff_id;
            $this->descripcion = $tarea->descripcion;
            $this->estado = $tarea->estado;
            $this->tipo = $tarea->tipo;
            $this->disenio_flag_first_proceso = $tarea->disenio_flag_first_proceso;
        }

        $this->modalOpen = true;
    }

    public function guardar()
    {
        $this->validate();

        TareaProduccion::updateOrCreate(
            ['id' => $this->tareaId],
            [
                'pedido_id' => $this->pedido_id,
                'staff_id' => $this->staff_id,
                'descripcion' => $this->descripcion,
                'estado' => $this->estado,
                'tipo' => $this->tipo,
                'disenio_flag_first_proceso' => $this->disenio_flag_first_proceso,
            ]
        );

        $this->modalOpen = false;
    }

    public function eliminar($id)
    {
        TareaProduccion::findOrFail($id)->delete();
    }

    // public function render()
    // {
    //     return view('livewire.produccion.tarea-produccion-crud', [
    //         'tareas' => TareaProduccion::with(['usuario', 'pedidos.pedidoTallas'])->paginate(10),
    //         'usuarios' => User::all(),
    //         'pedidos' => Pedido::all(),
    //     ]);
    // }

    public function render()
        {
            return view('livewire.produccion.tarea-produccion-crud', [
                'tareas' => TareaProduccion::with([
                    'usuario',
                    'pedidos.pedidoTallas',
                    'pedidos.pedidoCaracteristicas.caracteristica',
                    'pedidos.pedidoOpciones.opcion.caracteristicas'
                ])->paginate(10),
                'usuarios' => User::all(),
                'pedidos' => Pedido::all(),
            ]);
        }
}
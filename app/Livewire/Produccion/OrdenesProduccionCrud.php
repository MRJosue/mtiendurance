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
    ];

    public function abrirModal($id = null)
    {
        $this->reset(['ordenId', 'assigned_user_id', 'tipo', 'estado', 'flujo_id', 'descripcion']);
        if ($id) {
            $orden = OrdenProduccion::findOrFail($id);
            $this->ordenEdit = $orden;
            $this->ordenId = $orden->id;
            $this->assigned_user_id = $orden->assigned_user_id;
            $this->tipo = $orden->tipo;
            $this->estado = $orden->estado;
            $this->flujo_id = $orden->flujo_id;
            $this->descripcion = $orden->descripcion ?? '';
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
            ]
        );
        $this->modalOpen = false;
        session()->flash('message', 'Orden guardada correctamente.');
    }

    // Avanzar el estatus
    public function avanzarEstado($ordenId)
    {
        $orden = OrdenProduccion::findOrFail($ordenId);

        // Define el orden de los estados:
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
        $ordenes = OrdenProduccion::with(['creador', 'usuarioAsignado', 'flujo', 'pedidos'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('livewire.produccion.ordenes-produccion-crud', [
            'ordenes'  => $ordenes,
            'usuarios' => User::all(),
            'flujos'   => \App\Models\FlujoProduccion::all(),
        ]);
    }
}

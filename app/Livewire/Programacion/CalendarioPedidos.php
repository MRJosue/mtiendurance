<?php

namespace App\Livewire\Programacion;
use Livewire\Component;
use App\Models\Pedido;
use Carbon\Carbon;

class CalendarioPedidos extends Component
{
    public $pedidos;
    public $fechasProduccion = [];
    public $estados = [];

    public function mount()
    {
        $this->cargarPedidos();
    }

    public function cargarPedidos()
    {
        $this->pedidos = Pedido::with(['cliente', 'producto'])
            ->where('estado', '!=', 'ENTREGADO')
            ->get();

        foreach ($this->pedidos as $pedido) {
            $this->fechasProduccion[$pedido->id] = $pedido->fecha_produccion ? Carbon::parse($pedido->fecha_produccion)->toDateString() : null;
            $this->estados[$pedido->id] = $pedido->estado;
        }
    }

    public function actualizarPedido($pedidoId)
    {
        $pedido = Pedido::find($pedidoId);
        if ($pedido) {
            $pedido->fecha_produccion = $this->fechasProduccion[$pedidoId] ?? null;
            $pedido->estado = $this->estados[$pedidoId] ?? 'POR PROGRAMAR';
            $pedido->save();
            
            // Emitir evento para actualizar calendario
            $this->dispatch('pedidoActualizado', [
                'id' => $pedido->id,
                'fecha_produccion' => $pedido->fecha_produccion,
                'estado' => $pedido->estado
            ]);
        }
    }

    public function render()
    {
        return view('livewire.programacion.calendario-pedidos');
    }
}

// return view('livewire.programacion.calendario-pedidos');
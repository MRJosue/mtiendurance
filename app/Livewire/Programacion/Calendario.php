<?php

namespace App\Livewire\Programacion;


use Livewire\Component;
use App\Models\Pedido;
use Carbon\Carbon;

class Calendario extends Component
{
    public $eventos = [];

    protected $listeners = ['pedidoActualizado' => 'cargarEventos'];

    public function mount()
    {
        $this->cargarEventos();
    }

    public function cargarEventos()
    {
        $this->eventos = Pedido::whereNotNull('fecha_produccion')
            ->get()
            ->map(function ($pedido) {
                return [
                    'id' => $pedido->id,
                    'title' => 'Pedido #' . $pedido->id . ' - ' . ($pedido->producto->nombre ?? 'Sin Producto'),
                    'start' => Carbon::parse($pedido->fecha_produccion)->toDateString(),
                    'status' => $pedido->estado
                ];
            })->toArray();
    }

    public function render()
    {
        return view('livewire.programacion.calendario');
    }
}

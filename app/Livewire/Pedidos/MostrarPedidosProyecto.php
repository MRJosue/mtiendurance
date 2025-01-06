<?php

namespace App\Livewire\Pedidos;




use Livewire\Component;
use App\Models\Pedido;

class MostrarPedidosProyecto extends Component
{
    public $proyectoId;

    // Variable para almacenar los pedidos cargados
    public $pedidos;

    public function mount($proyectoId)
    {
        $this->proyectoId = $proyectoId;
        $this->cargarPedidos();
    }

    public function cargarPedidos()
    {

        // $this->proyectoId;
        // // Obtener todos los pedidos relacionados con el proyecto
        // $this->pedidos = Pedido::with(['cliente', 'producto', 'proyecto'])
        //     ->where('proyecto_id', $this->proyectoId)
        //     ->get();

        $this->pedidos  = Pedido::with(['cliente', 'producto.categoria', 'pedidoCaracteristicas.caracteristica'])
            ->where('proyecto_id', $this->proyectoId)
            ->get();

    }

    public function render()
    {
        return view('livewire.pedidos.mostrar-pedidos-proyecto', [
            'pedidos' => $this->pedidos,
        ]);
    }
}


//return view('livewire.pedidos.mostrar-pedidos-proyecto');

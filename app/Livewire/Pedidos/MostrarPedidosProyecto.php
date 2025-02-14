<?php

namespace App\Livewire\Pedidos;

use Livewire\Component;
use App\Models\Pedido;

class MostrarPedidosProyecto extends Component
{
    public $proyectoId;

    public function mount($proyectoId)
    {
        $this->proyectoId = $proyectoId;
    }

    public function render()
    {
        return view('livewire.pedidos.mostrar-pedidos-proyecto', [
            'pedidos' => Pedido::with([
                'cliente',
                'producto.categorias', // Corregido para Many-to-Many
                'pedidoCaracteristicas.caracteristica',
                'pedidoOpciones.opcion',
                'pedidoTallas.talla'
            ])->where('proyecto_id', $this->proyectoId)
              ->get(),
        ]);
    }
}

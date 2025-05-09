<?php

namespace App\Livewire\Dashboard\ClientePanel;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Pedido;

class Pedidos extends Component
{
    use WithPagination;

    public function render()
    {
        $pedidos = Pedido::with([
                'producto.categoria',
                'proyecto.user',
                'pedidoCaracteristicas.caracteristica',
                'pedidoOpciones.opcion.caracteristicas',
            ])
            ->whereHas('proyecto', fn($q) => $q->where('usuario_id', auth()->id()))
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('livewire.dashboard.cliente-panel.pedidos', compact('pedidos'));
    }
}
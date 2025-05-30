<?php

namespace App\Livewire\Dashboard\ClientePanel;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Pedido;

class Pedidos extends Component
{
    use WithPagination;
    public $mostrarFiltros = false;
    public $mostrarSoloNoAprobados = false;


    
        public function buscarPorFiltros()
        {
            $this->resetPage(); // solo reinicia la paginación
        }


        public function render()
        {
            $query = Pedido::with([
                'producto.categoria',
                'proyecto.user',
                'pedidoCaracteristicas.caracteristica',
                'pedidoOpciones.opcion.caracteristicas',
            ]);

            if (!auth()->user()->can('tablaPedidos-ver-todos-los-pedidos')) {
                $query->whereHas('proyecto', fn($q) => $q->where('usuario_id', auth()->id()));
            }

            if ($this->mostrarSoloNoAprobados) {
                // Mostrar pedidos cuyo proyecto NO esté aprobado
                // $query->whereHas('proyecto', fn($q) => $q->where('estado', '!=', 'DISEÑO APROBADO'));
            } else {
                // Mostrar solo pedidos con proyecto aprobado
                $query->whereHas('proyecto', fn($q) => $q->where('estado', 'DISEÑO APROBADO'));
            }

            $pedidos = $query->orderByDesc('created_at')->paginate(10);

            return view('livewire.dashboard.cliente-panel.pedidos', compact('pedidos'));
        }
}
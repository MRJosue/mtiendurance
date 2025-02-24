<?php
namespace App\Livewire\Pedidos;

use Livewire\Component;
use App\Models\Pedido;
use App\Models\Proyecto;
use Barryvdh\DomPDF\Facade\Pdf;

class OrdenProduccionPDF extends Component
{
    public $proyectoId;

    public function mount($proyectoId)
    {
        $this->proyectoId = $proyectoId;
    }

    public function generarPDF()
    {
        $proyecto = Proyecto::with(['pedidos.producto', 'pedidos.pedidoCaracteristicas.caracteristica', 'pedidos.pedidoOpciones.opcion', 'pedidos.pedidoTallas.talla'])
            ->findOrFail($this->proyectoId);

        $pdf = Pdf::loadView('pdf.orden-produccion', ['proyecto' => $proyecto]);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "Orden_Produccion_Proyecto_{$this->proyectoId}.pdf"
        );
    }

    public function render()
    {
        return view('livewire.pedidos.orden-produccion-pdf');
    }
}




// class OrdenProduccionPDF extends Component
// {
//     public function render()
//     {
//         return view('livewire.pedidos.orden-produccion-p-d-f');
//     }
// }

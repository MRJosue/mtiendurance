<?php

namespace App\Livewire\Modales;


use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Proyecto;

class ProyectoInfoModal extends Component
{
    public bool $open = false;
    public $infoProyecto = null;

    #[On('open-proyecto-info')]
    public function open(int $proyectoId): void
    {
        $proyecto = Proyecto::with(['user', 'categoria'])->findOrFail($proyectoId);

        $proyecto->caracteristicas_sel = is_array($proyecto->caracteristicas_sel)
            ? $proyecto->caracteristicas_sel
            : json_decode($proyecto->caracteristicas_sel, true);

        $proyecto->producto_sel = is_array($proyecto->producto_sel)
            ? $proyecto->producto_sel
            : json_decode($proyecto->producto_sel, true);

        $proyecto->categoria_sel = is_array($proyecto->categoria_sel)
            ? $proyecto->categoria_sel
            : json_decode($proyecto->categoria_sel, true);

        $this->infoProyecto = $proyecto;
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
        $this->infoProyecto = null;
    }

    public function render()
    {
        return view('livewire.modales.proyecto-info-modal');
    }
}



// use Livewire\Component;

// class ProyectoInfoModal extends Component
// {
//     public function render()
//     {
//         return view('livewire.modales.proyecto-info-modal');
//     }
// }

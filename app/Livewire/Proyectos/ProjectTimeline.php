<?php

namespace App\Livewire\Proyectos;

use Livewire\Component;
use App\Models\Proyecto;

class ProjectTimeline extends Component
{
    public $proyectoId;
    public $estadoActual;
    
    // Lista de estados en orden
    public $estados = [
        'PENDIENTE', 'ASIGNADO', 'EN PROCESO','REVISION', 'DISEÑO APROBADO'
    ];

    protected $listeners = ['estadoActualizado' => 'actualizarEstado'];

    /**
     * Se ejecuta al montar el componente y obtiene el estado del proyecto.
     */
    public function mount($proyectoId)
    {
        $this->proyectoId = $proyectoId;
        $this->actualizarEstado();
    }

    /**
     * Obtiene el estado actualizado del proyecto.
     */
    public function actualizarEstado()
    {
        $proyecto = Proyecto::find($this->proyectoId);

        if ($proyecto) {
            $this->estadoActual = $proyecto->estado;

            if ($this->estadoActual === 'DISEÑO RECHAZADO') {
                // Reemplazar 'EN PROCESO' por 'DISEÑO RECHAZADO'
                $index = array_search('EN PROCESO', $this->estados);
                if ($index !== false) {
                    $this->estados[$index] = 'DISEÑO RECHAZADO';
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.proyectos.project-timeline');
    }
}
//return view('livewire.proyectos.project-timeline');

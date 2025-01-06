<?php

namespace App\Livewire\Proyectos;

use Livewire\Component;

class ProjectTimeline extends Component
{
    public $estadoActual;

    // Lista de estados en orden
    public $estados = [
        'PENDIENTE', 'APROBADO', 'PROGRAMADO', 'IMPRESIÓN', 'PRODUCCIÓN',
        'COSTURA', 'ENTREGA', 'FACTURACIÓN', 'COMPLETADO'
    ];

    public function render()
    {
        return view('livewire.proyectos.project-timeline');
    }
}

//return view('livewire.proyectos.project-timeline');

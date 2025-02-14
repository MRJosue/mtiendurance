<?php

namespace App\Livewire\Proyectos;

use Livewire\Component;


use App\Models\Proyecto;
use Illuminate\Support\Facades\Log;

class ControlEstado extends Component
{
    public $proyectoId;
    public $estado;

    public function mount($proyectoId)
    {
        $this->proyectoId = $proyectoId;
        $this->cargarEstado();
    }

    public function cargarEstado()
    {
        $proyecto = Proyecto::find($this->proyectoId);
        if ($proyecto) {
            $this->estado = $proyecto->estado;
        }
    }

    public function cambiarEstado($accion)
    {
        $proyecto = Proyecto::find($this->proyectoId);

        Log::error("Mostramos la informacion del proyecto", ['proyectoId' =>  $proyecto]);
        Log::error("Mostramos la accion", ['accion' =>  $accion]);

        if ($proyecto && $proyecto->actualizarEstado($accion)) {
            $this->estado = $proyecto->fresh()->estado; // Refrescar el estado después de actualizar
            $this->dispatch('estadoActualizado'); // Evento para que otros componentes escuchen
            
        } else {
            Log::error("No se pudo actualizar el estado", ['proyectoId' => $this->proyectoId, 'accion' => $accion]);
        }
    }

    public function render()
    {
        return view('livewire.proyectos.control-estado');
    }
}
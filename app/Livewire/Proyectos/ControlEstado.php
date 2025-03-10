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

        Log::info("Información del proyecto", ['proyectoId' => $proyecto]);
        Log::info("Acción recibida", ['accion' => $accion]);

        if (!$proyecto) {
            Log::error("No se encontró el proyecto", ['proyectoId' => $this->proyectoId]);
            return;
        }

        // Si la acción es aprobar, cambia el estado directamente a "APROBADO"
        if ($accion === 'aprobar') {
            $proyecto->estado = 'ASIGNADO';
        } else {
            if (!$proyecto->actualizarEstado($accion)) {
                Log::error("No se pudo actualizar el estado", ['proyectoId' => $this->proyectoId, 'accion' => $accion]);
                return;
            }
        }

        $proyecto->save();
        $this->estado = $proyecto->fresh()->estado; // Refrescar el estado después de actualizar
        $this->dispatch('estadoActualizado'); // Notifica a otros componentes

        Log::info("Estado actualizado exitosamente", ['proyectoId' => $this->proyectoId, 'nuevo_estado' => $this->estado]);
    }

    public function render()
    {
        return view('livewire.proyectos.control-estado');
    }
}
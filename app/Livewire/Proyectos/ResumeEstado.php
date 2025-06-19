<?php

namespace App\Livewire\Proyectos;

use Livewire\Component;
use App\Models\Proyecto;
use App\Models\proyecto_estados;

class ResumeEstado extends Component
{
    public int $proyectoId;
    public bool $tieneAprobado = false;
    public ?proyecto_estados $registro = null;

    public function mount(int $proyectoId): void
    {
        $this->proyectoId = $proyectoId;

        // Revisar si el proyecto está en estado "DISEÑO APROBADO"
        $proyecto = Proyecto::findOrFail($this->proyectoId);
        if ($proyecto->estado === 'DISEÑO APROBADO') {
            // Obtener el registro de aprobación más reciente
            $this->registro = proyecto_estados::where('proyecto_id', $this->proyectoId)
                ->where('estado', 'DISEÑO APROBADO')
                ->with('usuario')
                ->latest('id')
                ->first();

            $this->tieneAprobado = (bool) $this->registro;
        }
    }

    public function render()
    {
        return view('livewire.proyectos.resume-estado', [
            'tieneAprobado' => $this->tieneAprobado,
            'registro'      => $this->registro,
        ]);
    }
}

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


    protected $listeners = ['resumen_aprobacion' => 'cargarAprobacion'];
    

    public function mount(int $proyectoId): void
    {
        $this->proyectoId = $proyectoId;

        $this->cargarAprobacion();

    }

    public function cargarAprobacion(): void
    {
        $this->tieneAprobado = false;
        $this->registro = null;

        $proyecto = Proyecto::findOrFail($this->proyectoId);
        if ($proyecto->estado === 'DISEÑO APROBADO') {
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

<?php

namespace App\Livewire\Proyectos;

use Livewire\Component;
use App\Models\Proyecto;
use App\Models\proyecto_estados;

use App\Models\ArchivoProyecto;        // ← Importamos el modelo de archivos

class ResumeEstado extends Component
{
    public int $proyectoId;
    public bool $tieneAprobado = false;
    public ?proyecto_estados $registro = null;
    public ?ArchivoProyecto $archivoProyecto = null;  // ← Nuevo: propiedad para el archivo

    protected $listeners = ['resumen_aprobacion' => 'cargarAprobacion'];

    public function mount(int $proyectoId): void
    {
        $this->proyectoId = $proyectoId;
        $this->cargarAprobacion();
    }

    public function cargarAprobacion(): void
    {
        $this->tieneAprobado   = false;
        $this->registro        = null;
        $this->archivoProyecto = null;    // ← Reiniciamos

        $proyecto = Proyecto::findOrFail($this->proyectoId);

        if ($proyecto->estado === 'DISEÑO APROBADO') {
            // Cargamos el registro de aprobación
            $this->registro = proyecto_estados::where('proyecto_id', $this->proyectoId)
                ->where('estado', 'DISEÑO APROBADO')
                ->with('usuario')
                ->latest('id')
                ->first();

            $this->tieneAprobado = (bool) $this->registro;

            if ($this->tieneAprobado) {
                // Buscamos el último archivo de tipo "diseño" (tipo_carga = 1)
                $this->archivoProyecto = ArchivoProyecto::where('proyecto_id', $this->proyectoId)
                    ->where('tipo_carga', 1)
                    ->latest('id')
                    ->first();
            }
        }
    }

    public function render()
    {
        return view('livewire.proyectos.resume-estado', [
            
            'tieneAprobado'    => $this->tieneAprobado,
            'registro'         => $this->registro,
            'archivoProyecto'  => $this->archivoProyecto,   // ← Pasamos a la vista
        ]);
    }
}
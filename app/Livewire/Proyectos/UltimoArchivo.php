<?php

namespace App\Livewire\Proyectos;

use Livewire\Component;
use App\Models\ArchivoProyecto;
use App\Models\Proyecto;
use App\Models\proyecto_estados;
use Illuminate\Support\Facades\Log;

class UltimoArchivo extends Component
{
    public $proyectoId;
    public $ultimoArchivo;
    public bool $tieneAprobado = false;
    public ?proyecto_estados $registroAprobacion = null;

    //listener para actualizar el componente 
    protected $listeners = [
        'archivoSubido' => 'cargarUltimoArchivo',
        'resumen_aprobacion' => 'cargarUltimoArchivo',
    ];



    
    public function mount($proyectoId)
    {
        $this->proyectoId = $proyectoId;
        $this->cargarUltimoArchivo();
    }
    

    public function cargarUltimoArchivo()
    {
        $this->ultimoArchivo = ArchivoProyecto::where('proyecto_id', $this->proyectoId)
            ->latest('id')
            ->where('tipo_carga', 1)
            ->first();

        $this->cargarAprobacion();

        if (!$this->ultimoArchivo) {
            Log::warning("No se encontró ningún archivo para el proyecto", ['proyectoId' => $this->proyectoId]);
        }
    }

    public function cargarAprobacion(): void
    {
        $this->tieneAprobado = false;
        $this->registroAprobacion = null;

        $proyecto = Proyecto::find($this->proyectoId);

        if (! $proyecto || $proyecto->estado !== 'DISEÑO APROBADO') {
            return;
        }

        $this->registroAprobacion = proyecto_estados::where('proyecto_id', $this->proyectoId)
            ->where('estado', 'DISEÑO APROBADO')
            ->with('usuario')
            ->latest('id')
            ->first();

        $this->tieneAprobado = (bool) $this->registroAprobacion;
    }

    public function render()
    {
        return view('livewire.proyectos.ultimo-archivo');
    }
}

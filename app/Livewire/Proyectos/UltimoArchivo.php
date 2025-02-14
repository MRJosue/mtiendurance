<?php

namespace App\Livewire\Proyectos;

use Livewire\Component;
use App\Models\ArchivoProyecto;
use Illuminate\Support\Facades\Log;

class UltimoArchivo extends Component
{
    public $proyectoId;
    public $ultimoArchivo;

    //listener para actualizar el componente 
    protected $listeners = ['archivoSubido' => 'cargarUltimoArchivo'];



    
    public function mount($proyectoId)
    {
        $this->proyectoId = $proyectoId;
        $this->cargarUltimoArchivo();
    }
    

    public function cargarUltimoArchivo()
    {
        $this->ultimoArchivo = ArchivoProyecto::where('proyecto_id', $this->proyectoId)
            ->latest('created_at')
            ->first();

        if (!$this->ultimoArchivo) {
            Log::warning("No se encontró ningún archivo para el proyecto", ['proyectoId' => $this->proyectoId]);
        }
    }

    public function render()
    {
        return view('livewire.proyectos.ultimo-archivo');
    }
}
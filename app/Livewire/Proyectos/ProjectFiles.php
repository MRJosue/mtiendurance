<?php

namespace App\Livewire\Proyectos;



use App\Models\ArchivoProyecto;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Log;

class ProjectFiles extends Component
{
    use WithFileUploads;

    public $proyectoId;
    public $archivo;

    public function mount($proyectoId)
    {
        $this->proyectoId = $proyectoId;
    }

    public function uploadFile()
    {
        $this->validate([
            'archivo' => 'required|file', // Máximo 10MB
        ]);
    
        $path = $this->archivo->store('proyectos/' . $this->proyectoId, 'public');
    
        ArchivoProyecto::create([
            'proyecto_id' => $this->proyectoId,
            'usuario_id' => Auth::id(),
            'nombre_archivo' => $this->archivo->getClientOriginalName(),
            'ruta_archivo' => $path,
            'tipo_archivo' => $this->archivo->getMimeType(), // Agrega el tipo de archivo
        ]);
    
        $this->archivo = null; // Limpiar el archivo cargado
            // Enviar evento para actualizar `UltimoArchivo`
             $this->dispatch('archivoSubido');
             Log::warning("Dispatch archivoSubido");
             
        session()->flash('message', 'Archivo subido exitosamente.');
    }

    public function deleteFile($id)
    {
        $archivo = ArchivoProyecto::findOrFail($id);

        if (\Storage::disk('public')->exists($archivo->ruta_archivo)) {
            \Storage::disk('public')->delete($archivo->ruta_archivo);
        }

        $archivo->delete();

        session()->flash('message', 'Archivo eliminado exitosamente.');
    }

    public function descargarArchivo($rutaArchivo)
{
    if (Storage::disk('public')->exists($rutaArchivo)) {
        return Storage::disk('public')->download($rutaArchivo);
    }

    return abort(404, 'Archivo no encontrado.');
}

    public function render()
    {
        return view('livewire.proyectos.project-files', [
            'archivos' => ArchivoProyecto::where('proyecto_id', $this->proyectoId)->get(),
        ]);
    }
}




// return view('livewire.proyectos.project-files');
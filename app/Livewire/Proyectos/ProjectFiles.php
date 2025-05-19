<?php

namespace App\Livewire\Proyectos;



use App\Models\ArchivoProyecto;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Log;


class ProjectFiles extends Component
{
    use WithFileUploads, WithPagination;

    public $proyectoId;
    public $archivo;
    public $modalVerArchivosProyecto = false;
    public $search = '';

    protected $updatesQueryString = ['search'];

    public function mount($proyectoId)
    {
        $this->proyectoId = $proyectoId;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function uploadFile()
    {
        $this->validate([
            'archivo' => 'required|file|max:10240',
        ]);

        $path = $this->archivo->store('proyectos/' . $this->proyectoId, 'public');

        ArchivoProyecto::create([
            'proyecto_id' => $this->proyectoId,
            'usuario_id' => Auth::id(),
            'nombre_archivo' => $this->archivo->getClientOriginalName(),
            'ruta_archivo' => $path,
            'tipo_archivo' => $this->archivo->getMimeType(),
        ]);

        $this->archivo = null;

        $this->dispatch('archivoSubido');

        session()->flash('message', 'Archivo subido exitosamente.');
    }

    public function deleteFile($id)
    {
        $archivo = ArchivoProyecto::findOrFail($id);

        if (Storage::disk('public')->exists($archivo->ruta_archivo)) {
            Storage::disk('public')->delete($archivo->ruta_archivo);
        }

        $archivo->delete();

        session()->flash('message', 'Archivo eliminado exitosamente.');
    }

    public function render()
    {
        $archivos = ArchivoProyecto::where('proyecto_id', $this->proyectoId)
            ->where('nombre_archivo', 'like', '%' . $this->search . '%')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('livewire.proyectos.project-files', [
            'archivos' => $archivos,
        ]);
    }
}



// return view('livewire.proyectos.project-files');
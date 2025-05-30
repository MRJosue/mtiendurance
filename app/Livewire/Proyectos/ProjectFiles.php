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
    public $tab = 'disenos';


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
            'tipo_archivo' => 1,
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
        $query = ArchivoProyecto::where('proyecto_id', $this->proyectoId)
            ->where('nombre_archivo', 'like', '%' . $this->search . '%');

        if ($this->tab === 'disenos') {
            $query->where('tipo_carga', 1);
        } elseif ($this->tab === 'iniciales') {
            $query->where('tipo_carga', 2);
        }

        $archivos = $query->orderByDesc('created_at')->paginate(10);

        return view('livewire.proyectos.project-files', compact('archivos'));
    }
}



// return view('livewire.proyectos.project-files');
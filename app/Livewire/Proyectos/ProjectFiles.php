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

    public bool $archivoDuplicado = false;
    public ?string $archivoNombre = null;
    public ?string $archivoNombreFinal = null;


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
        'archivo' => 'required|file|max:10240|mimes:jpg,jpeg,png,webp,svg,ai,psd,pdf,zip',
    ]);

    if (!$this->proyectoId) {
        session()->flash('message', 'Proyecto inválido.');
        return;
    }

    if ($this->archivoDuplicado) {
        session()->flash('message', 'Ya existe un archivo con ese nombre final en este proyecto.');
        return;
    }

    // Construimos nombre final por si no se generó en updatedArchivo
    $finalName = $this->archivoNombreFinal ?: $this->construirNombreFinal();

    // (Opcional recomendado) proteger el último archivo previo del proyecto
    $ultimo = ArchivoProyecto::where('proyecto_id', $this->proyectoId)
        ->latest('id')->first();
    if ($ultimo) {
        $ultimo->update(['flag_can_delete' => 0]); // proteger el último previo
    }

    // Guardar con nombre final
    $dir  = 'proyectos/' . $this->proyectoId;
    $path = $this->archivo->storeAs($dir, $finalName, 'public');

    // Calcular versión y tipo_carga según pestaña
    $version    = ArchivoProyecto::calcularVersion($this->proyectoId);
    $tipoCarga  = $this->tab === 'iniciales' ? 2 : 1;

    ArchivoProyecto::create([
        'proyecto_id'    => $this->proyectoId,
        'usuario_id'     => Auth::id(),
        'nombre_archivo' => $finalName, // con timestamp
        'ruta_archivo'   => $path,
        'tipo_archivo'   => $this->archivo->getClientMimeType(),
        'tipo_carga'     => $tipoCarga,
        'version'        => $version,
        'flag_can_delete'=> 1, // el recién subido sí puede eliminarse
    ]);

    // Reset + eventos UI
    $this->reset(['archivo', 'archivoNombre', 'archivoNombreFinal', 'archivoDuplicado']);
    $this->dispatch('archivoSubido');

    session()->flash('message', 'Archivo subido exitosamente.');
}


    /**
     * Acción Livewire para disparar la descarga de un archivo.
     *
     * @param  int  $id
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadFile(int $id)
    {
        // Recupera el modelo y delega en su método descargar()
        $archivo = ArchivoProyecto::findOrFail($id);

        return $archivo->descargar();
    }

    public function deleteFile($id)
    {
        $archivo = ArchivoProyecto::findOrFail($id);

        // Asegurar backend: no permitir borrar si flag_can_delete = 0
        if (!$archivo->flag_can_delete) {
            session()->flash('message', 'Este archivo está protegido y no puede eliminarse.');
            return;
        }

        if (Storage::disk('public')->exists($archivo->ruta_archivo)) {
            Storage::disk('public')->delete($archivo->ruta_archivo);
        }

        $archivo->delete();

        session()->flash('message', 'Archivo eliminado exitosamente.');
    }

    private function construirNombreFinal(): ?string
    {
        if (!$this->archivo) return null;

        $timestamp = now()->format('Ymd_Hi'); // p.ej. 20250918_1142
        $original  = $this->archivo->getClientOriginalName();
        $base      = pathinfo($original, PATHINFO_FILENAME);
        $ext       = $this->archivo->getClientOriginalExtension();

        $base = \Illuminate\Support\Str::slug($base, '_');
        $base = \Illuminate\Support\Str::limit($base, 80, '');

        return $ext ? "{$base}_{$timestamp}.{$ext}" : "{$base}_{$timestamp}";
    }

    public function updatedArchivo(): void
    {
        $this->archivoDuplicado   = false;
        $this->archivoNombre      = null;
        $this->archivoNombreFinal = null;

        if (!$this->archivo || !$this->proyectoId) return;

        $this->archivoNombre      = $this->archivo->getClientOriginalName();
        $this->archivoNombreFinal = $this->construirNombreFinal();

        // Chequeo de colisión exacta del FINAL (raro, pero seguro)
        $this->archivoDuplicado = ArchivoProyecto::where('proyecto_id', $this->proyectoId)
            ->where('nombre_archivo', $this->archivoNombreFinal)
            ->exists();
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
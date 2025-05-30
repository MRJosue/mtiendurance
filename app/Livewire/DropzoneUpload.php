<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;


class DropzoneUpload extends Component
{
    use WithFileUploads;

    public $archivos = [];

    protected $listeners = ['guardarArchivos'];

    public function guardarArchivos($preProyectoId)
    {
        foreach ($this->archivos as $file) {
            $nombreFinal = now()->format('Ymd_His') . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('preproyectos', $nombreFinal);

            \App\Models\ArchivoProyecto::create([
                'pre_proyecto_id' => $preProyectoId,
                'usuario_id' => auth()->id(),
                'nombre_archivo' => $file->getClientOriginalName(),
                'ruta_archivo' => $path,
                'tipo_archivo' => $file->getClientMimeType(),
                'descripcion' => '',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.dropzone-upload');
    }
}
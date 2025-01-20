<?php

namespace App\Livewire\Preproyectos;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\PreProyecto;
use App\Models\ArchivoProyecto;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;

use App\Models\User;

class CreatePreProject extends Component
{
    use WithFileUploads;

    public $nombre;
    public $descripcion;
    public $estado = 'PENDIENTE';
    public $fecha_produccion;
    public $fecha_embarque;
    public $fecha_entrega;
    public $files = [];
    public $fileDescriptions = [];
    public $producto_id;
    public $total;
    public $estatus;

    Public $auxiliar_preProyecto_id;

    public function create()
    {
        $this->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_produccion' => 'nullable|date',
            'fecha_embarque' => 'nullable|date',
            'fecha_entrega' => 'nullable|date',
            'files.*' => 'file|max:10240', // Máximo 10MB por archivo
            'fileDescriptions.*' => 'nullable|string|max:255',
            'producto_id' => 'required|exists:productos,id',
            'total' => 'required|numeric|min:0',
            'estatus' => 'required|string|max:255',
        ]);

        $cliente = Auth::user()->cliente;

        if (!$cliente) {
            session()->flash('error', 'El usuario no tiene un cliente asociado.');
            return;
        }

        // Crear Preproyecto
        $preProyecto = PreProyecto::create([
            'usuario_id' => Auth::id(),
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado,
            'fecha_produccion' => $this->fecha_produccion,
            'fecha_embarque' => $this->fecha_embarque,
            'fecha_entrega' => $this->fecha_entrega,
        ]);


        $auxiliar_preProyecto_id = $preProyecto->id;

        // Crear Archivos
        foreach ($this->files as $index => $file) {
            $rutaArchivo = $file->store('preproyectos/archivos', 'public');
            ArchivoProyecto::create([
                'pre_proyecto_id' => $auxiliar_preProyecto_id,
                'nombre_archivo' => $file->getClientOriginalName(),
                'ruta_archivo' => $rutaArchivo,
                'tipo_archivo' => $file->getMimeType(),
                'usuario_id' => Auth::id(),
                'descripcion' => $this->fileDescriptions[$index] ?? null,
            ]);
        }

        // Crear Pedido
        Pedido::create([
            'pre_proyecto_id' => $auxiliar_preProyecto_id,
            'producto_id' => $this->producto_id,
            'cliente_id' => $cliente->id,
            'total' => $this->total,
            'estatus' => $this->estatus,
        ]);

        session()->flash('message', 'Preproyecto creado exitosamente.');
        return redirect()->route('preproyectos.index');
    }

    public function render()
    {
        return view('livewire.preproyectos.create-pre-project', [
            'productos' => Producto::all(),
        ]);
    }
}
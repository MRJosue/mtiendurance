<?php

namespace App\Livewire\Preproyectos;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Proyecto;
use App\Models\ArchivoProyecto;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;

class CreatePreProject extends Component
{
    use WithFileUploads;

    public $nombre;
    public $descripcion;
    public $fecha_produccion;
    public $fecha_embarque;
    public $fecha_entrega;
    public $files = [];
    public $fileDescriptions = [];
    public $producto_id;
    public $total;

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
        ]);

        $user = Auth::user();
        $cliente = $user->cliente;

        if (!$cliente) {
            session()->flash('error', 'El usuario no tiene un cliente asociado.');
            return;
        }

        // Crear Proyecto
        $proyecto = Proyecto::create([
            'usuario_id' => $user->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'estado' => 'PENDIENTE',
            'fecha_produccion' => $this->fecha_produccion,
            'fecha_embarque' => $this->fecha_embarque,
            'fecha_entrega' => $this->fecha_entrega,
        ]);

        // Crear Archivos
        foreach ($this->files as $index => $file) {
            $rutaArchivo = $file->store('proyectos/archivos', 'public');
            ArchivoProyecto::create([
                'proyecto_id' => $proyecto->id,
                'nombre_archivo' => $file->getClientOriginalName(),
                'ruta_archivo' => $rutaArchivo,
                'tipo_archivo' => $file->getMimeType(),
                'usuario_id' => $user->id,
                'descripcion' => $this->fileDescriptions[$index] ?? null,
            ]);
        }

        // Crear Pedido
        Pedido::create([
            'proyecto_id' => $proyecto->id,
            'producto_id' => $this->producto_id,
            'cliente_id' => $cliente->id,
            'total' => $this->total,
            'estatus' => 'pendiente',
        ]);

        session()->flash('message', 'Proyecto creado exitosamente.');
        return redirect()->route('preproyectos.index');
    }

    public function render()
    {
        return view('livewire.preproyectos.create-pre-project', [
            'productos' => Producto::all(),
        ]);
    }
}

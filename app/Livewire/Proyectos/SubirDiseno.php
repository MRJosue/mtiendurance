<?php

namespace App\Livewire\Proyectos;


use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\ArchivoProyecto;
use App\Models\Proyecto;
use App\Models\proyecto_estados;
use App\Models\Tarea;
use App\Models\Pedido; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class SubirDiseno extends Component
{
    use WithFileUploads;

    public $proyectoId;
    public $archivo;
    public $comentario;
    public $modalOpen = false;
    public $modalAprobar = false;
    public $modalRechazar = false;
    public $modalConfirmarMuestra = false;

    public $comentarioRechazo;
    public $estado;

    protected $rules = [
        'archivo' => 'required|file|max:10240',
        'comentario' => 'nullable|string|max:500',
    ];

    public function mount($proyectoId)
    {
        $this->proyectoId = $proyectoId;
        $this->cargarEstado();
    }

    public function cargarEstado()
    {
        $this->estado = Proyecto::find($this->proyectoId)?->estado ?? '';
    }

    public function subir()
    {
        $this->validate();

        $proyecto = Proyecto::find($this->proyectoId);
        if (!$proyecto) return;

        $path = $this->archivo->store('disenos', 'public');

        $archivo = ArchivoProyecto::create([
            'proyecto_id' => $proyecto->id,
            'nombre_archivo' => $this->archivo->getClientOriginalName(),
            'ruta_archivo' => $path,
            'tipo_archivo' => $this->archivo->getClientMimeType(),
            'usuario_id' => Auth::id(),
            'descripcion' => $this->comentario,
        ]);

        $proyecto->estado = 'REVISION';
        $proyecto->save();

        proyecto_estados::create([
            'proyecto_id' => $proyecto->id,
            'estado' => 'REVISION',
            'comentario' => $this->comentario,
            'url' => $archivo->ruta_archivo,
            'fecha_inicio' => now(),
            'usuario_id' => Auth::id(),
            'last_uploaded_file_id' => $archivo->id,
        ]);

        Tarea::where('proyecto_id', $proyecto->id)->update(['estado' => 'EN PROCESO']);

        $this->registrarEventoEnChat("Se subió un nuevo archivo de diseño y se cambió el estado a REVISION.");

        $this->dispatch('estadoActualizado');
        $this->cargarEstado();

        $this->reset(['archivo', 'comentario', 'modalOpen']);
        session()->flash('message', 'Archivo subido correctamente.');
    }

    public function aprobarDiseno()
    {
        $proyecto = Proyecto::find($this->proyectoId);
        if (!$proyecto) return;

        $archivo = ArchivoProyecto::where('proyecto_id', $proyecto->id)->latest()->first();

        $proyecto->estado = 'DISEÑO APROBADO';
        $proyecto->save();

        proyecto_estados::create([
            'proyecto_id' => $proyecto->id,
            'estado' => 'DISEÑO APROBADO',
            'comentario' => 'Aprobado por el cliente',
            'url' => $archivo?->ruta_archivo,
            'fecha_inicio' => now(),
            'usuario_id' => Auth::id(),
            'last_uploaded_file_id' => $archivo?->id,
        ]);

        Tarea::where('proyecto_id', $proyecto->id)->update(['estado' => 'COMPLETADA']);

        $this->registrarEventoEnChat('El cliente aprobó el diseño. Estado actualizado a DISEÑO APROBADO.');

        $this->dispatch('estadoActualizado');
        $this->cargarEstado();

        $this->modalAprobar = false;
        session()->flash('message', 'Diseño aprobado correctamente.');
    }

    public function rechazarDiseno()
    {
        $this->validate([
            'comentarioRechazo' => 'required|string|min:5',
        ]);

        $proyecto = Proyecto::find($this->proyectoId);
        if (!$proyecto) return;

        $archivo = ArchivoProyecto::where('proyecto_id', $proyecto->id)->latest()->first();

        $proyecto->estado = 'EN PROCESO';
        $proyecto->save();

        Tarea::where('proyecto_id', $proyecto->id)->update(['estado' => 'RECHAZADO']);

        proyecto_estados::create([
            'proyecto_id' => $proyecto->id,
            'estado' => 'RECHAZADO',
            'comentario' => $this->comentarioRechazo,
            'url' => $archivo?->ruta_archivo,
            'fecha_inicio' => now(),
            'usuario_id' => Auth::id(),
            'last_uploaded_file_id' => $archivo?->id,
        ]);

        $this->registrarEventoEnChat('El cliente rechazó el diseño. Comentario: ' . $this->comentarioRechazo);

        $this->dispatch('estadoActualizado');
        $this->cargarEstado();

        $this->modalRechazar = false;
        $this->comentarioRechazo = '';
        session()->flash('message', 'Diseño rechazado correctamente.');
    }

    protected function registrarEventoEnChat($mensaje)
    {
        $proyecto = Proyecto::find($this->proyectoId);
        if (!$proyecto) return;

        $chat = $proyecto->chat ?? $proyecto->chat()->create([
            'proyecto_id' => $proyecto->id,
            'fecha_creacion' => now(),
        ]);

        $chat->mensajes()->create([
            'usuario_id' => Auth::id(),
            'mensaje' => $mensaje,
            'tipo' => 2,
            'fecha_envio' => now(),
        ]);
    }

    public function crearMuestraDesdeDiseno()
    {
        $proyecto = Proyecto::find($this->proyectoId);
    
        if (!$proyecto) {
            session()->flash('error', 'No se encontró el proyecto.');
            return;
        }
    
        $archivo = ArchivoProyecto::where('proyecto_id', $proyecto->id)->latest()->first();
    
        if (!$archivo) {
            session()->flash('error', 'No puedes crear una muestra sin haber subido al menos un archivo de diseño.');
            return;
        }
    
        // Validar si ya existe una muestra para este archivo y proyecto
        $existe = Pedido::where('proyecto_id', $proyecto->id)
            ->where('last_uploaded_file_id', $archivo->id)
            ->where('tipo', 'MUESTRA')
            ->exists();
    
        if ($existe) {
            session()->flash('error', 'Ya existe una muestra registrada para este diseño.');
            $this->modalConfirmarMuestra = false;
            return;
        }
    
        Pedido::crearMuestra($proyecto->id, [
            'cliente_id' => $proyecto->usuario_id,
            'direccion_fiscal_id' => null,
            'direccion_entrega_id' => null,
            'tipo' => 'MUESTRA',
            'estado' => 'POR PROGRAMAR',
            'total' => 0,
            'fecha_produccion' => null,
            'fecha_embarque' => null,
            'fecha_entrega' => null,
            'id_tipo_envio' => null,
        ]);
    
        $this->modalConfirmarMuestra = false;
        session()->flash('message', 'Muestra creada correctamente.');
        $this->dispatch('muestraCreada');
    }

    public function render()
    {
        return view('livewire.proyectos.subir-diseno');
    }
}
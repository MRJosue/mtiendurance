<?php

namespace App\Livewire\Proyectos;

use App\Models\proyecto_estados;
use Livewire\Component;
use App\Models\Proyecto;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NuevaNotificacion;
//use App\Models\ProyectoEstado;   // ajusta el nombre del modelo si es diferente


class TareasDiseno extends Component
{
    public Proyecto $proyecto;               // Proyecto actual
    public bool $modalOpen = false;          // Control del modal de asignación
    public bool $modalVerMas = false;        // Control del modal de historial
    public ?Proyecto $proyectoSeleccionado = null; // Proyecto cargado para historial

    public ?int $selectedUser = null;        // Diseñador elegido
    public string $taskDescription = '';     // Descripción de la tarea

    protected $rules = [
        'selectedUser'    => 'required|exists:users,id',
        'taskDescription' => 'required|min:5',
    ];

    /**
     * Se recibe el id del proyecto en la llamada al componente
     */
    public function mount(int $proyectoId): void
    {
        $this->proyecto = Proyecto::with(['tareas.staff', 'estados.usuario'])
                                  ->findOrFail($proyectoId);
    }

    /** Abre el modal de asignación */
    public function abrirModal(): void
    {
        $this->modalOpen = true;
    }

    /** Cierra el modal de asignación */
    public function cerrarModal(): void
    {
        $this->reset(['modalOpen', 'selectedUser', 'taskDescription']);
        $this->resetErrorBag();
    }

    /** Asigna la tarea al diseñador seleccionado */
    public function asignarTarea(): void
    {
        $this->validate();

        // 1. Validación extra: que no exista ya una tarea activa para este proyecto y diseñador
        $existe = Tarea::where('proyecto_id', $this->proyecto->id)
         
                    ->whereIn('estado',    ['PENDIENTE', 'EN PROCESO'])
                    ->exists();

        if ($existe) {
            $this->addError(
                'selectedUser',
                'Este diseñador ya tiene una tarea activa en el proyecto.'
            );
            return;
        }

        Tarea::create([
            'proyecto_id' => $this->proyecto->id,
            'staff_id'    => $this->selectedUser,
            'descripcion' => $this->taskDescription,
            'estado'      => 'PENDIENTE',
        ]);


        $this->proyecto->update(['estado' => 'ASIGNADO']);

        // Registrar cambio en la bitácora de estados
        proyecto_estados::create([
            'proyecto_id' => $this->proyecto->id,
            'estado'      => 'Proyecto asignado a diseñador',
            'fecha_inicio'=> now(),
            'usuario_id'  => Auth::id(),
        ]);

        // Notificaciones
        $ruta = 'proyectos/' . $this->proyecto->id;
        $this->enviarNotificacion(Auth::id(),          'Asignaste la tarea del proyecto ' . $this->proyecto->id, $ruta);
        $this->enviarNotificacion($this->selectedUser, 'Tienes asignado el diseño del proyecto ID: ' . $this->proyecto->id, $ruta);
        $this->enviarNotificacion($this->proyecto->usuario_id, 'Cambio de estatus en proyecto: ' . $this->proyecto->id, $ruta);

        session()->flash('message', 'Tarea asignada exitosamente.');
        $this->dispatch('tareaAsignada');   // Usa dispatch en v3
        $this->cerrarModal();
    }

    /** Muestra el modal de historial con todos los estados */
    public function verMas(): void
    {
        $this->proyectoSeleccionado = Proyecto::with('estados.usuario')
                                             ->findOrFail($this->proyecto->id);
        $this->modalVerMas = true;
    }

    /** Cierra el modal de historial */
    public function cerrarModalVerMas(): void
    {
        $this->modalVerMas = false;
        $this->proyectoSeleccionado = null;
    }

    /** Helper para enviar notificaciones */
    protected function enviarNotificacion(?int $userId, string $mensaje, ?string $ruta = null): void
    {
        if ($user = User::find($userId)) {
            $liga = $ruta ? config('app.url') . '/' . $ruta : null;
            $user->notify(new \App\Notifications\NuevaNotificacion($mensaje, $liga));
        }
    }

    public function render()
    {
        // Recarga relaciones por si cambian en tiempo real
        $this->proyecto->load(['tareas.staff', 'estados.usuario']);

        return view('livewire.proyectos.tareas-diseno', [
            'disenadores' => User::whereHas('roles', function ($q) {
                                 $q->where('name', 'diseñador');
                             })->get(),
        ]);
    }
}


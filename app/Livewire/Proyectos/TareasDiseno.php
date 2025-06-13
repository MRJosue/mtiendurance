<?php

namespace App\Livewire\Proyectos;

use App\Models\proyecto_estados;
use Livewire\Component;
use App\Models\Proyecto;
use App\Models\Tarea;
use App\Models\User;
//use App\Models\ProyectoEstado;   // ajusta el nombre del modelo si es diferente
use Illuminate\Support\Facades\Auth;
use App\Notifications\NuevaNotificacion;

class TareasDiseno extends Component
{
    public Proyecto $proyecto;          // Proyecto actual
    public bool $modalOpen = false;     // Control del modal
    public ?int $selectedUser = null;   // Diseñador elegido
    public string $taskDescription = ''; // Descripción de la tarea

    protected $rules = [
        'selectedUser'     => 'required|exists:users,id',
        'taskDescription'  => 'required|min:5',
    ];

    /** Se recibe el id del proyecto en la llamada al componente */
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

    /** Cierra el modal y limpia datos */
    public function cerrarModal(): void
    {
        $this->reset(['modalOpen', 'selectedUser', 'taskDescription']);
        $this->resetErrorBag();
    }

    /** Asigna la tarea al diseñador seleccionado */
    public function asignarTarea(): void
    {
        $this->validate();

        // 1. Crear la tarea
        Tarea::create([
            'proyecto_id' => $this->proyecto->id,
            'staff_id'    => $this->selectedUser,
            'descripcion' => $this->taskDescription,
            'estado'      => 'PENDIENTE',
        ]);

        // 2. Actualizar estado del proyecto
        $this->proyecto->update(['estado' => 'ASIGNADO']);

        // 3. Registrar cambio en la bitácora de estados
        proyecto_estados::create([
            'proyecto_id' => $this->proyecto->id,
            'estado'      => 'Proyecto asignado a diseñador',
            'fecha_inicio'=> now(),
            'usuario_id'  => Auth::id(),
        ]);

        // 4. Notificaciones
        $ruta = 'proyectos/' . $this->proyecto->id;
        $this->enviarNotificacion(Auth::id(),            'Asignaste la tarea del proyecto ' . $this->proyecto->id, $ruta);
        $this->enviarNotificacion($this->selectedUser,   'Tienes asignado el diseño del proyecto ID: ' . $this->proyecto->id, $ruta);
        $this->enviarNotificacion($this->proyecto->usuario_id, 'Cambio de estatus en proyecto: ' . $this->proyecto->id, $ruta);

        // 5. Aviso flash y evento front-end
        session()->flash('message', 'Tarea asignada exitosamente.');
        $this->dispatch('tareaAsignada');   // Usa dispatch (v3)
        $this->cerrarModal();
    }

    /** Helper para centralizar las notificaciones */
    protected function enviarNotificacion(?int $userId, string $mensaje, ?string $ruta = null): void
    {
        if ($user = User::find($userId)) {
            $liga = $ruta ? config('app.url') . '/' . $ruta : null;
            $user->notify(new NuevaNotificacion($mensaje, $liga));
        }
    }

    public function render()
    {
        // Recarga relaciones por si cambiaron
        $this->proyecto->load(['tareas.staff', 'estados.usuario']);

        return view('livewire.proyectos.tareas-diseno', [
            'disenadores' => User::whereHas('roles', function ($query) {
                $query->where('name', 'diseñador');
            })->get()
        ]);
    }
}



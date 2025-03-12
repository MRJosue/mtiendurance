<?php

namespace App\Livewire\Disenio;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Proyecto;
use App\Models\User;
use App\Models\Tarea;
use App\Notifications\NuevaNotificacion;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;


class ManageDisenio extends Component
{
    use WithPagination;

    public $perPage = 20;
    public $selectedProjects = [];
    public $selectAll = false;
    public $modalOpen = false;
    public $selectedProject;
    public $selectedUser;
    public $taskDescription;

    protected $rules = [
        'selectedUser' => 'required|exists:users,id',
        'taskDescription' => 'required|min:5',
    ];

    protected $messages = [
        'selectedUser.required' => 'Debe seleccionar un usuario.',
        'selectedUser.exists' => 'El usuario seleccionado no es válido.',
        'taskDescription.required' => 'Debe ingresar una descripción.',
        'taskDescription.min' => 'La descripción debe tener al menos 5 caracteres.',
    ];

    public function updating($field)
    {
        if ($field === 'perPage') {
            $this->resetPage();
        }
    }

    public function updatedSelectAll($value)
    {
        $this->selectedProjects = $value ? Proyecto::pluck('id')->toArray() : [];
    }

    public function deleteSelected()
    {
        Proyecto::whereIn('id', $this->selectedProjects)->delete();
        $this->selectedProjects = [];
        $this->selectAll = false;
        session()->flash('message', 'Proyectos eliminados exitosamente.');
    }

    public function exportSelected()
    {
        session()->flash('message', 'Exportación completada.');
    }

    public function abrirModalAsignacion($projectId)
    {
        $this->selectedProject = Proyecto::find($projectId);
        if (!$this->selectedProject) {
            session()->flash('message', 'Error: Proyecto no encontrado.');
            return;
        }
        $this->modalOpen = true;
    }

    public function asignarTarea()
    {
        $this->validate();

        Tarea::create([
            'proyecto_id' => $this->selectedProject->id,
            'staff_id' => $this->selectedUser,
            'descripcion' => $this->taskDescription,
            'estado' => 'PENDIENTE',
        ]);


        // Cambia el estatus del proyecto a asignado

        // Dispara el evento de creacion de estatus de proyecto

        // Enviamos notificacion al usuario para la asignacion 
    
        $ruta = 'proyectos/'.$this->selectedProject->id.'';
        $this -> enviarNotificacion(Auth::id(),'Asignaste la tarea del prollecto'.$this->selectedProject->id.' ', $ruta);
        $this -> enviarNotificacion($this->selectedUser,'Tienes asignado el diseño del proyecto ID:'.$this->selectedProject->id.' ', $ruta);
        session()->flash('message', 'Tarea asignada exitosamente.');
        $this->cerrarModal();
    }

    public function enviarNotificacion($userId = null, $mensaje = "Tienes una nueva notificación.", $ruta = null)
    {
        $user = $userId ? User::find($userId) : Auth::user(); // Si no se proporciona un ID, usa el usuario autenticado
        
        $dominioBase = config('app.url'); // Obtiene la URL base de la aplicación desde config
        $liga = $ruta ? $dominioBase . $ruta : null; // Construye la URL completa
    
        if ($user) {
            $user->notify(new NuevaNotificacion($mensaje, $liga));
            $this->dispatch('notificacionEnviada');
        } else {
            // Log::warning("Intento de enviar notificación a un usuario inexistente.", ['user_id' => $userId]);
        }
    }

    public function cerrarModal()
    {
        $this->modalOpen = false;
        $this->selectedUser = null;
        $this->taskDescription = '';
        $this->resetErrorBag(); // Reiniciar los errores al cerrar el modal
    }

    public function render()
    {
        return view('livewire.disenio.manage-disenio', [
            'projects' => Proyecto::with(['user', 'tareas.staff'])->paginate($this->perPage),
            'users' => User::all()
        ]);
    }
}

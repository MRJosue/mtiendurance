<?php

namespace App\Livewire\Dashboard\DisenioPanel;

use Livewire\Component;

use Livewire\WithPagination;
use App\Models\Proyecto;
use App\Models\User;
use App\Models\Tarea;
use App\Models\proyecto_estados;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NuevaNotificacion;

class Adminproyectos extends Component
{
    use WithPagination;

    public $perPage = 20;
    public $selectedProjects = [];
    public $selectAll = false;
    public $modalOpen = false;
    public $selectedProject;
    public $selectedUser;
    public $taskDescription;
    public $modalVerMas = false;
    public $proyectoSeleccionado = null;

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

    public function abrirModalAsignacion($projectId)
    {
        $this->selectedProject = Proyecto::find($projectId);
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

        $proyecto = Proyecto::find($this->selectedProject->id);
        if ($proyecto) {
            $proyecto->estado = 'ASIGNADO';
            $proyecto->save();
        }

        proyecto_estados::create([
            'proyecto_id' => $this->selectedProject->id,
            'estado' => "Proyecto asignado a diseñador",
            'fecha_inicio' => now(),
            'usuario_id' => Auth::id(),
        ]);

        $ruta = 'proyectos/' . $this->selectedProject->id;
        $this->enviarNotificacion(Auth::id(), 'Asignaste la tarea del proyecto ' . $this->selectedProject->id, $ruta);
        $this->enviarNotificacion($this->selectedUser, 'Tienes asignado el diseño del proyecto ID: ' . $this->selectedProject->id, $ruta);
        $this->enviarNotificacion($proyecto->usuario_id, 'Cambio de estatus en proyecto: ' . $this->selectedProject->id, $ruta);

        session()->flash('message', 'Tarea asignada exitosamente.');
        $this->cerrarModal();
    }

    public function enviarNotificacion($userId = null, $mensaje = "Tienes una nueva notificación.", $ruta = null)
    {
        $user = $userId ? User::find($userId) : Auth::user();
        $liga = $ruta ? config('app.url') . '/' . $ruta : null;
        if ($user) {
            $user->notify(new NuevaNotificacion($mensaje, $liga));
            $this->dispatch('notificacionEnviada');
        }
    }

    public function cerrarModal()
    {
        $this->modalOpen = false;
        $this->selectedUser = null;
        $this->taskDescription = '';
        $this->resetErrorBag();
    }

    public function verMas($proyectoId)
    {
        $this->proyectoSeleccionado = Proyecto::with('estados.usuario')->find($proyectoId);
        $this->modalVerMas = true;
    }

    public function cerrarModalVerMas()
    {
        $this->modalVerMas = false;
        $this->proyectoSeleccionado = null;
    }

    public function render()
    {
        return view('livewire.dashboard.disenio-panel.adminproyectos', [
            'projects' => Proyecto::with(['user', 'tareas.staff', 'estados.usuario'])->paginate($this->perPage),
            'users' => User::whereHas('roles', function ($q) {
                $q->where('name', 'diseñador');
            })->get()
        ]);
    }
}
<?php

namespace App\Livewire\Disenio;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Proyecto;
use App\Models\User;
use App\Models\Tarea;
use Illuminate\Validation\ValidationException;

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

        session()->flash('message', 'Tarea asignada exitosamente.');
        $this->cerrarModal();
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

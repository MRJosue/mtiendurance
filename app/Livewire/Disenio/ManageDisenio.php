<?php

namespace App\Livewire\Disenio;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Proyecto;
use App\Models\User;
use App\Models\Tarea;

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

    public function updating($field)
    {
        if ($field === 'perPage') {
            $this->resetPage();
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedProjects = Proyecto::pluck('id')->toArray();
        } else {
            $this->selectedProjects = [];
        }
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
        $this->modalOpen = true;
    }

    public function asignarTarea()
    {
        $this->validate([
            'selectedUser' => 'required',
            'taskDescription' => 'required|min:5',
        ]);

        Tarea::create([
            'proyecto_id' => $this->selectedProject->id,
            'staff_id' => $this->selectedUser,
            'descripcion' => $this->taskDescription,
            'estado' => 'PENDIENTE',
        ]);

        session()->flash('message', 'Tarea asignada exitosamente.');
        $this->modalOpen = false;
        $this->selectedUser = null;
        $this->taskDescription = '';
    }

    public function render()
    {
        return view('livewire.disenio.manage-disenio', [
            'projects' => Proyecto::with(['user', 'pedidos.producto.categoria', 'tareas.staff'])->paginate($this->perPage),
            'users' => User::all()
        ]);
    }
}

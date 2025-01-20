<?php

namespace App\Livewire\Preproyectos;


use Livewire\Component;
use Livewire\WithPagination; // Para la paginación
use App\Models\PreProyecto;

class ManagePreProjects extends Component
{
    use WithPagination;

    public $perPage = 10; // Cantidad de registros por página
    public $selectedProjects = [];
    public $selectAll = false;

    // Actualizar la página cuando se modifique el número de registros por página
    public function updating($field)
    {
        if ($field === 'perPage') {
            $this->resetPage();
        }
    }

    // Manejar la selección de todos los proyectos
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedProjects = PreProyecto::pluck('id')->toArray();
        } else {
            $this->selectedProjects = [];
        }
    }

    // Eliminar proyectos seleccionados
    public function deleteSelected()
    {
        PreProyecto::whereIn('id', $this->selectedProjects)->delete();
        $this->selectedProjects = [];
        $this->selectAll = false;
        session()->flash('message', 'Preproyectos eliminados exitosamente.');
    }

    // Exportar proyectos seleccionados
    public function exportSelected()
    {
        // Lógica de exportación
        session()->flash('message', 'Exportación completada.');
    }

    public function render()
    {
        return view('livewire.preproyectos.manage-pre-projects', [
            'projects' => PreProyecto::with('user')->paginate($this->perPage)
        ]);
    }
}


//return view('livewire.preproyectos.manage-pre-projects');
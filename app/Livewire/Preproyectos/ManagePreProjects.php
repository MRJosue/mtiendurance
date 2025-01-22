<?php

namespace App\Livewire\Preproyectos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Proyecto;

class ManagePreProjects extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $selectedProjects = [];
    public $selectAll = false;

    public function updating($field)
    {
        if ($field === 'perPage') {
            $this->resetPage();
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedProjects = Proyecto::where('estado', 'PENDIENTE')
                                              ->where('usuario_id', auth()->id())
                                              ->pluck('id')
                                              ->toArray();
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
        // Lógica de exportación (puedes implementar la exportación específica aquí)
        session()->flash('message', 'Exportación completada.');
    }

    public function render()
    {
        return view('livewire.preproyectos.manage-pre-projects', [
            'projects' => Proyecto::with(['user'])
                                  ->where('estado', 'PENDIENTE')
                                  ->where('usuario_id', auth()->id())
                                  ->paginate($this->perPage)
        ]);
    }
}


//return view('livewire.preproyectos.manage-pre-projects');
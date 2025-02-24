<?php

namespace App\Livewire\Preproyectos;


use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PreProyecto;

class ManagePreprojects extends Component
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
            $this->selectedProjects = PreProyecto::where('estado', 'PENDIENTE')
                                              ->where('usuario_id', auth()->id())
                                              ->pluck('id')
                                              ->toArray();
        } else {
            $this->selectedProjects = [];
        }
    }

    public function deleteSelected()
    {
        PreProyecto::whereIn('id', $this->selectedProjects)->delete();
        $this->selectedProjects = [];
        $this->selectAll = false;
        session()->flash('message', 'Preproyectos eliminados exitosamente.');
    }

    public function exportSelected()
    {
        // Lógica de exportación (puedes implementar la exportación específica aquí)
        session()->flash('message', 'Exportación completada.');
    }

    public function render()
    {
        return view('livewire.preproyectos.manage-preprojects', [
            'projects' => PreProyecto::with(['user'])
                                  ->where('estado', 'PENDIENTE')
                                  ->where('usuario_id', auth()->id())
                                  ->paginate($this->perPage)
        ]);
    }
}


//return view('livewire.preproyectos.manage-pre-projects');
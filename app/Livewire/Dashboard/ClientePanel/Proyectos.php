<?php

namespace App\Livewire\Dashboard\ClientePanel;

use Livewire\Component;

use Livewire\WithPagination;
use App\Models\Proyecto;

class Proyectos extends Component
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
            $this->selectedProjects = Proyecto::where('usuario_id', auth()->id())
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
        session()->flash('message', 'Exportación completada.');
    }

    public function render()
    {
        return view('livewire.dashboard.cliente-panel.proyectos', [
            'projects' => Proyecto::with(['user', 'pedidos.producto.categoria'])
                                  ->where('usuario_id', auth()->id())
                                  ->paginate($this->perPage)
        ]);
    }
}
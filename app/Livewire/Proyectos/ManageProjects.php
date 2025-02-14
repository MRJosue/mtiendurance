<?php

namespace App\Livewire\Proyectos;


use Livewire\Component;
use Livewire\WithPagination; // Importar el trait para paginación
use App\Models\Proyecto;

class ManageProjects extends Component
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
        // Lógica de exportación
        session()->flash('message', 'Exportación completada.');
    }
    
    public function render()
    {
        return view('livewire.proyectos.manage-projects', [
            'projects' => Proyecto::with(['user', 'pedidos.producto.categorias'])->paginate($this->perPage)
        ]);
    }
}

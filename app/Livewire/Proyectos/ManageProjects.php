<?php

namespace App\Livewire\Proyectos;


use Livewire\Component;
use Livewire\WithPagination; // Importar el trait para paginación
use App\Models\Proyecto;

class ManageProjects extends Component
{
    use WithPagination;

    public $perPage = 20;
    public $selectedProjects = [];
    public $selectAll = false;
    public $mostrarFiltros = false;
    
    public $estadosSeleccionados = ['PENDIENTE', 'ASIGNADO', 'EN PROCESO', 'REVISION'];

        public $estados = [
        'PENDIENTE', 'ASIGNADO', 'EN PROCESO','REVISION', 'DISEÑO APROBADO'
    ];

        public function buscarPorFiltros()
        {
            $this->resetPage();
        }

        
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
        $query = Proyecto::with(['user', 'pedidos.producto.categoria']);

        if (!auth()->user()->can('tablaProyectos-ver-todos-los-proyectos')) {
            $query->where('usuario_id', auth()->id());
        }

        if (!empty($this->estadosSeleccionados)) {
            $query->whereIn('estado', $this->estadosSeleccionados);
        }

        return view('livewire.proyectos.manage-projects', [
            'projects' => $query->paginate($this->perPage)
        ]);
    }
}

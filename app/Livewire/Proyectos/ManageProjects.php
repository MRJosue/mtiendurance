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


        /* ---------- Props UI ---------- */
    public array  $tabs      = ['PENDIENTE', 'ASIGNADO', 'EN PROCESO', 'REVISION', 'DISEÑO APROBADO'];
    public string $activeTab = 'PENDIENTE';              // tab inicial
 

    
    // public $estadosSeleccionados = ['PENDIENTE', 'ASIGNADO', 'EN PROCESO', 'REVISION'];
    public $estadosSeleccionados =[];

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


    public function exportSelected()
    {
        // Lógica de exportación
        session()->flash('message', 'Exportación completada.');
    }


        public function updatedSelectAll(bool $value): void
    {
        $this->selectedProjects = $value
            ? Proyecto::where('estado', $this->activeTab)->pluck('id')->toArray()
            : [];
    }

    public function deleteSelected(): void
    {
        Proyecto::whereIn('id', $this->selectedProjects)->delete();
        $this->reset(['selectedProjects', 'selectAll']);
        $this->dispatch('banner', ['message' => 'Proyectos eliminados exitosamente.']);
    }



    public function mount(): void
    {
        // Por si algún rol arranca en otra pestaña
        if (!in_array($this->activeTab, $this->tabs, true)) {
            $this->activeTab = $this->tabs[0];
        }
    }
    
    /* ---------- UI handlers ---------- */
    public function setTab(string $tab): void
    {
        if ($this->activeTab !== $tab) {
            $this->activeTab     = $tab;
            $this->selectAll     = false;
            $this->selectedProjects = [];
            $this->resetPage();
        }
    }

        public function updatingPerPage(): void
    {
        $this->resetPage();
    }




    public function render()
    {
        $query = Proyecto::with(['user', 'pedidos.producto.categoria'])
                  ->where('estado', $this->activeTab);

        if (!auth()->user()->can('tablaProyectos-ver-todos-los-proyectos')) {
            $query->where('usuario_id', auth()->id());
        }

        return view('livewire.proyectos.manage-projects', [
            'projects' => $query->paginate($this->perPage),
        ]);
    }
}

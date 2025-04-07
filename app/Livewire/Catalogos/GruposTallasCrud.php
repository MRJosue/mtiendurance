<?php

namespace App\Livewire\Catalogos;


use Livewire\Component;
use Livewire\WithPagination;
use App\Models\GrupoTalla;
use App\Models\Talla;

use Illuminate\Support\Facades\Log;

class GruposTallasCrud extends Component
{
    use WithPagination;

    public $nombre, $grupo_id, $selectedTallas = [];
    public $modalOpen = false;
    public $confirmingDelete = false;

    public $filtroActivo = '1';
    public $ind_activo = true;

    public $search = '';
    public $query = '';


    protected $rules = [
        'nombre' => 'required|string|max:255',
        'selectedTallas' => 'array'
    ];

    public function render()
    {
        $grupos = GrupoTalla::with('tallas')
        ->where('ind_activo', $this->filtroActivo)
        ->when($this->search, function ($query) {
            $query->where('nombre', 'like', '%' . $this->search . '%');
        })
        ->orderBy('nombre')
        ->paginate(15);

        return view('livewire.catalogos.grupos-tallas-crud', [
            'grupos' => $grupos,
            'tallasDisponibles' => Talla::where('ind_activo', 1)->orderBy('nombre')->get()
        ]);
    }

    public function buscar()
    {
        $this->search = $this->query;
        $this->resetPage();
    }

    public function openModal()
    {

        $this->reset(['nombre', 'grupo_id', 'selectedTallas']);
      
        $this->modalOpen = true;
    }

    public function edit(GrupoTalla $grupo)
    {
        $this->grupo_id = $grupo->id;
        $this->nombre = $grupo->nombre;

        $this->selectedTallas = $grupo->tallas()->pluck('tallas.id')->toArray();

        $this->ind_activo = (bool) $grupo->ind_activo;

        $this->modalOpen = true;
    }

    public function save()
    {
        $this->validate();

        $grupo = GrupoTalla::updateOrCreate(['id' => $this->grupo_id], [
            'nombre' => $this->nombre,
            'ind_activo' => $this->ind_activo,
        ]);
    
        // 🔍 Validar tallas activas seleccionadas
        $tallasValidas = Talla::whereIn('id', $this->selectedTallas)
            ->where('ind_activo', 1)
            ->pluck('id')
            ->toArray();
    
        // 💣 Limpieza previa y sincronización
        $grupo->tallas()->detach();
        $grupo->tallas()->sync($tallasValidas);
    
        $this->dispatch('alert', ['message' => 'Grupo de tallas guardado correctamente.']);
        $this->modalOpen = false;
    }

    public function confirmDelete(GrupoTalla $grupo)
    {
        $this->grupo_id = $grupo->id;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        GrupoTalla::findOrFail($this->grupo_id)->update(['ind_activo' => 0]);
        $this->dispatch('alert', ['message' => 'Grupo de tallas desactivado correctamente.']);
        $this->confirmingDelete = false;
    }
}
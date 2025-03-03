<?php

namespace App\Livewire\Catalogos;


use Livewire\Component;
use Livewire\WithPagination;
use App\Models\GrupoTalla;
use App\Models\Talla;

class GruposTallasCrud extends Component
{
    use WithPagination;

    public $nombre, $grupo_id, $selectedTallas = [];
    public $modalOpen = false;
    public $confirmingDelete = false;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'selectedTallas' => 'array'
    ];

    public function render()
    {
        return view('livewire.catalogos.grupos-tallas-crud', [
            'grupos' => GrupoTalla::with('tallas')->orderBy('nombre')->paginate(10),
            'tallasDisponibles' => Talla::orderBy('nombre')->get()
        ]);
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
        $this->modalOpen = true;
    }

    public function save()
    {
        $this->validate();

        $grupo = GrupoTalla::updateOrCreate(['id' => $this->grupo_id], [
            'nombre' => $this->nombre,
        ]);

        // Sincronizar tallas seleccionadas
        $grupo->tallas()->sync($this->selectedTallas);

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
        GrupoTalla::findOrFail($this->grupo_id)->delete();
        $this->dispatch('alert', ['message' => 'Grupo de tallas eliminado correctamente.']);
        $this->confirmingDelete = false;
    }
}
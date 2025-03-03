<?php

namespace App\Livewire\Catalogos;


use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Talla;

class TallasCrud extends Component
{
    use WithPagination;

    public $nombre, $descripcion, $talla_id;
    public $modalOpen = false;
    public $confirmingDelete = false;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
    ];

    public function render()
    {
        return view('livewire.catalogos.tallas-crud', [
            'tallas' => Talla::orderBy('nombre')->paginate(10),
        ]);
    }

    public function openModal()
    {
        $this->reset(['nombre', 'descripcion', 'talla_id']);
        $this->modalOpen = true;
    }

    public function edit(Talla $talla)
    {
        $this->talla_id = $talla->id;
        $this->nombre = $talla->nombre;
        $this->descripcion = $talla->descripcion;
        $this->modalOpen = true;
    }

    public function save()
    {
        $this->validate();

        Talla::updateOrCreate(['id' => $this->talla_id], [
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
        ]);

        $this->dispatch('alert', ['message' => 'Talla guardada correctamente.']);
        $this->modalOpen = false;
    }

    public function confirmDelete(Talla $talla)
    {
        $this->talla_id = $talla->id;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        Talla::findOrFail($this->talla_id)->delete();
        $this->dispatch('alert', ['message' => 'Talla eliminada correctamente.']);
        $this->confirmingDelete = false;
    }
}

//  return view('livewire.catalogos.tallas-crud');
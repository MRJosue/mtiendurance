<?php

namespace App\Livewire\Catalogos;



use App\Models\Estado;
use App\Models\Pais;
use Livewire\Component;
use Livewire\WithPagination;



class CrudEstado extends Component
{
    use WithPagination;

    public $nombre;
    public $pais_id;
    public $estadoId;
    public $isEditMode = false;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'pais_id' => 'required|exists:paises,id',
    ];

    public function render()
    {
        return view('livewire.catalogos.crud-estado', [
            'estados' => Estado::with('pais')->paginate(10),
            'paises' => Pais::all(),
        ]);
    }

    public function resetFields()
    {
        $this->nombre = '';
        $this->pais_id = null;
        $this->estadoId = null;
        $this->isEditMode = false;
    }

    public function store()
    {
        $this->validate();

        Estado::create([
            'nombre' => $this->nombre,
            'pais_id' => $this->pais_id,
        ]);

        session()->flash('message', 'Estado creado exitosamente.');

        $this->resetFields();
    }

    public function edit($id)
    {
        $estado = Estado::findOrFail($id);
        $this->estadoId = $estado->id;
        $this->nombre = $estado->nombre;
        $this->pais_id = $estado->pais_id;
        $this->isEditMode = true;
    }

    public function update()
    {
        $this->validate();

        $estado = Estado::findOrFail($this->estadoId);
        $estado->update([
            'nombre' => $this->nombre,
            'pais_id' => $this->pais_id,
        ]);

        session()->flash('message', 'Estado actualizado exitosamente.');

        $this->resetFields();
    }

    public function delete($id)
    {
        Estado::findOrFail($id)->delete();

        session()->flash('message', 'Estado eliminado exitosamente.');
    }
}


//return view('livewire.catalogos.crud-estado');
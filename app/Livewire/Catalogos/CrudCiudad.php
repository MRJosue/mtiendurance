<?php

namespace App\Livewire\Catalogos;


use App\Models\Ciudad;
use App\Models\Estado;
use App\Models\TipoEnvio;
use Livewire\Component;
use Livewire\WithPagination;

class CrudCiudad extends Component
{
    use WithPagination;

    public $nombre;
    public $estado_id;
    public $selectedTiposEnvio = [];
    public $ciudadId;
    public $isEditMode = false;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'estado_id' => 'required|exists:estados,id',
        'selectedTiposEnvio' => 'array',
        'selectedTiposEnvio.*' => 'exists:tipo_envio,id',
    ];

    public function render()
    {
        return view('livewire.catalogos.crud-ciudad', [
            'ciudades' => Ciudad::with(['estado', 'tipoEnvios'])->paginate(10),
            'estados' => Estado::all(),
            'tiposEnvio' => TipoEnvio::all(),
        ]);
    }

    public function resetFields()
    {
        $this->nombre = '';
        $this->estado_id = null;
        $this->selectedTiposEnvio = [];
        $this->ciudadId = null;
        $this->isEditMode = false;
    }

    public function store()
    {
        $this->validate();

        $ciudad = Ciudad::create([
            'nombre' => $this->nombre,
            'estado_id' => $this->estado_id,
        ]);

        $ciudad->syncTiposEnvio($this->selectedTiposEnvio);

        session()->flash('message', 'Ciudad creada exitosamente.');

        $this->resetFields();
    }

    public function edit($id)
    {
        $ciudad = Ciudad::with('tipoEnvios')->findOrFail($id);
        $this->ciudadId = $ciudad->id;
        $this->nombre = $ciudad->nombre;
        $this->estado_id = $ciudad->estado_id;
        $this->selectedTiposEnvio = $ciudad->tipoEnvios->pluck('id')->toArray();
        $this->isEditMode = true;
    }

    public function update()
    {
        $this->validate();

        $ciudad = Ciudad::findOrFail($this->ciudadId);
        $ciudad->update([
            'nombre' => $this->nombre,
            'estado_id' => $this->estado_id,
        ]);

        $ciudad->syncTiposEnvio($this->selectedTiposEnvio);

        session()->flash('message', 'Ciudad actualizada exitosamente.');

        $this->resetFields();
    }

    public function delete($id)
    {
        Ciudad::findOrFail($id)->delete();

        session()->flash('message', 'Ciudad eliminada exitosamente.');
    }
}


//    return view('livewire.catalogos.crud-ciudad');
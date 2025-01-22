<?php

namespace App\Livewire\Catalogos;

use Livewire\Component;


use App\Models\TipoEnvio;
use Livewire\WithPagination;

class CrudTipoEnvio extends Component
{
    use WithPagination;

    public $nombre;
    public $descripcion;
    public $dias_envio;
    public $tipoEnvioId;
    public $isEditMode = false;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'dias_envio' => 'required|integer|min:0',
    ];

    public function render()
    {
        return view('livewire.catalogos.crud-tipo-envio', [
            'tiposEnvio' => TipoEnvio::paginate(10),
        ]);
    }

    public function resetFields()
    {
        $this->nombre = '';
        $this->descripcion = '';
        $this->dias_envio = null;
        $this->tipoEnvioId = null;
        $this->isEditMode = false;
    }

    public function store()
    {
        $this->validate();

        TipoEnvio::create([
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'dias_envio' => $this->dias_envio,
        ]);

        session()->flash('message', 'Tipo de envío creado exitosamente.');

        $this->resetFields();
    }

    public function edit($id)
    {
        $tipoEnvio = TipoEnvio::findOrFail($id);
        $this->tipoEnvioId = $tipoEnvio->id;
        $this->nombre = $tipoEnvio->nombre;
        $this->descripcion = $tipoEnvio->descripcion;
        $this->dias_envio = $tipoEnvio->dias_envio;
        $this->isEditMode = true;
    }

    public function update()
    {
        $this->validate();

        $tipoEnvio = TipoEnvio::findOrFail($this->tipoEnvioId);
        $tipoEnvio->update([
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'dias_envio' => $this->dias_envio,
        ]);

        session()->flash('message', 'Tipo de envío actualizado exitosamente.');

        $this->resetFields();
    }

    public function delete($id)
    {
        TipoEnvio::findOrFail($id)->delete();

        session()->flash('message', 'Tipo de envío eliminado exitosamente.');
    }
}


//  return view('livewire.catalogos.crud-tipo-envio');
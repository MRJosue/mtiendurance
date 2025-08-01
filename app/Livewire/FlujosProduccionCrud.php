<?php

namespace App\Livewire;

use Livewire\Component;

use App\Models\FlujoProduccion;

class FlujosProduccionCrud extends Component
{
    public $flujos;
    public $nombre;
    public $descripcion;
    public $config;

    public $selectedFlujos = [];
    public $selectAll = false;

    public $modalOpen = false;
    public $editMode = false;
    public $flujoId;

    protected $rules = [
        'nombre'      => 'required|string|max:100',
        'descripcion' => 'nullable|string',
        'config'      => 'required|json',
    ];

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedFlujos = FlujoProduccion::pluck('id')->toArray();
        } else {
            $this->selectedFlujos = [];
        }
    }

    public function abrirModal($id = null)
    {
        $this->resetValidation();

        if ($id) {
            $this->editMode = true;
            $flujo = FlujoProduccion::findOrFail($id);
            $this->flujoId      = $id;
            $this->nombre       = $flujo->nombre;
            $this->descripcion  = $flujo->descripcion;
            $this->config = is_string($flujo->config) ? $flujo->config : json_encode($flujo->config, JSON_PRETTY_PRINT);

        } else {
            $this->editMode = false;
            $this->reset(['flujoId', 'nombre', 'descripcion', 'config']);
        }

        $this->modalOpen = true;
    }

    public function guardar()
    {
        $this->validate();

        $data = [
            'nombre'      => $this->nombre,
            'descripcion' => $this->descripcion,
            'config'      => json_decode($this->config, true),
        ];

        if ($this->editMode && $this->flujoId) {
            FlujoProduccion::find($this->flujoId)->update($data);
            $this->dispatch('notify', ['message' => 'Flujo actualizado correctamente']);
        } else {
            FlujoProduccion::create($data);
            $this->dispatch('notify', ['message' => 'Flujo creado correctamente']);
        }

        $this->modalOpen = false;
        $this->reset(['nombre', 'descripcion', 'config', 'flujoId', 'editMode']);
    }

    public function deleteSelected()
    {
        FlujoProduccion::destroy($this->selectedFlujos);
        $this->selectedFlujos = [];
        $this->selectAll = false;
    }

    public function delete($id)
    {
        FlujoProduccion::find($id)->delete();
    }

    public function render()
    {
        $this->flujos = FlujoProduccion::all();
        return view('livewire.flujos-produccion-crud', [
            'flujos' => $this->flujos,
        ]);
    }
}

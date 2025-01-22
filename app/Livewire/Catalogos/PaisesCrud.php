<?php

namespace App\Livewire\Catalogos;

use Livewire\Component;
use App\Models\Pais;
use Livewire\WithPagination;

class PaisesCrud extends Component
{
    use WithPagination;

    public $nombre;
    public $paisId;
    public $isEditMode = false;

    protected $rules = [
        'nombre' => 'required|string|max:255',
    ];

    public function render()
    {
        return view('livewire.catalogos.paises-crud', [
            'paises' => Pais::paginate(10),
        ]);
    }

    public function resetFields()
    {
        $this->nombre = '';
        $this->paisId = null;
        $this->isEditMode = false;
    }

    public function store()
    {
        $this->validate();

        Pais::create(['nombre' => $this->nombre]);

        session()->flash('message', 'País creado exitosamente.');

        $this->resetFields();
    }

    public function edit($id)
    {
        $pais = Pais::findOrFail($id);
        $this->paisId = $pais->id;
        $this->nombre = $pais->nombre;
        $this->isEditMode = true;
    }

    public function update()
    {
        $this->validate();

        $pais = Pais::findOrFail($this->paisId);
        $pais->update(['nombre' => $this->nombre]);

        session()->flash('message', 'País actualizado exitosamente.');

        $this->resetFields();
    }

    public function delete($id)
    {
        Pais::findOrFail($id)->delete();

        session()->flash('message', 'País eliminado exitosamente.');
    }

    public function getFormAction(): string
        {
            return $this->isEditMode ? 'update' : 'store';
        }
}


//  return view('livewire.catalogos.paises-crud');
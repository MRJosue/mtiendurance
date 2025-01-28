<?php

namespace App\Livewire\Catalogos;

use Livewire\Component;
use App\Models\Caracteristica;
use Livewire\WithPagination;

class CaracteristicaCrud extends Component
{
    use WithPagination;

    public $nombre;
    public $caracteristica_id;
    public $modal = false;
    public $search = '';
    public $query = '';
    protected $paginationTheme = 'tailwind';

    protected $rules = [
        'nombre' => 'required|string|max:255',
    ];

    public function buscar()
    {
        $this->search = $this->query;
        $this->resetPage();
    }

    public function render()
    {
        $query = Caracteristica::query();

        if (!empty($this->search)) {
            $query->where('nombre', 'like', '%' . $this->search . '%');
        }

        return view('livewire.catalogos.caracteristica-crud', [
            'caracteristicas' => $query->orderBy('created_at', 'desc')->paginate(5),
        ]);
    }

    public function crear()
    {
        $this->limpiar();
        $this->abrirModal();
    }

    public function abrirModal()
    {
        $this->modal = true;
    }

    public function cerrarModal()
    {
        $this->modal = false;
    }

    public function limpiar()
    {
        $this->nombre = '';
        $this->caracteristica_id = null;
    }

    public function guardar()
    {
        $this->validate();

        if ($this->caracteristica_id) {
            // Actualizar la característica existente
            $caracteristica = Caracteristica::findOrFail($this->caracteristica_id);
            $caracteristica->update(['nombre' => $this->nombre]);
            session()->flash('message', '¡Característica actualizada exitosamente!');
        } else {
            // Crear una nueva característica
            Caracteristica::create(['nombre' => $this->nombre]);
            session()->flash('message', '¡Característica creada exitosamente!');
        }

        $this->cerrarModal();
        $this->limpiar();
    }

    public function editar($id)
    {
        $caracteristica = Caracteristica::findOrFail($id);
        $this->caracteristica_id = $caracteristica->id;
        $this->nombre = $caracteristica->nombre;

        $this->abrirModal();
    }

    public function borrar($id)
    {
        $caracteristica = Caracteristica::find($id);

        if ($caracteristica) {
            $caracteristica->delete();
            session()->flash('message', 'Característica eliminada exitosamente.');
        }
    }
}

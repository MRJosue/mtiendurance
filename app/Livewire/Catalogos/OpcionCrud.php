<?php

namespace App\Livewire\Catalogos;

use Livewire\Component;
use App\Models\Opcion;
use App\Models\Caracteristica;
use Livewire\WithPagination;

class OpcionCrud extends Component
{
    use WithPagination;

    public $valor;
    public $caracteristica_id;
    public $opcion_id;
    public $modal = false;
    public $search; // La búsqueda efectiva
    public $query;  // Lo que el usuario está escribiendo en el input
    protected $paginationTheme = 'tailwind';

    protected $rules = [
        'valor' => 'required|string|max:255',
        'caracteristica_id' => 'required|exists:caracteristicas,id',
    ];

    public function buscar()
    {
        // Cuando se presione el botón Buscar, se aplicará el filtro
        $this->search = $this->query;
        $this->resetPage();
    }

    public function render()
    {


        $opciones = Opcion::with('caracteristica')
            ->where('valor', 'like', '%'.$this->search.'%')
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return view('livewire.catalogos.opcion-crud', [
            'opciones' => $opciones,
            'caracteristicas' => Caracteristica::orderBy('nombre')->get(),
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
        $this->valor = '';
        $this->caracteristica_id = '';
        $this->opcion_id = null;
    }

    public function guardar()
    {
        $this->validate();

        if ($this->opcion_id) {
            $opcion = Opcion::findOrFail($this->opcion_id);
            $opcion->update([
                'valor' => $this->valor,
                'caracteristica_id' => $this->caracteristica_id,
            ]);
            session()->flash('message', '¡Opción actualizada exitosamente!');
        } else {
            Opcion::create([
                'valor' => $this->valor,
                'caracteristica_id' => $this->caracteristica_id,
            ]);
            session()->flash('message', '¡Opción creada exitosamente!');
        }

        $this->cerrarModal();
        $this->limpiar();
    }

    public function editar($id)
    {
        $opcion = Opcion::findOrFail($id);
        $this->opcion_id = $opcion->id;
        $this->valor = $opcion->valor;
        $this->caracteristica_id = $opcion->caracteristica_id;
        $this->abrirModal();
    }

    public function borrar($id)
    {
        Opcion::find($id)->delete();
        session()->flash('message', 'Opción eliminada exitosamente.');
    }
}
// return view('livewire.catalogos.opcion-crud');

<?php

namespace App\Livewire\Catalogos;

use Livewire\Component;
use App\Models\Caracteristica;
use App\Models\Producto;
use Livewire\WithPagination;


class CaracteristicaCrud extends Component
{
    use WithPagination;

    public $nombre;
    public $producto_id;
    public $caracteristica_id;
    public $modal = false;
    public $search = ''; // La búsqueda efectiva
    public $query = '';  // Lo que el usuario está escribiendo en el input
    protected $paginationTheme = 'tailwind';

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'producto_id' => 'required|exists:productos,id',
    ];

    public function buscar()
    {
        // Cuando se presione el botón Buscar, se aplicará el filtro
        $this->search = $this->query;
        $this->resetPage();
    }



    public function render()
    {


        $query = Caracteristica::with('producto');

        // Aplicar el filtro de búsqueda si se ha proporcionado un término
        if (!empty($this->search)) {
            $query->where('nombre', 'like', '%' . $this->search . '%');
        }

        return view('livewire.catalogos.caracteristica-crud', [
            'caracteristicas' => $query->orderBy('created_at', 'desc')->paginate(5),
            'productos' => Producto::orderBy('nombre')->get(),
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
        $this->producto_id = '';
        $this->caracteristica_id = null;
    }

    public function guardar()
    {
        $this->validate();

        if ($this->caracteristica_id) {
            $caracteristica = Caracteristica::findOrFail($this->caracteristica_id);
            $caracteristica->update([
                'nombre' => $this->nombre,
                'producto_id' => $this->producto_id,
            ]);
            session()->flash('message', '¡Característica actualizada exitosamente!');
        } else {
            Caracteristica::create([
                'nombre' => $this->nombre,
                'producto_id' => $this->producto_id,
            ]);
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
        $this->producto_id = $caracteristica->producto_id;
        $this->abrirModal();
    }

    public function borrar($id)
    {
        Caracteristica::find($id)->delete();
        session()->flash('message', 'Característica eliminada exitosamente.');
    }
}

//      return view('livewire.catalogos.caracteristica-crud');

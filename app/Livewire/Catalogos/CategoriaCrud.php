<?php

namespace App\Livewire\Catalogos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Categoria;
use App\Models\Caracteristica;

class CategoriaCrud extends Component
{
    use WithPagination;

    public $nombre;
    public $categoria_id;
    public $flag_tallas = false;
    public $modal = false;
    public $search = '';
    public $query = '';
    public $caracteristicasSeleccionadas = []; // Nueva propiedad

    protected $paginationTheme = 'tailwind';

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'flag_tallas' => 'boolean',
        'caracteristicasSeleccionadas' => 'array', // Validación para el array de características
    ];

    public function buscar()
    {
        $this->search = $this->query;
        $this->resetPage();
    }

    public function render()
    {
        $categorias = Categoria::where('nombre', 'like', '%'.$this->search.'%')
            ->orderBy('created_at', 'desc')
            ->paginate(8);

        $caracteristicas = Caracteristica::all();

        return view('livewire.catalogos.categoria-crud', [
            'categorias' => $categorias,
            'caracteristicas' => $caracteristicas,
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
        $this->categoria_id = null;
        $this->flag_tallas = false;
        $this->caracteristicasSeleccionadas = [];
    }

    public function guardar()
    {
        $this->validate();

        if ($this->categoria_id) {
            $categoria = Categoria::findOrFail($this->categoria_id);
            $categoria->update([
                'nombre' => $this->nombre,
                'flag_tallas' => $this->flag_tallas,
            ]);
            session()->flash('message', '¡Categoría actualizada exitosamente!');
        } else {
            $categoria = Categoria::create([
                'nombre' => $this->nombre,
                'flag_tallas' => $this->flag_tallas,
            ]);
            session()->flash('message', '¡Categoría creada exitosamente!');
        }

        // Sincronizar características seleccionadas
        $categoria->caracteristicas()->sync($this->caracteristicasSeleccionadas);

        $this->cerrarModal();
        $this->limpiar();
    }

    public function editar($id)
    {
        $categoria = Categoria::findOrFail($id);
        $this->categoria_id = $categoria->id;
        $this->nombre = $categoria->nombre;
        $this->flag_tallas = (bool) $categoria->flag_tallas;
     
        $this->caracteristicasSeleccionadas = $categoria->caracteristicas()->pluck('caracteristicas.id')->toArray();

        $this->abrirModal();
    }

    public function borrar($id)
    {
        Categoria::find($id)->delete();
        session()->flash('message', 'Categoría eliminada exitosamente.');
    }
}

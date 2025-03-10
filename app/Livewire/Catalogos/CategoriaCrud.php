<?php

namespace App\Livewire\Catalogos;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use App\Models\Categoria;

class CategoriaCrud extends Component
{
    use WithPagination;

    public $nombre;
    public $categoria_id;
    public $flag_tallas = false; // Nueva propiedad para el flag
    public $modal = false;
    public $search = '';
    public $query = '';
    
    protected $paginationTheme = 'tailwind';

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'flag_tallas' => 'boolean', // Nueva regla de validación
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

        return view('livewire.catalogos.categoria-crud', [
            'categorias' => $categorias,
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
        $this->flag_tallas = false; // Reiniciar flag
    }

    public function guardar()
    {
        $this->validate();

        if ($this->categoria_id) {
            $categoria = Categoria::findOrFail($this->categoria_id);
            $categoria->update([
                'nombre' => $this->nombre,
                'flag_tallas' => $this->flag_tallas, // Guardar el flag
            ]);
            session()->flash('message', '¡Categoría actualizada exitosamente!');
        } else {
            Categoria::create([
                'nombre' => $this->nombre,
                'flag_tallas' => $this->flag_tallas, // Guardar el flag
            ]);
            session()->flash('message', '¡Categoría creada exitosamente!');
        }

        $this->cerrarModal();
        $this->limpiar();
    }

    public function editar($id)
    {
        $categoria = Categoria::findOrFail($id);
        $this->categoria_id = $categoria->id;
        $this->nombre = $categoria->nombre;
        $this->flag_tallas = (bool) $categoria->flag_tallas; 
        $this->abrirModal();
    }

    public function borrar($id)
    {
        Categoria::find($id)->delete();
        session()->flash('message', 'Categoría eliminada exitosamente.');
    }
}


<?php

namespace App\Livewire\Catalogos;

use Livewire\Component;
use App\Models\Producto;
use App\Models\Categoria;
use Livewire\WithPagination;

class ProductoCrud extends Component
{
    use WithPagination;

    public $nombre;
    public $categoria_id;
    public $producto_id;
    public $modal = false;
    public $search; // La búsqueda efectiva
    public $query;  // Lo que el usuario está escribiendo en el input
    public $producto;
    public $categoriaFiltro; // Nuevo: ID de la categoría a filtrar

    protected $paginationTheme = 'tailwind';


    protected $rules = [
        'nombre' => 'required|string|max:255',
        'categoria_id' => 'required|exists:categorias,id',
    ];

    public function buscar()
    {
        // Cuando se presione el botón Buscar, se aplicará el filtro
        $this->search = $this->query;
        $this->resetPage();
    }

    public function render()
    {
        // Construir la consulta filtrada
        $query = Producto::with('categorias');

        // Si hay un término de búsqueda, agregar condición a la consulta
        if (!empty($this->search)) {
            $query->where('nombre', 'like', '%' . $this->search . '%');
           //
           // ->orWhere('descripcion', 'like', '%' . $this->search . '%')
        }

        // Filtro por categoría
        if (!empty($this->categoriaFiltro)) {
            $query->where('categoria_id', $this->categoriaFiltro);
        }

        return view('livewire.catalogos.producto-crud', [
            'productos' => $query->orderBy('created_at', 'desc')->paginate(5),
            'categorias' => Categoria::orderBy('nombre')->get(),
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
        $this->categoria_id = '';
        $this->producto_id = null;
    }

    public function guardar()
    {
        $this->validate();
    
        if ($this->producto_id) {
            // Actualizar el producto existente
            $producto = Producto::findOrFail($this->producto_id);
            $producto->update([
                'nombre' => $this->nombre,
            ]);
    
            // Actualizar la relación en la tabla pivote
            $producto->categorias()->sync([$this->categoria_id]);
    
            session()->flash('message', '¡Producto actualizado exitosamente!');
        } else {
            // Crear un nuevo producto
            $producto = Producto::create([
                'nombre' => $this->nombre,
            ]);
    
            // Crear la relación en la tabla pivote
            $producto->categorias()->attach($this->categoria_id);
    
            session()->flash('message', '¡Producto creado exitosamente!');
        }
    
        $this->cerrarModal();
        $this->limpiar();
    }

    public function editar($id)
    {
        $producto = Producto::findOrFail($id);
        $this->producto_id = $producto->id;
        $this->nombre = $producto->nombre;
        $this->categoria_id = $producto->categoria_id;
        $this->abrirModal();
    }

    public function borrar($id)
    {
        Producto::find($id)->delete();
        session()->flash('message', 'Producto eliminado exitosamente.');
    }
}
//return view('livewire.catalogos.producto-crud');

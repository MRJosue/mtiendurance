<?php

namespace App\Livewire\Catalogos;

use Livewire\Component;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Caracteristica;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;


class ProductoCrud extends Component
{
    use WithPagination;

    public $nombre, $dias_produccion, $flag_armado;
    public $categoria_id;
    public $producto_id;
    public $modal = false;
    public $search, $query;
    public $categoriaFiltro;
    public $caracteristicasSeleccionadas = [];

    protected $paginationTheme = 'tailwind';

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'categoria_id' => 'required|exists:categorias,id',
        'dias_produccion' => 'required|integer|min:1',
        'flag_armado' => 'required|boolean',
    ];

    public function buscar()
    {
        $this->search = $this->query;
        $this->resetPage();
    }

    public function render()
    {
        $query = Producto::with(['categoria', 'caracteristicas']);

        if (!empty($this->search)) {
            $query->where('nombre', 'like', '%' . $this->search . '%');
        }

        if (!empty($this->categoriaFiltro)) {
            $query->where('categoria_id', $this->categoriaFiltro);
        }

        return view('livewire.catalogos.producto-crud', [
            'productos' => $query->orderBy('created_at', 'desc')->paginate(5),
            
            'categorias' => Categoria::orderBy('nombre')->get(),
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
        $this->nombre = '';
        $this->categoria_id = '';
        $this->dias_produccion = 1;
        $this->flag_armado = 1;
        $this->producto_id = null;
        $this->caracteristicasSeleccionadas = [];
    }

    public function guardar()
    {
        $this->validate();
    
        if ($this->producto_id) {
            $producto = Producto::findOrFail($this->producto_id);
            $producto->update([
                'nombre' => $this->nombre,
                'dias_produccion' => $this->dias_produccion,
                'flag_armado' => $this->flag_armado,
            ]);

            $producto->categoria_id = $this->categoria_id;
            $producto->save();
            $producto->caracteristicas()->sync($this->caracteristicasSeleccionadas);
            session()->flash('message', '¡Producto actualizado exitosamente!');
        } else {
            $producto = Producto::create([
                'nombre' => $this->nombre,
                'dias_produccion' => $this->dias_produccion,
                'flag_armado' => $this->flag_armado,
                'categoria_id' => $this->categoria_id,
            ]);

            $producto->categorias()->attach($this->categoria_id);
            $producto->caracteristicas()->attach($this->caracteristicasSeleccionadas);
            session()->flash('message', '¡Producto creado exitosamente!');
        }
    
        $this->cerrarModal();
        $this->limpiar();
    }

    public function editar($id)
    {
        
        $producto = Producto::with('caracteristicas')->findOrFail($id);
        $this->producto_id = $producto->id;
        $this->nombre = $producto->nombre;
        $this->dias_produccion = $producto->dias_produccion;
        $this->flag_armado = $producto->flag_armado;
        $this->categoria_id = $producto->categoria ? $producto->categoria->id : null;
        Log::debug('A categoria ID ', ['categoria_id' =>  $producto->categoria_id]);
        Log::debug('Categoria ID ', ['categoria_id' => $this->categoria_id]);
    
        $this->caracteristicasSeleccionadas = $producto->caracteristicas->pluck('id')->toArray();
        $this->abrirModal();
    }

    public function borrar($id)
    {
        Producto::find($id)->delete();
        session()->flash('message', 'Producto eliminado exitosamente.');
    }
}



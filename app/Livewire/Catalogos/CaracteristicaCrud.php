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
    public $flag_seleccion_multiple = 0; // Por defecto, inactivo
    public $producto_id = [];
    public $caracteristica_id;
    public $modal = false;
    public $search = '';
    public $query = '';

    protected $paginationTheme = 'tailwind';

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'flag_seleccion_multiple' => 'boolean',
        'producto_id' => 'required|array|min:1',
        'producto_id.*' => 'exists:productos,id',
    ];

    public function buscar()
    {
        $this->search = $this->query;
        $this->resetPage();
    }

    public function render()
    {
        $query = Caracteristica::with('productos');

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
        $this->flag_seleccion_multiple = 0;
        $this->producto_id = [];
        $this->caracteristica_id = null;
    }

    public function guardar()
    {
        $this->validate();
    
        if ($this->caracteristica_id) {
            $caracteristica = Caracteristica::findOrFail($this->caracteristica_id);
            $caracteristica->update([
                'nombre' => $this->nombre,
                'flag_seleccion_multiple' => (int) $this->flag_seleccion_multiple,
            ]);
    
            $caracteristica->productos()->sync($this->producto_id);
    
            session()->flash('message', '¡Característica actualizada exitosamente!');
        } else {
            $caracteristica = Caracteristica::create([
                'nombre' => $this->nombre,
                'flag_seleccion_multiple' => (int) $this->flag_seleccion_multiple,
            ]);
    
            $caracteristica->productos()->attach($this->producto_id);
    
            session()->flash('message', '¡Característica creada exitosamente!');
        }
    
        $this->cerrarModal();
        $this->limpiar();
        $this->render(); // 🔹 Fuerza la recarga del componente para ver los cambios inmediatamente
    }

    public function editar($id)
    {
        $caracteristica = Caracteristica::findOrFail($id);
        $this->caracteristica_id = $caracteristica->id;
        $this->nombre = $caracteristica->nombre;
        $this->flag_seleccion_multiple = (bool) $caracteristica->flag_seleccion_multiple; // Asegurar conversión a booleano
        $this->producto_id = $caracteristica->productos->pluck('id')->toArray();

        $this->abrirModal();
    }

    public function borrar($id)
    {
        $caracteristica = Caracteristica::find($id);

        if ($caracteristica) {
            $caracteristica->productos()->detach();
            $caracteristica->delete();

            session()->flash('message', 'Característica eliminada exitosamente.');
        }
    }
}

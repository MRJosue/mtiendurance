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


    public $filtroActivo = '1'; // Filtro para categorías activas/inactivas

    public $ind_activo = true; // Nuevo campo para edición

    public $nombreReadonly = false;


    protected $paginationTheme = 'tailwind';



    protected $rules = [
        'nombre' => 'required|string|max:255',
        'flag_tallas' => 'boolean',
        'caracteristicasSeleccionadas' => 'array', // Validación para el array de características
        'ind_activo' => 'boolean',
    ];

    public function buscar()
    {
        $this->search = $this->query;
        $this->resetPage();
    }

    public function render()
    {


        $categorias = Categoria::where('nombre', 'like', '%' . $this->search . '%')
            ->where('ind_activo', $this->filtroActivo)
            ->orderBy('created_at', 'desc')
            ->paginate(15);


        //$caracteristicas = Caracteristica::all();
        $caracteristicas = Caracteristica::where('ind_activo', 1)->orderBy('nombre')->get();


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
        $this->ind_activo = true;
    }

    public function guardar()
    {
        $this->validate();

        if ($this->categoria_id) {
            $categoria = Categoria::findOrFail($this->categoria_id);
            $categoria->update([
                'nombre' => $this->nombre,
                'flag_tallas' => $this->flag_tallas,
                'ind_activo' => $this->ind_activo,
            ]);
            session()->flash('message', '¡Categoría actualizada exitosamente!');
        } else {
            $categoria = Categoria::create([
                'nombre' => $this->nombre,
                'flag_tallas' => $this->flag_tallas,
                'ind_activo' => $this->ind_activo,
            ]);
            session()->flash('message', '¡Categoría creada exitosamente!');
        }

        // Sincronizar características seleccionadas
        $caracteristicasValidas = Caracteristica::whereIn('id', $this->caracteristicasSeleccionadas)
        ->where('ind_activo', 1)
        ->pluck('id')
        ->toArray();
    
        $categoria->caracteristicas()->sync($caracteristicasValidas);

        $this->cerrarModal();
        $this->limpiar();
    }

    public function editar($id)
    {


        $categoria = Categoria::findOrFail($id);
        $this->categoria_id = $categoria->id;
        $this->nombre = $categoria->nombre;
        $this->flag_tallas = (bool) $categoria->flag_tallas;
        $this->ind_activo = (bool) $categoria->ind_activo;
        $this->caracteristicasSeleccionadas = $categoria->caracteristicas()->pluck('caracteristicas.id')->toArray();
        
        // Si tiene más de un producto, deshabilita el campo nombre
        $this->nombreReadonly = $categoria->productos()->count() > 1;

        $this->abrirModal();
    }

    public function borrar($id)
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->update(['ind_activo' => 0]);
        session()->flash('message', 'Categoría eliminada exitosamente.');
    }
}

<?php

namespace App\Livewire\Catalogos;

use Livewire\Component;
use App\Models\Caracteristica;
use App\Models\Producto;
use App\Models\Opcion;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB; 

class CaracteristicaCrud extends Component
{
    use WithPagination;

    public $nombre;
    public $flag_seleccion_multiple = 0; // Por defecto, inactivo
    public $opcion_id = [];
    public $caracteristica_id;
    public $modal = false;
    public $search = '';
    public $query = '';

    public $filtroActivo = '1'; // Filtro por estado activo/inactivo
    public $ind_activo = true; // Checkbox en el modal

    protected $paginationTheme = 'tailwind';

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'flag_seleccion_multiple' => 'boolean',
        'opcion_id' => 'required|array|min:1',
        'opcion_id.*' => 'exists:opciones,id',
    ];

    public function buscar()
    {
        $this->search = $this->query;
        $this->resetPage();
    }

    public function render()
    {

        $query = Caracteristica::with('opciones')
        ->where('ind_activo', $this->filtroActivo);


        if (!empty($this->search)) {
            $query->where('nombre', 'like', '%' . $this->search . '%');
        }

        return view('livewire.catalogos.caracteristica-crud', [
            'caracteristicas' => $query->orderBy('created_at', 'desc')->paginate(15),
            'opciones' => Opcion::where('ind_activo', 1)->orderBy('nombre', 'asc')->get(),
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
        $this->opcion_id = [];
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
                'ind_activo' => $this->ind_activo,
            ]);

            $opcionesActivas = Opcion::whereIn('id', $this->opcion_id)
            ->where('ind_activo', 1)
            ->pluck('id')
            ->toArray();

            $caracteristica->opciones()->sync($opcionesActivas);
    
            //$caracteristica->opciones()->sync($this->opcion_id);
    
            session()->flash('message', '¡Característica actualizada exitosamente!');
        } else {
            $caracteristica = Caracteristica::create([
                'nombre' => $this->nombre,
                'flag_seleccion_multiple' => (int) $this->flag_seleccion_multiple,
                'ind_activo' => $this->ind_activo,
            ]);

            $opcionesActivas = Opcion::whereIn('id', $this->opcion_id)
            ->where('ind_activo', 1)
            ->pluck('id')
            ->toArray();

    
            $caracteristica->opciones()->attach($opcionesActivas);
    
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
        $this->opcion_id = $caracteristica->opciones->pluck('id')->toArray();

        $this->ind_activo = (bool) $caracteristica->ind_activo;

        $this->abrirModal();
    }

    public function borrar($id)
    {
        $caracteristica = Caracteristica::findOrFail($id);
        $caracteristica->update(['ind_activo' => 0]);
        session()->flash('message', 'Característica desactivada exitosamente.');

    }
}

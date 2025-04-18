<?php

namespace App\Livewire\Catalogos;

use Livewire\Component;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Caracteristica;
use App\Models\GrupoTalla;
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
    public $mostrarCaracteristicas = false;
    public $mostrarGruposTallas = false;
    public $caracteristicasSeleccionadas = [];
    public $gruposTallasSeleccionados = [];
    public $caracteristicasDisponibles = []; // Características de la categoría seleccionada

    public $filtroActivo = '1'; // Por defecto solo productos activos
    public $ind_activo = true; // Para el modal de edición

    protected $paginationTheme = 'tailwind';

    public $bloquear_nombre = false;


    protected $rules = [
        'nombre' => 'required|string|max:255',
        'categoria_id' => 'required|exists:categorias,id',
        'dias_produccion' => 'required|integer|min:1',
        'flag_armado' => 'required|boolean',
    ];

    public function onCategoriaChange()
    {
        Log::debug('Cambio en la categoría seleccionada: ' . $this->categoria_id);
    
        if ($this->categoria_id) {
            // Buscar la categoría seleccionada con sus características
            $categoria = Categoria::with('caracteristicasActivas')->find($this->categoria_id);
            $this->caracteristicasDisponibles = $categoria->caracteristicasActivas->pluck('id')->toArray();
    
            // Obtener todas las características configuradas en la categoría
            $this->caracteristicasDisponibles = $categoria->caracteristicas->pluck('id')->toArray();
    
            // Si es una nueva categoría, se deben limpiar las características seleccionadas
            if ($this->producto_id) {
                $producto = Producto::with('caracteristicas')->find($this->producto_id);
                $this->caracteristicasSeleccionadas = array_intersect($producto->caracteristicas->pluck('id')->toArray(), $this->caracteristicasDisponibles);
            } else {
                $this->caracteristicasSeleccionadas = [];
            }
    
            // Controlar la visibilidad de las características
            $this->mostrarCaracteristicas = count($this->caracteristicasDisponibles) > 0;
    
            // Verificar si la categoría tiene flag_tallas activo
            $this->mostrarGruposTallas = $categoria->flag_tallas == 1;
        } else {
            // Resetear valores cuando no hay categoría seleccionada
            $this->caracteristicasDisponibles = [];
            $this->caracteristicasSeleccionadas = [];
            $this->mostrarCaracteristicas = false;
            $this->mostrarGruposTallas = false;
        }
    }
    
    
    

    public function buscar()
    {
        $this->search = $this->query;
        $this->resetPage();
    }

    public function render()
    {
        $query = Producto::with(['categoria', 'caracteristicas', 'gruposTallas'])
        ->where('ind_activo', $this->filtroActivo);

        if (!empty($this->search)) {
            $query->where('nombre', 'like', '%' . $this->search . '%');
        }

        if (!empty($this->categoriaFiltro)) {
            $query->where('categoria_id', $this->categoriaFiltro);
        }

        //   'productos' => Producto::with(['categoria', 'caracteristicas', 'gruposTallas'])->orderBy('created_at', 'desc')->paginate(15),
        return view('livewire.catalogos.producto-crud', [
         
            'productos' => $query->orderBy('created_at', 'desc')->paginate(15),
            'categorias' => Categoria::where('ind_activo', 1)->orderBy('nombre')->get(),
            'caracteristicas' => Caracteristica::where('ind_activo', 1)->orderBy('nombre')->get(),
            'gruposTallasDisponibles' => GrupoTalla::where('ind_activo', 1)->orderBy('nombre')->get(),
        ]);
    }

    public function crear()
    {
        $this->limpiar();
        
        $this->onCategoriaChange();
        $this->abrirModal();
    }

    public function abrirModal()
    {
        $this->modal = true;
       
        
    }

    public function cerrarModal()
    {
        $this->limpiar();
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
        $this->gruposTallasSeleccionados = [];
        
    }

    public function guardar()
    {
        $this->validate();
    
        $producto = Producto::updateOrCreate(
            ['id' => $this->producto_id],
            [
                'nombre' => $this->nombre,
                'dias_produccion' => $this->dias_produccion,
                'flag_armado' => $this->flag_armado,
                'categoria_id' => $this->categoria_id, // Asegurar que la categoría se actualiza correctamente
                'ind_activo' => $this->ind_activo,
            ]
        );


    
        // Solo mantener las características válidas para la categoría
        // $caracteristicasValidas = array_intersect($this->caracteristicasSeleccionadas, $this->caracteristicasDisponibles);
        // $producto->caracteristicas()->sync($caracteristicasValidas);
    
        // ✅ Validar características activas y disponibles para esta categoría
        $caracteristicasValidas = \App\Models\Caracteristica::whereIn('id', $this->caracteristicasSeleccionadas)
        ->where('ind_activo', 1)
        ->whereIn('id', $this->caracteristicasDisponibles)
        ->pluck('id')
        ->toArray();

        $producto->caracteristicas()->sync($caracteristicasValidas);

        // Sincronizar grupos de tallas seleccionados si flag_tallas es 1
        if ($this->mostrarGruposTallas) {
            $gruposValidos = \App\Models\GrupoTalla::whereIn('id', $this->gruposTallasSeleccionados)
                ->where('ind_activo', 1)
                ->pluck('id')
                ->toArray();

            $producto->gruposTallas()->sync($gruposValidos);
        } else {
            $producto->gruposTallas()->detach();
        }
        
    
        session()->flash('message', '¡Producto guardado exitosamente!');
    
        $this->cerrarModal();
        $this->limpiar();
    }
    
    public function editar($id)
    {
        $producto = Producto::with(['caracteristicas', 'gruposTallas'])->findOrFail($id);
        $this->producto_id = $producto->id;
        $this->nombre = $producto->nombre;
        $this->dias_produccion = $producto->dias_produccion;
        $this->flag_armado = $producto->flag_armado;
        $this->categoria_id = $producto->categoria ? $producto->categoria->id : null;

        $this->ind_activo = (bool) $producto->ind_activo;

        // ⚠️ Verificar relaciones activas antes de permitir editar el nombre
        $tieneCaracteristicas = $producto->caracteristicas()->exists();
        $tienePedidos = \App\Models\Pedido::where('producto_id', $producto->id)->count() > 1;
    
        $this->bloquear_nombre = $tieneCaracteristicas || $tienePedidos;
        
        // Actualizamos las características disponibles antes de asignar las seleccionadas
        $this->onCategoriaChange();
        
    
        // Solo mantener las características válidas
        $this->caracteristicasSeleccionadas = array_intersect($producto->caracteristicas->pluck('id')->toArray(), $this->caracteristicasDisponibles);
        $this->gruposTallasSeleccionados = $producto->gruposTallas->pluck('id')->toArray();
    
        $this->abrirModal();
    }
    
    public function borrar($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->update(['ind_activo' => 0]);
        session()->flash('message', 'Producto eliminado exitosamente.');
    }
}



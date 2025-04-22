<?php

namespace App\Livewire\Catalogos;

use Livewire\Component;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Caracteristica;
use App\Models\ProductoCaracteristica;
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
    public $caracteristicasNoArmado = [];

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
            // if ($this->producto_id) {
            //     $producto = Producto::with('caracteristicas')->find($this->producto_id);
            //     $this->caracteristicasSeleccionadas = array_intersect($producto->caracteristicas->pluck('id')->toArray(), $this->caracteristicasDisponibles);
            // } else {
            //     $this->caracteristicasSeleccionadas = [];
            // }
            if (!$this->producto_id) {
                $this->caracteristicasSeleccionadas = [];
                $this->caracteristicasNoArmado = [];
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
            $this->caracteristicasNoArmado = [];
        }
    }
    
    
    

    public function buscar()
    {
        $this->search = $this->query;
        $this->resetPage();
    }

    public function render()
    {
        $query = Producto::with(['categoria', 'caracteristicas', 'caracteristicasNoArmado', 'gruposTallas'])
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
        $this->caracteristicasNoArmado = [];
        
    }

    public function guardar()
    {
        $this->validate();
    
        // Crear o actualizar el producto
        $producto = Producto::updateOrCreate(
            ['id' => $this->producto_id],
            [
                'nombre' => $this->nombre,
                'dias_produccion' => $this->dias_produccion,
                'flag_armado' => $this->flag_armado,
                'categoria_id' => $this->categoria_id,
                'ind_activo' => $this->ind_activo,
            ]
        );
    
        // Eliminar todas las relaciones actuales con características
        ProductoCaracteristica::where('producto_id', $producto->id)->delete();
    
        // Guardar características ARMADO (flag_armado = 1)
        $caracteristicasArmado = Caracteristica::whereIn('id', $this->caracteristicasSeleccionadas)
            ->where('ind_activo', 1)
            ->whereIn('id', $this->caracteristicasDisponibles)
            ->pluck('id')
            ->toArray();
    
        foreach ($caracteristicasArmado as $id) {
            $producto->caracteristicas()->attach($id, ['flag_armado' => 1]);
        }
    
        // Guardar características NO ARMADO (flag_armado = 0)
        $caracteristicasNoArmado = Caracteristica::whereIn('id', $this->caracteristicasNoArmado)
            ->where('ind_activo', 1)
            ->whereIn('id', $this->caracteristicasDisponibles)
            ->pluck('id')
            ->toArray();
    
        foreach ($caracteristicasNoArmado as $id) {
            $producto->caracteristicas()->attach($id, ['flag_armado' => 0]);
        }
    
        // Sincronizar grupos de tallas
        if ($this->mostrarGruposTallas) {
            $gruposValidos = GrupoTalla::whereIn('id', $this->gruposTallasSeleccionados)
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
    
    
    // public function editar($id)
    // {
    //     $producto = Producto::with(['caracteristicas', 'gruposTallas'])->findOrFail($id);
    //     $this->producto_id = $producto->id;
    //     $this->nombre = $producto->nombre;
    //     $this->dias_produccion = $producto->dias_produccion;
    //     $this->flag_armado = $producto->flag_armado;
    //     $this->categoria_id = $producto->categoria ? $producto->categoria->id : null;

    //     $this->ind_activo = (bool) $producto->ind_activo;

    //     // ⚠️ Verificar relaciones activas antes de permitir editar el nombre
    //     $tieneCaracteristicas = $producto->caracteristicas()->exists();
        
    //     $tienePedidos = Pedido::where('producto_id', $producto->id)->count() > 1;
    
    //     $this->bloquear_nombre = $tieneCaracteristicas || $tienePedidos;
        
    //     // Actualizamos las características disponibles antes de asignar las seleccionadas
    //     $this->onCategoriaChange();
        
    
    //     // Solo mantener las características válidas
    //     $this->caracteristicasSeleccionadas = array_intersect($producto->caracteristicas->pluck('id')->toArray(), $this->caracteristicasDisponibles);

    //     $this->caracteristicasNoArmado = \App\Models\ProductoCaracteristica::where('producto_id', $producto->id)
    //     ->where('flag_armado', 0)
    //     ->pluck('caracteristica_id')
    //     ->map(fn($id) => (int) $id) // ← Fuerza a int
    //     ->toArray();
            

    //     $this->gruposTallasSeleccionados = $producto->gruposTallas->pluck('id')->toArray();
    
    //     $this->abrirModal();
    // }

    public function editar($id)
    {
        $producto = Producto::with(['caracteristicas', 'gruposTallas'])->findOrFail($id);
    
        $this->producto_id = $producto->id;
        $this->nombre = $producto->nombre;
        $this->dias_produccion = $producto->dias_produccion;
        $this->flag_armado = $producto->flag_armado;
        $this->categoria_id = $producto->categoria ? $producto->categoria->id : null;
        $this->ind_activo = (bool) $producto->ind_activo;
    
        $this->bloquear_nombre = $producto->caracteristicas()->exists()
            || Pedido::where('producto_id', $producto->id)->count() > 1;
    
        // Obtener IDs de características armadas (flag_armado = 1)
        $this->caracteristicasSeleccionadas = ProductoCaracteristica::where('producto_id', $producto->id)
            ->where('flag_armado', 1)
            ->pluck('caracteristica_id')
            ->map(fn($id) => (int) $id)
            ->toArray();
    
        // Obtener IDs de características no armadas (flag_armado = 0)
        $this->caracteristicasNoArmado = ProductoCaracteristica::where('producto_id', $producto->id)
            ->where('flag_armado', 0)
            ->pluck('caracteristica_id')
            ->map(fn($id) => (int) $id)
            ->toArray();
    
        // Este debe ir después de cargar selecciones para que no las borre
        $this->onCategoriaChange();
    
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



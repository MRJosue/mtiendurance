<?php

namespace App\Livewire\Catalogos;

use Livewire\Component;
use App\Models\Opcion;
use App\Models\Caracteristica;
use Livewire\WithPagination;


class OpcionCrud extends Component
{
    use WithPagination;

    public $nombre;
    public $pasos;
    public $minutoPaso;
    public $valoru;
    public $opcion_id;
    public $modal = false;
    public $search = '';
    public $query = '';


    public $filtroActivo = '1'; // Mostrar activas por defecto
    public $ind_activo = true;  // Control en el modal

    public $mostrarConfirmacion = false;
    public $mensajeConfirmacion = '';
    public $accionPendiente = null;

    public $datosPendientes = [];


    protected $paginationTheme = 'tailwind';

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'pasos' => 'required|integer|min:0',
        'minutoPaso' => 'required|integer|min:0',
        'valoru' => 'required|numeric|min:0',
    ];

    public function buscar()
    {
        $this->search = $this->query;
        $this->resetPage();
    }

    public function render()
    {
        $query = Opcion::where('ind_activo', $this->filtroActivo);


        if (!empty($this->search)) {
            $query->where('nombre', 'like', '%' . $this->search . '%');
        }

        return view('livewire.catalogos.opcion-crud', [
            'opciones' => $query->orderBy('created_at', 'desc')->paginate(15),
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
        $this->pasos = 0;
        $this->minutoPaso = 0;
        $this->valoru = 0;
        $this->opcion_id = null;
    }

    public function guardar()
    {
        $this->validate();
    
            if ($this->opcion_id) {
                $opcion = Opcion::findOrFail($this->opcion_id);
        
                // Si está intentando desactivar
                if (!$this->ind_activo && $opcion->caracteristicas()->count() > 0) {
                    $this->cerrarModal(); // Cierra el modal principal primero

                    $this->mensajeConfirmacion = "La opción que estás desactivando tiene relaciones activas con características. ¿Deseas continuar y eliminar esas relaciones?";
                    $this->mostrarConfirmacion = true;
                    $this->accionPendiente = 'guardar';
                    $this->datosPendientes = [
                        'id' => $this->opcion_id,
                        'nombre' => $this->nombre,
                        'pasos' => $this->pasos,
                        'minutoPaso' => $this->minutoPaso,
                        'valoru' => $this->valoru,
                        'ind_activo' => false,
                    ];
                    return;
            }
    
            // Actualizar directamente si no hay conflicto
            $opcion->update([
                'nombre' => $this->nombre,
                'pasos' => $this->pasos,
                'minutoPaso' => $this->minutoPaso,
                'valoru' => $this->valoru,
                'ind_activo' => $this->ind_activo,
            ]);
    
            session()->flash('message', '¡Opción actualizada exitosamente!');
        } else {
            // Crear nueva
            Opcion::create([
                'nombre' => $this->nombre,
                'pasos' => $this->pasos,
                'minutoPaso' => $this->minutoPaso,
                'valoru' => $this->valoru,
                'ind_activo' => $this->ind_activo,
            ]);
            session()->flash('message', '¡Opción creada exitosamente!');
        }
    
        $this->cerrarModal();
        $this->limpiar();
    }
    
    public function editar($id)
    {
        $opcion = Opcion::findOrFail($id);
        $this->opcion_id = $opcion->id;
        $this->nombre = $opcion->nombre;
        $this->pasos = $opcion->pasos;
        $this->minutoPaso = $opcion->minutoPaso;
        $this->valoru = $opcion->valoru;
        $this->ind_activo = (bool) $opcion->ind_activo;

        $this->abrirModal();
    }

    public function borrar($id)
    {
        $opcion = Opcion::find($id);

        if ($opcion) {
            $opcion->update(['ind_activo' => 0]);
            session()->flash('message', 'Opción desactivada exitosamente.');
        }
    }


    public function eliminarRelacionesCaracteristicas($id)
    {
        $opcion = Opcion::findOrFail($id);
        $opcion->caracteristicas()->detach();
    }


    public function confirmarDesactivacion($id)
    {
        $opcion = Opcion::findOrFail($id);
        $relaciones = $opcion->caracteristicas()->count();

        $this->mensajeConfirmacion = "La opción tiene {$relaciones} relación(es) con características. ¿Deseas continuar y eliminar esas relaciones?";
        $this->opcion_id = $id;
        $this->accionPendiente = 'desactivar';
        $this->mostrarConfirmacion = true;
    }

    public function confirmarEliminacionTotal($id)
    {
        $opcion = Opcion::findOrFail($id);
        $relaciones = $opcion->caracteristicas()->count();

        $this->mensajeConfirmacion = "La opción tiene {$relaciones} relación(es) con características. ¿Deseas continuar y eliminar la opción junto con sus relaciones?";
        $this->opcion_id = $id;
        $this->accionPendiente = 'eliminar';
        $this->mostrarConfirmacion = true;
    }

    public function ejecutarAccionConfirmada()
    {
        if ($this->accionPendiente === 'guardar') {
            $opcion = Opcion::findOrFail($this->datosPendientes['id']);
    
            // Eliminar relaciones
            $opcion->caracteristicas()->detach();
    
            // Actualizar con los datos pendientes
            $opcion->update($this->datosPendientes);
    
            session()->flash('message', '¡Relaciones eliminadas y opción desactivada exitosamente!');
        }
    
        $this->mostrarConfirmacion = false;
        $this->accionPendiente = null;
        $this->datosPendientes = [];
    
        $this->cerrarModal();
        $this->limpiar();
    }
    

}

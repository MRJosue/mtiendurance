<?php

namespace App\Livewire\Catalogos;


use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Talla;

class TallasCrud extends Component
{
    use WithPagination;

    public $nombre, $descripcion, $talla_id;
    public $modalOpen = false;
    public $confirmingDelete = false;

    public $filtroActivo = '1';
    public $ind_activo = true;
    public $search = '';
    public $query = '';

    public $mostrarConfirmacion = false;
    public $mensajeConfirmacion = '';
    public $accionPendiente = null;
    public $datosPendientes = [];


    protected $rules = [
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
    ];

    public function render()
    {
        $tallas = Talla::where('ind_activo', $this->filtroActivo)
        ->when($this->search, function ($query) {
            $query->where('nombre', 'like', '%' . $this->search . '%');
        })
        ->orderBy('nombre')
        ->paginate(10);

        return view('livewire.catalogos.tallas-crud', compact('tallas'));
    }

    public function openModal()
    {
        $this->reset(['nombre', 'descripcion', 'talla_id']);
        $this->modalOpen = true;
    }

    public function edit(Talla $talla)
    {
        $this->talla_id = $talla->id;
        $this->nombre = $talla->nombre;
        $this->descripcion = $talla->descripcion;
        $this->modalOpen = true;
        $this->ind_activo = (bool) $talla->ind_activo;
    }

    public function save()
    {
        $this->validate();
    
        if ($this->talla_id) {
            $talla = Talla::findOrFail($this->talla_id);
    
            if (!$this->ind_activo && $talla->gruposTallas()->count() > 0) {
                // Mostrar confirmación si hay relaciones
                $this->mensajeConfirmacion = "La talla que deseas desactivar está relacionada con uno o más grupos de tallas. ¿Deseas eliminar esas relaciones y continuar?";
                $this->mostrarConfirmacion = true;
                $this->accionPendiente = 'guardar';
                $this->datosPendientes = [
                    'id' => $this->talla_id,
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion,
                    'ind_activo' => 0,
                ];
                return;
            }
        }
    
        // Crear o actualizar directamente
        Talla::updateOrCreate(['id' => $this->talla_id], [
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'ind_activo' => $this->ind_activo,
        ]);
    
        $this->dispatch('alert', ['message' => 'Talla guardada correctamente.']);
        $this->modalOpen = false;
    }

    public function confirmDelete(Talla $talla)
    {
        $this->talla_id = $talla->id;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        Talla::findOrFail($this->talla_id)->update(['ind_activo' => 0]);
        $this->dispatch('alert', ['message' => 'Talla desactivada correctamente.']);
        $this->confirmingDelete = false;
    }


    public function ejecutarAccionConfirmada()
    {
        if ($this->accionPendiente === 'guardar') {
            $talla = Talla::findOrFail($this->datosPendientes['id']);

            // Eliminar relaciones
            $talla->gruposTallas()->detach();

            // Actualizar la talla
            $talla->update($this->datosPendientes);

            $this->dispatch('alert', ['message' => 'Talla desactivada y relaciones eliminadas correctamente.']);
        }

        $this->mostrarConfirmacion = false;
        $this->accionPendiente = null;
        $this->datosPendientes = [];
        $this->modalOpen = false;
    }

    public function buscar()
    {
        $this->search = $this->query;
        $this->resetPage();
    }
}

//  return view('livewire.catalogos.tallas-crud');
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
        $query = Opcion::query();

        if (!empty($this->search)) {
            $query->where('nombre', 'like', '%' . $this->search . '%');
        }

        return view('livewire.catalogos.opcion-crud', [
            'opciones' => $query->orderBy('created_at', 'desc')->paginate(5),
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
        $this->pasos = null;
        $this->minutoPaso = null;
        $this->valoru = null;
        $this->opcion_id = null;
    }

    public function guardar()
    {
        $this->validate();

        if ($this->opcion_id) {
            // Actualizar opción existente
            $opcion = Opcion::findOrFail($this->opcion_id);
            $opcion->update([
                'nombre' => $this->nombre,
                'pasos' => $this->pasos,
                'minutoPaso' => $this->minutoPaso,
                'valoru' => $this->valoru,
            ]);
            session()->flash('message', '¡Opción actualizada exitosamente!');
        } else {
            // Crear nueva opción
            Opcion::create([
                'nombre' => $this->nombre,
                'pasos' => $this->pasos,
                'minutoPaso' => $this->minutoPaso,
                'valoru' => $this->valoru,
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
        $this->abrirModal();
    }

    public function borrar($id)
    {
        $opcion = Opcion::find($id);

        if ($opcion) {
            $opcion->delete(); // Eliminar la opción
            session()->flash('message', 'Opción eliminada exitosamente.');
        }
    }
}

<?php

namespace App\Livewire\Catalogos;

use Livewire\Component;
use App\Models\Opcion;
use App\Models\Caracteristica;
use Livewire\WithPagination;

class OpcionCrud extends Component
{
    use WithPagination;

    public $valor;
    public $nombre; // Nuevo campo
    public $caracteristica_id;
    public $opcion_id;
    public $modal = false;
    public $search; // Valor aplicado al buscar
    public $query;  // Entrada del usuario en el input de búsqueda

    protected $paginationTheme = 'tailwind';

    // Reglas de validación
    protected $rules = [
        'valor' => 'required|string|max:255',
        'nombre' => 'required|string|max:255', // Validación para el nuevo campo
        'caracteristica_id' => 'required|exists:caracteristicas,id',
    ];

    // Buscar y filtrar las opciones
    public function buscar()
    {
        $this->search = $this->query;
        $this->resetPage();
    }

    // Renderizar la vista con los datos
    public function render()
    {
        $opciones = Opcion::with('caracteristica')
            ->where(function ($query) {
                $query->where('valor', 'like', '%' . $this->search . '%')
                      ->orWhere('nombre', 'like', '%' . $this->search . '%'); // Búsqueda por nombre
            })
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return view('livewire.catalogos.opcion-crud', [
            'opciones' => $opciones,
            'caracteristicas' => Caracteristica::orderBy('nombre')->get(),
        ]);
    }

    // Abrir el modal para crear
    public function crear()
    {
        $this->limpiar();
        $this->abrirModal();
    }

    // Abrir el modal
    public function abrirModal()
    {
        $this->modal = true;
    }

    // Cerrar el modal
    public function cerrarModal()
    {
        $this->modal = false;
    }

    // Limpiar los campos del formulario
    public function limpiar()
    {
        $this->valor = '';
        $this->nombre = ''; // Limpiar el campo nombre
        $this->caracteristica_id = '';
        $this->opcion_id = null;
    }

    // Guardar o actualizar una opción
    public function guardar()
    {
        $this->validate();

        if ($this->opcion_id) {
            // Actualizar opción existente
            $opcion = Opcion::findOrFail($this->opcion_id);
            $opcion->update([
                'valor' => $this->valor,
                'nombre' => $this->nombre, // Actualizar el nuevo campo
                'caracteristica_id' => $this->caracteristica_id,
            ]);
            session()->flash('message', '¡Opción actualizada exitosamente!');
        } else {
            // Crear nueva opción
            Opcion::create([
                'valor' => $this->valor,
                'nombre' => $this->nombre, // Guardar el nuevo campo
                'caracteristica_id' => $this->caracteristica_id,
            ]);
            session()->flash('message', '¡Opción creada exitosamente!');
        }

        $this->cerrarModal();
        $this->limpiar();
    }

    // Editar una opción
    public function editar($id)
    {
        $opcion = Opcion::findOrFail($id);
        $this->opcion_id = $opcion->id;
        $this->valor = $opcion->valor;
        $this->nombre = $opcion->nombre; // Cargar el valor del campo nombre
        $this->caracteristica_id = $opcion->caracteristica_id;
        $this->abrirModal();
    }

    // Eliminar una opción
    public function borrar($id)
    {
        Opcion::find($id)->delete();
        session()->flash('message', 'Opción eliminada exitosamente.');
    }
}
// return view('livewire.catalogos.opcion-crud');

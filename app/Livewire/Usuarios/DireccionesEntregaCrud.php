<?php

namespace App\Livewire\Usuarios;

use App\Models\DireccionEntrega;
use App\Models\Pais;
use App\Models\Estado;
use App\Models\Ciudad;
use Livewire\Component;
use Livewire\WithPagination;

class DireccionesEntregaCrud extends Component
{
    use WithPagination;

    public $userId;
    public $nombre_contacto;
    public $nombre_empresa;
    public $calle;
    public $pais_id;
    public $estado_id;
    public $ciudad_id;
    public $codigo_postal;
    public $telefono;
    public $direccion_id;
    public $modal = false;
    public $search = '';
    public $query = '';

    protected $rules = [
        'nombre_contacto' => 'required|string|max:255',
        'nombre_empresa' => 'string|max:255',
        'calle' => 'required|string|max:255',
        'pais_id' => 'required|exists:paises,id',
        'estado_id' => 'required|exists:estados,id',
        'ciudad_id' => 'required|exists:ciudades,id',
        'codigo_postal' => 'required|string|max:10',
        'telefono' => 'nullable|string|max:15',
    ];

    public function mount($userId)
    {
        $this->userId = $userId;
    }

    public function buscar()
    {
        $this->search = $this->query;
        $this->resetPage();
    }

    public function render()
    {
        $query = DireccionEntrega::where('usuario_id', $this->userId);

        if (!empty($this->search)) {
            $query->where('nombre_contacto', 'like', '%' . $this->search . '%');
        }

        return view('livewire.usuarios.direcciones-entrega-crud', [
            'direcciones' => $query->with(['ciudad', 'ciudad.estado', 'ciudad.estado.pais'])->orderBy('created_at', 'desc')->paginate(5),
            'paises' => Pais::all(),
            'estados' => $this->pais_id ? Estado::where('pais_id', $this->pais_id)->get() : [],
            'ciudades' => $this->estado_id ? Ciudad::where('estado_id', $this->estado_id)->get() : [],
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
        $this->nombre_contacto = '';
        $this->nombre_empresa = '';
        $this->calle = '';
        $this->pais_id = null;
        $this->estado_id = null;
        $this->ciudad_id = null;
        $this->codigo_postal = '';
        $this->telefono = '';
        $this->direccion_id = null;
    }

    public function guardar()
    {
        $this->validate();

        $data = [
            'usuario_id' => $this->userId,
            'nombre_contacto' => $this->nombre_contacto,
            'nombre_empresa' => $this->nombre_empresa,
            'calle' => $this->calle,
            'pais_id' => $this->pais_id,
            'estado_id' => $this->estado_id,
            'ciudad_id' => $this->ciudad_id,
            'codigo_postal' => $this->codigo_postal,
            'telefono' => $this->telefono,
        ];

        if ($this->direccion_id) {
            $direccion = DireccionEntrega::findOrFail($this->direccion_id);
            $direccion->update($data);
            session()->flash('message', '¡Dirección actualizada exitosamente!');
        } else {
            DireccionEntrega::create($data);
            session()->flash('message', '¡Dirección creada exitosamente!');
        }

        $this->cerrarModal();
        $this->limpiar();
    }

    public function editar($id)
    {
        $direccion = DireccionEntrega::with(['ciudad', 'ciudad.estado', 'ciudad.estado.pais'])->findOrFail($id);
        $this->direccion_id = $direccion->id;
        $this->nombre_contacto = $direccion->nombre_contacto;
        $this->nombre_empresa = $direccion->nombre_empresa;
        $this->calle = $direccion->calle;
        $this->pais_id = $direccion->pais_id;
        $this->estado_id = $direccion->estado_id;
        $this->ciudad_id = $direccion->ciudad_id;
        $this->codigo_postal = $direccion->codigo_postal;
        $this->telefono = $direccion->telefono;
        $this->abrirModal();
    }

    public function borrar($id)
    {
        $direccion = DireccionEntrega::findOrFail($id);
        $direccion->delete();
        session()->flash('message', 'Dirección eliminada exitosamente.');
    }

    public function establecerDefault($id)
    {
        DireccionEntrega::where('usuario_id', $this->userId)->update(['flag_default' => false]);
        $direccion = DireccionEntrega::findOrFail($id);
        $direccion->update(['flag_default' => true]);
        session()->flash('message', '¡Dirección predeterminada actualizada exitosamente!');
    }
}


//  return view('livewire.usuarios.direcciones-entrega-crud');
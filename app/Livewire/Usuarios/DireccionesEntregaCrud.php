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

    // Campos del formulario
    public $nombre_contacto;
    public $nombre_empresa;
    public $calle;
    public $pais_id;
    public $estado_id;
    public $ciudad; // ✅ texto
    public $codigo_postal;
    public $telefono;

    public $direccion_id;
    public $modal = false;
    public $search = '';
    public $query = '';

    // Catálogos en memoria
    public $paisesList;
    public $estadosList;

    protected $rules = [
        'nombre_contacto' => 'required|string|max:255',
        'nombre_empresa'  => 'nullable|string|max:255',
        'calle'           => 'required|string|max:255',
        'pais_id'         => 'required|exists:paises,id',
        'estado_id'       => 'required|exists:estados,id',
        'ciudad'          => 'required|string|max:255', // ✅
        'codigo_postal'   => 'required|string|max:10',
        'telefono'        => 'nullable|string|max:15',
    ];

    public function mount($userId)
    {
        $this->userId = $userId;
        $this->paisesList  = Pais::orderBy('nombre')->get(['id','nombre']);
        $this->estadosList = collect();
    }

    /** Cuando cambia el país, reinicia estados */
    public function updatedPaisId($paisId)
    {
        $this->estado_id = null;

        $this->estadosList = $paisId
            ? Estado::where('pais_id', $paisId)->orderBy('nombre')->get(['id','nombre'])
            : collect();
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
            'direcciones' => $query
                ->with(['estado', 'pais', 'estado.pais'])
                ->orderByDesc('created_at')
                ->paginate(15),
        ]);
    }

    public function crear()
    {
        $this->limpiar();
        $this->abrirModal();
    }

    public function abrirModal() { $this->modal = true; }

    public function cerrarModal() { $this->modal = false; }

    public function limpiar()
    {
        $this->direccion_id     = null;
        $this->nombre_contacto  = '';
        $this->nombre_empresa   = '';
        $this->calle            = '';
        $this->pais_id          = null;
        $this->estado_id        = null;
        $this->ciudad           = '';   // ✅
        $this->codigo_postal    = '';
        $this->telefono         = '';

        $this->estadosList      = collect();
    }

    public function guardar()
    {
        $this->validate();

        $data = [
            'usuario_id'      => $this->userId,
            'nombre_contacto' => $this->nombre_contacto,
            'nombre_empresa'  => $this->nombre_empresa,
            'calle'           => $this->calle,
            'pais_id'         => $this->pais_id,
            'estado_id'       => $this->estado_id,
            'ciudad'          => $this->ciudad, // ✅
            'codigo_postal'   => $this->codigo_postal,
            'telefono'        => $this->telefono,
        ];

        if ($this->direccion_id) {
            DireccionEntrega::findOrFail($this->direccion_id)->update($data);
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
        $d = DireccionEntrega::with(['estado', 'pais', 'estado.pais'])->findOrFail($id);

        $this->direccion_id    = $d->id;
        $this->nombre_contacto = $d->nombre_contacto;
        $this->nombre_empresa  = $d->nombre_empresa;
        $this->calle           = $d->calle;

        // Cargar cascada País -> Estados
        $this->pais_id = $d->pais_id;
        $this->estadosList = $this->pais_id
            ? Estado::where('pais_id', $this->pais_id)->orderBy('nombre')->get(['id','nombre'])
            : collect();

        $this->estado_id = $d->estado_id;

        // ciudad texto
        $this->ciudad = $d->ciudad ?? '';

        $this->codigo_postal  = $d->codigo_postal;
        $this->telefono       = $d->telefono;

        $this->abrirModal();
    }

    public function borrar($id)
    {
        DireccionEntrega::findOrFail($id)->delete();
        session()->flash('message', 'Dirección eliminada exitosamente.');
    }

    public function establecerDefault($id)
    {
        DireccionEntrega::where('usuario_id', $this->userId)->update(['flag_default' => false]);
        DireccionEntrega::findOrFail($id)->update(['flag_default' => true]);
        session()->flash('message', '¡Dirección predeterminada actualizada exitosamente!');
    }
}
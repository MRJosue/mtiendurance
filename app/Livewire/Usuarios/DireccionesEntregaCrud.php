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
    public $ciudad_id;
    public $codigo_postal;
    public $telefono;

    public $direccion_id;
    public $modal = false;
    public $search = '';
    public $query = '';

    // Catálogos en memoria (colecciones)
    public $paisesList;
    public $estadosList;
    public $ciudadesList;

    protected $rules = [
        'nombre_contacto' => 'required|string|max:255',
        'nombre_empresa'  => 'nullable|string|max:255',
        'calle'           => 'required|string|max:255',
        'pais_id'         => 'required|exists:paises,id',
        'estado_id'       => 'required|exists:estados,id',
        'ciudad_id'       => 'required|exists:ciudades,id',
        'codigo_postal'   => 'required|string|max:10',
        'telefono'        => 'nullable|string|max:15',
    ];

    public function mount($userId)
    {
        $this->userId = $userId;
        $this->paisesList  = Pais::orderBy('nombre')->get(['id','nombre']);
        $this->estadosList = collect();
        $this->ciudadesList = collect();
    }

    /** Cuando cambia el país, reinicia estados y ciudades */
    public function updatedPaisId($paisId)
    {
        $this->estado_id    = null;
        $this->ciudad_id    = null;
        $this->ciudadesList = collect();

        $this->estadosList = $paisId
            ? Estado::where('pais_id', $paisId)->orderBy('nombre')->get(['id','nombre'])
            : collect();
    }

    /** Cuando cambia el estado, reinicia ciudades */
    public function updatedEstadoId($estadoId)
    {
        $this->ciudad_id    = null;
        $this->ciudadesList = $estadoId
            ? Ciudad::where('estado_id', $estadoId)->orderBy('nombre')->get(['id','nombre'])
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
            'direcciones' => $query->with(['ciudad.estado.pais'])
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
        $this->ciudad_id        = null;
        $this->codigo_postal    = '';
        $this->telefono         = '';

        // Limpia las listas dependientes
        $this->estadosList   = collect();
        $this->ciudadesList  = collect();
    }

    public function guardar()
    {
        $this->validate();

        $data = [
            'usuario_id'     => $this->userId,
            'nombre_contacto'=> $this->nombre_contacto,
            'nombre_empresa' => $this->nombre_empresa,
            'calle'          => $this->calle,
            'pais_id'        => $this->pais_id,
            'estado_id'      => $this->estado_id,
            'ciudad_id'      => $this->ciudad_id,
            'codigo_postal'  => $this->codigo_postal,
            'telefono'       => $this->telefono,
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
        $d = DireccionEntrega::with(['ciudad.estado.pais'])->findOrFail($id);

        $this->direccion_id    = $d->id;
        $this->nombre_contacto = $d->nombre_contacto;
        $this->nombre_empresa  = $d->nombre_empresa;
        $this->calle           = $d->calle;

        // Cargar cascada en orden
        $this->pais_id = $d->pais_id;
        $this->estadosList = $this->pais_id
            ? Estado::where('pais_id', $this->pais_id)->orderBy('nombre')->get(['id','nombre'])
            : collect();

        $this->estado_id = $d->estado_id;
        $this->ciudadesList = $this->estado_id
            ? Ciudad::where('estado_id', $this->estado_id)->orderBy('nombre')->get(['id','nombre'])
            : collect();

        $this->ciudad_id      = $d->ciudad_id;
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

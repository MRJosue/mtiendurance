<?php

namespace App\Livewire\Usuarios;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DireccionFiscal;
use App\Models\Pais;
use App\Models\Estado;

class DireccionesFiscalesCrud extends Component
{
    use WithPagination;

    public $userId;

    public $razon_social;
    public $rfc;
    public $calle;

    public $pais_id;
    public $estado_id;

    public $ciudad; // ✅ NUEVO texto

    public $codigo_postal;
    public $direccion_id;

    public $modal = false;
    public $search = '';
    public $query = '';

    protected $rules = [
        'razon_social'   => 'required|string|max:255',
        'rfc'            => 'required|string|max:13',
        'calle'          => 'required|string|max:255',
        'pais_id'        => 'required|exists:paises,id',
        'estado_id'      => 'required|exists:estados,id',
        'ciudad'         => 'required|string|max:255', // ✅
        'codigo_postal'  => 'required|string|max:10',
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

    public function updatedPaisId(): void
    {
        // Si cambia país, reinicia estado
        $this->estado_id = null;
    }

    public function render()
    {
        $query = DireccionFiscal::where('usuario_id', $this->userId);

        if (!empty($this->search)) {
            $query->where('rfc', 'like', '%' . $this->search . '%');
        }

        return view('livewire.usuarios.direcciones-fiscales-crud', [
            'direcciones' => $query
                ->with(['estado', 'pais', 'estado.pais'])
                ->orderBy('created_at', 'desc')
                ->paginate(5),

            'paises' => Pais::orderBy('nombre')->get(),
            'estados' => $this->pais_id ? Estado::where('pais_id', $this->pais_id)->orderBy('nombre')->get() : [],
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
        $this->rfc = '';
        $this->razon_social = '';
        $this->calle = '';
        $this->pais_id = null;
        $this->estado_id = null;
        $this->ciudad = ''; // ✅
        $this->codigo_postal = '';
        $this->direccion_id = null;
    }

    public function guardar()
    {
        $this->validate();

        $data = [
            'usuario_id'    => $this->userId,
            'razon_social'  => $this->razon_social,
            'rfc'           => $this->rfc,
            'calle'         => $this->calle,
            'pais_id'       => $this->pais_id,
            'estado_id'     => $this->estado_id,
            'ciudad'        => $this->ciudad, // ✅
            'codigo_postal' => $this->codigo_postal,
        ];

        if ($this->direccion_id) {
            $direccion = DireccionFiscal::findOrFail($this->direccion_id);
            $direccion->update($data);
            session()->flash('message', '¡Dirección fiscal actualizada exitosamente!');
        } else {
            DireccionFiscal::create($data);
            session()->flash('message', '¡Dirección fiscal creada exitosamente!');
        }

        $this->cerrarModal();
        $this->limpiar();
    }

    public function editar($id)
    {
        $direccion = DireccionFiscal::with(['estado', 'pais', 'estado.pais'])->findOrFail($id);

        $this->direccion_id = $direccion->id;
        $this->razon_social = $direccion->razon_social;
        $this->rfc          = $direccion->rfc;
        $this->calle        = $direccion->calle;

        $this->pais_id      = $direccion->pais_id;
        $this->estado_id    = $direccion->estado_id;

        $this->ciudad       = $direccion->ciudad ?? ''; // ✅

        $this->codigo_postal = $direccion->codigo_postal;

        $this->abrirModal();
    }

    public function borrar($id)
    {
        $direccion = DireccionFiscal::findOrFail($id);
        $direccion->delete();
        session()->flash('message', 'Dirección fiscal eliminada exitosamente.');
    }

    public function establecerDefault($id)
    {
        DireccionFiscal::where('usuario_id', $this->userId)->update(['flag_default' => false]);
        $direccion = DireccionFiscal::findOrFail($id);
        $direccion->update(['flag_default' => true]);
        session()->flash('message', '¡Dirección fiscal predeterminada actualizada exitosamente!');
    }
}
<?php

namespace App\Livewire\Usuarios;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ConfiguracionesUsuarioEmpresa extends Component
{
    use WithPagination;

    public $userId;
    public $search = '';
    public $perPage = 10;

    public $showModal = false;
    public $showDeleteModal = false;
    public $showUsuariosModal = false; // ya no se usa, lo dejamos por compatibilidad

    public $empresaId = null;
    public $nombre, $rfc, $telefono, $direccion;

    // Ya no se edita propietario desde aquí, pero dejamos la propiedad por si hay data legacy
    public $usuarioPropietarioId = null;

    public $empresaAEliminar = null;
    public $alertaRelacionUsuarios = false;

    protected $queryString = ['search'];

    protected function rules()
    {
        return [
            'nombre'   => 'required|string|max:255',
            'rfc'      => [
                'required',
                'string',
                'max:20',
                Rule::unique('empresas', 'rfc')->ignore($this->empresaId),
            ],
            'telefono'  => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            // usuarioPropietarioId ya no es requerido ni se usa al guardar
        ];
    }

    public function mount($userId)
    {
        $this->userId = $userId;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Solo empresas cuyo propietario sea el usuario consultado
        $empresas = Empresa::with('propietario')
            ->whereHas('propietario', fn ($q) => $q->where('id', $this->userId))
            ->when($this->search, fn ($q) =>
                $q->where('nombre', 'like', '%' . $this->search . '%')
            )
            ->orderBy('nombre')
            ->paginate($this->perPage);

        return view('livewire.usuarios.configuraciones-usuario-empresa', [
            'empresas' => $empresas,
        ]);
    }
    /**
     * Ya no se permite crear organización principal.
     * Si alguien llegara a disparar esta acción, mostramos error.
     */
    public function nuevaEmpresa()
    {
        $this->dispatch('notify', type: 'error', message: 'Ya no se permite crear organización principal.');
        return;
    }

    public function editarEmpresa($id)
    {
        $empresa = Empresa::with('propietario')
            ->where('id', $id)
            ->whereHas('propietario', fn ($q) => $q->where('id', $this->userId))
            ->firstOrFail();

        $this->empresaId = $empresa->id;
        $this->nombre    = $empresa->nombre;
        $this->rfc       = $empresa->rfc;
        $this->telefono  = $empresa->telefono;
        $this->direccion = $empresa->direccion;

        $this->showModal = true;
    }

    public function guardarEmpresa()
    {
        $this->validate();

        // No se permite crear, solo editar
        if (!$this->empresaId) {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Ya no se permite crear organización principal desde este módulo.'
            );
            return;
        }

        DB::beginTransaction();

        try {
            $empresa = Empresa::lockForUpdate()->findOrFail($this->empresaId);

            $empresa->fill([
                'nombre'   => $this->nombre,
                'rfc'      => $this->rfc,
                'telefono' => $this->telefono,
                'direccion'=> $this->direccion,
            ]);

            $empresa->save();

            // Ya no tocamos propietario aquí

            DB::commit();

            $this->dispatch('notify', type: 'success', message: 'Empresa actualizada correctamente.');
            $this->showModal = false;
            $this->resetForm();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('notify', type: 'error', message: 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function confirmarEliminar($id)
    {
        $empresa = Empresa::with('propietario')->findOrFail($id);
        $this->empresaAEliminar = $empresa->id;
        $this->alertaRelacionUsuarios = (bool) $empresa->propietario; // si tiene propietario, bloquear
        $this->showDeleteModal = true;
    }

    public function eliminarEmpresa()
    {
        $empresa = Empresa::with('propietario')->findOrFail($this->empresaAEliminar);

        if ($empresa->propietario) {
            // Bloquea borrado si hay un usuario que depende de ella (regla 1:1)
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'No puedes eliminar una empresa que tiene un propietario. Transfiere primero.'
            );
            $this->showDeleteModal = false;
            return;
        }

        $empresa->delete();
        $this->dispatch('notify', type: 'success', message: 'Empresa eliminada correctamente.');
        $this->showDeleteModal = false;
    }

    private function resetForm()
    {
        $this->empresaId  = null;
        $this->nombre     = null;
        $this->rfc        = null;
        $this->telefono   = null;
        $this->direccion  = null;
        $this->usuarioPropietarioId = null;
    }
}

<?php

namespace App\Livewire\Usuarios;


use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ConfiguracionesUsuarioEmpresa extends Component
{
    use WithPagination;

    public $userId;
    public $search = '';
    public $perPage = 10;

    public $showModal = false;
    public $showDeleteModal = false;
    public $showUsuariosModal = false; // <- ya no lo usaremos, pero lo dejo si lo referencian

    public $empresaId = null;
    public $nombre, $rfc, $telefono, $direccion;

    // NUEVO: propietario único
    public $usuarioPropietarioId = null;

    public $empresaAEliminar = null;
    public $alertaRelacionUsuarios = false;

    protected $queryString = ['search'];

    protected function rules()
    {
        return [
            'nombre'   => 'required|string|max:255',
            'rfc'      => ['required','string','max:20', Rule::unique('empresas','rfc')->ignore($this->empresaId)],
            'telefono' => 'nullable|string|max:20',
            'direccion'=> 'nullable|string|max:255',
            'usuarioPropietarioId' => ['required','integer','exists:users,id'],
        ];
    }

    public function mount($userId)
    {
        $this->userId = $userId;
    }

    public function updatingSearch() { $this->resetPage(); }

    public function render()
    {
        $user = User::find($this->userId);

        $empresasQuery = Empresa::with('propietario')
            ->when($this->search, fn($q) => $q->where('nombre','like','%'.$this->search.'%'))
            ->orderBy('nombre');

        if (!$user?->hasRole('admin')) {
            // solo su empresa, si tiene
            $empresasQuery->whereHas('propietario', fn($q) => $q->where('id', $this->userId));
        }

        $empresas = $empresasQuery->paginate($this->perPage);

        // Candidatos a propietario: por ejemplo clientes_principales o el universo que decidas
        $usuariosPropietarios = User::query()
            ->role('cliente_principal') // Ajusta si tu lógica permite otros roles
            ->select('id','name','email','empresa_id')
            ->orderBy('name')
            ->get();

        return view('livewire.usuarios.configuraciones-usuario-empresa', [
            'empresas' => $empresas,
            'usuariosPropietarios' => $usuariosPropietarios,
        ]);
    }

    public function nuevaEmpresa()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function editarEmpresa($id)
    {
        $empresa = Empresa::with('propietario')->findOrFail($id);
        $this->empresaId = $empresa->id;
        $this->nombre = $empresa->nombre;
        $this->rfc = $empresa->rfc;
        $this->telefono = $empresa->telefono;
        $this->direccion = $empresa->direccion;
        $this->usuarioPropietarioId = $empresa->propietario?->id; // puede venir null si aún no limpias data vieja
        $this->showModal = true;
    }

    public function guardarEmpresa()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $empresa = $this->empresaId
                ? Empresa::lockForUpdate()->findOrFail($this->empresaId)
                : new Empresa();

            $empresa->fill([
                'nombre'   => $this->nombre,
                'rfc'      => $this->rfc,
                'telefono' => $this->telefono,
                'direccion'=> $this->direccion,
            ]);
            $empresa->save();

            // Verifica si esta empresa ya tiene otro propietario distinto
            $propietarioActual = $empresa->propietario; // por relación

            if ($propietarioActual && $propietarioActual->id !== (int)$this->usuarioPropietarioId) {
                // Para no violar la regla “todo usuario debe tener empresa”, no lo dejamos en null.
                // Opciones:
                //  A) Bloquear con mensaje y pedir reasignación previa
                DB::rollBack();
                $this->dispatch('notify', type: 'error',
                    message: 'Esta empresa ya tiene propietario ('.$propietarioActual->name.'). Reasígnalo antes de cambiarlo.');
                return;
            }

            // Verifica si el usuario seleccionado ya es propietario de OTRA empresa
            $usuarioSel = User::lockForUpdate()->findOrFail($this->usuarioPropietarioId);
            if ($usuarioSel->empresa_id && $usuarioSel->empresa_id !== $empresa->id) {
                DB::rollBack();
                $this->dispatch('notify', type: 'error',
                    message: 'El usuario seleccionado ya tiene otra empresa asignada. Debes transferirla antes.');
                return;
            }

            // Asigna (o confirma) propiedad 1:1
            $usuarioSel->empresa_id = $empresa->id;
            $usuarioSel->save();

            DB::commit();
            $this->dispatch('notify', type: 'success', message: 'Empresa guardada correctamente.');
            $this->showModal = false;
            $this->resetForm();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('notify', type: 'error', message: 'Error al guardar: '.$e->getMessage());
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
            $this->dispatch('notify', type: 'error',
                message: 'No puedes eliminar una empresa que tiene un propietario. Transfiere primero.');
            $this->showDeleteModal = false;
            return;
        }

        $empresa->delete();
        $this->dispatch('notify', type: 'success', message: 'Empresa eliminada correctamente.');
        $this->showDeleteModal = false;
    }

    private function resetForm()
    {
        $this->empresaId = null;
        $this->nombre = null;
        $this->rfc = null;
        $this->telefono = null;
        $this->direccion = null;
        $this->usuarioPropietarioId = null;
    }
}


// use Livewire\Component;

// class ConfiguracionesUsuarioEmpresa extends Component
// {
//     public function render()
//     {
//         return view('livewire.usuarios.configuraciones-usuario-empresa');
//     }
// }

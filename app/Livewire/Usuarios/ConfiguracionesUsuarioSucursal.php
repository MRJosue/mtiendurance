<?php

namespace App\Livewire\Usuarios;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Empresa;
use Illuminate\Support\Facades\DB;

class ConfiguracionesUsuarioSucursal extends Component
{
    use WithPagination;

    public $empresa_id, $nombre, $telefono, $direccion, $tipo = 2, $editingId = null;

    public $showUserModal = false;            // Modal asignación usuarios
    public $showSucursalModal = false;        // Modal crear/editar sucursal
    public $showAssignedOnlyModal = false;    // Modal "Asignados"

    public $selectedSucursal = null;
    public $selectedUsers = [];               
    public $search = '';
    public $userId;
    public $usuariosDisponibles = [];         

    // Activar / Inactivar sucursal
    public bool $showDeactivateModal = false;
    public bool $showActivateModal   = false;
    public ?int $targetSucursalId    = null;

    public array $deactivateStats = [
        'nombre_sucursal' => '',
        'total_usuarios'  => 0,
    ];

    public array $activateStats = [
        'nombre_sucursal' => '',
        'total_usuarios'  => 0,
    ];

    protected $rules = [
        'empresa_id' => 'required|exists:empresas,id',
        'nombre'     => 'required|string|max:255',
        'telefono'   => 'nullable|string|max:30',
        'direccion'  => 'nullable|string|max:255',
        'tipo'       => 'required|in:1,2',
    ];

    public function mount($userId)
    {
        $this->userId = $userId;
        $this->usuariosDisponibles = collect();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Usuarios disponibles de la empresa (puedes filtrar más si quieres)
     */
    protected function cargarUsuariosDisponiblesPorEmpresa(?int $empresaId): void
    {
        if (empty($empresaId)) {
            $this->usuariosDisponibles = collect();
            return;
        }

        $this->usuariosDisponibles = User::where('empresa_id', $empresaId)
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        $user = User::find($this->userId);

        $sucursalesQuery = Sucursal::with(['empresa', 'usuarios'])
            ->where('nombre', 'like', "%{$this->search}%")
            ->orderBy('id', 'desc');

        $empresasQuery = Empresa::orderBy('nombre');

        if ($user && $user->empresa_id) {
            // Siempre se filtra por la empresa del usuario
            $sucursalesQuery->where('empresa_id', $user->empresa_id);
            $empresasQuery->where('id', $user->empresa_id);

            // 🔴 Si NO es propietario, solo mostrar la sucursal a la que está asignado
            if (!$user->es_propietario) {
                if ($user->sucursal_id) {
                    $sucursalesQuery->where('id', $user->sucursal_id);
                } else {
                    // Si no tiene sucursal asignada, no mostramos ninguna
                    $sucursalesQuery->whereRaw('0 = 1');
                }
            }
        } else {
            // Sin empresa, no mostramos nada
            $sucursalesQuery->whereRaw('0=1');
            $empresasQuery->whereRaw('0=1');
        }

        return view('livewire.usuarios.configuraciones-usuario-sucursal', [
            'sucursales' => $sucursalesQuery->paginate(10),
            'empresas'   => $empresasQuery->get(),
            'usuarioActual' => $user, // lo mandamos por si lo quieres usar en la vista
        ]);
    }

    public function resetInput()
    {
        $this->empresa_id = null;
        $this->nombre     = '';
        $this->telefono   = '';
        $this->direccion  = '';
        $this->tipo       = 2;
        $this->editingId  = null;
    }

    /* --------- CRUD en MODAL --------- */

    public function openCreateModal()
    {
        $this->resetInput();
        $this->tipo = 2; // siempre Secundaria

        $user = User::find($this->userId);

        if ($user && $user->empresa_id) {
            $this->empresa_id = $user->empresa_id;
        } else {
            $this->dispatch('notify', message: 'El usuario no tiene organización asignada.');
            return;
        }

        $this->showSucursalModal = true;
    }

    public function openEditModal($id)
    {
        $user = User::find($this->userId);

        $s = Sucursal::where('id', $id)
            ->when($user && $user->empresa_id, fn ($q) =>
                $q->where('empresa_id', $user->empresa_id)
            )
            ->firstOrFail();

        $this->editingId    = $s->id;
        $this->empresa_id   = $s->empresa_id;
        $this->nombre       = $s->nombre;
        $this->telefono     = $s->telefono;
        $this->direccion    = $s->direccion;
        $this->tipo         = (int) ($s->tipo ?? 2);
        $this->showSucursalModal = true;
    }

    public function closeSucursalModal()
    {
        $this->showSucursalModal = false;
    }

    public function store()
    {
        $this->tipo = 2;

        $user = User::find($this->userId);

        if (!$user || !$user->empresa_id) {
            $this->dispatch('notify', message: 'El usuario no tiene organización asignada.');
            return;
        }

        $this->empresa_id = $user->empresa_id;

        $this->validate();

        Sucursal::create([
            'empresa_id' => $this->empresa_id,
            'nombre'     => $this->nombre,
            'telefono'   => $this->telefono,
            'direccion'  => $this->direccion,
            'tipo'       => $this->tipo,
            'ind_activo' => 1,
        ]);

        $this->resetInput();
        $this->showSucursalModal = false;
        $this->dispatch('notify', message: 'Sucursal creada correctamente');
    }

    public function update()
    {
        $user = User::find($this->userId);

        if (!$user || !$user->empresa_id) {
            $this->dispatch('notify', message: 'El usuario no tiene organización asignada.');
            return;
        }

        $this->empresa_id = $user->empresa_id;

        $this->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'nombre'     => 'required|string|max:255',
            'telefono'   => 'nullable|string|max:30',
            'direccion'  => 'nullable|string|max:255',
        ]);

        $sucursal = Sucursal::where('id', $this->editingId)
            ->where('empresa_id', $user->empresa_id)
            ->firstOrFail();

        $sucursal->update([
            'empresa_id' => $this->empresa_id,
            'nombre'     => $this->nombre,
            'telefono'   => $this->telefono,
            'direccion'  => $this->direccion,
            'tipo'       => $sucursal->tipo, // no se toca
        ]);

        $this->resetInput();
        $this->showSucursalModal = false;
        $this->dispatch('notify', message: 'Sucursal actualizada correctamente');
    }

    /* --------- Activar / Inactivar sucursal --------- */

    public function openDeactivateModal(int $id): void
    {
        $user = User::find($this->userId);

        $sucursal = Sucursal::where('id', $id)
            ->when($user && $user->empresa_id, fn ($q) =>
                $q->where('empresa_id', $user->empresa_id)
            )
            ->firstOrFail();

        $this->targetSucursalId = $sucursal->id;

        $totalUsuarios = User::where('sucursal_id', $sucursal->id)->count();

        $this->deactivateStats = [
            'nombre_sucursal' => $sucursal->nombre,
            'total_usuarios'  => $totalUsuarios,
        ];

        $this->showDeactivateModal = true;
    }

    public function inactivarSucursalConfirmada(): void
    {
        if (!$this->targetSucursalId) {
            return;
        }

        $user = User::find($this->userId);

        $sucursal = Sucursal::where('id', $this->targetSucursalId)
            ->when($user && $user->empresa_id, fn ($q) =>
                $q->where('empresa_id', $user->empresa_id)
            )
            ->firstOrFail();

        $sucursal->update(['ind_activo' => 0]);

        $this->showDeactivateModal = false;
        $this->targetSucursalId = null;

        $this->dispatch('notify', message: 'Sucursal inactivada correctamente');
    }

    public function openActivateModal(int $id): void
    {
        $user = User::find($this->userId);

        $sucursal = Sucursal::where('id', $id)
            ->when($user && $user->empresa_id, fn ($q) =>
                $q->where('empresa_id', $user->empresa_id)
            )
            ->firstOrFail();

        $this->targetSucursalId = $sucursal->id;

        $totalUsuarios = User::where('sucursal_id', $sucursal->id)->count();

        $this->activateStats = [
            'nombre_sucursal' => $sucursal->nombre,
            'total_usuarios'  => $totalUsuarios,
        ];

        $this->showActivateModal = true;
    }

    public function activarSucursalConfirmada(): void
    {
        if (!$this->targetSucursalId) {
            return;
        }

        $user = User::find($this->userId);

        $sucursal = Sucursal::where('id', $this->targetSucursalId)
            ->when($user && $user->empresa_id, fn ($q) =>
                $q->where('empresa_id', $user->empresa_id)
            )
            ->firstOrFail();

        $sucursal->update(['ind_activo' => 1]);

        $this->showActivateModal = false;
        $this->targetSucursalId = null;

        $this->dispatch('notify', message: 'Sucursal activada correctamente');
    }

    /* --------- Usuarios por sucursal --------- */

    public function openUserModal($sucursalId)
    {
        $user = User::find($this->userId);

        $this->selectedSucursal = Sucursal::with('usuarios')
            ->where('id', $sucursalId)
            ->when($user && $user->empresa_id, fn ($q) =>
                $q->where('empresa_id', $user->empresa_id)
            )
            ->firstOrFail();

        $this->cargarUsuariosDisponiblesPorEmpresa($this->selectedSucursal->empresa_id);

        $this->selectedUsers = $this->selectedSucursal
            ->usuarios()
            ->pluck('id')
            ->toArray();

        $this->showUserModal = true;
    }

    public function assignUserToSucursal(int $userId): void
    {
        if (!$this->selectedSucursal) {
            return;
        }

        if (!$this->usuariosDisponibles->contains('id', $userId)) {
            return;
        }

        User::whereKey($userId)->update(['sucursal_id' => $this->selectedSucursal->id]);

        $this->selectedSucursal->load('usuarios');
        $this->selectedUsers = $this->selectedSucursal->usuarios()->pluck('id')->toArray();

        $this->cargarUsuariosDisponiblesPorEmpresa($this->selectedSucursal->empresa_id);

        $this->dispatch('notify', message: 'Usuario asignado a la sucursal');
        $this->dispatch('refreshMakeuser');
    }

    public function openAssignedOnlyModal($sucursalId)
    {
        $user = User::find($this->userId);

        $this->selectedSucursal = Sucursal::with('usuarios')
            ->where('id', $sucursalId)
            ->when($user && $user->empresa_id, fn ($q) =>
                $q->where('empresa_id', $user->empresa_id)
            )
            ->firstOrFail();

        $this->selectedUsers = $this->selectedSucursal
            ->usuarios()
            ->pluck('id')
            ->toArray();

        $this->showAssignedOnlyModal = true;
    }

    public function closeAssignedOnlyModal()
    {
        $this->showAssignedOnlyModal = false;
        $this->selectedSucursal = null;
        $this->selectedUsers = [];
    }

    public function removeUserFromSucursal(int $userId): void
    {
        if (!$this->selectedSucursal) {
            return;
        }

        DB::transaction(function () use ($userId) {
            User::whereKey($userId)
                ->where('sucursal_id', $this->selectedSucursal->id)
                ->update(['sucursal_id' => null]);
        });

        $this->selectedSucursal->load('usuarios');
        $this->selectedUsers = $this->selectedSucursal->usuarios()->pluck('id')->toArray();

        $this->dispatch('notify', message: 'Usuario removido de la sucursal');
        $this->dispatch('refreshMakeuser');
    }

    public function saveUsersToSucursal()
    {
        if (!$this->selectedSucursal) {
            return;
        }

        DB::transaction(function () {
            $antes = $this->selectedSucursal->usuarios()->pluck('id')->toArray();

            if (!empty($this->selectedUsers)) {
                User::whereIn('id', $this->selectedUsers)
                    ->update(['sucursal_id' => $this->selectedSucursal->id]);
            }

            $aNull = array_diff($antes, $this->selectedUsers);
            if (!empty($aNull)) {
                User::whereIn('id', $aNull)
                    ->where('sucursal_id', $this->selectedSucursal->id)
                    ->update(['sucursal_id' => null]);
            }
        });

        $this->dispatch('notify', message: 'Usuarios asignados a la sucursal');
        $this->showUserModal = false;
        $this->dispatch('refreshMakeuser');
    }

    public function closeUserModal()
    {
        $this->showUserModal = false;
        $this->selectedSucursal = null;
        $this->selectedUsers = [];
    }
}

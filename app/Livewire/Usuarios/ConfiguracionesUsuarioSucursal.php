<?php

namespace App\Livewire\Usuarios;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Empresa;

class ConfiguracionesUsuarioSucursal extends Component
{
    use WithPagination;

    public $empresa_id, $nombre, $telefono, $direccion, $editingId = null;
    public $showUserModal = false;         // Modal asignación usuarios
    public $showSucursalModal = false;     // Modal crear/editar sucursal
    public $selectedSucursal = null;
    public $selectedUsers = [];            // ids asignados (estado actual)
    public $search = '';
    public $userId;
    public $usuariosDisponibles = [];      // universo permitido (subordinados)

    protected $rules = [
        'empresa_id' => 'required|exists:empresas,id',
        'nombre'     => 'required|string|max:255',
        'telefono'   => 'nullable|string|max:30',
        'direccion'  => 'nullable|string|max:255',
    ];

    public function mount($userId)
    {
        $this->userId = $userId;
        // Se cargan al abrir el modal según la empresa de la sucursal
        $this->usuariosDisponibles = collect();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    protected function cargarUsuariosDisponiblesPorEmpresa(?int $empresaId): void
    {
        if (empty($empresaId)) {
            $this->usuariosDisponibles = collect(); // nada si no hay empresa
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

        if (!$user->hasRole('admin')) {
            if ($user->empresa_id) {
                $sucursalesQuery->where('empresa_id', $user->empresa_id);
                $empresasQuery->where('id', $user->empresa_id);
            } else {
                $sucursalesQuery->whereRaw('0=1');
                $empresasQuery->whereRaw('0=1');
            }
        }

        return view('livewire.usuarios.configuraciones-usuario-sucursal', [
            'sucursales' => $sucursalesQuery->paginate(10),
            'empresas'   => $empresasQuery->get(),
        ]);
    }

    public function resetInput()
    {
        $this->empresa_id = null;
        $this->nombre     = '';
        $this->telefono   = '';
        $this->direccion  = '';
        $this->editingId  = null;
    }

    /* --------- CRUD en MODAL --------- */

    public function openCreateModal()
    {
        $this->resetInput();
        $this->showSucursalModal = true;
    }

    public function openEditModal($id)
    {
        $sucursal           = Sucursal::findOrFail($id);
        $this->editingId    = $sucursal->id;
        $this->empresa_id   = $sucursal->empresa_id;
        $this->nombre       = $sucursal->nombre;
        $this->telefono     = $sucursal->telefono;
        $this->direccion    = $sucursal->direccion;
        $this->showSucursalModal = true;
    }

    public function closeSucursalModal()
    {
        $this->showSucursalModal = false;
    }

    public function store()
    {
        $this->validate();

        Sucursal::create([
            'empresa_id' => $this->empresa_id,
            'nombre'     => $this->nombre,
            'telefono'   => $this->telefono,
            'direccion'  => $this->direccion,
        ]);

        $this->resetInput();
        $this->showSucursalModal = false;
        $this->dispatch('notify', message: 'Sucursal creada correctamente');
    }

    public function update()
    {
        $this->validate();

        $sucursal = Sucursal::findOrFail($this->editingId);
        $sucursal->update([
            'empresa_id' => $this->empresa_id,
            'nombre'     => $this->nombre,
            'telefono'   => $this->telefono,
            'direccion'  => $this->direccion,
        ]);

        $this->resetInput();
        $this->showSucursalModal = false;
        $this->dispatch('notify', message: 'Sucursal actualizada correctamente');
    }

    public function delete($id)
    {
        Sucursal::destroy($id);
        $this->dispatch('notify', message: 'Sucursal eliminada');
    }

    /* --------- Modal de usuarios por sucursal --------- */

    public function openUserModal($sucursalId)
    {
        $this->selectedSucursal = Sucursal::with('usuarios')->findOrFail($sucursalId);

        // Cargar SOLO usuarios de la misma empresa de la sucursal
        $this->cargarUsuariosDisponiblesPorEmpresa($this->selectedSucursal->empresa_id);

        // ids actualmente asignados (pivot sucursal_user)
        $this->selectedUsers = $this->selectedSucursal
            ->usuarios()
            ->pluck('users.id')
            ->toArray();

        $this->showUserModal = true;
    }


    // Asignación inmediata (desde "pendientes" -> "asignados")
    public function assignUserToSucursal(int $userId): void
    {
        if (!$this->selectedSucursal) return;

        // Garantiza que el usuario pertenece a la misma empresa que la sucursal
        $esMiEmpresa = $this->usuariosDisponibles->contains('id', $userId);
        if (!$esMiEmpresa) return;

        $this->selectedSucursal->usuarios()->syncWithoutDetaching([$userId]);

        $this->selectedUsers = $this->selectedSucursal->usuarios()->pluck('users.id')->toArray();
        $this->dispatch('notify', message: 'Usuario asignado a la sucursal');
    }

    // Remoción inmediata (desde "asignados" -> "pendientes")
    public function removeUserFromSucursal(int $userId): void
    {
        if (!$this->selectedSucursal) return;

        $this->selectedSucursal->usuarios()->detach($userId);
        $this->selectedUsers = $this->selectedSucursal->usuarios()->pluck('users.id')->toArray();
        $this->dispatch('notify', message: 'Usuario removido de la sucursal');
    }

    // (Opcional) Guardado masivo si usaras checkboxes – lo dejamos por compatibilidad
    public function saveUsersToSucursal()
    {
        if (!$this->selectedSucursal) return;

        $this->selectedSucursal->usuarios()->sync($this->selectedUsers);
        $this->dispatch('notify', message: 'Usuarios asignados a la sucursal');
        $this->showUserModal = false;
    }

    public function closeUserModal()
    {
        $this->showUserModal = false;
        $this->selectedSucursal = null;
        $this->selectedUsers = [];
    }
}

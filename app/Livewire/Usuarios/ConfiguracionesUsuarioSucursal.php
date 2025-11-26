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
    public $selectedUsers = [];               // ids asignados (estado actual)
    public $search = '';
    public $userId;
    public $usuariosDisponibles = [];         // universo permitido (subordinados)

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
     * Usuarios disponibles = los que pertenecen a la organización (empresa_id)
     * pero todavía NO tienen sucursal asignada (sucursal_id = null).
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
            // SIEMPRE solo la empresa del usuario consultado
            $sucursalesQuery->where('empresa_id', $user->empresa_id);
            $empresasQuery->where('id', $user->empresa_id);
        } else {
            // Usuario sin empresa => no ve nada
            $sucursalesQuery->whereRaw('0=1');
            $empresasQuery->whereRaw('0=1');
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
        $this->tipo       = 2;   // por defecto "Secundaria"
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
            // Si no tiene empresa, no permitimos crear
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
        // En creación el tipo SIEMPRE será Secundaria
        $this->tipo = 2;

        $user = User::find($this->userId);

        if (!$user || !$user->empresa_id) {
            $this->dispatch('notify', message: 'El usuario no tiene organización asignada.');
            return;
        }

        // Blindamos empresa_id para que no se pueda cambiar vía Livewire
        $this->empresa_id = $user->empresa_id;

        $this->validate();

        Sucursal::create([
            'empresa_id' => $this->empresa_id,
            'nombre'     => $this->nombre,
            'telefono'   => $this->telefono,
            'direccion'  => $this->direccion,
            'tipo'       => $this->tipo,
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

        // Blindamos empresa_id
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


    public function delete($id)
    {
        $user = User::find($this->userId);

        $sucursal = Sucursal::where('id', $id)
            ->when($user && $user->empresa_id, fn ($q) =>
                $q->where('empresa_id', $user->empresa_id)
            )
            ->firstOrFail();

        $sucursal->delete();

        $this->dispatch('notify', message: 'Sucursal eliminada');
    }


    /* --------- Modal de usuarios por sucursal --------- */

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


    // Asignación inmediata (desde "disponibles" -> "asignados")
    public function assignUserToSucursal(int $userId): void
    {
        if (!$this->selectedSucursal) {
            return;
        }

        // Asegura que viene de la misma empresa y sin sucursal asignada
        if (!$this->usuariosDisponibles->contains('id', $userId)) {
            return;
        }

        User::whereKey($userId)->update(['sucursal_id' => $this->selectedSucursal->id]);

        // Refresca listas
        $this->selectedSucursal->load('usuarios');
        $this->selectedUsers = $this->selectedSucursal->usuarios()->pluck('id')->toArray();

        // Recalcula disponibles (los que quedan con sucursal_id null)
        $this->cargarUsuariosDisponiblesPorEmpresa($this->selectedSucursal->empresa_id);

        $this->dispatch('notify', message: 'Usuario asignado a la sucursal');
        $this->dispatch('refreshMakeuser');
    }

    // Modal SOLO de asignados (lectura)
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

    // Remoción inmediata de la sucursal (sin pivote)
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

    // Guardado masivo usando sucursal_id directo (por si algún día usas checkboxes)
    public function saveUsersToSucursal()
    {
        if (!$this->selectedSucursal) {
            return;
        }

        DB::transaction(function () {
            // Usuarios que ya estaban asignados antes
            $antes = $this->selectedSucursal->usuarios()->pluck('id')->toArray();

            // a) Asignar sucursal_id a los seleccionados
            if (!empty($this->selectedUsers)) {
                User::whereIn('id', $this->selectedUsers)
                    ->update(['sucursal_id' => $this->selectedSucursal->id]);
            }

            // b) Quitar sucursal_id a los que ya no están seleccionados
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

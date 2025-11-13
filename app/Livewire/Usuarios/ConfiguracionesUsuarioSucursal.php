<?php

namespace App\Livewire\Usuarios;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Empresa;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;


class ConfiguracionesUsuarioSucursal extends Component
{
    use WithPagination;

    public $empresa_id, $nombre, $telefono, $direccion, $tipo = 2, $editingId = null;
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
         'tipo'       => 'required|in:1,2',
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
        $this->tipo       = 2;   // por defecto "Secundaria"
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
        $s = Sucursal::findOrFail($id);
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
        $this->validate();

        $sucursal = Sucursal::findOrFail($this->editingId);
        $sucursal->update([
            'empresa_id' => $this->empresa_id,
            'nombre'     => $this->nombre,
            'telefono'   => $this->telefono,
            'direccion'  => $this->direccion,
            'tipo'       => $this->tipo,
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
        $this->cargarUsuariosDisponiblesPorEmpresa($this->selectedSucursal->empresa_id);

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

        // Asegura que viene de la misma empresa y sin sucursal asignada (como se cargó)
        if (!$this->usuariosDisponibles->contains('id', $userId)) return;

        User::whereKey($userId)->update(['sucursal_id' => $this->selectedSucursal->id]);

        // Refresca listas
        $this->selectedSucursal->load('usuarios');
        $this->selectedUsers = $this->selectedSucursal->usuarios()->pluck('users.id')->toArray();

        // Recalcula disponibles (los que quedan con sucursal_id null)
        $this->cargarUsuariosDisponiblesPorEmpresa($this->selectedSucursal->empresa_id);

        $this->dispatch('notify', message: 'Usuario asignado a la sucursal');
        $this->dispatch('refreshMakeuser');
    }


        // NUEVO: Modal SOLO de asignados (lectura)
    public function openAssignedOnlyModal($sucursalId)
    {
        $this->selectedSucursal = Sucursal::with('usuarios')->findOrFail($sucursalId);
        $this->selectedUsers = $this->selectedSucursal
            ->usuarios()
            ->pluck('users.id')
            ->toArray();

        $this->showAssignedOnlyModal = true;
    }

        public function closeAssignedOnlyModal()
    {
        $this->showAssignedOnlyModal = false;
        $this->selectedSucursal = null;
        $this->selectedUsers = [];
    }

    // Remoción inmediata
    public function removeUserFromSucursal(int $userId): void
    {
        if (!$this->selectedSucursal) return;

        DB::transaction(function () use ($userId) {
            // 1) Quita en pivote
            $this->selectedSucursal->usuarios()->detach($userId);

            // 2) Solo nulifica si el users.sucursal_id coincide con la sucursal actual
            User::whereKey($userId)
                ->where('sucursal_id', $this->selectedSucursal->id)
                ->update(['sucursal_id' => null]);
        });

        // Refresca estado local del modal
        $this->selectedUsers = $this->selectedSucursal->usuarios()->pluck('users.id')->toArray();
        $this->selectedSucursal->load('usuarios');

        // Notifica UI
        $this->dispatch('notify', message: 'Usuario removido de la sucursal');

        // Actualiza el otro componente
        $this->dispatch('refreshMakeuser');
    }

    // (Opcional) Guardado masivo si usaras checkboxes – lo dejamos por compatibilidad
    public function saveUsersToSucursal()
    {
        if (!$this->selectedSucursal) return;

        DB::transaction(function () {
            // Sincroniza pivote a la lista seleccionada
            $this->selectedSucursal->usuarios()->sync($this->selectedUsers);

            // Actualiza campo directo en users:
            //  a) A todos los seleccionados -> set sucursal_id actual
            User::whereIn('id', $this->selectedUsers)
                ->update(['sucursal_id' => $this->selectedSucursal->id]);

            //  b) A todos los NO seleccionados que antes estaban en esta sucursal -> null
            $antes = User::where('sucursal_id', $this->selectedSucursal->id)
                ->pluck('id')
                ->toArray();

            $aNull = array_diff($antes, $this->selectedUsers);
            if (!empty($aNull)) {
                User::whereIn('id', $aNull)->update(['sucursal_id' => null]);
            }
        });

        $this->dispatch('notify', message: 'Usuarios asignados a la sucursal');
        $this->showUserModal = false;

        // Refresca el otro componente
        $this->dispatch('refreshMakeuser');
    }

    public function closeUserModal()
    {
        $this->showUserModal = false;
        $this->selectedSucursal = null;
        $this->selectedUsers = [];
    }
}

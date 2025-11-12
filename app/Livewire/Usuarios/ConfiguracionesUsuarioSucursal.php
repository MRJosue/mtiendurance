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

    // Modales
    public $showUserModal = false;
    public $showSucursalModal = false;
    public $showAssignedOnlyModal = false;

    // Estado
    public $selectedSucursal = null;
    public $selectedUsers = []; // (compat) ids asignados si usas sync masivo
    public $search = '';
    public $userId;

    // Colección de usuarios disponibles (misma empresa, sucursal_id = null)
    public $usuariosDisponibles;

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
        $this->usuariosDisponibles = collect(); // se llena al abrir modal por empresa
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Carga usuarios disponibles SOLO con:
     *  - misma empresa
     *  - sucursal_id = null
     *  - ordenados por nombre
     */
    protected function cargarUsuariosDisponiblesPorEmpresa(?int $empresaId): void
    {
        if (empty($empresaId)) {
            $this->usuariosDisponibles = collect();
            return;
        }

        $this->usuariosDisponibles = User::where('empresa_id', $empresaId)
            ->whereNull('sucursal_id')
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        $user = User::find($this->userId);

        $sucursalesQuery = Sucursal::with('empresa')
            ->where('nombre', 'like', "%{$this->search}%")
            ->orderBy('id', 'desc');

        $empresasQuery = Empresa::orderBy('nombre');

        if (!$user || !$user->hasRole('admin')) {
            if ($user && $user->empresa_id) {
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
        $this->tipo       = 2;
        $this->editingId  = null;
    }

    /** --------- CRUD Sucursal (en modal) --------- */

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
        $sucursal = Sucursal::findOrFail($id);

        DB::transaction(function () use ($sucursal) {
            // 1) Nulificar users.sucursal_id de esta sucursal
            User::where('sucursal_id', $sucursal->id)->update(['sucursal_id' => null]);
            // 2) Si hay pivote, detach
            if (method_exists($sucursal, 'usuarios')) {
                $sucursal->usuarios()->detach();
            }
            // 3) Eliminar sucursal
            $sucursal->delete();
        });

        $this->dispatch('notify', message: 'Sucursal eliminada');
        $this->dispatch('refreshMakeuser'); // para sincronizar otras vistas
    }

    /** --------- Modal Usuarios por Sucursal --------- */

    public function openUserModal($sucursalId)
    {
        $this->selectedSucursal = Sucursal::findOrFail($sucursalId);

        // Carga disponibles exclusivamente como solicitaste
        $this->cargarUsuariosDisponiblesPorEmpresa($this->selectedSucursal->empresa_id);

        // (compat) lista de ids actualmente asignados por campo sucursal_id
        $this->selectedUsers = User::where('sucursal_id', $this->selectedSucursal->id)->pluck('id')->toArray();

        $this->showUserModal = true;
    }

    public function closeUserModal()
    {
        $this->showUserModal = false;
        $this->selectedSucursal = null;
        $this->selectedUsers = [];
        $this->usuariosDisponibles = collect();
    }

    /** Asignación inmediata: disponible -> asignado */
    public function assignUserToSucursal(int $userId): void
    {
        if (!$this->selectedSucursal) return;

        // Seguridad: debe pertenecer a la empresa y no tener sucursal
        $permitido = User::where('id', $userId)
            ->where('empresa_id', $this->selectedSucursal->empresa_id)
            ->whereNull('sucursal_id')
            ->exists();

        if (!$permitido) return;

        DB::transaction(function () use ($userId) {
            User::whereKey($userId)->update(['sucursal_id' => $this->selectedSucursal->id]);

            // (compat) asegurar pivote si lo usas en otros lados
            if (method_exists($this->selectedSucursal, 'usuarios')) {
                $this->selectedSucursal->usuarios()->syncWithoutDetaching([$userId]);
            }
        });

        // Refrescar estado
        $this->selectedUsers = User::where('sucursal_id', $this->selectedSucursal->id)->pluck('id')->toArray();
        $this->cargarUsuariosDisponiblesPorEmpresa($this->selectedSucursal->empresa_id);

        $this->dispatch('notify', message: 'Usuario asignado a la sucursal');
        $this->dispatch('refreshMakeuser');
    }

    /** Remoción inmediata: asignado -> disponible (sucursal_id = null) */
    public function removeUserFromSucursal(int $userId): void
    {
        if (!$this->selectedSucursal) return;

        DB::transaction(function () use ($userId) {
            // nulifica sólo si pertenece a esta sucursal
            User::whereKey($userId)
                ->where('sucursal_id', $this->selectedSucursal->id)
                ->update(['sucursal_id' => null]);

            // (compat) pivote
            if (method_exists($this->selectedSucursal, 'usuarios')) {
                $this->selectedSucursal->usuarios()->detach($userId);
            }
        });

        // Refrescar estado
        $this->selectedUsers = User::where('sucursal_id', $this->selectedSucursal->id)->pluck('id')->toArray();
        $this->cargarUsuariosDisponiblesPorEmpresa($this->selectedSucursal->empresa_id);

        $this->dispatch('notify', message: 'Usuario removido de la sucursal');
        $this->dispatch('refreshMakeuser');
    }

    /** Modal de solo lectura (asignados) */
    public function openAssignedOnlyModal($sucursalId)
    {
        $this->selectedSucursal = Sucursal::findOrFail($sucursalId);
        $this->selectedUsers = User::where('sucursal_id', $this->selectedSucursal->id)->pluck('id')->toArray();
        $this->showAssignedOnlyModal = true;
    }

    public function closeAssignedOnlyModal()
    {
        $this->showAssignedOnlyModal = false;
        $this->selectedSucursal = null;
        $this->selectedUsers = [];
    }

    /** (Opcional) Guardado masivo si usas checkboxes en otra variante */
    public function saveUsersToSucursal()
    {
        if (!$this->selectedSucursal) return;

        DB::transaction(function () {
            // Set a seleccionados
            User::whereIn('id', $this->selectedUsers)
                ->update(['sucursal_id' => $this->selectedSucursal->id]);

            // Set a null a los que estaban con esta sucursal y ya no están seleccionados
            $antes = User::where('sucursal_id', $this->selectedSucursal->id)->pluck('id')->toArray();
            $aNull = array_diff($antes, $this->selectedUsers);
            if (!empty($aNull)) {
                User::whereIn('id', $aNull)->update(['sucursal_id' => null]);
            }

            // (compat) sincroniza pivote si aplica
            if (method_exists($this->selectedSucursal, 'usuarios')) {
                $this->selectedSucursal->usuarios()->sync($this->selectedUsers);
            }
        });

        $this->dispatch('notify', message: 'Usuarios asignados a la sucursal');
        $this->showUserModal = false;
        $this->dispatch('refreshMakeuser');
    }
}

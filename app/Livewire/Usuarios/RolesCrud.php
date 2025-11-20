<?php

namespace App\Livewire\Usuarios;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use App\Models\GrupoOrden;
use App\Models\Permission; // TU modelo extendido
use Illuminate\Validation\Rule;

class RolesCrud extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // ====== Estado general / búsqueda ======
    public $query = '';
    public $search = '';

    // ====== Modales ======
    public $modalRol = false;
    public $modalPermiso = false;
    public $modalGrupo = false;
    public $modalConfirm = false;

    // ====== Confirmación de eliminación ======
    public $confirmType = null; // 'rol' | 'permiso' | 'grupo'
    public $confirmId = null;
    public $confirmName = null;

    // ====== Form Rol ======
    public $role_id = null;
    public $nombreRol = '';
    public $tipoRol = null; // 1=CLIENTE,2=PROVEEDOR,3=STAFF,4=ADMIN

    // ====== Form Permiso ======
    public $permiso_id = null;
    public $permiso_name = '';    // machine
    public $permiso_nombre = '';  // visible
    public $permiso_guard = 'web';
    public $permiso_orden = null;
    public $permiso_type_id = null;
    public $permiso_grupo_id = null;   // opcional: asignarlo a un grupo con orden
    public $permiso_grupo_orden = null;

    // ====== Form Grupo ======
    public $grupo_id = null;
    public $grupo_nombre = '';
    public $grupo_slug = '';
    public $grupo_orden = null;

    // Nuevas: selección/orden de permisos dentro del grupo
    public $grupo_permisos_sel = [];     // [permission_id, ...]
    public $grupo_permisos_orden = [];   // [permission_id => orden]

    // ====== Listeners (para togglePermiso desde JS) ======
    protected $listeners = ['togglePermiso' => 'togglePermiso'];

    /* ============ RENDER ============ */

    public function render()
    {
        // Sincroniza search con query (debounce desde Blade)
        $this->search = trim($this->query ?? '');

        $roles = Role::query()
            ->select(['id', 'name', 'guard_name', 'tipo'])
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', '%' . $this->search . '%')
            )
            ->orderBy('name');

        // Permisos-id por rol para la columna "Permisos por Grupo"
        $rolesList = $roles
            ->with(['permissions:id,name'])
            ->paginate(10);

        foreach ($rolesList as $rol) {
            $rol->permissions_ids = $rol->permissions->pluck('id')->all();
        }

        // Grupos de permisos
        $grupos = GrupoOrden::query()
            ->select(['id','nombre','slug','orden'])
            ->withCount('permissions')
            ->with(['permissions' => function ($q) {
                $q->select('permissions.id','permissions.name','permissions.nombre')
                  ->orderBy('grupo_orden_permission.orden')
                  ->orderBy('permissions.name');
            }])
            ->orderBy('orden')->orderBy('nombre')
            ->get();

        // Permisos por tipo (para modales de grupos / permisos)
        $permisos = Permission::select(['id','name','nombre','orden','permission_type_id'])
            ->with(['type:id,nombre'])
            ->orderByRaw('CASE WHEN permission_type_id IS NULL THEN 1 ELSE 0 END')
            ->orderByRaw('COALESCE(permission_type_id, 999999)')
            ->orderByRaw('CASE WHEN orden IS NULL THEN 1 ELSE 0 END')
            ->orderBy('orden')->orderBy('name')
            ->get();

        $permisosByType = $permisos->groupBy(fn($p) => $p->type->nombre ?? '— Sin tipo —');

        return view('livewire.usuarios.roles-crud', [
            'rolesList'      => $rolesList,
            'grupos'         => $grupos,
            'permisos'       => $permisos,
            'types'          => \DB::table('permission_types')->select(['id','nombre','orden'])->orderBy('orden')->get(),
            'permisosByType' => $permisosByType,
        ]);
    }

    /* ============ BÚSQUEDA ============ */

    public function buscar()
    {
        $this->search = $this->query;
        $this->resetPage();
    }

    /* ============ ROL: NUEVO / EDITAR / GUARDAR / ELIMINAR ============ */

    public function nuevoRol()
    {
        $this->resetRolForm();
        $this->modalRol = true;
    }

    public function editarRol($id)
    {
        $rol = Role::findOrFail($id);
        $this->role_id   = $rol->id;
        $this->nombreRol = $rol->name;
        $this->tipoRol   = $rol->tipo; // puede ser null si aún no se ha seteado
        $this->modalRol  = true;
    }

    public function guardarRol()
    {
        $this->validate([
            'nombreRol' => ['required', 'string', 'max:255'],
            'tipoRol'   => ['required', 'integer', 'in:1,2,3,4'],
        ]);

        $rol = $this->role_id
            ? Role::findOrFail($this->role_id)
            : new Role();

        $rol->name       = $this->nombreRol;
        $rol->guard_name = $rol->guard_name ?? 'web';
        $rol->tipo       = $this->tipoRol;
        $rol->save();

        // ⚠️ Ya NO tocamos permisos del rol aquí (ni syncPermissions)

        $this->dispatch('toast', ['type' => 'success', 'msg' => 'Rol guardado correctamente.']);
        $this->modalRol = false;
        $this->resetRolForm();
        $this->resetPage();
    }

    public function confirmarEliminarRol($id)
    {
        $rol = Role::findOrFail($id);
        $this->confirmType = 'rol';
        $this->confirmId   = $rol->id;
        $this->confirmName = $rol->name;
        $this->modalConfirm = true;
    }

    public function eliminarRol()
    {
        if ($this->confirmType === 'rol' && $this->confirmId) {
            $rol = Role::find($this->confirmId);
            if ($rol) {
                // Opcional: limpiar relaciones de permisos al borrar
                $rol->syncPermissions([]);
                $rol->delete();
                $this->dispatch('toast', ['type' => 'success', 'msg' => 'Rol eliminado.']);
            }
        }
        $this->cerrarConfirm();
    }

    protected function resetRolForm()
    {
        $this->role_id   = null;
        $this->nombreRol = '';
        $this->tipoRol   = null;
    }

    /* ============ PERMISOS (MODAL PERMISO) ============ */

    public function nuevoPermiso()
    {
        $this->resetPermisoForm();
        $this->modalPermiso = true;
    }

    public function editarPermiso($id)
    {
        $p = Permission::findOrFail($id);
        $this->permiso_id     = $p->id;
        $this->permiso_name   = $p->name;
        $this->permiso_nombre = $p->nombre ?? '';
        $this->permiso_guard  = $p->guard_name ?? 'web';
        $this->permiso_orden  = $p->orden;
        $this->permiso_type_id = $p->permission_type_id;

        $grupo = $p->groups()->first();
        $this->permiso_grupo_id    = $grupo?->id;
        $this->permiso_grupo_orden = $grupo?->pivot?->orden;

        $this->modalPermiso = true;
    }

    public function guardarPermiso()
    {
        $this->validate([
            'permiso_name'       => ['required','string','max:255', Rule::unique('permissions','name')->ignore($this->permiso_id)],
            'permiso_guard'      => ['required','string','max:50'],
            'permiso_nombre'     => ['nullable','string','max:255'],
            'permiso_orden'      => ['nullable','integer','min:0'],
            'permiso_type_id'    => ['nullable','integer','exists:permission_types,id'],
            'permiso_grupo_id'   => ['nullable','integer'],
            'permiso_grupo_orden'=> ['nullable','integer','min:0'],
        ]);

        $p = $this->permiso_id ? Permission::findOrFail($this->permiso_id) : new Permission();
        $p->name              = $this->permiso_name;
        $p->guard_name        = $this->permiso_guard;
        $p->nombre            = $this->permiso_nombre ?: $this->permiso_name;
        $p->orden             = $this->permiso_orden;
        $p->permission_type_id= $this->permiso_type_id;
        $p->save();

        if ($this->permiso_grupo_id) {
            $g = GrupoOrden::find($this->permiso_grupo_id);
            if ($g) {
                $g->permissions()->syncWithoutDetaching([
                    $p->id => ['orden' => $this->permiso_grupo_orden ?? 0]
                ]);
            }
        }

        $this->dispatch('toast', ['type' => 'success', 'msg' => 'Permiso guardado correctamente.']);
        $this->modalPermiso = false;
        $this->resetPermisoForm();
        $this->resetPage();
    }

    public function confirmarEliminarPermiso($id)
    {
        $p = Permission::findOrFail($id);
        $this->confirmType = 'permiso';
        $this->confirmId   = $p->id;
        $this->confirmName = $p->nombre ?? $p->name;
        $this->modalConfirm = true;
    }

    public function eliminarPermiso()
    {
        if ($this->confirmType === 'permiso' && $this->confirmId) {
            $p = Permission::find($this->confirmId);
            if ($p) {
                $p->roles()->detach();
                if (method_exists($p, 'groups')) {
                    $p->groups()->detach();
                }
                $p->delete();
                $this->dispatch('toast', ['type' => 'success', 'msg' => 'Permiso eliminado.']);
            }
        }
        $this->cerrarConfirm();
    }

    protected function resetPermisoForm()
    {
        $this->permiso_id        = null;
        $this->permiso_name      = '';
        $this->permiso_nombre    = '';
        $this->permiso_guard     = 'web';
        $this->permiso_orden     = null;
        $this->permiso_type_id   = null;
        $this->permiso_grupo_id  = null;
        $this->permiso_grupo_orden = null;
    }

    /* ============ GRUPOS ============ */

    public function nuevoGrupo()
    {
        $this->resetGrupoForm();
        $this->grupo_permisos_sel   = [];
        $this->grupo_permisos_orden = [];
        $this->modalGrupo = true;
    }

    public function editarGrupo($id)
    {
        $g = GrupoOrden::with('permissions')->findOrFail($id);

        $this->grupo_id     = $g->id;
        $this->grupo_nombre = $g->nombre;
        $this->grupo_slug   = $g->slug;
        $this->grupo_orden  = $g->orden;

        $this->grupo_permisos_sel   = $g->permissions->pluck('id')->toArray();
        $this->grupo_permisos_orden = $g->permissions
            ->mapWithKeys(fn ($p) => [$p->id => (int)($p->pivot->orden ?? 0)])
            ->toArray();

        $this->modalGrupo = true;
    }

    public function guardarGrupo()
    {
        $this->validate([
            'grupo_nombre' => ['required','string','max:255'],
            'grupo_slug'   => ['required','string','max:255', Rule::unique('grupos_orden','slug')->ignore($this->grupo_id)],
            'grupo_orden'  => ['nullable','integer','min:0'],
        ]);

        $g = $this->grupo_id ? GrupoOrden::findOrFail($this->grupo_id) : new GrupoOrden();
        $g->nombre = $this->grupo_nombre;
        $g->slug   = $this->grupo_slug;
        $g->orden  = $this->grupo_orden;
        $g->save();

        $sync = [];
        foreach ($this->grupo_permisos_sel as $pid) {
            $sync[(int)$pid] = ['orden' => (int)($this->grupo_permisos_orden[$pid] ?? 0)];
        }
        $g->permissions()->sync($sync);

        $this->dispatch('toast', ['type' => 'success', 'msg' => 'Grupo guardado correctamente.']);
        $this->modalGrupo = false;
        $this->resetGrupoForm();
        $this->grupo_permisos_sel   = [];
        $this->grupo_permisos_orden = [];
    }

    public function confirmarEliminarGrupo($id)
    {
        $g = GrupoOrden::findOrFail($id);
        $this->confirmType = 'grupo';
        $this->confirmId   = $g->id;
        $this->confirmName = $g->nombre;
        $this->modalConfirm = true;
    }

    public function eliminarGrupo()
    {
        if ($this->confirmType === 'grupo' && $this->confirmId) {
            $g = GrupoOrden::find($this->confirmId);
            if ($g) {
                $g->permissions()->detach();
                $g->delete();
                $this->dispatch('toast', ['type' => 'success', 'msg' => 'Grupo eliminado.']);
            }
        }
        $this->cerrarConfirm();
    }

    protected function resetGrupoForm()
    {
        $this->grupo_id     = null;
        $this->grupo_nombre = '';
        $this->grupo_slug   = '';
        $this->grupo_orden  = null;
        $this->grupo_permisos_sel   = [];
        $this->grupo_permisos_orden = [];
    }

    /* ============ ASIGNACIÓN RÁPIDA: ROL <-> PERMISO (columna grupos) ============ */

    public function togglePermiso(int $role_id, int $permiso_id, bool $checked): void
    {
        $role    = Role::find($role_id);
        $permiso = Permission::find($permiso_id);

        if (!$role || !$permiso) return;

        if ($checked) {
            if (!$role->hasPermissionTo($permiso->name)) {
                $role->givePermissionTo($permiso->name);
            }
        } else {
            if ($role->hasPermissionTo($permiso->name)) {
                $role->revokePermissionTo($permiso->name);
            }
        }

        $this->dispatch('toast', ['type' => 'success', 'msg' => 'Permiso actualizado.']);
    }

    public function syncGrupoConRol(int $roleId, int $grupoId, bool $assign = true): void
    {
        $role  = Role::findOrFail($roleId);
        $grupo = GrupoOrden::with(['permissions:id,name'])->findOrFail($grupoId);

        $permNames = $grupo->permissions->pluck('name')->all();

        if ($assign) {
            $role->givePermissionTo($permNames);
            $this->dispatch('toast', ['type' => 'success', 'msg' => 'Permisos del grupo asignados al rol.']);
        } else {
            $role->revokePermissionTo($permNames);
            $this->dispatch('toast', ['type' => 'success', 'msg' => 'Permisos del grupo quitados del rol.']);
        }
    }

    public function editarPermisoDeGrupo(int $grupoId, int $permisoId): void
    {
        $g = GrupoOrden::findOrFail($grupoId);
        $p = Permission::findOrFail($permisoId);

        $this->permiso_id     = $p->id;
        $this->permiso_name   = $p->name;
        $this->permiso_nombre = $p->nombre ?? '';
        $this->permiso_guard  = $p->guard_name ?? 'web';
        $this->permiso_orden  = $p->orden;
        $this->permiso_type_id = $p->permission_type_id;

        $pivot = $g->permissions()->where('permission_id', $p->id)->first()?->pivot;
        $this->permiso_grupo_id    = $g->id;
        $this->permiso_grupo_orden = $pivot?->orden ?? 0;

        $this->modalPermiso = true;
    }

    public function quitarPermisoDeGrupo(int $grupoId, int $permisoId): void
    {
        $g = GrupoOrden::findOrFail($grupoId);
        $g->permissions()->detach($permisoId);

        if ((int) $this->permiso_id === (int) $permisoId && (int) $this->permiso_grupo_id === (int) $grupoId) {
            $this->permiso_grupo_id    = null;
            $this->permiso_grupo_orden = null;
        }

        $this->dispatch('toast', ['type' => 'success', 'msg' => 'Permiso quitado del grupo.']);
        $this->resetPage();
    }

    /* ============ CONFIRM ============ */

    public function cerrarConfirm()
    {
        $this->modalConfirm = false;
        $this->confirmType  = null;
        $this->confirmId    = null;
        $this->confirmName  = null;
    }
}

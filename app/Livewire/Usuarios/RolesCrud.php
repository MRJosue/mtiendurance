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
    public $permisosSeleccionados = [];

    // ====== Form Permiso ======
    public $permiso_id = null;
    public $permiso_name = '';    // machine
    public $permiso_nombre = '';  // visible
    public $permiso_guard = 'web';
    public $permiso_orden = null;
    public $permiso_type_id = null;
    public $permiso_grupo_id = null; // opcional: asignarlo a un grupo con orden
    public $permiso_grupo_orden = null;

    // ====== Form Grupo ======
    public $grupo_id = null;
    public $grupo_nombre = '';
    public $grupo_slug = '';
    public $grupo_orden = null;

    // ====== Listeners (v3 usa ->dispatch desde Blade/JS) ======
    protected $listeners = ['togglePermiso' => 'togglePermiso'];

    public function render()
    {
        $roles = Role::query();
        if ($this->search) {
            $roles->where('name', 'like', '%' . $this->search . '%');
        }

        $grupos = GrupoOrden::with(['permissions' => function ($q) {
            $q->orderBy('grupo_orden_permission.orden');
        }])->orderBy('nombre')->get();

        return view('livewire.usuarios.roles-crud', [
            'rolesList' => $roles->orderBy('name')->paginate(10),
            'grupos'    => $grupos,
            'permisos'  => Permission::with('type')->orderBy('orden')->orderBy('name')->get(),
            'types'     => \DB::table('permission_types')->orderBy('orden')->get(),
        ]);
    }

    // ====== Búsqueda ======
    public function buscar()
    {
        $this->search = $this->query;
        $this->resetPage();
    }

    // ====== Rol: Crear / Editar / Guardar ======
    public function nuevoRol()
    {
        $this->resetRolForm();
        $this->modalRol = true;
    }

    public function editarRol($id)
    {
        $rol = Role::findOrFail($id);
        $this->role_id = $rol->id;
        $this->nombreRol = $rol->name;
        $this->permisosSeleccionados = $rol->permissions()->pluck('id')->toArray();
        $this->modalRol = true;
    }

    public function guardarRol()
    {
        $this->validate([
            'nombreRol' => ['required','string','max:255'],
        ]);

        $rol = $this->role_id ? Role::findOrFail($this->role_id) : new Role();
        $rol->name = $this->nombreRol;
        $rol->guard_name = $rol->guard_name ?? 'web';
        $rol->save();

        $ids = Permission::whereIn('id', $this->permisosSeleccionados)->pluck('name')->toArray();
        $rol->syncPermissions($ids);

        $this->dispatch('toast', ['type' => 'success', 'msg' => 'Rol guardado correctamente.']);
        $this->modalRol = false;
        $this->resetRolForm();
    }

    public function confirmarEliminarRol($id)
    {
        $rol = Role::findOrFail($id);
        $this->confirmType = 'rol';
        $this->confirmId = $rol->id;
        $this->confirmName = $rol->name;
        $this->modalConfirm = true;
    }

    public function eliminarRol()
    {
        if ($this->confirmType === 'rol' && $this->confirmId) {
            $rol = Role::find($this->confirmId);
            if ($rol) {
                $rol->syncPermissions([]);
                $rol->delete();
                $this->dispatch('toast', ['type' => 'success', 'msg' => 'Rol eliminado.']);
            }
        }
        $this->cerrarConfirm();
    }

    protected function resetRolForm()
    {
        $this->role_id = null;
        $this->nombreRol = '';
        $this->permisosSeleccionados = [];
    }

    // ====== Permiso: Crear / Editar / Guardar / Eliminar ======
    public function nuevoPermiso()
    {
        $this->resetPermisoForm();
        $this->modalPermiso = true;
    }

    public function editarPermiso($id)
    {
        $p = Permission::findOrFail($id);
        $this->permiso_id = $p->id;
        $this->permiso_name = $p->name;
        $this->permiso_nombre = $p->nombre ?? '';
        $this->permiso_guard = $p->guard_name ?? 'web';
        $this->permiso_orden = $p->orden;
        $this->permiso_type_id = $p->permission_type_id;
        // si el permiso pertenece a algún grupo, obtén el primero
        $grupo = $p->groups()->first(); // requiere relación en modelo GrupoOrden (hasManyThrough o belongsToMany)
        $this->permiso_grupo_id = $grupo?->id;
        $this->permiso_grupo_orden = $grupo?->pivot?->orden;
        $this->modalPermiso = true;
    }

    public function guardarPermiso()
    {
        $this->validate([
            'permiso_name'  => ['required','string','max:255', Rule::unique('permissions','name')->ignore($this->permiso_id)],
            'permiso_guard' => ['required','string','max:50'],
            'permiso_nombre'=> ['nullable','string','max:255'],
            'permiso_orden' => ['nullable','integer','min:0'],
            'permiso_type_id' => ['nullable','integer','exists:permission_types,id'],
            'permiso_grupo_id' => ['nullable','integer'],
            'permiso_grupo_orden' => ['nullable','integer','min:0'],
        ]);

        $p = $this->permiso_id ? Permission::findOrFail($this->permiso_id) : new Permission();
        $p->name = $this->permiso_name;
        $p->guard_name = $this->permiso_guard;
        $p->nombre = $this->permiso_nombre ?: $this->permiso_name;
        $p->orden = $this->permiso_orden;
        $p->permission_type_id = $this->permiso_type_id;
        $p->save();

        // opcional: asignarlo a un grupo con orden en el pivot
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
        $this->confirmId = $p->id;
        $this->confirmName = $p->nombre ?? $p->name;
        $this->modalConfirm = true;
    }

    public function eliminarPermiso()
    {
        if ($this->confirmType === 'permiso' && $this->confirmId) {
            $p = Permission::find($this->confirmId);
            if ($p) {
                // romper relaciones con grupos y roles
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
        $this->permiso_id = null;
        $this->permiso_name = '';
        $this->permiso_nombre = '';
        $this->permiso_guard = 'web';
        $this->permiso_orden = null;
        $this->permiso_type_id = null;
        $this->permiso_grupo_id = null;
        $this->permiso_grupo_orden = null;
    }

    // ====== Grupo: Crear / Editar / Guardar / Eliminar ======
    public function nuevoGrupo()
    {
        $this->resetGrupoForm();
        $this->modalGrupo = true;
    }

    public function editarGrupo($id)
    {
        $g = GrupoOrden::findOrFail($id);
        $this->grupo_id = $g->id;
        $this->grupo_nombre = $g->nombre;
        $this->grupo_slug = $g->slug;
        $this->grupo_orden = $g->orden;
        $this->modalGrupo = true;
    }

    public function guardarGrupo()
    {
        $this->validate([
            'grupo_nombre' => ['required','string','max:255'],
            'grupo_slug'   => ['required','string','max:255', Rule::unique('grupo_orden','slug')->ignore($this->grupo_id)],
            'grupo_orden'  => ['nullable','integer','min:0'],
        ]);

        $g = $this->grupo_id ? GrupoOrden::findOrFail($this->grupo_id) : new GrupoOrden();
        $g->nombre = $this->grupo_nombre;
        $g->slug = $this->grupo_slug;
        $g->orden = $this->grupo_orden;
        $g->save();

        $this->dispatch('toast', ['type' => 'success', 'msg' => 'Grupo guardado correctamente.']);
        $this->modalGrupo = false;
        $this->resetGrupoForm();
    }

    public function confirmarEliminarGrupo($id)
    {
        $g = GrupoOrden::findOrFail($id);
        $this->confirmType = 'grupo';
        $this->confirmId = $g->id;
        $this->confirmName = $g->nombre;
        $this->modalConfirm = true;
    }

    public function eliminarGrupo()
    {
        if ($this->confirmType === 'grupo' && $this->confirmId) {
            $g = GrupoOrden::find($this->confirmId);
            if ($g) {
                // Romper relación con permisos para mantener integridad
                $g->permissions()->detach();

                $g->delete();
                $this->dispatch('toast', ['type' => 'success', 'msg' => 'Grupo eliminado.']);
            }
        }
        $this->cerrarConfirm();
    }


    protected function resetGrupoForm()
    {
        $this->grupo_id = null;
        $this->grupo_nombre = '';
        $this->grupo_slug = '';
        $this->grupo_orden = null;
    }

    // ====== Asignación rápida de permisos rol<->permiso (checkbox) ======
    public function togglePermiso($role_id, $permiso_id, $checked)
    {
        $role = Role::find($role_id);
        $permiso = Permission::find($permiso_id);
        if ($role && $permiso) {
            if ($checked) {
                if (!$role->hasPermissionTo($permiso->name)) {
                    $role->givePermissionTo($permiso->name);
                }
            } else {
                if ($role->hasPermissionTo($permiso->name)) {
                    $role->revokePermissionTo($permiso->name);
                }
            }
        }
        $this->resetPage();
    }


        public function editarPermisoDeGrupo(int $grupoId, int $permisoId): void
    {
        $g = GrupoOrden::findOrFail($grupoId);
        $p = Permission::findOrFail($permisoId);

        $this->permiso_id = $p->id;
        $this->permiso_name = $p->name;
        $this->permiso_nombre = $p->nombre ?? '';
        $this->permiso_guard = $p->guard_name ?? 'web';
        $this->permiso_orden = $p->orden;
        $this->permiso_type_id = $p->permission_type_id;

        // leer orden del pivot en este grupo
        $pivot = $g->permissions()->where('permission_id', $p->id)->first()?->pivot;
        $this->permiso_grupo_id = $g->id;
        $this->permiso_grupo_orden = $pivot?->orden ?? 0;

        $this->modalPermiso = true;
    }

    // Quitar (detach) un permiso del grupo
    public function quitarPermisoDeGrupo(int $grupoId, int $permisoId): void
    {
        $g = GrupoOrden::findOrFail($grupoId);
        $g->permissions()->detach($permisoId);

        // Si justo estás editando ese permiso en el modal, limpia el grupo seleccionado
        if ((int) $this->permiso_id === (int) $permisoId && (int) $this->permiso_grupo_id === (int) $grupoId) {
            $this->permiso_grupo_id = null;
            $this->permiso_grupo_orden = null;
        }

        $this->dispatch('toast', ['type' => 'success', 'msg' => 'Permiso quitado del grupo.']);
        // refresca paginación / lista
        $this->resetPage();
    }
    

    // ====== Confirm general ======
    public function cerrarConfirm()
    {
        $this->modalConfirm = false;
        $this->confirmType = null;
        $this->confirmId = null;
        $this->confirmName = null;
    }
}

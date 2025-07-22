<?php

namespace App\Livewire\Usuarios;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use App\Models\GrupoOrden; // Importa tu modelo

use Spatie\Permission\Models\Permission;
use Livewire\WithPagination;

class RolesCrud extends Component
{
    use WithPagination;

    public $modal = false;
    public $role_id;
    public $nombre;
    public $permisosSeleccionados = [];

    public $query = '';
    public $search = '';

    protected $paginationTheme = 'tailwind';

    protected $listeners = ['togglePermiso'];

    public function render()
    {
        $roles = Role::query();

        if ($this->search) {
            $roles->where('name', 'like', '%' . $this->search . '%');
        }

        // Aquí agrupas permisos por grupo para el modal
        $grupos = GrupoOrden::with(['permissions' => function ($q) {
            $q->orderBy('grupo_orden_permission.orden');
        }])->orderBy('nombre')->get();

        return view('livewire.usuarios.roles-crud', [
            'rolesList' => $roles->paginate(10),
            'permisos' => Permission::orderBy('name')->get(), // Si lo sigues usando en otra parte
            'grupos' => $grupos, // Pásalo a la vista para el modal
        ]);
    }

    public function buscar()
    {
        $this->search = $this->query;
        $this->resetPage();
    }

    public function crear()
    {
        $this->limpiar();
        $this->abrirModal();
    }

    public function editar($id)
    {
        $rol = Role::findOrFail($id);
        $this->role_id = $rol->id;
        $this->nombre = $rol->name;
        $this->permisosSeleccionados = $rol->permissions->pluck('id')->toArray();
        $this->abrirModal();
    }

    public function guardar()
    {
        $this->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $rol = $this->role_id ? Role::findOrFail($this->role_id) : new Role();
        $rol->name = $this->nombre;
        $rol->save();

        $permisosNombres = \Spatie\Permission\Models\Permission::whereIn('id', $this->permisosSeleccionados)->pluck('name')->toArray();
        $rol->syncPermissions($permisosNombres);

        session()->flash('message', 'Rol guardado correctamente.');
        $this->cerrarModal();
        $this->limpiar();
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
        $this->role_id = null;
        $this->nombre = '';
        $this->permisosSeleccionados = [];
    }

public function togglePermiso($role_id, $permiso_id, $checked)
{
    $role = \Spatie\Permission\Models\Role::find($role_id);
    $permiso = \Spatie\Permission\Models\Permission::find($permiso_id);
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
}

<?php

namespace App\Livewire\Usuarios;

use Livewire\Component;
use Spatie\Permission\Models\Role;
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

    public function render()
    {
        $roles = Role::query();

        if ($this->search) {
            $roles->where('name', 'like', '%' . $this->search . '%');
        }

        return view('livewire.usuarios.roles-crud', [
            'rolesList' => $roles->paginate(10),
            'permisos' => Permission::orderBy('name')->get(),
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

        $rol->syncPermissions($this->permisosSeleccionados);

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
}

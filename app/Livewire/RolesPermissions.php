<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesPermissions extends Component
{
    public $roles = [];
    public $permissions = [];
    public $newRole = '';
    public $newPermission = '';
    public $selectedRole = null;
    public $selectedPermissions = [];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->roles = Role::pluck('name', 'id')->toArray();
        $this->permissions = Permission::pluck('name', 'id')->toArray();
    }

    public function loadPermissions()
    {
        if ($this->selectedRole) {
            $role = Role::find($this->selectedRole);
            if ($role) {
                $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
            }
        } else {
            $this->selectedPermissions = [];
        }
    }

    public function createRole()
    {
        $this->validate(['newRole' => 'required|string|unique:roles,name']);
        Role::create(['name' => $this->newRole]);
        $this->newRole = '';
        $this->loadData();
        session()->flash('message', 'Rol creado correctamente.');
    }

    public function createPermission()
    {
        $this->validate(['newPermission' => 'required|string|unique:permissions,name']);
        Permission::create(['name' => $this->newPermission]);
        $this->newPermission = '';
        $this->loadData();
        session()->flash('message', 'Permiso creado correctamente.');
    }

    public function assignPermissionsToRole()
    {
        if ($this->selectedRole) {
            $role = Role::find($this->selectedRole);
            if ($role) {
                $role->syncPermissions($this->selectedPermissions);
                session()->flash('message', 'Permisos actualizados correctamente.');
            }
        }
    }

    public function render()
    {
        return view('livewire.roles-permissions');
    }
}
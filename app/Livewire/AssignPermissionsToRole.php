<?php

namespace App\Livewire;


use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AssignPermissionsToRole extends Component
{
    public $roleId;
    public $permissions = [];
    public $selectedPermissions = [];

    public function mount($roleId)
    {
        $this->roleId = $roleId;
        $role = Role::findOrFail($roleId);

        $this->permissions = Permission::all(); // Obtiene todos los permisos disponibles
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray(); // Permisos asignados al rol
    }

    public function savePermissions()
    {
        $role = Role::findOrFail($this->roleId);

        // Obtén los permisos seleccionados y asignarlos al rol
        $permissions = Permission::whereIn('id', $this->selectedPermissions)->pluck('name')->toArray();
        $role->syncPermissions($permissions);

        session()->flash('message', 'Permisos actualizados correctamente.');
    }

    public function render()
    {
        return view('livewire.assign-permissions-to-role', [
            'permissions' => $this->permissions,
        ]);
    }
}

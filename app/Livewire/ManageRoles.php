<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ManageRoles extends Component
{
    public $roles, $permissions, $roleId, $name, $guard_name = 'web', $selectedPermissions = [];

    public $isEditing = false;
    public $isModalOpen = false;

    protected $rules = [
        'name' => 'required|string|max:255|unique:roles,name',
        'guard_name' => 'required|string|max:255',
    ];

    public function mount()
    {
        $this->roles = Role::all();
        $this->permissions = Permission::all();
    }

    public function openModal()
    {
        $this->resetFields();
        $this->isModalOpen = true;
    }

    public function resetFields()
    {
        $this->name = '';
        $this->guard_name = 'web';
        $this->selectedPermissions = [];
        $this->isEditing = false;
        $this->roleId = null;
    }

    public function createRole()
    {
        $this->validate();

        $role = Role::create([
            'name' => $this->name,
            'guard_name' => $this->guard_name,
        ]);

        $role->syncPermissions($this->selectedPermissions);

        session()->flash('message', 'Role created successfully.');
        $this->closeModal();
        $this->refreshRoles();
    }

    public function editRole($id)
    {
        $role = Role::findOrFail($id);
        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->guard_name = $role->guard_name;
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();

        $this->isEditing = true;
        $this->isModalOpen = true;
    }

    public function updateRole()
    {
        $role = Role::findOrFail($this->roleId);
        $this->validate([
            'name' => "required|string|max:255|unique:roles,name,{$role->id}",
            'guard_name' => 'required|string|max:255',
        ]);

        $role->update([
            'name' => $this->name,
            'guard_name' => $this->guard_name,
        ]);

        $role->syncPermissions($this->selectedPermissions);

        session()->flash('message', 'Role updated successfully.');
        $this->closeModal();
        $this->refreshRoles();
    }

    public function deleteRole($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        session()->flash('message', 'Role deleted successfully.');
        $this->refreshRoles();
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetFields();
    }

    public function refreshRoles()
    {
        $this->roles = Role::all();
    }

    public function render()
    {
        return view('livewire.manage-roles');
    }
}

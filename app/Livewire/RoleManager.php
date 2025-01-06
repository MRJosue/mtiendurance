<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Role;

class RoleManager extends Component
{
    public $roleId;
    public $name;
    public $guard_name = 'web';

    public function mount($roleId = null)
    {
        if ($roleId) {
            $role = Role::findOrFail($roleId);
            $this->roleId = $role->id;
            $this->name = $role->name;
            $this->guard_name = $role->guard_name;
        }
    }

    protected $rules = [
        'name' => 'required|string|max:255|unique:roles,name',
        'guard_name' => 'required|string|max:255',
    ];

    public function save()
    {
        $this->validate();

        if ($this->roleId) {
            $role = Role::findOrFail($this->roleId);
            $role->update([
                'name' => $this->name,
                'guard_name' => $this->guard_name,
            ]);
            session()->flash('message', 'Role updated successfully.');
        } else {
            Role::create([
                'name' => $this->name,
                'guard_name' => $this->guard_name,
            ]);
            session()->flash('message', 'Role created successfully.');
        }

        $this->reset(['name', 'guard_name', 'roleId']);
    }

    public function render()
    {
        return view('livewire.role-manager');
    }
}

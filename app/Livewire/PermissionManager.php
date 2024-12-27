<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Permission;

class PermissionManager extends Component
{
    public $permissionId;
    public $name;
    public $guard_name = 'web';

    public function mount($permissionId = null)
    {
        if ($permissionId) {
            $permission = Permission::findOrFail($permissionId);
            $this->permissionId = $permission->id;
            $this->name = $permission->name;
            $this->guard_name = $permission->guard_name;
        }
    }

    protected $rules = [
        'name' => 'required|string|max:255|unique:permissions,name',
        'guard_name' => 'required|string|max:255',
    ];

    public function save()
    {
        $this->validate();

        if ($this->permissionId) {
            $permission = Permission::findOrFail($this->permissionId);
            $permission->update([
                'name' => $this->name,
                'guard_name' => $this->guard_name,
            ]);
            session()->flash('message', 'Permission updated successfully.');
        } else {
            Permission::create([
                'name' => $this->name,
                'guard_name' => $this->guard_name,
            ]);
            session()->flash('message', 'Permission created successfully.');
        }

        // Resetear el formulario
        $this->reset(['name', 'guard_name', 'permissionId']);
    }

    public function render()
    {
        return view('livewire.permission-manager');
    }
}

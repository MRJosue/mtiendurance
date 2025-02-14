<?php

namespace App\Livewire;
use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignUserRole extends Component
{
    public $users = [];
    public $roles = [];
    public $selectedUser = null;
    public $selectedRole = null;

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->users = User::all();
        $this->roles = Role::pluck('name', 'id')->toArray();
    }

    public function assignRole()
    {
        $this->validate([
            'selectedUser' => 'required|exists:users,id',
            'selectedRole' => 'required|exists:roles,id',
        ]);

        $user = User::find($this->selectedUser);
        $role = Role::find($this->selectedRole);

        if ($user && $role) {
            $user->syncRoles([$role->name]);
            session()->flash('message', "Rol '{$role->name}' asignado a '{$user->name}' correctamente.");
        } else {
            session()->flash('error', 'Error al asignar el rol.');
        }
    }

    public function render()
    {
        return view('livewire.assign-user-role');
    }
}
<?php

namespace App\Livewire;
use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class AssignUserRole extends Component
{
    public $users = [];
    public $roles = [];
    public $selectedUser = null;
    public $selectedRole = null;

    public function mount()
    {
        Log::info('Mounting AssignUserRole component');
        $this->loadData();
    }

    public function loadData()
    {
        Log::info('Loading users and roles data');
        $this->users = User::all();
        $this->roles = Role::pluck('name', 'id')->toArray();
        Log::info('Users and roles loaded successfully', ['users_count' => count($this->users), 'roles_count' => count($this->roles)]);
    }

    public function assignRole()
    {
        Log::info('Attempting to assign role', ['selectedUser' => $this->selectedUser, 'selectedRole' => $this->selectedRole]);
        
        $this->validate([
            'selectedUser' => 'required|exists:users,id',
            'selectedRole' => 'required|exists:roles,id',
        ]);

        $user = User::find($this->selectedUser);
        $role = Role::find($this->selectedRole);

        if ($user && $role) {
            $user->syncRoles([$role->name]);
            Log::info('Role assigned successfully', ['user' => $user->name, 'role' => $role->name]);
            session()->flash('message', "Rol '{$role->name}' asignado a '{$user->name}' correctamente.");
        } else {
            Log::error('Error assigning role', ['user' => $this->selectedUser, 'role' => $this->selectedRole]);
            session()->flash('error', 'Error al asignar el rol.');
        }
    }

    public function render()
    {
        Log::info('Rendering AssignUserRole component');
        return view('livewire.assign-user-role');
    }
}

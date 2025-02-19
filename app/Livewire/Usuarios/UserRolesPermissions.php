<?php

namespace App\Livewire\Usuarios;

use Livewire\Component;
use App\Models\User;

class UserRolesPermissions extends Component
{
    public $userId;
    public $roles = [];
    public $permissions = [];

    public function mount($userId)
    {
        $this->userId = $userId;
        $this->loadData();
    }

    public function loadData()
    {
        $user = User::find($this->userId);

        if ($user) {
            // Obtener los roles asignados al usuario
            $this->roles = $user->getRoleNames()->toArray();

            // Obtener los permisos asignados a los roles del usuario
            $this->permissions = $user->roles->flatMap->permissions->pluck('name')->unique()->toArray();
        }
    }

    public function render()
    {
        return view('livewire.user-roles-permissions');
    }
}

///        return view('livewire.usuarios.user-roles-permissions');
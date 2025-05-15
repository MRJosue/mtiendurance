<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class UserRolesPermissions extends Component
{
    public $roles = [];
    public $permissions = [];

    

    public function mount()
    {
        $user = Auth::user();

        if ($user) {
            $this->roles = $user->roles->pluck('name'); // Obtener roles asignados al usuario
            $this->permissions = $user->roles->flatMap->permissions->pluck('name'); // Obtener permisos desde los roles
        }
    }

    public function render()
    {
        return view('livewire.user-roles-permissions');
    }
}
<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;



class ClientMessage extends Component
{
    public $showMessage = false;
    public $roles = [];
    public $permissions = [];

    public function mount()
    {
        $user = Auth::user();

        if ($user) {
            // Obtener los nombres de los roles y permisos del usuario
            $this->roles = $user->getRoleNames()->toArray();
            $this->permissions = $user->getAllPermissions()->pluck('name')->toArray();

            // Verificar si el usuario tiene el rol "Cliente" y el permiso "Acceso Especial"
            $this->showMessage = in_array('Cliente', $this->roles) && in_array('Pre Proyectos', $this->permissions);
        }
    }

    public function render()
    {
        return view('livewire.client-message');
    }
}

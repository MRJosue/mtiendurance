<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use App\Models\User;

class AssignRoles extends Component
{
    public $userId;
    public $roles = [];
    public $selectedRoles = [];

    public function mount($userId)
    {
        $this->userId = $userId;
        $this->roles = Role::all(); // Obtiene todos los roles disponibles
        $this->selectedRoles = User::findOrFail($userId)->roles->pluck('id')->toArray(); // Obtiene los roles asignados al usuario
    }

    public function saveRoles()
    {

        $user = User::findOrFail($this->userId);

        // Obtén los nombres de los roles basados en los IDs seleccionados
        $roleNames = Role::whereIn('id', $this->selectedRoles)->pluck('name')->toArray();

        // Sincroniza los roles seleccionados con el usuario
        $user->syncRoles($roleNames);

        session()->flash('message', 'Roles actualizados correctamente.');

        // $user = User::findOrFail($this->userId);

        // // Sincroniza los roles seleccionados con el usuario
        // $user->syncRoles($this->selectedRoles);

        // session()->flash('message', 'Roles actualizados correctamente.');
    }

    public function render()
    {
        return view('livewire.assign-roles', [
            'roles' => $this->roles
        ]);
    }
}

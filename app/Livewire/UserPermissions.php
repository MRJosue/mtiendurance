<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Spatie\Permission\Models\Role;




class userpermissions extends Component
{
    public $users;

    public function mount()
    {
        // Fetch all users with their roles
        $this->users = User::with('roles')->get();
    }

    public function render()
    {
        return view('livewire.user-permissions', [
            'users' => $this->users,
        ]);
    }
}



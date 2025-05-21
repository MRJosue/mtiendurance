<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class SwitchUser extends Component
{
    public $userId;

    public function mount()
    {
        $this->userId = Auth::id();
    }

    public function switchUser()
    {
 
            Auth::logout(); // cerrar sesión actual
            Auth::loginUsingId($this->userId); // iniciar sesión como otro
            return redirect()->route('dashboard');

    }

    public function render()
    {
        return view('livewire.switch-user', [
            'users' => User::all(),
        ]);
    }
}
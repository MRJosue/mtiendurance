<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class SwitchUser extends Component
{
    public $userId;
    public $search = ''; // <-- Nuevo: campo para búsqueda

    public function mount()
    {
        $this->userId = Auth::id();
    }

    public function switchUser()
    {
        Auth::logout();
        Auth::loginUsingId($this->userId);
        return redirect()->route('dashboard');
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->limit(50)
            ->get();

        return view('livewire.switch-user', [
        'users' => User::select('id', 'name', 'email')->orderBy('name')->get(),
        ]);
    }
}

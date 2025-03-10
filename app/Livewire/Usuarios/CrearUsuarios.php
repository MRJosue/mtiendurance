<?php

namespace App\Livewire\Usuarios;


use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class CrearUsuarios extends Component
{
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $role;
    public $rolesDisponibles = [];

    public function mount()
    {
        $this->rolesDisponibles = Role::pluck('name')->toArray();
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role' => ['required', Rule::in($this->rolesDisponibles)],
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function createUser()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        Log::debug('Role a asign', ['data' => $this->role]);

        $user->assignRole($this->role);

        // Resetear los campos
        $this->reset(['name', 'email', 'password', 'password_confirmation', 'role']);

        session()->flash('message', 'Usuario creado exitosamente.');
    }

    public function render()
    {
        return view('livewire.usuarios.crear-usuarios');
    }
}


//  return view('livewire.usuarios.crear-usuarios');
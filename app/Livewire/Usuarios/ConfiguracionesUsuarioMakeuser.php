<?php

namespace App\Livewire\Usuarios;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ConfiguracionesUsuarioMakeuser extends Component
{
    public $userId;
    public $jefe;
    public $subordinados = [];
    public $showForm = false;
    public $editingId = null;

    // Campos del formulario
    public $name, $email, $password;

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->editingId)
            ],
        ];
        if(!$this->editingId) {
            $rules['password'] = 'required|string|min:6';
        }
        return $rules;
    }

    public function mount($userId)
    {
        $this->userId = $userId;
        $this->loadJefe();
        $this->loadSubordinados();
    }

    public function loadJefe()
    {
        $this->jefe = User::findOrFail($this->userId);
    }

    public function loadSubordinados()
    {
        $ids = $this->jefe->subordinados ?? [];
        $this->subordinados = User::whereIn('id', $ids)->get();
    }

    public function showCreateForm()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function showEditForm($id)
    {
        $user = User::findOrFail($id);
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->showForm = true;
    }

    public function saveUser()
    {
        $data = $this->validate();

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $user->name = $this->name;
            $user->email = $this->email;
            if($this->password) $user->password = Hash::make($this->password);
            $user->save();
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);
            // Añadir el nuevo id al campo json de subordinados
            $subordinados = $this->jefe->subordinados ?? [];
            $subordinados[] = $user->id;
            $this->jefe->subordinados = $subordinados;
            $this->jefe->save();
        }

        $this->loadJefe();
        $this->loadSubordinados();
        $this->showForm = false;
        $this->resetForm();
        $this->dispatch('notify', type: 'success', message: 'Usuario guardado correctamente');
    }

    public function deleteUser($id)
    {
        // Elimina el usuario y quita su id del array subordinados
        $user = User::findOrFail($id);
        $user->delete();

        $subordinados = $this->jefe->subordinados ?? [];
        $subordinados = array_values(array_diff($subordinados, [$id]));
        $this->jefe->subordinados = $subordinados;
        $this->jefe->save();

        $this->loadJefe();
        $this->loadSubordinados();
        $this->dispatch('notify', type: 'success', message: 'Usuario eliminado correctamente');
    }

    public function resetForm()
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
    }

    public function render()
    {
        return view('livewire.usuarios.configuraciones-usuario-makeuser');
    }
}



// namespace App\Livewire\Usuarios;

// use Livewire\Component;

// class ConfiguracionesUsuarioMakeuser extends Component
// {
//     public function render()
//     {
//         return view('livewire.usuarios.configuraciones-usuario-makeuser');
//     }
// }

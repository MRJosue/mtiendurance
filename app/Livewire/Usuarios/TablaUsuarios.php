<?php

namespace App\Livewire\Usuarios;

use Livewire\Component;


use Livewire\WithPagination;
use App\Models\User;
class TablaUsuarios extends Component
{
    use WithPagination;

    public $search = [
        'id' => '',
        'name' => '',
        'email' => '',

    ];

    public function searchUsers()
    {
        $this->resetPage(); // Reinicia la paginación al buscar
    }

    public function updatingPage()
    {
        $this->dispatch('pageUpdated'); // Emite un evento cuando cambia de página
    }
    public function render()
    {
        $users = User::query()
            ->when($this->search['id'], fn($query, $id) => $query->where('id', $id))
            ->when($this->search['name'], fn($query, $name) => $query->where('name', 'like', "%{$name}%"))
            ->when($this->search['email'], fn($query, $email) => $query->where('email', 'like', "%{$email}%"))
            ->paginate(10);

        return view('livewire.usuarios.tabla-usuarios', ['users' => $users]);
    }
}

//livewire.usuarios.tabla-usuarios

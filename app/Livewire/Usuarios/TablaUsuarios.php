<?php

namespace App\Livewire\Usuarios;


use Livewire\Component;
use App\Models\User;
use Livewire\WithPagination;
 
class TablaUsuarios extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public function render()
    {
        return view('livewire.usuarios.tabla-usuarios', [
            'usuarios' => User::orderBy('id')->paginate(10),
        ]);
    }
}

//livewire.usuarios.tabla-usuarios

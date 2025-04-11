<?php

namespace App\Livewire\Usuarios;


use Livewire\Component;
use App\Models\User;
use Livewire\WithPagination;

use Spatie\Permission\Models\Role;

class TablaUsuarios extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $modal = false;
    public $usuario_id;
    public $rolesSeleccionados = [];
    public $search = '';

    public function render()
    {
        $query = User::with('roles');
    
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }
    
        return view('livewire.usuarios.tabla-usuarios', [
            'usuarios' => $query->orderBy('id')->paginate(10),
            'roles' => Role::orderBy('name')->get(),
        ]);
    }
    public function crear()
    {
        return redirect()->route('usuarios.create');
    }

    public function editarRoles($id)
    {
        $usuario = User::findOrFail($id);
        $this->usuario_id = $usuario->id;
        $this->rolesSeleccionados = $usuario->roles->pluck('id')->toArray();
        $this->modal = true;
    }

    public function guardarRoles()
    {
        $usuario = User::findOrFail($this->usuario_id);
        $nombresRoles = Role::whereIn('id', $this->rolesSeleccionados)->pluck('name')->toArray();
        $usuario->syncRoles($nombresRoles);

        session()->flash('message', 'Roles actualizados correctamente.');

        $this->cerrarModal();
    }

    public function cerrarModal()
    {
        $this->modal = false;
        $this->usuario_id = null;
        $this->rolesSeleccionados = [];
    }
}
//livewire.usuarios.tabla-usuarios

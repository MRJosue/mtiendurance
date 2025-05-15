<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;


class CambiarRolActual extends Component
{
    public $rolesDisponibles = [];
    public $rolActual;

    public $modalAsignarPermisos = false;
    public $modalCrearPermiso = false;
    public $modalCrearGrupoOrden = false;


 protected $listeners = ['permisosActualizados' => '$refresh'];
    public function mount()
    {
        $user = Auth::user();
        $this->rolActual = $user->roles->pluck('name')->first(); // Solo uno
        $this->rolesDisponibles = Role::pluck('name')->toArray();
    }

    public function actualizarRol()
    {
        $this->validate([
            'rolActual' => 'required|in:' . implode(',', $this->rolesDisponibles),
        ]);

        $user = Auth::user();
        $user->syncRoles([$this->rolActual]);

        session()->flash('message', 'Rol actualizado correctamente a: ' . $this->rolActual);
    }

    public function render()
    {
        return view('livewire.cambiar-rol-actual');
    }
}

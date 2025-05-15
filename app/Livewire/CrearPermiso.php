<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CrearPermiso extends Component
{
    public $nombrePermiso = '';
    public $rol;

    protected $rules = [
        'nombrePermiso' => 'required|string|min:3|unique:permissions,name',
    ];

    public function crear()
    {
        $this->validate();

        $permiso = Permission::create(['name' => $this->nombrePermiso]);

        if ($this->rol) {
            $rol = Role::where('name', $this->rol)->first();
            if ($rol) {
                $rol->givePermissionTo($permiso);
            }
        }

        session()->flash('message', 'Permiso creado y asignado correctamente.');
        $this->reset('nombrePermiso');
        $this->dispatch('permisoCreado');
    }

    public function render()
    {
        return view('livewire.crear-permiso');
    }
}
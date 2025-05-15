<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use App\Models\GrupoOrden;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\log;


class PermisosPorRol extends Component
{
    public $grupos; // grupos con permisos
    public $permisosTodos = []; // lista completa de permisos

    public $grupoNombre = '';
    public $grupoEditarId = null;
    public $permisosSeleccionados = [];

    public $modalCrearGrupo = false;
    public $modalCrearPermiso = false;

    public $nuevoPermiso = '';

    
    public $rol;


    public function mount()
    {
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $this->grupos = GrupoOrden::with(['permissions' => function ($q) {
            $q->orderBy('grupo_orden_permission.orden');
        }])->orderBy('nombre')->get();

        $this->permisosTodos = Permission::orderBy('name')->get();
    }

    public function abrirCrearGrupo()
    {
        $this->reset(['grupoNombre', 'permisosSeleccionados', 'grupoEditarId']);
        $this->modalCrearGrupo = true;
    }

    public function guardarGrupo()
    {
        $this->validate([
            'grupoNombre' => 'required|string|min:2',
        ]);

        $grupo = $this->grupoEditarId
            ? GrupoOrden::find($this->grupoEditarId)
            : GrupoOrden::create(['nombre' => $this->grupoNombre]);

        // Orden inicial por índice (puedes mejorar con UI más adelante)
        $syncData = collect($this->permisosSeleccionados)->mapWithKeys(function ($permisoId, $index) {
            return [$permisoId => ['orden' => $index + 1]];
        })->toArray();

        $grupo->permissions()->sync($syncData);

        $this->modalCrearGrupo = false;
        $this->cargarDatos();
    }

    public function editarGrupo($id)
    {
        $grupo = GrupoOrden::with('permissions')->findOrFail($id);
        $this->grupoNombre = $grupo->nombre;
        $this->grupoEditarId = $grupo->id;
        $this->permisosSeleccionados = $grupo->permissions->pluck('id')->toArray();
        $this->modalCrearGrupo = true;
    }

    public function quitarPermiso($grupoId, $permisoId)
    {
        $grupo = GrupoOrden::findOrFail($grupoId);
        $grupo->permissions()->detach($permisoId);
        $this->cargarDatos();
    }

    public function crearPermiso()
    {
        $this->validate([
            'nuevoPermiso' => 'required|string|min:2|unique:permissions,name',
        ]);

        Permission::create([
            'name' => $this->nuevoPermiso,
            'guard_name' => 'web'
        ]);

        $this->nuevoPermiso = '';
        $this->modalCrearPermiso = false;
        $this->cargarDatos();
    }

    public function render()
    {
        return view('livewire.permisos-por-rol');
    }


    public function asignarPermisosGrupo($grupoId)
    {

        Log::debug('asignarPermisosGrupo');
        $grupo = GrupoOrden::with('permissions')->findOrFail($grupoId);
        $rol = Role::where('name', $this->rol)->firstOrFail();

        foreach ($grupo->permissions as $permiso) {
            if (!$rol->hasPermissionTo($permiso->name)) {
                $rol->givePermissionTo($permiso->name);
            }
        }
        Log::debug('asignados');
        $this->dispatch('permisosActualizados');

        session()->flash('message', 'Permisos del grupo asignados al rol.');
    }

    public function revocarPermisosGrupo($grupoId)
    {
        Log::debug('revocarPermisosGrupo');
        $grupo = GrupoOrden::with('permissions')->findOrFail($grupoId);
        $rol = Role::where('name', $this->rol)->firstOrFail();

        foreach ($grupo->permissions as $permiso) {
            if ($rol->hasPermissionTo($permiso->name)) {
                $rol->revokePermissionTo($permiso->name);
            }
        }
        Log::debug('revocados');
        $this->dispatch('permisosActualizados');

        session()->flash('message', 'Permisos del grupo revocados del rol.');
    }
}

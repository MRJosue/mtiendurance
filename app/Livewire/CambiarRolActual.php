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

        // 1) Actualizar rol (solo uno)
        $user->syncRoles([$this->rolActual]);

        // 2) Mapear rol → tipo y actualizar campo `tipo`
        $nuevoTipo = $this->mapTipoPorRol($this->rolActual);

        if (!is_null($nuevoTipo)) {
            $user->tipo = $nuevoTipo;
            $user->save();
        }

        session()->flash(
            'message',
            'Rol actualizado correctamente a: ' . $this->rolActual . ' (tipo: ' . ($nuevoTipo ?? 'sin cambio') . ')'
        );
    }


        protected function mapTipoPorRol(string $rolName): ?int
    {
        $map = [
            'cliente'   => 1,
            'proveedor' => 2,
            'staff'     => 3,
            'admin'     => 4,
        ];

        return $map[$rolName] ?? null;
    }



    public function render()
    {
        return view('livewire.cambiar-rol-actual');
    }
}

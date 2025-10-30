<?php

namespace App\Livewire\Usuarios;


use Livewire\Component;
use App\Models\User;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
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
        $isPrivileged = $this->isPrivileged();

        $query = User::with('roles');

        // Filtro por búsqueda
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        // Si NO es admin ni staff, solo su usuario
        if (!$isPrivileged) {
            $query->where('id', Auth::id());
        }

        return view('livewire.usuarios.tabla-usuarios', [
            'usuarios' => $query->orderBy('id')->paginate(10),
            // Solo cargamos roles si los puede asignar
            'roles'    => $isPrivileged ? Role::orderBy('name')->get() : collect(),
            'isPrivileged' => $isPrivileged,
        ]);
    }


        public function crear()
        {
            // Solo admin/staff pueden crear
            abort_unless($this->isPrivileged(), 403);
            return redirect()->route('usuarios.create');
        }


    public function editarRoles($id)
    {
        // Solo admin/staff pueden editar roles
        abort_unless($this->isPrivileged(), 403);

        $usuario = User::findOrFail($id);
        $this->usuario_id = $usuario->id;
        $this->rolesSeleccionados = $usuario->roles->pluck('id')->toArray();
        $this->modal = true;
    }

    public function guardarRoles()
    {
        // Solo admin/staff pueden guardar roles
        abort_unless($this->isPrivileged(), 403);

        $usuario = User::findOrFail($this->usuario_id);
        $nombresRoles = Role::whereIn('id', $this->rolesSeleccionados)->pluck('name')->toArray();
        $usuario->syncRoles($nombresRoles);

        session()->flash('message', 'Roles actualizados correctamente.');

        $this->cerrarModal();
    }

        // Reset de página al escribir en la búsqueda
    public function updatedSearch()
    {
        $this->resetPage();
    }

    protected function isPrivileged(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        // Ajusta los nombres si en tu app son distintos
        return $user->hasAnyRole(['admin', 'staff']);
    }


    public function cerrarModal()
    {
        $this->modal = false;
        $this->usuario_id = null;
        $this->rolesSeleccionados = [];
    }
}
//livewire.usuarios.tabla-usuarios

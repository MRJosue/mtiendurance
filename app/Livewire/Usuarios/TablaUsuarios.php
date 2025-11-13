<?php

namespace App\Livewire\Usuarios;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class TablaUsuarios extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $modal = false;
    public $usuario_id;
    public $rolesSeleccionados = [];

    /** búsqueda global */
    public $search = '';

    /** ordenación */
    public string $sortField = 'id';
    public string $sortDir   = 'desc';

    /** filtros por columna */
    public array $filters = [
        'id'       => '',
        'name'     => '',
        'email'    => '',
        'role'     => '',
        'empresa'  => '',   
        'sucursal' => '',   
    ];


    public function render()
    {
        $isPrivileged = $this->isPrivileged();

        $query = User::query()->with([
            'roles',
            // relaciones necesarias para columnas y tooltip
            'empresa:id,nombre',
            'sucursal:id,nombre,empresa_id',
            'sucursal.empresa:id,nombre',
        ]);

        // Filtro global (nombre o email)
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                  ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        // Filtros por columna
        // ID (soporta lista separada por coma)
        if (!empty($this->filters['id'])) {
            $ids = collect(explode(',', $this->filters['id']))
                ->map(fn($v) => (int) trim($v))
                ->filter(); // elimina vacíos y ceros
            if ($ids->count() > 1) {
                $query->whereIn('id', $ids->all());
            } elseif ($ids->count() === 1) {
                $query->where('id', $ids->first());
            }
        }

        // Nombre
        if (!empty($this->filters['name'])) {
            $query->where('name', 'like', '%' . $this->filters['name'] . '%');
        }

        // Email
        if (!empty($this->filters['email'])) {
            $query->where('email', 'like', '%' . $this->filters['email'] . '%');
        }

        // Rol (por nombre)
        if (!empty($this->filters['role'])) {
            $roleName = $this->filters['role'];
            $query->whereHas('roles', function ($q) use ($roleName) {
                $q->where('name', $roleName);
            });
        }

        if (!empty($this->filters['empresa'])) {
            $v = trim($this->filters['empresa']);
            $query->where(function($w) use ($v) {
                $w->whereHas('empresa', fn($e) => $e->where('nombre', 'like', "%{$v}%"))
                  ->orWhereHas('sucursal.empresa', fn($e) => $e->where('nombre', 'like', "%{$v}%"));
            });
        }

        if (!empty($this->filters['sucursal'])) {
            $v = trim($this->filters['sucursal']);
            $query->whereHas('sucursal', fn($s) => $s->where('nombre', 'like', "%{$v}%"));
        }


        // Visibilidad según privilegio (no admin/staff: solo su propio usuario)
        if (!$isPrivileged) {
            $query->where('id', Auth::id());
        }

        // Orden
        $query->orderBy($this->sortField, $this->sortDir);

        return view('livewire.usuarios.tabla-usuarios', [
            'usuarios'       => $query->paginate(10),
            'roles'          => $isPrivileged ? Role::orderBy('name')->get() : collect(), // para modal
            'rolesListado'   => Role::orderBy('name')->get(), // para filtro por rol
            'isPrivileged'   => $isPrivileged,
            'sortField'      => $this->sortField,
            'sortDir'        => $this->sortDir,
        ]);

        
    }

    /* ===== Acciones ===== */

    public function crear()
    {
        abort_unless($this->isPrivileged(), 403);
        return redirect()->route('usuarios.create');
    }

    public function editarRoles($id)
    {
        abort_unless($this->isPrivileged(), 403);

        $usuario = User::findOrFail($id);
        $this->usuario_id = $usuario->id;
        $this->rolesSeleccionados = $usuario->roles->pluck('id')->toArray();
        $this->modal = true;
    }

    public function guardarRoles()
    {
        abort_unless($this->isPrivileged(), 403);

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

    /* ===== Orden y filtros ===== */

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir   = 'asc';
        }
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
    }

    /* ===== Helpers ===== */

    protected function isPrivileged(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        return $user->hasAnyRole(['admin', 'staff']);
    }
}

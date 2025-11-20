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

    /** Búsqueda global */
    public $search = '';

    /** Ordenación */
    public string $sortField = 'id';
    public string $sortDir   = 'desc';

    /** Filtros por columna */
    public array $filters = [
        'id'       => '',
        'name'     => '',
        'email'    => '',
        'role'     => '',
        'empresa'  => '',
        'sucursal' => '',
    ];

    /** ⭐ Nuevo: filtro de tipo (1=CLIENTE,2=PROVEEDOR,3=STAFF,4=ADMIN) */
    public ?int $tipo = null;

    /** Livewire recibe el tipo desde la llamada en Blade */
    public function mount(?int $tipo = null): void
    {
        $this->tipo = $tipo;
    }

    public function render()
    {
        $isPrivileged = $this->isPrivileged();

        $query = User::query()
            ->with([
                'roles',
                'empresa:id,nombre',
                'sucursal:id,nombre,empresa_id',
                'sucursal.empresa:id,nombre',
            ]);

        /** ⭐ Aplicar filtro por tipo si fue enviado desde Blade */
        if (!is_null($this->tipo)) {
            $query->where('tipo', $this->tipo);
        }

        /** Filtro global (nombre o correo) */
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                  ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        /** Filtro ID (permite 1,5,8) */
        if (!empty($this->filters['id'])) {
            $ids = collect(explode(',', $this->filters['id']))
                ->map(fn($v) => (int) trim($v))
                ->filter();

            if ($ids->count() > 1) {
                $query->whereIn('id', $ids->all());
            } elseif ($ids->count() === 1) {
                $query->where('id', $ids->first());
            }
        }

        /** Filtro por nombre */
        if (!empty($this->filters['name'])) {
            $query->where('name', 'like', '%' . $this->filters['name'] . '%');
        }

        /** Filtro por email */
        if (!empty($this->filters['email'])) {
            $query->where('email', 'like', '%' . $this->filters['email'] . '%');
        }

        /** Filtro por rol */
        if (!empty($this->filters['role'])) {
            $roleName = $this->filters['role'];
            $query->whereHas('roles', function ($q) use ($roleName) {
                $q->where('name', $roleName);
            });
        }

        /** Filtro por empresa (empresa directa o empresa de la sucursal) */
        if (!empty($this->filters['empresa'])) {
            $value = trim($this->filters['empresa']);

            $query->where(function($w) use ($value) {
                $w->whereHas('empresa', fn($e) => $e->where('nombre', 'like', "%{$value}%"))
                  ->orWhereHas('sucursal.empresa', fn($e) => $e->where('nombre', 'like', "%{$value}%"));
            });
        }

        /** Filtro por sucursal */
        if (!empty($this->filters['sucursal'])) {
            $value = trim($this->filters['sucursal']);
            $query->whereHas('sucursal', fn($s) => $s->where('nombre', 'like', "%{$value}%"));
        }

        /** Visibilidad según privilegio */
        if (!$isPrivileged) {
            $query->where('id', Auth::id());
        }

        /** Ordenamiento */
        $query->orderBy($this->sortField, $this->sortDir);

        return view('livewire.usuarios.tabla-usuarios', [
            'usuarios'       => $query->paginate(10),
            'roles'          => $isPrivileged ? Role::orderBy('name')->get() : collect(),
            'rolesListado'   => Role::orderBy('name')->get(),
            'isPrivileged'   => $isPrivileged,
            'sortField'      => $this->sortField,
            'sortDir'        => $this->sortDir,
        ]);
    }


    /* ===============================
     *   ACCIONES
     * =============================== */

    public function crear()
    {
        abort_unless($this->isPrivileged(), 403);

        // Si por alguna razón no viene tipo, que por default sea CLIENTE (1)
        $tipo = $this->tipo ?? 1;

        return redirect()->route('usuarios.create', ['tipo' => $tipo]);
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
        $nombresRoles = Role::whereIn('id', $this->rolesSeleccionados)
                            ->pluck('name')
                            ->toArray();

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

    /* ===============================
     *   ORDEN & FILTROS
     * =============================== */

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

    /* ===============================
     *   HELPERS
     * =============================== */

    protected function isPrivileged(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        return $user->hasAnyRole(['admin', 'staff']);
    }
}

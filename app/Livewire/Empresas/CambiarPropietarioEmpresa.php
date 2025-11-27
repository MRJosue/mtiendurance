<?php

namespace App\Livewire\Empresas;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Empresa;
use App\Models\User;
use App\Models\Sucursal;
use Illuminate\Support\Facades\DB;

class CambiarPropietarioEmpresa extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    /** Filtros avanzados y por columna */
    public bool $mostrarFiltros = false;

    public array $filters = [
        'id'              => null,
        'nombre'          => null,
        'rfc'             => null,
        'telefono'        => null,
        'propietario'     => null,
        'sin_propietario' => false,
    ];

    /** Ordenamiento */
    public string $sortField = 'nombre';
    public string $sortDir   = 'asc';

    protected array $sortable = [
        'id',
        'nombre',
        'rfc',
        'telefono',
        'propietario_nombre',
    ];

    /** Modal y selección */
    public bool $showModal = false;
    public ?int $empresaIdSeleccionada = null;
    public ?int $propietarioActualId   = null;
    public ?int $nuevoPropietarioId    = null;
    public string $searchUsuario       = '';

    public function mount(): void
    {
        // Admin global, sin parámetros
    }

    /* ===================== QUERIES / PROPERTIES ===================== */

    public function getEmpresasProperty()
    {
        $query = Empresa::query()
            ->with([
                'propietario' => function ($q) {
                    $q->where('es_propietario', true);
                },
            ]);

        // FILTROS (panel y columnas usan el mismo array)
        $query
            ->when($this->filters['id'], function ($q, $v) {
                $ids = collect(preg_split('/[,;\s]+/', (string) $v, -1, PREG_SPLIT_NO_EMPTY))
                    ->map(fn ($i) => (int) trim($i))
                    ->filter();

                if ($ids->isNotEmpty()) {
                    $q->whereIn('id', $ids->all());
                }
            })
            ->when($this->filters['nombre'], function ($q, $v) {
                $v = trim((string) $v);
                $q->where('nombre', 'like', '%' . $v . '%');
            })
            ->when($this->filters['rfc'], function ($q, $v) {
                $v = trim((string) $v);
                $q->where('rfc', 'like', '%' . $v . '%');
            })
            ->when($this->filters['telefono'], function ($q, $v) {
                $v = trim((string) $v);
                $q->where('telefono', 'like', '%' . $v . '%');
            })
            ->when($this->filters['propietario'], function ($q, $v) {
                $v = '%' . trim((string) $v) . '%';
                $q->whereHas('propietario', function ($p) use ($v) {
                    $p->where('name', 'like', $v)
                        ->orWhere('email', 'like', $v);
                });
            })
            ->when($this->filters['sin_propietario'], function ($q) {
                $q->whereDoesntHave('propietario');
            });

        // ORDENAMIENTO
        $dir = $this->sortDir === 'asc' ? 'asc' : 'desc';

        switch ($this->sortField) {
            case 'id':
            case 'nombre':
            case 'rfc':
            case 'telefono':
                $query->orderBy($this->sortField, $dir);
                break;

            case 'propietario_nombre':
                $empresasTable = (new Empresa)->getTable();
                $usersTable    = (new User)->getTable();

                $query->leftJoin($usersTable, function ($join) use ($empresasTable, $usersTable) {
                        $join->on("{$usersTable}.id", '=', "{$empresasTable}.propietario_id")
                             ->where("{$usersTable}.es_propietario", true);
                    })
                    ->select("{$empresasTable}.*")
                    ->orderBy("{$usersTable}.name", $dir);
                break;

            default:
                $query->orderBy('nombre', 'asc');
                break;
        }

        return $query->paginate(10);
    }

    public function getEmpresaSeleccionadaProperty(): ?Empresa
    {
        if (!$this->empresaIdSeleccionada) {
            return null;
        }

        return Empresa::with([
                'propietario' => function ($q) {
                    $q->where('es_propietario', true);
                },
            ])
            ->find($this->empresaIdSeleccionada);
    }

    public function getCandidatosProperty()
    {
        if (!$this->empresaIdSeleccionada) {
            return collect();
        }

        return User::query()
            ->select('id', 'name', 'email', 'tipo', 'empresa_id', 'sucursal_id', 'es_propietario')
            ->when($this->searchUsuario, function ($q) {
                $search = '%' . $this->searchUsuario . '%';
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', $search)
                          ->orWhere('email', 'like', $search);
                });
            })
            ->orderBy('name')
            ->limit(100)
            ->get();
    }

    /* ===================== ACCIONES ===================== */

    public function updatedFilters(): void
    {
        $this->resetPage();
    }

    public function buscarPorFiltros(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filters = [
            'id'              => null,
            'nombre'          => null,
            'rfc'             => null,
            'telefono'        => null,
            'propietario'     => null,
            'sin_propietario' => false,
        ];
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (!in_array($field, $this->sortable, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir   = 'asc';
        }

        $this->resetPage();
    }

    public function abrirModal(int $empresaId): void
    {
        $this->empresaIdSeleccionada = $empresaId;

        $empresa = Empresa::with([
                'propietario' => function ($q) {
                    $q->where('es_propietario', true);
                },
            ])
            ->findOrFail($empresaId);

        $this->propietarioActualId = $empresa->propietario?->id;
        $this->nuevoPropietarioId  = $this->propietarioActualId;
        $this->searchUsuario       = '';

        $this->showModal = true;
    }

    public function cerrarModal(): void
    {
        $this->reset([
            'showModal',
            'empresaIdSeleccionada',
            'propietarioActualId',
            'nuevoPropietarioId',
            'searchUsuario',
        ]);
    }

    public function actualizarPropietario(): void
    {
        $this->validate([
            'empresaIdSeleccionada' => 'required|exists:empresas,id',
            'nuevoPropietarioId'    => 'required|exists:users,id',
        ], [
            'nuevoPropietarioId.required' => 'Selecciona un nuevo propietario.',
        ]);

        if ($this->nuevoPropietarioId === $this->propietarioActualId) {
            $this->dispatch(
                'notify',
                message: 'No hay cambios que guardar.',
                type: 'info'
            );
            return;
        }

        DB::transaction(function () {
            /** @var Empresa $empresa */
            $empresa = Empresa::lockForUpdate()->findOrFail($this->empresaIdSeleccionada);

            // 1) Buscar sucursal principal de esta empresa
            $sucursalPrincipalId = Sucursal::where('empresa_id', $empresa->id)
                ->where('tipo', 1)
                ->value('id');

            if (!$sucursalPrincipalId) {
                throw new \RuntimeException('La empresa no tiene sucursal principal (tipo=1) configurada.');
            }

            /** @var User $nuevo */
            $nuevo = User::lockForUpdate()->findOrFail($this->nuevoPropietarioId);

            // 2) Usuarios de esta empresa (EXCEPTO el nuevo)
            $usuariosEmpresa = User::where('empresa_id', $empresa->id)
                ->where('id', '<>', $nuevo->id)
                ->pluck('id')
                ->map(fn ($v) => (int) $v)
                ->values()
                ->all();

            // 3) Reset flags
            User::where('empresa_id', $empresa->id)->update([
                'user_can_sel_preproyectos' => null,
                'subordinados'              => null,
                'es_propietario'            => false,
                'updated_at'                => now(),
            ]);

            // 4) Configurar nuevo propietario
            $nuevo->empresa_id                = $empresa->id;
            $nuevo->sucursal_id               = $sucursalPrincipalId;
            $nuevo->es_propietario            = true;
            $nuevo->user_can_sel_preproyectos = $usuariosEmpresa;
            $nuevo->subordinados              = $usuariosEmpresa;
            $nuevo->updated_at                = now();
            $nuevo->save();
        });

        $this->resetPage();
        $this->cerrarModal();

        $this->dispatch(
            'notify',
            message: 'Propietario actualizado correctamente.',
            type: 'success'
        );
    }

    public function buscarCandidatos(): void
    {
        // Solo forzamos un re-render; el filtro ya se aplica en getCandidatosProperty()
        // Si en algún punto paginamos candidatos, aquí podríamos resetear página.
        // $this->resetPage();  // no es necesario ahora, pero lo dejo comentado por si luego cambias a paginate
    }

    public function limpiarBusquedaUsuarios(): void
    {
        $this->searchUsuario = '';
        // Forzamos un nuevo render para mostrar todos los candidatos
        // $this->resetPage();
    }

    public function render()
    {
        return view('livewire.empresas.cambiar-propietario-empresa', [
            'empresas'            => $this->empresas,
            'empresaSeleccionada' => $this->empresaSeleccionada,
            'candidatos'          => $this->candidatos,
        ]);
    }
}

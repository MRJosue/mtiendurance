<?php

namespace App\Livewire\Empresas;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Sucursal;

class CambiarPropietarioEmpresa extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    /** Filtro de empresas */
    public string $searchEmpresa = '';

    /** Modal y selección */
    public bool $showModal = false;
    public ?int $empresaIdSeleccionada = null;
    public ?int $propietarioActualId   = null;
    public ?int $nuevoPropietarioId    = null;
    public string $searchUsuario       = '';

    public function mount(): void
    {
        // Sin parámetros; se usa como admin global
    }

    /* ===================== QUERIES / PROPERTIES ===================== */

    public function getEmpresasProperty()
    {
        return Empresa::query()
            ->with([
                'propietario' => function ($q) {
                    $q->where('es_propietario', true);
                },
            ])
            ->when($this->searchEmpresa, function ($q) {
                $q->where(function ($inner) {
                    $inner->where('nombre', 'like', '%' . $this->searchEmpresa . '%')
                          ->orWhere('rfc', 'like', '%' . $this->searchEmpresa . '%');
                });
            })
            ->orderBy('nombre')
            ->paginate(10);
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
                $q->where(function ($inner) {
                    $inner->where('name', 'like', '%' . $this->searchUsuario . '%')
                          ->orWhere('email', 'like', '%' . $this->searchUsuario . '%');
                });
            })
            ->orderBy('name')
            ->limit(100)
            ->get();
    }

    /* ===================== ACCIONES ===================== */

    public function updatingSearchEmpresa()
    {
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

            // 2) Usuarios de esta empresa (EXCEPTO el nuevo propietario)
            $usuariosEmpresa = User::where('empresa_id', $empresa->id)
                ->where('id', '<>', $nuevo->id)
                ->pluck('id')
                ->map(fn ($v) => (int) $v)
                ->values()
                ->all();

            // 3) Actualizar flags de TODOS los usuarios de la empresa:
            //    - Solo el nuevo propietario mantiene subordinados / user_can_sel_preproyectos
            //    - El resto se limpia (null)
            User::where('empresa_id', $empresa->id)->update([
                'user_can_sel_preproyectos' => null,
                'subordinados'              => null,
                'es_propietario'            => false,
                'updated_at'                => now(),
            ]);

            // 4) Configurar nuevo propietario
            $nuevo->empresa_id               = $empresa->id;
            $nuevo->sucursal_id              = $sucursalPrincipalId;
            $nuevo->es_propietario           = true;
            $nuevo->user_can_sel_preproyectos = $usuariosEmpresa;
            $nuevo->subordinados             = $usuariosEmpresa;
            $nuevo->updated_at               = now();
            $nuevo->save();
        });

        // Refrescar datos
        $this->resetPage();
        $this->cerrarModal();

        $this->dispatch(
            'notify',
            message: 'Propietario actualizado correctamente.',
            type: 'success'
        );
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

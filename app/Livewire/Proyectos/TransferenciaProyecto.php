<?php

namespace App\Livewire\Proyectos;

use App\Models\Proyecto;
use App\Models\ProyectoTransferencia;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TransferenciaProyecto extends Component
{
    public Proyecto $proyecto;

    public ?ProyectoTransferencia $transferencia = null;

    public bool $modalSolicitudSubordinado = false;
    public ?int $owner_nuevo_subordinado_id = null;
    public string $motivo_subordinado = '';
    public $subordinadosUsuarios = [];

    public bool $modalSolicitudGeneral = false;
    public string $solicitudGeneralQuery = '';
    public array $solicitudGeneralResultados = [];
    public ?int $owner_nuevo_general_id = null;
    public ?array $solicitudGeneralSeleccionado = null;
    public string $motivo_general = '';

    public bool $modalAdminDirecto = false;
    public ?int $admin_owner_nuevo_id = null;
    public string $admin_motivo = 'Transferencia directa por administrador';
    public string $adminQuery = '';
    public array $adminResultados = [];

    public bool $modalAdminReprogramar = false;

    public function mount(Proyecto $proyecto)
    {
        $this->proyecto = $proyecto;

        $this->transferencia = ProyectoTransferencia::with(['ownerActual', 'ownerNuevo'])
            ->where('proyecto_id', $proyecto->id)
            ->whereIn('estado', ['PENDIENTE', 'APROBADO'])
            ->latest()
            ->first();

        $this->cargarSubordinados();
    }

    protected function cargarSubordinados(): void
    {
        $actor = Auth::user();

        $raw = $actor->subordinados ?? null;
        $ids = [];

        if (is_array($raw)) {
            $ids = $raw;
        } elseif (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $ids = $decoded;
            } else {
                $ids = preg_split('/\s*,\s*/', $raw);
            }
        }

        $ids = array_map(function ($v) {
            if (is_array($v) && isset($v['id'])) {
                return (int) $v['id'];
            }
            return (int) $v;
        }, $ids);

        $ids = array_values(array_unique(array_filter($ids, fn ($v) => $v > 0)));

        $this->subordinadosUsuarios = empty($ids)
            ? collect()
            : User::query()
                ->whereIn('id', $ids)
                ->where('ind_activo', 1)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'cliente');
                })
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
    }

    protected function validarClienteActivo(int $userId): User
    {
        $usuario = User::query()
            ->where('id', $userId)
            ->where('ind_activo', 1)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'cliente');
            })
            ->first();

        if (!$usuario) {
            throw new \RuntimeException('El usuario seleccionado no es un cliente activo válido.');
        }

        if ((int) $usuario->id === (int) $this->proyecto->usuario_id) {
            throw new \RuntimeException('El nuevo propietario no puede ser el mismo propietario actual.');
        }

        return $usuario;
    }

    protected function validarClienteSubordinado(int $userId): User
    {
        $usuario = collect($this->subordinadosUsuarios)->firstWhere('id', $userId);

        if (!$usuario) {
            throw new \RuntimeException('El usuario seleccionado no pertenece a tus subordinados.');
        }

        return $this->validarClienteActivo((int) $userId);
    }

    protected function crearRegistroSolicitud(int $ownerNuevoId, ?string $motivo, string $tipoSolicitud): void
    {
        if ($this->transferencia) {
            throw new \RuntimeException('Ya existe una solicitud activa.');
        }

        $this->transferencia = ProyectoTransferencia::create([
            'proyecto_id'       => $this->proyecto->id,
            'owner_actual_id'   => $this->proyecto->usuario_id,
            'owner_nuevo_id'    => $ownerNuevoId,
            'solicitado_por_id' => Auth::id(),
            'estado'            => 'PENDIENTE',
            'motivo'            => $motivo,
            'tipo_solicitud'    => $tipoSolicitud,
        ]);

        $this->transferencia->load(['ownerActual', 'ownerNuevo']);
    }

    public function abrirModalSolicitudSubordinado()
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->owner_nuevo_subordinado_id = null;
        $this->motivo_subordinado = '';
        $this->modalSolicitudSubordinado = true;
    }

    public function cerrarModalSolicitudSubordinado()
    {
        $this->modalSolicitudSubordinado = false;
        $this->owner_nuevo_subordinado_id = null;
        $this->motivo_subordinado = '';
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function crearSolicitudSubordinado()
    {
        $this->validate([
            'owner_nuevo_subordinado_id' => 'required|integer|exists:users,id',
            'motivo_subordinado' => 'nullable|string|max:500',
        ]);

        try {
            $this->validarClienteSubordinado((int) $this->owner_nuevo_subordinado_id);

            $this->crearRegistroSolicitud(
                (int) $this->owner_nuevo_subordinado_id,
                $this->motivo_subordinado,
                'SUBORDINADO'
            );

            $this->cerrarModalSolicitudSubordinado();

            $this->dispatch('notify', message: 'Solicitud a subordinado creada');
        } catch (\Throwable $e) {
            report($e);
            $this->addError('owner_nuevo_subordinado_id', $e->getMessage());
        }
    }

    public function abrirModalSolicitudGeneral()
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->solicitudGeneralQuery = '';
        $this->solicitudGeneralResultados = [];
        $this->owner_nuevo_general_id = null;
        $this->solicitudGeneralSeleccionado = null;
        $this->motivo_general = '';
        $this->modalSolicitudGeneral = true;
    }

    public function cerrarModalSolicitudGeneral()
    {
        $this->modalSolicitudGeneral = false;
        $this->solicitudGeneralQuery = '';
        $this->solicitudGeneralResultados = [];
        $this->owner_nuevo_general_id = null;
        $this->solicitudGeneralSeleccionado = null;
        $this->motivo_general = '';
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function updatedSolicitudGeneralQuery()
    {
        $q = trim($this->solicitudGeneralQuery);

        if (mb_strlen($q) < 2) {
            $this->solicitudGeneralResultados = [];
            return;
        }

        $this->solicitudGeneralResultados = User::query()
            ->where('ind_activo', 1)
            ->where('id', '!=', $this->proyecto->usuario_id)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'cliente');
            })
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'name', 'email'])
            ->toArray();
    }

    public function seleccionarSolicitudGeneralUsuario(int $id)
    {
        try {
            $usuario = $this->validarClienteActivo($id);

            $this->owner_nuevo_general_id = $usuario->id;
            $this->solicitudGeneralSeleccionado = [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email,
            ];
            $this->solicitudGeneralResultados = [];
        } catch (\Throwable $e) {
            report($e);
            $this->addError('owner_nuevo_general_id', $e->getMessage());
        }
    }

    public function crearSolicitudGeneral()
    {
        $this->validate([
            'owner_nuevo_general_id' => 'required|integer|exists:users,id',
            'motivo_general' => 'nullable|string|max:500',
        ]);

        try {
            $this->validarClienteActivo((int) $this->owner_nuevo_general_id);

            $this->crearRegistroSolicitud(
                (int) $this->owner_nuevo_general_id,
                $this->motivo_general,
                'GENERAL'
            );

            $this->cerrarModalSolicitudGeneral();

            $this->dispatch('notify', message: 'Solicitud abierta creada');
        } catch (\Throwable $e) {
            report($e);
            $this->addError('owner_nuevo_general_id', $e->getMessage());
        }
    }

    public function autorizar()
    {
        abort_unless(Auth::user()->can('proyectos.transferencia.aprobar'), 403);

        if (!$this->transferencia) {
            $this->dispatch('notify', message: 'No hay solicitud activa.');
            return;
        }

        $this->transferencia->update([
            'estado' => 'APROBADO',
            'aprobado_por_id' => Auth::id(),
            'approved_at' => now(),
        ]);

        $this->transferencia->refresh();

        $this->dispatch('notify', message: 'Transferencia autorizada');
    }

    public function cancelar()
    {
        if (!$this->transferencia) {
            $this->dispatch('notify', message: 'No hay solicitud activa.');
            return;
        }

        $this->transferencia->update([
            'estado' => 'CANCELADO',
        ]);

        $this->transferencia = null;

        $this->dispatch('notify', message: 'Solicitud cancelada');
    }

    public function aplicarTransferencia()
    {
        abort_unless(Auth::user()->can('proyectos.transferencia.aplicar'), 403);

        if (!$this->transferencia) {
            $this->dispatch('notify', message: 'No hay solicitud activa.');
            return;
        }

        try {
            DB::transaction(function () {
                $transfer = ProyectoTransferencia::lockForUpdate()->findOrFail($this->transferencia->id);
                $proyecto = Proyecto::lockForUpdate()->findOrFail($this->proyecto->id);

                if ($transfer->estado !== 'PENDIENTE') {
                    throw new \RuntimeException('La solicitud ya no está pendiente.');
                }

                if ((int) $proyecto->usuario_id !== (int) $transfer->owner_actual_id) {
                    throw new \RuntimeException('El propietario actual del proyecto cambió. Revisa antes de aplicar.');
                }

                $this->validarClienteActivo((int) $transfer->owner_nuevo_id);

                $proyecto->update([
                    'usuario_id' => $transfer->owner_nuevo_id,
                ]);

                $transfer->update([
                    'estado'     => 'APLICADO',
                    'applied_at' => now(),
                ]);

                $this->transferencia = null;
                $this->proyecto->refresh();
            });

            $this->dispatch('notify', message: 'Transferencia aplicada ✅');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', message: 'Error al aplicar: ' . $e->getMessage());
        }
    }

    public function abrirModalAdminDirecto()
    {
        abort_unless(Auth::user()->hasRole('admin'), 403);

        $this->resetErrorBag();
        $this->resetValidation();

        $this->admin_owner_nuevo_id = null;
        $this->adminQuery = '';
        $this->adminResultados = [];
        $this->modalAdminDirecto = true;
    }

    public function updatedAdminQuery()
    {
        if (!Auth::user()->hasRole('admin')) {
            $this->adminResultados = [];
            return;
        }

        $q = trim($this->adminQuery);

        if (mb_strlen($q) < 2) {
            $this->adminResultados = [];
            return;
        }

        $this->adminResultados = User::query()
            ->where('ind_activo', 1)
            ->where('id', '!=', $this->proyecto->usuario_id)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'cliente');
            })
            ->where(function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(15)
            ->get(['id','name','email'])
            ->toArray();
    }

    public function seleccionarAdminUsuario(int $id)
    {
        abort_unless(Auth::user()->hasRole('admin'), 403);

        try {
            $this->validarClienteActivo($id);
            $this->admin_owner_nuevo_id = $id;
            $this->adminResultados = [];
        } catch (\Throwable $e) {
            report($e);
            $this->addError('admin_owner_nuevo_id', $e->getMessage());
        }
    }

    public function aplicarTransferenciaDirecta()
    {
        abort_unless(Auth::user()->hasRole('admin'), 403);

        $this->validate([
            'admin_owner_nuevo_id' => 'required|exists:users,id|different:proyecto.usuario_id',
            'admin_motivo' => 'nullable|string|max:500',
        ]);

        try {
            $this->validarClienteActivo((int) $this->admin_owner_nuevo_id);

            DB::transaction(function () {
                $proyecto = Proyecto::lockForUpdate()->findOrFail($this->proyecto->id);

                ProyectoTransferencia::create([
                    'proyecto_id'       => $proyecto->id,
                    'owner_actual_id'   => $proyecto->usuario_id,
                    'owner_nuevo_id'    => $this->admin_owner_nuevo_id,
                    'solicitado_por_id' => Auth::id(),
                    'aprobado_por_id'   => Auth::id(),
                    'estado'            => 'APLICADO',
                    'motivo'            => $this->admin_motivo ?: 'Transferencia directa por administrador',
                    'tipo_solicitud'    => 'ADMIN_DIRECTA',
                    'approved_at'       => now(),
                    'applied_at'        => now(),
                ]);

                $proyecto->update([
                    'usuario_id' => $this->admin_owner_nuevo_id,
                ]);

                $this->transferencia = null;
                $this->proyecto->refresh();
            });

            $this->modalAdminDirecto = false;
            $this->dispatch('notify', message: 'Transferencia directa aplicada ✅');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', message: 'Error al aplicar: ' . $e->getMessage());
        }
    }

    public function abrirModalAdminReprogramar()
    {
        abort_unless(Auth::user()->hasRole('admin'), 403);
        $this->modalAdminReprogramar = true;
    }

    public function adminGenerarReprogramacion()
    {
        abort_unless(Auth::user()->hasRole('admin'), 403);

        $proyecto = Proyecto::find($this->proyecto->id);

        if (!$proyecto) {
            $this->dispatch('notify', message: 'Proyecto no encontrado');
            return;
        }

        $proyecto->update([
            'flag_solicitud_reconfigurar' => 1,
            'flag_reconfigurar' => 1,
        ]);

        $this->proyecto->refresh();

        $this->dispatch('reconfiguracionSolicitada', proyectoId: $proyecto->id);

        $this->modalAdminReprogramar = false;

        return $this->redirectRoute(
            'reprogramacion.reprogramacionproyectopedido',
            ['proyecto' => $proyecto->id]
        );
    }

    public function render()
    {
        return view('livewire.proyectos.transferencia-proyecto');
    }
}
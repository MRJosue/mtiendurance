<?php

namespace App\Livewire\Proyectos;

use Livewire\Component;
use App\Models\Proyecto;
use App\Models\User;
use App\Models\ProyectoTransferencia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransferenciaProyecto extends Component
{
    public Proyecto $proyecto;

    public bool $modalSolicitud = false;

    public ?int $owner_nuevo_id = null;
    public string $motivo = '';

    public ?ProyectoTransferencia $transferencia = null;

    public bool $modalAdminDirecto = false;
    public ?int $admin_owner_nuevo_id = null;
    public string $admin_motivo = 'Transferencia directa por administrador';

    public $subordinadosUsuarios = []; // colección/array para el select de solicitud

    public string $adminQuery = '';
    public $adminResultados = [];


    public bool $modalAdminReprogramar = false;


    protected function rules()
    {
        return [
            'owner_nuevo_id' => 'required|exists:users,id|different:proyecto.usuario_id',
            'motivo' => 'nullable|string|max:500',
        ];
    }

    public function mount(Proyecto $proyecto)
    {
        $this->proyecto = $proyecto;

        // ===== Transferencia activa (si existe) =====
        $this->transferencia = ProyectoTransferencia::where('proyecto_id', $proyecto->id)
            ->whereIn('estado', ['PENDIENTE', 'APROBADO'])
            ->latest()
            ->first();

        // ===== Subordinados del usuario autenticado =====
        $actor = Auth::user();

        $raw = $actor->subordinados ?? null;
        $ids = [];

        if (is_array($raw)) {
            $ids = $raw;

        } elseif (is_string($raw) && trim($raw) !== '') {

            // Intentar JSON
            $decoded = json_decode($raw, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $ids = $decoded;
            } else {
                // Fallback CSV: "1,2,3"
                $ids = preg_split('/\s*,\s*/', $raw);
            }
        }

        // Normalizar a IDs enteros
        $ids = array_map(function ($v) {
            if (is_array($v) && isset($v['id'])) {
                return (int) $v['id'];
            }
            return (int) $v;
        }, $ids);

        // Limpiar basura
        $ids = array_values(array_unique(array_filter(
            $ids,
            fn ($v) => $v > 0
        )));

        // (Opcional) permitir que el usuario se seleccione a sí mismo
        // $ids[] = (int) $actor->id;

        $this->subordinadosUsuarios = empty($ids)
            ? collect()
            : User::query()
                ->whereIn('id', $ids)
                ->where('ind_activo', 1)
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
    }

    /* ==========================
     |  Solicitud
     ========================== */

    public function abrirModalSolicitud()
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->reset(['owner_nuevo_id', 'motivo']);
        $this->modalSolicitud = true;
    }

    public function crearSolicitud()
    {
        $this->validate();

        if ($this->transferencia) {
            $this->addError('general', 'Ya existe una solicitud activa.');
            return;
        }

        $this->transferencia = ProyectoTransferencia::create([
            'proyecto_id'       => $this->proyecto->id,
            'owner_actual_id'   => $this->proyecto->usuario_id,
            'owner_nuevo_id'    => $this->owner_nuevo_id,
            'solicitado_por_id' => Auth::id(),
            'estado'            => 'PENDIENTE',
            'motivo'            => $this->motivo,
        ]);

        $this->modalSolicitud = false;

        $this->dispatch('notify', message: 'Solicitud de transferencia creada');
    }

    /* ==========================
     |  Autorización / Cancelación
     ========================== */

    public function autorizar()
    {
        abort_unless(Auth::user()->can('proyectos.transferencia.aprobar'), 403);

        $this->transferencia->update([
            'estado' => 'APROBADO',
            'aprobado_por_id' => Auth::id(),
            'approved_at' => now(),
        ]);

        $this->dispatch('notify', message: 'Transferencia autorizada');
    }

    public function cancelar()
    {
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

            // Solo permitir aplicar si sigue PENDIENTE (como tú lo pediste)
            if ($transfer->estado !== 'PENDIENTE') {
                throw new \RuntimeException('La solicitud ya no está pendiente.');
            }

            // Validación fuerte: dueño actual no cambió
            if ((int) $proyecto->usuario_id !== (int) $transfer->owner_actual_id) {
                throw new \RuntimeException('El propietario actual del proyecto cambió. Revisa antes de aplicar.');
            }

            // Aplicar cambio
            $proyecto->update([
                'usuario_id' => $transfer->owner_nuevo_id,
            ]);

            // Marcar aplicada
            $transfer->update([
                'estado'     => 'APLICADO',
                'applied_at' => now(),
            ]);

            // Limpieza en el componente
            $this->transferencia = null;
            $this->proyecto->refresh();
        });

        $this->dispatch('notify', message: 'Transferencia aplicada ✅');
    } catch (\Throwable $e) {
        // Para que NO se sienta “no hizo nada”
        report($e);
        $this->dispatch('notify', message: 'Error al aplicar: ' . $e->getMessage());
    }
}

public function abrirModalAdminDirecto()
{
    abort_unless(Auth::user()->hasRole('admin'), 403);

    $this->reset(['admin_owner_nuevo_id']);
    $this->modalAdminDirecto = true;
}

public function aplicarTransferenciaDirecta()
{
    abort_unless(Auth::user()->hasRole('admin'), 403);

    $this->validate([
        'admin_owner_nuevo_id' => 'required|exists:users,id|different:proyecto.usuario_id',
        'admin_motivo' => 'nullable|string|max:500',
        
    ]);

    try {
        DB::transaction(function () {
            $proyecto = Proyecto::lockForUpdate()->findOrFail($this->proyecto->id);

            // Registrar histórico (aunque sea directo)
            ProyectoTransferencia::create([
                'proyecto_id'       => $proyecto->id,
                'owner_actual_id'   => $proyecto->usuario_id,
                'owner_nuevo_id'    => $this->admin_owner_nuevo_id,
                'solicitado_por_id' => Auth::id(),
                'aprobado_por_id'   => Auth::id(),
                'estado'            => 'APLICADO',
                'motivo'            => $this->admin_motivo ?: 'Transferencia directa por administrador',
                'approved_at'       => now(),
                'applied_at'        => now(),
            ]);

            // Aplicar cambio real
            $proyecto->update([
                'usuario_id' => $this->admin_owner_nuevo_id,
            ]);

            // Limpieza y refresh
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

        $this->admin_owner_nuevo_id = $id;

        // opcional: ocultar lista al seleccionar
        $this->adminResultados = [];
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

        // === Basado en reconfiguración: activar flags ===
        // 1) Marca que hay solicitud (como cliente)
        // 2) Habilita reconfiguración (para que el flujo quede consistente)
        $proyecto->update([
            'flag_solicitud_reconfigurar' => 1,
            'flag_reconfigurar' => 1,
        ]);

        $this->proyecto->refresh();

        // Para que otros componentes (como SubirDiseno) se enteren si lo usas:
        $this->dispatch('reconfiguracionSolicitada', proyectoId: $proyecto->id);

        $this->modalAdminReprogramar = false;

        // Redirige al flujo real de reprogramación (igual que tu SubirDiseno->confirmarReconfigurar)
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



// namespace App\Livewire\Proyectos;

// use Livewire\Component;

// class TransferenciaProyecto extends Component
// {
//     public function render()
//     {
//         return view('livewire.proyectos.transferencia-proyecto');
//     }
// }

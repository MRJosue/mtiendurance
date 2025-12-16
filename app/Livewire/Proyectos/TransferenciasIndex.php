<?php

namespace App\Livewire\Proyectos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Proyecto;
use App\Models\ProyectoTransferencia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransferenciasIndex extends Component
{
    use WithPagination;

    public int $perPage = 20;
    public array $perPageOptions = [10, 20, 30, 50, 100];

    // ✅ Quitamos APROBADO
    public array $tabs = ['TODAS', 'PENDIENTE', 'APLICADO', 'CANCELADO'];
    public string $activeTab = 'TODAS';

    public string $sortField = 'id';
    public string $sortDir = 'desc';

    // ✅ selección múltiple
    public array $selectedTransferencias = [];
    public bool $selectAll = false;

    public array $filters = [
        'id' => '',
        'proyecto_id' => '',
        'owner_actual_id' => '',
        'owner_nuevo_id' => '',
    ];

    protected $queryString = [
        'activeTab' => ['except' => 'TODAS'],
        'perPage'   => ['except' => 20],
        'sortField' => ['except' => 'id'],
        'sortDir'   => ['except' => 'desc'],
    ];

    public function setTab(string $tab): void
    {
        $this->activeTab = in_array($tab, $this->tabs) ? $tab : 'TODAS';
        $this->resetPage();
        $this->selectAll = false;
        $this->selectedTransferencias = [];
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir = 'asc';
        }

        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
        $this->selectAll = false;
        $this->selectedTransferencias = [];
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
        $this->selectAll = false;
        $this->selectedTransferencias = [];
    }

    protected function baseQuery()
    {
        return ProyectoTransferencia::query()
            ->with([
                'proyecto:id,nombre,usuario_id',
                'ownerActual:id,name,email',
                'ownerNuevo:id,name,email',
                'solicitadoPor:id,name,email',
                'aprobadoPor:id,name,email',
            ]);
    }

    /* ==========================
     |  Acciones PENDIENTE
     ========================== */

    protected function canCancelar(ProyectoTransferencia $t): bool
    {
        // Ajusta si tienes un permiso específico
        return Auth::user()->hasRole('admin')
            || (int) $t->solicitado_por_id === (int) Auth::id()
            || Auth::user()->can('proyectos.transferencia.cancelar');
    }

    protected function canAplicar(): bool
    {
        return Auth::user()->can('proyectos.transferencia.aplicar') || Auth::user()->hasRole('admin');
    }

    public function cancelar(int $transferenciaId): void
    {
        $t = ProyectoTransferencia::findOrFail($transferenciaId);

        abort_unless($this->canCancelar($t), 403);

        if ($t->estado !== 'PENDIENTE') {
            $this->dispatch('notify', message: 'Solo se pueden cancelar solicitudes PENDIENTE.');
            return;
        }

        $t->update(['estado' => 'CANCELADO']);

        $this->selectedTransferencias = array_values(array_diff($this->selectedTransferencias, [$transferenciaId]));
        $this->selectAll = false;

        $this->dispatch('notify', message: "Solicitud #{$t->id} cancelada ✅");
    }

    public function aplicar(int $transferenciaId): void
    {
        abort_unless($this->canAplicar(), 403);

        try {
            DB::transaction(function () use ($transferenciaId) {
                $t = ProyectoTransferencia::lockForUpdate()->findOrFail($transferenciaId);

                if ($t->estado !== 'PENDIENTE') {
                    throw new \RuntimeException('Solo se pueden aplicar solicitudes PENDIENTE.');
                }

                $proyecto = Proyecto::lockForUpdate()->findOrFail($t->proyecto_id);

                // Validación fuerte: dueño actual no cambió
                if ((int) $proyecto->usuario_id !== (int) $t->owner_actual_id) {
                    throw new \RuntimeException('El propietario actual del proyecto cambió. Revisa antes de aplicar.');
                }

                // Aplicar cambio real
                $proyecto->update([
                    'usuario_id' => $t->owner_nuevo_id,
                ]);

                // Marcar aplicada
                $t->update([
                    'estado'     => 'APLICADO',
                    'applied_at' => now(),
                ]);
            });

            $this->selectedTransferencias = array_values(array_diff($this->selectedTransferencias, [$transferenciaId]));
            $this->selectAll = false;

            $this->dispatch('notify', message: "Transferencia #{$transferenciaId} aplicada ✅");
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', message: 'Error al aplicar: ' . $e->getMessage());
        }
    }

    public function cancelarSeleccionadas(): void
    {
        if (empty($this->selectedTransferencias)) {
            $this->dispatch('notify', message: 'No hay solicitudes seleccionadas.');
            return;
        }

        $ids = $this->selectedTransferencias;

        $ok = 0; $skip = 0; $fail = 0;

        foreach ($ids as $id) {
            try {
                $t = ProyectoTransferencia::find($id);
                if (!$t) { $skip++; continue; }

                if (!$this->canCancelar($t)) { $skip++; continue; }
                if ($t->estado !== 'PENDIENTE') { $skip++; continue; }

                $t->update(['estado' => 'CANCELADO']);
                $ok++;
            } catch (\Throwable $e) {
                report($e);
                $fail++;
            }
        }

        $this->selectedTransferencias = [];
        $this->selectAll = false;

        $this->dispatch('notify', message: "Canceladas: {$ok} | Omitidas: {$skip} | Fallidas: {$fail}");
    }

    public function aplicarSeleccionadas(): void
    {
        abort_unless($this->canAplicar(), 403);

        if (empty($this->selectedTransferencias)) {
            $this->dispatch('notify', message: 'No hay solicitudes seleccionadas.');
            return;
        }

        $ok = 0; $skip = 0; $fail = 0;

        foreach ($this->selectedTransferencias as $id) {
            try {
                DB::transaction(function () use ($id, &$ok, &$skip) {
                    $t = ProyectoTransferencia::lockForUpdate()->find($id);
                    if (!$t) { $skip++; return; }
                    if ($t->estado !== 'PENDIENTE') { $skip++; return; }

                    $proyecto = Proyecto::lockForUpdate()->find($t->proyecto_id);
                    if (!$proyecto) { $skip++; return; }

                    if ((int) $proyecto->usuario_id !== (int) $t->owner_actual_id) {
                        $skip++; return;
                    }

                    $proyecto->update(['usuario_id' => $t->owner_nuevo_id]);
                    $t->update(['estado' => 'APLICADO', 'applied_at' => now()]);
                    $ok++;
                });
            } catch (\Throwable $e) {
                report($e);
                $fail++;
            }
        }

        $this->selectedTransferencias = [];
        $this->selectAll = false;

        $this->dispatch('notify', message: "Aplicadas: {$ok} | Omitidas: {$skip} | Fallidas: {$fail}");
    }

    public function render()
    {
        $q = $this->baseQuery();

        if ($this->activeTab !== 'TODAS') {
            $q->where('estado', $this->activeTab);
        }

        if (!empty($this->filters['id'])) {
            $q->where('id', (int) $this->filters['id']);
        }
        if (!empty($this->filters['proyecto_id'])) {
            $q->where('proyecto_id', (int) $this->filters['proyecto_id']);
        }
        if (!empty($this->filters['owner_actual_id'])) {
            $q->where('owner_actual_id', (int) $this->filters['owner_actual_id']);
        }
        if (!empty($this->filters['owner_nuevo_id'])) {
            $q->where('owner_nuevo_id', (int) $this->filters['owner_nuevo_id']);
        }

        $allowedSort = ['id','proyecto_id','estado','created_at','approved_at','applied_at'];
        if (!in_array($this->sortField, $allowedSort)) {
            $this->sortField = 'id';
        }

        $transferencias = $q
            ->orderBy($this->sortField, $this->sortDir)
            ->paginate($this->perPage);

        // ✅ solo se pueden seleccionar PENDIENTE
        $pageSelectableIds = $transferencias->getCollection()
            ->where('estado', 'PENDIENTE')
            ->pluck('id')
            ->values();

        return view('livewire.proyectos.transferencias-index', compact('transferencias', 'pageSelectableIds'));
    }
}

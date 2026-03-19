<?php

namespace App\Livewire\Tareas;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tarea;
use App\Models\proyecto_estados;
use Illuminate\Support\Facades\Auth;

class AdministraTareas extends Component
{
    use WithPagination;

    public $selectedTask;
    public $newStatus;
    public $statuses = ['PENDIENTE', 'EN PROCESO', 'COMPLETADA', 'RECHAZADO', 'CANCELADO'];
    public $modalOpen = false;
    public $mostrarModalConfirmacion = false;
    public $proyectoPendienteConfirmacion = null;

    public int $perPage = 10;
    public array $perPageOptions = [10, 20, 30, 50, 100];

    public string $sortField = 'id';
    public string $sortDir = 'desc';

    protected array $sortable = [
        'id',
        'proyecto_id',
        'tipo',
        'estado',
        'descripcion',
    ];

    public string $activeTab = 'TODAS';

    public array $tabs = [
        'TODAS',
        'PENDIENTE',
        'EN PROCESO',
        'COMPLETADA',
        'RECHAZADO',
        'CANCELADO',
    ];

    public array $filters = [
        'id' => null,
        'proyecto_id' => null,
        'proyecto' => null,
        'usuario' => null,
        'asignado' => null,
        'tipo' => null,
        'estado' => null,
    ];

    public function mount(): void
    {
        $this->activeTab = 'TODAS';
        $this->perPage = 10;
    }

    public function updating($field): void
    {
        if ($field === 'perPage') {
            $this->resetPage();
        }
    }

    public function updatedPerPage($value): void
    {
        $value = (int) $value;

        if (!in_array($value, $this->perPageOptions, true)) {
            $value = 10;
        }

        $this->perPage = $value;
        $this->resetPage();
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        if (!in_array($tab, $this->tabs, true)) {
            return;
        }

        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function filtroEstado(string $estado): void
    {
        if (($this->filters['estado'] ?? '') === $estado) {
            $this->filters['estado'] = '';
        } else {
            $this->filters['estado'] = $estado;
        }

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filters = [
            'id' => null,
            'proyecto_id' => null,
            'proyecto' => null,
            'usuario' => null,
            'asignado' => null,
            'tipo' => null,
            'estado' => null,
        ];

        $this->resetPage();
        $this->dispatch('filters-cleared');
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
            $this->sortDir = 'asc';
        }

        $this->resetPage();
    }

    public function abrirModal($taskId): void
    {
        $this->selectedTask = Tarea::find($taskId);

        if (!$this->selectedTask) {
            session()->flash('error', 'Error: Tarea no encontrada.');
            return;
        }

        $this->newStatus = $this->selectedTask->estado;
        $this->modalOpen = true;
    }

    public function actualizarEstado(): void
    {
        if (!$this->selectedTask) {
            session()->flash('error', 'No hay tarea seleccionada.');
            return;
        }

        $this->validate([
            'newStatus' => 'required|in:PENDIENTE,EN PROCESO,COMPLETADA,RECHAZADO,CANCELADO',
        ]);

        $this->selectedTask->estado = $this->newStatus;
        $this->selectedTask->save();

        $proyecto = $this->selectedTask->proyecto;

        if ($proyecto) {
            if (in_array($this->newStatus, ['EN PROCESO', 'RECHAZADO'])) {
                $proyecto->estado = 'EN PROCESO';
            }

            if ($this->newStatus === 'COMPLETADA') {
                $proyecto->estado = 'REVISION';
            }

            $proyecto->save();

            proyecto_estados::create([
                'proyecto_id' => $proyecto->id,
                'estado' => $proyecto->estado,
                'fecha_inicio' => now(),
                'usuario_id' => Auth::id(),
            ]);
        }

        session()->flash('message', 'Estatus de la tarea actualizado correctamente.');
        $this->cerrarModal();
    }

    public function cerrarModal(): void
    {
        $this->modalOpen = false;
        $this->selectedTask = null;
        $this->newStatus = null;
    }

    public function verificarProceso($taskId)
    {
        $tarea = Tarea::with('proyecto')->find($taskId);

        if (!$tarea || !$tarea->proyecto) {
            session()->flash('error', 'La tarea o el proyecto no fueron encontrados.');
            return;
        }

        $usuarioAutenticadoId = Auth::id();

        // Si la tarea NO pertenece al usuario autenticado,
        // se envía directo a los detalles del proyecto.
        if ((int) $tarea->staff_id !== (int) $usuarioAutenticadoId) {
            return redirect()->route('proyecto.show', $tarea->proyecto->id);
        }

        // Si la tarea sí le pertenece, sigue el flujo normal.
        if ((int) $tarea->disenio_flag_first_proceso === 0) {
            $this->mostrarModalConfirmacion = true;
            $this->proyectoPendienteConfirmacion = $tarea->proyecto;
            return;
        }

        return redirect()->route('proyecto.show', $tarea->proyecto->id);
    }

    public function confirmarInicioProceso()
    {
        if (!$this->proyectoPendienteConfirmacion) {
            return;
        }

        $proyecto = $this->proyectoPendienteConfirmacion;
        $tarea = $proyecto->tareas()->first();

        if ($tarea) {
            $tarea->disenio_flag_first_proceso = 1;
            $tarea->save();
        }

        $proyecto->estado = 'EN PROCESO';
        $proyecto->save();

        proyecto_estados::create([
            'proyecto_id' => $proyecto->id,
            'estado' => 'EN PROCESO',
            'fecha_inicio' => now(),
            'usuario_id' => Auth::id(),
        ]);

        $this->mostrarModalConfirmacion = false;
        $this->proyectoPendienteConfirmacion = null;

        return redirect()->route('proyecto.show', $proyecto->id);
    }

    public function cancelarConfirmacion(): void
    {
        $this->mostrarModalConfirmacion = false;
        $this->proyectoPendienteConfirmacion = null;
    }

    protected function buildTasksQuery()
    {
        $user = auth()->user();
        $puedeVerTodasLasTareas = $user->hasRole('admin') || $user->can('tareas-disenio-ver-todas');

        $query = Tarea::query()->with([
            'proyecto',
            'proyecto.user',
            'staff',
        ]);

        if (!$puedeVerTodasLasTareas) {
            $query->where('staff_id', $user->id);
        }

        if ($this->activeTab !== 'TODAS') {
            $query->where('estado', $this->activeTab);
        }

        $query
            ->when($this->filters['id'], function ($q, $v) {
                $ids = collect(preg_split('/[,;\s]+/', (string) $v, -1, PREG_SPLIT_NO_EMPTY))
                    ->map(fn ($i) => (int) trim($i))
                    ->filter();

                if ($ids->count() === 1) {
                    $q->where('id', $ids->first());
                } elseif ($ids->isNotEmpty()) {
                    $q->whereIn('id', $ids->all());
                }
            })
            ->when($this->filters['proyecto_id'], function ($q, $v) {
                $ids = collect(preg_split('/[,;\s]+/', (string) $v, -1, PREG_SPLIT_NO_EMPTY))
                    ->map(fn ($i) => (int) trim($i))
                    ->filter();

                if ($ids->count() === 1) {
                    $q->where('proyecto_id', $ids->first());
                } elseif ($ids->isNotEmpty()) {
                    $q->whereIn('proyecto_id', $ids->all());
                }
            })
            ->when($this->filters['proyecto'], fn ($q, $v) =>
                $q->whereHas('proyecto', fn ($sub) =>
                    $sub->where('nombre', 'like', '%' . trim($v) . '%')
                )
            )
            ->when($this->filters['usuario'] && $puedeVerTodasLasTareas, fn ($q, $v) =>
                $q->whereHas('proyecto.user', fn ($sub) =>
                    $sub->where('name', 'like', '%' . trim($v) . '%')
                        ->orWhere('email', 'like', '%' . trim($v) . '%')
                )
            )
            ->when($this->filters['asignado'], fn ($q, $v) =>
                $q->whereHas('staff', fn ($sub) =>
                    $sub->where('name', 'like', '%' . trim($v) . '%')
                        ->orWhere('email', 'like', '%' . trim($v) . '%')
                )
            )
            ->when($this->filters['tipo'], fn ($q, $v) =>
                $q->where('tipo', 'like', '%' . trim($v) . '%')
            )
            ->when($this->filters['estado'], fn ($q, $v) =>
                $q->where('estado', $v)
            );

        if (!in_array($this->sortField, $this->sortable, true)) {
            $this->sortField = 'id';
        }

        $query->orderBy($this->sortField, $this->sortDir);

        return $query;
    }

    

    public function render()
    {
        $user = auth()->user();
        $puedeVerTodasLasTareas = $user->hasRole('admin') || $user->can('tareas-disenio-ver-todas');

        $tasks = $this->buildTasksQuery()->paginate($this->perPage);

        return view('livewire.tareas.administra-tareas', [
            'tasks' => $tasks,
            'puedeVerTodasLasTareas' => $puedeVerTodasLasTareas,
        ]);
    }
}
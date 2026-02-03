<?php

namespace App\Livewire\Disenio;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tarea;
use App\Models\proyecto_estados;
use Illuminate\Support\Facades\Auth;

class AdministrarTareas extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    /** UI / Estado */
    public array $perPageOptions = [10, 25, 50, 100];
    public int $perPage = 10;

    public string $sortField = 'tareas.id';
    public string $sortDir   = 'desc';

    /** Filtros */
    public array $filters = [
        'id'          => '',
        'proyecto_id' => '',
        'proyecto'    => '',
        'asignado'    => '',
        'descripcion' => '',
        'estado'      => '',
    ];

    /** Modales */
    public $selectedTask;
    public ?string $newStatus = null;
    public array $statuses = ['PENDIENTE', 'EN PROCESO', 'COMPLETADA', 'RECHAZADO', 'CANCELADO'];
    public bool $modalOpen = false;
    public bool $mostrarModalConfirmacion = false;
    public $proyectoPendienteConfirmacion = null;

    // ======== Acciones UI ========

    public function updatingPerPage() { $this->resetPage(); }
    public function updatedFilters()  { $this->resetPage(); }

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

    public function filtroEstado(string $estado): void
    {
        $this->filters['estado'] = ($this->filters['estado'] === $estado) ? '' : $estado;
        $this->resetPage();
    }

    // ======== Modales / Estado ========

    public function abrirModal($taskId)
    {
        $this->selectedTask = Tarea::with(['proyecto','staff'])->find($taskId);
        if (!$this->selectedTask) {
            session()->flash('error', 'Error: Tarea no encontrada.');
            return;
        }
        $this->newStatus = $this->selectedTask->estado;
        $this->modalOpen = true;
    }

    public function cerrarModal()
    {
        $this->modalOpen = false;
        $this->selectedTask = null;
        $this->newStatus = null;
    }

    public function actualizarEstado()
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

        // Reglas de negocio para estado del proyecto
        $proyecto = $this->selectedTask->proyecto;

        if ($this->newStatus === 'EN PROCESO' && $proyecto) {
            $proyecto->estado = 'EN PROCESO';
            $proyecto->save();

            proyecto_estados::create([
                'proyecto_id'  => $proyecto->id,
                'estado'       => 'EN PROCESO',
                'fecha_inicio' => now(),
                'usuario_id'   => Auth::id(),
            ]);
        }

        if ($this->newStatus === 'RECHAZADO' && $proyecto) {
            // Mantén la regla que solicitaste anteriormente
            $proyecto->estado = 'EN PROCESO';
            $proyecto->save();

            proyecto_estados::create([
                'proyecto_id'  => $proyecto->id,
                'estado'       => 'EN PROCESO',
                'fecha_inicio' => now(),
                'usuario_id'   => Auth::id(),
            ]);
        }

        if ($this->newStatus === 'COMPLETADA' && $proyecto) {
            $proyecto->estado = 'REVISION';
            $proyecto->save();

            proyecto_estados::create([
                'proyecto_id'  => $proyecto->id,
                'estado'       => 'REVISION',
                'fecha_inicio' => now(),
                'usuario_id'   => Auth::id(),
            ]);
        }

        session()->flash('message', 'Estatus de la tarea actualizado correctamente.');
        $this->cerrarModal();
    }

    public function verificarProceso($proyectoId)
    {
        $tarea = Tarea::with('proyecto')
            ->where('proyecto_id', $proyectoId)
            ->first();

        if ($tarea && (int)($tarea->disenio_flag_first_proceso ?? 0) === 0) {
            $this->mostrarModalConfirmacion = true;
            $this->proyectoPendienteConfirmacion = $tarea->proyecto;
            return;
        }

        return redirect()->route('proyecto.show', $proyectoId);
    }

    public function confirmarInicioProceso()
    {
        if (!$this->proyectoPendienteConfirmacion) return;

        $proyecto = $this->proyectoPendienteConfirmacion;
        $tarea = $proyecto->tareas()->first();

        if ($tarea) {
            $tarea->disenio_flag_first_proceso = 1;
            $tarea->save();
        }

        $proyecto->estado = 'EN PROCESO';
        $proyecto->save();

        proyecto_estados::create([
            'proyecto_id'  => $proyecto->id,
            'estado'       => 'EN PROCESO',
            'fecha_inicio' => now(),
            'usuario_id'   => Auth::id(),
        ]);

        $this->mostrarModalConfirmacion = false;
        return redirect()->route('proyecto.show', $proyecto->id);
    }

    public function cancelarConfirmacion()
    {
        $this->mostrarModalConfirmacion = false;
        $this->proyectoPendienteConfirmacion = null;
    }

    // ======== Query con filtros y orden ========

    public function render()
    {
        $q = Tarea::query()
            ->select('tareas.*')
            ->with(['proyecto', 'staff'])
            ->leftJoin('proyectos', 'proyectos.id', '=', 'tareas.proyecto_id')
            ->leftJoin('users', 'users.id', '=', 'tareas.staff_id');

        // Rol: si no eres admin, solo tus tareas
        if (!auth()->user()->hasRole('admin')) {
            $q->where('tareas.staff_id', auth()->id());
        }

        // Filtro ID (soporta coma)
        if ($id = trim((string)($this->filters['id'] ?? ''))) {
            $ids = collect(preg_split('/\s*,\s*/', $id, -1, PREG_SPLIT_NO_EMPTY))
                ->map(fn($v) => (int)$v)
                ->filter();
            if ($ids->isNotEmpty()) {
                $q->whereIn('tareas.id', $ids->all());
            }
        }

        // Filtro proyecto_id (coma)
        if ($pid = trim((string)($this->filters['proyecto_id'] ?? ''))) {
            $pids = collect(preg_split('/\s*,\s*/', $pid, -1, PREG_SPLIT_NO_EMPTY))
                ->map(fn($v) => (int)$v)
                ->filter();
            if ($pids->isNotEmpty()) {
                $q->whereIn('tareas.proyecto_id', $pids->all());
            }
        }

        // Filtro nombre de proyecto
        if ($p = trim((string)($this->filters['proyecto'] ?? ''))) {
            $q->where('proyectos.nombre', 'like', "%{$p}%");
        }

        // Filtro asignado a (nombre o email)
        if ($a = trim((string)($this->filters['asignado'] ?? ''))) {
            $q->where(function ($w) use ($a) {
                $w->where('users.name',  'like', "%{$a}%")
                  ->orWhere('users.email','like', "%{$a}%");
            });
        }

        // Filtro descripción
        if ($d = trim((string)($this->filters['descripcion'] ?? ''))) {
            $q->where('tareas.descripcion', 'like', "%{$d}%");
        }

        // Filtro estado
        if ($e = trim((string)($this->filters['estado'] ?? ''))) {
            $q->where('tareas.estado', $e);
        }

        // Orden
        $q->orderBy($this->sortField, $this->sortDir);

        return view('livewire.disenio.administrar-tareas', [
            'tasks' => $q->paginate($this->perPage),
        ]);
    }
}

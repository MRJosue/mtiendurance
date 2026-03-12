<?php

namespace App\Livewire\Dashboard\DisenioPanel;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tarea;
use App\Models\proyecto_estados;
use Illuminate\Support\Facades\Auth;

class Tareasdisenio extends Component
{
    use WithPagination;

    public $selectedTask;
    public $newStatus;
    public $statuses = ['PENDIENTE', 'EN PROCESO', 'COMPLETADA', 'RECHAZADO', 'CANCELADO'];
    public $modalOpen = false;
    public $mostrarModalConfirmacion = false;
    public $proyectoPendienteConfirmacion = null;

    public function abrirModal($taskId)
    {
        $this->selectedTask = Tarea::find($taskId);

        if (!$this->selectedTask) {
            session()->flash('error', 'Error: Tarea no encontrada.');
            return;
        }

        $this->newStatus = $this->selectedTask->estado;
        $this->modalOpen = true;
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

    public function cerrarModal()
    {
        $this->modalOpen = false;
        $this->selectedTask = null;
        $this->newStatus = null;
    }

    public function verificarProceso($proyectoId)
    {
        $tarea = Tarea::where('proyecto_id', $proyectoId)->first();

        if ($tarea && $tarea->disenio_flag_first_proceso == 0) {
            $this->mostrarModalConfirmacion = true;
            $this->proyectoPendienteConfirmacion = $tarea->proyecto;
        } else {
            return redirect()->route('proyecto.show', $proyectoId);
        }
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

    public function cancelarConfirmacion()
    {
        $this->mostrarModalConfirmacion = false;
        $this->proyectoPendienteConfirmacion = null;
    }

    public function render()
    {
        $user = auth()->user();

        $puedeVerTodasLasTareas = $user->hasRole('admin') || $user->can('tareas-disenio-ver-todas');

        $tasks = Tarea::with([
            'proyecto',
            'proyecto.user',
            'staff',
        ]);

        if (!$puedeVerTodasLasTareas) {
            $tasks->where('staff_id', $user->id);
        }

        $tasks = $tasks->orderByDesc('id')->paginate(10);

        return view('livewire.dashboard.disenio-panel.tareasdisenio', [
            'tasks' => $tasks,
            'puedeVerTodasLasTareas' => $puedeVerTodasLasTareas,
        ]);
    }
}
<?php

namespace App\Livewire\Proyectos;

use Livewire\Component;
use App\Models\Proyecto;
use App\Models\proyecto_estados;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProjectTimeline extends Component
{
    public $proyectoId;
    public $estadoActual;

    public $mostrarModalConfirmacion = false;
    public $estadoSeleccionado = null;

    // Lista de estados en orden
    public $estados = [
        'PENDIENTE',
        'ASIGNADO',
        'EN PROCESO',
        'REVISION',
        'DISEÑO APROBADO',
    ];

    protected $listeners = ['estadoActualizado' => 'actualizarEstado'];

    public function mount($proyectoId)
    {
        $this->proyectoId = $proyectoId;
        $this->actualizarEstado();
    }

    public function actualizarEstado()
    {
        $proyecto = Proyecto::find($this->proyectoId);

        if ($proyecto) {
            $this->estadoActual = $proyecto->estado;

            if ($this->estadoActual === 'DISEÑO RECHAZADO') {
                $this->estados = [
                    'PENDIENTE',
                    'ASIGNADO',
                    'DISEÑO RECHAZADO',
                    'REVISION',
                    'DISEÑO APROBADO',
                ];
            } else {
                $this->estados = [
                    'PENDIENTE',
                    'ASIGNADO',
                    'EN PROCESO',
                    'REVISION',
                    'DISEÑO APROBADO',
                ];
            }
        }
    }

    public function getEsAdminProperty()
    {
        $user = Auth::user();

        return $user && $user->hasRole('admin');
    }

    public function seleccionarEstado($estado)
    {
        if (!$this->esAdmin) {
            return;
        }

        if (!in_array($estado, $this->estados)) {
            return;
        }

        if ($estado === $this->estadoActual) {
            return;
        }

        $this->estadoSeleccionado = $estado;
        $this->mostrarModalConfirmacion = true;
    }

    public function cancelarCambioEstado()
    {
        $this->mostrarModalConfirmacion = false;
        $this->estadoSeleccionado = null;
    }

    public function confirmarCambioEstado()
    {
        if (!$this->esAdmin || !$this->estadoSeleccionado) {
            return;
        }

        $proyecto = Proyecto::find($this->proyectoId);

        if (!$proyecto) {
            session()->flash('error', 'Proyecto no encontrado.');
            $this->cancelarCambioEstado();
            return;
        }

        DB::beginTransaction();

        try {
            $estadoAnterior = $proyecto->estado;
            $nuevoEstado = $this->estadoSeleccionado;
            $ahora = Carbon::now();

            // Cerrar último estado abierto del historial
            $ultimoEstadoAbierto = proyecto_estados::where('proyecto_id', $proyecto->id)
                ->whereNull('fecha_fin')
                ->latest('id')
                ->first();

            if ($ultimoEstadoAbierto) {
                $ultimoEstadoAbierto->update([
                    'fecha_fin' => $ahora,
                ]);
            }

            // Actualizar estado actual del proyecto
            $proyecto->estado = $nuevoEstado;
            $proyecto->save();

            // Crear nuevo registro en historial
            proyecto_estados::create([
                'proyecto_id' => $proyecto->id,
                'estado' => $nuevoEstado,
                'fecha_inicio' => $ahora,
                'fecha_fin' => null,
                'usuario_id' => Auth::id(),
                'comentario' => 'Cambio de estado manual desde timeline. Estado anterior: ' . $estadoAnterior,
                'url' => null,
                'last_uploaded_file_id' => null,
            ]);

            DB::commit();

            $this->estadoActual = $proyecto->estado;
            $this->cancelarCambioEstado();

            session()->flash('message', 'Estado actualizado correctamente y registrado en historial.');

            $this->dispatch('estadoActualizado');
        } catch (\Throwable $e) {
            DB::rollBack();

            session()->flash('error', 'Ocurrió un error al actualizar el estado.');
        }
    }

    public function render()
    {
        return view('livewire.proyectos.project-timeline');
    }
}
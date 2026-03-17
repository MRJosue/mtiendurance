<?php

namespace App\Livewire\Proyectos;

use Livewire\Component;
use App\Models\Proyecto;
use Illuminate\Support\Facades\Auth;

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

        $proyecto->estado = $this->estadoSeleccionado;
        $proyecto->save();

        $this->estadoActual = $proyecto->estado;

        $this->cancelarCambioEstado();

        session()->flash('message', 'Estado actualizado correctamente.');

        $this->dispatch('estadoActualizado');
    }

    public function render()
    {
        return view('livewire.proyectos.project-timeline');
    }
}
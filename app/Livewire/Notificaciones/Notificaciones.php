<?php

namespace App\Livewire\Notificaciones;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

class Notificaciones extends Component
{
    public $notificaciones;

    protected $listeners = ['notificacionEnviada' => 'cargarNotificaciones'];

    public function mount()
    {
        $this->cargarNotificaciones();
    }


    public function cargarNotificaciones()
    {
        // Obtiene primero las NO LEÍDAS, luego completa hasta 8 con las LEÍDAS
        $notificacionesNoLeidas = Auth::user()->notifications()
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        $remainingCount = 8 - $notificacionesNoLeidas->count();

        $notificacionesLeidas = Auth::user()->notifications()
            ->whereNotNull('read_at')
            ->orderBy('created_at', 'desc')
            ->limit($remainingCount)
            ->get();

        // Combina ambas colecciones manteniendo el orden
        $this->notificaciones = new Collection([...$notificacionesNoLeidas, ...$notificacionesLeidas]);
    }


    public function marcarComoLeida($id)
    {
        $notificacion = Auth::user()->notifications()->find($id); 
        if ($notificacion) {
            $notificacion->markAsRead();
            $this->cargarNotificaciones(); // Actualiza la lista de notificaciones
        }
    }

    public function marcarTodasComoLeidas()
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->cargarNotificaciones(); // Recargar las notificaciones
    }

    public function render()
    {
        return view('livewire.notificaciones.notificaciones');
    }
}

// return view('livewire.notificaciones.notificaciones');



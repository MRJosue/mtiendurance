<?php

namespace App\Livewire\Notificaciones;


use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NuevaNotificacion;
use Livewire\Livewire;

class EnviarNotificacion extends Component
{
    public function enviarNotificacion()
    {
        $user = Auth::user(); // Usuario autenticado
        
        if ($user) {
            $user->notify(new NuevaNotificacion("Tienes una nueva notificación en tiempo real."));
            $this->dispatch('notificacionEnviada');
        }
    }

    public function render()
    {
        return view('livewire.notificaciones.enviar-notificacion');
    }
}

// return view('livewire.notificaciones.enviar-notificacion');
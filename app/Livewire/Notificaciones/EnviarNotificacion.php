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
        $user = Auth::user();

        if (!$user) return;

        try {
            $user->notify(new NuevaNotificacion("Tienes una nueva notificación en tiempo real."));
        } catch (\Throwable $e) {
            Log::warning('Notificación enviada, pero falló broadcast (WS caído)', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }

        // ✅ Actualiza la campana/lista aunque no haya websockets
        $this->dispatch('notificacionEnviada');
    }
    public function render()
    {
        return view('livewire.notificaciones.enviar-notificacion');
    }
}

// return view('livewire.notificaciones.enviar-notificacion');
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NuevaNotificacion extends Notification
{
    use Queueable;

    public $mensaje;
    public $liga;

    public function __construct($mensaje, $liga = null)
    {
        $this->mensaje = $mensaje;
        $this->liga = $liga;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'mensaje' => $this->mensaje,
            'liga' => $this->liga, // Guarda la liga en la base de datos
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'mensaje' => $this->mensaje,
            'liga' => $this->liga, // Asegurar que se envíe la URL en tiempo real
        ]);
    }
}
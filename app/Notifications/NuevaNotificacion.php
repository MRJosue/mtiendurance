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

    public function __construct($mensaje)
    {
        $this->mensaje = $mensaje;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'mensaje' => $this->mensaje,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'mensaje' => $this->mensaje,
        ]);
    }
}
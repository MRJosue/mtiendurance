<?php

namespace App\Events;


use App\Models\MensajeChat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewChatMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $mensaje;

    public function __construct(MensajeChat $mensaje)
    {
        $this->mensaje = $mensaje;
    }

    public function broadcastOn()
    {
        return new Channel('public-chat');
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->mensaje->id,
            'mensaje' => $this->mensaje->mensaje,
            'usuario' => $this->mensaje->usuario->name,
            'fecha_envio' => $this->mensaje->fecha_envio->format('d/m/Y H:i'),
        ];
    }
}
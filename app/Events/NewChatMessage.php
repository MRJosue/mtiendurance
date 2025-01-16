<?php

namespace App\Events;

use App\Models\MensajeChat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NewChatMessage implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $mensaje;

    public function __construct(MensajeChat $mensaje)
    {
        dump('mensaje emitido');
        $this->mensaje = $mensaje;
        
    }

    public function broadcastOn()
    {

        
        return new PrivateChannel('chat.' . $this->mensaje->chat_id);
    }

    public function broadcastWith()
    {

        
        return [
            'id' => $this->mensaje->id,
            'mensaje' => $this->mensaje->mensaje,
            'usuario' => $this->mensaje->usuario->name,
            'fecha_envio' => $this->mensaje->fecha_envio->toDateTimeString(),
        ];
    }
}
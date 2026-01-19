<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
class TestReverbMessage implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public string $message, public string $sentAt)
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('reverb-test');
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
            'sentAt'  => $this->sentAt,
        ];
    }
}
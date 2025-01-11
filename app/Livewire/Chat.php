<?php

namespace App\Livewire;
namespace App\Http\Livewire;

use Livewire\Component;

class Chat extends Component
{
    public $messages = [];
    public $newMessage;

    public function sendMessage()
    {
        if (trim($this->newMessage) === '') {
            return;
        }

        $message = [
            'user' => auth()->user()->name ?? 'Guest',
            'content' => $this->newMessage,
        ];

        // Emitir evento para Socket.IO
        broadcast(new \App\Events\ChatMessage($message))->toOthers();

        $this->messages[] = $message;
        $this->newMessage = '';
    }

    public function render()
    {
        return view('livewire.chat');
    }
}

<div>
    <div id="chat-box" style="height: 300px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px;">
        @foreach ($messages as $message)
            <p><strong>{{ $message['user'] }}:</strong> {{ $message['content'] }}</p>
        @endforeach
    </div>

    <input type="text" wire:model="newMessage" wire:keydown.enter="sendMessage" placeholder="Escribe tu mensaje..." style="width: 100%; padding: 10px;">
    <button wire:click="sendMessage" style="width: 100%; padding: 10px; margin-top: 5px;">Enviar</button>
</div>

<script>
    // Auto-scroll al final del chat
    Livewire.on('messageAdded', () => {
        const chatBox = document.getElementById('chat-box');
        chatBox.scrollTop = chatBox.scrollHeight;
    });
</script>

<div wire:poll.keep-alive wire:ignore class="chat-container flex flex-col h-full">
    <!-- Lista de mensajes -->
    <div id="messages" class="chat-messages flex-grow overflow-y-scroll bg-gray-100 p-4">
        @foreach ($mensajes as $mensaje)
            <div class="chat-message my-2 p-2 rounded-lg 
                {{ $mensaje->usuario_id === auth()->id() ? 'bg-blue-200 text-right ml-auto rounded-full' : 'bg-gray-200 text-left mr-auto rounded-full' }}">
                <strong>{{ $mensaje->usuario->name }}</strong>: {{ $mensaje->mensaje }}
                <span class="text-xs text-gray-500 block">
                    {{ $mensaje->fecha_envio->format('d/m/Y H:i') }}
                </span>
            </div>
        @endforeach
    </div>

    <!-- Entrada de texto para mensajes -->
    <div class="chat-input mt-4">
        <form wire:submit.prevent="enviarMensaje" class="flex">
            <input type="text" wire:model="mensaje" 
                class="flex-grow border rounded-l px-4 py-2" 
                placeholder="Escribe tu mensaje...">
            <button type="submit" 
                class="bg-blue-500 text-white px-4 py-2 rounded-r">
                Enviar
            </button>
        </form>
        @error('mensaje') 
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <!-- Script para escuchar eventos de Laravel Echo -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            console.log('listener');
            Echo.private('chat.{{ $chatId }}')
                .listen('NewChatMessage', (e) => {
                    Livewire.emit('mensajeRecibido');
                });
        });
    </script>
</div>


<div class="chat-container flex flex-col h-full w-full min-h-0">
   
    <!-- Lista de mensajes -->
    <div id="messages" class="flex-grow overflow-y-auto bg-gray-100 p-4">
        @foreach ($mensajes as $mensaje)
            <div class="chat-message my-2 p-2 rounded-lg 
                {{ $mensaje['usuario_id'] === auth()->id() ? 'bg-blue-200 text-right ml-auto rounded-full' : 'bg-gray-200 text-left mr-auto rounded-full' }}">
                <strong>{{ $mensaje['usuario']['name'] }}</strong>: {{ $mensaje['mensaje'] }}
                <span class="text-xs text-gray-500 block">
                    {{ \Carbon\Carbon::parse($mensaje['fecha_envio'])->format('d/m/Y H:i') }}
                </span>
            </div>
        @endforeach
    </div>

    <!-- Entrada de texto -->
    <div class="border-t pt-2 bg-white dark:bg-gray-800 flex-none">
        <form wire:submit.prevent="enviarMensaje" class="flex">
            <input type="text" wire:model="mensaje"
                   class="flex-grow border rounded-l px-4 py-2"
                   placeholder="Escribe tu mensaje…">
            <button type="submit"
                    class="bg-blue-500 text-white px-4 py-2 rounded-r">
                Enviar
            </button>
        </form>
        @error('mensaje')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>

    @push('scripts')
    {{-- <script>
        document.addEventListener('DOMContentLoaded', () => {
            const scrollToBottom = () => {
                const box = document.getElementById('messages');
                if (box) box.scrollTop = box.scrollHeight;
            };

            window.Echo.channel('public-chat')
                .listen('NewChatMessage', () => Livewire.dispatch('actualizarMensajes'));

            Livewire.on('actualizarMensajes', () => setTimeout(scrollToBottom, 100));

            scrollToBottom();
        });
    </script> --}}
    @endpush
</div>

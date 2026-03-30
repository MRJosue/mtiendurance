
<div class="chat-container flex flex-col h-full w-full min-h-0 rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
   
    <!-- Lista de mensajes -->
    <div id="messages" class="flex-grow overflow-y-auto bg-gray-50 p-4 max-h-[30vh] dark:bg-gray-950/60">
        @foreach ($mensajes as $mensaje)
            <div class="chat-message my-2 max-w-[85%] px-4 py-3 shadow-sm
                {{ $mensaje['usuario_id'] === auth()->id()
                    ? 'ml-auto rounded-2xl rounded-br-md bg-blue-600 text-right text-white'
                    : 'mr-auto rounded-2xl rounded-bl-md bg-white text-left text-gray-800 dark:bg-gray-800 dark:text-gray-100' }}">
                <div class="text-xs font-semibold {{ $mensaje['usuario_id'] === auth()->id() ? 'text-blue-100' : 'text-gray-500 dark:text-gray-400' }}">
                    {{ $mensaje['usuario']['name'] }}
                </div>
                <div class="mt-1 text-sm leading-5">
                    {{ $mensaje['mensaje'] }}
                </div>
                <span class="mt-2 text-xs block {{ $mensaje['usuario_id'] === auth()->id() ? 'text-blue-100/90' : 'text-gray-500 dark:text-gray-400' }}">
                    {{ \Carbon\Carbon::parse($mensaje['fecha_envio'])->format('d/m/Y H:i') }}
                </span>
            </div>
        @endforeach
    </div>

    <!-- Entrada de texto -->
    <div class="border-t border-gray-200 p-3 bg-white dark:border-gray-700 dark:bg-gray-900 flex-none">
        <form wire:submit.prevent="enviarMensaje" class="flex gap-2">
            <input type="text" wire:model="mensaje"
                   class="flex-grow border border-gray-300 rounded-xl px-4 py-2.5 bg-white text-gray-700 placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:placeholder:text-gray-500"
                   placeholder="Escribe tu mensaje…">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-medium dark:bg-blue-500 dark:hover:bg-blue-400">
                Enviar
            </button>
        </form>
        @error('mensaje')
            <span class="mt-2 inline-block text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>

    @push('scripts')
    <script>
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
    </script>
    @endpush
</div>

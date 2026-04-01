<div wire:poll.3s="actualizarMensajes" class="chat-container relative flex h-full w-full min-h-0 flex-col rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
    <!-- Lista de mensajes -->
    <div id="messages-proveedor" class="flex-grow overflow-y-auto bg-gray-50 p-4 max-h-[30vh] dark:bg-gray-950/60">
        @foreach ($mensajes as $mensaje)
            @php
                $isOwnMessage = $mensaje['usuario_id'] === auth()->id();
                $isSystemMessage = empty($mensaje['usuario_id']);
                $messageClasses = $isSystemMessage
                    ? 'mx-auto rounded-2xl border border-amber-200 bg-amber-50 text-center text-amber-900 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-100'
                    : ($isOwnMessage
                        ? 'ml-auto rounded-2xl rounded-br-md bg-blue-600 text-right text-white'
                        : 'mr-auto rounded-2xl rounded-bl-md border border-emerald-200 bg-emerald-50 text-left text-emerald-950 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-100');
                $metaClasses = $isSystemMessage
                    ? 'text-amber-700 dark:text-amber-200/90'
                    : ($isOwnMessage ? 'text-blue-100' : 'text-emerald-700 dark:text-emerald-200/80');
                $timeClasses = $isSystemMessage
                    ? 'text-amber-700/90 dark:text-amber-200/80'
                    : ($isOwnMessage ? 'text-blue-100/90' : 'text-emerald-700/80 dark:text-emerald-200/70');
            @endphp

            <div
                wire:key="chat-proveedor-message-{{ $mensaje['id'] }}"
                class="chat-message my-2 max-w-[85%] px-4 py-3 shadow-sm {{ $messageClasses }}"
            >
                <div class="text-xs font-semibold {{ $metaClasses }}">
                    {{ $isSystemMessage ? 'Sistema' : ($mensaje['usuario']['name'] ?? 'Usuario') }}
                </div>
                <div class="mt-1 text-sm leading-5">
                    {{ $mensaje['mensaje'] }}
                </div>

                <span class="mt-2 block text-xs {{ $timeClasses }}">
                    {{ \Carbon\Carbon::parse($mensaje['fecha_envio'])->format('d/m/Y H:i') }}
                </span>
            </div>
        @endforeach
    </div>

    <button
        id="scroll-to-bottom-chat-proveedor"
        type="button"
        class="absolute bottom-24 right-4 hidden h-11 w-11 items-center justify-center rounded-full bg-blue-600 text-xl text-white shadow-lg transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 dark:bg-blue-500 dark:hover:bg-blue-400"
        aria-label="Ir al último mensaje"
        title="Ir al último mensaje"
    >
        ↓
    </button>

    <!-- Entrada de texto -->
    <div class="border-t border-gray-200 p-3 bg-white dark:border-gray-700 dark:bg-gray-900 flex-none">
        <form wire:submit.prevent="enviarMensaje" class="flex gap-2">
            <input
                type="text"
                wire:model="mensaje"
                class="flex-grow border border-gray-300 rounded-xl px-4 py-2.5 bg-white text-gray-700 placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:placeholder:text-gray-500"
                placeholder="Escribe tu mensaje…"
            >
            <button
                type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-medium dark:bg-blue-500 dark:hover:bg-blue-400"
            >
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
            const box = document.getElementById('messages-proveedor');
            const scrollButton = document.getElementById('scroll-to-bottom-chat-proveedor');

            if (!box || !scrollButton) return;

            const toggleScrollButtonProveedor = () => {
                const distanceToBottom = box.scrollHeight - box.scrollTop - box.clientHeight;
                const shouldShow = distanceToBottom > 24;

                scrollButton.classList.toggle('hidden', !shouldShow);
                scrollButton.classList.toggle('flex', shouldShow);
            };

            const scrollToBottomProveedor = () => {
                box.scrollTop = box.scrollHeight;
                toggleScrollButtonProveedor();
            };

            scrollToBottomProveedor();

            box.addEventListener('scroll', toggleScrollButtonProveedor);
            scrollButton.addEventListener('click', scrollToBottomProveedor);

            document.addEventListener('livewire:navigated', () => {
                setTimeout(scrollToBottomProveedor, 100);
            });

            if (window.Livewire?.hook) {
                Livewire.hook('morph.updated', ({ el }) => {
                    if (el.id === 'messages-proveedor') {
                        setTimeout(toggleScrollButtonProveedor, 50);
                    }
                });
            }
        });
    </script>
    @endpush
</div>

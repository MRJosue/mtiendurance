<div class="chat-container flex flex-col h-full" style="height: 500px;">
    <!-- Lista de mensajes -->
    <div id="messages" class="chat-messages flex-grow overflow-y-scroll bg-gray-100 p-4">
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

    @push('scripts')
    <script>
document.addEventListener('DOMContentLoaded', function () {
    const scrollToBottom = () => {
        const messagesContainer = document.getElementById('messages');
        if (messagesContainer) {
            const totalScroll = messagesContainer.scrollHeight - messagesContainer.scrollTop;
            const interval = 50; // Tiempo entre cada paso (ms)
            const steps = 60; // Dividimos el movimiento en 60 pasos para suavizar
            const stepSize = totalScroll / steps;
            let currentStep = 0;

            const smoothScroll = setInterval(() => {
                if (currentStep < steps) {
                    messagesContainer.scrollTop += stepSize;
                    currentStep++;
                } else {
                    clearInterval(smoothScroll); // Detenemos el intervalo una vez completado
                }
            }, interval);
        }
    };

    window.Echo.channel('public-chat')
        .listen('NewChatMessage', (e) => {
            
            Livewire.dispatch('actualizarMensajes');
            setTimeout(() => {
                scrollToBottom(); // Ejecutar el desplazamiento después de 3 segundos
            }, 1000);
        });

    // Listener de Livewire para actualizar la vista
    Livewire.on('actualizarMensajes', () => {
        console.log('Actualiza b ');
        setTimeout(() => {
            scrollToBottom(); // Ejecutar el desplazamiento después de 3 segundos
        }, 1000);
    });

    scrollToBottom(); 
});
    </script>
    @endpush
</div>

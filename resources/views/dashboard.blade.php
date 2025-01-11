<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}

                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Probar si Laravel Echo está inicializado
        console.log('Echo:', window.Echo);
    
        if (window.Echo) {
            // Escuchar el canal y evento
            window.Echo.channel('test-channel')
                .listen('.test-event', (e) => {
                    console.log('Evento recibido:', e);
                });
        } else {
            console.error('Laravel Echo no está inicializado.');
        }
    
        // Función para emitir el evento con AJAX
        function emitirEventoAjax() {
            $.ajax({
                url: '/emit-event',
                method: 'POST',
                data: {
                    message: 'Hola desde AJAX',
                    _token: '{{ csrf_token() }}', // Incluye el token CSRF para solicitudes POST
                },
                success: function (response) {
                    console.log('Respuesta del servidor:', response);
                },
                error: function (xhr, status, error) {
                    console.error('Error al emitir el evento:', error);
                }
            });
        }
    
        // Llamar a la función después de 2 segundos como prueba
        setTimeout(emitirEventoAjax, 2000);
    </script>
    
</x-app-layout>

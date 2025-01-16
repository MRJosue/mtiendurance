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

    @push('scripts')
    <script>
        // document.addEventListener('DOMContentLoaded', () => {
        // // Escuchar un canal público
        // console.log('Evento',window.Echo);
        // window.Echo.channel('test-channel')
        //     .listen('.test-event', (e) => {
        //         console.log('/////E:',e);
        //         console.log('Evento recibido:', e.message);
        //     })
        //     .on('subscribed', () => {
        //         console.log('Conectado correctamente a test-channel');
        //     })
        //     .error((error) => {
        //         console.error('Error en la conexión al canal:', error);
        //     });
        // });
    </script>
    @endpush
</x-app-layout>

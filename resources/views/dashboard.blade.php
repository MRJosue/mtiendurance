<x-app-layout>
    <x-slot name="header" class="pl-64">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">

        
        @hasanyrole('admin|cliente')
            <div class="w-full px-4 sm:px-6 lg:px-20 xl:px-32 mx-auto">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        @livewire('dashboard.cliente-panel')
                    </div>
                </div>
                
            </div>
        @endhasanyrole


        {{-- @hasanyrole('admin|proveedor')
            <div class="w-full px-4 sm:px-6 lg:px-20 xl:px-32 mx-auto">

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        @livewire('dashboard.proveedor-panel')
                    </div>
                </div>
                    
            </div>
        @endhasanyrole --}}


        @hasanyrole('admin|estaf')
        <div class="w-full px-4 sm:px-6 lg:px-20 xl:px-32 mx-auto">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- @livewire('dashboard.estaf-panel') --}}
                </div>
            </div>
        </div>
        @endhasanyrole


        @hasanyrole('admin|jefediseñador')
        <div class="w-full px-4 sm:px-6 lg:px-20 xl:px-32 mx-auto">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- resources\views\livewire\dashboard\jefe-disenador-panel.blade.php --}}
                    {{-- resources/views/livewire/dashboard/jefe-disenador-panel.blade.php --}}
                    @livewire('dashboard.jefe-disenador-panel')
                </div>
            </div>
        </div>
        @endhasanyrole
      
        @hasanyrole('admin|diseñador')
        <div class="w-full px-4 sm:px-6 lg:px-20 xl:px-32 mx-auto">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @livewire('dashboard.disenador-panel')
                </div>
            </div>
        </div>
        @endhasanyrole
      

        @hasanyrole('admin|operador')
        <div class="w-full px-4 sm:px-6 lg:px-20 xl:px-32 mx-auto">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- @livewire('dashboard.operador-panel') --}}
                </div>
            </div>
        </div>
        @endhasanyrole
      
      

        {{-- <div class="w-full px-4 sm:px-6 lg:px-20 xl:px-32 mx-auto">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
            
                    @livewire('client-message')
                </div>
            </div>
        </div> --}}

    </div>



    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Echo.private('test-channel')
            .listen('SomeEvent', (e) => {
                console.log('Recibido:', e);
            });

        });

    </script>
    @endpush
</x-app-layout>

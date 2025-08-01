<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tareas') }}/{{ __('Produccion') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-2xl font-bold mb-4">Lista de Ordenes de costura</h1>
                   
                   
                   
                    {{-- @livewire('produccion.tarea-produccion-crud') --}}
                    {{-- @livewire('produccion.ordenes-produccion-crud') --}}
                    @livewire('produccion.ordenes-produccion-crud', ['tipo' => 'COSTURA'])    



                </div>

                <div class="p-6 text-gray-900 dark:text-gray-100">

                </div>
            </div>
        </div>
    </div>

    
</x-app-layout>

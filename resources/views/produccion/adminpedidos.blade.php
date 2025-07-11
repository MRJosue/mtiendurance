<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Administracion de Pedidos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-2xl font-bold mb-4">Lista de Pedidos</h1>
                    {{-- @livewire('programacion.calendario-pedidos') --}}
                    {{-- @livewire('programacion.pedidos-crud-general') --}}
                    @livewire('produccion.administra-pedidos-crud')



                </div>

                <div class="p-6 text-gray-900 dark:text-gray-100">

                </div>
            </div>
        </div>
    </div>

    
</x-app-layout>

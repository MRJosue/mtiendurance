<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pre proyectos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto px-4 sm:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl min-h-[85vh]">
                <div class="p-8 text-gray-900 dark:text-gray-100">
                    @livewire('preproyectos.edit-pre-project', ['preProyectoId' => $preproyecto->id])
                </div>
            </div>
        </div>
    </div>


</x-app-layout>

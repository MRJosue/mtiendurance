<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Solicitudes de Proyectos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="dashboard-shell max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="dashboard-panel">
                <div class="dashboard-panel__body">


                    @livewire('preproyectos.manage-preprojects')


                </div>
            </div>
        </div>
    </div>

    

</x-app-layout>

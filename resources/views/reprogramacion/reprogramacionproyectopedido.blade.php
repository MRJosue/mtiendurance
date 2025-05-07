<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Programacion') }}/{{ __('Reprogramacion') }}/ID:{{$proyecto->id}} Nombre: {{$proyecto->nombre}}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-2xl font-bold mb-4">Reprograma el proyecto</h1>


                    @php
                        $className = 'App\\Livewire\\Reprogramacion\\EditProject';
                        $classExists = class_exists($className);
                        $bladePath = resource_path('views/livewire/reprogramacion/edit-project.blade.php');
                        $bladeExists = file_exists($bladePath);
                    @endphp
                    
                    <div class="p-4 mb-4 rounded {{ $classExists && $bladeExists ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        <strong>Diagnóstico Livewire:</strong><br>
                        Clase <code>{{ $className }}</code>: 
                        {{ $classExists ? '✅ Cargada correctamente' : '❌ No encontrada' }}<br>
                        Vista Blade <code>edit-project.blade.php</code>: 
                        {{ $bladeExists ? '✅ Existe en /resources/views/livewire/reprogramacion' : '❌ No encontrada' }}
                    </div>
                    
                    @livewire('reprogramacion.edit-project', ['ProyectoId' => $proyecto->id])
                    {{-- <livewire:reprogramacion.edit-project :ProyectoId="$proyecto->id" /> --}}

                </div>

                <div class="p-6 text-gray-900 dark:text-gray-100">

                </div>
            </div>
        </div>
    </div>

    
</x-app-layout>
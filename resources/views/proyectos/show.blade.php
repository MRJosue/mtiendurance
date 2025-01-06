<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Proyecto/Detalles/')}}{{$proyecto->nombre}}
        </h2>
    </x-slot>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <livewire:proyectos.project-timeline :estado-actual="$proyecto->estado" />
                </div>
            </div>
        </div>
    </div>



    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <!-- Encabezado -->
                            <h2 class="text-2xl font-bold mb-4">Detalles del Proyecto</h2>

                            <!-- Información General -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-lg">
                                        <span class="font-semibold">ID del Proyecto:</span> {{ $proyecto->id }}
                                    </p>
                                </div>

                                <div class="sm:col-span-2">
                                    <p class="text-lg">
                                        <span class="font-semibold">Nombre:</span> {{ $proyecto->nombre }}
                                    </p>
                                </div>
                                <div class="sm:col-span-2">
                                    <p class="text-lg">
                                        <span class="font-semibold">Descripción:</span> {{ $proyecto->descripcion }}
                                    </p>
                                </div>
                            </div>

                            <!-- Fechas -->
                            <div class="mt-6">
                                <h3 class="text-xl font-bold mb-2">Fechas</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-lg">
                                            <span class="font-semibold">Fecha de Creación:</span> {{ $proyecto->fecha_creacion }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-lg">
                                            <span class="font-semibold">Fecha de Entrega:</span>
                                            @if ($proyecto->fecha_entrega)
                                                {{ $proyecto->fecha_entrega }}
                                            @else
                                                <span class="text-gray-500">No definida</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <h1>Datos de pedido</h1>

                    @livewire('pedidos.mostrar-pedidos-proyecto', ['proyectoId' => $proyecto->id])


                </div>
            </div>

        </div>
    </div>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1>Chat</h1>
                </div>
            </div>
        </div>
    </div>



    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1>Archivos</h1>
                </div>
            </div>
        </div>
    </div>



    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1>Tareas</h1>
                </div>
            </div>
        </div>
    </div>






</x-app-layout>

<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Proyecto/Detalles/')}}{{$proyecto->nombre}}
        </h2>
    </x-slot>


    <div class="py-3">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- <livewire:proyectos.project-timeline :estado-actual="$proyecto->estado" /> --}}
                        <livewire:proyectos.project-timeline :proyecto-id="$proyecto->id" />
                </div>
            </div>
        </div>
    </div>



    <div class="py-3">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <livewire:proyectos.ultimo-archivo :proyecto-id="$proyecto->id" />
                                </div>
                                <div>
                                    <div class="p-6 text-gray-900 dark:text-gray-100">
                                        <!-- Encabezado -->
                                        <h2 class="text-2xl font-bold mb-4">Detalles del Proyecto</h2>
            
                                        <!-- Información General -->
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-lg">
                                                    {{-- <span class="font-semibold">ID del Proyecto:</span> {{ $proyecto->id }} --}}
                                                    <span class="font-semibold">Nombre:</span> {{ $proyecto->nombre }}
                                                </p>
                                            </div>
            
            
                                            <div class="sm:col-span-2">
                                                <p class="text-lg">
                                                    <span class="font-semibold">Descripción:</span> {{ $proyecto->descripcion }}
                                                </p>
                                            </div>
            
                                            <div class="sm:col-span-2">
                                                <p class="text-lg">
                                                    <span class="font-semibold">Direccion de etrega:</span>
                                                    <br>
                                                    {{$proyecto->direccion_entrega}}
                                                </p>
                                            </div>
                                        </div>
            
                                        
            
                                        <!-- Fechas -->
                                        <div class="mt-6">
                                            <h3 class="text-xl font-bold mb-2">Fechas</h3>
                                            <div class="grid grid-cols-1  gap-4">
                                                {{-- <div>
                                                    <p class="text-lg">
                                                        <span class="font-semibold">Fecha de Creación:</span>
                                                        {{ \Carbon\Carbon::parse($proyecto->fecha_creacion)->format('d-m-Y') }}
                                                    </p>
                                                </div> --}}
                                                <div>
                                                    <p class="text-lg">
                                                        <span class="font-semibold">Produccion:</span>
                                                        @if ($proyecto->fecha_produccion)
                                                            {{ \Carbon\Carbon::parse($proyecto->fecha_produccion)->format('d-m-Y') }}
                                                        @else
                                                            <span class="text-gray-500">No definida</span>
                                                        @endif
                                                    </p>
                                                </div>
                                                <div>
                                                    <p class="text-lg">
                                                        <span class="font-semibold">Fecha de Embarque:</span>
                                                        @if ($proyecto->fecha_embarque)
                                                        {{ \Carbon\Carbon::parse($proyecto->fecha_embarque)->format('d-m-Y') }}
                                                        @else
                                                            <span class="text-gray-500">No definida</span>
                                                        @endif
                                                    </p>
                                                </div>
            
                                                <div>
                                                    <p class="text-lg">
                                                        <span class="font-semibold">Fecha de Entrega:</span>
                                                        @if ($proyecto->fecha_entrega)
            
                                                        {{ \Carbon\Carbon::parse($proyecto->fecha_entrega)->format('d-m-Y') }}
                                                        @else
                                                            <span class="text-gray-500">No definida</span>
                                                        @endif
                                                    </p>
                                                </div>
            
            
                                                <div>
                                                    <livewire:pedidos.orden-produccion-pdf :proyectoId="$proyecto->id" />

                                                </div>
                                                
                                              
                                            </div>
                                        </div>
            
            
            
            
                                    </div>
                                </div>
                            </div>


                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">


                    @livewire('pedidos.mostrar-pedidos-proyecto', ['proyectoId' => $proyecto->id])


                </div>
                
            </div>



            <div class=" ">
                <livewire:proyectos.control-estado :proyecto-id="$proyecto->id" />
            </div>

        </div>
    </div>




    <div class="py-3">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h2 class="text-2xl font-bold mb-4">Chat del proyecto</h2>
                    <div wire:poll.2s class="chat-container">
                        <!-- Chat content -->
                        <livewire:chat-component :proyecto-id="$proyecto->id" />
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="py-3">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                 
                    <livewire:proyectos.project-files :proyecto-id="$proyecto->id" />
                </div>
            </div>
        </div>
    </div>



    <div class="py-3">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
           

                    <livewire:proyectos.tareas-manager proyecto-id="{{ $proyecto->id }}" />
                </div>
            </div>
        </div>
    </div>






</x-app-layout>

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
            
                                        </div>

                                        @php

                                            $categoria = is_array($proyecto->categoria_sel) 
                                                ? $proyecto->categoria_sel 
                                                : json_decode($proyecto->categoria_sel, true);
                                        @endphp


                                    <div class="grid grid-cols-1 sm:grid-cols-1 gap-4 mb-4  ">
                                        <div class="sm:col-span-2">
                                            <span class="text-lg font-semibold">Categoria:</span> <span class="font-medium">{{ $categoria['nombre'] ?? 'Sin categoría' }}</span>
                                        </div>
                                    </div>

                                    
                                        @php
                                        $producto = is_array($proyecto->producto_sel) 
                                            ? $proyecto->producto_sel 
                                            : json_decode($proyecto->producto_sel, true);
                                        @endphp
                                    
                                    <div class="grid grid-cols-1 sm:grid-cols-1 gap-4 mb-4  ">
                                        <div class="sm:col-span-2">
                                            <span class="text-lg font-semibold">Producto:</span> <span class="font-medium">{{ $producto['id'].' '.$producto['nombre'] ?? 'Sin producto' }}</span>
                                    </div>


                                
                                    </div>

                                        @php
                                        $caracteristicas = is_array($proyecto->caracteristicas_sel) 
                                            ? $proyecto->caracteristicas_sel 
                                            : json_decode($proyecto->caracteristicas_sel, true);
                                          @endphp
                                    
                                    <div class="grid grid-cols-1 sm:grid-cols-1 gap-4">
                                        @foreach($caracteristicas as $caracteristica)
                                            <div class="p-4 border rounded-lg shadow">
                                                <h3 class="text-lg font-semibold">{{ $caracteristica['nombre'] }}</h3>
                                                <ul class="mt-2 list-disc list-inside">
                                                    @foreach($caracteristica['opciones'] as $opcion)
                                                        <li>
                                                            <span class="font-medium">{{ $opcion['nombre'] }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endforeach
                                    </div>
            
                                    </div>
                                </div>
                            </div>


                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">


                    {{-- @livewire('pedidos.mostrar-pedidos-proyecto', ['proyectoId' => $proyecto->id]) --}}
                    @livewire('pedidos.pedidos-crud-proyecto', ['proyectoId' => $proyecto->id])



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

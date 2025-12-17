<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex-1">
                {{ __('Proyecto/Detalles/') }}{{ $proyecto->nombre }} ID:{{ $proyecto->id }}
            </h2>
            <div class="shrink-0">
                <livewire:proyectos.project-timeline :proyecto-id="$proyecto->id" />
            </div>
        </div>
    </x-slot>
    <div class="py-6">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full min-h-0">
                <div class="p-6 text-gray-900 dark:text-gray-100 h-full min-h-0">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 h-full min-h-0">
                        
                        <!-- Lado izquierdo: Último archivo y Subir Diseño centrados -->
                        <div class="flex flex-col items-center justify-center p-1 space-y-4">
                            <!-- Componente que muestra el último archivo -->
                            <livewire:proyectos.ultimo-archivo :proyecto-id="$proyecto->id" />
                            <!-- Componente para subir diseño -->
                            
                            <livewire:proyectos.subir-diseno :proyecto-id="$proyecto->id" />


                            <livewire:proyectos.resume-estado :proyecto-id="$proyecto->id" />
                                


                        </div>
                        <!-- Lado derecho: pestañas Detalles / Chat -->
                        <div x-data="{ tab: 'detalles' }" class="text-gray-900 dark:text-gray-100">
                            <!-- Tabs -->
                            <div class="flex overflow-x-auto border-b border-gray-300 dark:border-gray-600 mb-4 space-x-4">
                                <button 
                                    @click="tab = 'detalles'" 
                                    :class="tab === 'detalles' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-blue-600'" 
                                    class="py-2 px-4 font-medium whitespace-nowrap focus:outline-none"
                                >
                                    Detalles del Proyecto
                                </button>



                                <button 
                                    @click="tab = 'chat'" 
                                    :class="tab === 'chat' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-blue-600'" 
                                    class="py-2 px-4 font-medium whitespace-nowrap focus:outline-none"
                                >
                                    Chat del Proyecto
                                </button>

                                @can('chatProveedor')
                                    <button 
                                        @click="tab = 'chatProveedor'" 
                                        :class="tab === 'chatProveedor' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-blue-600'" 
                                        class="py-2 px-4 font-medium whitespace-nowrap focus:outline-none"
                                    >
                                        Chat del Proveedor
                                    </button>
                                @endcan


                                @can('proyectodiseñopestañaTareasDelProyecto')
                                    <button 
                                        @click="tab = 'tareas'" 
                                        :class="tab === 'tareas' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-blue-600'" 
                                        class="py-2 px-4 font-medium whitespace-nowrap focus:outline-none"
                                    >
                                        Tareas del Diseño
                                    </button>
                                @endcan

                                    <button 
                                        @click="tab = 'transferencia'" 
                                        :class="tab === 'transferencia' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-blue-600'" 
                                        class="py-2 px-4 font-medium whitespace-nowrap focus:outline-none"
                                    >
                                        transferencia
                                    </button>
                            </div>
                            <!-- Contenido Detalles -->
                            <div x-show="tab === 'detalles'" x-cloak
                            x-data="{ sub: 'info' }"
                            class="flex flex-col h-full min-h-0 overflow-y-auto">


                                    
                                            <h2 class="text-2xl font-bold mb-4">Detalles del Proyecto</h2>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-3">
                                                <div>
                                                    <div class="text-lg">
                                                        <span class="font-semibold">ID:</span>{{ $proyecto->id }} 
                                                    </div>
                                                    <div class="text-lg">
                                                        <span class="font-semibold">Nombre de Proyecto:</span> {{ $proyecto->nombre }} 
                                                    </div>

                                                </div>
                                                <div>
                                                    <p class="text-lg">
                                                        <span class="font-semibold">Cliente  : </span> {{ $proyecto->user->name }}
                                                    </p>

                                                    <p class="text-lg">
                                                        <span class="font-semibold">Empresa:</span>
                                                        <span
                                                            class="inline-flex items-center gap-1 align-middle"
                                                            title="{{ $proyecto->user->tooltip_sucursal_empresa }}"
                                                        >
                                                            {{ $proyecto->user->sucursal_nombre ?? 'Sin empresa' }}

                                                            <!-- Icono info (opcional, solo visual) -->
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 opacity-70" fill="none" viewBox="0 0 24 24"
                                                                stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M11.25 11.25l.041-.02a.75.75 0 011.06.69v3.83m0-8.25h.008v.008H12V7.5zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                        </span>
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
                                                $producto = is_array($proyecto->producto_sel)
                                                    ? $proyecto->producto_sel
                                                    : json_decode($proyecto->producto_sel, true);
                                                $caracteristicas = is_array($proyecto->caracteristicas_sel)
                                                    ? $proyecto->caracteristicas_sel
                                                    : json_decode($proyecto->caracteristicas_sel, true);
                                            @endphp
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-3">
                                                    <div>
                                                        <p class="text-lg font-semibold">Categoría:</p>
                                                        <p>{{ $categoria['nombre'] ?? 'Sin categoría' }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-lg font-semibold">Producto:</p>
                                                        <p>{{ $producto['nombre'] ?? 'Sin producto' }}</p>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-3">
                                                    @forelse(($caracteristicas ?? []) as $caracteristica)
                                                        @php
                                                            // Asegura estructura esperada
                                                            $caracteristica = is_array($caracteristica) ? $caracteristica : (array) $caracteristica;
                                                            $opciones = $caracteristica['opciones'] ?? [];
                                                            if (!is_array($opciones)) {
                                                                $opciones = (array) $opciones;
                                                            }
                                                        @endphp
                                                        <div class="p-4 border rounded-lg shadow bg-gray-50 dark:bg-gray-700">
                                                            <h3 class="text-lg font-semibold">{{ $caracteristica['nombre'] ?? 'Sin nombre' }}</h3>

                                                            @if(!empty($opciones))
                                                                <ul class="mt-2 list-disc list-inside">
                                                                    @foreach($opciones as $opcion)
                                                                        @php $opcion = is_array($opcion) ? $opcion : (array) $opcion; @endphp
                                                                        <li><span class="font-medium">{{ $opcion['nombre'] ?? '—' }}</span></li>
                                                                    @endforeach
                                                                </ul>
                                                            @else
                                                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Sin opciones</p>
                                                            @endif
                                                        </div>
                                                    @empty
                                                        <div class="sm:col-span-2 lg:col-span-3">
                                                            <p class="text-sm text-gray-600 dark:text-gray-300">Sin características seleccionadas.</p>
                                                        </div>
                                                    @endforelse


                                                </div>
                            </div>
                            <!-- Contenido Chat -->
                            <div x-show="tab === 'chat'" x-cloak class="flex flex-col flex-1 min-h-0 p-5">
                                <h2 class="text-2xl font-bold mb-4">Chat del Proyecto</h2>
                                <div wire:poll.2s class="flex-1 min-h-0 flex flex-col overflow-y-auto bg-white dark:bg-gray-700 rounded-lg shadow-md p-4">
                                    <livewire:chat-component :proyecto-id="$proyecto->id" />
                                </div>
                            </div>

                            <!-- Contenido Chat -->
                            <div x-show="tab === 'chatProveedor'" x-cloak class="flex flex-col flex-1 min-h-0 p-5">
                                <h2 class="text-2xl font-bold mb-4">Chat del Proyecto</h2>
                                <div wire:poll.2s class="flex-1 min-h-0 flex flex-col overflow-y-auto bg-white dark:bg-gray-700 rounded-lg shadow-md p-4">
                                    <livewire:chat-proveedor-component :proyecto-id="$proyecto->id" />
                                </div>
                            </div>
                            
                            <!-- Contenido Tareas -->
                            @can('proyectodiseñopestañaTareasDelProyecto')
                                <div x-show="tab === 'tareas'" x-cloak class="flex flex-col flex-1 min-h-0 overflow-y-auto p-5">
                                    <h2 class="text-2xl font-bold mb-4">Tareas del Diseño</h2>
                                    <livewire:proyectos.tareas-diseno :proyecto-id="$proyecto->id" />
                                </div>
                            @endcan


                            <div x-show="tab === 'transferencia'" x-cloak class="flex flex-col flex-1 min-h-0 p-5">
                                    <h2 class="text-2xl font-bold mb-4">Transferencias</h2>
                                    <div wire:poll.2s class="flex-1 min-h-0 flex flex-col overflow-y-auto bg-white dark:bg-gray-700 rounded-lg shadow-md p-4">
                                  
                                 <livewire:proyectos.transferencia-proyecto :proyecto="$proyecto" wire:key="transfer-{{ $proyecto->id }}" />

                                </div>
                            </div>
                            

                        </div>
                    </div>
                </div>


                @can('vistaproyectoSeccionPedidos')
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mt-6">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <div x-data="{ tab: 'pedidos' }">
                                <!-- Pestañas -->
                                <div class="flex space-x-4 border-b border-gray-300 dark:border-gray-600">
                                    <button 
                                        @click="tab = 'pedidos'" 
                                        :class="tab === 'pedidos' ? 'border-blue-500 text-blue-500' : 'text-gray-500 dark:text-gray-300'" 
                                        class="py-2 px-4 font-semibold border-b-2 focus:outline-none"
                                    >
                                        Pedidos
                                    </button>
                                    <button 
                                        @click="tab = 'muestras'" 
                                        :class="tab === 'muestras' ? 'border-blue-500 text-blue-500' : 'text-gray-500 dark:text-gray-300'" 
                                        class="py-2 px-4 font-semibold border-b-2 focus:outline-none"
                                    >
                                        Muestras
                                    </button>
                                </div>
                                <!-- Contenido de las pestañas -->
                                <div class="mt-4">
                                    <div x-show="tab === 'pedidos'">
                                        @livewire('pedidos.pedidos-crud-proyecto', ['proyectoId' => $proyecto->id ])
                                    </div>
                                    <div x-show="tab === 'muestras'">
                                        @livewire('pedidos.muestras-crud-proyecto', ['proyectoId' => $proyecto->id])
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
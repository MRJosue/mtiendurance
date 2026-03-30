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
        <div class="project-detail-shell project-show-shell">
            <div class="project-detail-panel h-full min-h-0">
                <div class="project-detail-body h-full min-h-0">
                    <div class="project-show-main-grid">
                        
                        <!-- Lado izquierdo: Último archivo y Subir Diseño centrados -->
                        <div class="project-show-sidebar">
                            <!-- Componente que muestra el último archivo -->
                            <livewire:proyectos.ultimo-archivo :proyecto-id="$proyecto->id" />
                            <!-- Componente para subir diseño -->
                            
                            <livewire:proyectos.subir-diseno :proyecto-id="$proyecto->id" />


                            <livewire:proyectos.resume-estado :proyecto-id="$proyecto->id" />
                                


                        </div>
                        <!-- Lado derecho: pestañas Detalles / Chat -->
                        <div x-data="{ tab: 'detalles' }" class="project-show-content text-gray-900 dark:text-gray-100">
                            <!-- Tabs -->
                            <div class="project-tab-list project-show-tab-list">
                                <button 
                                    @click="tab = 'detalles'" 
                                    :class="tab === 'detalles' ? 'project-tab-button project-tab-button--active' : 'project-tab-button project-tab-button--inactive'" 
                                >
                                    Detalles del Proyecto
                                </button>



                                <button 
                                    @click="tab = 'chat'" 
                                    :class="tab === 'chat' ? 'project-tab-button project-tab-button--active' : 'project-tab-button project-tab-button--inactive'" 
                                >
                                    Chat del Proyecto
                                </button>

                                @can('proyectodiseñopestañachatProveedor')
                                    <button 
                                        @click="tab = 'chatProveedor'" 
                                        :class="tab === 'chatProveedor' ? 'project-tab-button project-tab-button--active' : 'project-tab-button project-tab-button--inactive'" 
                                    >
                                        Chat del Proveedor
                                    </button>
                                @endcan


                                @can('proyectodiseñopestañaTareasDelProyecto')
                                    <button 
                                        @click="tab = 'tareas'" 
                                        :class="tab === 'tareas' ? 'project-tab-button project-tab-button--active' : 'project-tab-button project-tab-button--inactive'" 
                                    >
                                        Tareas del Diseño
                                    </button>
                                @endcan

                                    <button 
                                        @click="tab = 'transferencia'" 
                                        :class="tab === 'transferencia' ? 'project-tab-button project-tab-button--active' : 'project-tab-button project-tab-button--inactive'" 
                                    >
                                        transferencia
                                    </button>
                            </div>
                            <!-- Contenido Detalles -->
                            <div x-show="tab === 'detalles'" x-cloak
                            x-data="{ sub: 'info' }"
                            class="project-show-tab-panel flex flex-col h-full min-h-0 overflow-y-auto">


                                    
                                            <h2 class="project-section-title">Detalles del Proyecto</h2>
                                            <div class="project-show-info-grid">
                                                <div class="project-card">
                                                    <div class="project-meta-text">
                                                        <span class="project-meta-label">ID:</span>{{ $proyecto->id }} 
                                                    </div>
                                                    <div class="project-meta-text">
                                                        <span class="project-meta-label">Nombre de Proyecto:</span> {{ $proyecto->nombre }} 
                                                    </div>

                                                </div>
                                                <div class="project-card">
                                                    <p class="project-meta-text">
                                                        <span class="project-meta-label">Cliente  : </span> {{ $proyecto->user->name }}
                                                    </p>

                                                    <p class="project-meta-text">
                                                        <span class="project-meta-label">Empresa:</span>
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

                                                <div class="project-card sm:col-span-2">
                                                    <p class="project-meta-text">
                                                        <span class="project-meta-label">Descripción:</span> {{ $proyecto->descripcion }}
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
                                                <div class="project-show-info-grid">
                                                    <div class="project-card">
                                                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">Categoría:</p>
                                                        <p class="project-meta-text">{{ $categoria['nombre'] ?? 'Sin categoría' }}</p>
                                                    </div>
                                                    <div class="project-card">
                                                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">Producto:</p>
                                                        <p class="project-meta-text">{{ $producto['nombre'] ?? 'Sin producto' }}</p>
                                                    </div>
                                                </div>

                                                <div class="project-show-feature-grid">
                                                    @forelse(($caracteristicas ?? []) as $caracteristica)
                                                        @php
                                                            // Asegura estructura esperada
                                                            $caracteristica = is_array($caracteristica) ? $caracteristica : (array) $caracteristica;
                                                            $opciones = $caracteristica['opciones'] ?? [];
                                                            if (!is_array($opciones)) {
                                                                $opciones = (array) $opciones;
                                                            }
                                                        @endphp
                                                        <div class="project-muted-card">
                                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $caracteristica['nombre'] ?? 'Sin nombre' }}</h3>

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
                                                        <div class="sm:col-span-2 lg:col-span-3 project-card">
                                                            <p class="text-sm text-gray-600 dark:text-gray-300">Sin características seleccionadas.</p>
                                                        </div>
                                                    @endforelse


                                                </div>
                            </div>
                            <!-- Contenido Chat -->
                            <div x-show="tab === 'chat'" x-cloak class="project-show-tab-panel flex flex-col flex-1 min-h-0">
                                <h2 class="project-section-title">Chat del Proyecto</h2>
                                <div wire:poll.2s class="project-chat-shell">
                                    <livewire:chat-component :proyecto-id="$proyecto->id" />
                                </div>
                            </div>

                            <!-- Contenido Chat -->
                            <div x-show="tab === 'chatProveedor'" x-cloak class="project-show-tab-panel flex flex-col flex-1 min-h-0">
                                <h2 class="project-section-title">Chat del Proyecto</h2>
                                <div wire:poll.2s class="project-chat-shell">
                                    <livewire:chat-proveedor-component :proyecto-id="$proyecto->id" />
                                </div>
                            </div>
                            
                            <!-- Contenido Tareas -->
                            @can('proyectodiseñopestañaTareasDelProyecto')
                                <div x-show="tab === 'tareas'" x-cloak class="project-show-tab-panel flex flex-col flex-1 min-h-0 overflow-y-auto">
                                    <h2 class="project-section-title">Tareas del Diseño</h2>
                                    <livewire:proyectos.tareas-diseno :proyecto-id="$proyecto->id" />
                                </div>
                            @endcan


                            <div x-show="tab === 'transferencia'" x-cloak class="project-show-tab-panel flex flex-col flex-1 min-h-0">
                                    <h2 class="project-section-title">Transferencias</h2>
                                    <div wire:poll.2s class="project-chat-shell">
                                  
                                 <livewire:proyectos.transferencia-proyecto :proyecto="$proyecto" wire:key="transfer-{{ $proyecto->id }}" />

                                </div>
                            </div>
                            

                        </div>
                    </div>
                </div>


                @can('vistaproyectoSeccionPedidos')
                    <div class="project-detail-panel mt-6">
                        <div class="project-detail-body">
                            <div x-data="{ tab: 'pedidos' }">
                                <!-- Pestañas -->
                                <div class="project-tab-list space-x-4">
                                    <button 
                                        @click="tab = 'pedidos'" 
                                        :class="tab === 'pedidos' ? 'project-tab-button project-tab-button--active' : 'project-tab-button project-tab-button--inactive'" 
                                    >
                                        Pedidos
                                    </button>
                                    <button 
                                        @click="tab = 'muestras'" 
                                        :class="tab === 'muestras' ? 'project-tab-button project-tab-button--active' : 'project-tab-button project-tab-button--inactive'" 
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

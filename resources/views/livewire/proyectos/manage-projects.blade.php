<div 
    x-data="{
        abierto: JSON.parse(localStorage.getItem('dashboard_preproyecto_abierto') ?? 'true'),
        toggle() {
            this.abierto = !this.abierto;
            localStorage.setItem('dashboard_preproyecto_abierto', JSON.stringify(this.abierto));
        }
    }"
    class="container mx-auto p-6"
>
            <h2 
                @click="toggle()"
                class="text-xl font-bold mb-4 border-b border-gray-300 pb-2 cursor-pointer hover:text-blue-600 transition"
            >
                Diseños
            <span class="text-sm text-gray-500 ml-2" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
            </h2>   

            <!-- Contenido del panel -->
            <div x-show="abierto" x-transition>

                <ul class="flex flex-wrap border-b border-gray-200 mb-4 gap-1">
                    @foreach ($this->tabs as $tab)
                        <li>
                            <button
                                wire:click="setTab('{{ $tab }}')"
                                @class([
                                    'px-4 py-2 rounded-t-lg text-sm whitespace-nowrap',
                                    'border-b-2 font-semibold bg-white'           => $activeTab === $tab,
                                    'text-gray-600 hover:text-blue-500'           => $activeTab !== $tab,
                                    'border-blue-500 text-blue-600'               => $activeTab === $tab,
                                    'border-transparent'                          => $activeTab !== $tab,
                                ])
                            >
                                {{ $tab }}
                            </button>
                        </li>
                    @endforeach
                </ul>

                <div x-data="{ selectedProjects: @entangle('selectedProjects') }" class="container mx-auto p-6">
                    {{-- <!-- Botones de acción -->
                    @hasanyrole('admin|estaf')
                            <div class="mb-4 flex flex-wrap gap-2">
                                <button
                                    class="px-3 py-1.5 text-sm bg-blue-500 text-white rounded-md hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="selectedProjects.length === 0"
                                    wire:click="exportSelected"
                                >
                                    Exportar
                                </button>

                                <button
                                    class="px-3 py-1.5 text-sm bg-red-500 text-white rounded-md hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="selectedProjects.length === 0"
                                    wire:click="deleteSelected"
                                >
                                    Eliminar
                                </button>
                            </div>
                    @endhasanyrole
 --}}


                        @if($mostrarFiltros)
                            {{-- <div 
                                x-data="{ abierto: @entangle('mostrarFiltros') }" 
                                class="mb-6"
                            >
                            <template x-if="abierto">
                                <div 
                                    x-show="abierto" 
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 transform scale-95"
                                    x-transition:enter-end="opacity-100 transform scale-100"
                                    x-transition:leave="transition ease-in duration-200"
                                    x-transition:leave-start="opacity-100 transform scale-100"
                                    x-transition:leave-end="opacity-0 transform scale-95"
                                    class="w-full bg-white border border-gray-200 shadow-md rounded-lg"
                                >
                                    <div class="flex justify-between items-center p-4 border-b">
                                        <h2 class="text-lg font-bold text-gray-700">Filtros</h2>
                                        <div class="flex items-center gap-2">
                                            <button 
                                                wire:click="buscarPorFiltros"
                                                class="bg-white border border-gray-300 text-gray-700 px-3 py-1 rounded hover:bg-gray-100 text-sm"
                                            >
                                                Filtrar
                                            </button>
                                            <button 
                                                @click="abierto = false" 
                                                class="text-gray-500 hover:text-gray-700 text-xl leading-none"
                                            >
                                                ✕
                                            </button>
                                        </div>
                                    </div>

                                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                        <x-select
                                            label="Estado del Proyecto"
                                            placeholder="Selecciona estados"
                                            multiselect
                                            :options="collect($this->estados)->map(fn($estado) => ['id' => $estado, 'name' => $estado])->toArray()"
                                            option-value="id"
                                            option-label="name"
                                            wire:model="estadosSeleccionados"
                                            autocomplete="off"
                                        />
                                    </div>
                                </div>
                            </template>
                                <template x-if="!abierto">
                                    <div class="mb-4">
                                        <button @click="abierto = true" class="text-sm text-blue-600 hover:underline">
                                            Mostrar Filtros
                                        </button>
                                    </div>
                                </template>
                            </div> --}}
                        @else
                            {{-- <div class="mb-4">
                                <button wire:click="$set('mostrarFiltros', true)" class="text-sm text-blue-600 hover:underline">
                                    Mostrar Filtros
                                </button>
                            </div> --}}
                        @endif

                    <!-- Tabla -->
                    <div class="overflow-x-auto bg-white rounded-lg shadow">
                        <table class="min-w-full border-collapse border border-gray-200 rounded-lg">
                            <thead class="bg-gray-100">
                                <tr>
                                    @hasanyrole('admin|estaf')
                                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">
                                            <input
                                                type="checkbox"
                                                wire:model="selectAll"
                                                @change="selectedProjects = $event.target.checked ? @js($projects->pluck('id')) : []"
                                            />
                                        </th>
                                    @endhasanyrole
                                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">ID</th>
                                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Nombre del Proyecto</th>
                                    @can('tablaProyectos-ver-todos-los-proyectos')
                                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Usuario</th>
                                    @endcan
                                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Pedidos</th>
                                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Estado del Disño</th>
                         
                                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($projects as $project)
                                    <tr class="hover:bg-gray-50">
                                        @hasanyrole('admin|estaf')
                                        <td class="border-b px-4 py-2 text-gray-700 text-sm">
                                            <input
                                                type="checkbox"
                                                wire:model="selectedProjects"
                                                value="{{ $project->id }}"
                                            />
                                        </td>
                                        @endhasanyrole
                                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $project->id }}</td>
                                    
                                            <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $project->nombre }}</td>
                                    
                                        @can('tablaProyectos-ver-todos-los-proyectos')
                                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $project->user->name ?? 'Sin usuario' }}</td>
                                        @endcan
                                        <td class="border-b px-4 py-2 text-gray-700 text-sm">
                                            @if($project->pedidos->isNotEmpty())
                                                <ul class="list-disc list-inside">
                                                    @foreach($project->pedidos as $pedido)
                                                        <li class="text-gray-600">
                                                            <span class="font-semibold">Categoría:</span> {{ $pedido->producto->categoria->nombre ?? 'Sin categoría' }},
                                                            <span class="font-semibold">Producto:</span> {{ $pedido->producto->nombre ?? 'Sin producto' }},
                                                            <span class="font-semibold">Total:</span> {{ number_format($pedido->total) }},
                                                            <span class="font-semibold">Estatus:</span> {{ $pedido->estatus }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-gray-500">Sin pedidos</span>
                                            @endif
                                        </td>
                                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $project->estado ?? 'Sin estado' }}</td>
                                     
                                        <td class="border-b px-4 py-2 text-gray-700 text-sm">
                                            <a href="{{ route('proyecto.show', $project->id) }}" class="text-blue-500 hover:underline">
                                                Ver detalles
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="mt-4">
                        {{ $projects->links() }}
                    </div>
                </div>
            </div>
</div>
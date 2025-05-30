<div 
    x-data="{
        abierto: JSON.parse(localStorage.getItem('dashboard_preproyectos_abierto') ?? 'true'),
        toggle() {
            this.abierto = !this.abierto;
            localStorage.setItem('dashboard_preproyectos_abierto', JSON.stringify(this.abierto));
        }
    }"
    class="container mx-auto p-6"
>
    <h2 
        @click="toggle()"
        class="text-xl font-bold mb-4 border-b border-gray-300 pb-2 cursor-pointer hover:text-blue-600 transition"
    >
        Solicitudes de Proyectos
        <span class="text-sm text-gray-500 ml-2" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
    {{-- </h2>
        Mis preproyectos
        <span class="text-sm text-gray-500 ml-2" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
    </h2> --}}
    </h2>
    <!-- Contenido del panel -->
    <div x-show="abierto" x-transition>

        <div class="p-6 text-gray-900 dark:text-gray-100">
            <a class="w-full sm:w-auto px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed" 
                    href="{{ route('preproyectos.create') }}">Crear Nueva Solicitud</a>
        </div>

        <div x-data="{ selectedProjects: @entangle('selectedProjects') }" class="container mx-auto p-6">
            <!-- Botones de acción -->
            <div class="mb-4 flex flex-wrap space-y-2 sm:space-y-0 sm:space-x-4">
                {{-- <button
                    class="w-full sm:w-auto px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="selectedProjects.length === 0"
                    wire:click="exportSelected"
                >
                    Exportar Seleccionados
                </button>
                <button
                    class="w-full sm:w-auto px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="selectedProjects.length === 0"
                    wire:click="deleteSelected"
                >
                    Eliminar Seleccionados
                </button> --}}
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table class="min-w-full border-collapse border border-gray-200 rounded-lg">
                    <thead class="bg-gray-100">
                        <tr>
                            {{-- <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">
                                <input
                                    type="checkbox"
                                    wire:model="selectAll"
                                    @change="selectedProjects = $event.target.checked ? @js($projects->pluck('id')) : []"
                                />
                            </th> --}}
                            <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">ID</th>
                            <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Nombre del Proyecto</th>
                            @can('tablaPreproyectos-ver-todos-los-preproyectos')
                            <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Usuario</th>
                            @endcan
                            <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Estado</th>
                            <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projects as $project)
                            <tr class="hover:bg-gray-50">
                                {{-- <td class="border-b px-4 py-2 text-gray-700 text-sm">
                                    <input
                                        type="checkbox"
                                        wire:model="selectedProjects"
                                        value="{{ $project->id }}"
                                    />
                                </td> --}}
                                <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $project->id }}</td>
                                <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $project->nombre }}</td>
                                
                                @can('tablaPreproyectos-ver-todos-los-preproyectos')
                                <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $project->user->name ?? 'Sin usuario' }}</td>
                                @endcan
                            
                                <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $project->estado }}</td>
                                <td class="border-b px-4 py-2 text-gray-700 text-sm">
                                    <a href="{{ route('preproyectos.show', $project->id) }}" class="text-blue-500 hover:underline">
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

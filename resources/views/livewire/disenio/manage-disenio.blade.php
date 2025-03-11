<div x-data="{ selectedProjects: @entangle('selectedProjects') }" class="container mx-auto p-6">
    <!-- Botones de acción -->
    <div class="mb-4 flex flex-wrap space-y-2 sm:space-y-0 sm:space-x-4">
        <button
            class="w-full sm:w-auto px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="selectedProjects.length === 0"
            wire:click="exportSelected"
        >
            Exportar Seleccionados
        </button>

    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full border-collapse border border-gray-200 rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">
                        <input
                            type="checkbox"
                            wire:model="selectAll"
                            @change="selectedProjects = $event.target.checked ? @js($projects->pluck('id')) : []"
                        />
                    </th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">ID</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Nombre del Proyecto</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Usuario</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Estado</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Tarea</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($projects as $project)
                    <tr class="hover:bg-gray-50">
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">
                            <input
                                type="checkbox"
                                wire:model="selectedProjects"
                                value="{{ $project->id }}"
                            />
                        </td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $project->id }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $project->nombre }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $project->user->name ?? 'Sin usuario' }}</td>

                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $project->estado ?? 'Sin estado' }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">
                            @if($project->tareas->isNotEmpty())
                                <ul class="list-disc list-inside">
                                    @foreach($project->tareas as $tarea)
                                        <li>
                                            <span class="font-semibold">Usuario:</span> {{ $tarea->staff->name ?? 'No asignado' }}<br>
                                            <span class="font-semibold">Descripción:</span> {{ $tarea->descripcion }}<br>
                                            <span class="font-semibold">Estado:</span> {{ $tarea->estado }}
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-gray-500">Sin tareas</span>
                            @endif
                        </td>
                        
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">
                            <button 
                            class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600"
                            wire:click="abrirModalAsignacion({{ $project->id }})">
                                Asignar Tarea
                            </button>
                            
                            <a href="{{ route('proyecto.show', $project->id) }}" class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600">
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
    <div x-data="{ open: @entangle('modalOpen') }">
        <div x-show="open" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-96">
                <h2 class="text-lg font-semibold mb-4">Asignar Tarea</h2>
    
                <label class="block text-sm font-medium text-gray-700">Usuario</label>
                <select wire:model="selectedUser" class="w-full p-2 border rounded">
                    <option value="">Seleccione un usuario</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
    
                <label class="block text-sm font-medium text-gray-700 mt-4">Descripción</label>
                <textarea wire:model="taskDescription" class="w-full p-2 border rounded"></textarea>
    
                <div class="mt-4 flex justify-end">
                    <button class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600" @click="open = false">
                        Cancelar
                    </button>
                    <button class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 ml-2"
                        wire:click="asignarTarea">
                        Asignar
                    </button>
                </div>
            </div>
        </div>
    </div>
    
</div>



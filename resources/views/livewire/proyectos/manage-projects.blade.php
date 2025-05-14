<div x-data="{ selectedProjects: @entangle('selectedProjects') }" class="container mx-auto p-6">
    <!-- Botones de acción -->
    @hasanyrole('admin|estaf')
        <div class="mb-4 flex flex-wrap space-y-2 sm:space-y-0 sm:space-x-4">
            <button
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
            </button>
        </div>
    @endhasanyrole

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
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Usuario</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Pedidos</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Estado</th>
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
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $project->user->name ?? 'Sin usuario' }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">
                            @if($project->pedidos->isNotEmpty())
                                <ul class="list-disc list-inside">
                                    @foreach($project->pedidos as $pedido)
                                        <li class="text-gray-600">
                                            <span class="font-semibold">Categoría:</span> {{ $pedido->producto->categoria->nombre ?? 'Sin categoría' }},
                                            <span class="font-semibold">Producto:</span> {{ $pedido->producto->nombre ?? 'Sin producto' }},
                                            <span class="font-semibold">Total:</span> ${{ number_format($pedido->total, 2) }},
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

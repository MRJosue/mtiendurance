<div class="max-w-6xl mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Gestión de Diseños</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-3">
            {{ session('message') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-3">
        <button wire:click="exportSelected" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
            Exportar Seleccionados
        </button>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full border-collapse border border-gray-300 rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2 text-left"><input type="checkbox" wire:model="selectAll"></th>
                    <th class="border p-2 text-left">ID</th>
                    <th class="border p-2 text-left">Nombre del Proyecto</th>
                    <th class="border p-2 text-left">Usuario</th>
                    <th class="border p-2 text-left">Estado</th>
                    <th class="border p-2 text-left">Tareas</th>
                    <th class="border p-2 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($projects as $project)
                    <tr class="hover:bg-gray-50">
                        <td class="border p-2"><input type="checkbox" wire:model="selectedProjects" value="{{ $project->id }}"></td>
                        <td class="border p-2">{{ $project->id }}</td>
                        <td class="border p-2">{{ $project->nombre }}</td>
                        <td class="border p-2">{{ $project->user->name ?? 'Sin usuario' }}</td>
                        <td class="border p-2">{{ $project->estado ?? 'Sin estado' }}</td>
                        <td class="border p-2">
                            @if($project->tareas->isNotEmpty())
                                <ul class="list-disc list-inside">
                                    @foreach($project->tareas as $tarea)
                                        <li><strong>Usuario:</strong> {{ $tarea->staff->name ?? 'No asignado' }}<br>
                                            <strong>Descripción:</strong> {{ $tarea->descripcion }}<br>
                                            <strong>Estado:</strong> {{ $tarea->estado }}
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-gray-500">Sin tareas</span>
                            @endif
                        </td>
                        <td class="border p-2 text-center space-x-2">
                            <button wire:click="abrirModalAsignacion({{ $project->id }})" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-3 py-1 rounded">
                                Asignar Tarea
                            </button>
                            <a href="{{ route('proyecto.show', $project->id) }}" class="bg-green-500 hover:bg-green-600 text-white font-semibold px-3 py-1 rounded">
                                Ver detalles
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $projects->links() }}
    </div>

    @if($modalOpen)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded shadow-lg w-full max-w-md p-6">
                <h2 class="text-lg font-semibold mb-4">Asignar Tarea</h2>
                <label class="block text-sm font-medium text-gray-700">Usuario</label>
                <select wire:model="selectedUser" class="w-full p-2 border rounded mb-3">
                    <option value="">Seleccione un usuario</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                <label class="block text-sm font-medium text-gray-700">Descripción</label>
                <textarea wire:model="taskDescription" class="w-full p-2 border rounded mb-3"></textarea>
                <div class="flex justify-end space-x-2">
                    <button wire:click="cerrarModal" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded">
                        Cancelar
                    </button>
                    <button wire:click="asignarTarea" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
                        Asignar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

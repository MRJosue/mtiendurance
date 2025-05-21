<div class="max-w-6xl mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Gestión de Diseños</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-3">
            {{ session('message') }}
        </div>
    @endif

    @error('selectedUser')
        <div class="bg-red-100 text-red-800 p-3 rounded mb-3">{{ $message }}</div>
    @enderror
    @error('taskDescription')
        <div class="bg-red-100 text-red-800 p-3 rounded mb-3">{{ $message }}</div>
    @enderror

    <div class="flex items-center justify-between mb-3">
        {{-- <button wire:click="exportSelected" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
            Exportar Seleccionados
        </button> --}}
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow border">
        <table class="min-w-full text-sm text-left table-auto border-collapse">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                <tr>
                    {{-- <th class="px-4 py-3 border">
                        <input type="checkbox" wire:model="selectAll">
                    </th> --}}
                    <th class="px-4 py-3 border">ID</th>
                    <th class="px-4 py-3 border">Proyecto</th>
                    <th class="px-4 py-3 border">Usuario</th>
                    <th class="px-4 py-3 border">Estado</th>
                    <th class="px-4 py-3 border">Tareas</th>
                    <th class="px-4 py-3 border">Historial</th>
                    <th class="px-4 py-3 border text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                @foreach($projects as $project)
                    <tr class="hover:bg-gray-50">
                        {{-- <td class="px-4 py-2 border">
                            <input type="checkbox" wire:model="selectedProjects" value="{{ $project->id }}">
                        </td> --}}
                        <td class="px-4 py-2 border font-semibold">{{ $project->id }}</td>
                        <td class="px-4 py-2 border">{{ $project->nombre }}</td>
                        <td class="px-4 py-2 border">{{ $project->user->name ?? 'Sin usuario' }}</td>
                        <td class="px-4 py-2 border font-medium">{{ $project->estado ?? 'Sin estado' }}</td>
    
                        <td class="px-4 py-2 border whitespace-normal">
                            @if($project->tareas->isNotEmpty())
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($project->tareas as $tarea)
                                        <li class="text-xs">
                                            <strong>Usuario:</strong> {{ $tarea->staff->name ?? 'No asignado' }}<br>
                                            <strong>Descripción:</strong> {{ $tarea->descripcion }}<br>
                                            <strong>Estado:</strong> {{ $tarea->estado }}
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-gray-500 text-sm">Sin tareas</span>
                            @endif
                        </td>
    
                        <td class="px-4 py-2 border whitespace-normal">
                            @if($project->estados->isNotEmpty())
                                <ul class="list-disc list-inside text-gray-600 space-y-1 text-xs">
                                    @foreach($project->estados->sortByDesc('id')->take(2) as $estado)
                                        <li>
                                            <strong>{{ $estado->estado }}</strong> 
                                            ({{ \Carbon\Carbon::parse($estado->fecha_inicio)->format('d-m-Y H:i') }})
                                            por {{ $estado->usuario->name ?? 'Desconocido' }}
                                        </li>
                                    @endforeach
                                </ul>
                                @if($project->estados->count() > 2)
                                    <button wire:click="verMas({{ $project->id }})" class="text-blue-500 hover:underline text-xs mt-1">
                                        Ver más
                                    </button>
                                @endif
                            @else
                                <span class="text-gray-500 text-sm">Sin historial</span>
                            @endif
                        </td>
    
                        <td class="px-4 py-2 border text-center space-y-1">
                            @if($project->tareas->isEmpty()) 
                                <button wire:click="abrirModalAsignacion({{ $project->id }})"
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-semibold px-3 py-1 rounded">
                                    Asignar Tarea
                                </button>
                            @endif
                            <a href="{{ route('proyecto.show', $project->id) }}"
                                class="bg-green-500 hover:bg-green-600 text-white text-xs font-semibold px-3 py-1 rounded inline-block mt-1">
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
                @error('selectedUser')
                <div class="bg-red-100 text-red-800 p-3 rounded mb-3">{{ $message }}</div>
                @enderror

                <label class="block text-sm font-medium text-gray-700">Descripción</label>
                <textarea wire:model="taskDescription" class="w-full p-2 border rounded mb-3"></textarea>
                @error('taskDescription') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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

@if($modalVerMas && $proyectoSeleccionado)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="relative bg-white rounded-lg shadow-lg p-6 w-full max-w-3xl max-h-[80vh] overflow-y-auto">

            <!-- Botón X flotante -->
            <button
                wire:click="cerrarModalVerMas"
                class="absolute top-3 right-3 text-gray-600 hover:text-red-600 text-2xl font-bold focus:outline-none"
                aria-label="Cerrar"
            >
                &times;
            </button>

            <h3 class="text-xl font-bold mb-4">
                Historial de Estatus - Proyecto #{{ $proyectoSeleccionado->id }}
            </h3>

            <table class="table-auto w-full text-sm border">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border px-4 py-2">Estatus</th>
                        <th class="border px-4 py-2">Comentario</th>
                        <th class="border px-4 py-2">Archivo</th>
                        <th class="border px-4 py-2">ID Archivo</th>
                        <th class="border px-4 py-2">Fecha</th>
                        <th class="border px-4 py-2">Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($proyectoSeleccionado->estados->sortByDesc('id') as $estado)
                        <tr>
                            <td class="border px-4 py-2">{{ $estado->estado }}</td>
                            <td class="border px-4 py-2">{{ $estado->comentario ?? '-' }}</td>
                            <td class="border px-4 py-2">
                                @if($estado->url)
                                    <a href="{{ asset('storage/' . $estado->url) }}" target="_blank" class="text-blue-600 underline">Ver archivo</a>
                                @else
                                    <span class="text-gray-500">No disponible</span>
                                @endif
                            </td>
                            <td class="border px-4 py-2 text-center">{{ $estado->last_uploaded_file_id ?? '-' }}</td>
                            <td class="border px-4 py-2">{{ \Carbon\Carbon::parse($estado->fecha_inicio)->format('d-m-Y H:i') }}</td>
                            <td class="border px-4 py-2">{{ $estado->usuario->name ?? 'Desconocido' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Botón de cerrar al pie -->
            <div class="mt-4 text-right">
                <button wire:click="cerrarModalVerMas" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
@endif

</div>

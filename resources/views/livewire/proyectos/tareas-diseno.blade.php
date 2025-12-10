<div x-data class="container mx-auto p-6">
    <!-- Mensaje flash -->
    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <!-- Sección de Tareas -->
    <div>
        @if ($proyecto->tareas->isEmpty())
        <div class="flex flex-wrap gap-2 mb-4">
            <button
                wire:click="abrirModal"
                class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-4 py-2 rounded"
            >
                Asignar tarea a diseñador
            </button>

            {{-- 🔹 Botón para asignar proveedor si el proyecto lo requiere --}}
            @if($proyecto->flag_requiere_proveedor)
                <button
                    wire:click="abrirModalProveedor"
                    class="bg-purple-500 hover:bg-purple-600 text-white font-semibold px-4 py-2 rounded"
                >
                    {{ $proyecto->proveedor?->name ? 'Cambiar / Chat proveedor' : 'Asignar proveedor' }}
                </button>
            @endif
        </div>
        @else

            <div class="flex flex-wrap gap-2 mb-4">
            <button
                wire:click="abrirModal"
                class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-4 py-2 rounded"
            >
                Asignar tarea a diseñador
            </button>

            {{-- 🔹 Botón para asignar proveedor si el proyecto lo requiere --}}
            @if($proyecto->flag_requiere_proveedor)
                <button
                    wire:click="abrirModalProveedor"
                    class="bg-purple-500 hover:bg-purple-600 text-white font-semibold px-4 py-2 rounded"
                >
                    {{ $proyecto->proveedor?->name ? 'Cambiar / Chat proveedor' : 'Asignar proveedor' }}
                </button>
            @endif
        </div>

            <div class="overflow-x-auto bg-white rounded-lg shadow border">
                <table class="min-w-full text-sm text-left table-auto border-collapse">
                    <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 border">Diseñador</th>
                            <th class="px-4 py-3 border">Descripción</th>
                            <th class="px-4 py-3 border">Estado</th>
                            <th class="px-4 py-3 border">Creado</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        @foreach ($proyecto->tareas as $tarea)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border">{{ $tarea->staff->name ?? '—' }}</td>
                                <td class="px-4 py-2 border">{{ $tarea->descripcion }}</td>
                                <td class="px-4 py-2 border">{{ $tarea->estado }}</td>
                                <td class="px-4 py-2 border">{{ $tarea->created_at->format('d-m-Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Sección de Historial -->
    <div class="mt-6">
        <h3 class="text-lg font-semibold mb-2">Historial de Estados</h3>

        @if($proyecto->estados->isNotEmpty())
            <ul class="list-disc list-inside space-y-1 text-sm text-gray-700">
                @foreach($proyecto->estados->sortByDesc('id')->take(2) as $estado)
                    <li>
                        <strong>{{ $estado->estado }}</strong>
                        ({{ \Carbon\Carbon::parse($estado->fecha_inicio)->format('d-m-Y H:i') }}) por {{ $estado->usuario->name ?? 'Desconocido' }}
                    </li>
                @endforeach
            </ul>

            @if($proyecto->estados->count() > 2)
                <button wire:click="verMas"
                        class="text-blue-500 hover:underline text-sm mt-1">
                    Ver más
                </button>
            @endif
        @else
            <p class="text-gray-500 text-sm">Sin historial</p>
        @endif
    </div>

    <!-- Modal de Asignación -->
    @if ($modalOpen)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="relative bg-white rounded shadow-lg w-full max-w-md p-6">
                <!-- Botón X -->
                <button wire:click="cerrarModal"
                        class="absolute top-2 right-2 text-gray-600 hover:text-gray-800 text-xl font-bold">×</button>

                <h3 class="text-lg font-semibold mb-4">Asignar Tarea</h3>

                <!-- Selector de diseñador -->
                <label class="block text-sm font-medium text-gray-700 mb-1">Diseñador</label>
                <select wire:model="selectedUser" class="w-full p-2 border rounded mb-3">
                    <option value="">Seleccione un diseñador</option>
                    @foreach ($disenadores as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                @error('selectedUser')
                    <div class="bg-red-100 text-red-800 p-2 rounded mb-3">{{ $message }}</div>
                @enderror

                <!-- Descripción -->
                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <textarea wire:model="taskDescription" class="w-full p-2 border rounded mb-3" rows="3"></textarea>
                @error('taskDescription')
                    <div class="bg-red-100 text-red-800 p-2 rounded mb-3">{{ $message }}</div>
                @enderror

                <div class="flex justify-end gap-2">
                    <button wire:click="cerrarModal" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded">Cancelar</button>
                    <button wire:click="asignarTarea" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">Asignar</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Historial Completo -->
    @if ($modalVerMas && $proyectoSeleccionado)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="relative bg-white rounded-lg shadow-lg p-6 w-full max-w-3xl max-h-[80vh] overflow-y-auto">
                <!-- Botón X -->
                <button wire:click="cerrarModalVerMas"
                        class="absolute top-2 right-2 text-gray-600 hover:text-gray-800 text-xl font-bold">×</button>

                <h3 class="text-xl font-bold mb-4">Historial de Estados - Proyecto #{{ $proyectoSeleccionado->id }}</h3>
                <table class="w-full table-auto text-sm border-collapse border border-gray-200">
                    <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-2 border">Estatus</th>
                            <th class="px-4 py-2 border">Comentario</th>
                            <th class="px-4 py-2 border">Archivo</th>
                            <th class="px-4 py-2 border">ID Archivo</th>
                            <th class="px-4 py-2 border">Fecha</th>
                            <th class="px-4 py-2 border">Usuario</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        @foreach($proyectoSeleccionado->estados->sortByDesc('id') as $estado)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border">{{ $estado->estado }}</td>
                                <td class="px-4 py-2 border">{{ $estado->comentario ?? '-' }}</td>
                                <td class="px-4 py-2 border">
                                    @if($estado->url)
                                        <a href="{{ asset('storage/' . $estado->url) }}" target="_blank" class="text-blue-500 hover:underline">Ver archivo</a>
                                    @else
                                        <span class="text-gray-500">No disponible</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 border text-center">{{ $estado->last_uploaded_file_id ?? '-' }}</td>
                                <td class="px-4 py-2 border">{{ \Carbon\Carbon::parse($estado->fecha_inicio)->format('d-m-Y H:i') }}</td>
                                <td class="px-4 py-2 border">{{ $estado->usuario->name ?? 'Desconocido' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Modal de Asignación de Proveedor -->
    @if ($modalProveedorOpen)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="relative bg-white rounded shadow-lg w-full max-w-md p-6">
                <!-- Botón X -->
                <button wire:click="cerrarModalProveedor"
                        class="absolute top-2 right-2 text-gray-600 hover:text-gray-800 text-xl font-bold">×</button>

                <h3 class="text-lg font-semibold mb-4">Asignar Proveedor</h3>

                <!-- Selector de proveedor -->
                <label class="block text-sm font-medium text-gray-700 mb-1">Proveedor</label>
                <select wire:model="selectedProveedor" class="w-full p-2 border rounded mb-3">
                    <option value="">Seleccione un proveedor</option>
                    @foreach ($proveedores as $prov)
                        <option value="{{ $prov->id }}">
                            {{ $prov->name }} (ID: {{ $prov->id }})
                        </option>
                    @endforeach
                </select>
                @error('selectedProveedor')
                    <div class="bg-red-100 text-red-800 p-2 rounded mb-3">{{ $message }}</div>
                @enderror

                <p class="text-xs text-gray-500 mb-4">
                    Al asignar un proveedor se creará (si no existe) un chat de proveedor para este proyecto.
                </p>

                <div class="flex justify-end gap-2">
                    <button wire:click="cerrarModalProveedor" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded">
                        Cancelar
                    </button>
                    <button wire:click="asignarProveedor" class="bg-purple-500 hover:bg-purple-600 text-white font-semibold px-4 py-2 rounded">
                        Asignar proveedor
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Aquí puedes manejar eventos dispatch si lo necesitas
    });
</script>
@endpush
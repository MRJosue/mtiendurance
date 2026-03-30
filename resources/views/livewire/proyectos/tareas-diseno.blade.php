<div x-data class="container mx-auto p-6">
    <!-- Mensaje flash -->
    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4 dark:bg-green-900/40 dark:text-green-300">
            {{ session('message') }}
        </div>
    @endif

    <!-- Sección de Tareas -->
    <div>


        @if ($proyecto->tareas->isEmpty())
            <div class="flex flex-wrap gap-2 mb-4">

            @can('Boton-asignar-tarea') 
                 <button
                    wire:click="abrirModal"
                    class="w-full sm:w-auto bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-4 py-2 rounded"
                >
                    Asignar tarea
                </button>

            @endcan
            @can('Boton-asignar-proveedor')
                    @if($proyecto->flag_requiere_proveedor)
                        <button
                            wire:click="abrirModalProveedor"
                            class="w-full sm:w-auto bg-purple-500 hover:bg-purple-600 text-white font-semibold px-4 py-2 rounded"
                        >
                            {{ $proyecto->proveedor?->name ? 'Cambiar / Chat proveedor' : 'Asignar proveedor' }}
                        </button>
                    @endif
            @endcan

            </div>
        @else
            <div class="flex flex-wrap gap-2 mb-4">

                @can('Boton-asignar-tarea') 
                <button
                    wire:click="abrirModal"
                    class="w-full sm:w-auto bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-4 py-2 rounded"
                >
                    Asignar tarea
                </button>
                @endcan
                @can('Boton-asignar-proveedor')
                @if($proyecto->flag_requiere_proveedor)
                    <button
                        wire:click="abrirModalProveedor"
                        class="w-full sm:w-auto bg-purple-500 hover:bg-purple-600 text-white font-semibold px-4 py-2 rounded"
                    >
                        {{ $proyecto->proveedor?->name ? 'Cambiar / Chat proveedor' : 'Asignar proveedor' }}
                    </button>
                @endif
                @endcan
            </div>
             @can('Ver-historial-tareas')
                <div class="overflow-x-auto bg-white rounded-lg shadow border border-gray-200 dark:bg-gray-900 dark:border-gray-700">
                    <table class="min-w-full text-sm text-left table-auto border-collapse">
                        <thead class="bg-gray-100 text-gray-700 uppercase text-xs dark:bg-gray-800 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-3 border">Staff</th>
                                <th class="px-4 py-3 border">Tipo</th>
                                <th class="px-4 py-3 border">Descripción</th>
                                <th class="px-4 py-3 border">Estado</th>
                                <th class="px-4 py-3 border">Creado</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 dark:text-gray-200">
                            @foreach ($proyecto->tareas as $tarea)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/70">
                                    <td class="px-4 py-2 border border-gray-200 dark:border-gray-700">{{ $tarea->staff->name ?? '—' }}</td>
                                    <td class="px-4 py-2 border border-gray-200 dark:border-gray-700">{{ $tarea->tipo ?? '—' }}</td>
                                    <td class="px-4 py-2 border border-gray-200 dark:border-gray-700">{{ $tarea->descripcion }}</td>
                                    <td class="px-4 py-2 border border-gray-200 dark:border-gray-700">{{ $tarea->estado }}</td>
                                    <td class="px-4 py-2 border border-gray-200 dark:border-gray-700">{{ $tarea->created_at->format('d-m-Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endcan
        @endif
    </div>

    <!-- Sección de Historial -->

    @can('Ver-historial-tareas')
            <div class="mt-6">
                <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">Historial de Estados</h3>

                @if($proyecto->estados->isNotEmpty())
                    <ul class="list-disc list-inside space-y-1 text-sm text-gray-700 dark:text-gray-300">
                        @foreach($proyecto->estados->sortByDesc('id')->take(2) as $estado)
                            <li>
                                <strong>{{ $estado->estado }}</strong>
                                ({{ \Carbon\Carbon::parse($estado->fecha_inicio)->format('d-m-Y H:i') }}) por {{ $estado->usuario->name ?? 'Desconocido' }}
                            </li>
                        @endforeach
                    </ul>

                    @if($proyecto->estados->count() > 2)
                        <button wire:click="verMas" class="text-blue-500 hover:underline text-sm mt-1 dark:text-blue-400">
                            Ver más
                        </button>
                    @endif
                @else
                    <p class="text-gray-500 text-sm dark:text-gray-400">Sin historial</p>
                @endif
            </div>
    @endcan


    <!-- Modal de Asignación -->
    @if ($modalOpen)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 px-4">
            <div class="relative bg-white rounded shadow-lg w-full max-w-md p-6 dark:bg-gray-900 dark:border dark:border-gray-700">
                <button
                    wire:click="cerrarModal"
                    class="absolute top-2 right-2 text-gray-600 hover:text-gray-800 text-xl font-bold dark:text-gray-400 dark:hover:text-gray-200"
                >
                    ×
                </button>

                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Asignar Tarea</h3>

                <!-- Tipo de tarea -->
                <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-300">Tipo de tarea</label>
                <select wire:model="taskType" class="w-full p-2 border border-gray-300 rounded mb-3 bg-white text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                    <option value="">Seleccione un tipo</option>
                    <option value="DISEÑO">DISEÑO</option>
                    <option value="PRODUCCION">PRODUCCION</option>
                    <option value="CORTE">CORTE</option>
                    <option value="PINTURA">PINTURA</option>
                    <option value="FACTURACION">FACTURACION</option>
                    <option value="INDEFINIDA">INDEFINIDA</option>
                </select>
                @error('taskType')
                    <div class="bg-red-100 text-red-800 p-2 rounded mb-3 dark:bg-red-900/40 dark:text-red-300">{{ $message }}</div>
                @enderror

                <!-- Selector de staff -->
                <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-300">Usuario staff</label>
                <select wire:model="selectedUser" class="w-full p-2 border border-gray-300 rounded mb-3 bg-white text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                    <option value="">Seleccione un usuario staff</option>
                    @foreach ($staffUsers as $user)
                        <option value="{{ $user->id }}">
                            {{ $user->name }} - {{ $user->roles->first()->nombre ?? $user->roles->first()->name ?? 'Sin rol' }}
                        </option>
                    @endforeach
                </select>
                @error('selectedUser')
                    <div class="bg-red-100 text-red-800 p-2 rounded mb-3 dark:bg-red-900/40 dark:text-red-300">{{ $message }}</div>
                @enderror

                <!-- Descripción -->
                <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-300">Descripción</label>
                <textarea wire:model="taskDescription" class="w-full p-2 border border-gray-300 rounded mb-3 bg-white text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" rows="3"></textarea>
                @error('taskDescription')
                    <div class="bg-red-100 text-red-800 p-2 rounded mb-3 dark:bg-red-900/40 dark:text-red-300">{{ $message }}</div>
                @enderror

                <div class="flex flex-col sm:flex-row justify-end gap-2">
                    <button
                        wire:click="cerrarModal"
                        class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded"
                    >
                        Cancelar
                    </button>
                    <button
                        wire:click="asignarTarea"
                        class="w-full sm:w-auto bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded"
                    >
                        Asignar
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Historial Completo -->
    @if ($modalVerMas && $proyectoSeleccionado)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 px-4">
            <div class="relative bg-white rounded-lg shadow-lg p-6 w-full max-w-3xl max-h-[80vh] overflow-y-auto dark:bg-gray-900 dark:border dark:border-gray-700">
                <button
                    wire:click="cerrarModalVerMas"
                    class="absolute top-2 right-2 text-gray-600 hover:text-gray-800 text-xl font-bold dark:text-gray-400 dark:hover:text-gray-200"
                >
                    ×
                </button>

                <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">Historial de Estados - Proyecto #{{ $proyectoSeleccionado->id }}</h3>

                <div class="overflow-x-auto">
                    <table class="w-full table-auto text-sm border-collapse border border-gray-200 dark:border-gray-700">
                        <thead class="bg-gray-100 text-gray-700 uppercase text-xs dark:bg-gray-800 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-2 border">Estatus</th>
                                <th class="px-4 py-2 border">Comentario</th>
                                <th class="px-4 py-2 border">Archivo</th>
                                <th class="px-4 py-2 border">ID Archivo</th>
                                <th class="px-4 py-2 border">Fecha</th>
                                <th class="px-4 py-2 border">Usuario</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 dark:text-gray-200">
                            @foreach($proyectoSeleccionado->estados->sortByDesc('id') as $estado)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/70">
                                    <td class="px-4 py-2 border border-gray-200 dark:border-gray-700">{{ $estado->estado }}</td>
                                    <td class="px-4 py-2 border border-gray-200 dark:border-gray-700">{{ $estado->comentario ?? '-' }}</td>
                                    <td class="px-4 py-2 border border-gray-200 dark:border-gray-700">
                                        @if($estado->url)
                                            <a href="{{ asset('storage/' . $estado->url) }}" target="_blank" class="text-blue-500 hover:underline dark:text-blue-400">Ver archivo</a>
                                        @else
                                            <span class="text-gray-500 dark:text-gray-400">No disponible</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-center">{{ $estado->last_uploaded_file_id ?? '-' }}</td>
                                    <td class="px-4 py-2 border border-gray-200 dark:border-gray-700">{{ \Carbon\Carbon::parse($estado->fecha_inicio)->format('d-m-Y H:i') }}</td>
                                    <td class="px-4 py-2 border border-gray-200 dark:border-gray-700">{{ $estado->usuario->name ?? 'Desconocido' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Asignación de Proveedor -->
    @if ($modalProveedorOpen)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 px-4">
            <div class="relative bg-white rounded shadow-lg w-full max-w-md p-6 dark:bg-gray-900 dark:border dark:border-gray-700">
                <button
                    wire:click="cerrarModalProveedor"
                    class="absolute top-2 right-2 text-gray-600 hover:text-gray-800 text-xl font-bold dark:text-gray-400 dark:hover:text-gray-200"
                >
                    ×
                </button>

                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Asignar Proveedor</h3>

                <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-300">Proveedor</label>
                <select wire:model="selectedProveedor" class="w-full p-2 border border-gray-300 rounded mb-3 bg-white text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                    <option value="">Seleccione un proveedor</option>
                    @foreach ($proveedores as $prov)
                        <option value="{{ $prov->id }}">
                            {{ $prov->name }} (ID: {{ $prov->id }})
                        </option>
                    @endforeach
                </select>
                @error('selectedProveedor')
                    <div class="bg-red-100 text-red-800 p-2 rounded mb-3 dark:bg-red-900/40 dark:text-red-300">{{ $message }}</div>
                @enderror

                <p class="text-xs text-gray-500 mb-4 dark:text-gray-400">
                    Al asignar un proveedor se creará (si no existe) un chat de proveedor para este proyecto.
                </p>

                <div class="flex flex-col sm:flex-row justify-end gap-2">
                    <button
                        wire:click="cerrarModalProveedor"
                        class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded"
                    >
                        Cancelar
                    </button>
                    <button
                        wire:click="asignarProveedor"
                        class="w-full sm:w-auto bg-purple-500 hover:bg-purple-600 text-white font-semibold px-4 py-2 rounded"
                    >
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

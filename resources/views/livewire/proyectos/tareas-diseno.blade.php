<div>

    <!-- Contenido plegable -->
    <div >
        @if (session()->has('message'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                {{ session('message') }}
            </div>
        @endif

        {{-- Si NO hay tareas, botón de asignar --}}
        @if ($proyecto->tareas->isEmpty())
            <button
                wire:click="abrirModal"
                class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-4 py-2 rounded mb-4"
            >
                Asignar tarea a diseñador
            </button>
        @else
        {{-- Si hay tareas, mostrar log --}}
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

    <!-- Modal de asignación -->
    @if ($modalOpen)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded shadow-lg w-full max-w-md p-6">
                <h3 class="text-lg font-semibold mb-4">Asignar Tarea</h3>

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

                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <textarea wire:model="taskDescription" class="w-full p-2 border rounded mb-3" rows="3"></textarea>
                @error('taskDescription')
                    <div class="bg-red-100 text-red-800 p-2 rounded mb-3">{{ $message }}</div>
                @enderror

                <div class="flex justify-end gap-2">
                    <button
                        wire:click="cerrarModal"
                        class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded"
                    >
                        Cancelar
                    </button>
                    <button
                        wire:click="asignarTarea"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded"
                    >
                        Asignar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- JS opcional encapsulado --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Aquí puedes manejar escuchas de eventos dispatch si los necesitas
        // ej: window.addEventListener('tareaAsignada', () => { ... })
    });
</script>
@endpush

<div class="max-w-6xl mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Administración de Estatus de Tareas</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-3">
            {{ session('message') }}
        </div>
    @endif

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full border-collapse border border-gray-300 rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2 text-left">ID</th>
                    <th class="border p-2 text-left">Proyecto</th>
                    <th class="border p-2 text-left">Asignado a</th>
                    <th class="border p-2 text-left">Descripción</th>
                    <th class="border p-2 text-left">Estado</th>
                    <th class="border p-2 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tasks as $task)
                    <tr class="hover:bg-gray-50">
                        <td class="border p-2">{{ $task->id }}</td>
                        <td class="border p-2">{{ $task->proyecto->nombre ?? 'Sin proyecto' }}</td>
                        <td class="border p-2">{{ $task->staff->name ?? 'No asignado' }}</td>
                        <td class="border p-2">{{ $task->descripcion }}</td>
                        <td class="border p-2">{{ $task->estado }}</td>
                        <td class="border p-2 text-center">


                            <button wire:click="abrirModal({{ $task->id }})" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-3 py-1 rounded">
                                Cambiar Estado
                            </button>

                            <button wire:click="verificarProceso({{ $task->proyecto->id }})"
                                class="bg-green-500 hover:bg-green-600 text-white font-semibold px-3 py-1 rounded">
                                Ver detalles
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $tasks->links() }}
    </div>

    @if($modalOpen)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded shadow-lg w-full max-w-md p-6">
                <h2 class="text-lg font-semibold mb-4">Actualizar Estado</h2>

                <label class="block text-sm font-medium text-gray-700">Nuevo Estado</label>
                <select wire:model="newStatus" class="w-full p-2 border rounded mb-3">
                    @foreach($statuses as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>
                @error('newStatus') <div class="bg-red-100 text-red-800 p-3 rounded mb-3">{{ $message }}</div> @enderror

                <div class="flex justify-end space-x-2">
                    <button wire:click="cerrarModal" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded">
                        Cancelar
                    </button>
                    <button wire:click="actualizarEstado" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($mostrarModalConfirmacion)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded shadow-lg w-full max-w-md p-6">
                <h2 class="text-lg font-semibold mb-4 text-center text-gray-800">¿Iniciar proceso de diseño?</h2>
                <p class="text-gray-600 text-center mb-6">
                    Esta acción marcará el proyecto como <strong>"EN PROCESO"</strong> y notificará a los responsables.
                </p>
                <div class="flex justify-end space-x-3">
                    <button wire:click="cancelarConfirmacion" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                        Cancelar
                    </button>
                    <button wire:click="confirmarInicioProceso" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    @endif


</div>

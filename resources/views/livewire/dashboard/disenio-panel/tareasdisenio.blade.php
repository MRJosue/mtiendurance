<div 
    x-data="{
        abierto: JSON.parse(localStorage.getItem('dashboard_admintareasdisenio_abierto') ?? 'true'),
        toggle() {
            this.abierto = !this.abierto;
            localStorage.setItem('dashboard_admintareasdisenio_abierto', JSON.stringify(this.abierto));
        }
    }"
    class="p-2 sm:p-3 h-full min-h-0 flex flex-col"
>
    <h2 
        @click="toggle()"
        class="text-xl font-bold mb-4 border-b border-gray-300 pb-2 cursor-pointer hover:text-blue-600 transition"
    >
        Administración de Tareas
        <span class="text-sm text-gray-500 ml-2" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
    </h2>

    <div x-show="abierto" x-transition>
        @if (session()->has('message'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-3">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 text-red-800 p-3 rounded mb-3">
                {{ session('error') }}
            </div>
        @endif

        <div class="overflow-x-auto bg-white rounded-lg shadow min-h-64 pb-8">
            <table class="min-w-full border-collapse border border-gray-200 rounded-lg">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">ID</th>
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">ID proyecto</th>
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Proyecto</th>

                        @if($puedeVerTodasLasTareas)
                            <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Usuario</th>
                        @endif

                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Asignado a</th>
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Tipo</th>
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Descripción</th>
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Estado</th>
                        <th class="border-b px-4 py-2 text-center text-sm font-medium text-gray-600">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        <tr class="hover:bg-gray-50">
                            <td class="border-b px-4 py-2 text-gray-700 text-sm">
                                {{ $task->id }}
                            </td>

                            <td class="border-b px-4 py-2 text-gray-700 text-sm">
                                {{ $task->proyecto->id ?? 'Sin proyecto' }}
                            </td>

                            <td class="border-b px-4 py-2 text-gray-700 text-sm">
                                {{ $task->proyecto->nombre ?? 'Sin proyecto' }}
                            </td>

                            @if($puedeVerTodasLasTareas)
                                <td class="border-b px-4 py-2 text-gray-700 text-sm">
                                    {{ $task->proyecto->user->name ?? 'Sin usuario' }}
                                </td>
                            @endif

                            <td class="border-b px-4 py-2 text-gray-700 text-sm">
                                {{ $task->staff->name ?? 'No asignado' }}
                            </td>

                            <td class="border-b px-4 py-2 text-gray-700 text-sm">
                                {{ $task->tipo ?? 'Sin tipo' }}
                            </td>

                            <td class="border-b px-4 py-2 text-gray-700 text-sm">
                                {{ $task->descripcion }}
                            </td>

                            <td class="border-b px-4 py-2 text-gray-700 text-sm">
                                @php
                                    $estado = strtoupper($task->estado ?? 'PENDIENTE');
                                    $clases = [
                                        'PENDIENTE'   => 'bg-yellow-100 text-yellow-800 ring-yellow-600/20',
                                        'EN PROCESO'  => 'bg-blue-100 text-blue-800 ring-blue-600/20',
                                        'COMPLETADA'  => 'bg-emerald-100 text-emerald-800 ring-emerald-600/20',
                                        'RECHAZADO'   => 'bg-rose-100 text-rose-800 ring-rose-600/20',
                                        'CANCELADO'   => 'bg-gray-100 text-gray-800 ring-gray-600/20',
                                    ];
                                    $badge = $clases[$estado] ?? 'bg-gray-100 text-gray-800 ring-gray-600/20';
                                @endphp

                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-semibold ring-1 ring-inset {{ $badge }}">
                                    <span class="h-1.5 w-1.5 rounded-full 
                                        @if($estado==='PENDIENTE') bg-yellow-500
                                        @elseif($estado==='EN PROCESO') bg-blue-500
                                        @elseif($estado==='COMPLETADA') bg-emerald-500
                                        @elseif($estado==='RECHAZADO') bg-rose-500
                                        @elseif($estado==='CANCELADO') bg-gray-500
                                        @else bg-gray-500 @endif">
                                    </span>
                                    {{ $estado }}
                                </span>
                            </td>

                            <td class="border-b px-4 py-2 text-center text-sm">
                                <x-dropdown>
                                    <x-dropdown.item>
                                        <b wire:click="verificarProceso({{ $task->proyecto->id }})">
                                            Ver detalles
                                        </b>
                                    </x-dropdown.item>
                                    
                                    <x-dropdown.item separator>

                                        @case('admin-disenio-cambiar-estado-tarea')
                                            <b wire:click="abrirModal({{ $task->id }})">
                                                Cambiar Estado
                                            </b>
                                        @break

                                    </x-dropdown.item>
                                </x-dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td 
                                colspan="{{ $puedeVerTodasLasTareas ? 9 : 8 }}" 
                                class="px-4 py-6 text-center text-sm text-gray-500"
                            >
                                No hay tareas disponibles.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $tasks->links() }}
        </div>

        @if($modalOpen)
            <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 px-4">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <h2 class="text-lg font-semibold mb-4">Actualizar Estado</h2>

                    <label class="block text-sm font-medium text-gray-700 mb-1">Nuevo Estado</label>
                    <select wire:model="newStatus" class="w-full p-2 border rounded mb-3">
                        @foreach($statuses as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>

                    @error('newStatus')
                        <div class="bg-red-100 text-red-800 p-3 rounded mb-3">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="flex flex-col sm:flex-row justify-end gap-2">
                        <button 
                            wire:click="cerrarModal" 
                            class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded-lg"
                        >
                            Cancelar
                        </button>
                        <button 
                            wire:click="actualizarEstado" 
                            class="w-full sm:w-auto bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg"
                        >
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if($mostrarModalConfirmacion)
            <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 px-4">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <h2 class="text-lg font-semibold mb-4 text-center text-gray-800">
                        ¿Iniciar proceso de diseño?
                    </h2>

                    <p class="text-gray-600 text-center mb-6">
                        Esta acción marcará el proyecto como <strong>"EN PROCESO"</strong> y notificará a los responsables.
                    </p>

                    <div class="flex flex-col sm:flex-row justify-end gap-3">
                        <button 
                            wire:click="cancelarConfirmacion" 
                            class="w-full sm:w-auto bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg"
                        >
                            Cancelar
                        </button>
                        <button 
                            wire:click="confirmarInicioProceso" 
                            class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
                        >
                            Confirmar
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
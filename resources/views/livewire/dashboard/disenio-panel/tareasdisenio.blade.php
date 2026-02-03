<div 
                        x-data="{
                            abierto: JSON.parse(localStorage.getItem('dashboard_admintareasdisenio_abierto') ?? 'true'),
                            toggle() {
                                this.abierto = !this.abierto;
                                localStorage.setItem('dashboard_admintareasdisenio_abierto', JSON.stringify(this.abierto));
                            }
                        }"
                        class="container mx-auto p-6"
                    >
                        <h2 
                            @click="toggle()"
                            class="text-2xl font-bold mb-4 border-b border-gray-300 pb-2 cursor-pointer hover:text-blue-600 transition"
                        >
                           Administración de Tareas
                           <span class="text-sm text-gray-500 ml-2" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
                        </h2>
                            

        <div x-show="abierto" x-transition

                    @if (session()->has('message'))
                        <div class="bg-green-100 text-green-800 p-3 rounded mb-3">
                            {{ session('message') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto bg-white rounded-lg shadow min-h-64 pb-8">
                        <table class="w-full border-collapse border border-gray-300 rounded-lg">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border p-2 text-left">ID</th>
                                    <th class="border p-2 text-left">ID proyecto</th>
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
                                        <td class="border p-2">{{ $task->proyecto->id ?? 'Sin proyecto' }}</td>
                                        <td class="border p-2">{{ $task->proyecto->nombre ?? 'Sin proyecto' }}</td>
                                        <td class="border p-2">{{ $task->staff->name ?? 'No asignado' }}</td>
                                        <td class="border p-2">{{ $task->descripcion }}</td>
                                        <td class="border p-2">
                                            @php
                                                $estado = strtoupper($task->estado ?? 'PENDIENTE');
                                                $clases = [
                                                    'PENDIENTE'   => 'bg-yellow-100 text-yellow-800 ring-yellow-600/20',
                                                    'EN PROCESO'  => 'bg-blue-100 text-blue-800 ring-blue-600/20',
                                                    'COMPLETADA'  => 'bg-emerald-100 text-emerald-800 ring-emerald-600/20',
                                                    'RECHAZADO'   => 'bg-rose-100 text-rose-800 ring-rose-600/20',
                                                ];
                                                $badge = $clases[$estado] ?? 'bg-gray-100 text-gray-800 ring-gray-600/20';
                                            @endphp

                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-semibold ring-1 ring-inset {{ $badge }}">
                                                <span class="h-1.5 w-1.5 rounded-full 
                                                    @if($estado==='PENDIENTE') bg-yellow-500
                                                    @elseif($estado==='EN PROCESO') bg-blue-500
                                                    @elseif($estado==='COMPLETADA') bg-emerald-500
                                                    @elseif($estado==='RECHAZADO') bg-rose-500
                                                    @else bg-gray-500 @endif">
                                                </span>
                                                {{ $estado }}
                                            </span>
                                        </td>

                                        <td class="border p-2 text-center">
                                            {{-- border-b px-4 py-2 --}}
                                            <x-dropdown>
                                                <x-dropdown.item>
                                                    <b 
                                                    wire:click="verificarProceso({{ $task->proyecto->id }})"
                                                    >Ver detalles</b>
                                                </x-dropdown.item>
                                                
                                                <x-dropdown.item separator>
                                                    <b
                                                     wire:click="abrirModal({{ $task->id }})"
                                                    >Cambiar Estado</b>
                                                </x-dropdown.item>
                                            
                                            </x-dropdown>
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
</div>

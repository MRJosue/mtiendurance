<div x-data class="container mx-auto p-6">
    <h2 class="text-2xl font-bold mb-4">Órdenes de Producción</h2>

    @if(session()->has('message'))
        <div class="mb-4 p-2 bg-green-100 text-green-800 rounded">
            {{ session('message') }}
        </div>
    @endif

    <!-- Tabla de órdenes -->
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full border-collapse border border-gray-200 rounded-lg text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border-b px-4 py-2">ID</th>
                    <th class="border-b px-4 py-2">Tipo</th>
                    <th class="border-b px-4 py-2">Estado</th>
                    <th class="border-b px-4 py-2">Flujo</th>
                    <th class="border-b px-4 py-2">Responsable</th>
                    <th class="border-b px-4 py-2">Pedidos</th>
                    <th class="border-b px-4 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ordenes as $orden)
                    <tr class="hover:bg-gray-50">
                        <td class="border-b px-4 py-2">{{ $orden->id }}</td>
                        <td class="border-b px-4 py-2">{{ $orden->tipo }}</td>
                        <td class="border-b px-4 py-2">{{ $orden->estado }}</td>
                        <td class="border-b px-4 py-2">{{ $orden->flujo->nombre ?? '-' }}</td>
                        <td class="border-b px-4 py-2">{{ $orden->usuarioAsignado->name ?? 'No asignado' }}</td>
                        <td class="border-b px-4 py-2">
                            @foreach($orden->pedidos as $pedido)
                                <div class="text-xs">{{ $pedido->id }} ({{ $pedido->proyecto->nombre ?? '-' }})</div>
                            @endforeach
                        </td>
                        <td class="border-b px-4 py-2 space-y-1">
                            <button wire:click="abrirModal({{ $orden->id }})"
                                class="px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 w-full mb-1">
                                Editar
                            </button>
                            @if($orden->estado !== 'TERMINADO' && $orden->estado !== 'CANCELADO')
                                <button wire:click="avanzarEstado({{ $orden->id }})"
                                    class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 w-full mb-1">
                                    Avanzar Estatus
                                </button>
                                <button wire:click="cancelarOrden({{ $orden->id }})"
                                    class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600 w-full">
                                    Cancelar
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-gray-500">No hay órdenes registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $ordenes->links() }}
    </div>

    <!-- Modal -->
    @if($modalOpen)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
                <h3 class="text-lg font-bold mb-4">Editar Orden de Producción</h3>
                <form wire:submit.prevent="guardar">
                    <div class="mb-2">
                        <label class="block text-sm">Responsable</label>
                        <select wire:model="assigned_user_id" class="w-full border rounded p-2">
                            <option value="">Seleccionar usuario</option>
                            @foreach($usuarios as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="block text-sm">Tipo</label>
                        <input type="text" wire:model="tipo" class="w-full border rounded p-2"/>
                    </div>
                    <div class="mb-2">
                        <label class="block text-sm">Estado</label>
                        <select wire:model="estado" class="w-full border rounded p-2">
                            @foreach($estadosDisponibles as $estadoOption)
                                <option value="{{ $estadoOption }}">{{ $estadoOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="block text-sm">Flujo de producción</label>
                        <select wire:model="flujo_id" class="w-full border rounded p-2">
                            <option value="">Sin flujo</option>
                            @foreach($flujos as $flujo)
                                <option value="{{ $flujo->id }}">{{ $flujo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="block text-sm">Descripción</label>
                        <textarea wire:model="descripcion" class="w-full border rounded p-2"></textarea>
                    </div>
                    <div class="flex justify-end space-x-2 mt-4">
                        <button type="button" wire:click="$set('modalOpen', false)" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cerrar</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>

<!-- Script AlpineJS para modales (opcional) -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Aquí podrías poner lógica extra si necesitas para Alpine o Livewire
});
</script>

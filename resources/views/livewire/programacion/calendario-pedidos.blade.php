
<div class="flex flex-col sm:flex-row gap-4 p-6">


    <!-- Lista de pedidos -->
    <div class="w-full sm:w-1/3 bg-white shadow rounded-lg p-4">
        <h2 class="text-lg font-semibold mb-2">Lista de Pedidos</h2>
        <div class="space-y-4">
            @foreach ($pedidos as $pedido)
                <div class="border p-3 rounded-lg shadow-sm">
                    <p><strong>Pedido #{{ $pedido->id }}</strong></p>
                    <p>Cliente: {{ $pedido->cliente->nombre ?? 'Sin Cliente' }}</p>
                    <p>Producto: {{ $pedido->producto->nombre ?? 'Sin Producto' }}</p>
                    <p>Estado:
                        <select wire:model.defer="estados.{{ $pedido->id }}" class="border p-1 rounded">
                            <option value="POR PROGRAMAR">Por Programar</option>
                            <option value="EN PRODUCCIÓN">En Producción</option>
                            <option value="FINALIZADO">Finalizado</option>
                        </select>
                    </p>
                    <p>Fecha de Producción:
                        <input type="date" wire:model.defer="fechasProduccion.{{ $pedido->id }}" class="border p-1 rounded">
                    </p>
                    <button wire:click="actualizarPedido({{ $pedido->id }})" class="mt-2 px-4 py-1 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                        Guardar
                    </button>
                </div>
            @endforeach
        </div>
    </div>

        <!-- Calendario -->
        <div class="w-full sm:w-2/3 bg-white shadow rounded-lg p-4">
            <h2 class="text-lg font-semibold mb-2">Calendario</h2>
            <livewire:programacion.calendario />
        </div>
</div>

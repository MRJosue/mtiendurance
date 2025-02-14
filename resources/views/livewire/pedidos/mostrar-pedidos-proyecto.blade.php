<div class="container mx-auto p-6">
    <h2 class="text-2xl font-bold mb-4 text-gray-800">Pedidos del Proyecto ID: {{ $proyectoId }}</h2>

    @if ($pedidos->isEmpty())
        <p class="text-gray-500 text-center">No hay pedidos relacionados con este proyecto.</p>
    @else
        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
            <table class="min-w-full border border-gray-200 rounded-lg">
                <thead class="bg-gray-100 text-gray-600 uppercase text-sm">
                    <tr>
                        <th class="py-3 px-4 text-left">ID</th>
                        <th class="py-3 px-4 text-left">Cliente</th>
                        <th class="py-3 px-4 text-left">Producto</th>
                        <th class="py-3 px-4 text-left">Categorías</th>
                        <th class="py-3 px-4 text-left">Características</th>
                        <th class="py-3 px-4 text-left">Opciones</th>
                        <th class="py-3 px-4 text-left">Tallas</th>
                        <th class="py-3 px-4 text-left">Fecha Creación</th>
                        <th class="py-3 px-4 text-left">Total</th>
                        <th class="py-3 px-4 text-left">Estatus</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    @foreach ($pedidos as $pedido)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">{{ $pedido->id }}</td>
                            <td class="py-3 px-4">{{ $pedido->cliente->nombre ?? 'Sin datos' }}</td>
                            <td class="py-3 px-4">{{ $pedido->producto->nombre ?? 'Sin datos' }}</td>

                            <!-- Categorías (many-to-many) -->
                            <td class="py-3 px-4">
                                @if ($pedido->producto->categorias->isNotEmpty())
                                    <ul class="list-disc list-inside">
                                        @foreach ($pedido->producto->categorias as $categoria)
                                            <li>{{ $categoria->nombre }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-gray-500">Sin categoría</span>
                                @endif
                            </td>

                            <!-- Características -->
                            <td class="py-3 px-4">
                                @if ($pedido->pedidoCaracteristicas->isNotEmpty())
                                    <ul class="list-disc list-inside">
                                        @foreach ($pedido->pedidoCaracteristicas as $caracteristica)
                                            <li>{{ $caracteristica->caracteristica->nombre ?? 'N/A' }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-gray-500">Sin características</span>
                                @endif
                            </td>

                            <!-- Opciones -->
                            <td class="py-3 px-4">
                                @if ($pedido->pedidoOpciones->isNotEmpty())
                                    <ul class="list-disc list-inside">
                                        @foreach ($pedido->pedidoOpciones as $opcion)
                                            <div>{{ $opcion->opcion->nombre??'N/A' }}</div>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-gray-500">Sin opciones</span>
                                @endif
                            </td>

                            <!-- Tallas -->
                            <td class="py-3 px-4">
                                @if ($pedido->pedidoTallas->isNotEmpty())
                                    <ul class="list-disc list-inside">
                                        @foreach ($pedido->pedidoTallas as $talla)
                                           <div>{{ $talla->talla->nombre ?? 'N/A' }}: {{ $talla->cantidad ?? '0' }}</div>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-gray-500">Sin tallas</span>
                                @endif
                            </td>

                            <td class="py-3 px-4">{{ $pedido->fecha_creacion }}</td>
                            <td class="py-3 px-4 font-semibold">${{ number_format($pedido->total, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 rounded-lg text-xs font-bold 
                                    {{ $pedido->estatus === 'PENDIENTE' ? 'bg-yellow-200 text-yellow-800' : 'bg-green-200 text-green-800' }}">
                                    {{ strtoupper($pedido->estatus) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

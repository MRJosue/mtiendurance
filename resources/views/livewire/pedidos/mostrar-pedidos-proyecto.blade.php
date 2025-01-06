<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Pedidos del Proyecto ID: {{ $proyectoId }}</h2>

    @if ($pedidos->isEmpty())
        <p class="text-gray-500">No hay pedidos relacionados con este proyecto.</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300">
                <thead>
                    <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">ID</th>
                        <th class="py-3 px-6 text-left">Cliente</th>
                        <th class="py-3 px-6 text-left">Producto</th>
                        <th class="py-3 px-6 text-left">Categoría</th>
                        <th class="py-3 px-6 text-left">Características</th>
                        <th class="py-3 px-6 text-left">Opcion</th>
                        <th class="py-3 px-6 text-left">Tallas</th>
                        <th class="py-3 px-6 text-left">Fecha de Creación</th>
                        <th class="py-3 px-6 text-left">Total</th>
                        <th class="py-3 px-6 text-left">Estatus</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    @foreach ($pedidos as $pedido)
                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-3 px-6 text-left whitespace-nowrap">{{ $pedido->id }}</td>
                            <td class="py-3 px-6 text-left">{{ $pedido->cliente->nombre ?? 'N/A' }}</td>
                            <td class="py-3 px-6 text-left">{{ $pedido->producto->nombre ?? 'N/A' }}</td>
                            <td class="py-3 px-6 text-left">{{ $pedido->producto->categoria->nombre ?? 'N/A' }}</td>
                            <td class="py-3 px-6 text-left">
                                @if ($pedido->pedidoCaracteristicas->isNotEmpty())
                                    <ul class="list-disc pl-4">
                                        @foreach ($pedido->pedidoCaracteristicas as $caracteristica)
                                            <li>{{ $caracteristica->caracteristica->nombre ?? 'N/A' }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    N/A
                                @endif
                            </td>

                            <td class="py-3 px-6 text-left">
                                @if ($pedido->pedidoOpciones->isNotEmpty())
                                <ul class="list-disc pl-4">
                                    @foreach ($pedido->pedidoOpciones as $opcion)
                                        <li>{{ $opcion->opcion->nombre ?? 'N/A' }}</li>
                                    @endforeach
                                </ul>
                            @else
                                N/A
                            @endif
                            </td>

                            <td class="py-3 px-6 text-left">
                                @if ($pedido->pedidoTallas->isNotEmpty())
                                    <ul class="list-disc pl-4">
                                        @foreach ($pedido->pedidoTallas as $talla)
                                            <li>{{ $talla->talla->nombre ?? 'N/A' }}: {{ $talla->cantidad ?? '0' }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    N/A
                                @endif
                            </td>

                            <td class="py-3 px-6 text-left">{{ $pedido->fecha_creacion }}</td>
                            <td class="py-3 px-6 text-left">{{ $pedido->total }}</td>
                            <td class="py-3 px-6 text-left">{{ $pedido->estatus }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

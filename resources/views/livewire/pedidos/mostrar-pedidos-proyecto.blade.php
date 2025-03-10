<div class="container mx-auto p-6">
 

    @if ($pedidos->isEmpty())
        <p class="text-gray-500 text-center">No hay pedidos relacionados con este proyecto.</p>
    @else
        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
            <table class="min-w-full border border-gray-200 rounded-lg">
                <thead class="bg-gray-100 text-gray-600 uppercase text-sm">
                    <tr>
                        <th class="py-4 px-4 text-left  w-32">ID</th>
                        <th class="py-3 px-4 text-left">Cliente</th>
                        <th class="py-3 px-4 text-left">Fecha de Produccion</th>
                        <th class="py-3 px-4 text-left">Fecha de Embarque</th>
                        <th class="py-3 px-4 text-left">Fecha de Entrega</th>
                        <th class="py-3 px-4 text-left">Tallas</th>
                        <th class="py-3 px-4 text-left">No de piezas</th>
                        <th class="py-3 px-4 text-left">Estatus</th>
                        <th class="py-3 px-4 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    @foreach ($pedidos as $pedido)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4 w-32"><p>{{$pedido->proyecto_id.'-'.$pedido->id }}</p></td>
                            <td class="py-3 px-4">{{ $pedido->cliente->nombre_empresa ?? 'Sin datos' }}</td>

                            <!-- Categorías (many-to-many) -->
                            <td class="py-3 px-4">
                                @if ($pedido->fecha_produccion) {{-- Verifica si existe la categoría --}}
                                    <span>{{ $pedido->fecha_produccion}}</span>
                                @else
                                    <span class="text-gray-500">Sin Fecha</span>
                                @endif
                            </td>

                            <!-- Características -->
                            <td class="py-3 px-4">
                                @if ($pedido->fecha_embarque) {{-- Verifica si existe la categoría --}}
                                    <span>{{ $pedido->fecha_embarque}}</span>
                                @else
                                    <span class="text-gray-500">Sin Fecha</span>
                                @endif
                            </td>

                            <!-- Opciones -->
                            <td class="py-3 px-4">
                                @if ($pedido->fecha_embarque) {{-- Verifica si existe la categoría --}}
                                    <span>{{ $pedido->fecha_embarque}}</span>
                                @else
                                    <span class="text-gray-500">Sin Fecha</span>
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

                     
                            <td class="py-3 px-4 font-semibold">{{ number_format($pedido->total, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 rounded-lg text-xs font-bold 
                                    {{ $pedido->estatus === 'PENDIENTE' ? 'bg-yellow-200 text-yellow-800' : 'bg-green-200 text-green-800' }}">
                                    {{ strtoupper($pedido->estatus) }}
                                </span>
                            </td>
                             <th class="py-3 px-4">
                              <a href="#">Aprobar produccion</a>
                              <a href="#">Detalles</a> 
                            </th>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="w-full p-6">
    <h2 class="text-2xl font-bold mb-4">Gestión de Pedidos</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-3">
            {{ session('message') }}
        </div>
    @endif

    {{-- Botón Nuevo Pedido --}}
    <div class="flex items-center justify-between mb-4">
        <button
            wire:click="abrirModal"
            class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded"
        >
            Nuevo Pedido
        </button>
    </div>

    {{-- Tabla de Pedidos (sin responsividad) --}}
    <div class="overflow-x-auto bg-white rounded shadow">
        <table class="min-w-full table-auto divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="p-2 text-left">ID</th>
                    <th class="p-2 text-left">Proyecto</th>
                    <th class="p-2 text-left">Producto / Categoría</th>
                    <th class="p-2 text-left">Características</th>
                    <th class="p-2 text-left">Cliente</th>
                    <th class="p-2 text-left">Piezas Totales</th>
                    <th class="p-2 text-left">Estado Diseño</th>
                    <th class="p-2 text-center">Estado Pedido</th>
                    @can('proyectopedidoscolumnafechaproduccion')
                        <th class="p-2 text-left">Producción</th>
                    @endcan
                    @can('proyectopedidoscolumnafechaenbarque')
                        <th class="p-2 text-left">Embarque</th>
                    @endcan
                    @can('proyectopedidoscolumnafechaEntrega')
                        <th class="p-2 text-left">Entrega</th>
                    @endcan
                    <th class="p-2 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($pedidos as $pedido)
                    <tr class="hover:bg-gray-50">
                        <td class="p-2 font-semibold"
                            title="Proyecto {{ $pedido->proyecto_id }} - Pedido #{{ $pedido->id }}: {{ $pedido->descripcion_corta }}"
                        >
                            {{ $pedido->proyecto_id }}-{{ $pedido->id }}
                        </td>
                        <td class="p-2">{{ $pedido->proyecto->nombre }}</td>
                        <td class="p-2">
                            <div>{{ $pedido->producto->nombre ?? 'Sin producto' }}</div>
                            <div class="text-xs text-gray-500">{{ $pedido->producto->categoria->nombre ?? 'Sin categoría' }}</div>
                        </td>
                        <td class="p-2 text-xs text-gray-700">
                            @if($pedido->pedidoCaracteristicas->isNotEmpty())
                                <ul class="list-disc list-inside">
                                    @foreach($pedido->pedidoCaracteristicas as $car)
                                        <li>{{ $car->caracteristica->nombre }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-gray-400">Sin características</span>
                            @endif
                        </td>
                        <td class="p-2">{{ $pedido->usuario->name ?? 'Sin usuario' }}</td>
                        <td class="p-2">{{ $pedido->total }}</td>
                        <td class="p-2">
                            <span class="px-2 py-1 rounded text-xs text-white font-semibold {{
                                collect([
                                    'PENDIENTE' => 'bg-yellow-400 text-black',
                                    'ASIGNADO' => 'bg-blue-500',
                                    'EN PROCESO' => 'bg-orange-500',
                                    'REVISION' => 'bg-purple-600',
                                    'DISEÑO APROBADO' => 'bg-emerald-600',
                                    'DISEÑO RECHAZADO' => 'bg-red-600',
                                    'CANCELADO' => 'bg-gray-500',
                                ])->get(strtoupper($pedido->proyecto->estado), 'bg-yellow-400 text-black')
                            }}">
                                {{ strtoupper($pedido->proyecto->estado) }}
                            </span>
                        </td>
                        <td class="p-2 text-center">
                            <span class="px-2 py-1 rounded text-xs text-white font-semibold" style="background-color:
                                @if($pedido->estado==='APROBADO') #10B981
                                @elseif($pedido->estado==='ENTREGADO') #3B82F6
                                @elseif($pedido->estado==='RECHAZADO') #EF4444
                                @elseif($pedido->estado==='ARCHIVADO') #6B7280
                                @else #FBBF24 @endif;">
                                {{ strtoupper($pedido->estado) }}
                            </span>
                        </td>
                        @can('proyectopedidoscolumnafechaproduccion')
                            <td class="p-2">{{ $pedido->fecha_produccion ?? 'No definida' }}</td>
                        @endcan
                        @can('proyectopedidoscolumnafechaenbarque')
                            <td class="p-2">{{ $pedido->fecha_embarque ?? 'No definida' }}</td>
                        @endcan
                        @can('proyectopedidoscolumnafechaEntrega')
                            <td class="p-2">{{ $pedido->fecha_entrega ?? 'No definida' }}</td>
                        @endcan
                        <td class="p-2 flex justify-center space-x-2">
                            @hasanyrole('admin')
                                <button
                                    wire:click="abrirModal({{ $pedido->id }})"
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-3 py-1 rounded text-xs"
                                >Editar</button>
                            @endhasanyrole

                            @if ($pedido->estado == 'POR APROBAR' && $pedido->proyecto->estado === 'DISEÑO APROBADO')
                                <button
                                    wire:click="confirmarAprobacion({{ $pedido->id }})"
                                    class="bg-green-600 hover:bg-green-700 text-white font-semibold px-3 py-1 rounded text-xs"
                                >Aprobar</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    <div class="mt-4">
        {{ $pedidos->links() }}
    </div>

    {{-- Modales (igual que antes) --}}
    @if($modal)
        {{-- ... --}}
    @endif
    @if($modal_confirmar_aprobacion)
        {{-- ... --}}
    @endif
    @if($modal_reconfigurar_proyecto)
        {{-- ... --}}
    @endif
</div>

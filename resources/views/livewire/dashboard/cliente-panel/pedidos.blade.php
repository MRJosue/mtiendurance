<div 
    x-data="{
        abierto: JSON.parse(localStorage.getItem('dashboard_pedidos_abierto') ?? 'true'),
        toggle() {
            this.abierto = !this.abierto;
            localStorage.setItem('dashboard_pedidos_abierto', JSON.stringify(this.abierto));
        }
    }"
    class="container mx-auto p-6"
>
    <h2 
        @click="toggle()"
        class="text-xl font-bold mb-4 border-b border-gray-300 pb-2 cursor-pointer hover:text-blue-600 transition"
    >
        Pedidos de Proyecto
    <span class="text-sm text-gray-500 ml-2" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
    </h2>

    <!-- Contenido colapsable -->
    <div x-show="abierto" x-transition>
        <div class="overflow-x-auto bg-white rounded shadow">
            <table class="min-w-full table-auto text-sm text-left text-gray-700">
                <thead class="bg-gray-100 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-2">ID Proyecto</th>
                        <th class="px-4 py-2">ID Pedido</th>
                        <th class="px-4 py-2">Producto / Categoría</th>
                        <th class="px-4 py-2">Características</th>
                        <th class="px-4 py-2">Total</th>
                        <th class="px-4 py-2">Estado</th>
                        <th class="px-4 py-2">Producción</th>
                        <th class="px-4 py-2">Entrega</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidos as $pedido)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-semibold">{{ $pedido->proyecto_id }}</td>
                            <td class="px-4 py-2 font-semibold">{{ $pedido->id }}</td>
                            <td class="px-4 py-2">
                                <div class="font-medium">{{ $pedido->producto->nombre ?? 'Sin producto' }}</div>
                                <div class="text-xs text-gray-500">{{ $pedido->producto->categoria->nombre ?? 'Sin categoría' }}</div>
                            </td>
                            <td class="px-4 py-2">
                                @if($pedido->pedidoCaracteristicas->isNotEmpty())
                                    <ul class="list-disc list-inside text-xs">
                                        @foreach($pedido->pedidoCaracteristicas as $caracteristica)
                                            <li>
                                                {{ $caracteristica->caracteristica->nombre ?? 'Sin nombre' }}
                                                @php
                                                    $opciones = $pedido->pedidoOpciones->filter(fn($op) =>
                                                        $op->opcion &&
                                                        $op->opcion->caracteristicas->pluck('id')->contains($caracteristica->caracteristica_id)
                                                    );
                                                @endphp
                                                @if($opciones->isNotEmpty())
                                                    <ul class="list-inside text-gray-500 ml-3">
                                                        @foreach($opciones as $opcion)
                                                            <li>{{ $opcion->opcion->nombre ?? 'Sin opción' }}</li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-gray-400 text-xs">Sin características</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">{{ $pedido->total }} piezas</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded text-xs text-white"
                                      style="background-color:
                                      @if($pedido->estado === 'APROBADO') #10B981
                                      @elseif($pedido->estado === 'ENTREGADO') #3B82F6
                                      @elseif($pedido->estado === 'RECHAZADO') #EF4444
                                      @elseif($pedido->estado === 'ARCHIVADO') #6B7280
                                      @else #FBBF24 @endif;">
                                      {{ strtoupper($pedido->estado) }}
                                </span>
                            </td>
                            <td class="px-4 py-2">{{ $pedido->fecha_produccion ?? 'No definida' }}</td>
                            <td class="px-4 py-2">{{ $pedido->fecha_entrega ?? 'No definida' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-4 text-center text-gray-500">No hay pedidos disponibles.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $pedidos->links() }}
        </div>
    </div>
</div>

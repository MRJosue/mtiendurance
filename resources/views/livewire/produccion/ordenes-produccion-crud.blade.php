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
                    <th class="border-b px-4 py-2">Fecha de Inicio</th>
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
                       <td class="border-b px-4 py-2">{{ $orden->fecha_sin_iniciar ? \Carbon\Carbon::parse($orden->fecha_sin_iniciar)->format('d/m/Y') : '-' }}</td>
                        <td class="border-b px-4 py-2">{{ $orden->flujo->nombre ?? '-' }}</td>
                        <td class="border-b px-4 py-2">{{ $orden->usuarioAsignado->name ?? 'No asignado' }}</td>
                        <td class="border-b px-4 py-2">
                            @foreach($orden->pedidos as $pedido)
                                <div class="text-xs leading-tight">
                                    #{{ $pedido->proyecto_id }}-{{ $pedido->id }}
                                    <span class="text-gray-700">{{ $pedido->proyecto->nombre ?? '-' }}</span><br>
                                    <span class="text-gray-500 italic">{{ $pedido->producto->nombre ?? 'Sin producto' }}</span>
                                </div>
                            @endforeach
                        </td>
                        <td class="border-b px-4 py-2 space-y-0.5">

                            <button 
                                wire:click="verCaracteristicas({{ $orden->id }})"
                                class="px-1 py-1 text-xs bg-violet-500 text-white rounded hover:bg-violet-600 w-full flex items-center justify-center mb-0.5"
                                title="Ver características"
                            >
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                                Características
                            </button>
                            {{-- <button wire:click="abrirModal({{ $orden->id }})"
                                class="px-1 py-1 text-xs bg-blue-500 text-white rounded hover:bg-blue-600 w-full flex items-center justify-center">
                          
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6-6M19 13V19a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h6"></path></svg>
                                Editar
                            </button> --}}
                            @if($orden->estado !== 'TERMINADO' && $orden->estado !== 'CANCELADO')
                                <button wire:click="avanzarEstado({{ $orden->id }})"
                                    class="px-1 py-1 text-xs bg-green-500 text-white rounded hover:bg-green-600 w-full flex items-center justify-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a4 4 0 014-4h3" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5" />
                                    </svg>
                                    Avanzar
                                </button>
                                <button wire:click="cancelarOrden({{ $orden->id }})"
                                    class="px-1 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600 w-full flex items-center justify-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
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
                        <button type="button" wire:click="$set('modalOpen', false)" class="px-2 py-1 text-xs bg-gray-200 rounded hover:bg-gray-300">Cerrar</button>
                        <button type="submit" class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif


    @if($modalCaracteristicas && $ordenCaracteristicas)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-2xl overflow-y-auto max-h-[80vh]">
                <h3 class="text-lg font-bold mb-4">Características de la Orden de {{ $orden->tipo }} #{{ $ordenCaracteristicas->id }}  </h3>
                
                @forelse($ordenCaracteristicas->pedidos as $pedido)
                    <div class="mb-4 border-b pb-2">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between text-sm font-bold text-gray-800 mb-1 gap-2">
                            <div>
                                Pedido #{{ $pedido->proyecto_id }}-{{ $pedido->id }} ({{ $pedido->proyecto->nombre ?? '-' }})

              

                                <livewire:proyectos.liga-archivo-aprobado :proyecto-id="$pedido->proyecto_id" />
                            </div>
                            <div>
                                <span class="bg-slate-200 text-blue-900 px-2 py-0.5 rounded text-md font-semibold inline-block">
                                    Total piezas: {{ $pedido->total ?? '-' }}
                                </span>
                            </div>
                        </div>

         

                        <div class="text-sm font-bold text-gray-800 mb-1 flex items-center gap-2">
                             Fecha de embarque: {{ $pedido->fecha_embarque }}
                        </div>


                        <div class="text-sm font-bold text-gray-800 mb-1 flex items-center gap-2">
                            Caracteristicas:
                        </div>
                        @if($pedido->pedidoCaracteristicas->isNotEmpty())
                            <ul class="text-xs text-gray-700 space-y-2 p-1">
                                @foreach($pedido->pedidoCaracteristicas->chunk(2) as $chunk)
                                    <li class="flex gap-4">
                                        @foreach($chunk as $pc)
                                            <div class="w-1/2">
                                                <span class="font-bold">{{ $pc->caracteristica->nombre ?? 'Sin característica' }}</span>
                                                @php
                                                    $opcionesRelacionadas = $pedido->pedidoOpciones
                                                        ->filter(fn($po) =>
                                                            $po->opcion &&
                                                            $po->opcion->caracteristicas->pluck('id')->contains($pc->caracteristica_id)
                                                        );
                                                @endphp

                                                @if($opcionesRelacionadas->isNotEmpty())
                                                    <ul class="ml-4 list-disc list-inside">
                                                        @foreach($opcionesRelacionadas as $opcion)
                                                            <li>{{ $opcion->opcion->nombre ?? 'Sin opción' }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <span class="ml-2 text-gray-400">(Sin opciones)</span>
                                                @endif


   
                                            </div>
                                        @endforeach
                                    </li>
                                @endforeach

                            </ul>
                        @else
                            <div class="text-xs text-gray-400">Sin características</div>
                        @endif
                        
            
                        @if($pedido->tallas_agrupadas  != '[]')
                       
                            <div class="mt-2 text-sm font-bold text-gray-800">Tallas:</div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mt-1">
                                @foreach($pedido->tallas_agrupadas as $grupo)
                                    @foreach($grupo['tallas'] as $talla)
                                        <div class="text-xs bg-gray-100 p-2 rounded shadow-sm">
                                            <div class="font-semibold text-gray-700">
                                                {{ $grupo['grupo_nombre'] }} - {{ $talla['nombre'] }}
                                            </div>
                                            <div class="text-blue-600 font-bold">Cantidad: {{ $talla['cantidad'] }}</div>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                        @endif



                    </div>
                @empty
                    <div class="text-center text-gray-500 py-4">No hay pedidos en esta orden.</div>
                @endforelse

                @php
                $totalPiezasOrden = $ordenCaracteristicas->pedidos->sum('total');
                @endphp
                <div class="mt-4 text-right font-bold text-lg text-gray-800">
                    Total de piezas de la orden: {{ $totalPiezasOrden }}
                </div>
                
                <div class="flex justify-end mt-4">
                    <button type="button" wire:click="$set('modalCaracteristicas', false)"
                        class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 text-xs">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif


    @if($modalEntrega && $ordenEntrega)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-2xl max-h-[80vh] overflow-y-auto">
                <h3 class="text-lg font-bold mb-4">Confirmar Entrega - Orden de {{ $orden->tipo }} #{{ $ordenEntrega->id }}</h3>

                @foreach($ordenEntrega->pedidos as $pedido)
                    <div class="mb-4 border-b pb-2">
                        <div class="text-sm font-bold text-gray-800">
                           Pedido #{{ $pedido->proyecto_id }}-{{ $pedido->id }}  ({{ $pedido->proyecto->nombre ?? '-' }}) - {{ $pedido->producto->nombre ?? 'Sin producto' }}
                        </div>
                        <div class="text-xs text-gray-700 mb-1">
                            Total piezas: {{ $pedido->total }}
                        </div>


                        <div class="text-sm font-bold text-gray-800 mb-1 flex items-center gap-2">
                            Caracteristicas:
                        </div>
                        @if($pedido->pedidoCaracteristicas->isNotEmpty())
                            <ul class="text-xs text-gray-700 space-y-2 p-1">
                                @foreach($pedido->pedidoCaracteristicas->chunk(2) as $chunk)
                                    <li class="flex gap-4">
                                        @foreach($chunk as $pc)
                                            <div class="w-1/2">
                                                <span class="font-bold">{{ $pc->caracteristica->nombre ?? 'Sin característica' }}</span>
                                                @php
                                                    $opcionesRelacionadas = $pedido->pedidoOpciones
                                                        ->filter(fn($po) =>
                                                            $po->opcion &&
                                                            $po->opcion->caracteristicas->pluck('id')->contains($pc->caracteristica_id)
                                                        );
                                                @endphp

                                                @if($opcionesRelacionadas->isNotEmpty())
                                                    <ul class="ml-4 list-disc list-inside">
                                                        @foreach($opcionesRelacionadas as $opcion)
                                                            <li>{{ $opcion->opcion->nombre ?? 'Sin opción' }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <span class="ml-2 text-gray-400">(Sin opciones)</span>
                                                @endif


   
                                            </div>
                                        @endforeach
                                    </li>
                                @endforeach


                                <div class="text-xs text-gray-700 mb-1">
                                    Total piezas: {{ $pedido->total }}
                                </div>

                                @if($pedido->tallas_agrupadas && $pedido->tallas_agrupadas->isNotEmpty())
                                    <div class="mt-2 text-sm font-bold text-gray-800">Tallas entregadas:</div>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mt-1">
                                        @foreach($pedido->tallas_agrupadas as $grupo)
                                            @foreach($grupo['tallas'] as $talla)
                                                <div class="text-xs bg-gray-100 p-2 rounded shadow-sm">
                                                    <div class="font-semibold text-gray-700">
                                                        {{ $grupo['grupo_nombre'] }} - {{ $talla['nombre'] }}
                                                    </div>
                                                    <div class="text-blue-600 text-xs mb-1">Esperadas: {{ $talla['cantidad'] }}</div>
                                                    <input type="number"
                                                        min="0"
                                                        max="{{ $talla['cantidad'] }}"
                                                        wire:model.defer="cantidadesTallasEntregadas.{{ $pedido->id }}.{{ $grupo['grupo_nombre'] }}.{{ $talla['nombre'] }}"
                                                        class="w-full border px-2 py-1 rounded text-xs"
                                                    />
                                                </div>
                                            @endforeach
                                        @endforeach
                                    </div>

                                @else
                                    <div class="mb-2">
                                        <label class="block text-xs text-gray-600">Piezas entregadas</label>
                                        <input type="number" wire:model.defer="piezasEntregadas.{{ $pedido->id }}"
                                            class="w-full border rounded px-2 py-1 text-sm" min="0" max="{{ $pedido->total }}">
                                    </div>
                                @endif


                            </ul>
                        @else
                            <div class="text-xs text-gray-400">Sin características</div>
                        @endif


                        
                    </div>
                @endforeach

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" wire:click="$set('modalEntrega', false)"
                        class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 text-xs">
                        Cancelar
                    </button>
                    <button type="button" wire:click="confirmarEntrega"
                        class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-xs">
                        Confirmar Entrega y Terminar
                    </button>
                </div>
            </div>
        </div>
    @endif


</div>

<!-- Script AlpineJS para modales -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Aquí podrías poner lógica extra si necesitas para Alpine o Livewire
});
</script>
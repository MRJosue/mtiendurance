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

                
                        @if($mostrarFiltros)
                            <div 
                                x-data="{ abierto: @entangle('mostrarFiltros') }" 
                                class="mb-6"
                            >

                            
                            <template x-if="abierto">
                                <div 
                                  
                                    class="w-full bg-white border border-gray-200 shadow-md rounded-lg"
                                >
                                    <div class="flex justify-between items-center p-4 border-b">
                                        <h2 class="text-lg font-bold text-gray-700">Filtros</h2>
                                        <div class="flex items-center gap-2">
                                            <button 
                                                wire:click="buscarPorFiltros"
                                                class="bg-white border border-gray-300 text-gray-700 px-3 py-1 rounded hover:bg-gray-100 text-sm"
                                            >
                                                Filtrar
                                            </button>
                                            <button 
                                                @click="abierto = false" 
                                                class="text-gray-500 hover:text-gray-700 text-xl leading-none"
                                            >
                                                ✕
                                            </button>
                                        </div>
                                    </div>

                                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                               <div class="flex items-center space-x-2">
                                                    <input
                                                        type="checkbox"
                                                        id="no-aprobados"
                                                        wire:model.defer="mostrarSoloNoAprobados"
                                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                                    />
                                                    <label for="no-aprobados" class="text-sm text-gray-700">
                                                        Mostrar pedidos de diseños No aprobados
                                                    </label>
                                                </div>
                                    </div>
                                </div>
                            </template>
                                <template x-if="!abierto">
                                    <div class="mb-4">
                                        <button @click="abierto = true" class="text-sm text-blue-600 hover:underline">
                                            Mostrar Filtros
                                        </button>
                                    </div>
                                </template>
                            </div>
                        @else
                            <div class="mb-4">
                                <button wire:click="$set('mostrarFiltros', true)" class="text-sm text-blue-600 hover:underline">
                                    Mostrar Filtros
                                </button>
                            </div>
                        @endif

                <!-- PESTAÑAS PEDIDOS | MUESTRAS -->
            <ul class="flex flex-wrap border-b border-gray-200 mb-4 gap-1">
                @foreach ($this->tabs as $tab)
                    <li>
                        <button
                            wire:click="setTab('{{ $tab }}')"
                            @class([
                                'px-2 py-1 rounded-t-lg text-sm whitespace-nowrap',
                                'border-b-2 font-semibold bg-white' => $activeTab === $tab,
                                'text-gray-600 hover:text-blue-500' => $activeTab !== $tab,
                                'border-blue-500 text-blue-600'     => $activeTab === $tab,
                                'border-transparent'                => $activeTab !== $tab,
                            ])
                        >
                            {{ $tab }}
                        </button>
                    </li>
                @endforeach
            </ul>
        <div class="overflow-x-auto bg-white rounded shadow">
            <table class="min-w-full table-fixed text-sm text-left text-gray-700">
                <thead class="bg-gray-100 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-2 py-1 w-24">ID </th>
                       
                        <th class="px-2 py-1">Nombre del proyecto</th>
                        <th class="px-2 py-1">Cliente</th>
                        <th class="px-2 py-1">Producto / Categoría</th>
                        {{-- <th class="px-2 py-1">Características</th> --}}
                        <th class="px-2 py-1">Total</th>
                        <th class="px-2 py-1">Estado del Diseño</th>
                        <th class="px-2 py-1">Estado del Pedido</th>
                        
                        <th class="px-2 py-1">Producción</th>
                        <th class="px-2 py-1">Entrega</th>
                        <th class="px-2 py-1">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-xs">
                    @forelse($pedidos as $pedido)
                        <tr class="hover:bg-gray-50">
                            <td
                                class="p-2 px-4 py-2 font-semibold min-w-[4rem]"
                                title="{{ $pedido->tooltip_clave }}"
                            >
                                {!! $pedido->clave_link !!}
                            </td>
                            <td class="px-2 py-1 font-bold">{{$pedido->proyecto->nombre}}</td>
                            <td class="px-2 py-1 font-bold">{{$pedido->usuario->name ?? 'Sin cliente'}}</td>
                            <td class="px-2 py-1">
                                <div class="font-medium">{{ $pedido->producto->nombre ?? 'Sin producto' }}</div>
                                <div class="text-xs text-gray-500">{{ $pedido->producto->categoria->nombre ?? 'Sin categoría' }}</div>
                            </td>
                            {{-- <td class="px-2 py-1 align-top">
                                @if($pedido->pedidoCaracteristicas->isNotEmpty())
                                    <ul class="list-none space-y-1 text-xs">
                                        @foreach($pedido->pedidoCaracteristicas as $index => $caracteristica)
                                            <li x-data="{ abierto_{{ $index }}: false }">
                                                <button 
                                                    @click="abierto_{{ $index }} = !abierto_{{ $index }}" 
                                                    class="flex items-center gap-1 text-left w-full text-gray-700 hover:text-blue-600 transition"
                                                >
                                                    <svg :class="{ 'rotate-90': abierto_{{ $index }} }" class="w-4 h-4 transform transition-transform duration-200 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                    <span>{{ $caracteristica->caracteristica->nombre ?? 'Sin nombre' }}</span>
                                                </button>

                                                @php
                                                    $opciones = $pedido->pedidoOpciones->filter(fn($op) =>
                                                        $op->opcion &&
                                                        $op->opcion->caracteristicas->pluck('id')->contains($caracteristica->caracteristica_id)
                                                    );
                                                @endphp

                                                @if($opciones->isNotEmpty())
                                                    <ul x-show="abierto_{{ $index }}" x-transition class="ml-6 mt-1 list-disc text-gray-500">
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
                            </td> --}}
                            <td class="px-2 py-1">{{ $pedido->total }} piezas</td>

                            <td class="px-2 py-1 ">
                                @php
                                    $estado = strtoupper($pedido->proyecto->estado);
                                    $colores = [
                                        'PENDIENTE'         => 'bg-yellow-400 text-black',     // 🟡 En espera de atención
                                        'ASIGNADO'          => 'bg-blue-500 text-white',       // 🔵 Ya hay responsable
                                        'EN PROCESO'        => 'bg-orange-500 text-white',     // 🟠 En ejecución
                                        'REVISION'          => 'bg-purple-600 text-white',     // 🟣 Validación en curso
                                        'DISEÑO APROBADO'   => 'bg-emerald-600 text-white',    // ✅ Diseño final listo
                                        'DISEÑO RECHAZADO'  => 'bg-red-600 text-white',        // ❌ Cambio o corrección
                                        'CANCELADO'         => 'bg-gray-500 text-white',       // ⚫ Terminado sin continuar
                                    ];
                                    $claseColor = $colores[$estado] ?? 'bg-yellow-400';
                                @endphp

                                <span class="px-2 py-1 rounded text-xs text-white font-semibold {{ $claseColor }}">
                                    {{ $estado }}
                                </span>
                            </td>

                            <td class="px-2 py-1">
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





                            <td class="px-2 py-1">{{ $pedido->fecha_produccion ?? 'No definida' }}</td>
                            <td class="px-2 py-1">{{ $pedido->fecha_entrega ?? 'No definida' }}</td>
                            <td>

      
                                    <div class="relative group inline-block">
                                        <x-mini-button rounded icon="clipboard" flat red interaction="negative"  href="{{ route('proyecto.show', $pedido->proyecto_id) }}"  />
                                        <div class="absolute z-10 w-max left-1/2 -translate-x-1/2 -top-8 px-2 py-1 text-xs bg-gray-800 text-white rounded shadow opacity-0 group-hover:opacity-100 pointer-events-none transition">
                                            Ir a Diseño
                                        </div>
                                    </div>

                                    <div class="relative group inline-block">
                                        <button 
                                            wire:click="abrirModalVerInfo({{ $pedido->proyecto_id }})"
                                            type="button"
                                            class="focus:outline-none"
                                        >
                                            <x-mini-button rounded icon="information-circle" flat blue interaction="negative"    wire:click="abrirModalVerInfo({{ $pedido->proyecto_id }})"/>
                                        </button>
                                        <div class="absolute z-10 w-max left-1/2 -translate-x-1/2 -top-8 px-2 py-1 text-xs bg-gray-800 text-white rounded shadow opacity-0 group-hover:opacity-100 pointer-events-none transition">
                                            Ver información
                                        </div>
                                    </div>

                            </td>
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


    @if($modalVerInfo && $infoProyecto)
    <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white p-6 rounded shadow-lg w-full max-w-2xl relative overflow-y-auto max-h-[90vh]">
            <h2 class="text-xl font-bold mb-4">Detalles del Proyecto</h2>
            <button 
                wire:click="$set('modalVerInfo', false)" 
                class="absolute top-3 right-4 text-gray-500 hover:text-red-600 text-2xl leading-none"
                title="Cerrar"
            >&times;</button>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-lg"><span class="font-semibold">Cliente:</span> {{ $infoProyecto->user->name ?? 'Sin usuario' }}</p>
                </div>
                <div>
                    <p class="text-lg"><span class="font-semibold">Proyecto:</span> {{ $infoProyecto->nombre }} <span class="text-sm font-bold">ID:{{ $infoProyecto->id }}</span></p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-lg"><span class="font-semibold">Descripción:</span> {{ $infoProyecto->descripcion }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-lg font-semibold">Categoría:</p>
                    <p>{{ $infoProyecto->categoria_sel['nombre'] ?? $infoProyecto->categoria->nombre ?? 'Sin categoría' }}</p>
                </div>
                <div>
                    <p class="text-lg font-semibold">Producto:</p>
                    <p>{{ $infoProyecto->producto_sel['id'] ?? $infoProyecto->producto->id ?? '' }} {{ $infoProyecto->producto_sel['nombre'] ?? $infoProyecto->producto->nombre ?? 'Sin producto' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-2">
                @foreach($infoProyecto->caracteristicas_sel ?? [] as $caracteristica)
                    <div class="p-4 border rounded-lg shadow bg-gray-50">
                        <h3 class="text-lg font-semibold">{{ $caracteristica['nombre'] }}</h3>
                        <ul class="mt-2 list-disc list-inside">
                            @foreach($caracteristica['opciones'] ?? [] as $opcion)
                                <li><span class="font-medium">{{ $opcion['nombre'] }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

</div>

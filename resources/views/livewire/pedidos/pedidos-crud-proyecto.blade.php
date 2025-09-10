<div class="container mx-auto p-6">
    <h2 class="text-2xl font-bold mb-4">Gestión de Pedidos</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-3">
            {{ session('message') }}
        </div>
    @endif

    {{-- Botón Nuevo Pedido --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 space-y-2 sm:space-y-0">
        <button
            wire:click="abrirModal"
            class="w-full sm:w-auto bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded"
        >
            Nuevo Pedido
        </button>
    </div>

    {{-- Sección de Filtros --}}
    @if($mostrarFiltros)
        <div x-data="{ abierto: @entangle('mostrarFiltros') }" class="mb-6">
            <template x-if="abierto">
                <div class="w-full bg-white border border-gray-200 shadow-md rounded-lg">
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
                        <!-- Agrega más filtros aquí -->
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

    {{-- VISTA MÓVIL: Tarjetas --}}
    <div class="block sm:hidden space-y-4">
        @foreach($pedidos as $pedido)
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="font-semibold">#{{ $pedido->proyecto_id }}-{{ $pedido->id }}</span>
                    <span
                        class="px-2 py-1 rounded-lg text-white text-xs font-semibold"
                        style="background-color:
                            @if    ($pedido->estado == 'POR APROBAR') #FFDD57
                            @elseif($pedido->estado == 'APROBADO')    #F39C12
                            @elseif($pedido->estado == 'ENTREGADO')   #3498DB
                            @elseif($pedido->estado == 'RECHAZADO')   #9B59B6
                            @elseif($pedido->estado == 'ARCHIVADO')   #E67E22
                            @elseif($pedido->estado == 'POR REPROGRAMAR') #E74C3C
                            @else #BDC3C7 @endif;"
                    >
                        {{ strtoupper($pedido->estado) }}
                    </span>
                </div>
                <div class="text-sm text-gray-700 space-y-1">
                    <p><strong>Proyecto:</strong> {{ $pedido->proyecto->nombre }}</p>
                    <p><strong>Producto:</strong> {{ $pedido->producto->nombre ?? 'Sin producto' }} / {{ $pedido->producto->categoria->nombre ?? 'Sin categoría' }}</p>
                    <p><strong>Características:</strong> 
                        @if($pedido->pedidoCaracteristicas->isNotEmpty())
                            {{ $pedido->pedidoCaracteristicas->pluck('caracteristica.nombre')->join(', ') }}
                        @else
                            Sin características
                        @endif
                    </p>
                    <p><strong>Cliente:</strong> {{ $pedido->usuario->name ?? 'Sin usuario' }}</p>
                    <p><strong>Total piezas:</strong> {{ $pedido->total }}</p>
                    <p><strong>Estado Diseño:</strong> {{ strtoupper($pedido->proyecto->estado) }}</p>
                    <p><strong>Estado Pedido:</strong> {{ strtoupper($pedido->estado) }}</p>
                    <p><strong>Producción:</strong> {{ $pedido->fecha_produccion ?? 'No definida' }}</p>
                    <p><strong>Entrega:</strong> {{ $pedido->fecha_entrega ?? 'No definida' }}</p>
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    @hasanyrole('admin')
                        <button
                            wire:click="abrirModal({{ $pedido->id }})"
                            class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-3 py-1 rounded text-xs"
                        >Editar</button>
                    @endhasanyrole

                    @if ($pedido->estado == 'POR APROBAR' && $pedido->proyecto?->estado === 'DISEÑO APROBADO')
                        <button
                            wire:click="confirmarAprobacion({{ $pedido->id }})"
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold px-3 py-1 rounded text-xs"
                        >Aprobar</button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- VISTA DESKTOP: Tabla --}}
    <div class="hidden sm:block overflow-x-auto bg-white rounded shadow">
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
                        <td class="p-2 px-4 py-2 font-semibold min-w-[4rem]"
                            title="Proyecto {{ $pedido->proyecto_id }} - Pedido #{{ $pedido->id }}: {{ $pedido->descripcion_corta }}"
                        >
                            {{ $pedido->proyecto_id }}-{{ $pedido->id }}
                        </td>
                        <td class="p-2">{{ $pedido->proyecto->nombre }}</td>
                        <td class="p-2">
                            <div class="font-medium">{{ $pedido->producto->nombre ?? 'Sin producto' }}</div>
                            <div class="text-xs text-gray-500">{{ $pedido->producto->categoria->nombre ?? 'Sin categoría' }}</div>
                        </td>
                        <td class="p-2 align-top text-xs text-gray-700">
                            @if($pedido->pedidoCaracteristicas->isNotEmpty())
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($pedido->pedidoCaracteristicas as $car)
                                        <li>{{ $car->caracteristica->nombre ?? 'Sin nombre' }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-gray-400">Sin características</span>
                            @endif
                        </td>
                        <td class="p-2">{{ $pedido->usuario->name ?? 'Sin usuario' }}</td>
                        <td class="p-2">{{ $pedido->total }} piezas</td>
                        <td class="p-2">
                            @php
                                $colorProyecto = collect([
                                    'PENDIENTE'         => 'bg-yellow-400 text-black',
                                    'ASIGNADO'          => 'bg-blue-500 text-white',
                                    'EN PROCESO'        => 'bg-orange-500 text-white',
                                    'REVISION'          => 'bg-purple-600 text-white',
                                    'DISEÑO APROBADO'   => 'bg-emerald-600 text-white',
                                    'DISEÑO RECHAZADO'  => 'bg-red-600 text-white',
                                    'CANCELADO'         => 'bg-gray-500 text-white',
                                ])->get(strtoupper($pedido->proyecto->estado), 'bg-gray-400 text-black');
                            @endphp

                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $colorProyecto }}">
                                {{ strtoupper($pedido->proyecto->estado) }}
                            </span>
                        </td>
                        <td class="p-2 text-center">
                            @php
                                $nombreEstado = $pedido->estadoPedido?->nombre ?? 'SIN ESTADO';
                                $colorClase   = $pedido->estadoPedido?->color ?? 'bg-gray-400 text-black';
                            @endphp

                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $colorClase }}">
                                @if ($pedido->flag_solicitud_aprobar_sin_fechas == '1')
                                    APROBACION ESPECIAL
                                @else
                                    {{ strtoupper($nombreEstado) }}
                                @endif
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


                            @if ($pedido->estado == 'POR APROBAR' && $pedido->proyecto?->estado === 'DISEÑO APROBADO' && $pedido->flag_solicitud_aprobar_sin_fechas == '0')
                                <button
                                    wire:click="confirmarAprobacionEspecial({{ $pedido->id }})"
                                    class="bg-yellow-500 hover:bg-yellow-600 bg-green-600 hover:bg-green-700 text-white font-semibold px-3 py-1 rounded text-xs"
                                >Aprobacion Especial</button>
                            @endif


                            @if ($pedido->estado == 'POR APROBAR' && $pedido->proyecto?->estado === 'DISEÑO APROBADO'  )

                                        @if ($pedido->flag_solicitud_aprobar_sin_fechas == '1' AND $pedido->flag_aprobar_sin_fechas == '0')
                                            
                                        @else
                                            <button
                                            wire:click="confirmarAprobacion({{ $pedido->id }})"
                                            class="bg-green-600 hover:bg-green-700 text-white font-semibold px-3 py-1 rounded text-xs"
                                            >Aprobar</button>
                                        @endif



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
    @if($modal)
    <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white rounded shadow-lg w-full max-w-lg flex flex-col">
            <!-- Encabezado -->
            <div class="flex items-center justify-between border-b border-gray-200 p-4">
                <h5 class="text-xl font-bold">{{ $pedidoId ? 'Editar Pedido' : 'Nuevo Pedido' }}</h5>
                <button class="text-gray-500 hover:text-gray-700" wire:click="$set('modal', false)">&times;</button>
            </div>

            <!-- Contenedor con scroll -->
            <div class="overflow-y-auto max-h-[60vh] p-4">
                <!-- SECCIÓN: Cantidades por Tallas -->
                @if(!empty($tallas_disponibles))
                    <h6 class="text-lg font-semibold mt-4">Cantidades por Tallas</h6>
                    @foreach ($tallas_disponibles as $grupoTalla)
                        <p class="font-semibold text-gray-700 border-b pb-2 mt-2">{{ $grupoTalla['nombre'] }}</p>
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            @foreach ($grupoTalla['tallas'] as $talla)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ $talla['nombre'] }}</label>
                                    {{-- <input type="number" class="w-full border border-gray-300 rounded p-2"
                                        wire:model.lazy="cantidades_tallas.{{ $grupoTalla['id'] }}.{{ $talla['id'] }}" min="0"> --}}
                                        <input type="number" min="0"
                                        wire:model.defer="inputsTallas.{{ $grupoTalla['id'] }}_{{ $talla['id'] }}"
                                        class="w-full border border-gray-300 rounded p-2"
                                        placeholder="0">

                                </div>

                            @endforeach
                        </div>
                    @endforeach
                @endif

                <!-- Campo Total -->
                {{-- @if($mostrar_total) --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Total</label>
                        <input type="number" step="0.01" class="w-full border border-gray-300 rounded p-2"
                            wire:model="total">
                                   <!-- Mensaje de error si el total de tallas no coincide -->
                        @if($error_total)
                            <div class="bg-red-100 text-red-800 p-3 rounded mb-3 mx-4">
                                {{ $error_total }}
                            </div>
                        @endif
                    </div>
                {{-- @endif --}}

                <!-- SECCIÓN: Tipo y Estado -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                     @can('proyectopedidosEditarinputTipo')

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tipo</label>
                            <select wire:model="tipo" class="w-full border border-gray-300 rounded p-2">
                                <option value="PEDIDO">Pedido</option>
                                <option value="MUESTRA">Muestra</option>
                            </select>
                            @error('tipo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                     @endcan
                     @can('proyectopedidosEditarInputEstado')
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Estado</label>
                                <select wire:model="estado_id" class="w-full border rounded p-2">
                                    <option value="">-- Selecciona un estado --</option>
                                    @foreach ($estados as $e)
                                        <option value="{{ $e['id'] }}">{{ $e['nombre'] }}</option>
                                    @endforeach
                                </select>
                                @error('estado_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                     @endcan
                </div>

                <!-- SECCIÓN: Direcciones -->
                <h6 class="text-lg font-semibold mb-2">Direcciones</h6>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Dirección Fiscal</label>
                        <select wire:model="direccion_fiscal_id" class="w-full border border-gray-300 rounded p-2">
                            <option value="">Seleccionar Dirección Fiscal</option>
                            @foreach ($direccionesFiscales as $direccion)
                                <option value="{{ $direccion->id }}">{{ $direccion->calle }}, {{ $direccion->ciudad->nombre }}, {{ $direccion->ciudad->estado->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Dirección de Entrega</label>
                        <select wire:change='cargarTiposEnvio' wire:model="direccion_entrega_id" class="w-full border border-gray-300 rounded p-2">
                            <option value="">Selecciona una dirección</option>
                            @foreach ($direccionesEntrega as $direccion)
                                <option value="{{ $direccion->id }}">{{ $direccion->calle }}, {{ $direccion->ciudad->nombre }}, {{ $direccion->ciudad->estado->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- SECCIÓN: Tipo de Envío -->
                <h6 class="text-lg font-semibold mb-2">Tipo de Envío</h6>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Tipo de Envío</label>
                    <select wire:change="on_Calcula_Fechas_Entrega" wire:model="id_tipo_envio" class="w-full border border-gray-300 rounded p-2">
                        <option value="">Selecciona un tipo de envío</option>
                        @foreach ($tiposEnvio as $tipo)
                            <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- SECCIÓN: Cliente -->
                {{-- <h6 class="text-lg font-semibold mb-2">Cliente</h6>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Selecciona un Cliente</label>
                    <select wire:model="cliente_id" class="w-full border border-gray-300 rounded p-2">
                        <option value="">-- Seleccionar Cliente --</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}">{{ $cliente->nombre_empresa }}</option>
                        @endforeach
                    </select>
                    @error('cliente_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div> --}}

                <!-- SECCIÓN: Fechas -->
                <h6 class="text-lg font-semibold mb-2">Fechas</h6>
                @if($mensaje_produccion)
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-3 rounded mb-4">
                        {{ $mensaje_produccion }}
                    </div>
                @endif

                <div class="grid grid-cols-3 gap-4 mb-4">
                     @can('proyectopedidosEditarinputFechaproduccion')
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Producción</label>
                            <input type="date" class="w-full border border-gray-300 rounded p-2" wire:model="fecha_produccion">
                        </div>
                    @endcan

                    @can('proyectopedidosEditarinputFechaEmbarque')
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Embarque</label>
                            <input type="date" class="w-full border border-gray-300 rounded p-2" wire:model="fecha_embarque">
                        </div>
                    @endcan
                    @can('proyectopedidosEditarinputFechaEntrega')
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Entrega</label>
                            <input wire:change="validarFechaEntrega" wire:model="fecha_entrega"
                                type="date" class="w-full mt-1 border rounded-lg p-2"
                                min="{{ date('Y-m-d') }}" id="fechaEntrega">
                        </div>
                    @endcan
                </div>
            </div>

            <!-- SECCIÓN: Botones de Acción (Siempre visibles) -->
            <div class="sticky bottom-0 bg-white border-t border-gray-200 p-4 flex justify-end space-x-2">
                <button wire:click="$set('modal', false)" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-4 py-2 rounded">
                    Cancelar
                </button>
                <button wire:click="guardar" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
                    Guardar
                </button>
            </div>
        </div>
    </div>
    @endif
    @if ($modal_confirmar_aprobacion)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded shadow-lg w-full max-w-md p-6">
                <h3 class="text-xl font-semibold mb-4">¿Confirmar aprobación del pedido?</h3>
                
                <p class="text-gray-700 mb-2">ID del Pedido: <strong>{{ $pedidoId }}</strong></p>
                @php
                    $pedido = \App\Models\Pedido::find($pedidoId);
                @endphp

                @if($pedido)
                    <ul class="text-sm text-gray-600 space-y-1 mb-4">
                        {{-- $pedido->cliente->nombre_empresa --}}
                        <li><strong>Cliente:</strong> {{ $pedido->usuario->name ?? 'Sin cliente' }}</li>
                        <li><strong>Total:</strong> {{ $pedido->total }}</li>
                        <li><strong>Producción:</strong> {{ $pedido->fecha_produccion ?? 'No definida' }}</li>
                        <li><strong>Embarque:</strong> {{ $pedido->fecha_embarque ?? 'No definida' }}</li>
                        <li><strong>Entrega:</strong> {{ $pedido->fecha_entrega ?? 'No definida' }}</li>
                        <li><strong>Flag Aprobación sin fechas:</strong> {{ $pedido->flag_aprobar_sin_fechas ? 'Sí' : 'No' }}</li>
                    </ul>
                @endif

                <div class="flex justify-end gap-2 mt-4">
                    <button wire:click="$set('modal_confirmar_aprobacion', false)"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                        Cancelar
                    </button>
                    <button wire:click="aprobar_pedido"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                        Confirmar y Aprobar
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($modal_confirmar_aprobacion_especial)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded shadow-lg w-full max-w-md p-6">
                <h3 class="text-xl  mb-4">¿Confirmar aprobación <b>Especial</b> del pedido?</h3>
                <p class="text-xs mb-4">Esta Solicitud permitira aprobar el pedido para pedidos urgentes</p> 
                
                <p class="text-gray-700 mb-4">ID del Pedido: <strong>{{ $pedidoId }}</strong></p>

                @php
                    $pedido = \App\Models\Pedido::find($pedidoId);
                @endphp

                @if($pedido)
                    <ul class="text-sm text-gray-600 space-y-1 mb-4">
                        {{-- $pedido->cliente->nombre_empresa --}}
                        <li><strong>Cliente:</strong> {{ $pedido->usuario->name ?? 'Sin cliente' }}</li>
                        <li><strong>Total:</strong> {{ $pedido->total }}</li>
                        <li><strong>Producción:</strong> {{ $pedido->fecha_produccion ?? 'No definida' }}</li>
                        <li><strong>Embarque:</strong> {{ $pedido->fecha_embarque ?? 'No definida' }}</li>
                        <li><strong>Entrega:</strong> {{ $pedido->fecha_entrega ?? 'No definida' }}</li>
                        <li><strong>Flag Aprobación sin fechas:</strong> {{ $pedido->flag_aprobar_sin_fechas ? 'Sí' : 'No' }}</li>
                    </ul>
                @endif

                <div class="flex justify-end gap-2 mt-4">
                    <button wire:click="$set('modal_confirmar_aprobacion_especial', false)"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                        Cancelar
                    </button>
                    <button wire:click="Crea_Solicitud_Aprobacion_Especial"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                        Confirmar y Solicitar
                    </button>
                </div>
            </div>
        </div>
    @endif
    
    @if ($modal_reconfigurar_proyecto)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded shadow-lg w-full max-w-lg p-6">
                <!-- Encabezado -->
                <div class="mb-4">
                    <h2 class="text-xl font-bold text-red-600">⚠️ Proyecto mal configurado</h2>
                    <p class="text-gray-700 mt-2">
                        Este proyecto tiene errores de configuración y no puede aprobarse aún. 
                        
                    </p>
                </div>

                <!-- Opciones -->
                <div class="mt-6 flex justify-end space-x-2">
                    <button wire:click="$set('modal_reconfigurar_proyecto', false)"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-4 py-2 rounded">
                        Cancelar
                    </button>

                    <button wire:click="solicitarReconfiguracion({{ $pedido->id }})"
                            class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded">
                        Solicitar Reconfiguración
                    </button>
                </div>
            </div>
        </div>
    @endif

    
</div>

<div x-data="{ selectedPedidos: @entangle('selectedPedidos') }" class="container mx-auto p-6">

    <h2 class="text-2xl font-bold mb-4">Todos los Pedidos</h2>


    <div x-data="{ mostrarFiltros: false }" class="mb-4 bg-white shadow rounded p-4 border border-gray-200">
        <div @click="mostrarFiltros = !mostrarFiltros" class="cursor-pointer text-blue-600 font-semibold flex justify-between items-center">
            <span>Filtros</span>
            <span x-text="mostrarFiltros ? '▲' : '▼'" class="text-sm"></span>
        </div>
    
        <div x-show="mostrarFiltros" x-transition class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="text-sm text-gray-700">Usuario</label>
                <input type="text" wire:model.defer="filtro_usuario" class="w-full border rounded p-2" placeholder="Buscar usuario...">
            </div>

            <!-- Categoría -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Categoría</label>
                <select wire:model="filtro_categoria" class="w-full border border-gray-300 rounded p-2">
                    <option value="">-- Todas --</option>
                    @foreach($categorias_activas as $categoria)
                        <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
            </div>
                

            <!-- Producto -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Producto</label>
                <select wire:model="filtro_producto" class="w-full border border-gray-300 rounded p-2">
                    <option value="">-- Todos --</option>
                    @foreach($productos_activos as $producto)
                        <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                    @endforeach
                </select>
            </div>




            <div>
                <label class="text-sm text-gray-700">Total mínimo</label>
                <input type="number" wire:model.defer="filtro_total_min" class="w-full border rounded p-2" min="0">
            </div>
    
            <div>
                <label class="text-sm text-gray-700">Estado del Pedido</label>
                <select wire:model.defer="filtro_estado" class="w-full border rounded p-2">
                    <option value="">Todos</option>
                    <option value="POR APROBAR">Por aprobar</option>
                    <option value="APROBADO">Aprobado</option>
                    <option value="ENTREGADO">Entregado</option>
                    <option value="RECHAZADO">Rechazado</option>
                    <option value="ARCHIVADO">Archivado</option>
                </select>
            </div>
    
            <div>
                <label class="text-sm text-gray-700">Estatus Producción</label>
                <select wire:model.defer="filtro_estado_produccion" class="w-full border rounded p-2">
                    <option value="">Todos</option>
                    <option value="POR APROBAR">Por Aprobar</option>
                    <option value="POR PROGRAMAR">Por Programar</option>
                    <option value="PROGRAMADO">Programado</option>
                    <option value="IMPRESIÓN">Impresión</option>
                    <option value="CORTE">Corte</option>
                    <option value="COSTURA">Costura</option>
                    <option value="ENTREGA">Entrega</option>
                    <option value="FACTURACIÓN">Facturación</option>
                    <option value="COMPLETADO">Completado</option>
                    <option value="RECHAZADO">Rechazado</option>
                </select>
            </div>
        </div>
    
        <div class="mt-4 flex gap-3">
            <button wire:click="aplicarFiltros" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Aplicar
            </button>
            <button wire:click="limpiarFiltros" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                Limpiar
            </button>
        </div>
    </div>
    


    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-4 flex flex-wrap space-y-2 sm:space-y-0 sm:space-x-4">
        <button
            class="w-full sm:w-auto px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="selectedPedidos.length === 0"
            wire:click="exportSelected"
        >
            Exportar Seleccionados
        </button>
        <button
            class="w-full sm:w-auto px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="selectedPedidos.length === 0"
            wire:click="deleteSelected"
        >
            Eliminar Seleccionados
        </button>
    </div>
        
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full border-collapse border border-gray-200 text-sm">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="px-4 py-2 border">
                        <input type="checkbox"
                            wire:model="selectAll"
                            @change="selectedPedidos = $event.target.checked ? @js($pedidos->pluck('id')) : []"
                        />
                    </th>
                    <th class="px-4 py-2 border">ID</th>
                    <th class="px-4 py-2 border">Usuario</th>
                    <th class="px-4 py-2 border">Producto / Categoría</th>
                    <th class="px-4 py-2 border">Total</th>
                    <th class="px-4 py-2 border">Tipo</th>
                    <th class="px-4 py-2 border">Estado</th>
                    <th class="px-4 py-2 border">Producción</th>
                    <th class="px-4 py-2 border">Embarque</th>
                    <th class="px-4 py-2 border">Entrega</th>
                    <th class="px-4 py-2 border">Estatus Producción</th>
                    <th class="px-4 py-2 border">Acciones</th>
                </tr>
            </thead>
            <tbody>



                @forelse($pedidos as $pedido)
                    @php
                        $hoy = \Carbon\Carbon::today();
                        $color = 'bg-white';

                        $fechaEntrega = $pedido->fecha_entrega ? \Carbon\Carbon::parse($pedido->fecha_entrega) : null;

                        if ($pedido->estado_produccion === 'COMPLETADO') {
                            $color = 'bg-green-100';
                        } elseif ($fechaEntrega) {
                            $diff = $hoy->diffInDays($fechaEntrega, false); // Negativo si ya pasó

                            if ($diff > 7) {
                                $color = 'bg-white';
                            } elseif ($diff >= 4) {
                                $color = 'bg-yellow-100';
                            } elseif ($diff >= 1) {
                                $color = 'bg-orange-100';
                            } elseif ($diff <= 0) {
                                $color = 'bg-red-100';
                            }
                        }
                    @endphp
                
                <tr class="{{ $color }} hover:bg-gray-50 transition">
                    <td class="px-4 py-2 border">
                        <input type="checkbox"
                            wire:model="selectedPedidos"
                            value="{{ $pedido->id }}"
                        />
                    </td>  
                        <td class="px-4 py-2 border font-medium">{{ $pedido->id }}-{{ $pedido->proyecto_id }}</td>

                        <td class="px-4 py-2 border">{{ $pedido->proyecto->user->name ?? 'Sin usuario' }}</td>
            
                        <td class="px-4 py-2 border">
                            <div class="font-semibold">{{ $pedido->producto->nombre ?? 'Sin producto' }}</div>
                            <div class="text-xs text-gray-500">{{ $pedido->producto->categoria->nombre ?? 'Sin categoría' }}</div>
                        </td>
            
                        <td class="px-4 py-2 border">{{ $pedido->total }} piezas</td>
                        <td class="px-4 py-2 border">{{ $pedido->tipo }}</td>
            
                        <td class="px-4 py-2 border">
                            <span class="text-xs font-bold px-2 py-1 rounded text-white"
                                  style="background-color:
                                      @if($pedido->estado === 'POR APROBAR') #FBBF24
                                      @elseif($pedido->estado === 'APROBADO') #10B981
                                      @elseif($pedido->estado === 'ENTREGADO') #3B82F6
                                      @elseif($pedido->estado === 'RECHAZADO') #EF4444
                                      @elseif($pedido->estado === 'ARCHIVADO') #6B7280
                                      @else #D1D5DB @endif;">
                                {{ strtoupper($pedido->estado) }}
                            </span>
                        </td>
            
                        <td class="px-4 py-2 border">{{ $pedido->fecha_produccion ?? 'No definida' }}</td>
                        <td class="px-4 py-2 border">{{ $pedido->fecha_embarque ?? 'No definida' }}</td>
                        <td class="px-4 py-2 border">{{ $pedido->fecha_entrega ?? 'No definida' }}</td>
            
                        <td class="px-4 py-2 border">
                            <span class="text-xs bg-gray-100 px-2 py-1 rounded text-gray-800">
                                {{ $pedido->estado_produccion ?? 'Sin definir' }}
                            </span>
                        </td>
            
                        <td class="px-4 py-2 border text-center">
                            <button wire:click="abrirModal({{ $pedido->id }})"
                                    class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                                Editar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center py-4 text-gray-500">
                            No hay pedidos disponibles.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $pedidos->links() }}
    </div>

    @if($modal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded shadow-lg w-full max-w-2xl flex flex-col">
                <!-- Encabezado -->
                <div class="flex items-center justify-between border-b border-gray-200 p-4">
                    <h5 class="text-xl font-bold">{{ $pedidoId ? 'Editar Pedido' : 'Nuevo Pedido' }}</h5>
                    <button class="text-gray-500 hover:text-gray-700" wire:click="$set('modal', false)">&times;</button>
                </div>


                

                <!-- Contenido con scroll -->
                <div class="overflow-y-auto max-h-[70vh] p-4">

                    {{-- Información del pedido (colapsable) --}}
                    <div x-data="{ openInfo: true }" class="mb-6 border border-gray-200 rounded-lg p-4">
                        <div @click="openInfo = !openInfo" class="flex justify-between items-center cursor-pointer select-none">
                            <h6 class="text-lg font-bold text-gray-800">
                                Configuracion del Producto
                            </h6>
                            <span class="text-sm text-blue-500 hover:underline">
                                <span x-show="!openInfo">Mostrar</span>
                                <span x-show="openInfo">Ocultar</span>
                            </span>
                        </div>
                    
                        <div x-show="openInfo" x-transition class="mt-3 space-y-1 text-sm text-gray-700">
                            <div>
                                <span class="font-semibold">ID Pedido:</span>
                                {{ $pedidoId ?? 'Nuevo' }} - {{ $proyecto_id_pedido ?? 'N/A' }}
                            </div>
                            <div>
                                <span class="font-semibold">Nombre del Proyecto:</span> {{ $proyecto_nombre ?? 'N/A' }}
                            </div>
                            <div>
                                <span class="font-semibold">Producto:</span> {{ $producto_nombre ?? 'N/A' }}
                            </div>
                            <div>
                                <span class="font-semibold">Categoría:</span> {{ $categoria_nombre ?? 'N/A' }}
                            </div>
                        </div>
                    </div>

                    {{-- Total y Cantidades por Talla --}}
                    @if(!empty($tallas_disponibles))
                        <h6 class="text-lg font-semibold mb-2">Cantidades por Tallas</h6>
                        @foreach ($tallas_disponibles as $grupoTalla)
                            <p class="font-semibold text-gray-700 mt-3">{{ $grupoTalla['nombre'] }}</p>
                            <div class="grid grid-cols-3 gap-4 mb-2">
                                @foreach ($grupoTalla['tallas'] as $talla)
                                    <div>
                                        <label class="text-sm">{{ $talla['nombre'] }}</label>
                                        {{-- <input type="number" min="0"
                                            wire:model.lazy="cantidades_tallas.{{ $grupoTalla['id'] }}.{{ $talla['id'] }}"
                                            class="w-full border border-gray-300 rounded p-2"> --}}

                                        <input type="number" min="0"
                                            wire:model.defer="inputsTallas.{{ $grupoTalla['id'] }}_{{ $talla['id'] }}"
                                            class="w-full border border-gray-300 rounded p-2"
                                            placeholder="0">
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @endif

                    <div class="mb-4 mt-4">
                        <label class="block text-sm font-medium text-gray-700">Total</label>
                        <input type="number" wire:model="total"
                            class="w-full border border-gray-300 rounded p-2">
                        @if($error_total)
                            <div class="text-red-600 text-sm mt-2">{{ $error_total }}</div>
                        @endif
                    </div>

                    {{-- Tipo y Estado --}}
                    <div class="grid grid-cols-1 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tipo</label>
                            <select wire:model="tipo" class="w-full border border-gray-300 rounded p-2">
                                <option value="PEDIDO">Pedido</option>
                                <option value="MUESTRA">Muestra</option>
                            </select>
                            @error('tipo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>


                    </div>

                    <div  class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Estado del pedido</label>
                            <select wire:model="estado" class="w-full border border-gray-300 rounded p-2">
                                <option value="POR APROBAR">Por aprobar</option>
                                <option value="APROBADO">Aprobado</option>
                                <option value="ENTREGADO">Entregado</option>
                                <option value="RECHAZADO">Rechazado</option>
                                <option value="ARCHIVADO">Archivado</option>
                            </select>
                            @error('estado') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Estado Producción</label>
                            <select wire:model="estado_produccion" class="w-full border border-gray-300 rounded p-2">
                                <option value="">-- Seleccionar estado de producción --</option>
                                <option value="POR APROBAR">Por Aprobar</option>
                                <option value="POR PROGRAMAR">Por Programar</option>
                                <option value="PROGRAMADO">Programado</option>
                                <option value="IMPRESIÓN">Impresion</option>
                                <option value="CORTE">Corte</option>
                                <option value="COSTURA">Costura</option>
                                <option value="ENTREGA">Entrega</option>
                                <option value="FACTURACIÓN">Facturación</option>
                                <option value="COMPLETADO">Completado</option>
                                <option value="RECHAZADO">Rechazado</option>
                            </select>
                            @error('estado_produccion') 
                                <span class="text-red-500 text-sm">{{ $message }}</span> 
                            @enderror
                        </div>
                    </div>

                    {{-- Cliente --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Cliente</label>
                        <select wire:model="cliente_id" class="w-full border border-gray-300 rounded p-2">
                            <option value="">-- Selecciona un cliente --</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}">{{ $cliente->nombre_empresa }}</option>
                            @endforeach
                        </select>
                        @error('cliente_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- Direcciones --}}
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Dirección Fiscal</label>
                            <select wire:model="direccion_fiscal_id" class="w-full border border-gray-300 rounded p-2">
                                <option value="">Seleccionar dirección</option>
                                @foreach ($direccionesFiscales as $dir)
                                    <option value="{{ $dir->id }}">{{ $dir->calle }}, {{ $dir->ciudad->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Dirección de Entrega</label>
                            <select wire:change="cargarTiposEnvio" wire:model="direccion_entrega_id"
                                class="w-full border border-gray-300 rounded p-2">
                                <option value="">Seleccionar dirección</option>
                                @foreach ($direccionesEntrega as $dir)
                                    <option value="{{ $dir->id }}">{{ $dir->calle }}, {{ $dir->ciudad->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Tipo de Envío --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Tipo de Envío</label>
                        <select wire:change="on_Calcula_Fechas_Entrega" wire:model="id_tipo_envio"
                            class="w-full border border-gray-300 rounded p-2">
                            <option value="">Seleccionar tipo</option>
                            @foreach($tipos_envio as $tipo)
                                <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Fechas --}}
                    <h6 class="text-lg font-semibold mb-2">Fechas</h6>

                    @if($mensaje_produccion)
                        <div class="bg-yellow-100 text-yellow-800 p-3 rounded mb-3">
                            {{ $mensaje_produccion }}
                        </div>
                    @endif

                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Producción</label>
                            <input type="date" wire:model="fecha_produccion"
                                class="w-full border border-gray-300 rounded p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Embarque</label>
                            <input type="date" wire:model="fecha_embarque"
                                class="w-full border border-gray-300 rounded p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Entrega</label>
                            <input type="date" 
                                wire:model="fecha_entrega" class="w-full border border-gray-300 rounded p-2"
                                min="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t p-4 flex justify-end gap-2">
                    <button wire:click="$set('modal', false)"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-4 py-2 rounded">
                        Cancelar
                    </button>
                    <button wire:click="guardar"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

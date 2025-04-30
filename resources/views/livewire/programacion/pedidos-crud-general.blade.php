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

        <button
            class="w-full sm:w-auto px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="selectedPedidos.length === 0"
            wire:click="abrirModalCrearTareaConPedidos"
        >
            Crear Tarea con Pedidos Seleccionados
        </button>

        <button
            class="w-full sm:w-auto px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="selectedPedidos.length === 0"
            wire:click="abrirModalCrearOrdenCorte"
        >
            Crear Orden de Corte
        </button>
        
    </div>
        
    <div class="overflow-x-auto">
        <div class="inline-block min-w-full align-middle">
            <table class="min-w-full text-sm text-left text-gray-700">
                <thead class="bg-gray-100 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 border">
                            <input type="checkbox"
                                wire:model="selectAll"
                                @change="selectedPedidos = $event.target.checked ? @js($pedidos->pluck('id')) : []"
                            />
                        </th>
                        <th class="px-4 py-3 border">ID</th>
                        {{-- <th class="px-4 py-3 border">Usuario</th> --}}
                        <th class="px-4 py-3 border">Producto / Categoría</th>
                        <th class="px-4 py-3 border">Caracteristicas</th>
                        {{-- <th class="px-4 py-3 border">Total</th> --}}
                        <th class="px-4 py-3 border">Tipo</th>
                        <th class="px-4 py-3 border">Estado</th>
                        <th class="px-4 py-3 border">Producción</th>
                        <th class="px-4 py-3 border">Embarque</th>
                        <th class="px-4 py-3 border">Entrega</th>
                        <th class="px-4 py-3 border">Producción</th>
                        <th class="px-4 py-3 border">Tareas</th>
                        <th class="px-4 py-3 border text-center">Acciones</th>
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
                                $diff = $hoy->diffInDays($fechaEntrega, false);
                                if ($diff > 7) $color = 'bg-white';
                                elseif ($diff >= 4) $color = 'bg-yellow-100';
                                elseif ($diff >= 1) $color = 'bg-orange-100';
                                else $color = 'bg-red-100';
                            }
                        @endphp
    
                        <tr class="{{ $color }} hover:bg-gray-50 transition">
                            <td class="px-4 py-2 border">
                                <input type="checkbox"
                                    wire:model="selectedPedidos"
                                    value="{{ $pedido->id }}"
                                />
                            </td>
                            <td class="px-4 py-2 border font-semibold">{{ $pedido->id }}-{{ $pedido->proyecto_id }}</td>
                            {{-- <td class="px-4 py-2 border">{{ $pedido->proyecto->user->name ?? 'Sin usuario' }}</td> --}}
                            <td class="px-4 py-2 border">
                                <div class="font-medium">{{ $pedido->producto->nombre ?? 'Sin producto' }}</div>
                                <div class="text-xs text-gray-500">{{ $pedido->producto->categoria->nombre ?? 'Sin categoría' }}</div>
                            </td>
                            <td class="px-4 py-2 border">
                                @if($pedido->pedidoCaracteristicas->isNotEmpty())
                                    <ul class="list-disc list-inside text-sm text-gray-700">
                                        @foreach($pedido->pedidoCaracteristicas as $pc)
                                            <li class="font-semibold">{{ $pc->caracteristica->nombre ?? 'Sin característica' }}
                                                @php
                                                    $opcionesRelacionadas = $pedido->pedidoOpciones
                                                        ->filter(fn($po) =>
                                                            $po->opcion &&
                                                            $po->opcion->caracteristicas->pluck('id')->contains($pc->caracteristica_id)
                                                        );
                                                @endphp
                                                @if($opcionesRelacionadas->isNotEmpty())
                                                    <ul class="list-disc list-inside ml-4 text-xs text-gray-600">
                                                        @foreach($opcionesRelacionadas as $opcion)
                                                            <li>{{ $opcion->opcion->nombre ?? 'Sin opción' }} @if($opcion->valor)  @endif</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <div class="text-xs text-gray-400 ml-4">Sin opciones</div>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-gray-500 text-sm">Sin características</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 border">{{ $pedido->total }} piezas</td>
                            {{-- <td class="px-4 py-2 border">{{ $pedido->tipo }}</td> --}}
                            <td class="px-4 py-2 border">
                                <span class="text-xs font-bold px-2 py-1 rounded text-white"
                                    style="background-color:
                                        @if($pedido->estado === 'POR APROBAR') #FBBF24
                                        @elseif($pedido->estado === 'APROBADO') #10B981
                                        @elseif($pedido->estado === 'ENTREGADO') #3B82F6
                                        @elseif($pedido->estado === 'RECHAZADO') #EF4444
                                        @elseif($pedido->estado === 'ARCHIVADO') #6B7280
                                        @elseif($pedido->estado === 'POR REPROGRAMAR') #E74C3C
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
                            <td class="px-4 py-2 border text-sm text-gray-700">
                                @if($pedido->tareasProduccion->isEmpty())
                                    <span class="text-gray-500">Sin tareas</span>
                                @else
                                    <ul class="space-y-1">
                                        @foreach($pedido->tareasProduccion as $tarea)
                                            <li class="border-b pb-1">
                                                <div class="text-xs font-semibold">{{ $tarea->tipo }} - {{ $tarea->estado }}</div>
                                                <div class="text-xs text-gray-500">{{ $tarea->descripcion }}</div>
                                                <div class="text-xs italic text-gray-400">Responsable: {{ $tarea->staff->name ?? 'N/A' }}</div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                            <td class="px-4 py-2 border text-center space-y-1">
                                <button wire:click="abrirModal({{ $pedido->id }})"
                                        class="w-full px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                                    Editar
                                </button>
    
                                @if($pedido->estado === 'POR REPROGRAMAR')
                                    <a href="{{ route('reprogramacion.reprogramacionproyectopedido', ['proyecto' => $pedido->proyecto_id]) }}"
                                    class="w-full block px-3 py-1 bg-orange-500 text-white rounded hover:bg-orange-600">
                                        Reprogramar
                                    </a>
                                @endif
                                @if($pedido->flag_aprobar_sin_fechas == 0 && $pedido->estado == 'POR APROBAR')
                                    <button wire:click="confirmarAprobarSinFechas({{ $pedido->id }})"
                                            class="w-full px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                                        Aprobar sin fechas
                                    </button>
                                @endif

                                <button wire:click="abrirModalCrearTarea({{ $pedido->id }})"
                                    class="w-full px-3 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                    Crear Tarea
                                </button>



                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="text-center py-4 text-gray-500">
                                No hay pedidos disponibles.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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
                                <option value="POR REPROGRAMAR">Por Reprogramar</option>
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

    @if($modal_aprobar_sin_fechas)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                <h2 class="text-xl font-bold text-blue-600 mb-4">¿Aprobar este pedido sin validar fechas?</h2>

                <p class="text-gray-700 mb-4">
                    Esta acción permitirá al cliente  <strong>APROBAR</strong> ,
                    aunque no tenga fechas de produccion y entrega en tiempo. ¿Deseas continuar?
                </p>

                <div class="flex justify-end gap-2">
                    <button wire:click="$set('modal_aprobar_sin_fechas', false)"
                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                        Cancelar
                    </button>
                    <button wire:click="aprobarSinFechas"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Sí, aprobar sin fechas
                    </button>
                </div>
            </div>
        </div>
    @endif



    @if($modalCrearTarea)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-lg shadow-lg">
                <h2 class="text-xl font-bold mb-4">Asignar Tarea al Pedido #{{ $nuevoTareaPedidoId }}</h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Usuario Responsable</label>
                    <select wire:model="nuevoTareaStaffId" class="w-full border border-gray-300 rounded p-2">
                        <option value="">-- Selecciona --</option>
                        @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Tipo de Tarea</label>
                    <select wire:model="nuevoTareaTipo" class="w-full border border-gray-300 rounded p-2">
                        <option value="DISEÑO">DISEÑO</option>
                        <option value="PRODUCCION">PRODUCCIÓN</option>
                        <option value="CORTE">CORTE</option>
                        <option value="PINTURA">PINTURA</option>
                        <option value="FACTURACION">FACTURACIÓN</option>
                        <option value="INDEFINIDA">INDEFINIDA</option>
                    </select>
                </div>

                <div class="flex justify-end gap-2">
                    <button wire:click="$set('modalCrearTarea', false)" class="bg-gray-300 text-gray-800 px-4 py-2 rounded">Cancelar</button>
                    <button wire:click="guardarTarea" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Guardar</button>
                </div>
            </div>
        </div>
    @endif

    @if($modalCrearTareaConPedidos)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-lg shadow-lg">
                <h2 class="text-xl font-bold mb-4">Asignar Tarea a los Pedidos Seleccionados</h2>

                {{-- <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Orden de Producción</label>
                    <select wire:model="orden_id" class="w-full border border-gray-300 rounded p-2">
                        <option value="">-- Selecciona una orden --</option>
                        @foreach(App\Models\OrdenProduccion::all() as $orden)
                            <option value="{{ $orden->id }}">{{ $orden->nombre ?? 'Orden #' . $orden->id }} ({{ $orden->tipo }})</option>
                        @endforeach
                    </select>
                    @error('orden_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div> --}}

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Tipo de Tarea</label>
                    <select wire:model="nuevoTareaTipo" class="w-full border border-gray-300 rounded p-2">
                        <option value="DISEÑO">DISEÑO</option>
                        <option value="CORTE">CORTE</option>
                        <option value="BORDADO">BORDADO</option>
                        <option value="PINTURA">PINTURA</option>
                        <option value="FACTURACION">FACTURACIÓN</option>
                        <option value="INDEFINIDA">INDEFINIDA</option>
                    </select>
                    @error('nuevoTareaTipo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Usuario Responsable</label>
                    <select wire:model="nuevoTareaStaffId" class="w-full border border-gray-300 rounded p-2">
                        <option value="">-- Selecciona el responsable --</option>
                        @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                        @endforeach
                    </select>
                    @error('nuevoTareaStaffId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Descripción (Opcional)</label>
                    <textarea wire:model="nuevoTareaDescripcion" class="w-full border border-gray-300 rounded p-2"></textarea>
                </div>

                <div class="flex justify-end gap-2">
                    <button wire:click="$set('modalCrearTareaConPedidos', false)" class="bg-gray-300 text-gray-800 px-4 py-2 rounded">Cancelar</button>
                    <button wire:click="crearTareaConPedidos" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Crear Tarea</button>
                </div>
            </div>
        </div>
    @endif

    @if($modalCrearOrdenCorte)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-lg shadow-lg">
                <h2 class="text-xl font-bold mb-4">Crear Orden de Corte</h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Fecha de Inicio</label>
                    <input type="date" wire:model="ordenCorte_fecha_inicio" class="w-full border border-gray-300 rounded p-2">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Total de Piezas</label>
                    <input type="number" min="1" wire:model="ordenCorte_total" class="w-full border border-gray-300 rounded p-2">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Características (opcional)</label>
                    <textarea wire:model="ordenCorte_caracteristicas" class="w-full border border-gray-300 rounded p-2"></textarea>
                </div>

                <div class="flex justify-end gap-2">
                    <button wire:click="$set('modalCrearOrdenCorte', false)" class="bg-gray-300 text-gray-800 px-4 py-2 rounded">Cancelar</button>
                    <button wire:click="guardarOrdenCorte" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Crear Orden</button>
                </div>
            </div>
        </div>
    @endif

</div>

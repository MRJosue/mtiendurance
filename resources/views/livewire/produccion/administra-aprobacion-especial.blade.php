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
    
            {{-- Filtro: Estado del Pedido (por id) --}}
            <div>
                <label class="text-sm text-gray-700">Estado del Pedido</label>
                <select wire:model.defer="filtro_estado_id" class="w-full border rounded p-2">
                    <option value="">Todos</option>
                    @foreach($catalogoEstados as $e)
                        <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                    @endforeach
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

                <div class="md:col-span-3">
                    <label class="inline-flex items-center text-sm text-gray-700">
                        <input
                            type="checkbox"
                            wire:model="filtro_inactivos"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50 mr-2"
                        >
                        <span>Mostrar solo pedidos inactivos</span>
                    </label>
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

    <div class="mb-4 flex flex-wrap gap-2">



       
    </div>
    <div class="mb-4 flex flex-wrap gap-2">
            @if (session()->has('error'))
                <div x-data="{ visible: true }" x-show="visible" x-transition class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded relative mb-4">
                    <strong class="font-bold">¡Error!</strong>
                    <span class="block sm:inline ml-2">{{ session('error') }}</span>
               
                </div>
            @endif
    </div>


        
<div class="overflow-x-auto bg-white rounded-lg shadow">
    <table class="min-w-full border-collapse rounded-lg text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-2 py-1 border">
                    <input type="checkbox" wire:model="selectAll" @change="selectedPedidos = $event.target.checked ? @js($pedidos->pluck('id')) : []" />
                </th>
                <th class="px-2 py-1 border">ID</th>
                <th class="px-2 py-1 border">Producto / Categoría</th>
                <th class="px-2 py-1 border">Total</th>
                <th class="px-2 py-1 border">Estado</th>
                <th class="px-2 py-1 border">Producción</th>
                <th class="px-2 py-1 border">Embarque</th>
                <th class="px-2 py-1 border">Entrega</th>
                <th class="px-2 py-1 border">Estatus Prod.</th>
                <th class="px-2 py-1 border text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pedidos as $pedido)
                <tr class="hover:bg-gray-50 transition {{ $pedido->estado_produccion === 'COMPLETADO' ? 'bg-green-100' : '' }}">
                    <td class="px-2 py-1 border">
                        <input type="checkbox" wire:model="selectedPedidos" value="{{ $pedido->id }}" />
                    </td>
                    <td
                        class="p-2 px-4 py-2 font-semibold min-w-[4rem]"
                        title="{{ $pedido->tooltip_clave }}"
                    >
                        {!! $pedido->clave_link !!}
                    </td>
                    <td class="px-2 py-1 border">
                        <div class="font-medium text-sm">{{ $pedido->producto->nombre ?? 'Sin producto' }}</div>
                        <div class="text-xs text-gray-500">{{ $pedido->producto->categoria->nombre ?? 'Sin categoría' }}</div>
                    </td>
                    <td class="px-2 py-1 border text-sm">{{ $pedido->total }} piezas</td>
                    <td class="px-2 py-1 border">
                        @php $nombreEstado = $pedido->estadoPedido->nombre ?? 'N/D'; @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                            {{ $nombreEstado === 'POR APROBAR' ? 'bg-yellow-400 text-black'
                            : ($nombreEstado === 'APROBADO' ? 'bg-green-500 text-white'
                            : ($nombreEstado === 'RECHAZADO' ? 'bg-red-500 text-white'
                            : 'bg-gray-300 text-gray-700')) }}">
                            {{ $nombreEstado }}
                        </span>
                    </td>
                    <td class="px-2 py-1 border text-sm">{{ $pedido->fecha_produccion ?? 'N/D' }}</td>
                    <td class="px-2 py-1 border text-sm">{{ $pedido->fecha_embarque ?? 'N/D' }}</td>
                    <td class="px-2 py-1 border text-sm">{{ $pedido->fecha_entrega ?? 'N/D' }}</td>
                    <td class="px-2 py-1 border text-xs bg-gray-100 rounded text-gray-800">
                       

                            @if ($pedido->flag_aprobar_sin_fechas == '1')
                                Pendiente de Aprobar por el cliente
                            @else
                                Pendiente de Revisar aprobación
                            @endif
                    </td>

                    <td class="px-2 py-1 border space-y-1 text-center">
                      @if ($pedido->flag_aprobar_sin_fechas != '1')
                            <button wire:click="abrirModalRevisarAprobacion({{ $pedido->id }})" class="w-full px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-xs">Revisar aprobación</button>
                      @endif

                       
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="py-4 text-center text-gray-500">No hay pedidos disponibles.</td>
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
                            <select wire:model="estado_id" class="w-full border border-gray-300 rounded p-2">
                                @foreach($catalogoEstados as $e)
                                    <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                                @endforeach
                            </select>
                            @error('estado_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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
        <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl flex flex-col max-h-[90vh]">
    
            {{-- Encabezado --}}
            <div class="p-4 border-b">
                <h2 class="text-xl font-bold">Crear Orden de Corte</h2>
            </div>
    
            {{-- Cuerpo con scroll --}}
            <div class="overflow-y-auto p-6 space-y-4">
                {{-- Sección de pedidos relacionados --}}
                <div x-data="{ showPedidos: false }" class="border rounded p-4">
                    <div class="flex justify-between items-center cursor-pointer select-none" @click="showPedidos = !showPedidos">
                        <h3 class="font-semibold text-gray-700">Pedidos seleccionados (IDs: {{ implode(', ', $selectedPedidos) }})</h3>
                        <span class="text-sm text-blue-500 hover:underline">
                            <span x-show="!showPedidos">Mostrar</span>
                            <span x-show="showPedidos">Ocultar</span>
                        </span>
                    </div>
    
                    <div x-show="showPedidos" x-transition class="mt-3 space-y-4">
                        @foreach($selectedPedidos as $pedidoId)
                            @php
                                $pedido = \App\Models\Pedido::with(['producto.categoria', 'pedidoCaracteristicas.caracteristica', 'pedidoOpciones.opcion.caracteristicas'])->find($pedidoId);
                            @endphp
    
                            @if($pedido)
                                <div class="border p-3 rounded bg-gray-50">
                                    <div class="font-bold text-gray-800 mb-2">Pedido #{{ $pedido->id }} – {{ $pedido->producto->nombre ?? 'Sin producto' }}</div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-700">
                                        @foreach($pedido->pedidoCaracteristicas as $pc)
                                            <div>
                                                <div class="font-semibold">{{ $pc->caracteristica->nombre ?? 'Característica' }}</div>
                                                @php
                                                    $opciones = $pedido->pedidoOpciones->filter(function($po) use ($pc) {
                                                        return $po->opcion && $po->opcion->caracteristicas->pluck('id')->contains($pc->caracteristica_id);
                                                    });
                                                @endphp
                                                @if($opciones->isNotEmpty())
                                                    <ul class="list-disc list-inside text-xs text-gray-600 ml-2">
                                                        @foreach($opciones as $op)
                                                            <li>{{ $op->opcion->nombre }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <p class="text-xs text-gray-500 italic">Sin opciones</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>

                                    
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
    
                {{-- Fecha de inicio --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha de Inicio</label>
                    <input type="date" wire:model="ordenCorte_fecha_inicio" class="w-full border border-gray-300 rounded p-2">
                </div>
    
                {{-- Tallas agrupadas --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tallas agrupadas</label>
                    @if(!empty($ordenCorte_tallas_json))
                        <div class="space-y-2">
                            @php $totalCorte = 0; @endphp
                            @foreach($ordenCorte_tallas_json as $clave => $info)
                                @php
                                    $aCortar = max(0, $info['cantidad'] - ($info['stock'] ?? 0));
                                    $totalCorte += $aCortar;
                                @endphp
                                <div class="flex items-center justify-between gap-2 border p-2 rounded">
                                    <div class="w-1/2 text-sm text-gray-700">
                                        <strong>{{ $info['grupo'] }} - {{ $info['talla'] }}</strong><br>
                                        <span>Cantidad: {{ $info['cantidad'] }}</span>
                                    </div>
                                    <div class="w-1/2 text-sm">
                                        <label class="block text-xs text-gray-500">A cortar</label>
                                        <span class="font-semibold text-blue-600">{{ $aCortar }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
    
                {{-- Total a cortar --}}
                <div>
                    <p class="text-sm font-medium text-gray-800">
                        Total a cortar:
                        <span class="text-xl font-bold text-green-600">{{ $totalCorte }} piezas</span>
                    </p>
                </div>
    
                {{-- Características --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Características (opcional)</label>
                    <textarea wire:model="ordenCorte_caracteristicas" class="w-full border border-gray-300 rounded p-2"></textarea>
                </div>
            </div>
    
            {{-- Pie con botones --}}
            <div class="p-4 border-t flex justify-end gap-2 bg-white sticky bottom-0">
                <button wire:click="$set('modalCrearOrdenCorte', false)" class="bg-gray-300 text-gray-800 px-4 py-2 rounded">Cancelar</button>
                <button wire:click="guardarOrdenCorte" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Crear Orden</button>
            </div>
        </div>
    </div>
    @endif
    
    @if($modalOrdenes)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl flex flex-col max-h-[85vh]">
    
            {{-- Encabezado con botón X --}}
            <div class="p-4 border-b flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-800">Órdenes de Producción relacionadas</h2>
                <button wire:click="$set('modalOrdenes', false)" class="text-gray-500 hover:text-red-600 text-xl font-bold">&times;</button>
            </div>
    
            {{-- Contenido con scroll --}}
            <div class="overflow-y-auto p-4 space-y-4 text-sm text-gray-700">
                @forelse($pedidoOrdenes as $orden)
                    <div class="border rounded p-4 bg-gray-50">
                        <div class="flex justify-between items-center mb-2">
                            <div>
                                <div><strong>ID:</strong> {{ $orden['id'] }}</div>
                                <div><strong>Tipo:</strong> {{ $orden['tipo'] }}</div>
                                <div><strong>Creado:</strong> {{ $orden['creado'] }}</div>
                            </div>



                            {{-- Botón de impresión --}}
                            <button
                                class="text-blue-600 hover:underline text-xs"
                                onclick="window.open('{{ route('produccion.ordenes_produccion.imprimir', $orden['id']) }}', '_blank')"
>
                                🖨️ Imprimir
                            </button>


                        </div>
    
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-2 mb-4">
                                <div>
                                    <strong>Fecha sin iniciar:</strong>
                                    <span>{{ $orden['fecha_sin_iniciar'] ?? 'N/D' }}</span>
                                </div>
                                <div>
                                    <strong>Fecha en proceso:</strong>
                                    <span>{{ $orden['fecha_en_proceso'] ?? 'N/D' }}</span>
                                </div>
                                <div>
                                    <strong>Fecha terminado:</strong>
                                    <span>{{ $orden['fecha_terminado'] ?? 'N/D' }}</span>
                                </div>
                                <div>
                                    <strong>Tiempo total de producción:</strong>
                                    <span>
                                        @if($orden['duracion'])
                                            {{ $orden['duracion'] }}
                                        @else
                                            N/D
                                        @endif
                                    </span>
                                </div>
                        </div>
                        {{-- Pedidos relacionados --}}
                        <div class="mb-2">
                            <strong>Pedidos:</strong>
                            <ul class="list-disc list-inside text-sm text-gray-800 ml-4">
                                @foreach($orden['pedidos'] as $p)
                                    <li>Pedido #{{ $p['id'] }} – {{ $p['producto'] }}</li>
                                @endforeach
                            </ul>
                        </div>
    
                        {{-- Suborden: Corte --}}
                        @if($orden['orden_corte'])
                            <div class="bg-white border-t mt-2 pt-2 text-sm text-gray-700">
                                <strong>Suborden Corte:</strong><br>
                                Fecha Inicio: {{ $orden['orden_corte']['fecha_inicio'] ?? 'N/A' }}<br>
                                Total piezas: {{ $orden['orden_corte']['total'] ?? 0 }}
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="italic text-gray-500">Este pedido no tiene órdenes asociadas.</p>
                @endforelse
            </div>
    
            {{-- Pie con botón cerrar --}}
            <div class="border-t p-4 flex justify-end">
                <button wire:click="$set('modalOrdenes', false)"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
    @endif

    @if($modalRevisarAprobacion)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
                <h2 class="text-xl font-bold mb-4">Revisión de Aprobación Pedido #{{ $pedidoId }}</h2>

                <div class="grid grid-cols-1 gap-4 mb-4">


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

                    {{-- Campos en readonly / disabled --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total</label>
                        <input type="number" value="{{ $total }}" disabled class="w-full border border-gray-300 rounded p-2 bg-gray-100 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Estatus</label>
                        <input type="text" value="{{ $estatus }}" disabled class="w-full border border-gray-300 rounded p-2 bg-gray-100 cursor-not-allowed">
                    </div>

    

                    {{-- Fechas editables --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Producción</label>
                        <input type="date" wire:model="fecha_produccion" class="w-full border border-gray-300 rounded p-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Embarque</label>
                        <input type="date" wire:model="fecha_embarque" class="w-full border border-gray-300 rounded p-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Entrega</label>
                        <input type="date" wire:model="fecha_entrega" min="{{ date('Y-m-d') }}" class="w-full border border-gray-300 rounded p-2">
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <button wire:click="rechazarSolicitud" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Rechazar Solicitud</button>
                    <button wire:click="aprobarSolicitud" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Aprobar Solicitud</button>
                    <button wire:click="$set('modalRevisarAprobacion', false)" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cerrar</button>
                </div>
            </div>
        </div>
    @endif

        
    
</div>

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

    <div class="mb-4 flex flex-wrap gap-2">


        <!-- Exportar -->
        <button
            class="px-3 py-1.5 text-sm bg-blue-500 text-white rounded-md hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="selectedPedidos.length === 0"
            wire:click="exportSelected"
        >
            Exportar
        </button>

        <!-- Eliminar -->
        <button
            class="px-3 py-1.5 text-sm bg-red-500 text-white rounded-md hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="selectedPedidos.length === 0"
            wire:click="deleteSelected"
        >
            Eliminar
        </button>

        <!-- Crear Tarea -->
        {{-- <button
            class="px-3 py-1.5 text-sm bg-gray-500 text-white rounded-md hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="selectedPedidos.length === 0"
            wire:click="abrirModalCrearTareaConPedidos"
        >
            Crear Tarea
        </button> --}}

        <!-- Crear Orden de Corte  La orden de corte esta combinada en las ordenes de produccion-->
        {{-- <button
            class="px-3 py-1.5 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="selectedPedidos.length === 0"
            wire:click="abrirModalCrearOrdenCorte"
        >
            Crear Orden de Corte
        </button> --}}

        
        <button
                class="px-3 py-1.5 text-sm bg-purple-600 text-white rounded-md hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="selectedPedidos.length === 0"
                wire:click="abrirModalCrearOrdenProduccion"
            >
           Crear Orden de Producción
       </button>



       
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
                <th class="px-2 py-1 border">Órdenes</th>
                <th class="px-2 py-1 border text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pedidos as $pedido)
                <tr class="hover:bg-gray-50 transition {{ $pedido->estado_produccion === 'COMPLETADO' ? 'bg-green-100' : '' }}">
                    <td class="px-2 py-1 border">
                        <input type="checkbox" wire:model="selectedPedidos" value="{{ $pedido->id }}" />
                    </td>
                    <td class="px-2 py-1 font-semibold border"
                        title="Proyecto {{ $pedido->proyecto_id }} – Pedido #{{ $pedido->id }}: {{ $pedido->descripcion_corta }}">
                        {{ $pedido->proyecto_id }}-{{ $pedido->id }}
                    </td>
                    <td class="px-2 py-1 border">
                        <div class="font-medium text-sm">{{ $pedido->producto->nombre ?? 'Sin producto' }}</div>
                        <div class="text-xs text-gray-500">{{ $pedido->producto->categoria->nombre ?? 'Sin categoría' }}</div>
                    </td>
                    <td class="px-2 py-1 border text-sm">{{ $pedido->total }} piezas</td>
                    <td class="px-2 py-1 border">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $pedido->estado === 'POR APROBAR' ? 'bg-yellow-400 text-black' : ($pedido->estado === 'APROBADO' ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-700') }}">
                            {{ $pedido->estado }}
                        </span>
                    </td>
                    <td class="px-2 py-1 border text-sm">{{ $pedido->fecha_produccion ?? 'N/D' }}</td>
                    <td class="px-2 py-1 border text-sm">{{ $pedido->fecha_embarque ?? 'N/D' }}</td>
                    <td class="px-2 py-1 border text-sm">{{ $pedido->fecha_entrega ?? 'N/D' }}</td>
                    <td class="px-2 py-1 border text-xs bg-gray-100 rounded text-gray-800">
                        {{ $pedido->ultimo_estatus_orden_produccion ?? $pedido->estado_produccion ?? 'N/D' }}
                    </td>
                    <td class="px-2 py-1 border text-center">
                        <button wire:click="verOrdenesDePedido({{ $pedido->id }})" class="text-blue-500 hover:underline text-xs">Ver más</button>
                    </td>
                    <td class="px-2 py-1 border space-y-1 text-center">
                        <button wire:click="abrirModal({{ $pedido->id }})" class="w-full px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-xs">Editar</button>
                       
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
                                <div><strong>Estado:</strong> {{ $orden['estado'] }}</div>

                                <div><strong>Creado:</strong> {{ $orden['creado'] }}</div>
                            </div>


                            @if ($orden['estado'] == 'TERMINADO' || $orden['estado'] == 'CANCELADO')
                                {{-- No mostrar botón cancelar --}}
                            @else
                                <button
                                    wire:click="cancelarOrden({{ $orden['id'] }})"
                                    class="text-red-600 hover:underline text-xs ml-4"
                                    onclick="return confirm('¿Seguro que deseas cancelar esta orden?')"
                                >
                                    ❌ Cancelar
                                </button>
                            @endif


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
                                    <strong>Fecha de Inicio de produccion:</strong>
                                    <span>{{ $orden['fecha_sin_iniciar'] ?? 'N/D' }}</span>
                                </div>
                                <div>
                                    <strong>Fecha De inicio de proceso:</strong>
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


    @if($modalCrearOrdenProduccion)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl flex flex-col max-h-[90vh]">
            <!-- Encabezado -->
            <div class="p-4 border-b">
                <h2 class="text-xl font-bold">Crear Orden de Producción</h2>
            </div>

            <!-- Cuerpo -->
            <div class="overflow-y-auto p-6 space-y-4">
                <!-- Pedidos seleccionados -->
                <div x-data="{ showPedidos: false }" class="border rounded p-4">
                    <div class="flex justify-between items-center cursor-pointer select-none" @click="showPedidos = !showPedidos">
                        <h3 class="font-semibold text-gray-700">
                            Pedidos seleccionados (IDs: {{ implode(', ', $selectedPedidos) }})
                        </h3>
                        <span class="text-sm text-blue-500 hover:underline">
                            <span x-show="!showPedidos">Mostrar</span>
                            <span x-show="showPedidos">Ocultar</span>
                        </span>
                    </div>
                    <div x-show="showPedidos" x-transition class="mt-3 space-y-2 text-sm text-gray-700">
                        @foreach($selectedPedidos as $pedidoId)
                            @php $pedido = \App\Models\Pedido::find($pedidoId); @endphp
                            @if($pedido)
                                <div>Pedido #{{ $pedido->id }} – {{ $pedido->producto->nombre ?? 'Sin producto' }}</div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Fecha de Inicio -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha de Inicio</label>
                    <input type="date" wire:model="ordenProd_fecha_inicio" class="w-full border border-gray-300 rounded p-2">
                    @error('ordenProd_fecha_inicio') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Usuario Asignado</label>
                    <select wire:model="ordenProd_usuario_asignado_id" class="w-full border border-gray-300 rounded p-2">
                        <option value="">-- Selecciona usuario staff --</option>
                        @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                        @endforeach
                    </select>
                    @error('ordenProd_usuario_asignado_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Tipo de Orden -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo de Orden</label>
                    <select wire:model="ordenProd_tipo" class="w-full border border-gray-300 rounded p-2">



                        <option value="CORTE">Corte</option>
                        <option value="SUBLIMADO">Sublimado</option>
                        <option value="COSTURA">Costura</option>
                        <option value="MAQUILA">Maquila</option>
                        <option value="FACTURACION">Facturacion</option>
                        <option value="ENVIO">Envio</option>
                        <option value="OTRO">Otro</option>
                        <option value="RECHAZADO">Rechazado</option>
                        
                    </select>
                    @error('ordenProd_tipo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Pie -->
            <div class="p-4 border-t flex justify-end gap-2 bg-white sticky bottom-0">
                <button wire:click="$set('modalCrearOrdenProduccion', false)"
                        class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
                    Cancelar
                </button>
                <button wire:click="guardarOrdenProduccion"
                        class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                    Crear Orden
                </button>
            </div>
        </div>
    </div>
    @endif


    @if($modalCrearOrden)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl flex flex-col max-h-[90vh]">
            <div class="p-4 border-b">
                <h2 class="text-xl font-bold">Crear Orden de Producción</h2>
            </div>

            <div class="overflow-y-auto p-6 space-y-4">
                <!-- En el modalCrearOrdenProduccion -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Flujo de Producción</label>
                    <select wire:model="ordenProd_flujo_id" class="w-full border border-gray-300 rounded p-2">
                        <option value="">-- Selecciona un flujo --</option>
                        @foreach($flujosProduccion as $flujo)
                            <option value="{{ $flujo->id }}">{{ $flujo->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tipo de Orden (bloqueado si hay flujo seleccionado) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo de Orden</label>
                    <select wire:model="ordenProd_tipo" 
                        class="w-full border border-gray-300 rounded p-2"
                        @if($ordenProd_flujo_id) disabled @endif
                    >
                        <option value="CORTE">Corte</option>
                        <option value="SUBLIMADO">Sublimado</option>
                        <option value="COSTURA">Costura</option>
                        <option value="MAQUILA">Maquila</option>
                        <option value="FACTURACION">Facturación</option>
                        <option value="ENVIO">Envío</option>
                        <option value="OTRO">Otro</option>
                        <option value="RECHAZADO">Rechazado</option>
                    </select>
                </div>


                <div>
                    <label class="block text-sm font-medium text-gray-700">Usuario Asignado</label>
                    <select wire:model="ordenProd_usuario_asignado_id" class="w-full border border-gray-300 rounded p-2">
                        <option value="">-- Selecciona usuario staff --</option>
                        @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                        @endforeach
                    </select>
                    @error('ordenProd_usuario_asignado_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Pedidos seleccionados -->
                <div x-data="{ showPedidos: false }" class="border rounded p-4">
                    <div class="flex justify-between items-center cursor-pointer select-none" @click="showPedidos = !showPedidos">
                        <h3 class="font-semibold text-gray-700">
                            Pedidos seleccionados (IDs: {{ implode(', ', $selectedPedidos) }})
                        </h3>
                        <span class="text-sm text-blue-500 hover:underline">
                            <span x-show="!showPedidos">Mostrar</span>
                            <span x-show="showPedidos">Ocultar</span>
                        </span>
                    </div>
                    <div x-show="showPedidos" x-transition class="mt-3 space-y-2 text-sm text-gray-700">
                        @foreach($selectedPedidos as $pedidoId)
                            @php $pedido = \App\Models\Pedido::find($pedidoId); @endphp
                            @if($pedido)
                                <div>Pedido #{{ $pedido->id }} – {{ $pedido->producto->nombre ?? 'Sin producto' }}</div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Fecha de inicio -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha de Inicio</label>
                    <input type="date"
                        wire:model="{{ $tipo_modal_orden === 'CORTE' ? 'ordenCorte_fecha_inicio' : 'ordenProd_fecha_inicio' }}"
                        class="w-full border border-gray-300 rounded p-2">
                </div>

                <!-- Usuario asignado (solo si NO es corte) -->
                @if($tipo_modal_orden && $tipo_modal_orden !== 'CORTE')
                <div>
                    <label class="block text-sm font-medium text-gray-700">Usuario Asignado</label>
                    <select wire:model="ordenProd_usuario_asignado_id" class="w-full border border-gray-300 rounded p-2">
                        <option value="">-- Selecciona usuario --</option>
                        @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Si es CORTE, mostrar tallas agrupadas -->
                @if($ordenCorte_tallas_json)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tallas agrupadas</label>
                        @foreach($ordenCorte_tallas_json as $clave => $info)
                            <div class="flex justify-between items-center border p-2 rounded mb-2 text-sm">
                                <div>
                                    <strong>{{ $info['grupo'] }} - {{ $info['talla'] }}</strong><br>
                                    <span>Cantidad: {{ $info['cantidad'] }}</span>
                                </div>
                                <div class="text-blue-600 font-semibold">A cortar: {{ max(0, $info['cantidad'] - ($info['stock'] ?? 0)) }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Características (opcional)</label>
                        <textarea wire:model="ordenCorte_caracteristicas" class="w-full border border-gray-300 rounded p-2"></textarea>
                    </div>
                @endif
            </div>

            <div class="p-4 border-t flex justify-end gap-2">
                <button wire:click="$set('modalCrearOrden', false)" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">
                    Cancelar
                </button>
                <button 
                    wire:click="guardarOrden" 
                    class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 cursor-pointer"
                   
                >
                    Crear Orden
                </button>
            </div>
        </div>
    </div>
    @endif
    
    
</div>

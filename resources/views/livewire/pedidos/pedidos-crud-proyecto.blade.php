<div class="max-w-6xl mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Gestión de Pedidos</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-3">
            {{ session('message') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-3">
        <button wire:click="abrirModal" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
            Nuevo Pedido
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="table-auto w-full border-collapse border border-gray-300 text-sm">
            <thead>
                <tr class="bg-gray-100 text-gray-700 text-sm">
                    <th class="border border-gray-300 p-2 w-32">ID</th>
                    <th class="border border-gray-300 p-2 w-40">Cliente</th>
                    <th class="border border-gray-300 p-2 w-56">Piezas Totales</th>
                    <th class="border border-gray-300 p-2 w-40">Dirección Fiscal</th>
                    <th class="border border-gray-300 p-2 w-40">Dirección Entrega</th>
                    <th class="border border-gray-300 p-2 w-32">Tipo Envío</th>
             
                    <th class="border border-gray-300 p-2 w-32">Estado</th>
                    @can('proyectopedidoscolumnafechaproduccion')
                    <th class="border border-gray-300 p-2 w-36">Producción</th>
                    @endcan
                    @can('proyectopedidoscolumnafechaenbarque')
                    <th class="border border-gray-300 p-2 w-36">Embarque</th>
                    @endcan
                    @can('proyectopedidoscolumnafechaEntrega')
                    <th class="border border-gray-300 p-2 w-36">Entrega</th>
                    @endcan
                    <th class="border border-gray-300 p-2 w-32">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedidos as $pedido)
                    <tr class="text-sm">
                        <td class="border border-gray-300 p-2 font-semibold">{{ $pedido->id.'-'.$pedido->proyecto_id }}</td>
                        <td class="border border-gray-300 p-2">{{ $pedido->cliente->nombre_empresa ?? 'Sin cliente' }}</td>
                        
                        <!-- Piezas Totales -->
                        <td class="border border-gray-300 p-2 font-semibold w-56">
                            @if($pedido->pedidoTallas->isNotEmpty())
                                @php
                                    // Agrupar por el grupo de tallas (no solo por el nombre de la talla)
                                    $tallasAgrupadas = $pedido->pedidoTallas->groupBy('grupo_talla_id');
                                @endphp
                                <ul class="list-none">
                                    @foreach($tallasAgrupadas as $grupoTallaId => $tallas)
                                        <li class="font-semibold text-gray-700 border-b pb-1 mt-2">
                                            {{ $tallas->first()->grupoTalla->nombre ?? 'Sin Grupo' }}
                                        </li>
                                        <ul class="list-disc list-inside text-gray-600">
                                            @foreach($tallas as $talla)
                                                <li>{{ $talla->talla->nombre ?? 'N/A' }}: {{ $talla->cantidad ?? '0' }}</li>
                                            @endforeach
                                        </ul>
                                    @endforeach
                                </ul>
                            
                            @endif
                            <ul class="list-none">
                                <li class="font-semibold text-gray-700 border-b pb-1 mt-2">
                                    Total
                                </li>
                                <ul>
                                    {{ $pedido->total }}
                                </ul>
                            </ul>

                            
                        </td>
            
                        <td class="border border-gray-300 p-2">{{ $pedido->direccion_fiscal ?? 'No definida' }}</td>
                        <td class="border border-gray-300 p-2">{{ $pedido->direccion_entrega ?? 'No definida' }}</td>
                        <td class="border border-gray-300 p-2">{{ $pedido->tipoEnvio->nombre ?? 'No definido' }}</td>
                       
                        
                        <!-- Estado con Colores -->
                        <td class="border border-gray-300 p-2 font-bold text-center">
                            <span class="px-2 py-1 rounded-lg text-white text-xs"
                                  style="background-color: 
                                      @if    ($pedido->estado == 'POR APROBAR') #FFDD57 
                                      @elseif($pedido->estado == 'APROBADO') #F39C12
                                      @elseif($pedido->estado == 'ENTREGADO') #3498DB
                                      @elseif($pedido->estado == 'RECHAZADO') #9B59B6
                                      @elseif($pedido->estado == 'ARCHIVADO') #E67E22
                                      @elseif($pedido->estado == 'POR REPROGRAMAR') #E74C3C
                                      @else #BDC3C7 @endif;">
                                {{ strtoupper($pedido->estado) }}
                            </span>
                        </td>
                        @can('proyectopedidoscolumnafechaproduccion')
                        <td class="border border-gray-300 p-2">{{ $pedido->fecha_produccion ?? 'No definida' }}</td>
                        @endcan
                        @can('proyectopedidoscolumnafechaenbarque')
                        <td class="border border-gray-300 p-2">{{ $pedido->fecha_embarque ?? 'No definida' }}</td>
                        @endcan
                        @can('proyectopedidoscolumnafechaEntrega')
                        <td class="border border-gray-300 p-2">{{ $pedido->fecha_entrega ?? 'No definida' }}</td>
                        @endcan
                        <td class="border  flex space-x-2 justify-center">

                            @hasanyrole('admin')
                            <button wire:click="abrirModal({{ $pedido->id }})" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-3 py-1 rounded">
                                Editar
                            </button>
                            @endhasanyrole

                            @if ($pedido->estado == 'POR APROBAR')
                            <button wire:click="abrirModal({{ $pedido->id }})" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-3 py-1 rounded">
                                Editar
                            </button>
                            @endif

                            @if ($pedido->proyecto && $pedido->proyecto->estado === 'DISEÑO APROBADO' && $pedido->estado == 'POR APROBAR')
                            <button wire:click="confirmarAprobacion({{ $pedido->id }})"
                                    class="bg-green-600 hover:bg-green-700 text-white font-semibold px-3 py-1 rounded">
                                Aprobar
                            </button>
                            @endif

                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

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
                            <select wire:model="estado" class="w-full border border-gray-300 rounded p-2">
                                <option value="POR APROBAR">Por aprobar</option>
                                <option value="APROBADO">Aprobado</option>
                                <option value="ENTREGADO">Entrega</option>
                                <option value="RECHAZADO">Rechazado</option>
                                <option value="ARCHIVADO">Archivado</option>

                            </select>
                            @error('estado') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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
                <h6 class="text-lg font-semibold mb-2">Cliente</h6>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Selecciona un Cliente</label>
                    <select wire:model="cliente_id" class="w-full border border-gray-300 rounded p-2">
                        <option value="">-- Seleccionar Cliente --</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}">{{ $cliente->nombre_empresa }}</option>
                        @endforeach
                    </select>
                    @error('cliente_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

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
                        <li><strong>Cliente:</strong> {{ $pedido->cliente->nombre_empresa ?? 'Sin cliente' }}</li>
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

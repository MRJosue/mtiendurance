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
                    <th class="border border-gray-300 p-2 w-28">Tipo</th>
                    <th class="border border-gray-300 p-2 w-32">Estado</th>
                    <th class="border border-gray-300 p-2 w-36">Producción</th>
                    <th class="border border-gray-300 p-2 w-36">Embarque</th>
                    <th class="border border-gray-300 p-2 w-36">Entrega</th>
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
                                <ul class="list-disc list-inside">
                                    @foreach($pedido->pedidoTallas as $talla)
                                        <li>{{ $talla->talla->nombre ?? 'N/A' }}: {{ $talla->cantidad ?? '0' }}</li>
                                    @endforeach
                                </ul>
                            @else
                                {{ number_format($pedido->total, 2) }}
                            @endif
                        </td>
            
                        <td class="border border-gray-300 p-2">{{ $pedido->direccion_fiscal ?? 'No definida' }}</td>
                        <td class="border border-gray-300 p-2">{{ $pedido->direccion_entrega ?? 'No definida' }}</td>
                        <td class="border border-gray-300 p-2">{{ $pedido->tipoEnvio->nombre ?? 'No definido' }}</td>
                        <td class="border border-gray-300 p-2">{{ $pedido->tipo }}</td>
                        
                        <!-- Estado con Colores -->
                        <td class="border border-gray-300 p-2 font-bold text-center">
                            <span class="px-2 py-1 rounded-lg text-white text-xs"
                                  style="background-color: 
                                      @if($pedido->estado == 'POR PROGRAMAR') #FFDD57 
                                      @elseif($pedido->estado == 'PROGRAMADO') #F39C12
                                      @elseif($pedido->estado == 'IMPRESIÓN') #3498DB
                                      @elseif($pedido->estado == 'PRODUCCIÓN') #9B59B6
                                      @elseif($pedido->estado == 'COSTURA') #E67E22
                                      @elseif($pedido->estado == 'ENTREGA') #27AE60
                                      @elseif($pedido->estado == 'FACTURACIÓN') #2C3E50
                                      @elseif($pedido->estado == 'COMPLETADO') #1ABC9C
                                      @elseif($pedido->estado == 'RECHAZADO') #E74C3C
                                      @else #BDC3C7 @endif;">
                                {{ strtoupper($pedido->estado) }}
                            </span>
                        </td>
            
                        <td class="border border-gray-300 p-2">{{ $pedido->fecha_produccion ?? 'No definida' }}</td>
                        <td class="border border-gray-300 p-2">{{ $pedido->fecha_embarque ?? 'No definida' }}</td>
                        <td class="border border-gray-300 p-2">{{ $pedido->fecha_entrega ?? 'No definida' }}</td>
                        <td class="border  flex space-x-2 justify-center">
                            <button wire:click="abrirModal({{ $pedido->id }})" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-3 py-1 rounded">
                                Editar
                            </button>
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
        <div class="bg-white rounded shadow-lg w-full max-w-lg">
            <div class="flex items-center justify-between border-b border-gray-200 p-4">
                <h5 class="text-xl font-bold">{{ $pedidoId ? 'Editar Pedido' : 'Nuevo Pedido' }}</h5>
                <button class="text-gray-500 hover:text-gray-700" wire:click="$set('modal', false)">&times;</button>
            </div>

            <div class="p-4">
                <!-- SECCIÓN: Información General -->
                <!-- SECCIÓN: Cantidades por Tallas -->
                @if(!empty($tallas_disponibles))
                    <h6 class="text-lg font-semibold mt-4">Cantidades por Tallas</h6>
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        @foreach($tallas_disponibles as $talla)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ $talla['nombre'] }}</label>
                                <input type="number" class="w-full border border-gray-300 rounded p-2"
                                    wire:model.lazy="cantidades_tallas.{{ $talla['id'] }}" min="0">
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Campo Total (Oculto si hay tallas seleccionadas) -->
                @if($mostrar_total)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Total</label>
                        <input type="number" step="0.01" class="w-full border border-gray-300 rounded p-2"
                            wire:model="total">
                        @error('total') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tipo</label>
                        <select wire:model="tipo" class="w-full border border-gray-300 rounded p-2">
                            <option value="PEDIDO">Pedido</option>
                            <option value="MUESTRA">Muestra</option>
                        </select>
                        @error('tipo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Estado</label>
                        <select wire:model="estado" class="w-full border border-gray-300 rounded p-2">
                            <option value="POR PROGRAMAR">Por Programar</option>
                            <option value="PROGRAMADO">Programado</option>
                            <option value="IMPRESIÓN">Impresión</option>
                            <option value="PRODUCCIÓN">Producción</option>
                            <option value="COSTURA">Costura</option>
                            <option value="ENTREGA">Entrega</option>
                            <option value="FACTURACIÓN">Facturación</option>
                            <option value="COMPLETADO">Completado</option>
                            <option value="RECHAZADO">Rechazado</option>
                        </select>
                        @error('estado') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
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

                <!-- SECCIÓN: Fechas -->
                <h6 class="text-lg font-semibold mb-2">Fechas</h6>
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Producción</label>
                        <input type="date" class="w-full border border-gray-300 rounded p-2" wire:model="fecha_produccion">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Embarque</label>
                        <input type="date" class="w-full border border-gray-300 rounded p-2" wire:model="fecha_embarque">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Entrega</label>
                        {{-- <input wire:change="on_Calcula_Fechas_Entrega" type="date" class="w-full border border-gray-300 rounded p-2" wire:model="fecha_entrega">
                         --}}
                         <input 
                         wire:change="validarFechaEntrega"
                         wire:model="fecha_entrega"
                         type="date" 
                         class="w-full mt-1 border rounded-lg p-2"
                         min="{{ date('Y-m-d') }}"
                         id="fechaEntrega">
                    </div>
                </div>
            </div>

            <!-- SECCIÓN: Botones de Acción -->
            <div class="flex items-center justify-end border-t border-gray-200 p-4 space-x-2">
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

</div>

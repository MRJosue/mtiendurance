<div x-data="{ selected: @entangle('selected') }"
    x-on:dropdown-cerrar.window="document.body.dispatchEvent(new MouseEvent('click', { bubbles: true }))"
 class="container mx-auto p-6">

    {{-- Filtros minimizables --}}
    @if($mostrarFiltros)
        <div x-data="{ abierto: @entangle('mostrarFiltros') }" class="mb-6">
            <template x-if="abierto">
                <div class="w-full bg-white border border-gray-200 shadow-md rounded-lg">
                    <div class="flex justify-between items-center p-4 border-b">
                        <h2 class="text-lg font-bold text-gray-700">Filtros</h2>
                        <div class="flex items-center gap-2">
                            <button
                                wire:click="buscarPorFiltros"
                                class="bg-white border border-gray-300 text-gray-700 px-3 py-1 rounded hover:bg-gray-100 text-sm">
                                Filtrar
                            </button>
                            <button
                                @click="abierto = false"
                                class="text-gray-500 hover:text-gray-700 text-xl leading-none"
                                title="Cerrar filtros">✕</button>
                        </div>
                    </div>

                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <input class="w-full border rounded px-3 py-2" placeholder="ID o Proyecto-ID"
                               wire:model.live.debounce.500ms="f_id" />

                        <input class="w-full border rounded px-3 py-2" placeholder="Producto / Categoría"
                               wire:model.live.debounce.500ms="f_producto" />

                        <input class="w-full border rounded px-3 py-2" placeholder="Cliente"
                               wire:model.live.debounce.500ms="f_cliente" />

                        <input class="w-full border rounded px-3 py-2" placeholder="Archivo / Versión"
                               wire:model.live.debounce.500ms="f_archivo" />

                        <input class="w-full border rounded px-3 py-2" placeholder="Piezas (>=)"
                               wire:model.live.debounce.500ms="f_total_min" />

                        <input class="w-full border rounded px-3 py-2" placeholder="Solicitó (usuario estado {{ $estadoColumna }})"
                               wire:model.live.debounce.500ms="f_usuario" />

                        <input class="w-full border rounded px-3 py-2" placeholder="Instrucciones"
                               wire:model.live.debounce.500ms="f_instrucciones" />

                        <select class="w-full border rounded px-3 py-2"
                                wire:model.live="f_estatus">
                            <option value="">Estatus (todos)</option>
                            <option value="PENDIENTE">PENDIENTE</option>
                            <option value="SOLICITADA">SOLICITADA</option>
                            <option value="MUESTRA LISTA">MUESTRA LISTA</option>
                            <option value="ENTREGADA">ENTREGADA</option>
                            <option value="CANCELADA">CANCELADA</option>
                        </select>
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

    {{-- (A) Acciones --}}
    <div class="mb-4 flex flex-wrap space-y-2 sm:space-y-0 sm:space-x-4">
        <button
            class="w-full sm:w-auto px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="selected.length === 0"
            wire:click="abrirModalEntregarSeleccion">
            Entregar selección
        </button>
    </div>

    {{-- Tabla --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow min-h-64 pb-8">
        <table class="min-w-full border-collapse border border-gray-200 rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border-b px-4 py-2">
                        <input type="checkbox"
                            @change="
                                const checked = $event.target.checked;
                                selected = checked ? @js($pedidos->pluck('id')) : [];
                            " />
                    </th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">ID</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Producto</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Cliente</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Archivo y versión</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Piezas</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Solicitó</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Instrucciones</th>

                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Estado</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedidos as $pedido)
                    @php
                        $estatusMuestra = strtoupper($pedido->estatus_muestra ?? '');
                        $clase = collect([
                            'PENDIENTE'      => 'bg-yellow-400 text-black',
                            'SOLICITADA'     => 'bg-blue-500 text-white',
                            'MUESTRA LISTA'  => 'bg-emerald-600 text-white',
                            'ENTREGADA'      => 'bg-green-600 text-white',
                            'CANCELADA'      => 'bg-gray-500 text-white',
                        ])->get($estatusMuestra, 'bg-gray-300 text-gray-800');

                        $reg = $ultimosPorEstado->get($pedido->id) ?? null;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="border-b px-4 py-2">
                            <input type="checkbox"
                                value="{{ $pedido->id }}"
                                :checked="selected.includes({{ $pedido->id }})"
                                @change="
                                    if ($event.target.checked) selected.push({{ $pedido->id }});
                                    else selected = selected.filter(i => i !== {{ $pedido->id }});
                                " />
                        </td>

                        <td
                            class="p-2 px-4 py-2 font-semibold min-w-[4rem]"
                            title="{{ $pedido->tooltip_clave }}"
                        >
                            {!! $pedido->clave_link !!}
                        </td>

                        <td class="border-b px-4 py-2">
                            <div class="font-medium">{{ $pedido->producto->nombre ?? 'Sin producto' }}</div>
                            <div class="text-xs text-gray-500">{{ $pedido->producto->categoria->nombre ?? 'Sin categoría' }}</div>
                        </td>

                        <td class="border-b px-4 py-2"> {{ $pedido->usuario->name ?? $pedido->cliente->razon_social ?? 'Cliente' }}</td>

                        <td class="border-b px-4 py-2 align-top">
                            @if($pedido->archivo?->verimagen)
                                <div class="flex items-center justify-between space-x-3 max-w-[20rem]">
                                    <div class="flex items-center space-x-3 min-w-0 flex-1">
                                        @if($pedido->archivo->es_imagen)
                                            <a href="{{ $pedido->archivo->verimagen }}" target="_blank" rel="noopener" class="shrink-0">
                                                <img src="{{ $pedido->archivo->verimagen }}" alt="{{ $pedido->archivo->nombre_archivo }}"
                                                     class="h-12 w-12 rounded object-cover ring-1 ring-gray-200" />
                                            </a>
                                        @endif
                                        <div class="min-w-0">
                                            <a href="{{ $pedido->archivo->verimagen }}" target="_blank" rel="noopener"
                                               class="text-blue-600 hover:underline block truncate"
                                               title="{{ $pedido->archivo->nombre_archivo }}">
                                                {{ $pedido->archivo->nombre_archivo }}
                                            </a>
                                            <div class="text-xs text-gray-500">Versión: {{ $pedido->archivo->version ?? '-' }}</div>
                                        </div>
                                    </div>

                                </div>
                            @else
                                <span class="text-gray-500">Sin archivo</span>
                            @endif
                        </td>

                        <td class="border-b px-4 py-2">{{ $pedido->total ?? 'N/A' }}</td>

                        <td class="border-b px-4 py-2">
                            @if($reg && $reg->usuario)
                                <span class="font-medium">{{ $reg->usuario->name }}</span>
                                <span class="text-xs text-gray-500">#{{ $reg->usuario_id }}</span>
                            @else
                                <span class="text-gray-500">—</span>
                            @endif
                        </td>

                        <td class="border-b px-4 py-2">{{ $pedido->instrucciones_muestra ?? 'N/A' }}</td>

                        <td class="border-b px-4 py-2">
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $clase }}">
                                {{ $estatusMuestra ?: 'N/A' }}
                            </span>
                        </td>
                        

                        {{-- (B) Acciones por fila --}}
                        <td class="border-b px-4 py-2">

                            <x-dropdown  >
                                <x-dropdown.item>
                                    <b 
                                     
                                    aria-label="Marcar como SOLICITADA"
                                    wire:click.stop="abrirModalEntregar({{ $pedido->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="marcarSolicitada">Marcar como Etregada</b>
                                </x-dropdown.item>
                            
                                {{-- <x-dropdown.item separator>
                                    <b   
                                        wire:click.stop='cancelarMuestra([{{ $pedido->id }}])'
                                        wire:loading.attr="disabled"
                                        wire:target="cancelarMuestra">Cancelar muestra</b>
                                </x-dropdown.item> --}}
                            
                                <x-dropdown.item separator>
                                    <b 
                                       
                                    wire:click.stop="abrirModalEstados({{ $pedido->id }})" >Ver Estados</b>
                                </x-dropdown.item>
                            </x-dropdown>

                            

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

       {{-- === NUEVO: Modal de Estados === --}}
    @if($modalEstadosOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50"></div>

            <div class="relative bg-white w-full max-w-3xl rounded-2xl shadow-xl">
                {{-- Header --}}
                <div class="flex items-center justify-between px-5 py-3 border-b">
                    <h3 class="text-lg font-semibold">
                        Estados del Pedido #{{ $pedidoEstadosId }}
                    </h3>
                    <button
                        class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300"
                        wire:click="cerrarModalEstados"
                    >
                        x
                    </button>
                </div>

                {{-- Body con scroll --}}
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <div class="max-h-80 overflow-y-auto border rounded">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-100 sticky top-0">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700">ID</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700">Estado</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700">Usuario</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700">Comentario</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700">Fecha inicio</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700">Fecha fin</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700">Registrado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($estadosModal as $e)
                                        <tr class="border-t hover:bg-gray-50">
                                            <td class="px-3 py-2">{{ $e['id'] }}</td>
                                            <td class="px-3 py-2">{{ $e['estado'] }}</td>
                                            <td class="px-3 py-2">
                                                <span class="font-medium">{{ $e['usuario'] }}</span>
                                                <span class="text-xs text-gray-500">#{{ $e['usuario_id'] }}</span>
                                            </td>
                                            <td class="px-3 py-2">
                                                {{ $e['comentario'] ?? '—' }}
                                            </td>
                                            <td class="px-3 py-2">{{ $e['fecha_inicio'] ?? '—' }}</td>
                                            <td class="px-3 py-2">{{ $e['fecha_fin'] ?? '—' }}</td>
                                            <td class="px-3 py-2">{{ $e['created_at'] ?? '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-3 py-6 text-center text-gray-500">
                                                Sin estados registrados.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-5 py-3 border-t flex justify-end">
                    <button
                        class="px-4 py-2 rounded-lg bg-gray-700 text-white hover:bg-gray-800"
                        wire:click="cerrarModalEstados"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- (C) NUEVO: Modal Confirmar Entrega --}}
    @if($modalEntregaOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" wire:click="cerrarModalEntregar"></div>

            <div class="relative bg-white w-full max-w-xl rounded-2xl shadow-xl">
                <div class="flex items-center justify-between px-5 py-3 border-b">
                    <h3 class="text-lg font-semibold">Confirmar entrega del Pedido #{{ $entregaPedidoId }}</h3>
                    <button class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300" wire:click="cerrarModalEntregar" aria-label="Cerrar">x</button>
                </div>

                <form wire:submit.prevent="confirmarEntrega" class="px-5 py-4">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de entrega</label>
                            <select class="w-full border rounded px-3 py-2" wire:model.live="entregaSeleccion">
                              
                                <option value="DIGITAL">DIGITAL</option>
                                <option value="FISICA">FISICA</option>
                            </select>
                            @error('entregaSeleccion') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Evidencia (1 archivo)</label>
                            <input type="file" class="w-full border rounded px-3 py-2"
                                   wire:model="evidencia"
                                   accept="image/*,application/pdf" />
                            @error('evidencia') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                            @if($evidencia && str_starts_with($evidencia->getMimeType(), 'image/'))
                                <div class="mt-3">
                                    <img src="{{ $evidencia->temporaryUrl() }}" alt="Vista previa"
                                        class="h-32 rounded object-cover ring-1 ring-gray-200">
                                </div>
                            @endif
                        </div>

                        <div class="text-sm text-gray-600">
                            Al confirmar, el pedido se marcará como <span class="font-semibold">ENTREGADA</span>
                            y la evidencia se guardará ligada al pedido.
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end gap-2 border-t pt-4">
                        <button type="button" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300" wire:click="cerrarModalEntregar">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 disabled:opacity-50" wire:loading.attr="disabled">
                            Confirmar entrega
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif


    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // JS opcional aquí
    });
    </script>
</div>

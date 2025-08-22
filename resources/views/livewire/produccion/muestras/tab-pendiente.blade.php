{{-- resources/views/livewire/admin/muestras/tab-pendiente.blade.php --}}
<div x-data="{ selected: @entangle('selected') }" class="container mx-auto p-6">

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

                    <input class="w-full border rounded px-3 py-2" placeholder="Solicitado por ({{ $estadoColumna }})"
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



    {{-- Acciones --}}
    <div class="mb-4 flex flex-wrap space-y-2 sm:space-y-0 sm:space-x-4">
        <button
            class="w-full sm:w-auto px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="selected.length === 0"
            wire:click="marcarSolicitada"
        >
            Marcar como SOLICITADA
        </button>

        <button
            class="w-full sm:w-auto px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="selected.length === 0"
            wire:click="cancelarMuestra"
        >
            Cancelar Seleccionados
        </button>
    </div>

    {{-- Tabla --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full border-collapse border border-gray-200 rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border-b px-4 py-2">
                        <input type="checkbox"
                            @change="
                                const checked = $event.target.checked;
                                selected = checked ? @js($pedidos->pluck('id')) : [];
                            "
                        />
                    </th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">ID</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Producto</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Cliente</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Archivo y versión</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Piezas</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Solicitado por</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Instrucciones</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Estatus</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedidos as $pedido)
                    <tr class="hover:bg-gray-50">
                        <td class="border-b px-4 py-2">
                            <input type="checkbox"
                                value="{{ $pedido->id }}"
                                :checked="selected.includes({{ $pedido->id }})"
                                @change="
                                    if ($event.target.checked) selected.push({{ $pedido->id }});
                                    else selected = selected.filter(i => i !== {{ $pedido->id }});
                                "
                            />
                        </td>

                        <td class="border-b px-4 py-2">{{ $pedido->proyecto_id }}-{{ $pedido->id }}</td>

                        <td class="border-b px-4 py-2">
                            <div class="font-medium">{{ $pedido->producto->nombre ?? 'Sin producto' }}</div>
                            <div class="text-xs text-gray-500">{{ $pedido->producto->categoria->nombre ?? 'Sin categoría' }}</div>
                        </td>

                        <td class="border-b px-4 py-2"> {{ $pedido->usuario->name ?? $pedido->cliente->razon_social ?? 'Cliente' }}</td>

                        <td class="border-b px-4 py-2 align-top">
                            @if($pedido->archivo?->verimagen)
                                <div class="flex items-center justify-between space-x-3 max-w-[20rem]">
                                    {{-- Archivo --}}
                                    <div class="flex items-center space-x-3 min-w-0 flex-1">
                                        @if($pedido->archivo->es_imagen)
                                            <a href="{{ $pedido->archivo->verimagen }}" target="_blank" rel="noopener" class="shrink-0">
                                                <img
                                                    src="{{ $pedido->archivo->verimagen }}"
                                                    alt="{{ $pedido->archivo->nombre_archivo }}"
                                                    class="h-12 w-12 rounded object-cover ring-1 ring-gray-200"
                                                />
                                            </a>
                                        @endif

                                        <div class="min-w-0">
                                            <a href="{{ $pedido->archivo->verimagen }}"
                                            target="_blank" rel="noopener"
                                            class="text-blue-600 hover:underline block truncate"
                                            title="{{ $pedido->archivo->nombre_archivo }}">
                                                {{ $pedido->archivo->nombre_archivo }}
                                            </a>
                                            <div class="text-xs text-gray-500">
                                                Versión: {{ $pedido->archivo->version ?? '-' }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Botón ir a diseño --}}
                                    <div class="relative group shrink-0">
                                        <x-mini-button 
                                            rounded 
                                            icon="clipboard" 
                                            flat 
                                            red 
                                            interaction="negative"  
                                            href="{{ route('proyecto.show', $pedido->proyecto_id) }}"  
                                        />
                                        <div class="absolute z-10 w-max left-1/2 -translate-x-1/2 -top-8 px-2 py-1 text-xs bg-gray-800 text-white rounded shadow opacity-0 group-hover:opacity-100 pointer-events-none transition">
                                            Ir a Diseño
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-gray-500">Sin archivo</span>
                            @endif
                        </td>

                        <td class="border-b px-4 py-2">{{ $pedido->total ?? 'N/A' }}</td>

                        {{-- Solicitado por (usamos el eager de estados limitado a 1) --}}
                        <td class="border-b px-4 py-2">
                            @php $reg = $pedido->estados->first(); @endphp
                            @if($reg && $reg->usuario)
                                <span class="font-medium">{{ $reg->usuario->name }}</span>
                                <span class="text-xs text-gray-500">#{{ $reg->usuario_id }}</span>
                            @else
                                <span class="text-gray-500">—</span>
                            @endif
                        </td>

                        <td class="border-b px-4 py-2">{{ $pedido->instrucciones_muestra ?? 'N/A' }}</td>

                        {{-- Badge de estatus_muestra --}}
                        <td class="border-b px-4 py-2">
                            @php
                                $estatusMuestra = strtoupper($pedido->estatus_muestra ?? '');
                                $clase = collect([
                                    'PENDIENTE'      => 'bg-yellow-400 text-black',
                                    'SOLICITADA'     => 'bg-blue-500 text-white',
                                    'MUESTRA LISTA'  => 'bg-emerald-600 text-white',
                                    'ENTREGADA'      => 'bg-green-600 text-white',
                                    'CANCELADA'      => 'bg-gray-500 text-white',
                                ])->get($estatusMuestra, 'bg-gray-300 text-gray-800');
                            @endphp
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $clase }}">
                                {{ $estatusMuestra ?: 'N/A' }}
                            </span>
                        </td>

                        <td class="border-b px-4 py-2">
                            <div class="flex flex-wrap items-center gap-2">
                                {{-- Marcar SOLICITADA --}}
                                <div class="relative group">
                                <button
                                    type="button"
                                    aria-label="Marcar como SOLICITADA"
                                    class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400/50 disabled:opacity-50 disabled:cursor-not-allowed"
                                    wire:click.stop='marcarSolicitada([{{ $pedido->id }}])'
                                    wire:loading.attr="disabled"
                                    wire:target="marcarSolicitada"
                                >
                                    {{-- icono check --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0L3.293 9.957a1 1 0 111.414-1.414l4 4 6.543-6.543a1 1 0 011.457 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="hidden sm:inline">Marcar SOLICITADA</span>
                                    {{-- spinner mientras corre la acción --}}
                                    <svg class="ml-1 h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" wire:loading wire:target="marcarSolicitada">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                                    </svg>
                                </button>
                                {{-- tooltip (solo en móviles/estrecho mostramos el texto arriba) --}}
                                <div class="absolute z-10 w-max left-1/2 -translate-x-1/2 -top-8 px-2 py-1 text-xs bg-gray-800 text-white rounded shadow opacity-0 group-hover:opacity-100 pointer-events-none transition sm:hidden">
                                    Marcar SOLICITADA
                                </div>

                                {{-- Cancelar --}}
                                <div class="relative group">
                                    <button
                                        type="button"
                                        aria-label="Cancelar muestra"
                                        class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-red-600 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-400/50"
                                        wire:click.stop='cancelarMuestra([{{ $pedido->id }}])'
                                        wire:loading.attr="disabled"
                                        wire:target="cancelarMuestra"
                                    >
                                        {{-- icono X --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 8.586l4.95-4.95a1 1 0 111.414 1.414L11.414 10l4.95 4.95a1 1 0 01-1.414 1.414L10 11.414l-4.95 4.95a1 1 0 01-1.414-1.414L8.586 10l-4.95-4.95A1 1 0 115.05 3.636L10 8.586z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="hidden sm:inline">Cancelar</span>
                                        <svg class="ml-1 h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" wire:loading wire:target="cancelarMuestra">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                                        </svg>
                                    </button>
                                    <div class="absolute z-10 w-max left-1/2 -translate-x-1/2 -top-8 px-2 py-1 text-xs bg-gray-800 text-white rounded shadow opacity-0 group-hover:opacity-100 pointer-events-none transition sm:hidden">
                                        Cancelar muestra
                                    </div>
                                </div>

                                </div>

                                {{-- Ver estados --}}
                                <div class="relative group">
                                <button
                                    type="button"
                                    aria-label="Ver estados"
                                    class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-slate-700 text-white hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-400/50"
                                    wire:click.stop="abrirModalEstados({{ $pedido->id }})"
                                >
                                    {{-- icono eye --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 5c-7.633 0-11 7-11 7s3.367 7 11 7 11-7 11-7-3.367-7-11-7zm0 12a5 5 0 110-10 5 5 0 010 10z"/><circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <span class="hidden sm:inline">Ver estados</span>
                                </button>


                                

                                <div class="absolute z-10 w-max left-1/2 -translate-x-1/2 -top-8 px-2 py-1 text-xs bg-gray-800 text-white rounded shadow opacity-0 group-hover:opacity-100 pointer-events-none transition sm:hidden">
                                    Ver estados
                                </div>


                                </div>
                            </div>
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

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Aquí tus scripts específicos si los necesitas
    });
    </script>
</div>

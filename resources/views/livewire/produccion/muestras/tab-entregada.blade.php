{{-- resources/views/livewire/admin/muestras/tab-pendiente.blade.php --}}
<div x-data="{ selected: @entangle('selected') }" 
x-on:dropdown-cerrar.window="document.body.dispatchEvent(new MouseEvent('click', { bubbles: true }))"
 class="container mx-auto p-6 text-gray-900 dark:text-gray-100">

    {{-- Filtros minimizables --}}
        @if($mostrarFiltros)
            <div x-data="{ abierto: @entangle('mostrarFiltros') }" class="mb-6">
                <template x-if="abierto">
                <div class="w-full rounded-lg border border-gray-200 bg-white shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between border-b border-gray-200 p-4 dark:border-gray-700">
                        <h2 class="text-lg font-bold text-gray-700 dark:text-gray-100">Filtros</h2>
                            <div class="flex items-center gap-2">
                                <button
                                    wire:click="buscarPorFiltros"
                                class="rounded border border-gray-300 bg-white px-3 py-1 text-sm text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700">
                                    Filtrar
                                </button>
                                <button
                                    @click="abierto = false"
                                class="text-xl leading-none text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                    title="Cerrar filtros">✕</button>
                            </div>
                        </div>

                        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <input class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" placeholder="ID o Proyecto-ID"
                                wire:model.live.debounce.500ms="f_id" />

                        <input class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" placeholder="Producto / Categoría"
                                wire:model.live.debounce.500ms="f_producto" />

                        <input class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" placeholder="Cliente"
                                wire:model.live.debounce.500ms="f_cliente" />

                            {{-- <input class="w-full border rounded px-3 py-2" placeholder="Archivo / Versión"
                                wire:model.live.debounce.500ms="f_archivo" /> --}}

                        <input class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" placeholder="Piezas (>=)"
                                wire:model.live.debounce.500ms="f_total_min" />

                            {{-- <input class="w-full border rounded px-3 py-2" placeholder="Solicitado por ({{ $estadoColumna }})"
                                wire:model.live.debounce.500ms="f_usuario" /> --}}

                            {{-- <input class="w-full border rounded px-3 py-2" placeholder="Instrucciones"
                                wire:model.live.debounce.500ms="f_instrucciones" /> --}}

                            {{-- <select class="w-full border rounded px-3 py-2"
                                    wire:model.live="f_estatus">
                                <option value="">Estatus (todos)</option>
                                <option value="PENDIENTE">PENDIENTE</option>
                                <option value="SOLICITADA">SOLICITADA</option>
                                <option value="MUESTRA LISTA">MUESTRA LISTA</option>
                                <option value="ENTREGADA">ENTREGADA</option>
                                <option value="CANCELADA">CANCELADA</option>
                            </select> --}}
                        </div>
                    </div>
                </template>

                <template x-if="!abierto">
                    <div class="mb-4">
                    <button @click="abierto = true" class="text-sm text-blue-600 hover:underline dark:text-blue-400">
                            Mostrar Filtros
                        </button>
                    </div>
                </template>
            </div>
        @else
            <div class="mb-4">
            <button wire:click="$set('mostrarFiltros', true)" class="text-sm text-blue-600 hover:underline dark:text-blue-400">
                    Mostrar Filtros
                </button>
            </div>
        @endif


    {{-- Acciones --}}
    <div class="mb-4 flex flex-wrap space-y-2 sm:space-y-0 sm:space-x-4">
        {{-- <button
            class="w-full sm:w-auto px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="selected.length === 0"
            wire:click="marcarSolicitada"
        >
            Marcar como Entregada
        </button> --}}
    </div>

    {{-- Tabla --}}
    <div class="min-h-64 overflow-x-auto rounded-lg bg-white pb-8 shadow dark:bg-gray-800">
        <table class="min-w-full rounded-lg border border-collapse border-gray-200 dark:border-gray-700">
            <thead class="bg-gray-100 dark:bg-gray-900/70">
                <tr>
                    <th class="border-b px-4 py-2">
                        <input type="checkbox"
                            @change="
                                const checked = $event.target.checked;
                                selected = checked ? @js($pedidos->pluck('id')) : [];
                            "
                        />
                    </th>
                    <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">ID</th>
                    <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Producto</th>
                    <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Cliente</th>
                    <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Archivo y versión</th>
                    <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Piezas</th>
                    <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Solicitó</th>
                    <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Instrucciones</th>
                    <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Estatus</th>
                    <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Tipo de entrega</th>
                    <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Evidencia</th>
                    <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedidos as $pedido)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
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
                        <td
                            class="p-2 px-4 py-2 font-semibold min-w-[4rem]"
                            title="{{ $pedido->tooltip_clave }}"
                        >
                            {!! $pedido->clave_link !!}
                        </td>

                        <td class="border-b px-4 py-2">
                            <div class="font-medium">{{ $pedido->producto->nombre ?? 'Sin producto' }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $pedido->producto->categoria->nombre ?? 'Sin categoría' }}</div>
                        </td>

                        <td class="border-b px-4 py-2"> {{ $pedido->usuario->name ?? $pedido->cliente->razon_social ?? 'Cliente' }}</td>
                        

                        <td class="border-b px-4 py-2">
                            @if($pedido->archivo)
                                <a href="{{ Storage::url($pedido->archivo->ruta_archivo) }}"
                                class="text-blue-600 hover:underline dark:text-blue-400"
                                   target="_blank">
                                   {{ $pedido->archivo->nombre_archivo }}
                                </a>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Versión: {{ $pedido->archivo->version ?? '-' }}</div>
                            @else
                                <span class="text-gray-500 dark:text-gray-400">Sin archivo</span>
                            @endif


                        </td>

                        <td class="border-b px-4 py-2">{{ $pedido->total ?? 'N/A' }}</td>
                        
                        {{-- Celda --}}
                        <td class="border-b px-4 py-2">
                            @php $reg = $pedido->estados->first(); @endphp
                            @if($reg && $reg->usuario)
                                <span class="font-medium">{{ $reg->usuario->name }}</span>
                            @else
                                <span class="text-gray-500 dark:text-gray-400">—</span>
                            @endif
                        </td>


                        <td class="border-b px-4 py-2">{{ $pedido->instrucciones_muestra ?? 'N/A' }}</td>

                        {{-- Badge de estatus_muestra con tus colores --}}
                        <td class="border-b px-4 py-2">
                            @php
                                $estatusMuestra = strtoupper($pedido->estatus_muestra ?? '');
                                $clase = collect([
                                    'PENDIENTE'      => 'bg-yellow-400 text-black',
                                    'SOLICITADA'     => 'bg-blue-500 text-white',
                                    'MUESTRA LISTA'  => 'bg-emerald-600 text-white',
                                    'ENTREGADA'      => 'bg-green-600 text-white',
                                    'CANCELADA'      => 'bg-gray-500 text-white',
                                ])->get($estatusMuestra, 'bg-gray-300 text-gray-800 dark:bg-gray-700 dark:text-gray-200');
                            @endphp
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $clase }}">
                                {{ $estatusMuestra ?: 'N/A' }}
                            </span>
                        </td>


                        {{-- Tipo de entrega --}}
                        <td class="border-b px-4 py-2">
                            @php
                                $rawTipo = $pedido->estatus_entrega_muestra ?? null;
                                // Evitamos convertir Closures/objetos: solo aceptamos string
                                $tipo = is_string($rawTipo) ? strtoupper($rawTipo) : 'PENDIENTE';

                                $mapEntrega = [
                                    'PENDIENTE' => 'bg-yellow-400 text-black',
                                    'DIGITAL'   => 'bg-sky-600 text-white',
                                    'FISICA'    => 'bg-fuchsia-600 text-white',
                                ];
                                $claseEntrega = $mapEntrega[$tipo] ?? 'bg-gray-300 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
                            @endphp

                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $claseEntrega }}">
                                {{ $tipo }}
                            </span>
                        </td>

                        {{-- Evidencia (usa la relación evidenciaEntrega) --}}
                        <td class="border-b px-4 py-2">
                            @php
                                // gracias al with('evidenciaEntrega'), esto no pega N+1
                                $evidencia = $pedido->evidenciaEntrega; // objeto o null
                            @endphp

                            @if($evidencia)
                                <a href="{{ $evidencia->verimagen ?? \Illuminate\Support\Facades\Storage::disk('public')->url($evidencia->ruta_archivo) }}"
                                class="text-blue-600 hover:underline dark:text-blue-400" target="_blank" rel="noopener">
                                    {{ $evidencia->nombre_archivo }}
                                </a>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $evidencia->tipo_archivo ?? 'archivo' }} · {{ optional($evidencia->created_at)->format('Y-m-d H:i') }}
                                </div>
                            @else
                                <span class="text-gray-500 dark:text-gray-400">Sin evidencia</span>
                            @endif
                        </td>



                        <td class="border-b px-4 py-2">

                            <x-dropdown  >
                                {{-- <x-dropdown.item>
                                    <b 
                                     
                                    aria-label="Marcar como SOLICITADA"
                                    wire:click.stop="abrirModalEntregar({{ $pedido->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="marcarSolicitada">Marcar como Etregada</b>
                                </x-dropdown.item> --}}
                            
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

            <div class="relative w-full max-w-3xl rounded-2xl bg-white shadow-xl dark:bg-gray-800">
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-3 dark:border-gray-700">
                    <h3 class="text-lg font-semibold">
                        Estados del Pedido #{{ $pedidoEstadosId }}
                    </h3>
                    <button
                        class="rounded bg-gray-200 px-3 py-1 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
                        wire:click="cerrarModalEstados"
                    >
                        x
                    </button>
                </div>

                {{-- Body con scroll --}}
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <div class="max-h-80 overflow-y-auto rounded border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full text-sm">
                                <thead class="sticky top-0 bg-gray-100 dark:bg-gray-900/70">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300">ID</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Estado</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Usuario</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Comentario</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Fecha inicio</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Fecha fin</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Registrado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($estadosModal as $e)
                                        <tr class="border-t border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/40">
                                            <td class="px-3 py-2">{{ $e['id'] }}</td>
                                            <td class="px-3 py-2">{{ $e['estado'] }}</td>
                                            <td class="px-3 py-2">
                                                <span class="font-medium">{{ $e['usuario'] }}</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">#{{ $e['usuario_id'] }}</span>
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
                                            <td colspan="7" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">
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
                <div class="flex justify-end border-t border-gray-200 px-5 py-3 dark:border-gray-700">
                    <button
                        class="rounded-lg bg-gray-700 px-4 py-2 text-white hover:bg-gray-800 dark:bg-gray-600 dark:hover:bg-gray-500"
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

<div x-data class="container mx-auto p-6">
    <h2 class="text-2xl font-bold mb-4">Gestión de Muestras</h2>

    {{-- Sección de Filtros (minimizable) --}}
    @if($mostrarFiltros)
        <div x-data="{ abierto: @entangle('mostrarFiltros') }" class="mb-6">
            <template x-if="abierto">
                <div class="w-full bg-white border border-gray-200 shadow-md rounded-lg">
                    <div class="flex justify-between items-center p-4 border-b">
                        <h2 class="text-lg font-bold text-gray-700">Filtros</h2>
                        <div class="flex items-center gap-2">
                            {{-- <button 
                                wire:click="buscarPorFiltros"
                                class="bg-white border border-gray-300 text-gray-700 px-3 py-1 rounded hover:bg-gray-100 text-sm"
                            >
                                Filtrar
                            </button> --}}
                            <button 
                                @click="abierto = false" 
                                class="text-gray-500 hover:text-gray-700 text-xl leading-none"
                                title="Cerrar filtros"
                            >
                                ✕
                            </button>
                        </div>
                    </div>

                    {{-- GRID de filtros --}}
                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <input type="text" class="w-full border rounded px-3 py-2"
                            placeholder="Buscar ID (proyecto-id)"
                            wire:model.live.debounce.500ms="f_id" />

                        {{-- <input type="text" class="w-full border rounded px-3 py-2"
                            placeholder="Producto / Categoría"
                            wire:model.live.debounce.500ms="f_producto" /> --}}

                        {{-- <input type="text" class="w-full border rounded px-3 py-2"
                            placeholder="Cliente"
                            wire:model.live.debounce.500ms="f_cliente" /> --}}

                        <input type="text" class="w-full border rounded px-3 py-2"
                            placeholder="Archivo / Versión"
                            wire:model.live.debounce.500ms="f_archivo" />

                        {{-- <input type="text" class="w-full border rounded px-3 py-2"
                            placeholder="Piezas solicitadas (>=)"
                            wire:model.live.debounce.500ms="f_total_min" /> --}}

                        {{-- <input type="text" class="w-full border rounded px-3 py-2"
                            placeholder="Solicitante"
                            wire:model.live.debounce.500ms="f_usuario" /> --}}

                        {{-- <input type="text" class="w-full border rounded px-3 py-2"
                            placeholder="Motivos / Instrucciones"
                            wire:model.live.debounce.500ms="f_instrucciones" /> --}}

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

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full table-auto divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="border px-4 py-2">ID</th>
                    <th class="border px-4 py-2">Producto</th>
                    <th class="border px-4 py-2">Cliente</th>
                    <th class="border px-4 py-2">Archivo y versión</th>
                    <th class="border px-4 py-2">Piezas solicitadas</th>
                    <th class="border px-4 py-2">Quien solicita la Muestra</th>
                    <th class="border px-4 py-2">Motivos e instrucciones</th>
                    <th class="border px-4 py-2">Estatus</th>
                    <th class="border px-4 py-2">Tipo de entrega</th>
                    <th class="border px-4 py-2">Evidencia</th>


                    <th class="border px-4 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
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
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td
                            class="p-2 px-4 py-2 font-semibold min-w-[4rem]"
                            title="{{ $pedido->tooltip_clave }}"
                        >
                            {!! $pedido->clave_link !!}
                        </td>

                        

                        <td class="p-2">
                            <div class="font-medium">{{ $pedido->producto->nombre ?? 'Sin producto' }}</div>
                            <div class="text-xs text-gray-500">{{ $pedido->producto->categoria->nombre ?? 'Sin categoría' }}</div>
                        </td>

                        <td class="p-2 px-4 py-2 font-semibold min-w-[4rem]">
                            {{  $pedido->usuario->name ?? $pedido->cliente->razon_social ?? 'Cliente' }}
                        </td>

                        <td class="p-2 px-4 py-2 font-semibold min-w-[10rem]">
                            @if($pedido->archivo)
                                <a href="{{ Storage::url($pedido->archivo->ruta_archivo) }}"
                                   class="text-blue-600 underline"
                                   target="_blank" rel="noopener">
                                    {{ $pedido->archivo->nombre_archivo }}
                                </a>
                                <p class="text-xs text-gray-500">Versión: {{ $pedido->archivo->version }}</p>
                            @else
                                <span class="text-gray-500">Sin archivo</span>
                            @endif
                        </td>

                        <td class="p-2 px-4 py-2 font-semibold min-w-[4rem]">
                            {{ $pedido->total ?? 'N/A' }}
                        </td>

                        <td class="p-2 px-4 py-2 font-semibold min-w-[8rem]">
                            {{ $pedido->usuario->name ?? 'Sin usuario' }}
                        </td>

                        <td class="p-2 px-4 py-2 min-w-[12rem]">
                            <span class="block text-gray-700">
                                {{ $pedido->instrucciones_muestra ?? 'N/A' }}
                            </span>
                        </td>

                        <td class="p-2 px-4 py-2 font-semibold min-w-[8rem]">
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $clase }}">
                                {{ $estatusMuestra ?: 'N/A' }}
                            </span>
                        </td>



                        {{-- NUEVA: Tipo de entrega --}}
                        <td class="p-2 px-4 py-2 font-semibold min-w-[8rem]">
                            @php
                                $rawTipo = $pedido->estatus_entrega_muestra ?? null;
                                $tipo = is_string($rawTipo) ? strtoupper($rawTipo) : 'PENDIENTE';
                                $mapEntrega = [
                                    'PENDIENTE' => 'bg-yellow-400 text-black',
                                    'DIGITAL'   => 'bg-sky-600 text-white',
                                    'FISICA'    => 'bg-fuchsia-600 text-white',
                                ];
                                $claseEntrega = $mapEntrega[$tipo] ?? 'bg-gray-300 text-gray-800';
                            @endphp
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $claseEntrega }}">
                                {{ $tipo }}
                            </span>
                        </td>

                        {{-- NUEVA: Evidencia (última evidencia cargada por with([...])) --}}
                        <td class="p-2 px-4 py-2 min-w-[12rem]">
                            @php
                                // gracias al with('archivos' ... limit 1) esto no causa N+1
                                $evidencia = optional($pedido->archivos)->first();
                            @endphp

                            @if($evidencia)
                                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($evidencia->ruta_archivo) }}"
                                class="text-blue-600 underline"
                                target="_blank" rel="noopener">
                                {{ $evidencia->nombre_archivo }}
                                </a>


    
                            @else
                                <span class="text-gray-500">Sin evidencia</span>



                            @endif

                            @unless($esCliente)
                                <br>
                                <span> 
                                <a 
                                    href="{{ route('produccion.adminmuestras', ['tab' => $estatusMuestra ?: 'PENDIENTE']) }}"
                                    target="_blank"
                                      class="text-blue-600 underline" 
                                    rel="noopener">
                                    Ir a administración
                                </a>
                                </span>
                            @endunless
                        </td>

 

                        <td class="p-2 px-4 py-2 font-semibold min-w-[6rem]">
                            {{-- Acciones aquí --}}
                            {{-- <button
                                class="px-3 py-1 rounded bg-gray-700 text-white hover:bg-gray-800"
                                wire:click="abrirModal({{ $pedido->id }})">
                                Editar
                            </button> --}}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $pedidos->onEachSide(1)->links() }}
    </div>
</div>

{{-- Si agregas scripts aquí, encapsúlalos siempre --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
  // scripts opcionales
});
</script>

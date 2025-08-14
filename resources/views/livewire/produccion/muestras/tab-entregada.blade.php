{{-- resources/views/livewire/admin/muestras/tab-pendiente.blade.php --}}
<div x-data="{ selected: @entangle('selected') }" class="container mx-auto p-6">
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
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Solicitó</th>
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

                        <td class="border-b px-4 py-2">{{ $pedido->cliente->nombre ?? 'Cliente' }}</td>

                        <td class="border-b px-4 py-2">
                            @if($pedido->archivo)
                                <a href="{{ Storage::url($pedido->archivo->ruta_archivo) }}"
                                   class="text-blue-600 hover:underline"
                                   target="_blank">
                                   {{ $pedido->archivo->nombre_archivo }}
                                </a>
                                <div class="text-xs text-gray-500">Versión: {{ $pedido->archivo->version ?? '-' }}</div>
                            @else
                                <span class="text-gray-500">Sin archivo</span>
                            @endif
                        </td>

                        <td class="border-b px-4 py-2">{{ $pedido->total ?? 'N/A' }}</td>
                        
                        {{-- Celda --}}
                        <td class="border-b px-4 py-2">
                            @php $reg = $pedido->estados->first(); @endphp
                            @if($reg && $reg->usuario)
                                <span class="font-medium">{{ $reg->usuario->name }}</span>
                            @else
                                <span class="text-gray-500">—</span>
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
                                ])->get($estatusMuestra, 'bg-gray-300 text-gray-800');
                            @endphp
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $clase }}">
                                {{ $estatusMuestra ?: 'N/A' }}
                            </span>
                        </td>

                        <td class="border-b px-4 py-2">
                            {{-- <button
                                class="px-3 py-1 rounded bg-blue-500 text-white hover:bg-blue-600"
                                wire:click="marcarSolicitada([{{ $pedido->id }}])"
                            >
                                Marcar como Entregada
                            </button> --}}

                            <button
                                class="px-3 py-1 rounded bg-gray-700 text-white hover:bg-gray-800"
                                wire:click="abrirModalEstados({{ $pedido->id }})"
                            >
                                Ver estados
                            </button>
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
                        Cerrar
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

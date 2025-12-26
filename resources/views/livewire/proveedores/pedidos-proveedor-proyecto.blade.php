<div class="container mx-auto p-6">
    <h2 class="text-2xl font-bold mb-4">
        Pedidos asignados (Proveedor) — Proyecto: {{ $proyecto->nombre ?? ('#'.$proyecto->id) }}
    </h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-3">
            {{ session('message') }}
        </div>
    @endif

    {{-- Filtros --}}
    <div class="mb-4">
        <button wire:click="$toggle('mostrarFiltros')" class="text-sm text-blue-600 hover:underline">
            {{ $mostrarFiltros ? 'Ocultar filtros' : 'Mostrar filtros' }}
        </button>
    </div>

    @if($mostrarFiltros)
        <div class="w-full bg-white border border-gray-200 shadow-md rounded-lg mb-6">
            <div class="flex justify-between items-center p-4 border-b">
                <h2 class="text-lg font-bold text-gray-700">Filtros</h2>
                <button wire:click="buscarPorFiltros"
                    class="bg-white border border-gray-300 text-gray-700 px-3 py-1 rounded hover:bg-gray-100 text-sm">
                    Filtrar
                </button>
            </div>

            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div class="flex items-center space-x-2">
                    <input type="checkbox" wire:model.live="filters.inactivos"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                    <label class="text-sm text-gray-700">Mostrar solo inactivos</label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Estatus proveedor</label>
                    <select wire:model.live="filters.estatus_proveedor" class="w-full border rounded p-2">
                        <option value="">Todos</option>
                        @foreach($estatusProveedorOptions as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center space-x-2">
                    <input type="checkbox" wire:model.live="filters.solo_no_vistos"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                    <label class="text-sm text-gray-700">Solo no vistos</label>
                </div>
            </div>
        </div>
    @endif

    {{-- Tabla --}}
    <div class="overflow-x-auto bg-white rounded shadow min-h-64 pb-8">
        <table class="min-w-full table-auto divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="p-2 text-left">ID</th>
                    <th class="p-2 text-left">Proyecto</th>
                    <th class="p-2 text-left">Producto / Categoría</th>
                    <th class="p-2 text-left">Piezas</th>
                    <th class="p-2 text-left">Producción</th>
                    <th class="p-2 text-left">Embarque</th>
                    <th class="p-2 text-left">Entrega</th>
                    <th class="p-2 text-center">Estatus Proveedor</th>
                    <th class="p-2 text-center">Visto</th>
                    <th class="p-2 text-center">Acciones</th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($pedidos as $pedido)
                    <tr class="hover:bg-gray-50">
                        <td
                            class="p-2 px-4 py-2 font-semibold min-w-[4rem] cursor-help"
                            title="{{ $pedido->tooltip_clave ?? '' }}"
                        >
                            {{ $pedido->clave ?? $pedido->id }}
                        </td>

                        <td class="p-2">{{ $pedido->proyecto->nombre ?? '—' }}</td>

                        <td class="p-2">
                            <div class="font-medium">{{ $pedido->producto->nombre ?? 'Sin producto' }}</div>
                            <div class="text-xs text-gray-500">{{ $pedido->producto->categoria->nombre ?? 'Sin categoría' }}</div>
                        </td>

                        <td class="p-2">{{ $pedido->total }} piezas</td>

                        <td class="p-2">{{ $pedido->fecha_produccion?->format('Y-m-d') ?? '—' }}</td>
                        <td class="p-2">{{ $pedido->fecha_embarque?->format('Y-m-d') ?? '—' }}</td>
                        <td class="p-2">{{ $pedido->fecha_entrega?->format('Y-m-d') ?? '—' }}</td>

                        <td class="p-2 text-center">
                            @php
                                $e = strtoupper($pedido->estatus_proveedor ?? 'PENDIENTE');
                                $badge = match($e) {
                                    'PENDIENTE' => 'bg-gray-200 text-gray-800',
                                    'EN PROCESO' => 'bg-yellow-200 text-yellow-900',
                                    'LISTO' => 'bg-emerald-200 text-emerald-900',
                                    'BLOQUEADO' => 'bg-red-200 text-red-900',
                                    default => 'bg-gray-200 text-gray-800',
                                };
                            @endphp
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $badge }}">
                                {{ $e }}
                            </span>
                        </td>

                        <td class="p-2 text-center">
                            @if($pedido->proveedor_visto_at)
                                <span class="text-xs text-gray-600">
                                    {{ $pedido->proveedor_visto_at->format('Y-m-d H:i') }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400">No</span>
                            @endif
                        </td>

                        <td class="p-2 text-center">
                            <x-dropdown>
                                <x-dropdown.item>
                                    <b wire:click="abrirModalProveedor({{ $pedido->id }})">
                                        Actualizar estatus proveedor
                                    </b>
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

    {{-- Modal proveedor --}}
    @if($modalProveedor)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded shadow-lg w-full max-w-lg flex flex-col">
                <div class="flex items-center justify-between border-b border-gray-200 p-4">
                    <h5 class="text-xl font-bold">Actualizar estatus de proveedor</h5>
                    <button class="text-gray-500 hover:text-gray-700" wire:click="$set('modalProveedor', false)">&times;</button>
                </div>

                <div class="p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Estatus proveedor</label>
                        <select wire:model="estatus_proveedor" class="w-full border rounded p-2">
                            @foreach($estatusProveedorOptions as $opt)
                                <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
                        @error('estatus_proveedor') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nota (opcional)</label>
                        <textarea wire:model.defer="nota_proveedor" rows="4" class="w-full border rounded p-2"
                            placeholder="Ej: Falta material / esperando confirmación / listo para entregar..."></textarea>
                        @error('nota_proveedor') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="border-t border-gray-200 p-4 flex justify-end gap-2">
                    <button wire:click="$set('modalProveedor', false)"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-4 py-2 rounded">
                        Cancelar
                    </button>
                    <button wire:click="guardarProveedor"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

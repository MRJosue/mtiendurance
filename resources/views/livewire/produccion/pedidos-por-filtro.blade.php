<div
    x-data="{
        active: @entangle('activeFiltroId'),
        selected: [], // por si luego quieres hacer acciones masivas
    }"
    class="container mx-auto p-6"
>
    <!-- Encabezado y búsqueda -->
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center gap-3">
        <h2 class="text-xl font-bold text-gray-800">Pedidos por Filtro de Producción</h2>

        <div class="flex-1"></div>

        <div class="w-full sm:w-72">
            <input
                type="text"
                placeholder="Buscar por proyecto, producto o estado…"
                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                wire:model.live.debounce.400ms="search"
            />
        </div>
    </div>

    <!-- Tabs de filtros -->
    <div class="mb-4 w-full overflow-x-auto">
        <div class="inline-flex flex-wrap gap-2">
            @forelse($filtros as $f)
                <button
                    class="px-4 py-2 rounded-lg border transition"
                    :class="active === {{ $f->id }}
                        ? 'bg-blue-600 text-white border-blue-600'
                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                    @click="$wire.set('activeFiltroId', {{ $f->id }})"
                >
                    {{ $f->nombre }}
                </button>
            @empty
                <span class="text-gray-500">No hay filtros visibles.</span>
            @endforelse
        </div>
    </div>

    <!-- Resumen del filtro seleccionado -->
    @if($filtro)
        <div class="mb-4 grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Filtro seleccionado</h3>
                <div class="text-gray-800 font-medium">{{ $filtro->nombre }}</div>
                <div class="text-gray-500 text-sm">{{ $filtro->descripcion ?: '—' }}</div>

                <div class="mt-3 text-xs text-gray-500 flex flex-wrap gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                        ID: {{ $filtro->id }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                        {{ $productoIds->count() }} productos
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                        {{ $columnas->count() }} columnas
                    </span>
                </div>
            </div>

            <!-- Productos configurados (chips) -->
            <div class="bg-white rounded-lg shadow p-4 lg:col-span-2">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Productos en este filtro</h3>
                @php
                    $productos = $filtro->productos ?? collect();
                @endphp
                <div class="flex flex-wrap gap-2">
                    @forelse($productos as $p)
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 border text-gray-700 text-sm">
                            {{ $p->nombre }}
                        </span>
                    @empty
                        <span class="text-gray-500 text-sm">No hay productos asignados.</span>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <!-- Tabla de pedidos -->
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full border-collapse border border-gray-200 rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                        <input type="checkbox"
                               @change="
                                const checked = $event.target.checked;
                                const ids = @js(method_exists($pedidos,'pluck') ? $pedidos->pluck('id') : []);
                                selected = checked ? ids : [];
                               ">
                    </th>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">ID</th>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Proyecto</th>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Producto</th>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Total</th>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Estado</th>

                    <!-- Columnas dinámicas de características -->
                    @foreach($columnas as $col)
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                            {{ $col['label'] ?? $col['nombre'] }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @if(method_exists($pedidos,'count') && $pedidos->count() > 0)
                    @foreach($pedidos as $pedido)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2">
                                <input type="checkbox"
                                       :value="{{ $pedido->id }}"
                                       :checked="selected.includes({{ $pedido->id }})"
                                       @change="
                                        const id = {{ $pedido->id }};
                                        if ($event.target.checked) { if (!selected.includes(id)) selected.push(id) }
                                        else { selected = selected.filter(i => i !== id) }
                                       ">
                            </td>
                            <td class="px-3 py-2 text-sm text-gray-700">{{ $pedido->id }}</td>
                            <td class="px-3 py-2 text-sm text-gray-700">
                                {{ $pedido->proyecto->nombre ?? '—' }}
                            </td>
                            <td class="px-3 py-2 text-sm text-gray-700">
                                {{ $pedido->producto->nombre ?? '—' }}
                            </td>
                            <td class="px-3 py-2 text-sm text-gray-700">
                                {{ number_format((float)($pedido->total ?? 0), 2) }}
                            </td>
                            <td class="px-3 py-2 text-sm text-gray-700">
                                {{ $pedido->estado_produccion ?? $pedido->estado ?? $pedido->estatus ?? '—' }}
                            </td>

                            <!-- Celdas dinámicas por característica -->
                            @foreach($columnas as $col)
                                @php
                                    $vals = $valoresPorPedidoYCar[$pedido->id][$col['id']] ?? null;
                                    $fallback = $col['fallback'] ?? '—';
                                @endphp
                                <td class="px-3 py-2 text-sm text-gray-700 align-top">
                                    @if($vals && count($vals))
                                        @if(($col['multivalor_modo'] ?? 'inline') === 'badges')
                                            <div class="flex flex-wrap gap-1">
                                                @foreach(array_slice($vals, 0, (int)($col['max_items'] ?? 4)) as $v)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 border text-gray-700 text-xs">
                                                        {{ $v }}
                                                    </span>
                                                @endforeach
                                                @if(count($vals) > (int)($col['max_items'] ?? 4))
                                                    <span class="text-xs text-gray-500">+{{ count($vals) - (int)($col['max_items'] ?? 4) }}</span>
                                                @endif
                                            </div>
                                        @elseif(($col['multivalor_modo'] ?? 'inline') === 'count')
                                            <span class="text-xs text-gray-600">{{ count($vals) }} opción(es)</span>
                                        @else
                                            {{ collect($vals)->take((int)($col['max_items'] ?? 4))->implode(', ') }}
                                            @if(count($vals) > (int)($col['max_items'] ?? 4))
                                                <span class="text-xs text-gray-500"> +{{ count($vals) - (int)($col['max_items'] ?? 4) }}</span>
                                            @endif
                                        @endif
                                    @else
                                        <span class="text-gray-400">{{ $fallback }}</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="{{ 6 + $columnas->count() }}" class="px-4 py-6 text-center text-sm text-gray-500">
                            @if($filtro && $productoIds->isEmpty())
                                Este filtro no tiene productos asignados.
                            @else
                                No hay pedidos para mostrar.
                            @endif
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    @if(method_exists($pedidos,'links'))
        <div class="mt-4">
            {{ $pedidos->links() }}
        </div>
    @endif
</div>

{{-- Scripts encapsulados --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Si escuchas eventos globales (p.ej., desde el CRUD) puedes forzar refresh:
    window.addEventListener('filtro-produccion-actualizado', () => {
        // Livewire recarga vía $listeners -> $refresh, no necesitas más aquí.
        // Este bloque queda por si quieres meter toasts o algún indicador.
        console.log('Filtros de producción actualizados.');
    });
});
</script>

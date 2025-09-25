<div x-data="{ active: @entangle('activeFiltroId'), selected: [] }" class="container mx-auto p-6">
    {{-- Header con búsqueda --}}
    <div class="mb-4 flex flex-wrap gap-2 items-center">
        <h2 class="text-xl font-bold">Hoja: {{ $this->hoja->nombre }}</h2>
        <div class="flex-1"></div>

        {{-- Tamaño de página --}}
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-600">Mostrar</label>
            <select
                class="w-20 rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                wire:model.live="perPage"
                title="Número de registros por página"
            >
                @foreach($perPageOptions as $opt)
                    <option value="{{ $opt }}">{{ $opt }}</option>
                @endforeach
            </select>
        </div>

        {{-- Buscador --}}
        <input
            class="w-full sm:w-72 rounded-lg border-gray-300 focus:ring-blue-500"
            placeholder="Buscar…"
            wire:model.live.debounce.400ms="search"
        >
    </div>

    {{-- Tabs de filtros --}}
    <div class="mb-4 overflow-x-auto">
        <div class="inline-flex gap-2">
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
                <span class="text-gray-500">Esta hoja no tiene filtros asignados.</span>
            @endforelse
        </div>
    </div>

    {{-- Chips de configuración rápida --}}
    <div class="mb-3 text-xs text-gray-600 flex flex-wrap gap-2">
        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border">
            Estados: {{ !empty($chipEstados) ? implode(', ', $chipEstados) : 'Todos' }}
        </span>
        <span class="px-2 py-0.5 rounded-full bg-sky-50 text-sky-700 border">
            Estados Diseño: {{ !empty($chipEstadosDiseno) ? implode(', ', $chipEstadosDiseno) : 'Todos' }}
        </span>
        <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border">
            Rol: {{ $this->hoja->rol->name ?? '—' }}
        </span>
    </div>

    


    {{-- Tabla --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full border-collapse border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2">
                        <input
                            type="checkbox"
                            @change="
                                const ids = @js(method_exists($pedidos,'pluck') ? $pedidos->pluck('id') : []);
                                selected = $event.target.checked ? ids : [];
                            "
                        >
                    </th>

                    {{-- ID --}}
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                        <button
                            class="inline-flex items-center gap-1 hover:text-blue-600"
                            wire:click="sortBy('id')"
                            title="Ordenar por ID"
                        >
                            <span>ID</span>
                            <span class="text-xs">
                                @if($sortColumn === 'id')
                                    {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                                @else
                                    ⇵
                                @endif
                            </span>
                        </button>
                    </th>

                    {{-- Columnas base configurables (excluye ID) --}}
                    @foreach($baseCols as $bc)
                        @if(($bc['key'] ?? '') !== 'id' && ($bc['visible'] ?? true))
                            @php
                                // Mapea la key visible a la clave de ordenamiento que entiende el backend
                                $key = match($bc['key']) {
                                    'proyecto'       => 'proyecto',
                                    'producto'       => 'producto',
                                    'cliente'        => 'cliente',
                                    'estado'         => 'estado',
                                    'estado_disenio' => 'estado_disenio',
                                    'total'          => 'total',
                                    'fecha_produccion','fecha_embarque','fecha_entrega' => $bc['key'],
                                    default => null,
                                };
                            @endphp
                            <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                                <div class="inline-flex items-center gap-1">
                                    <span>{{ $bc['label'] ?? ucfirst($bc['key']) }}</span>

                                    @if($key)
                                        <button
                                            class="inline-flex items-center text-xs hover:text-blue-600"
                                            wire:click="sortBy('{{ $key }}')"
                                            title="Ordenar por {{ $bc['label'] ?? $bc['key'] }}"
                                        >
                                            @if($sortColumn === $key)
                                                {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                                            @else
                                                ⇵
                                            @endif
                                        </button>
                                    @endif
                                </div>
                            </th>
                        @endif
                    @endforeach

                    {{-- Columnas dinámicas del filtro --}}
                    @foreach($columnasFiltro as $col)
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                            {{ $col['label'] ?? $col['nombre'] }}
                        </th>
                    @endforeach

                     <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Acciones</th>
                </tr>

                {{-- Filtros por columna --}}
                <tr class="border-t border-gray-200">
                    {{-- Checkbox maestro (sin filtro) --}}
                    <th class="px-3 py-2"></th>

                    {{-- Filtro ID (exacto) --}}
                    <th class="px-3 py-2">
                        <input
                            type="number"
                            class="w-28 sm:w-32 rounded-lg border-gray-300 focus:ring-blue-500"
                            placeholder="ID"
                            wire:model.live.debounce.400ms="filters.id"
                        >
                    </th>

                    {{-- Filtros para columnas base (según key) --}}
                    @foreach($baseCols as $bc)
                        @if(($bc['key'] ?? '') !== 'id' && ($bc['visible'] ?? true))
                            <th class="px-3 py-2">
                                @switch($bc['key'])
                                    @case('proyecto')
                                        <input class="w-36 sm:w-44 rounded-lg border-gray-300 focus:ring-blue-500"
                                            placeholder="Proyecto…" wire:model.live.debounce.400ms="filters.proyecto">
                                    @break
                                    
                                    @case('cliente')
                                        <input class="w-36 sm:w-44 rounded-lg border-gray-300 focus:ring-blue-500"
                                            placeholder="Cliente…"
                                            wire:model.live.debounce.400ms="filters.cliente">
                                    @break

                                    @case('producto')
                                        <input class="w-36 sm:w-44 rounded-lg border-gray-300 focus:ring-blue-500"
                                            placeholder="Producto…" wire:model.live.debounce.400ms="filters.producto">
                                    @break

                                    @case('estado')
                                        <select class="w-36 sm:w-44 rounded-lg border-gray-300 focus:ring-blue-500"
                                                wire:model.live.debounce.400ms="filters.estado_id">
                                            <option value="">Todos</option>
                                            @foreach($this->estados as $e)
                                                <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                                            @endforeach
                                        </select>
                                    @break

                                    @case('estado_disenio')
                                        <select class="w-36 sm:w-44 rounded-lg border-gray-300 focus:ring-blue-500"
                                                wire:model.live.debounce.400ms="filters.estado_disenio">
                                            <option value="">Todos</option>
                                            @foreach($this->estadosDiseno as $s)
                                                <option value="{{ $s }}">{{ $s }}</option>
                                            @endforeach
                                        </select>
                                    @break

                                    @case('total')
                                        <input class="w-28 sm:w-32 rounded-lg border-gray-300 focus:ring-blue-500"
                                            placeholder="Total…" wire:model.live.debounce.400ms="filters.total">
                                    @break


                                     @case('fecha_produccion')
                                        <div x-data="{ open:false }" class="relative">
                                            <button
                                                @click="open = !open"
                                                class="w-40 rounded-lg border border-gray-300 bg-white px-2 py-1 text-sm text-gray-700 hover:bg-gray-50"
                                            >
                                                Producción…
                                            </button>

                                            <div
                                                x-cloak
                                                x-show="open"
                                                @click.away="open = false"
                                                x-transition
                                                class="absolute z-50 mt-1 w-56 rounded-lg border bg-white shadow p-2"
                                            >
                                                <div class="flex flex-col gap-2" @click.stop @mousedown.stop>
                                                    <label class="text-xs text-gray-600">Desde</label>
                                                    <input type="date"
                                                        class="w-48 rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                        wire:model.live.debounce.400ms="filters.fecha_produccion_from">

                                                    <label class="text-xs text-gray-600">Hasta</label>
                                                    <input type="date"
                                                        class="w-48 rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                        wire:model.live.debounce.400ms="filters.fecha_produccion_to">

                                                    <div class="pt-1 flex gap-2">
                                                        <button type="button"
                                                                class="px-2 py-1 text-xs rounded-lg border bg-white hover:bg-gray-50"
                                                                @click="$wire.set('filters.fecha_produccion_from', null); $wire.set('filters.fecha_produccion_to', null)">
                                                            Limpiar
                                                        </button>
                                                        <button type="button"
                                                                class="px-2 py-1 text-xs rounded-lg border bg-white hover:bg-gray-50"
                                                                @click="open=false">
                                                            Aplicar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @break

                                    @case('fecha_embarque')
                                        <div x-data="{ open:false }" class="relative">
                                            <button
                                                @click="open = !open"
                                                class="w-40 rounded-lg border border-gray-300 bg-white px-2 py-1 text-sm text-gray-700 hover:bg-gray-50"
                                            >
                                                Embarque…
                                            </button>

                                            <div x-cloak x-show="open" @click.away="open=false" x-transition
                                                class="absolute z-50 mt-1 w-56 rounded-lg border bg-white shadow p-2">
                                                <div class="flex flex-col gap-2" @click.stop @mousedown.stop>
                                                    <label class="text-xs text-gray-600">Desde</label>
                                                    <input type="date"
                                                        class="w-48 rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                        wire:model.live.debounce.400ms="filters.fecha_embarque_from">

                                                    <label class="text-xs text-gray-600">Hasta</label>
                                                    <input type="date"
                                                        class="w-48 rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                        wire:model.live.debounce.400ms="filters.fecha_embarque_to">

                                                    <div class="pt-1 flex gap-2">
                                                        <button type="button"
                                                                class="px-2 py-1 text-xs rounded-lg border bg-white hover:bg-gray-50"
                                                                @click="$wire.set('filters.fecha_embarque_from', null); $wire.set('filters.fecha_embarque_to', null)">
                                                            Limpiar
                                                        </button>
                                                        <button type="button"
                                                                class="px-2 py-1 text-xs rounded-lg border bg-white hover:bg-gray-50"
                                                                @click="open=false">
                                                            Aplicar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @break

                                    @case('fecha_entrega')
                                        <div x-data="{ open:false }" class="relative">
                                            <button
                                                @click="open = !open"
                                                class="w-40 rounded-lg border border-gray-300 bg-white px-2 py-1 text-sm text-gray-700 hover:bg-gray-50"
                                            >
                                                Entrega…
                                            </button>

                                            <div x-cloak x-show="open" @click.away="open=false" x-transition
                                                class="absolute z-50 mt-1 w-56 rounded-lg border bg-white shadow p-2">
                                                <div class="flex flex-col gap-2" @click.stop @mousedown.stop>
                                                    <label class="text-xs text-gray-600">Desde</label>
                                                    <input type="date"
                                                        class="w-48 rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                        wire:model.live.debounce.400ms="filters.fecha_entrega_from">

                                                    <label class="text-xs text-gray-600">Hasta</label>
                                                    <input type="date"
                                                        class="w-48 rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                        wire:model.live.debounce.400ms="filters.fecha_entrega_to">

                                                    <div class="pt-1 flex gap-2">
                                                        <button type="button"
                                                                class="px-2 py-1 text-xs rounded-lg border bg-white hover:bg-gray-50"
                                                                @click="$wire.set('filters.fecha_entrega_from', null); $wire.set('filters.fecha_entrega_to', null)">
                                                            Limpiar
                                                        </button>
                                                        <button type="button"
                                                                class="px-2 py-1 text-xs rounded-lg border bg-white hover:bg-gray-50"
                                                                @click="open=false">
                                                            Aplicar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @break
   




                                    @default
                                        <input class="w-32 sm:w-40 rounded-lg border-gray-300 focus:ring-blue-500"
                                            placeholder="Filtrar…" wire:model.live.debounce.400ms="filters.{{ $bc['key'] }}">
                                 @endswitch
                            </th>
                        @endif
                    @endforeach

                    {{-- Filtros para columnas dinámicas (características) --}}
                    @foreach($columnasFiltro as $col)
                        <th class="px-3 py-2">
                            <input
                                class="w-32 sm:w-40 rounded-lg border-gray-300 focus:ring-blue-500"
                                placeholder="Buscar {{ $col['label'] ?? $col['nombre'] }}…"
                                wire:model.live.debounce.400ms="filtersCar.{{ $col['id'] }}"
                            >
                        </th>
                    @endforeach
                    {{-- separaccion de acciones --}}
                   <th></th>
                </tr>
            </thead>

            <tbody>
                @if(method_exists($pedidos,'count') && $pedidos->count())
                    @foreach($pedidos as $pedido)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2">
                                <input
                                    type="checkbox"
                                    :value="{{ $pedido->id }}"
                                    :checked="selected.includes({{ $pedido->id }})"
                                    @change="
                                        const id={{ $pedido->id }};
                                        if ($event.target.checked) {
                                            if (!selected.includes(id)) selected.push(id)
                                        } else {
                                            selected = selected.filter(i => i !== id)
                                        }
                                    "
                                >
                            </td>

                            {{-- ID --}}
                            <td
                                class="p-2 px-4 py-2 font-semibold min-w-[4rem]"
                                title="{{ $pedido->tooltip_clave }}"
                            >
                                {!! $pedido->clave_link !!}
                            </td>


                            {{-- Columnas base (sin ID) --}}
                            @foreach($baseCols as $bc)
                                @if(($bc['key'] ?? '') !== 'id' && ($bc['visible'] ?? true))
                                    <td class="px-3 py-2 text-sm text-gray-700">
                                        @switch($bc['key'])
                                            @case('proyecto') {{ $pedido->proyecto->nombre ?? '—' }} @break
                                            @case('producto') {{ $pedido->producto->nombre ?? '—' }} @break
                                            @case('cliente'){{ $pedido->usuario->name ?? '—' }}@break
                                            @case('estado')   {{ $pedido->estadoPedido->nombre ?? '—' }} @break
                                            @case('estado_disenio'){{ $pedido->proyecto->estado ?? '—' }}@break
                                            @case('total')    {{ number_format((float)($pedido->total ?? 0), 2) }} @break

                                            {{-- NUEVOS: fechas (si tienes casts a date en el modelo Pedido, usa ->format) --}}
                                            @case('fecha_produccion') {{ $pedido->fecha_produccion?->format('d/m/Y') ?? '—' }} @break
                                            @case('fecha_embarque')   {{ $pedido->fecha_embarque?->format('d/m/Y') ?? '—' }} @break
                                            @case('fecha_entrega')    {{ $pedido->fecha_entrega?->format('d/m/Y') ?? '—' }} @break

                                            @default — 
                                        @endswitch
                                    </td>
                                @endif
                            @endforeach

                            {{-- Columnas dinámicas (características del filtro) --}}
                            @foreach($columnasFiltro as $col)
                                @php
                                    $vals = $valoresPorPedidoYCar[$pedido->id][$col['id']] ?? null;
                                    $fallback = $col['fallback'] ?? '—';
                                @endphp
                                <td class="px-3 py-2 text-sm text-gray-700">
                                    @if($vals && count($vals))
                                        @if(($col['multivalor_modo'] ?? 'inline') === 'badges')
                                            <div class="flex flex-wrap gap-1">
                                                @foreach(array_slice($vals, 0, (int)($col['max_items'] ?? 4)) as $v)
                                                    <span class="px-2 py-0.5 rounded-full bg-gray-100 border text-gray-700 text-xs">{{ $v }}</span>
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
                                                <span class="text-xs text-gray-500">+{{ count($vals) - (int)($col['max_items'] ?? 4) }}</span>
                                            @endif
                                        @endif
                                    @else
                                        <span class="text-gray-400">{{ $fallback }}</span>
                                    @endif
                                </td>
                            @endforeach
                                {{-- Aqui irian las acciones --}}
                            <td class="px-3 py-2 text-sm text-gray-700">
                                <x-dropdown>
                                    <x-dropdown.item>
                                        <b>Help Center</b>
                                    </x-dropdown.item>
                                
                                    <x-dropdown.item separator>
                                        <b>Live Chat</b>
                                    </x-dropdown.item>
                                
                                    <x-dropdown.item separator>
                                        <b>Logout</b>
                                    </x-dropdown.item>
                                </x-dropdown>
                            </td>

                           
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td
                            colspan="{{ 2 + $baseCols->filter(fn($c)=>($c['key']??'')!=='id' && ($c['visible']??true))->count() + $columnasFiltro->count() }}"
                            class="px-4 py-6 text-center text-sm text-gray-500"
                        >
                            No hay pedidos para mostrar.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

    </div>

 

    @if(method_exists($pedidos,'links'))
        <div class="mt-4">{{ $pedidos->links() }}</div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Aquí puedes agregar toasts o listeners si necesitas
});
</script>

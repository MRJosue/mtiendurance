<div 
    x-data="{
        abierto: JSON.parse(localStorage.getItem('dashboard_pedidos_abierto') ?? 'true'),
        toggle() {
            this.abierto = !this.abierto;
            localStorage.setItem('dashboard_pedidos_abierto', JSON.stringify(this.abierto));
        }
    }"
    class="p-2 sm:p-3 h-full min-h-0 flex flex-col"
>
    <h2 
        @click="toggle()"
        class="text-xl font-bold mb-4 border-b border-gray-300 pb-2 cursor-pointer hover:text-blue-600 transition"
    >
        Muestras de Proyecto
    <span class="text-sm text-gray-500 ml-2" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
    </h2>

    <!-- Contenido colapsable -->
    <div x-show="abierto" x-transition>

                
                        @if($mostrarFiltros)
                            <div 
                                x-data="{ abierto: @entangle('mostrarFiltros') }" 
                                class="mb-6"
                            >

                            
<template x-if="abierto">
    <div class="w-full bg-white border border-gray-200 shadow-md rounded-lg">
        {{-- Header --}}
        <div class="p-4 border-b">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center justify-between sm:justify-start gap-3">
                    <h2 class="text-lg font-bold text-gray-700">Filtros</h2>

                    {{-- Cerrar (compacto) --}}
                    <button
                        type="button"
                        @click="abierto = false"
                        class="sm:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100"
                        title="Cerrar"
                    >
                        ✕
                    </button>
                </div>

                {{-- Acciones --}}
                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <button
                        type="button"
                        wire:click="buscarPorFiltros"
                        class="w-full sm:w-auto bg-white border border-gray-300 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-100 text-sm"
                    >
                        Filtrar
                    </button>

                    <button
                        type="button"
                        wire:click="clearFilters"
                        class="w-full sm:w-auto bg-white border border-gray-300 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-100 text-sm"
                    >
                        Limpiar
                    </button>

                    <button
                        type="button"
                        wire:click="exportExcel"
                        wire:loading.attr="disabled"
                        wire:target="exportExcel"
                        class="w-full sm:w-auto bg-emerald-600 text-white px-3 py-2 rounded-lg hover:bg-emerald-700 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        @disabled(! $this->hasFilters)
                        title="{{ $this->hasFilters ? 'Exportar Excel' : 'Aplica al menos 1 filtro para exportar' }}"
                    >
                        <span wire:loading.remove wire:target="exportExcel">Exportar Excel</span>
                        <span wire:loading wire:target="exportExcel">Exportando...</span>
                    </button>

                    {{-- Cerrar (desktop) --}}
                    <button
                        type="button"
                        @click="abierto = false"
                        class="hidden sm:inline-flex items-center justify-center px-3 py-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 text-sm"
                        title="Cerrar"
                    >
                        Cerrar ✕
                    </button>
                </div>
            </div>

            {{-- Hint --}}
            <p class="mt-2 text-xs text-gray-500">
                La exportación solo se habilita cuando hay al menos un filtro aplicado.
            </p>
        </div>

        {{-- Body --}}
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                {{-- No aprobados --}}
                <div class="rounded-lg border border-gray-200 p-3">
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            id="no-aprobados"
                            wire:model.defer="mostrarSoloNoAprobados"
                            class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        />
                        <span class="text-sm text-gray-700">
                            Mostrar pedidos de diseños <span class="font-semibold">No aprobados</span>
                        </span>
                    </label>
                </div>

                {{-- PerPage --}}
                <div class="rounded-lg border border-gray-200 p-3">
                    <label for="perPage" class="block text-sm text-gray-700 font-medium mb-1">
                        Registros por página
                    </label>
                    <select
                        id="perPage"
                        wire:model.live="perPage"
                        class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm"
                    >
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="30">30</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                {{-- Inactivos --}}
                <div class="rounded-lg border border-gray-200 p-3">
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            id="solo-inactivos"
                            wire:model.live="filters.inactivos"
                            class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        />
                        <span class="text-sm text-gray-700">
                            Mostrar solo pedidos <span class="font-semibold">inactivos</span>
                        </span>
                    </label>
                </div>
            </div>
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
            



            @php
                $arrow = function(string $field) use ($sortField, $sortDir) {
                    if ($sortField !== $field) return '⇅';
                    return $sortDir === 'asc' ? '▲' : '▼';
                };
            @endphp

            @php
    $tabsMuestraStyles = [
        'TODOS'         => 'bg-slate-100 text-slate-800',
        'PENDIENTE'     => 'bg-yellow-100 text-yellow-800',
        'SOLICITADA'    => 'bg-blue-100 text-blue-800',
        'MUESTRA LISTA' => 'bg-emerald-100 text-emerald-800',
        'ENTREGADA'     => 'bg-green-100 text-green-800',
        'CANCELADA'     => 'bg-gray-100 text-gray-800',
    ];
@endphp

<!-- PESTAÑAS POR ESTADO DE LA MUESTRA -->
<div class="mb-4">
    <div class="overflow-x-auto">
        <ul class="flex flex-nowrap sm:flex-wrap border-b border-gray-200 gap-1 min-w-max sm:min-w-0">
            @foreach ($this->tabsEstadoVisibles as $tab)
                <li>
                    <button
                        type="button"
                        wire:click="setEstadoTab('{{ $tab }}')"
                        @class([
                            'px-3 py-2 rounded-t-lg text-sm whitespace-nowrap transition',
                            'border-b-2 font-semibold bg-white border-blue-500 text-blue-600' => $activeEstadoTab === $tab,
                            'text-gray-600 hover:text-blue-500 border-b-2 border-transparent' => $activeEstadoTab !== $tab,
                        ])
                    >
                        {{ $tab }}

                        @if($tab !== 'TODOS')
                            <span class="ml-1 text-xs">
                                ({{ $estadoTabsCounts[$tab] ?? 0 }})
                            </span>
                        @else
                            <span class="ml-1 text-xs">
                                ({{ $estadoTabsCounts['TODOS'] ?? 0 }})
                            </span>
                        @endif
                    </button>
                </li>
            @endforeach
        </ul>
    </div>
</div>

        <div class="overflow-x-auto bg-white rounded-lg shadow min-h-64 pb-8">
        <table class="w-full table-auto border-collapse border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    {{-- Checkbox maestro por página --}}
                    <th class="px-3 py-2 w-10">
                        <input
                            type="checkbox"
                            :checked="idsPagina.length && idsPagina.map(Number).every(id => selected.map(Number).includes(id))"
                            @change="
                                const pagina = idsPagina.map(Number);
                                if ($event.target.checked) {
                                    selected = Array.from(new Set([...selected.map(Number), ...pagina]));
                                } else {
                                    selected = selected.map(Number).filter(i => !pagina.includes(i));
                                }
                            "
                        />
                    </th>

                    {{-- ID pedido/proyecto --}}
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                        <button
                            class="inline-flex items-center gap-1 hover:text-blue-600"
                            wire:click="sortBy('id')"
                            title="Ordenar por ID de pedido"
                        >
                            <span>ID</span>
                            <span class="text-xs">
                                @if($sortField === 'id')
                                    {{ $sortDir === 'asc' ? '▲' : '▼' }}
                                @else
                                    ⇵
                                @endif
                            </span>
                        </button>
                    </th>

                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                        <button class="inline-flex items-center gap-1 hover:text-blue-600" wire:click="sortBy('proyecto_nombre')">
                            <span>Nombre del proyecto</span>
                            <span class="text-xs">
                                @if($sortField === 'proyecto_nombre')
                                    {{ $sortDir === 'asc' ? '▲' : '▼' }}
                                @else
                                    ⇵
                                @endif
                            </span>
                        </button>
                    </th>

                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                        <button class="inline-flex items-center gap-1 hover:text-blue-600" wire:click="sortBy('cliente_nombre')">
                            <span>Cliente</span>
                            <span class="text-xs">
                                @if($sortField === 'cliente_nombre')
                                    {{ $sortDir === 'asc' ? '▲' : '▼' }}
                                @else
                                    ⇵
                                @endif
                            </span>
                        </button>
                    </th>

                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Producto / Categoría</th>

                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                        <button class="inline-flex items-center gap-1 hover:text-blue-600" wire:click="sortBy('total')">
                            <span>Total</span>
                            <span class="text-xs">
                                @if($sortField === 'total')
                                    {{ $sortDir === 'asc' ? '▲' : '▼' }}
                                @else
                                    ⇵
                                @endif
                            </span>
                        </button>
                    </th>

                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                        Estado de la Muestra
                    </th>


                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                        <button class="inline-flex items-center gap-1 hover:text-blue-600" wire:click="sortBy('fecha_produccion')">
                            <span>Producción</span>
                            <span class="text-xs">
                                @if($sortField === 'fecha_produccion')
                                    {{ $sortDir === 'asc' ? '▲' : '▼' }}
                                @else
                                    ⇵
                                @endif
                            </span>
                        </button>
                    </th>

                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                        <button class="inline-flex items-center gap-1 hover:text-blue-600" wire:click="sortBy('fecha_entrega')">
                            <span>Entrega</span>
                            <span class="text-xs">
                                @if($sortField === 'fecha_entrega')
                                    {{ $sortDir === 'asc' ? '▲' : '▼' }}
                                @else
                                    ⇵
                                @endif
                            </span>
                        </button>
                    </th>

                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Acciones</th>
                </tr>

                {{-- Filtros por columna (dropdown compacto tipo HojaViewer) --}}
                <tr class="border-t border-gray-200">
                    <th class="px-3 py-2"></th>

                    {{-- Filtro ID --}}
                    <th class="px-3 py-2">
                        <div x-data="{ open:false }" class="relative inline-flex items-center">
                            <button @click="open = !open" class="px-2 py-1 rounded hover:bg-gray-200 text-sm" title="Filtrar ID">⋮</button>
                            <div x-cloak x-show="open" @click.away="open=false" x-transition class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3">
                                <label class="block text-xs text-gray-600 mb-1">ID de Pedido o Proyecto</label>
                                <input
                                    type="text"
                                    class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                    placeholder="Ej. 1001 o 1001,1002"
                                    wire:model.live.debounce.400ms="filters.id"
                                />
                                <div class="grid grid-cols-2 gap-2 mt-3">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Desde</label>
                                        <input type="date" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm" wire:model.live="filters.fecha_desde">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Hasta</label>
                                        <input type="date" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm" wire:model.live="filters.fecha_hasta">
                                    </div>
                                </div>
                                <div class="mt-2 flex justify-end gap-2">
                                    <button type="button" class="px-2 py-1 text-xs rounded border" wire:click="clearFilters">Limpiar</button>
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </th>

                    {{-- Filtro Proyecto --}}
                    <th class="px-3 py-2">
                        <div x-data="{ open:false }" class="relative inline-flex items-center">
                            <button @click="open = !open" class="px-2 py-1 rounded hover:bg-gray-200 text-sm" title="Filtrar Proyecto">⋮</button>
                            <div x-cloak x-show="open" @click.away="open=false" x-transition class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3">
                                <label class="block text-xs text-gray-600 mb-1">Proyecto</label>
                                <input class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm" placeholder="Proyecto…" wire:model.live.debounce.400ms="filters.proyecto">
                                <div class="mt-2 flex justify-end gap-2">
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="$wire.set('filters.proyecto','')">Limpiar</button>
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </th>

                    {{-- Filtro Cliente --}}
                    <th class="px-3 py-2">
                        <div x-data="{ open:false }" class="relative inline-flex items-center">
                            <button @click="open = !open" class="px-2 py-1 rounded hover:bg-gray-200 text-sm" title="Filtrar Cliente">⋮</button>
                            <div x-cloak x-show="open" @click.away="open=false" x-transition class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3">
                                <label class="block text-xs text-gray-600 mb-1">Nombre o correo</label>
                                <input class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm" placeholder="Cliente…" wire:model.live.debounce.400ms="filters.cliente">
                                <div class="mt-2 flex justify-end gap-2">
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="$wire.set('filters.cliente','')">Limpiar</button>
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </th>

                    {{-- sin filtro específico para Producto/Categoría --}}
                    <th></th>

                    {{-- Filtro Total --}}
                    <th class="px-3 py-2">
                        <div x-data="{ open:false }" class="relative inline-flex items-center">
                            <button @click="open = !open" class="px-2 py-1 rounded hover:bg-gray-200 text-sm" title="Filtrar Total">⋮</button>
                            <div x-cloak x-show="open" @click.away="open=false" x-transition class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3">
                                <label class="block text-xs text-gray-600 mb-1">Total</label>
                                <input class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm" placeholder="Total…" wire:model.live.debounce.400ms="filters.total">
                                <div class="mt-2 flex justify-end gap-2">
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="$wire.set('filters.total','')">Limpiar</button>
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </th>

                    {{-- Filtro Estado Muestra --}}
                <th class="px-3 py-2">
                    <div x-data="{ open:false }" class="relative inline-flex items-center">
                        <button @click="open = !open" class="px-2 py-1 rounded hover:bg-gray-200 text-sm" title="Filtrar Estado de la Muestra">⋮</button>
                        <div x-cloak x-show="open" @click.away="open=false" x-transition class="absolute z-50 mt-1 w-56 rounded-lg border bg-white shadow p-3">
                            <label class="block text-xs text-gray-600 mb-1">Estado de la Muestra</label>
                            <select class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm" wire:model.live="filters.estado_pedido">
                                <option value="">— Cualquiera —</option>
                                <option value="PENDIENTE">PENDIENTE</option>
                                <option value="SOLICITADA">SOLICITADA</option>
                                <option value="MUESTRA LISTA">MUESTRA LISTA</option>
                                <option value="ENTREGADA">ENTREGADA</option>
                                <option value="CANCELADA">CANCELADA</option>
                            </select>
                            <div class="mt-2 flex justify-end gap-2">
                                <button type="button" class="px-2 py-1 text-xs rounded border" @click="$wire.set('filters.estado_pedido','')">Limpiar</button>
                                <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </th>


                    {{-- Fechas --}}
                    <th class="px-3 py-2">
                        <div x-data="{ open:false }" class="relative inline-flex items-center">
                            <button @click="open = !open" class="px-2 py-1 rounded hover:bg-gray-200 text-sm" title="Filtrar Producción">⋮</button>
                            <div x-cloak x-show="open" @click.away="open=false" x-transition class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3">
                                <label class="block text-xs text-gray-600 mb-1">Producción</label>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-xs text-gray-600">Desde</span>
                                        <input type="date" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm" wire:model.live.debounce.400ms="filters.fecha_produccion_from">
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-600">Hasta</span>
                                        <input type="date" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm" wire:model.live.debounce.400ms="filters.fecha_produccion_to">
                                    </div>
                                </div>
                                <div class="mt-2 flex justify-end gap-2">
                                    <button type="button" class="px-2 py-1 text-xs rounded border"
                                            @click="$wire.set('filters.fecha_produccion_from', null); $wire.set('filters.fecha_produccion_to', null)">Limpiar</button>
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </th>

                    <th class="px-3 py-2">
                        <div x-data="{ open:false }" class="relative inline-flex items-center">
                            <button @click="open = !open" class="px-2 py-1 rounded hover:bg-gray-200 text-sm" title="Filtrar Entrega">⋮</button>
                            <div x-cloak x-show="open" @click.away="open=false" x-transition class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3">
                                <label class="block text-xs text-gray-600 mb-1">Entrega</label>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-xs text-gray-600">Desde</span>
                                        <input type="date" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm" wire:model.live.debounce.400ms="filters.fecha_entrega_from">
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-600">Hasta</span>
                                        <input type="date" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm" wire:model.live.debounce.400ms="filters.fecha_entrega_to">
                                    </div>
                                </div>
                                <div class="mt-2 flex justify-end gap-2">
                                    <button type="button" class="px-2 py-1 text-xs rounded border"
                                            @click="$wire.set('filters.fecha_entrega_from', null); $wire.set('filters.fecha_entrega_to', null)">Limpiar</button>
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </th>

                    <th></th>
                </tr>
            </thead>

            <tbody class="text-sm">
                @forelse($pedidos as $pedido)
                    <tr class="hover:bg-gray-50">
                        {{-- Checkbox fila --}}
                        <td class="px-3 py-2">
                            <input
                                type="checkbox"
                                :value="{{ $pedido->id }}"
                                :checked="selected.map(Number).includes(Number({{ $pedido->id }}))"
                                @change="
                                    const id = Number({{ $pedido->id }});
                                    if ($event.target.checked) {
                                        if (!selected.map(Number).includes(id)) selected.push(id)
                                    } else {
                                        selected = selected.map(Number).filter(i => i !== id)
                                    }
                                "
                                wire:key="chk-{{ $pedido->id }}"
                            />
                        </td>

                        {{-- ID/Clave --}}
                        <td class="p-2 px-4 py-2 font-semibold min-w-[4rem]" title="{{ $pedido->tooltip_clave }}">
                            {!! $pedido->clave_link !!}
                        </td>

                        {{-- Proyecto --}}
                        <td class="px-3 py-2 font-semibold">{{ $pedido->proyecto->nombre }}</td>

                        {{-- Cliente --}}
                        <td class="px-3 py-2 font-semibold">
                            <span
                                title="{{ $pedido->usuario?->tooltip_sucursal_empresa }}"
                                class="inline-block cursor-help"
                            >
                                {{ $pedido->usuario->name ?? 'Sin cliente' }}
                            </span>
                        </td>

                        {{-- Producto / Categoría --}}
                        <td class="px-3 py-2">
                            <div class="font-medium">{{ $pedido->producto->nombre ?? 'Sin producto' }}</div>
                            <div class="text-xs text-gray-500">{{ $pedido->producto->categoria->nombre ?? 'Sin categoría' }}</div>
                        </td>

                        {{-- Total piezas --}}
                        <td class="px-3 py-2">
                            @if((int)($pedido->flag_tallas ?? 0) === 1)
                                <button
                                    type="button"
                                    wire:click="abrirModalTallas({{ $pedido->id }})"
                                    class="text-blue-600 hover:underline font-semibold"
                                    title="Ver distribución de tallas"
                                >
                                    {{ number_format((float)($pedido->total ?? 0), 0) }} piezas
                                </button>
                            @else
                                {{ number_format((float)($pedido->total ?? 0), 0) }} piezas
                            @endif
                        </td>

                        {{-- Estado Diseño (chips fijos estilo HojaViewer) --}}
                        <td class="px-3 py-2">
                            @php
                                $mapMuestra = [
                                    'PENDIENTE'     => 'bg-yellow-400 text-black',
                                    'SOLICITADA'    => 'bg-blue-500 text-white',
                                    'MUESTRA LISTA' => 'bg-emerald-600 text-white',
                                    'ENTREGADA'     => 'bg-green-600 text-white',
                                    'CANCELADA'     => 'bg-gray-500 text-white',
                                ];

                                $estadoMuestra = strtoupper($pedido->estatus_muestra ?? 'PENDIENTE');
                                $claseMuestra = $mapMuestra[$estadoMuestra] ?? 'bg-gray-200 text-gray-700';
                            @endphp

                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap min-w-[11rem] justify-center {{ $claseMuestra }}">
                                {{ $estadoMuestra }}
                            </span>
                        </td>

                        {{-- Producción --}}
                        <td class="px-3 py-2">{{ $pedido->fecha_produccion?->format('Y-m-d') ?? 'No definida' }}</td>

                        {{-- Entrega --}}
                        <td class="px-3 py-2">{{ $pedido->fecha_entrega?->format('Y-m-d') ?? 'No definida' }}</td>

                        {{-- Acciones --}}
                        <td class="px-3 py-2">
                            <x-dropdown>
                                <x-dropdown.item
                                    :href="route('proyecto.show', $pedido->proyecto_id)"
                                    label="Ir a Diseño"
                                />

                                {{-- ✅ FIX: ahora el click es sobre TODO el item --}}
                                <x-dropdown.item
                                    separator
                                    wire:click="abrirModalVerInfo({{ $pedido->proyecto_id }})"
                                    class="w-full cursor-pointer"
                                >
                                    <span class="block w-full font-semibold">Ver información</span>
                                </x-dropdown.item>

                                @if(($acciones['aprobar_pedido'] ?? false))
                                    <x-dropdown.item
                                        @click="if (confirm('¿Aprobar este pedido?')) $wire.aprobarPedido({{ $pedido->id }})"
                                        label="Aprobar pedido"
                                    />
                                @endif

                                @if(($acciones['programar_pedido'] ?? false))
                                    <x-dropdown.item
                                        @click="if (confirm('¿Programar este pedido?')) $wire.programarPedido({{ $pedido->id }})"
                                        label="Programar pedido"
                                    />
                                @endif

                                @if(($acciones['abrir_chat'] ?? false))
                                    <x-dropdown.item
                                        @click="$wire.dispatch('abrir-chat', { proyecto_id: {{ $pedido->proyecto_id }} })"
                                        label="Abrir chat"
                                    />
                                @endif

                                @if(($acciones['crear_tarea'] ?? false))
                                    <x-dropdown.item
                                        @click="$wire.dispatch('abrir-modal-tarea', { pedido_id: {{ $pedido->id }} })"
                                        label="Crear tarea"
                                    />
                                @endif

                                @if(($acciones['editar_pedido'] ?? false))
                                    <x-dropdown.item
                                        @click="$wire.dispatch('editar-pedido', { id: {{ $pedido->id }} })"
                                        label="Editar pedido"
                                    />
                                @endif

                                @if(($acciones['duplicar_pedido'] ?? false))
                                    <x-dropdown.item
                                        @click="$wire.dispatch('duplicar-pedido', { id: {{ $pedido->id }} })"
                                        label="Duplicar pedido"
                                    />
                                @endif

                                @if(($acciones['eliminar_pedido'] ?? false))
                                    <x-dropdown.item
                                        separator
                                        @click="if (confirm('¿Archivar este pedido?')) $wire.dispatch('eliminar-pedido', { id: {{ $pedido->id }} })"
                                        label="Archivar pedido"
                                    />
                                @endif

                                @if(($acciones['entregar_pedido'] ?? false))
                                    <x-dropdown.item
                                        @click="$wire.dispatch('abrir-modal-entrega', { id: {{ $pedido->id }} })"
                                        label="Entregar pedido"
                                    />
                                @endif

                                @if(($acciones['cancelar_pedido'] ?? false))
                                    <x-dropdown.item
                                        @click="if (confirm('¿Cancelar este pedido?')) $wire.dispatch('cancelar-pedido', { id: {{ $pedido->id }} })"
                                        label="Cancelar pedido"
                                    />
                                @endif

                                @if(($acciones['subir_archivos'] ?? false))
                                    <x-dropdown.item
                                        @click="$wire.dispatch('subir-archivos', { id: {{ $pedido->id }} })"
                                        label="Subir archivos"
                                    />
                                @endif

                                @if(($acciones['exportar_excel'] ?? false))
                                    <x-dropdown.item
                                        separator
                                        @click="$wire.dispatch('exportar-excel-pedido', { id: {{ $pedido->id }} })"
                                        label="Exportar a Excel"
                                    />
                                @endif
                            </x-dropdown>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-4 py-6 text-center text-sm text-gray-500">
                            No hay pedidos para mostrar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        <div class="mt-4">
            {{ $pedidos->links() }}
        </div>
        
    </div>


    @if($modalVerInfo && $infoProyecto)
    <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white p-6 rounded shadow-lg w-full max-w-2xl relative overflow-y-auto max-h-[90vh]">
            <h2 class="text-xl font-bold mb-4">Detalles del Proyecto</h2>
            <button 
                wire:click="$set('modalVerInfo', false)" 
                class="absolute top-3 right-4 text-gray-500 hover:text-red-600 text-2xl leading-none"
                title="Cerrar"
            >&times;</button>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-lg"><span class="font-semibold">Cliente:</span> {{ $infoProyecto->user->name ?? 'Sin usuario' }}</p>
                </div>
                <div>
                    <p class="text-lg"><span class="font-semibold">Proyecto:</span> {{ $infoProyecto->nombre }} <span class="text-sm font-bold">ID:{{ $infoProyecto->id }}</span></p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-lg"><span class="font-semibold">Descripción:</span> {{ $infoProyecto->descripcion }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-lg font-semibold">Categoría:</p>
                    <p>{{ $infoProyecto->categoria_sel['nombre'] ?? $infoProyecto->categoria->nombre ?? 'Sin categoría' }}</p>
                </div>
                <div>
                    <p class="text-lg font-semibold">Producto:</p>
                    <p>{{ $infoProyecto->producto_sel['id'] ?? $infoProyecto->producto->id ?? '' }} {{ $infoProyecto->producto_sel['nombre'] ?? $infoProyecto->producto->nombre ?? 'Sin producto' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-2">
                @foreach($infoProyecto->caracteristicas_sel ?? [] as $caracteristica)
                    <div class="p-4 border rounded-lg shadow bg-gray-50">
                        <h3 class="text-lg font-semibold">{{ $caracteristica['nombre'] }}</h3>
                        <ul class="mt-2 list-disc list-inside">
                            @foreach($caracteristica['opciones'] ?? [] as $opcion)
                                <li><span class="font-medium">{{ $opcion['nombre'] }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

@if($modalTallas)
    <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-2xl relative overflow-y-auto max-h-[90vh]">
            <h2 class="text-xl font-bold mb-4">
                Distribución de tallas
                @if($tallasPedidoId)
                    <span class="text-sm text-gray-500 font-semibold">Pedido #{{ $tallasPedidoId }}</span>
                @endif
            </h2>

            <button
                type="button"
                wire:click="cerrarModalTallas"
                class="absolute top-3 right-4 text-gray-500 hover:text-red-600 text-2xl leading-none"
                title="Cerrar"
            >&times;</button>

            @if(!empty($tallasDistribucionPorGrupo))

                <div class="space-y-4">
                    @foreach($tallasDistribucionPorGrupo as $grupo)
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <div class="px-4 py-2 bg-gray-100 flex items-center justify-between">
                                <div class="font-semibold text-gray-700">
                                    {{ $grupo['grupo'] }}
                                </div>
                                <div class="text-sm font-bold text-gray-700">
                                    Subtotal: {{ number_format((int)$grupo['subtotal']) }}
                                </div>
                            </div>

                            <div class="p-4">
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                                    @foreach($grupo['items'] as $it)
                                        <div class="border rounded-lg p-2 flex items-center justify-between">
                                            <span class="font-semibold text-gray-700">{{ $it['talla'] }}</span>
                                            <span class="text-gray-900">{{ number_format((int)$it['cantidad']) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex justify-end">
                    <div class="px-4 py-2 rounded-lg bg-gray-50 border font-bold text-gray-800">
                        Total: {{ number_format((int)$tallasTotal) }} piezas
                    </div>
                </div>

            @else
                <p class="text-sm text-gray-500">No hay tallas registradas para este pedido.</p>
            @endif
        </div>
    </div>
@endif

</div>

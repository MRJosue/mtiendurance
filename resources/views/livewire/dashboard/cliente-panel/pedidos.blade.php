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
        {{ __('orders.title') }}
    <span class="text-sm text-gray-500 ml-2" x-text="abierto ? @js(__('orders.hide')) : @js(__('orders.show'))"></span>
    </h2>

    <!-- Contenido colapsable -->
    <div x-show="abierto" x-transition>
        @php
            $designStatusLabel = function (?string $status) {
                return match ($status) {
                    'PENDIENTE' => __('orders.status_pending'),
                    'ASIGNADO' => __('orders.status_assigned'),
                    'EN PROCESO' => __('orders.status_in_progress'),
                    'REVISION' => __('orders.status_review'),
                    'DISEÑO APROBADO' => __('orders.status_design_approved'),
                    'DISEÑO RECHAZADO' => __('orders.status_design_rejected'),
                    'RECHAZADO' => __('orders.status_rejected'),
                    'CANCELADO' => __('orders.status_cancelled'),
                    null, '' => __('orders.no_status'),
                    default => $status,
                };
            };

            $orderStatusLabel = function (?string $status) {
                return match ($status) {
                    'TODOS' => __('orders.tab_all'),
                    'PENDIENTE' => __('orders.status_pending'),
                    'APROBADO' => __('orders.status_approved'),
                    'POR PROGRAMAR' => __('orders.status_to_schedule'),
                    'PROGRAMADO' => __('orders.status_scheduled'),
                    'ENTREGADO' => __('orders.status_delivered'),
                    'RECHAZADO' => __('orders.status_rejected'),
                    'CANCELADO' => __('orders.status_cancelled'),
                    'ARCHIVADO' => __('orders.status_archived'),
                    null, '' => __('orders.no_status'),
                    default => $status,
                };
            };
        @endphp

                
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
                    <h2 class="text-lg font-bold text-gray-700">{{ __('orders.filters') }}</h2>

                    {{-- Cerrar (compacto) --}}
                    <button
                        type="button"
                        @click="abierto = false"
                        class="sm:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100"
                        title="{{ __('orders.close') }}"
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
                        {{ __('orders.filter') }}
                    </button>

                    <button
                        type="button"
                        wire:click="clearFilters"
                        class="w-full sm:w-auto bg-white border border-gray-300 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-100 text-sm"
                    >
                        {{ __('orders.clear') }}
                    </button>

                    <button
                        type="button"
                        wire:click="exportExcel"
                        wire:loading.attr="disabled"
                        wire:target="exportExcel"
                        class="w-full sm:w-auto bg-emerald-600 text-white px-3 py-2 rounded-lg hover:bg-emerald-700 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        @disabled(! $this->hasFilters)
                        title="{{ $this->hasFilters ? __('orders.export_excel') : __('orders.apply_at_least_one_filter_to_export') }}"
                    >
                        <span wire:loading.remove wire:target="exportExcel">{{ __('orders.export_excel') }}</span>
                        <span wire:loading wire:target="exportExcel">{{ __('orders.exporting') }}</span>
                    </button>

                    {{-- Cerrar (desktop) --}}
                    <button
                        type="button"
                        @click="abierto = false"
                        class="hidden sm:inline-flex items-center justify-center px-3 py-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 text-sm"
                        title="{{ __('orders.close') }}"
                    >
                        {{ __('orders.close') }} ✕
                    </button>
                </div>
            </div>

            {{-- Hint --}}
            <p class="mt-2 text-xs text-gray-500">
                {{ __('orders.export_enabled_only_with_filters') }}
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
                            {{ __('orders.show_orders_with_designs') }} <span class="font-semibold">{{ __('orders.not_approved') }}</span>
                        </span>
                    </label>
                </div>

                {{-- PerPage --}}
                <div class="rounded-lg border border-gray-200 p-3">
                    <label for="perPage" class="block text-sm text-gray-700 font-medium mb-1">
                        {{ __('orders.records_per_page') }}
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
                            {{ __('orders.show_only_orders') }} <span class="font-semibold">{{ __('orders.inactive') }}</span>
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
                                            {{ __('orders.show_filters') }}
                                        </button>
                                    </div>
                                </template>
                            </div>
                        @else
                            <div class="mb-4">
                                <button wire:click="$set('mostrarFiltros', true)" class="text-sm text-blue-600 hover:underline">
                                    {{ __('orders.show_filters') }}
                                </button>
                            </div>
                        @endif
            

                <!-- PESTAÑAS POR ESTADO DEL PEDIDO -->
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
                                        {{ $orderStatusLabel($tab) }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="mb-3 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        {{ __('orders.showing') }}
                        <span class="font-semibold text-blue-600">{{ $orderStatusLabel($activeEstadoTab) }}</span>
                    </div>
                </div>

            @php
                $arrow = function(string $field) use ($sortField, $sortDir) {
                    if ($sortField !== $field) return '⇅';
                    return $sortDir === 'asc' ? '▲' : '▼';
                };
            @endphp

    

        <div
            x-data='{
                selected: [],
                idsPagina: @json($pedidos->pluck("id")->map(fn ($id) => (int) $id)->values()->all()),
            }'
            class="overflow-x-auto bg-white rounded-lg shadow min-h-64 pb-8"
        >
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
                            title="{{ __('orders.sort_by_order_id') }}"
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
                            <span>{{ __('orders.project_name') }}</span>
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
                            <span>{{ __('orders.client') }}</span>
                            <span class="text-xs">
                                @if($sortField === 'cliente_nombre')
                                    {{ $sortDir === 'asc' ? '▲' : '▼' }}
                                @else
                                    ⇵
                                @endif
                            </span>
                        </button>
                    </th>

                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">{{ __('orders.product_category') }}</th>

                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                        <button class="inline-flex items-center gap-1 hover:text-blue-600" wire:click="sortBy('total')">
                            <span>{{ __('orders.total') }}</span>
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
                        <button class="inline-flex items-center gap-1 hover:text-blue-600" wire:click="sortBy('estado_diseno')">
                            <span>{{ __('orders.design_status') }}</span>
                            <span class="text-xs">
                                @if($sortField === 'estado_diseno')
                                    {{ $sortDir === 'asc' ? '▲' : '▼' }}
                                @else
                                    ⇵
                                @endif
                            </span>
                        </button>
                    </th>

                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                        <button class="inline-flex items-center gap-1 hover:text-blue-600" wire:click="sortBy('estado')">
                            <span>{{ __('orders.order_status') }}</span>
                            <span class="text-xs">
                                @if($sortField === 'estado')
                                    {{ $sortDir === 'asc' ? '▲' : '▼' }}
                                @else
                                    ⇵
                                @endif
                            </span>
                        </button>
                    </th>

                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                        <button class="inline-flex items-center gap-1 hover:text-blue-600" wire:click="sortBy('fecha_produccion')">
                            <span>{{ __('orders.production') }}</span>
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
                            <span>{{ __('orders.delivery') }}</span>
                            <span class="text-xs">
                                @if($sortField === 'fecha_entrega')
                                    {{ $sortDir === 'asc' ? '▲' : '▼' }}
                                @else
                                    ⇵
                                @endif
                            </span>
                        </button>
                    </th>

                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">{{ __('orders.actions') }}</th>
                </tr>

                {{-- Filtros por columna (dropdown compacto tipo HojaViewer) --}}
                <tr class="border-t border-gray-200">
                    <th class="px-3 py-2"></th>

                    {{-- Filtro ID --}}
                    <th class="px-3 py-2">
                        <div x-data="{ open:false }" class="relative inline-flex items-center">
                            <button @click="open = !open" class="px-2 py-1 rounded hover:bg-gray-200 text-sm" title="{{ __('orders.filter_id') }}">⋮</button>
                            <div x-cloak x-show="open" @click.away="open=false" x-transition class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3">
                                <label class="block text-xs text-gray-600 mb-1">{{ __('orders.order_or_project_id') }}</label>
                                <input
                                    type="text"
                                    class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                    placeholder="{{ __('orders.id_example') }}"
                                    wire:model.live.debounce.400ms="filters.id"
                                />
                                <div class="grid grid-cols-2 gap-2 mt-3">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">{{ __('orders.from') }}</label>
                                        <input type="date" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm" wire:model.live="filters.fecha_desde">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">{{ __('orders.to') }}</label>
                                        <input type="date" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm" wire:model.live="filters.fecha_hasta">
                                    </div>
                                </div>
                                <div class="mt-2 flex justify-end gap-2">
                                    <button type="button" class="px-2 py-1 text-xs rounded border" wire:click="clearFilters">{{ __('orders.clear') }}</button>
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">{{ __('orders.close') }}</button>
                                </div>
                            </div>
                        </div>
                    </th>

                    {{-- Filtro Proyecto --}}
                    <th class="px-3 py-2">
                        <div x-data="{ open:false }" class="relative inline-flex items-center">
                            <button @click="open = !open" class="px-2 py-1 rounded hover:bg-gray-200 text-sm" title="{{ __('orders.filter_project') }}">⋮</button>
                            <div x-cloak x-show="open" @click.away="open=false" x-transition class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3">
                                <label class="block text-xs text-gray-600 mb-1">{{ __('orders.project') }}</label>
                                <input class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm" placeholder="{{ __('orders.project_placeholder') }}" wire:model.live.debounce.400ms="filters.proyecto">
                                <div class="mt-2 flex justify-end gap-2">
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="$wire.set('filters.proyecto','')">{{ __('orders.clear') }}</button>
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">{{ __('orders.close') }}</button>
                                </div>
                            </div>
                        </div>
                    </th>

                    {{-- Filtro Cliente --}}
                    <th class="px-3 py-2">
                        <div x-data="{ open:false }" class="relative inline-flex items-center">
                            <button @click="open = !open" class="px-2 py-1 rounded hover:bg-gray-200 text-sm" title="{{ __('orders.filter_client') }}">⋮</button>
                            <div x-cloak x-show="open" @click.away="open=false" x-transition class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3">
                                <label class="block text-xs text-gray-600 mb-1">{{ __('orders.name_or_email') }}</label>
                                <input class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm" placeholder="{{ __('orders.client_placeholder') }}" wire:model.live.debounce.400ms="filters.cliente">
                                <div class="mt-2 flex justify-end gap-2">
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="$wire.set('filters.cliente','')">{{ __('orders.clear') }}</button>
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">{{ __('orders.close') }}</button>
                                </div>
                            </div>
                        </div>
                    </th>

                    {{-- sin filtro específico para Producto/Categoría --}}
                    <th></th>

                    {{-- Filtro Total --}}
                    <th class="px-3 py-2">
                        <div x-data="{ open:false }" class="relative inline-flex items-center">
                            <button @click="open = !open" class="px-2 py-1 rounded hover:bg-gray-200 text-sm" title="{{ __('orders.filter_total') }}">⋮</button>
                            <div x-cloak x-show="open" @click.away="open=false" x-transition class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3">
                                <label class="block text-xs text-gray-600 mb-1">{{ __('orders.total') }}</label>
                                <input class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm" placeholder="{{ __('orders.total_placeholder') }}" wire:model.live.debounce.400ms="filters.total">
                                <div class="mt-2 flex justify-end gap-2">
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="$wire.set('filters.total','')">{{ __('orders.clear') }}</button>
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">{{ __('orders.close') }}</button>
                                </div>
                            </div>
                        </div>
                    </th>

                    {{-- Filtro Estado Diseño --}}
                    <th class="px-3 py-2">
                        <div x-data="{ open:false }" class="relative inline-flex items-center">
                            <button @click="open = !open" class="px-2 py-1 rounded hover:bg-gray-200 text-sm" title="{{ __('orders.filter_design_status') }}">⋮</button>
                            <div x-cloak x-show="open" @click.away="open=false" x-transition class="absolute z-50 mt-1 w-56 rounded-lg border bg-white shadow p-3">
                                <label class="block text-xs text-gray-600 mb-1">{{ __('orders.design_status') }}</label>
                                <select class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm" wire:model.live="filters.estado_diseno">
                                    <option value="">{{ __('orders.any') }}</option>
                                    <option value="PENDIENTE">{{ __('orders.status_pending') }}</option>
                                    <option value="ASIGNADO">{{ __('orders.status_assigned') }}</option>
                                    <option value="EN PROCESO">{{ __('orders.status_in_progress') }}</option>
                                    <option value="REVISION">{{ __('orders.status_review') }}</option>
                                    <option value="DISEÑO APROBADO">{{ __('orders.status_design_approved') }}</option>
                                    <option value="DISEÑO RECHAZADO">{{ __('orders.status_design_rejected') }}</option>
                                    <option value="CANCELADO">{{ __('orders.status_cancelled') }}</option>
                                </select>
                                <div class="mt-2 flex justify-end gap-2">
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="$wire.set('filters.estado_diseno','')">{{ __('orders.clear') }}</button>
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">{{ __('orders.close') }}</button>
                                </div>
                            </div>
                        </div>
                    </th>

                    {{-- Filtro Estado Pedido --}}
                    <th class="px-3 py-2">
                        <div x-data="{ open:false }" class="relative inline-flex items-center">
                            <button @click="open = !open" class="px-2 py-1 rounded hover:bg-gray-200 text-sm" title="{{ __('orders.filter_order_status') }}">⋮</button>
                            <div x-cloak x-show="open" @click.away="open=false" x-transition class="absolute z-50 mt-1 w-56 rounded-lg border bg-white shadow p-3">
                                <label class="block text-xs text-gray-600 mb-1">{{ __('orders.order_status') }}</label>
                                <select class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm" wire:model.live="filters.estado_pedido">
                                    <option value="">{{ __('orders.any') }}</option>
                                    <option value="PENDIENTE">{{ __('orders.status_pending') }}</option>
                                    <option value="APROBADO">{{ __('orders.status_approved') }}</option>
                                    <option value="POR PROGRAMAR">{{ __('orders.status_to_schedule') }}</option>
                                    <option value="PROGRAMADO">{{ __('orders.status_scheduled') }}</option>
                                    <option value="ENTREGADO">{{ __('orders.status_delivered') }}</option>
                                    <option value="RECHAZADO">{{ __('orders.status_rejected') }}</option>
                                    <option value="CANCELADO">{{ __('orders.status_cancelled') }}</option>
                                    <option value="ARCHIVADO">{{ __('orders.status_archived') }}</option>
                                </select>
                                <div class="mt-2 flex justify-end gap-2">
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="$wire.set('filters.estado_pedido','')">{{ __('orders.clear') }}</button>
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">{{ __('orders.close') }}</button>
                                </div>
                            </div>
                        </div>
                    </th>

                    {{-- Fechas --}}
                    <th class="px-3 py-2">
                        <div x-data="{ open:false }" class="relative inline-flex items-center">
                            <button @click="open = !open" class="px-2 py-1 rounded hover:bg-gray-200 text-sm" title="{{ __('orders.filter_production') }}">⋮</button>
                            <div x-cloak x-show="open" @click.away="open=false" x-transition class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3">
                                <label class="block text-xs text-gray-600 mb-1">{{ __('orders.production') }}</label>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-xs text-gray-600">{{ __('orders.from') }}</span>
                                        <input type="date" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm" wire:model.live.debounce.400ms="filters.fecha_produccion_from">
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-600">{{ __('orders.to') }}</span>
                                        <input type="date" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm" wire:model.live.debounce.400ms="filters.fecha_produccion_to">
                                    </div>
                                </div>
                                <div class="mt-2 flex justify-end gap-2">
                                    <button type="button" class="px-2 py-1 text-xs rounded border"
                                            @click="$wire.set('filters.fecha_produccion_from', null); $wire.set('filters.fecha_produccion_to', null)">{{ __('orders.clear') }}</button>
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">{{ __('orders.close') }}</button>
                                </div>
                            </div>
                        </div>
                    </th>

                    <th class="px-3 py-2">
                        <div x-data="{ open:false }" class="relative inline-flex items-center">
                            <button @click="open = !open" class="px-2 py-1 rounded hover:bg-gray-200 text-sm" title="{{ __('orders.filter_delivery') }}">⋮</button>
                            <div x-cloak x-show="open" @click.away="open=false" x-transition class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3">
                                <label class="block text-xs text-gray-600 mb-1">{{ __('orders.delivery') }}</label>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-xs text-gray-600">{{ __('orders.from') }}</span>
                                        <input type="date" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm" wire:model.live.debounce.400ms="filters.fecha_entrega_from">
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-600">{{ __('orders.to') }}</span>
                                        <input type="date" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm" wire:model.live.debounce.400ms="filters.fecha_entrega_to">
                                    </div>
                                </div>
                                <div class="mt-2 flex justify-end gap-2">
                                    <button type="button" class="px-2 py-1 text-xs rounded border"
                                            @click="$wire.set('filters.fecha_entrega_from', null); $wire.set('filters.fecha_entrega_to', null)">{{ __('orders.clear') }}</button>
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">{{ __('orders.close') }}</button>
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
                                {{ $pedido->usuario->name ?? __('orders.no_client') }}
                            </span>
                        </td>

                        {{-- Producto / Categoría --}}
                        <td class="px-3 py-2">
                            <div class="font-medium">{{ $pedido->producto->nombre ?? __('orders.no_product') }}</div>
                            <div class="text-xs text-gray-500">{{ $pedido->producto->categoria->nombre ?? __('orders.no_category') }}</div>
                        </td>

                        {{-- Total piezas --}}
                        <td class="px-3 py-2">
                            @if((int)($pedido->flag_tallas ?? 0) === 1)
                                <button
                                    type="button"
                                    wire:click="abrirModalTallas({{ $pedido->id }})"
                                    class="text-blue-600 hover:underline font-semibold"
                                    title="{{ __('orders.view_size_distribution') }}"
                                >
                                    {{ number_format((float)($pedido->total ?? 0), 0) }} {{ __('orders.pieces') }}
                                </button>
                            @else
                                {{ number_format((float)($pedido->total ?? 0), 0) }} {{ __('orders.pieces') }}
                            @endif
                        </td>

                        {{-- Estado Diseño (chips fijos estilo HojaViewer) --}}
                        <td class="px-3 py-2">
                            @php
                                $map = [
                                    'PENDIENTE'        => 'bg-yellow-400 text-black',
                                    'ASIGNADO'         => 'bg-blue-500 text-white',
                                    'EN PROCESO'       => 'bg-orange-500 text-white',
                                    'REVISION'         => 'bg-purple-600 text-white',
                                    'DISEÑO APROBADO'  => 'bg-emerald-600 text-white',
                                    'DISEÑO RECHAZADO' => 'bg-red-600 text-white',
                                    'CANCELADO'        => 'bg-gray-500 text-white',
                                ];
                                $estadoDiseno = strtoupper($pedido->proyecto->estado);
                                $clase = $map[$estadoDiseno] ?? 'bg-gray-200 text-gray-700';
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap min-w-[11rem] justify-center {{ $clase }}">
                                {{ $designStatusLabel($estadoDiseno) }}
                            </span>
                        </td>

                        {{-- Estado Pedido --}}
                        <td class="px-3 py-2">
                            @php
                                $color = match(strtoupper($pedido->estado)){
                                    'APROBADO'      => 'bg-emerald-600 text-white',
                                    'ENTREGADO'     => 'bg-blue-600 text-white',
                                    'RECHAZADO'     => 'bg-red-600 text-white',
                                    'ARCHIVADO'     => 'bg-gray-600 text-white',
                                    'PROGRAMADO'    => 'bg-indigo-600 text-white',
                                    'POR PROGRAMAR' => 'bg-amber-500 text-black',
                                    default         => 'bg-yellow-400 text-black',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap min-w-[9rem] justify-center {{ $color }}">
                                {{ $orderStatusLabel(strtoupper($pedido->estado)) }}
                            </span>
                        </td>

                        {{-- Producción --}}
                        <td class="px-3 py-2">{{ $pedido->fecha_produccion?->format('Y-m-d') ?? __('orders.not_defined') }}</td>

                        {{-- Entrega --}}
                        <td class="px-3 py-2">{{ $pedido->fecha_entrega?->format('Y-m-d') ?? __('orders.not_defined') }}</td>

                        {{-- Acciones --}}
                        <td class="px-3 py-2">
                            <x-dropdown>
                                <x-dropdown.item
                                    :href="route('proyecto.show', $pedido->proyecto_id)"
                                    label="{{ __('orders.go_to_design') }}"
                                />

                                {{-- ✅ FIX: ahora el click es sobre TODO el item --}}
                                <x-dropdown.item
                                    separator
                                    wire:click="abrirModalVerInfo({{ $pedido->proyecto_id }})"
                                    class="w-full cursor-pointer"
                                >
                                    <span class="block w-full font-semibold">{{ __('orders.view_information') }}</span>
                                </x-dropdown.item>

                                @if(($acciones['aprobar_pedido'] ?? false))
                                    <x-dropdown.item
                                        @click="if (confirm(@js(__('orders.confirm_approve_order')))) $wire.aprobarPedido({{ $pedido->id }})"
                                        label="{{ __('orders.approve_order') }}"
                                    />
                                @endif

                                @if(($acciones['programar_pedido'] ?? false))
                                    <x-dropdown.item
                                        @click="if (confirm(@js(__('orders.confirm_schedule_order')))) $wire.programarPedido({{ $pedido->id }})"
                                        label="{{ __('orders.schedule_order') }}"
                                    />
                                @endif

                                @if(($acciones['abrir_chat'] ?? false))
                                    <x-dropdown.item
                                        @click="$wire.dispatch('abrir-chat', { proyecto_id: {{ $pedido->proyecto_id }} })"
                                        label="{{ __('orders.open_chat') }}"
                                    />
                                @endif

                                @if(($acciones['crear_tarea'] ?? false))
                                    <x-dropdown.item
                                        @click="$wire.dispatch('abrir-modal-tarea', { pedido_id: {{ $pedido->id }} })"
                                        label="{{ __('orders.create_task') }}"
                                    />
                                @endif

                                @if(($acciones['editar_pedido'] ?? false))
                                    <x-dropdown.item
                                        @click="$wire.dispatch('editar-pedido', { id: {{ $pedido->id }} })"
                                        label="{{ __('orders.edit_order') }}"
                                    />
                                @endif

                                @if(($acciones['duplicar_pedido'] ?? false))
                                    <x-dropdown.item
                                        @click="$wire.dispatch('duplicar-pedido', { id: {{ $pedido->id }} })"
                                        label="{{ __('orders.duplicate_order') }}"
                                    />
                                @endif

                                @if(($acciones['eliminar_pedido'] ?? false))
                                    <x-dropdown.item
                                        separator
                                        @click="if (confirm(@js(__('orders.confirm_archive_order')))) $wire.dispatch('eliminar-pedido', { id: {{ $pedido->id }} })"
                                        label="{{ __('orders.archive_order') }}"
                                    />
                                @endif

                                @if(($acciones['entregar_pedido'] ?? false))
                                    <x-dropdown.item
                                        @click="$wire.dispatch('abrir-modal-entrega', { id: {{ $pedido->id }} })"
                                        label="{{ __('orders.deliver_order') }}"
                                    />
                                @endif

                                @if(($acciones['cancelar_pedido'] ?? false))
                                    <x-dropdown.item
                                        @click="if (confirm(@js(__('orders.confirm_cancel_order')))) $wire.dispatch('cancelar-pedido', { id: {{ $pedido->id }} })"
                                        label="{{ __('orders.cancel_order') }}"
                                    />
                                @endif

                                @if(($acciones['subir_archivos'] ?? false))
                                    <x-dropdown.item
                                        @click="$wire.dispatch('subir-archivos', { id: {{ $pedido->id }} })"
                                        label="{{ __('orders.upload_files') }}"
                                    />
                                @endif

                                @if(($acciones['exportar_excel'] ?? false))
                                    <x-dropdown.item
                                        separator
                                        @click="$wire.dispatch('exportar-excel-pedido', { id: {{ $pedido->id }} })"
                                        label="{{ __('orders.export_to_excel') }}"
                                    />
                                @endif
                            </x-dropdown>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-4 py-6 text-center text-sm text-gray-500">
                            {{ __('orders.no_orders_to_show') }}
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
            <h2 class="text-xl font-bold mb-4">{{ __('orders.project_details') }}</h2>
            <button 
                wire:click="$set('modalVerInfo', false)" 
                class="absolute top-3 right-4 text-gray-500 hover:text-red-600 text-2xl leading-none"
                title="{{ __('orders.close') }}"
            >&times;</button>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-lg"><span class="font-semibold">{{ __('orders.client_label') }}</span> {{ $infoProyecto->user->name ?? __('orders.no_user') }}</p>
                </div>
                <div>
                    <p class="text-lg"><span class="font-semibold">{{ __('orders.project_label') }}</span> {{ $infoProyecto->nombre }} <span class="text-sm font-bold">{{ __('orders.id_compact') }}{{ $infoProyecto->id }}</span></p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-lg"><span class="font-semibold">{{ __('orders.description_label') }}</span> {{ $infoProyecto->descripcion }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-lg font-semibold">{{ __('orders.category') }}:</p>
                    <p>{{ $infoProyecto->categoria_sel['nombre'] ?? $infoProyecto->categoria->nombre ?? __('orders.no_category') }}</p>
                </div>
                <div>
                    <p class="text-lg font-semibold">{{ __('orders.product') }}:</p>
                    <p>{{ $infoProyecto->producto_sel['id'] ?? $infoProyecto->producto->id ?? '' }} {{ $infoProyecto->producto_sel['nombre'] ?? $infoProyecto->producto->nombre ?? __('orders.no_product') }}</p>
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
                {{ __('orders.size_distribution') }}
                @if($tallasPedidoId)
                    <span class="text-sm text-gray-500 font-semibold">{{ __('orders.order_number', ['id' => $tallasPedidoId]) }}</span>
                @endif
            </h2>

            <button
                type="button"
                wire:click="cerrarModalTallas"
                class="absolute top-3 right-4 text-gray-500 hover:text-red-600 text-2xl leading-none"
                title="{{ __('orders.close') }}"
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
                                    {{ __('orders.subtotal') }} {{ number_format((int)$grupo['subtotal']) }}
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
                        {{ __('orders.total_label') }} {{ number_format((int)$tallasTotal) }} {{ __('orders.pieces') }}
                    </div>
                </div>

            @else
                <p class="text-sm text-gray-500">{{ __('orders.no_sizes_registered') }}</p>
            @endif
        </div>
    </div>
@endif

</div>

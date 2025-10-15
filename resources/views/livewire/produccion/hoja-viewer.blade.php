<div x-data="{ active: @entangle('activeFiltroId'), selected: [] }" >

        @php
            // Colores fijos para estados de proyecto/diseño
            $coloresEstadoDiseno = [
                'PENDIENTE'        => 'bg-yellow-400 text-black',
                'ASIGNADO'         => 'bg-blue-500 text-white',
                'EN PROCESO'       => 'bg-orange-500 text-white',
                'REVISION'         => 'bg-purple-600 text-white',
                'DISEÑO APROBADO'  => 'bg-emerald-600 text-white',
                'DISEÑO RECHAZADO' => 'bg-red-600 text-white',
                'CANCELADO'        => 'bg-gray-500 text-white',
            ];
        @endphp
        {{-- Header con búsqueda --}}
    <div     x-data="{
                active: @entangle('activeFiltroId'),
                selected: @entangle('selectedIds').live,
                idsPagina: @entangle('idsPagina').live
            }"
        class="p-2 sm:p-3 h-full min-h-0 flex flex-col">



        {{-- Header con búsqueda y acciones --}}
        <div class="mb-4 flex flex-wrap items-center gap-2">
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

            {{-- Buscador + Limpiar --}}
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <input
                    class="w-full sm:w-72 rounded-lg border-gray-300 focus:ring-blue-500"
                    placeholder="Buscar…"
                    wire:model.live.debounce.400ms="search"
                >
                <button
                    type="button"
                    class="px-3 py-2 rounded-lg border text-sm bg-white hover:bg-gray-50"
                    wire:click="limpiarFiltros"
                    title="Limpiar filtros"
                >
                    Limpiar
                </button>
            </div>

            {{-- Acciones en grupo --}}
            <div class="w-full sm:w-auto flex items-center gap-2">

                <div class="w-full sm:w-auto flex items-center gap-2" >
                    <x-dropdown>
                        <x-slot name="trigger">
                            <x-button
                                type="button"
                                label="Acciones"
                                gray
                                x-bind:disabled="selected.length === 0"
                                class="disabled:opacity-50 disabled:cursor-not-allowed"
                            />
                        </x-slot>

                        {{-- Solo renderiza opciones permitidas --}}
                        @if($acciones['bulk_edit_estado'])
                            <x-dropdown.item
                                @click="
                                    if (!selected.length) { alert('Selecciona al menos un pedido.'); return; }
                                    if (confirm('¿Marcar como RECHAZADO los pedidos seleccionados?')) {
                                        $wire.cambiarEstadoRechazado(selected);
                                    }
                                "
                                label="Rechazar seleccionados"
                            />
                        @endif

                        @if($acciones['bulk_aprobar'])
                            <x-dropdown.item
                                @click="
                                    if (!selected.length) { alert('Selecciona al menos un pedido.'); return; }
                                    $wire.dispatch('abrir-modal-aprobar', { ids: selected })
                                "
                                label="Aprobar seleccionados"
                            />
                        @endif

                        @if($acciones['bulk_programar'])
                            <x-dropdown.item
                                @click="$wire.dispatch('abrir-modal-programar', { ids: selected })"
                                label="Programar seleccionados"
                            />
                        @endif

                        @if($acciones['bulk_exportar'])
                            <x-dropdown.item separator
                                @click="$wire.dispatch('exportar-seleccion', { ids: selected })"
                                label="Exportar seleccionados"
                            />
                        @endif

                        @if($acciones['bulk_eliminar'])
                            <x-dropdown.item separator
                                @click="
                                    if (confirm('¿Eliminar definitivamente los pedidos seleccionados?')) {
                                        $wire.dispatch('eliminar-seleccion', { ids: selected })
                                    }
                                "
                                label="Archivar seleccionados"
                            />
                        @endif
                    </x-dropdown>
                </div>
            </div>


            
                        
            
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

        <div class="gap-2">
            
            @if(method_exists($pedidos,'links'))
                <div class="mt-4">{{ $pedidos->links() }}</div>
            @endif
        </div>


        {{-- Tabla --}}
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full border-collapse border border-gray-200">
                <thead class="bg-gray-100">
                    <tr >
                        <th class="px-3 py-2">
                            <input
                                type="checkbox"
                                
                                :checked="idsPagina.length && idsPagina.every(id => selected.includes(Number(id)))"
                                @change="
                                    const pagina = idsPagina.map(Number);
                                    if ($event.target.checked) {
                                    
                                    selected = Array.from(new Set([...selected.map(Number), ...pagina]));
                                    } else {
                                    
                                    selected = selected.map(Number).filter(i => !pagina.includes(i));
                                    }"
                            />
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
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                            <div class="inline-flex items-center gap-1">


                                {{-- Filtro ID en dropdown --}}
                                <div x-data="{ open:false }" class="relative">
                                    <button @click="open = !open" class="p-1 rounded hover:bg-gray-200" title="Filtrar ID">
                                        ⋮
                                    </button>
                                    <div
                                        x-cloak x-show="open" @click.away="open=false" x-transition
                                        class="absolute z-50 mt-1 w-56 rounded-lg border bg-white shadow p-3"
                                    >
                                        <label class="block text-xs text-gray-600 mb-1">ID Proyecto o 12-345</label>
                                        <input
                                            type="text"
                                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                            placeholder="ID Proyecto o 12-345"
                                            wire:model.live.debounce.400ms="filters.id"
                                        />
                                        <div class="mt-2 flex justify-end gap-2">
                                            <button type="button" class="px-2 py-1 text-xs rounded border" @click="$wire.set('filters.id','')">Limpiar</button>
                                            <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </th>

                        {{-- Filtros para columnas base (según key) --}}
                        @foreach($baseCols as $bc)
                        @if(($bc['key'] ?? '') !== 'id' && ($bc['visible'] ?? true))
                            <th class="px-3 py-2">
                            <div x-data="{ open:false }" class="relative inline-flex items-center">
                                <button
                                @click="open = !open"
                                class="px-2 py-1 rounded hover:bg-gray-200 text-sm"
                                title="Filtrar {{ $bc['label'] ?? $bc['key'] }}"
                                >
                                ⋮
                                </button>

                                {{-- Indicador filtro activo por tipo --}}
                                @switch($bc['key'])
                                @case('proyecto')
                                    <span x-cloak class="ml-1 w-2 h-2 rounded-full bg-blue-600" x-show="$wire.get('filters.proyecto')?.length"></span>
                                    @break
                                @case('cliente')
                                    <span x-cloak class="ml-1 w-2 h-2 rounded-full bg-blue-600" x-show="$wire.get('filters.cliente')?.length"></span>
                                    @break
                                @case('producto')
                                    <span x-cloak class="ml-1 w-2 h-2 rounded-full bg-blue-600" x-show="$wire.get('filters.producto')?.length"></span>
                                    @break
                                @case('estado')
                                    <span x-cloak class="ml-1 w-2 h-2 rounded-full bg-blue-600" x-show="$wire.get('filters.estado_id')"></span>
                                    @break
                                @case('estado_disenio')
                                    <span x-cloak class="ml-1 w-2 h-2 rounded-full bg-blue-600" x-show="$wire.get('filters.estado_disenio')?.length"></span>
                                    @break
                                @case('total')
                                    <span x-cloak class="ml-1 w-2 h-2 rounded-full bg-blue-600" x-show="$wire.get('filters.total')?.length"></span>
                                    @break
                                @case('fecha_produccion')
                                    <span x-cloak class="ml-1 w-2 h-2 rounded-full bg-blue-600"
                                        x-show="$wire.get('filters.fecha_produccion_from') || $wire.get('filters.fecha_produccion_to')"></span>
                                    @break
                                @case('fecha_embarque')
                                    <span x-cloak class="ml-1 w-2 h-2 rounded-full bg-blue-600"
                                        x-show="$wire.get('filters.fecha_embarque_from') || $wire.get('filters.fecha_embarque_to')"></span>
                                    @break
                                @case('fecha_entrega')
                                    <span x-cloak class="ml-1 w-2 h-2 rounded-full bg-blue-600"
                                        x-show="$wire.get('filters.fecha_entrega_from') || $wire.get('filters.fecha_entrega_to')"></span>
                                    @break
                                @default
                                    <span x-cloak class="ml-1 w-2 h-2 rounded-full bg-blue-600"
                                        x-show="$wire.get('filters.{{ $bc['key'] }}')?.length"></span>
                                @endswitch

                                <div
                                x-cloak x-show="open" @click.away="open=false" x-transition
                                class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3"
                                >
                                @switch($bc['key'])
                                    @case('proyecto')
                                    <label class="block text-xs text-gray-600 mb-1">Proyecto</label>
                                    <input class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                            placeholder="Proyecto…"
                                            wire:model.live.debounce.400ms="filters.proyecto">
                                    @break

                                    @case('cliente')
                                    <label class="block text-xs text-gray-600 mb-1">Cliente</label>
                                    <input class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                            placeholder="Cliente…"
                                            wire:model.live.debounce.400ms="filters.cliente">
                                    @break

                                    @case('producto')
                                    <label class="block text-xs text-gray-600 mb-1">Producto</label>
                                    <input class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                            placeholder="Producto…"
                                            wire:model.live.debounce.400ms="filters.producto">
                                    @break

                                    @case('estado')
                                    <label class="block text-xs text-gray-600 mb-1">Estado</label>
                                    <select class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                            wire:model.live.debounce.400ms="filters.estado_id">
                                        <option value="">Todos</option>
                                        @foreach($this->estados as $e)
                                        <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @break

                                    @case('estado_disenio')
                                    <label class="block text-xs text-gray-600 mb-1">Estado Diseño</label>
                                    <select class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                            wire:model.live.debounce.400ms="filters.estado_disenio">
                                        <option value="">Todos</option>
                                        @foreach($this->estadosDiseno as $s)
                                        <option value="{{ $s }}">{{ $s }}</option>
                                        @endforeach
                                    </select>
                                    @break

                                    @case('total')
                                    <label class="block text-xs text-gray-600 mb-1">Total</label>
                                    <input class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                            placeholder="Total…"
                                            wire:model.live.debounce.400ms="filters.total">
                                    @break

                                    @case('fecha_produccion')
                                    <label class="block text-xs text-gray-600 mb-1">Producción</label>
                                    <div class="space-y-2">
                                        <div>
                                        <span class="text-xs text-gray-600">Desde</span>
                                        <input type="date" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                wire:model.live.debounce.400ms="filters.fecha_produccion_from">
                                        </div>
                                        <div>
                                        <span class="text-xs text-gray-600">Hasta</span>
                                        <input type="date" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                wire:model.live.debounce.400ms="filters.fecha_produccion_to">
                                        </div>
                                    </div>
                                    @break

                                    @case('fecha_embarque')
                                    <label class="block text-xs text-gray-600 mb-1">Embarque</label>
                                    <div class="space-y-2">
                                        <div>
                                        <span class="text-xs text-gray-600">Desde</span>
                                        <input type="date" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                wire:model.live.debounce.400ms="filters.fecha_embarque_from">
                                        </div>
                                        <div>
                                        <span class="text-xs text-gray-600">Hasta</span>
                                        <input type="date" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                wire:model.live.debounce.400ms="filters.fecha_embarque_to">
                                        </div>
                                    </div>
                                    @break

                                    @case('fecha_entrega')
                                    <label class="block text-xs text-gray-600 mb-1">Entrega</label>
                                    <div class="space-y-2">
                                        <div>
                                        <span class="text-xs text-gray-600">Desde</span>
                                        <input type="date" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                wire:model.live.debounce.400ms="filters.fecha_entrega_from">
                                        </div>
                                        <div>
                                        <span class="text-xs text-gray-600">Hasta</span>
                                        <input type="date" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                wire:model.live.debounce.400ms="filters.fecha_entrega_to">
                                        </div>
                                    </div>
                                    @break

                                    @default
                                    <label class="block text-xs text-gray-600 mb-1">{{ $bc['label'] ?? $bc['key'] }}</label>
                                    <input class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                            placeholder="Filtrar…"
                                            wire:model.live.debounce.400ms="filters.{{ $bc['key'] }}">
                                @endswitch

                                <div class="mt-3 flex justify-end gap-2">
                                    @switch($bc['key'])
                                    @case('proyecto')
                                        <button type="button" class="px-2 py-1 text-xs rounded border" @click="$wire.set('filters.proyecto','')">Limpiar</button>
                                        @break
                                    @case('cliente')
                                        <button type="button" class="px-2 py-1 text-xs rounded border" @click="$wire.set('filters.cliente','')">Limpiar</button>
                                        @break
                                    @case('producto')
                                        <button type="button" class="px-2 py-1 text-xs rounded border" @click="$wire.set('filters.producto','')">Limpiar</button>
                                        @break
                                    @case('estado')
                                        <button type="button" class="px-2 py-1 text-xs rounded border" @click="$wire.set('filters.estado_id',null)">Limpiar</button>
                                        @break
                                    @case('estado_disenio')
                                        <button type="button" class="px-2 py-1 text-xs rounded border" @click="$wire.set('filters.estado_disenio','')">Limpiar</button>
                                        @break
                                    @case('total')
                                        <button type="button" class="px-2 py-1 text-xs rounded border" @click="$wire.set('filters.total','')">Limpiar</button>
                                        @break
                                    @case('fecha_produccion')
                                        <button type="button" class="px-2 py-1 text-xs rounded border"
                                                @click="$wire.set('filters.fecha_produccion_from', null); $wire.set('filters.fecha_produccion_to', null)">Limpiar</button>
                                        @break
                                    @case('fecha_embarque')
                                        <button type="button" class="px-2 py-1 text-xs rounded border"
                                                @click="$wire.set('filters.fecha_embarque_from', null); $wire.set('filters.fecha_embarque_to', null)">Limpiar</button>
                                        @break
                                    @case('fecha_entrega')
                                        <button type="button" class="px-2 py-1 text-xs rounded border"
                                                @click="$wire.set('filters.fecha_entrega_from', null); $wire.set('filters.fecha_entrega_to', null)">Limpiar</button>
                                        @break
                                    @default
                                        <button type="button" class="px-2 py-1 text-xs rounded border"
                                                @click="$wire.set('filters.{{ $bc['key'] }}','')">Limpiar</button>
                                    @endswitch
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">Cerrar</button>
                                </div>
                                </div>
                            </div>
                            </th>
                        @endif
                        @endforeach


                        {{-- Filtros para columnas dinámicas (características) --}}
                        @foreach($columnasFiltro as $col)
                        @php $label = $col['label'] ?? $col['nombre']; @endphp
                        <th class="px-3 py-2">
                            <div x-data="{ open:false }" class="relative inline-flex items-center">
                            <button @click="open = !open" class="px-2 py-1 rounded hover:bg-gray-200 text-sm" title="Filtrar {{ $label }}">⋮</button>
                            <span x-cloak class="ml-1 w-2 h-2 rounded-full bg-blue-600"
                                    x-show="$wire.get('filtersCar.{{ $col['id'] }}')?.length"></span>

                            <div
                                x-cloak x-show="open" @click.away="open=false" x-transition
                                class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3"
                            >
                                <label class="block text-xs text-gray-600 mb-1">{{ $label }}</label>
                                <input
                                class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                placeholder="Buscar {{ $label }}…"
                                wire:model.live.debounce.400ms="filtersCar.{{ $col['id'] }}"
                                >
                                <div class="mt-3 flex justify-end gap-2">
                                <button type="button" class="px-2 py-1 text-xs rounded border"
                                        @click="$wire.set('filtersCar.{{ $col['id'] }}','')">Limpiar</button>
                                <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">Cerrar</button>
                                </div>
                            </div>
                            </div>
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
                                        :checked="selected.includes(Number({{ $pedido->id }}))"
                                        @change="
                                            const id = Number({{ $pedido->id }});
                                            if ($event.target.checked) {
                                                if (!selected.includes(id)) selected.push(id)
                                            } else {
                                                selected = selected.filter(i => i !== id)
                                            }
                                        "
                                        wire:key="chk-{{ $pedido->id }}"
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
                                                @case('estado')
                                                    @php
                                                        $nombreEstado = $pedido->estadoPedido->nombre ?? '—';
                                                        $claseColor   = trim((string)($pedido->estadoPedido->color ?? '')) ?: 'bg-gray-200 text-gray-700';
                                                    @endphp

                                                    @if(   $acciones['bulk_edit_estado'])
                                                            <div
                                                            x-data="{ edit:false, value:'{{ $pedido->estado_id }}' }"
                                                            wire:key="cell-estado-{{ $pedido->id }}"
                                                            class="inline-flex items-center gap-2"
                                                            >
                                                            <span
                                                                x-cloak
                                                                x-show="!edit"
                                                                @dblclick="edit=true"
                                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap min-w-[9rem] justify-center {{ $claseColor }} cursor-pointer"
                                                                title="{{ $nombreEstado }}"
                                                            >{{ $nombreEstado }}</span>

                                                            <select
                                                                x-cloak
                                                                x-show="edit"
                                                                x-model="value"
                                                                @change="$wire.updateField({{ $pedido->id }}, 'estado_id', value); edit=false;"
                                                                class="w-44 rounded-lg border-gray-300 focus:ring-blue-500 text-xs"
                                                            >
                                                                @foreach($this->estadosAll as $e)
                                                                <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                                                                @endforeach
                                                            </select>

                                                            <button type="button" class="text-xs text-blue-600 hover:underline" @click="edit = !edit" x-text="edit ? 'Guardar' : 'Editar'"></button>
                                                            </div>
                                                        @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap min-w-[9rem] justify-center {{ $claseColor }}" title="{{ $nombreEstado }}">{{ $nombreEstado }}</span>
                                                    @endif

                                                @break
                                                @case('estado_disenio')
                                                    @php
                                                        $estadoDiseno = $pedido->proyecto->estado ?? '—';
                                                        $claseDiseno  = $coloresEstadoDiseno[$estadoDiseno] ?? 'bg-gray-200 text-gray-700';
                                                    @endphp

                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap min-w-[11rem] justify-center {{ $claseDiseno }}"
                                                        title="{{ $estadoDiseno }}"
                                                    >
                                                        {{ $estadoDiseno }}
                                                    </span>
                                                @break
                                                @case('total')
                                                    @if( $acciones['bulk_edit_total'])
                                                        <div x-data="{
                                                                edit:false,
                                                                value:'{{ number_format((float)($pedido->total ?? 0), 2, '.', '') }}',
                                                                save(){ this.edit=false; $wire.updateField({{ $pedido->id }}, 'total', this.value); }
                                                            }"
                                                            wire:key="cell-fecha-produccion-{{ $pedido->id }}"
                                                            wire:key="cell-total-{{ $pedido->id }}"
                                                            class="inline-flex items-center gap-2">
                                                            <template x-if="!edit">
                                                                <span @dblclick="edit=true" class="cursor-pointer select-none">
                                                                    {{ number_format((float)($pedido->total ?? 0), 2) }}
                                                                </span>
                                                            </template>
                                                            <template x-if="edit">
                                                                <input type="number" step="0.01" x-model="value"
                                                                    @keydown.enter.prevent="save()" @blur="save()"
                                                                    class="w-28 rounded-lg border-gray-300 focus:ring-blue-500 text-sm">
                                                            </template>
                                                            <button type="button" class="text-xs text-blue-600 hover:underline"
                                                                    @click="edit = !edit" x-text="edit ? 'Guardar' : 'Editar'"></button>
                                                        </div>
                                                    @else
                                                        {{ number_format((float)($pedido->total ?? 0), 2) }}
                                                    @endif
                                                @break

                                                {{-- NUEVOS: fechas (si tienes casts a date en el modelo Pedido, usa ->format) $acciones['editar_pedido'] || --}}
                                                @case('fecha_produccion')

                                                @if(   $acciones['bulk_edit_fecha_produccion'])
                                                    <div x-data="{
                                                            edit:false,
                                                            value:'{{ $pedido->fecha_produccion?->format('Y-m-d') }}',
                                                            save(){ this.edit=false; $wire.updateField({{ $pedido->id }}, 'fecha_produccion', this.value || null); }
                                                        }" wire:key="cell-fecha-produccion-{{ $pedido->id }}" class="inline-flex items-center gap-2">
                                                        <template x-if="!edit">
                                                            <span @dblclick="edit=true" class="cursor-pointer select-none">
                                                                {{ $pedido->fecha_produccion?->format('d/m/Y') ?? '—' }}
                                                            </span>
                                                        </template>
                                                        <template x-if="edit">
                                                            <input type="date" x-model="value"
                                                                @keydown.enter.prevent="save()" @blur="save()"
                                                                class="w-40 rounded-lg border-gray-300 focus:ring-blue-500 text-sm">
                                                        </template>
                                                        <button type="button" class="text-xs text-blue-600 hover:underline" @click="edit = !edit" x-text="edit ? 'Guardar' : 'Editar'"></button>
                                                    </div>
                                                @else
                                                    <span class="cursor-pointer select-none">
                                                        {{ $pedido->fecha_produccion?->format('d/m/Y') ?? '—' }}
                                                    </span>
                                                @endif

                                                @break

                                                @case('fecha_embarque')

                                                    @if(   $acciones['bulk_edit_fecha_embarque'])
                                                        <div x-data="{
                                                            edit:false,
                                                            value:'{{ $pedido->fecha_embarque?->format('Y-m-d') }}',
                                                            save(){ this.edit=false; $wire.updateField({{ $pedido->id }}, 'fecha_embarque', this.value || null); }
                                                        }" wire:key="cell-fecha-produccion-{{ $pedido->id }}" class="inline-flex items-center gap-2">
                                                            <template x-if="!edit">
                                                                <span @dblclick="edit=true" class="cursor-pointer select-none">
                                                                    {{ $pedido->fecha_embarque?->format('d/m/Y') ?? '—' }}
                                                                </span>
                                                            </template>
                                                            <template x-if="edit">
                                                                <input type="date" x-model="value"
                                                                    @keydown.enter.prevent="save()" @blur="save()"
                                                                    class="w-40 rounded-lg border-gray-300 focus:ring-blue-500 text-sm">
                                                            </template>
                                                            <button type="button" class="text-xs text-blue-600 hover:underline" @click="edit = !edit" x-text="edit ? 'Guardar' : 'Editar'"></button>
                                                        </div>
                                                    @else
                                                                    <span class="cursor-pointer select-none">
                                                                    {{ $pedido->fecha_embarque?->format('d/m/Y') ?? '—' }}
                                                                    </span>
                                                    @endif

                                                @break

                                                @case('fecha_entrega')

                                                    @if (   $acciones['bulk_edit_fecha_entrega'])
                                                        <div x-data="{
                                                                edit:false,
                                                                value:'{{ $pedido->fecha_entrega?->format('Y-m-d') }}',
                                                                save(){ this.edit=false; $wire.updateField({{ $pedido->id }}, 'fecha_entrega', this.value || null); }
                                                            }" wire:key="cell-fecha-produccion-{{ $pedido->id }}" class="inline-flex items-center gap-2">
                                                            <template x-if="!edit">
                                                                <span @dblclick="edit=true" class="cursor-pointer select-none">
                                                                    {{ $pedido->fecha_entrega?->format('d/m/Y') ?? '—' }}
                                                                </span>
                                                            </template>
                                                            <template x-if="edit">
                                                                <input type="date" x-model="value"
                                                                    @keydown.enter.prevent="save()" @blur="save()"
                                                                    class="w-40 rounded-lg border-gray-300 focus:ring-blue-500 text-sm">
                                                            </template>
                                                            <button type="button" class="text-xs text-blue-600 hover:underline" @click="edit = !edit" x-text="edit ? 'Guardar' : 'Editar'"></button>
                                                        </div>
                                                    @else
                                                                <span  class="cursor-pointer select-none">
                                                                    {{ $pedido->fecha_entrega?->format('d/m/Y') ?? '—' }}
                                                                </span>
                                                    @endif
                                    
                                                @break

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
                                                @if($acciones['ver_detalle'])
                                                    <x-dropdown.item
                                                        @click="$wire.dispatch('ir-a-detalle', { id: {{ $pedido->id }} })"
                                                        label="Ver detalle"
                                                    />
                                                @endif

                                                @if($acciones['abrir_chat'])
                                                    <x-dropdown.item
                                                        @click="$wire.dispatch('abrir-chat', { proyecto_id: {{ $pedido->proyecto_id }} })"
                                                        label="Abrir chat"
                                                    />
                                                @endif

                                                @if($acciones['crear_tarea'])
                                                    <x-dropdown.item
                                                        @click="$wire.dispatch('abrir-modal-tarea', { pedido_id: {{ $pedido->id }} })"
                                                        label="Crear tarea"
                                                    />
                                                @endif

                                                @if($acciones['editar_pedido'])
                                                    <x-dropdown.item
                                                        @click="$wire.dispatch('editar-pedido', { id: {{ $pedido->id }} })"
                                                        label="Editar pedido"
                                                    />
                                                @endif

                                                @if($acciones['duplicar_pedido'])
                                                    <x-dropdown.item
                                                        @click="$wire.dispatch('duplicar-pedido', { id: {{ $pedido->id }} })"
                                                        label="Duplicar pedido"
                                                    />
                                                @endif

                                                @if($acciones['eliminar_pedido'])
                                                    <x-dropdown.item separator
                                                        @click="if (confirm('¿Eliminar este pedido?')) $wire.dispatch('eliminar-pedido', { id: {{ $pedido->id }} })"
                                                        label="Archivar pedido"
                                                    />
                                                @endif

                                                @if($acciones['programar_pedido'])
                                                    <x-dropdown.item
                                                        @click="$wire.dispatch('programar-pedido', { id: {{ $pedido->id }} })"
                                                        label="Programar pedido"
                                                    />
                                                @endif

                                                @if($acciones['entregar_pedido'])
                                                    <x-dropdown.item
                                                        @click="$wire.dispatch('abrir-modal-entrega', { id: {{ $pedido->id }} })"
                                                        label="Entregar pedido"
                                                    />
                                                @endif

                                                @if($acciones['cancelar_pedido'])
                                                    <x-dropdown.item
                                                        @click="if (confirm('¿Cancelar este pedido?')) $wire.dispatch('cancelar-pedido', { id: {{ $pedido->id }} })"
                                                        label="Cancelar pedido"
                                                    />
                                                @endif

                                                @if($acciones['subir_archivos'])
                                                    <x-dropdown.item
                                                        @click="$wire.dispatch('subir-archivos', { id: {{ $pedido->id }} })"
                                                        label="Subir archivos"
                                                    />
                                                @endif

                                                @if($acciones['exportar_excel'])
                                                    <x-dropdown.item separator
                                                        @click="$wire.dispatch('exportar-excel-pedido', { id: {{ $pedido->id }} })"
                                                        label="Exportar a Excel"
                                                    />
                                                @endif
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

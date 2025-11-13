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
        Pedidos de Proyecto
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
                                <div 
                                  
                                    class="w-full bg-white border border-gray-200 shadow-md rounded-lg"
                                >
                                    <div class="flex justify-between items-center p-4 border-b">
                                        <h2 class="text-lg font-bold text-gray-700">Filtros</h2>
                                        <div class="flex items-center gap-2">
                                            <button 
                                                wire:click="buscarPorFiltros"
                                                class="bg-white border border-gray-300 text-gray-700 px-3 py-1 rounded hover:bg-gray-100 text-sm"
                                            >
                                                Filtrar
                                            </button>
                                            <button 
                                                @click="abierto = false" 
                                                class="text-gray-500 hover:text-gray-700 text-xl leading-none"
                                            >
                                                ✕
                                            </button>
                                        </div>
                                    </div>

                                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                               <div class="flex items-center space-x-2">
                                                    <input
                                                        type="checkbox"
                                                        id="no-aprobados"
                                                        wire:model.defer="mostrarSoloNoAprobados"
                                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                                    />
                                                    <label for="no-aprobados" class="text-sm text-gray-700">
                                                        Mostrar pedidos de diseños No aprobados
                                                    </label>
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

                <!-- PESTAÑAS PEDIDOS | MUESTRAS -->
            <ul class="flex flex-wrap border-b border-gray-200 mb-4 gap-1">
                @foreach ($this->tabs as $tab)
                    <li>
                        <button
                            wire:click="setTab('{{ $tab }}')"
                            @class([
                                'px-2 py-1 rounded-t-lg text-sm whitespace-nowrap',
                                'border-b-2 font-semibold bg-white' => $activeTab === $tab,
                                'text-gray-600 hover:text-blue-500' => $activeTab !== $tab,
                                'border-blue-500 text-blue-600'     => $activeTab === $tab,
                                'border-transparent'                => $activeTab !== $tab,
                            ])
                        >
                            {{ $tab }}
                        </button>
                    </li>
                @endforeach
            </ul>

            @php
                $arrow = function(string $field) use ($sortField, $sortDir) {
                    if ($sortField !== $field) return '⇅';
                    return $sortDir === 'asc' ? '▲' : '▼';
                };
            @endphp


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
                        <button class="inline-flex items-center gap-1 hover:text-blue-600" wire:click="sortBy('estado_diseno')">
                            <span>Estado del Diseño</span>
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
                            <span>Estado del Pedido</span>
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

                    {{-- Filtro Estado Diseño --}}
                    <th class="px-3 py-2">
                        <div x-data="{ open:false }" class="relative inline-flex items-center">
                            <button @click="open = !open" class="px-2 py-1 rounded hover:bg-gray-200 text-sm" title="Filtrar Estado Diseño">⋮</button>
                            <div x-cloak x-show="open" @click.away="open=false" x-transition class="absolute z-50 mt-1 w-56 rounded-lg border bg-white shadow p-3">
                                <label class="block text-xs text-gray-600 mb-1">Estado del Diseño</label>
                                <select class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm" wire:model.live="filters.estado_diseno">
                                    <option value="">— Cualquiera —</option>
                                    <option value="PENDIENTE">PENDIENTE</option>
                                    <option value="ASIGNADO">ASIGNADO</option>
                                    <option value="EN PROCESO">EN PROCESO</option>
                                    <option value="REVISION">REVISION</option>
                                    <option value="DISEÑO APROBADO">DISEÑO APROBADO</option>
                                    <option value="DISEÑO RECHAZADO">DISEÑO RECHAZADO</option>
                                    <option value="CANCELADO">CANCELADO</option>
                                </select>
                                <div class="mt-2 flex justify-end gap-2">
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="$wire.set('filters.estado_diseno','')">Limpiar</button>
                                    <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </th>

                    {{-- Filtro Estado Pedido --}}
                    <th class="px-3 py-2">
                        <div x-data="{ open:false }" class="relative inline-flex items-center">
                            <button @click="open = !open" class="px-2 py-1 rounded hover:bg-gray-200 text-sm" title="Filtrar Estado Pedido">⋮</button>
                            <div x-cloak x-show="open" @click.away="open=false" x-transition class="absolute z-50 mt-1 w-56 rounded-lg border bg-white shadow p-3">
                                <label class="block text-xs text-gray-600 mb-1">Estado del Pedido</label>
                                <select class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm" wire:model.live="filters.estado_pedido">
                                    <option value="">— Cualquiera —</option>
                                    <option value="PENDIENTE">PENDIENTE</option>
                                    <option value="APROBADO">APROBADO</option>
                                    <option value="POR PROGRAMAR">POR PROGRAMAR</option>
                                    <option value="PROGRAMADO">PROGRAMADO</option>
                                    <option value="ENTREGADO">ENTREGADO</option>
                                    <option value="RECHAZADO">RECHAZADO</option>
                                    <option value="CANCELADO">CANCELADO</option>
                                    <option value="ARCHIVADO">ARCHIVADO</option>
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
                        <td class="px-3 py-2">{{ number_format((float)($pedido->total ?? 0), 0) }} piezas</td>

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
                                {{ $estadoDiseno }}
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
                                {{ strtoupper($pedido->estado) }}
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

                                <x-dropdown.item separator>
                                    <b wire:click="abrirModalVerInfo({{ $pedido->proyecto_id }})">Ver información</b>
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
                                    <x-dropdown.item separator
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
                                    <x-dropdown.item separator
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

</div>

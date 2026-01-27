<div
    x-data="{
        abierto: JSON.parse(localStorage.getItem('dashboard_pedidos_proveedor_abierto') ?? 'true'),
        toggle() {
            this.abierto = !this.abierto;
            localStorage.setItem('dashboard_pedidos_proveedor_abierto', JSON.stringify(this.abierto));
        }
    }"
    class="p-2 sm:p-3 h-full min-h-0 flex flex-col"
>
    <h2
        @click="toggle()"
        class="text-xl font-bold mb-4 border-b border-gray-300 pb-2 cursor-pointer hover:text-blue-600 transition"
    >
        Pedidos asignados (Proveedor)
        <span class="text-sm text-gray-500 ml-2" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
    </h2>

    <div x-show="abierto" x-transition>

        @if (session()->has('message'))
            <div class="mb-3 p-3 rounded-lg bg-emerald-100 text-emerald-800">
                {{ session('message') }}
            </div>
        @endif

        {{-- Tabs PEDIDOS | MUESTRAS --}}
        <ul class="flex flex-wrap border-b border-gray-200 mb-4 gap-1">
            @foreach ($tabs as $tab)
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

        {{-- Filtros (colapsable) --}}
        <div class="mb-4">
            <button wire:click="$toggle('mostrarFiltros')" class="text-sm text-blue-600 hover:underline">
                {{ $mostrarFiltros ? 'Ocultar filtros' : 'Mostrar filtros' }}
            </button>
        </div>

        @if($mostrarFiltros)
            <div class="w-full bg-white border border-gray-200 shadow-md rounded-lg mb-6">
                <div class="flex justify-between items-center p-4 border-b">
                    <h2 class="text-lg font-bold text-gray-700">Filtros</h2>
                    <div class="flex items-center gap-2">
                        <button wire:click="buscarPorFiltros"
                            class="bg-white border border-gray-300 text-gray-700 px-3 py-1 rounded hover:bg-gray-100 text-sm">
                            Filtrar
                        </button>
                        <button wire:click="clearFilters"
                            class="bg-white border border-gray-300 text-gray-700 px-3 py-1 rounded hover:bg-gray-100 text-sm">
                            Limpiar
                        </button>
                    </div>
                </div>

                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm text-gray-700 font-medium">ID pedido/proyecto</label>
                        <input type="text"
                            wire:model.live.debounce.400ms="filters.id"
                            class="w-full rounded-lg border-gray-300 text-sm"
                            placeholder="Ej. 1001 o 1001,1002">
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-sm text-gray-700 font-medium">Proyecto</label>
                        <input type="text"
                            wire:model.live.debounce.400ms="filters.proyecto"
                            class="w-full rounded-lg border-gray-300 text-sm"
                            placeholder="Nombre del proyecto…">
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-sm text-gray-700 font-medium">Cliente</label>
                        <input type="text"
                            wire:model.live.debounce.400ms="filters.cliente"
                            class="w-full rounded-lg border-gray-300 text-sm"
                            placeholder="Nombre o correo…">
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-sm text-gray-700 font-medium">Registros</label>
                        <select wire:model.live="perPage" class="w-full rounded-lg border-gray-300 text-sm">
                            @foreach($perPageOptions as $n)
                                <option value="{{ $n }}">{{ $n }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model.live="filters.inactivos"
                            class="rounded border-gray-300 text-blue-600">
                        <label class="text-sm text-gray-700">Solo inactivos</label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model.live="filters.solo_no_vistos"
                            class="rounded border-gray-300 text-blue-600">
                        <label class="text-sm text-gray-700">Solo no vistos</label>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-sm text-gray-700 font-medium">Estatus proveedor</label>
                        <select wire:model.live="filters.estatus_proveedor" class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="">Todos</option>
                            @foreach($estatusProveedorOptions as $opt)
                                <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="flex flex-col gap-1">
                            <label class="text-sm text-gray-700 font-medium">Desde</label>
                            <input type="date" wire:model.live="filters.fecha_desde" class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm text-gray-700 font-medium">Hasta</label>
                            <input type="date" wire:model.live="filters.fecha_hasta" class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
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
                    @forelse($pedidos as $pedido)
                        <tr class="hover:bg-gray-50">
                            <td
                                class="p-2 px-4 py-2 font-semibold min-w-[4rem] cursor-help whitespace-nowrap"
                                title="{{ $pedido->tooltip_clave ?? '' }}"
                            >
                                {{ $pedido->clave ?? $pedido->id }}
                            </td>

                            <td class="p-2 whitespace-nowrap">
                                {{ $pedido->proyecto->nombre ?? '—' }}
                                <div class="text-xs text-gray-500">#{{ $pedido->proyecto_id }}</div>
                            </td>

                            <td class="p-2 min-w-[14rem]">
                                <div class="font-medium">{{ $pedido->producto->nombre ?? 'Sin producto' }}</div>
                                <div class="text-xs text-gray-500">{{ $pedido->producto->categoria->nombre ?? 'Sin categoría' }}</div>
                            </td>

                            <td class="p-2 whitespace-nowrap">
                                {{ number_format((float)($pedido->total ?? 0), 0) }} piezas
                            </td>

                            <td class="p-2 whitespace-nowrap">
                                {{ $pedido->fecha_produccion?->format('Y-m-d') ?? '—' }}
                            </td>

                            <td class="p-2 whitespace-nowrap">
                                {{ $pedido->fecha_embarque?->format('Y-m-d') ?? '—' }}
                            </td>

                            <td class="p-2 whitespace-nowrap">
                                {{ $pedido->fecha_entrega?->format('Y-m-d') ?? '—' }}
                            </td>

                            <td class="p-2 text-center whitespace-nowrap">
                                @php
                                    $e = strtoupper($pedido->estatus_proveedor ?? 'PENDIENTE');
                                    $badge = match($e) {
                                        'PENDIENTE'  => 'bg-gray-200 text-gray-800',
                                        'EN PROCESO' => 'bg-yellow-200 text-yellow-900',
                                        'LISTO'      => 'bg-emerald-200 text-emerald-900',
                                        'BLOQUEADO'  => 'bg-red-200 text-red-900',
                                        default      => 'bg-gray-200 text-gray-800',
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $badge }}">
                                    {{ $e }}
                                </span>
                            </td>

                            <td class="p-2 text-center whitespace-nowrap">
                                @if($pedido->proveedor_visto_at)
                                    <span class="text-xs text-gray-600">
                                        {{ $pedido->proveedor_visto_at->format('Y-m-d H:i') }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">No</span>
                                @endif
                            </td>

                            <td class="p-2 text-center whitespace-nowrap">
                                <x-dropdown>
                                    <x-dropdown.item>
                                        <b
                                            class="cursor-pointer"
                                            wire:click="abrirModalProveedor({{ $pedido->id }})"
                                        >
                                            Actualizar estatus proveedor
                                        </b>
                                    </x-dropdown.item>

                                    <x-dropdown.item separator
                                        :href="route('proyecto.proveedor.show', $pedido->proyecto_id)"
                                        label="Ver proyecto"
                                    />
                                </x-dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-6 text-center text-sm text-gray-500">
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

        {{-- Modal --}}
        @if($modalProveedor)
            <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                <div class="bg-white rounded-xl shadow-lg w-full max-w-lg mx-3 sm:mx-0 flex flex-col">
                    <div class="flex items-center justify-between border-b border-gray-200 p-4">
                        <h5 class="text-xl font-bold">Actualizar estatus de proveedor</h5>
                        <button class="text-gray-500 hover:text-gray-700" wire:click="$set('modalProveedor', false)">&times;</button>
                    </div>

                    <div class="p-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Estatus proveedor</label>
                            <select wire:model="estatus_proveedor" class="w-full rounded-lg border-gray-300 p-2">
                                @foreach($estatusProveedorOptions as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nota (opcional)</label>
                            <textarea wire:model.defer="nota_proveedor" rows="4" class="w-full rounded-lg border-gray-300 p-2"
                                placeholder="Ej: Falta material / esperando confirmación / listo para entregar..."></textarea>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 p-4 flex justify-end gap-2">
                        <button wire:click="$set('modalProveedor', false)"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-4 py-2 rounded-lg">
                            Cancelar
                        </button>
                        <button wire:click="guardarProveedor"
                            class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg">
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>

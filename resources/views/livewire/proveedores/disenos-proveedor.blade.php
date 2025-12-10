<div
    x-data="{
        abierto: JSON.parse(localStorage.getItem('proveedor_disenos_abierto') ?? 'true'),
        toggle() {
            this.abierto = !this.abierto;
            localStorage.setItem('proveedor_disenos_abierto', JSON.stringify(this.abierto));
        }
    }"
    class="container mx-auto p-6"
>
    <h2
        @click="toggle()"
        class="text-xl font-bold mb-4 border-b border-gray-300 pb-2 cursor-pointer hover:text-blue-600 transition"
    >
        Diseños asignados
        <span class="text-sm text-gray-500 ml-2" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
    </h2>

    <div x-show="abierto" x-transition>
        {{-- Tabs de estado --}}
        <ul class="flex flex-wrap border-b border-gray-200 mb-4 gap-1">
            @foreach ($this->tabs as $tab)
                <li>
                    <button
                        wire:click="setTab('{{ $tab }}')"
                        @class([
                            'px-4 py-2 rounded-t-lg text-sm whitespace-nowrap',
                            'border-b-2 font-semibold bg-white'     => $activeTab === $tab,
                            'text-gray-600 hover:text-blue-500'     => $activeTab !== $tab,
                            'border-blue-500 text-blue-600'         => $activeTab === $tab,
                            'border-transparent'                    => $activeTab !== $tab,
                        ])
                    >
                        {{ $tab }}
                    </button>
                </li>
            @endforeach
        </ul>

        {{-- Barra superior: per-page --}}
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                {{-- Puedes agregar chips/leyendas si quieres --}}
            </div>

            <div class="flex items-center gap-2">
                <label for="per-page" class="text-sm text-gray-600">Registros por página</label>
                <select
                    id="per-page"
                    class="w-28 rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500"
                    wire:model.live="perPage"
                >
                    @foreach($perPageOptions as $n)
                        <option value="{{ $n }}">{{ $n }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @php
            $arrow = function(string $field) use ($sortField, $sortDir) {
                if ($sortField !== $field) return '⇵';
                return $sortDir === 'asc' ? '▲' : '▼';
            };

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

        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="w-full table-auto border-collapse border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        {{-- ID --}}
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 align-top">
                            <div class="flex items-center justify-between gap-2 min-w-[10rem]">
                                <button
                                    class="inline-flex items-center gap-1 hover:text-blue-600"
                                    wire:click="sortBy('id')"
                                    title="Ordenar por ID"
                                >
                                    <span>ID</span>
                                    <span class="text-xs">{!! $arrow('id') !!}</span>
                                </button>

                                {{-- Filtro por ID con dropdown flotante --}}
                                <div x-data="dropdownTeleport()" class="relative shrink-0">
                                    <button
                                        x-ref="btn"
                                        @click="toggle"
                                        class="p-1 rounded hover:bg-gray-200"
                                        title="Filtros de ID"
                                    >
                                        ⋮
                                    </button>

                                    <template x-teleport="body">
                                        <div
                                            x-show="open"
                                            x-transition
                                            @click.outside="close"
                                            :style="style"
                                            class="fixed z-50 w-64 rounded-lg border bg-white shadow p-3 space-y-3"
                                        >
                                        <div>
                                                <label class="block text-xs text-gray-600 mb-1">
                                                    ID Proyecto (ej. 101 ó 101,102)
                                                </label>
                                                <input
                                                    type="text"
                                                    class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                    placeholder="ID…"
                                                    wire:model.live.debounce.400ms="filters.id"
                                                />
                                            </div>

                                            <div class="pt-1 flex justify-end gap-2">
                                                <button
                                                    type="button"
                                                    class="px-2 py-1 text-xs rounded border"
                                                    @click="$wire.clearFilters()"
                                                >
                                                    Limpiar
                                                </button>
                                                <button
                                                    type="button"
                                                    class="px-2 py-1 text-xs rounded border"
                                                    @click="close"
                                                >
                                                    Cerrar
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </th>

                        {{-- Nombre --}}
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 align-top">
                            <div class="flex items-center justify-between gap-2 min-w-[14rem]">
                                <button
                                    class="inline-flex items-center gap-1 hover:text-blue-600"
                                    wire:click="sortBy('nombre')"
                                    title="Ordenar por Nombre"
                                >
                                    <span>Nombre del Proyecto</span>
                                    <span class="text-xs">{!! $arrow('nombre') !!}</span>
                                </button>

                                <div x-data="{ open:false }" class="relative shrink-0">
                                    <button
                                        @click="open = !open"
                                        class="p-1 rounded hover:bg-gray-200"
                                        title="Filtrar Nombre"
                                    >
                                        ⋮
                                    </button>
                                    <div
                                        x-cloak
                                        x-show="open"
                                        @click.away="open=false"
                                        x-transition
                                        class="absolute right-0 z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3"
                                    >
                                        <label class="block text-xs text-gray-600 mb-1">Nombre contiene</label>
                                        <input
                                            type="text"
                                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                            placeholder="Buscar…"
                                            wire:model.live.debounce.400ms="filters.nombre"
                                        />
                                        <div class="mt-2 flex justify-end gap-2">
                                            <button
                                                type="button"
                                                class="px-2 py-1 text-xs rounded border"
                                                @click="$wire.set('filters.nombre','')"
                                            >
                                                Limpiar
                                            </button>
                                            <button
                                                type="button"
                                                class="px-2 py-1 text-xs rounded border"
                                                @click="open=false"
                                            >
                                                Cerrar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </th>

                        {{-- Cliente (siempre visible para proveedor) --}}
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 align-top">
                            <span class="min-w-[12rem] inline-block">Cliente</span>
                        </th>

                        @can('proveedor.ver-todos-disenos')
                            {{-- Proveedor asignado --}}
                            <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 align-top">
                                <span class="min-w-[12rem] inline-block">Proveedor asignado</span>
                            </th>
                        @endcan

                        {{-- Estado Proyecto --}}
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                            Estado Proyecto
                        </th>

                        {{-- Estado Diseño --}}
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 align-top">
                            <div class="flex items-center gap-2 min-w-[12rem]">
                                <button
                                    class="inline-flex items-center gap-1 hover:text-blue-600"
                                    wire:click="sortBy('estado')"
                                    title="Ordenar por Estado Diseño"
                                >
                                    <span>Estado Diseño</span>
                                    <span class="text-xs">{!! $arrow('estado') !!}</span>
                                </button>

                                <div x-data="{ open:false }" class="relative">
                                    <button
                                        @click="open = !open"
                                        class="p-1 rounded hover:bg-gray-200"
                                        title="Filtrar Estado"
                                    >
                                        ⋮
                                    </button>
                                    <div
                                        x-cloak
                                        x-show="open"
                                        @click.away="open=false"
                                        x-transition
                                        class="absolute z-50 mt-1 w-60 rounded-lg border bg-white shadow p-3"
                                    >
                                        <label class="block text-xs text-gray-600 mb-1">Estado Diseño</label>
                                        <select
                                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                            wire:model.live.debounce.400ms="filters.estado"
                                        >
                                            <option value="">Todos</option>
                                            @foreach(array_keys($coloresEstadoDiseno) as $est)
                                                <option value="{{ $est }}">{{ $est }}</option>
                                            @endforeach
                                        </select>
                                        <div class="mt-2 flex justify-end gap-2">
                                            <button
                                                type="button"
                                                class="px-2 py-1 text-xs rounded border"
                                                @click="$wire.set('filters.estado','')"
                                            >
                                                Limpiar
                                            </button>
                                            <button
                                                type="button"
                                                class="px-2 py-1 text-xs rounded border"
                                                @click="open=false"
                                            >
                                                Cerrar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </th>

                        {{-- Acciones --}}
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                            Acciones
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($projects as $project)
                        <tr class="hover:bg-gray-50">
                            {{-- ID con link --}}
                            <td
                                class="px-3 py-2 text-sm font-semibold min-w-[6rem]"
                                title="{{ $project->nombre ?? 'Proyecto #'.$project->id }}"
                            >
                                {!! $project->proyecto_link !!}
                            </td>

                            {{-- Nombre --}}
                            <td class="px-3 py-2 text-sm text-gray-700">
                                {{ $project->nombre }}
                            </td>

                            {{-- Cliente --}}
                            <td class="px-3 py-2 text-sm text-gray-700">
                                @if($project->user)
                                    <span class="inline-block">
                                        {{ $project->user->name }}
                                    </span>
                                @else
                                    <span class="text-gray-500">Sin Cliente</span>
                                @endif
                            </td>

                            @can('proveedor.ver-todos-disenos')
                                {{-- Proveedor asignado --}}
                                <td class="px-3 py-2 text-sm text-gray-700">
                                    @if($project->proveedor)
                                        <span class="inline-block">
                                            {{ $project->proveedor->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-500">Sin proveedor</span>
                                    @endif
                                </td>
                            @endcan

                            {{-- Estado Proyecto --}}
                            <td class="px-3 py-2 text-sm">
                                @if($project->ind_activo)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">
                                        Inactivo
                                    </span>
                                @endif
                            </td>

                            {{-- Estado Diseño --}}
                            @php
                                $estado = $project->estado ?? 'Sin estado';
                                $badge  = $coloresEstadoDiseno[$estado] ?? 'bg-gray-300 text-gray-700';
                            @endphp
                            <td class="px-3 py-2 text-sm whitespace-nowrap min-w-[10rem]">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap min-w-[10rem] justify-center {{ $badge }}"
                                >
                                    {{ $estado }}
                                </span>
                            </td>

                            {{-- Acciones: solo Ver detalles --}}
                            <td class="px-3 py-2 text-sm">
                                <x-dropdown>
                                    <x-dropdown.item
                                        :href="route('proyecto.proveedor.show', $project->id)"
                                        label="Ver detalles"
                                    />
                                </x-dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
                                No hay proyectos asignados para mostrar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="mt-4">
            {{ $projects->links() }}
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.dropdownTeleport = () => ({
                open: false,
                style: '',
                toggle() {
                    this.open = !this.open;
                    if (this.open) this.reposition();
                },
                close() { this.open = false; },
                reposition() {
                    const btn = this.$refs.btn;
                    if (!btn) return;
                    const r = btn.getBoundingClientRect();
                    const panelW = 256; // w-64
                    const gap = 6;

                    let left = r.right - panelW;
                    const top = r.bottom + gap;

                    const vw = window.innerWidth;
                    const margin = 8;

                    if (left < margin) left = margin;
                    if (left + panelW > vw - margin) left = vw - margin - panelW;

                    this.style = `top:${top}px;left:${left}px`;
                }
            });
        });
    </script>
</div>

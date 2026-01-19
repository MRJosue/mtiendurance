<div
    x-data="{
        abierto: JSON.parse(localStorage.getItem('dashboard_tareas_abierto') ?? 'true'),
        toggle() {
            this.abierto = !this.abierto;
            localStorage.setItem('dashboard_tareas_abierto', JSON.stringify(this.abierto));
        }
    }"
    class="p-2 sm:p-3 h-full min-h-0 flex flex-col"
>
    <h2
        @click="toggle()"
        class="text-xl font-bold mb-4 border-b border-gray-300 pb-2 cursor-pointer hover:text-blue-600 transition"
    >
        Tareas de Diseño
        <span class="text-sm text-gray-500 ml-2" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
    </h2>

    <div x-show="abierto" x-transition class="min-h-0">
        {{-- TOP BAR: chips + perPage (igual que Diseños) --}}
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                @php
                    $estadoChips = ['PENDIENTE','EN PROCESO','COMPLETADA','RECHAZADO','CANCELADO'];
                @endphp

                <div class="flex flex-wrap gap-2">
                    @foreach($estadoChips as $chip)
                        <button
                            class="px-3 py-1 text-xs rounded-full border hover:bg-gray-100
                                   @if(($filters['estado'] ?? '') === $chip) bg-blue-50 border-blue-400 text-blue-700 @endif"
                            wire:click="filtroEstado('{{ $chip }}')"
                            title="Filtrar por {{ $chip }}"
                        >
                            {{ $chip }}
                        </button>
                    @endforeach

                    @if(($filters['estado'] ?? '') !== '')
                        <button
                            class="px-3 py-1 text-xs rounded-full border hover:bg-gray-100"
                            wire:click="$set('filters.estado','')"
                            title="Limpiar filtro Estado"
                        >
                            Limpiar estado
                        </button>
                    @endif
                </div>
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

        {{-- TABLA con el MISMO frame que Diseños --}}
        @php
            $arrow = function(string $field) use ($sortField, $sortDir) {
                if ($sortField !== $field) return '⇵';
                return $sortDir === 'asc' ? '▲' : '▼';
            };
        @endphp

        <div class="overflow-x-auto bg-white rounded-lg shadow min-h-64 pb-8">
            <table class="w-full table-auto border-collapse border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        {{-- ID --}}
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 align-top">
                            <div class="flex items-center justify-between gap-2 min-w-[10rem]">
                                <button
                                    class="inline-flex items-center gap-1 hover:text-blue-600"
                                    wire:click="sortBy('tareas.id')"
                                    title="Ordenar por ID"
                                >
                                    <span>ID</span>
                                    <span class="text-xs">{!! $arrow('tareas.id') !!}</span>
                                </button>

                                {{-- Filtro ID (dropdown teleport) --}}
                                <div x-data="dropdownTeleport()" class="relative shrink-0">
                                    <button x-ref="btn" @click="toggle" class="p-1 rounded hover:bg-gray-200" title="Filtrar ID">⋮</button>
                                    <template x-teleport="body">
                                        <div
                                            x-show="open"
                                            x-transition
                                            @click.outside="close"
                                            :style="style"
                                            class="fixed z-50 w-64 rounded-lg border bg-white shadow p-3"
                                        >
                                            <label class="block text-xs text-gray-600 mb-1">ID Tarea (ej. 100 ó 100,101)</label>
                                            <input
                                                type="text"
                                                class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                placeholder="ID…"
                                                wire:model.live.debounce.400ms="filters.id"
                                            />
                                            <div class="mt-2 flex justify-end gap-2">
                                                <button type="button" class="px-2 py-1 text-xs rounded border"
                                                        @click="$wire.set('filters.id','')">Limpiar</button>
                                                <button type="button" class="px-2 py-1 text-xs rounded border"
                                                        @click="close">Cerrar</button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </th>

                        {{-- ID Proyecto --}}
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 align-top">
                            <div class="flex items-center justify-between gap-2 min-w-[8rem]">
                                <button class="inline-flex items-center gap-1 hover:text-blue-600"
                                        wire:click="sortBy('proyectos.id')" title="Ordenar por ID Proyecto">
                                    <span>ID Proyecto</span>
                                    <span class="text-xs">{!! $arrow('proyectos.id') !!}</span>
                                </button>
                            </div>
                        </th>

                        {{-- Proyecto --}}
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 align-top">
                            <div class="flex items-center justify-between gap-2 min-w-[14rem]">
                                <button class="inline-flex items-center gap-1 hover:text-blue-600"
                                        wire:click="sortBy('proyectos.nombre')" title="Ordenar por Proyecto">
                                    <span>Proyecto</span>
                                    <span class="text-xs">{!! $arrow('proyectos.nombre') !!}</span>
                                </button>
                            </div>
                        </th>

                        {{-- Asignado --}}
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 align-top">
                            <div class="flex items-center justify-between gap-2 min-w-[12rem]">
                                <button class="inline-flex items-center gap-1 hover:text-blue-600"
                                        wire:click="sortBy('users.name')" title="Ordenar por Asignado">
                                    <span>Asignado a</span>
                                    <span class="text-xs">{!! $arrow('users.name') !!}</span>
                                </button>
                            </div>
                        </th>

                        {{-- Descripción --}}
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 align-top">
                            <div class="flex items-center justify-between gap-2 min-w-[14rem]">
                                <span>Descripción</span>
                                <div x-data="{ open:false }" class="relative shrink-0">
                                    <button @click="open = !open" class="p-1 rounded hover:bg-gray-200" title="Filtrar Descripción">⋮</button>
                                    <div
                                        x-cloak x-show="open" @click.away="open=false" x-transition
                                        class="absolute right-0 z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3"
                                    >
                                        <label class="block text-xs text-gray-600 mb-1">Texto contiene</label>
                                        <input
                                            type="text"
                                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                            placeholder="Buscar…"
                                            wire:model.live.debounce.400ms="filters.descripcion"
                                        />
                                        <div class="mt-2 flex justify-end gap-2">
                                            <button type="button" class="px-2 py-1 text-xs rounded border"
                                                    @click="$wire.set('filters.descripcion','')">Limpiar</button>
                                            <button type="button" class="px-2 py-1 text-xs rounded border"
                                                    @click="open=false">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </th>

                        {{-- Estado --}}
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 align-top">
                            <div class="flex items-center gap-2 min-w-[12rem]">
                                <button class="inline-flex items-center gap-1 hover:text-blue-600"
                                        wire:click="sortBy('tareas.estado')" title="Ordenar por Estado">
                                    <span>Estado</span>
                                    <span class="text-xs">{!! $arrow('tareas.estado') !!}</span>
                                </button>

                                <div x-data="{ open:false }" class="relative">
                                    <button @click="open = !open" class="p-1 rounded hover:bg-gray-200" title="Filtrar Estado">⋮</button>
                                    <div
                                        x-cloak x-show="open" @click.away="open=false" x-transition
                                        class="absolute z-50 mt-1 w-60 rounded-lg border bg-white shadow p-3"
                                    >
                                        <label class="block text-xs text-gray-600 mb-1">Estado</label>
                                        <select
                                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                            wire:model.live.debounce.400ms="filters.estado"
                                        >
                                            <option value="">Todos</option>
                                            @foreach($statuses as $est)
                                                <option value="{{ $est }}">{{ $est }}</option>
                                            @endforeach
                                        </select>
                                        <div class="mt-2 flex justify-end gap-2">
                                            <button type="button" class="px-2 py-1 text-xs rounded border"
                                                    @click="$wire.set('filters.estado','')">Limpiar</button>
                                            <button type="button" class="px-2 py-1 text-xs rounded border"
                                                    @click="open=false">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </th>

                        {{-- Acciones --}}
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($tasks as $task)
                        @php
                            $estado = strtoupper($task->estado ?? 'PENDIENTE');
                            $clases = [
                                'PENDIENTE'   => 'bg-yellow-100 text-yellow-800 ring-yellow-600/20',
                                'EN PROCESO'  => 'bg-blue-100 text-blue-800 ring-blue-600/20',
                                'COMPLETADA'  => 'bg-emerald-100 text-emerald-800 ring-emerald-600/20',
                                'RECHAZADO'   => 'bg-rose-100 text-rose-800 ring-rose-600/20',
                                'CANCELADO'   => 'bg-gray-100 text-gray-800 ring-gray-600/20',
                            ];
                            $badge = $clases[$estado] ?? 'bg-gray-100 text-gray-800 ring-gray-600/20';
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 text-sm">{{ $task->id }}</td>
                            <td class="px-3 py-2 text-sm">{{ $task->proyecto->id ?? '—' }}</td>
                            <td class="px-3 py-2 text-sm">{{ $task->proyecto->nombre ?? 'Sin proyecto' }}</td>
                            <td class="px-3 py-2 text-sm">{{ $task->staff->name ?? 'No asignado' }}</td>
                            <td class="px-3 py-2 text-sm">{{ $task->descripcion }}</td>
                            <td class="px-3 py-2 text-sm">
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-semibold ring-1 ring-inset {{ $badge }}">
                                    <span class="h-1.5 w-1.5 rounded-full
                                        @if($estado==='PENDIENTE') bg-yellow-500
                                        @elseif($estado==='EN PROCESO') bg-blue-500
                                        @elseif($estado==='COMPLETADA') bg-emerald-500
                                        @elseif($estado==='RECHAZADO') bg-rose-500
                                        @else bg-gray-500 @endif">
                                    </span>
                                    {{ $estado }}
                                </span>
                            </td>

                            <td class="px-3 py-2 text-sm">
                                <x-dropdown>
                                    @can('admin-disenio-cambiar-estado-tarea')
                                        <x-dropdown.item separator>
                                            <b wire:click="abrirModal({{ $task->id }})"
                                               wire:loading.attr="disabled">
                                                Cambiar Estado
                                            </b>
                                        </x-dropdown.item>
                                    @endcan

                                    <x-dropdown.item separator>
                                        <b wire:click="verificarProceso({{ $task->proyecto->id }})">
                                            Ver detalles
                                        </b>
                                    </x-dropdown.item>
                                </x-dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                                No hay tareas para mostrar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="mt-4">
            {{ $tasks->links() }}
        </div>
    </div>

    {{-- Modal: Cambiar estado (igual lo puedes dejar tal cual) --}}
    @if($modalOpen)
        <div class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
            <div class="bg-white rounded shadow-lg w-full max-w-md p-6">
                <h2 class="text-lg font-semibold mb-4">Actualizar Estado</h2>

                <label class="block text-sm font-medium text-gray-700">Nuevo Estado</label>
                <select wire:model.live="newStatus" class="w-full p-2 border rounded mb-3">
                    @foreach($statuses as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>

                @error('newStatus')
                    <div class="bg-red-100 text-red-800 p-3 rounded mb-3">{{ $message }}</div>
                @enderror

                <div class="flex justify-end space-x-2">
                    <button wire:click="cerrarModal" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded">
                        Cancelar
                    </button>
                    <button wire:click="actualizarEstado" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Scripts: DOMContentLoaded + dropdownTeleport (igual que Diseños) --}}
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
            const top  = r.bottom + gap;

            const vw = window.innerWidth;
            const margin = 8;
            if (left < margin) left = margin;
            if (left + panelW > vw - margin) left = vw - margin - panelW;

            this.style = `top:${top}px;left:${left}px`;
        }
    });

    // Si quieres disparar acciones desde la UI usando dispatch (en lugar de emit)
    window.addEventListener('abrir-modal-estado', e => {
        const id = e.detail?.id;
        if (id) $wire.abrirModal(id);
    });

    window.addEventListener('verificar-proceso', e => {
        const id = e.detail?.id;
        if (id) $wire.verificarProceso(id);
    });
});
</script>

<div
    x-data="{
        abierto: JSON.parse(localStorage.getItem('dashboard_admintareasdisenio_abierto') ?? 'true'),
        toggle() {
            this.abierto = !this.abierto;
            localStorage.setItem('dashboard_admintareasdisenio_abierto', JSON.stringify(this.abierto));
        }
    }"
    class="p-2 sm:p-3 h-full min-h-0 flex flex-col"
>
    <h2
        @click="toggle()"
        class="text-xl font-bold mb-4 border-b border-gray-300 pb-2 cursor-pointer hover:text-blue-600 transition"
    >
        Administración de Tareas
        <span class="text-sm text-gray-500 ml-2" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
    </h2>

    <div x-show="abierto" x-transition class="min-h-0">
        @if (session()->has('message'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-3">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 text-red-800 p-3 rounded mb-3">
                {{ session('error') }}
            </div>
        @endif

        {{-- Tabs --}}
        <ul class="flex flex-wrap border-b border-gray-200 mb-4 gap-1">
            @foreach ($tabs as $tab)
                <li>
                    <button
                        wire:click="setTab('{{ $tab }}')"
                        @class([
                            'px-4 py-2 rounded-t-lg text-sm whitespace-nowrap transition',
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

        {{-- top bar --}}


        @php
            $arrow = function(string $field) use ($sortField, $sortDir) {
                if ($sortField !== $field) return '⇵';
                return $sortDir === 'asc' ? '▲' : '▼';
            };

            $coloresEstadoTarea = [
                'PENDIENTE'   => 'bg-yellow-400 text-black',
                'EN PROCESO'  => 'bg-blue-500 text-white',
                'COMPLETADA'  => 'bg-emerald-600 text-white',
                'RECHAZADO'   => 'bg-red-600 text-white',
                'CANCELADO'   => 'bg-gray-500 text-white',
            ];
        @endphp

        <div class="w-full px-2 sm:px-3">
            <div class="overflow-x-auto bg-white rounded-lg shadow min-h-64 pb-8">
                <table class="w-full table-auto border-collapse border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            {{-- ID --}}
                            <th class="w-[5.5rem] min-w-[5.5rem] max-w-[5.5rem] px-2 py-2 text-left text-sm font-medium text-gray-600 align-top">
                                <div class="flex items-center justify-between gap-1 w-full">
                                    <button
                                        class="inline-flex items-center gap-1 hover:text-blue-600"
                                        wire:click="sortBy('id')"
                                        title="Ordenar por ID"
                                    >
                                        <span>ID</span>
                                        <span class="text-xs">{!! $arrow('id') !!}</span>
                                    </button>

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
                                                    <label class="block text-xs text-gray-600 mb-1">ID tarea</label>
                                                    <input
                                                        type="text"
                                                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                        placeholder="Ej. 1 o 1,2,3"
                                                        wire:model.live.debounce.400ms="filters.id"
                                                    />
                                                </div>

                                                <div class="pt-1 flex justify-end gap-2">
                                                    <button
                                                        type="button"
                                                        class="px-2 py-1 text-xs rounded border"
                                                        @click="$wire.set('filters.id','')"
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

                            {{-- ID Proyecto --}}
                            <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 align-top">
                                <div class="flex items-center justify-between gap-2 min-w-[10rem]">
                                    <button
                                        class="inline-flex items-center gap-1 hover:text-blue-600"
                                        wire:click="sortBy('proyecto_id')"
                                        title="Ordenar por ID proyecto"
                                    >
                                        <span>ID proyecto</span>
                                        <span class="text-xs">{!! $arrow('proyecto_id') !!}</span>
                                    </button>

                                    <div x-data="{ open:false }" class="relative shrink-0">
                                        <button
                                            @click="open = !open"
                                            class="p-1 rounded hover:bg-gray-200"
                                            title="Filtrar ID proyecto"
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
                                            <label class="block text-xs text-gray-600 mb-1">ID proyecto</label>
                                            <input
                                                type="text"
                                                class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                placeholder="Ej. 10 o 10,11"
                                                wire:model.live.debounce.400ms="filters.proyecto_id"
                                            />
                                            <div class="mt-2 flex justify-end gap-2">
                                                <button
                                                    type="button"
                                                    class="px-2 py-1 text-xs rounded border"
                                                    @click="$wire.set('filters.proyecto_id','')"
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

                            {{-- Proyecto --}}
                            <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 align-top">
                                <div class="flex items-center justify-between gap-2 min-w-[14rem]">
                                    <button
                                        class="inline-flex items-center gap-1 hover:text-blue-600"
                                        wire:click="sortBy('proyecto_nombre')"
                                        title="Ordenar por proyecto"
                                    >
                                        <span>Proyecto</span>
                                        <span class="text-xs">{!! $arrow('proyecto_nombre') !!}</span>
                                    </button>

                                    <div x-data="{ open:false }" class="relative shrink-0">
                                        <button
                                            @click="open = !open"
                                            class="p-1 rounded hover:bg-gray-200"
                                            title="Filtrar proyecto"
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
                                            <label class="block text-xs text-gray-600 mb-1">Nombre del proyecto</label>
                                            <input
                                                type="text"
                                                class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                placeholder="Buscar proyecto..."
                                                wire:model.live.debounce.400ms="filters.proyecto"
                                            />
                                            <div class="mt-2 flex justify-end gap-2">
                                                <button
                                                    type="button"
                                                    class="px-2 py-1 text-xs rounded border"
                                                    @click="$wire.set('filters.proyecto','')"
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

                            {{-- Usuario --}}
                            @if($puedeVerTodasLasTareas)
                                <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 align-top">
                                    <div class="flex items-center justify-between gap-2 min-w-[12rem]">
                                        <span>Cliente</span>

                                        <div x-data="{ open:false }" class="relative shrink-0">
                                            <button
                                                @click="open = !open"
                                                class="p-1 rounded hover:bg-gray-200"
                                                title="Filtrar usuario"
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
                                                <label class="block text-xs text-gray-600 mb-1">Nombre o correo</label>
                                                <input
                                                    type="text"
                                                    class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                    placeholder="Usuario..."
                                                    wire:model.live.debounce.400ms="filters.usuario"
                                                />
                                                <div class="mt-2 flex justify-end gap-2">
                                                    <button
                                                        type="button"
                                                        class="px-2 py-1 text-xs rounded border"
                                                        @click="$wire.set('filters.usuario','')"
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
                            @endif

                            {{-- Asignado --}}
                            <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 align-top">
                                <div class="flex items-center justify-between gap-2 min-w-[12rem]">
                                    <button
                                        class="inline-flex items-center gap-1 hover:text-blue-600"
                                        wire:click="sortBy('asignado')"
                                        title="Ordenar por asignado"
                                    >
                                        <span>Asignado a</span>
                                        <span class="text-xs">{!! $arrow('asignado') !!}</span>
                                    </button>

                                    <div x-data="{ open:false }" class="relative shrink-0">
                                        <button
                                            @click="open = !open"
                                            class="p-1 rounded hover:bg-gray-200"
                                            title="Filtrar asignado"
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
                                            <label class="block text-xs text-gray-600 mb-1">Nombre o correo</label>
                                            <input
                                                type="text"
                                                class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                placeholder="Asignado a..."
                                                wire:model.live.debounce.400ms="filters.asignado"
                                            />
                                            <div class="mt-2 flex justify-end gap-2">
                                                <button
                                                    type="button"
                                                    class="px-2 py-1 text-xs rounded border"
                                                    @click="$wire.set('filters.asignado','')"
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

                            {{-- Tipo --}}
                            <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 align-top">
                                <div class="flex items-center justify-between gap-2 min-w-[10rem]">
                                    <button
                                        class="inline-flex items-center gap-1 hover:text-blue-600"
                                        wire:click="sortBy('tipo')"
                                        title="Ordenar por tipo"
                                    >
                                        <span>Tipo</span>
                                        <span class="text-xs">{!! $arrow('tipo') !!}</span>
                                    </button>

                                    <div x-data="{ open:false }" class="relative shrink-0">
                                        <button
                                            @click="open = !open"
                                            class="p-1 rounded hover:bg-gray-200"
                                            title="Filtrar tipo"
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
                                            <label class="block text-xs text-gray-600 mb-1">Tipo</label>
                                            <input
                                                type="text"
                                                class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                placeholder="Tipo..."
                                                wire:model.live.debounce.400ms="filters.tipo"
                                            />
                                            <div class="mt-2 flex justify-end gap-2">
                                                <button
                                                    type="button"
                                                    class="px-2 py-1 text-xs rounded border"
                                                    @click="$wire.set('filters.tipo','')"
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

                            {{-- Descripción --}}
                            <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 align-top">
                                <div class="flex items-center justify-between gap-2 min-w-[16rem]">
                                    <button
                                        class="inline-flex items-center gap-1 hover:text-blue-600"
                                        wire:click="sortBy('descripcion')"
                                        title="Ordenar por descripción"
                                    >
                                        <span>Descripción de tarea</span>
                                        <span class="text-xs">{!! $arrow('descripcion') !!}</span>
                                    </button>
                                </div>
                            </th>

                            {{-- Estado --}}
                            <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 align-top">
                                <div class="flex items-center justify-between gap-2 min-w-[11rem]">
                                    <button
                                        class="inline-flex items-center gap-1 hover:text-blue-600"
                                        wire:click="sortBy('estado')"
                                        title="Ordenar por estado"
                                    >
                                        <span>Estado de ka tarea</span>
                                        <span class="text-xs">{!! $arrow('estado') !!}</span>
                                    </button>

                                    <div x-data="{ open:false }" class="relative shrink-0">
                                        <button
                                            @click="open = !open"
                                            class="p-1 rounded hover:bg-gray-200"
                                            title="Filtrar estado"
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
                                            <label class="block text-xs text-gray-600 mb-1">Estado</label>
                                            <select
                                                class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                wire:model.live.debounce.400ms="filters.estado"
                                            >
                                                <option value="">Todos</option>
                                                @foreach($statuses as $status)
                                                    <option value="{{ $status }}">{{ $status }}</option>
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
                        @forelse($tasks as $task)
                            @php
                                $estado = strtoupper($task->estado ?? 'PENDIENTE');
                                $badge = $coloresEstadoTarea[$estado] ?? 'bg-gray-300 text-gray-700';
                            @endphp

                            <tr class="hover:bg-gray-50">
                                <td class="w-[5.5rem] min-w-[5.5rem] max-w-[5.5rem] px-2 py-2 text-sm font-semibold text-gray-700 whitespace-nowrap">
                                    {{ $task->id }}
                                </td>

                                <td class="px-3 py-2 text-sm text-gray-700">
                                    @if($task->proyecto)
                                        <a
                                            href="{{ route('proyecto.show', $task->proyecto->id) }}"
                                            class="text-blue-600 hover:underline font-medium"
                                        >
                                            {{ $task->proyecto->id }}
                                        </a>
                                    @else
                                        <span class="text-gray-500">Sin proyecto</span>
                                    @endif
                                </td>

                                <td class="px-3 py-2 text-sm text-gray-700">
                                    {{ $task->proyecto->nombre ?? 'Sin proyecto' }}
                                </td>

                                @if($puedeVerTodasLasTareas)
                                    <td class="px-3 py-2 text-sm text-gray-700">
                                        {{ $task->proyecto->user->name ?? 'Sin usuario' }}
                                    </td>
                                @endif

                                <td class="px-3 py-2 text-sm text-gray-700">
                                    {{ $task->staff->name ?? 'No asignado' }}
                                </td>

                                <td class="px-3 py-2 text-sm text-gray-700 whitespace-nowrap">
                                    {{ $task->tipo ?? 'Sin tipo' }}
                                </td>

                                <td class="px-3 py-2 text-sm text-gray-700 max-w-[24rem]">
                                    <div class="break-words whitespace-normal">
                                        {{ $task->descripcion ?: 'Sin descripción' }}
                                    </div>
                                </td>

                                <td class="px-3 py-2 text-sm whitespace-nowrap min-w-[10rem]">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap min-w-[10rem] justify-center {{ $badge }}">
                                        {{ $estado }}
                                    </span>
                                </td>

                                <td class="px-3 py-2 text-sm">
                                    <x-dropdown>
                                        @can('ir-detalles-tarea')
                                            @if($task->proyecto)
                                                <x-dropdown.item
                                                    wire:click="verificarProceso({{ $task->proyecto->id }})"
                                                    label="Ver detalles"
                                                />
                                            @endif
                                        @endcan

                                        @can('admin-disenio-cambiar-estado-tarea')
                                            <x-dropdown.item
                                                separator
                                                wire:click="abrirModal({{ $task->id }})"
                                                label="Cambiar Estado"
                                            />
                                        @endcan
                                    </x-dropdown>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="{{ $puedeVerTodasLasTareas ? 9 : 8 }}"
                                    class="px-4 py-6 text-center text-sm text-gray-500"
                                >
                                    No hay tareas disponibles.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $tasks->links() }}
            </div>
        </div>

        @if($modalOpen)
            <div class="fixed inset-0 flex items-center justify-center bg-black/50 z-50 px-4">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <h2 class="text-lg font-semibold mb-4">Actualizar Estado</h2>

                    <label class="block text-sm font-medium text-gray-700 mb-1">Nuevo Estado</label>
                    <select wire:model.live="newStatus" class="w-full p-2 border rounded-lg mb-3">
                        @foreach($statuses as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>

                    @error('newStatus')
                        <div class="bg-red-100 text-red-800 p-3 rounded mb-3">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="flex flex-col sm:flex-row justify-end gap-2">
                        <button
                            wire:click="cerrarModal"
                            class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded-lg"
                        >
                            Cancelar
                        </button>
                        <button
                            wire:click="actualizarEstado"
                            class="w-full sm:w-auto bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg"
                        >
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if($mostrarModalConfirmacion)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
                <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-lg">
                    <h2 class="text-lg font-semibold mb-2">
                        Confirmar inicio de proceso
                    </h2>

                    <p class="text-sm text-gray-700">
                        Esta acción marcará el proyecto como <b>EN PROCESO</b> y notificará a los responsables.
                    </p>

                    <div class="mt-4 rounded-lg border bg-gray-50 p-3 text-sm">
                        <div>
                            <span class="text-gray-500">Proyecto:</span>
                            <b>{{ $proyectoPendienteConfirmacion->nombre ?? '—' }}</b>
                        </div>
                        <div>
                            <span class="text-gray-500">ID:</span>
                            <b>{{ $proyectoPendienteConfirmacion->id ?? '—' }}</b>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end sm:gap-3">
                        <button
                            wire:click="cancelarConfirmacion"
                            class="w-full sm:w-auto rounded-lg bg-gray-500 px-4 py-2 text-white hover:bg-gray-600"
                        >
                            Cancelar
                        </button>
                        <button
                            wire:click="confirmarInicioProceso"
                            class="w-full sm:w-auto rounded-lg bg-blue-500 px-4 py-2 text-white hover:bg-blue-600"
                        >
                            Confirmar
                        </button>
                    </div>
                </div>
            </div>
        @endif
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
                close() {
                    this.open = false;
                },
                reposition() {
                    const btn = this.$refs.btn;
                    if (!btn) return;

                    const r = btn.getBoundingClientRect();
                    const panelW = 256;
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

            window.addEventListener('abrir-modal-estado', e => {
                const id = e.detail?.id;
                if (id && window.Livewire) {
                    window.Livewire.dispatch('abrir-modal-estado-interno', { id });
                }
            });
        });
    </script>
</div>
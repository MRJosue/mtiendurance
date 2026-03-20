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
        class="text-lg sm:text-xl font-bold mb-4 border-b border-gray-300 pb-2 cursor-pointer hover:text-blue-600 transition"
    >
        Administración de Tareas
        <span class="text-xs sm:text-sm text-gray-500 ml-2" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
    </h2>

    <div x-show="abierto" x-transition>
        @if (session()->has('message'))
            <div class="bg-green-100 text-green-800 p-3 rounded-lg mb-3 text-sm">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 text-red-800 p-3 rounded-lg mb-3 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @php
            $arrow = function (string $field) use ($sortField, $sortDir) {
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

        {{-- Tabs --}}
        <div class="mb-4">
            <div class="flex flex-wrap gap-2">
                @foreach ($tabs as $tab)
                    <button
                        type="button"
                        wire:key="tab-{{ md5($tab) }}"
                        wire:click.prevent="setTab('{{ $tab }}')"
                        class="px-3 py-1.5 rounded-lg text-xs sm:text-sm font-medium border transition disabled:opacity-50"
                        @if($activeTab === $tab)
                            style="background-color:#2563eb;color:white;border-color:#2563eb;"
                        @else
                            style="background-color:white;color:#4b5563;border-color:#d1d5db;"
                        @endif
                    >
                        {{ $tab }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Top bar --}}
        <div class="mb-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2">
                <button
                    type="button"
                    wire:click="clearFilters"
                    class="w-full sm:w-auto px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 border text-sm"
                >
                    Limpiar filtros
                </button>

                <div class="inline-flex items-center gap-2 text-sm text-gray-600">
                    <span class="font-medium">Total:</span>
                    <span class="px-2 py-1 bg-gray-100 rounded-lg">{{ $tasks->total() }}</span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                <label class="text-sm font-medium text-gray-700 whitespace-nowrap">
                    Registros por página
                </label>

                <select
                    wire:model.live="perPage"
                    class="w-full sm:w-28 rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                >
                    @foreach($perPageOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-xs sm:text-sm">
                    <thead class="bg-gray-100">
                        <tr class="text-gray-600">
                            {{-- ID --}}
                            <th class="w-[58px] px-2 py-2 text-left font-semibold whitespace-nowrap">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1 hover:text-blue-600"
                                    wire:click="sortBy('id')"
                                >
                                    <span>ID</span>
                                    <span class="text-[10px]">{!! $arrow('id') !!}</span>
                                </button>
                            </th>

                            {{-- Proyecto ID --}}
                            <th class="w-[72px] px-2 py-2 text-left font-semibold whitespace-nowrap">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1 hover:text-blue-600"
                                    wire:click="sortBy('proyecto_id')"
                                >
                                    <span>Proy.</span>
                                    <span class="text-[10px]">{!! $arrow('proyecto_id') !!}</span>
                                </button>
                            </th>

                            {{-- Proyecto --}}
                            <th class="min-w-[170px] px-2 py-2 text-left font-semibold">
                                Proyecto
                            </th>

                            {{-- Cliente --}}
                            @if($puedeVerTodasLasTareas)
                                <th class="min-w-[140px] px-2 py-2 text-left font-semibold">
                                    Cliente
                                </th>
                            @endif

                            {{-- Asignado --}}
                            <th class="min-w-[140px] px-2 py-2 text-left font-semibold">
                                Asignado
                            </th>

                            {{-- Tipo --}}
                            <th class="w-[110px] px-2 py-2 text-left font-semibold whitespace-nowrap">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1 hover:text-blue-600"
                                    wire:click="sortBy('tipo')"
                                >
                                    <span>Tipo</span>
                                    <span class="text-[10px]">{!! $arrow('tipo') !!}</span>
                                </button>
                            </th>

                            {{-- Descripción --}}
                            <th class="min-w-[220px] px-2 py-2 text-left font-semibold">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1 hover:text-blue-600"
                                    wire:click="sortBy('descripcion')"
                                >
                                    <span>Descripción</span>
                                    <span class="text-[10px]">{!! $arrow('descripcion') !!}</span>
                                </button>
                            </th>

                            {{-- Estado --}}
                            <th class="w-[120px] px-2 py-2 text-left font-semibold whitespace-nowrap">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1 hover:text-blue-600"
                                    wire:click="sortBy('estado')"
                                >
                                    <span>Estado</span>
                                    <span class="text-[10px]">{!! $arrow('estado') !!}</span>
                                </button>
                            </th>

                            {{-- Acciones --}}
                            <th class="w-[78px] px-2 py-2 text-left font-semibold whitespace-nowrap">
                                Acciones
                            </th>
                        </tr>

                        {{-- Filtros compactos --}}
                        <tr class="bg-white border-t border-gray-200">
                            <th class="px-2 py-2">
                                <input
                                    type="text"
                                    wire:model.live.debounce.400ms="filters.id"
                                    placeholder="ID"
                                    class="w-full rounded-lg border-gray-300 text-[11px] px-2 py-1"
                                />
                            </th>

                            <th class="px-2 py-2">
                                <input
                                    type="text"
                                    wire:model.live.debounce.400ms="filters.proyecto_id"
                                    placeholder="ID"
                                    class="w-full rounded-lg border-gray-300 text-[11px] px-2 py-1"
                                />
                            </th>

                            <th class="px-2 py-2">
                                <input
                                    type="text"
                                    wire:model.live.debounce.400ms="filters.proyecto"
                                    placeholder="Proyecto..."
                                    class="w-full rounded-lg border-gray-300 text-[11px] px-2 py-1"
                                />
                            </th>

                            @if($puedeVerTodasLasTareas)
                                <th class="px-2 py-2">
                                    <input
                                        type="text"
                                        wire:model.live.debounce.400ms="filters.usuario"
                                        placeholder="Cliente..."
                                        class="w-full rounded-lg border-gray-300 text-[11px] px-2 py-1"
                                    />
                                </th>
                            @endif

                            <th class="px-2 py-2">
                                <input
                                    type="text"
                                    wire:model.live.debounce.400ms="filters.asignado"
                                    placeholder="Asignado..."
                                    class="w-full rounded-lg border-gray-300 text-[11px] px-2 py-1"
                                />
                            </th>

                            <th class="px-2 py-2">
                                <input
                                    type="text"
                                    wire:model.live.debounce.400ms="filters.tipo"
                                    placeholder="Tipo..."
                                    class="w-full rounded-lg border-gray-300 text-[11px] px-2 py-1"
                                />
                            </th>

                            <th class="px-2 py-2">
                                <div class="text-[11px] text-gray-400 px-1">—</div>
                            </th>

                            <th class="px-2 py-2">
                                <select
                                    wire:model.live.debounce.400ms="filters.estado"
                                    class="w-full rounded-lg border-gray-300 text-[11px] px-2 py-1"
                                >
                                    <option value="">Todos</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}">{{ $status }}</option>
                                    @endforeach
                                </select>
                            </th>

                            <th class="px-2 py-2">
                                <div class="text-[11px] text-gray-400 px-1">—</div>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($tasks as $task)
                            @php
                                $estado = strtoupper($task->estado ?? 'PENDIENTE');
                                $badge = $coloresEstadoTarea[$estado] ?? 'bg-gray-300 text-gray-700';
                            @endphp

                            <tr class="border-t border-gray-100 hover:bg-gray-50 align-top">
                                <td class="px-2 py-2 font-semibold text-gray-700 whitespace-nowrap">
                                    {{ $task->id }}
                                </td>

                                <td class="px-2 py-2 text-gray-700 whitespace-nowrap">
                                    @if($task->proyecto)
                                        <a
                                            href="{{ route('proyecto.show', $task->proyecto->id) }}"
                                            class="text-blue-600 hover:underline font-medium"
                                        >
                                            {{ $task->proyecto->id }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">S/P</span>
                                    @endif
                                </td>

                                <td class="px-2 py-2 text-gray-700">
                                    <div class="truncate max-w-[220px]" title="{{ $task->proyecto->nombre ?? 'Sin proyecto' }}">
                                        {{ $task->proyecto->nombre ?? 'Sin proyecto' }}
                                    </div>
                                </td>

                                @if($puedeVerTodasLasTareas)
                                    <td class="px-2 py-2 text-gray-700">
                                        <div class="truncate max-w-[180px]" title="{{ $task->proyecto->user->name ?? 'Sin usuario' }}">
                                            {{ $task->proyecto->user->name ?? 'Sin usuario' }}
                                        </div>
                                    </td>
                                @endif

                                <td class="px-2 py-2 text-gray-700">
                                    <div class="truncate max-w-[180px]" title="{{ $task->staff->name ?? 'No asignado' }}">
                                        {{ $task->staff->name ?? 'No asignado' }}
                                    </div>
                                </td>

                                <td class="px-2 py-2 text-gray-700 whitespace-nowrap">
                                    {{ $task->tipo ?? 'Sin tipo' }}
                                </td>

                                <td class="px-2 py-2 text-gray-700">
                                    <div class="line-clamp-2 break-words max-w-[260px]" title="{{ $task->descripcion ?: 'Sin descripción' }}">
                                        {{ $task->descripcion ?: 'Sin descripción' }}
                                    </div>
                                </td>

                                <td class="px-2 py-2">
                                    <span class="inline-flex items-center justify-center px-2 py-1 rounded-full text-[10px] sm:text-[11px] font-semibold whitespace-nowrap {{ $badge }}">
                                        {{ $estado }}
                                    </span>
                                </td>

                                <td class="px-2 py-2">
                                    <x-dropdown>
                                        @can('ir-detalles-tarea')
                                            @if($task->proyecto)
                                                <x-dropdown.item
                                                    wire:click="verificarProceso({{ $task->id }})"
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
        </div>

        <div class="mt-4">
            {{ $tasks->links() }}
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
                        <div class="bg-red-100 text-red-800 p-3 rounded mb-3 text-sm">
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
            window.addEventListener('abrir-modal-estado', e => {
                const id = e.detail?.id;
                if (id && window.Livewire) {
                    window.Livewire.dispatch('abrir-modal-estado-interno', { id });
                }
            });
        });
    </script>
</div>
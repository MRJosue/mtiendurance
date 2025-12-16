<div
    x-data="{
        abierto: JSON.parse(localStorage.getItem('transferencias_proyecto_abierto') ?? 'true'),
        selected: @entangle('selectedTransferencias'),
        toggle() {
            this.abierto = !this.abierto;
            localStorage.setItem('transferencias_proyecto_abierto', JSON.stringify(this.abierto));
        }
    }"
    class="p-2 sm:p-3 h-full min-h-0 flex flex-col"
>
    <h2
        @click="toggle()"
        class="text-xl font-bold mb-4 border-b border-gray-300 pb-2 cursor-pointer hover:text-blue-600 transition"
    >
        Transferencias de Propietario
        <span class="text-sm text-gray-500 ml-2" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
    </h2>

    <div x-show="abierto" x-transition class="min-h-0 flex flex-col">

        {{-- Tabs --}}
        <ul class="flex flex-wrap border-b border-gray-200 mb-4 gap-1">
            @foreach ($this->tabs as $tab)
                <li>
                    <button
                        type="button"
                        wire:click="setTab('{{ $tab }}')"
                        @class([
                            'px-4 py-2 rounded-t-lg text-sm whitespace-nowrap',
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

        {{-- Acciones / PerPage --}}
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                {{-- filtros rápidos --}}
                <input
                    type="text"
                    class="w-full sm:w-40 rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="ID transferencia…"
                    wire:model.live.debounce.400ms="filters.id"
                />

                <input
                    type="text"
                    class="w-full sm:w-40 rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Proyecto ID…"
                    wire:model.live.debounce.400ms="filters.proyecto_id"
                />

                <input
                    type="text"
                    class="w-full sm:w-40 rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Owner actual ID…"
                    wire:model.live.debounce.400ms="filters.owner_actual_id"
                />

                <input
                    type="text"
                    class="w-full sm:w-40 rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Owner nuevo ID…"
                    wire:model.live.debounce.400ms="filters.owner_nuevo_id"
                />

                <button
                    type="button"
                    class="px-3 py-2 rounded-lg border text-sm hover:bg-gray-50"
                    wire:click="$set('filters', {id:'',proyecto_id:'',owner_actual_id:'',owner_nuevo_id:''})"
                >
                    Limpiar
                </button>

            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    class="w-full sm:w-auto px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                    :disabled="(selected || []).length === 0"
                    wire:click="cancelarSeleccionadas"
                >
                    Cancelar seleccionadas
                </button>

                <button
                    type="button"
                    class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                    :disabled="(selected || []).length === 0"
                    wire:click="aplicarSeleccionadas"
                >
                    Aplicar seleccionadas
                </button>
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

            $badgeEstado = [
                'PENDIENTE' => 'bg-yellow-100 text-yellow-800',
                'APROBADO'  => 'bg-green-100 text-green-800',
                'APLICADO'  => 'bg-blue-100 text-blue-800',
                'CANCELADO' => 'bg-gray-200 text-gray-800',
            ];
        @endphp

        {{-- Tabla --}}
        <div class="overflow-x-auto bg-white rounded-lg shadow min-h-64 pb-4">
            <table class="w-full table-auto border-collapse border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                            <input
                                type="checkbox"
                                wire:model="selectAll"
                                @change="selected = $event.target.checked ? @js($pageSelectableIds) : []"
                            />
                        </th>
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                            <button class="inline-flex items-center gap-1 hover:text-blue-600" wire:click="sortBy('id')">
                                ID <span class="text-xs">{!! $arrow('id') !!}</span>
                            </button>
                        </th>

                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                            <button class="inline-flex items-center gap-1 hover:text-blue-600" wire:click="sortBy('proyecto_id')">
                                Proyecto <span class="text-xs">{!! $arrow('proyecto_id') !!}</span>
                            </button>
                        </th>

                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                            <button class="inline-flex items-center gap-1 hover:text-blue-600" wire:click="sortBy('estado')">
                                Estado <span class="text-xs">{!! $arrow('estado') !!}</span>
                            </button>
                        </th>

                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Owner actual</th>
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Owner nuevo</th>
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Solicitado por</th>

                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                            <button class="inline-flex items-center gap-1 hover:text-blue-600" wire:click="sortBy('created_at')">
                                Creado <span class="text-xs">{!! $arrow('created_at') !!}</span>
                            </button>
                        </th>



                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                            <button class="inline-flex items-center gap-1 hover:text-blue-600" wire:click="sortBy('applied_at')">
                                Aplicado <span class="text-xs">{!! $arrow('applied_at') !!}</span>
                            </button>
                        </th>
                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                            Acciones
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($transferencias as $t)
                        <tr class="hover:bg-gray-50">

                            <td class="px-3 py-2 text-sm">
                                @if($t->estado === 'PENDIENTE')
                                    <input type="checkbox" wire:model="selectedTransferencias" value="{{ $t->id }}">
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>

                            <td class="px-3 py-2 text-sm font-semibold whitespace-nowrap">
                                #{{ $t->id }}
                            </td>

                            <td class="px-3 py-2 text-sm">
                                <div class="flex flex-col">
                                    <span class="font-semibold">#{{ $t->proyecto_id }}</span>
                                    <span class="text-xs text-gray-500">
                                        {{ $t->proyecto->nombre ?? '—' }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-3 py-2 text-sm whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 rounded text-xs font-semibold {{ $badgeEstado[$t->estado] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ $t->estado }}
                                </span>
                            </td>

                            <td class="px-3 py-2 text-sm">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $t->ownerActual->name ?? '—' }}</span>
                                    <span class="text-xs text-gray-500">{{ $t->ownerActual->email ?? '' }}</span>
                                    <span class="text-xs text-gray-400">ID: {{ $t->owner_actual_id }}</span>
                                </div>
                            </td>

                            <td class="px-3 py-2 text-sm">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $t->ownerNuevo->name ?? '—' }}</span>
                                    <span class="text-xs text-gray-500">{{ $t->ownerNuevo->email ?? '' }}</span>
                                    <span class="text-xs text-gray-400">ID: {{ $t->owner_nuevo_id }}</span>
                                </div>
                            </td>

                            <td class="px-3 py-2 text-sm">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $t->solicitadoPor->name ?? '—' }}</span>
                                    <span class="text-xs text-gray-500">{{ $t->solicitadoPor->email ?? '' }}</span>
                                    <span class="text-xs text-gray-400">ID: {{ $t->solicitado_por_id }}</span>
                                </div>
                            </td>

                            <td class="px-3 py-2 text-sm whitespace-nowrap">
                                {{ optional($t->created_at)->format('Y-m-d H:i') ?? '—' }}
                            </td>



                            <td class="px-3 py-2 text-sm whitespace-nowrap">
                                {{ $t->applied_at ? \Carbon\Carbon::parse($t->applied_at)->format('Y-m-d H:i') : '—' }}
                            </td>
                            <td class="px-3 py-2 text-sm whitespace-nowrap">
                                @if($t->estado === 'PENDIENTE')
                                    <x-dropdown>
                                        <x-dropdown.item>
                                            <button type="button" wire:click="aplicar({{ $t->id }})" class="w-full text-left">
                                                Aplicar transferencia
                                            </button>
                                        </x-dropdown.item>

                                        <x-dropdown.item separator>
                                            <button type="button" wire:click="cancelar({{ $t->id }})" class="w-full text-left">
                                                Cancelar solicitud
                                            </button>
                                        </x-dropdown.item>
                                    </x-dropdown>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-6 text-center text-sm text-gray-500">
                                No hay transferencias para mostrar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="mt-4">
            {{ $transferencias->links() }}
        </div>

    </div>
</div>

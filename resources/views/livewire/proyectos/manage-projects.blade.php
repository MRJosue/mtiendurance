<div 
    x-data="{
        abierto: JSON.parse(localStorage.getItem('dashboard_preproyecto_abierto') ?? 'true'),
        showDeactivate: @entangle('showDeactivateModal'),
        showActivate: @entangle('showActivateModal'),
        toggle() {
            this.abierto = !this.abierto;
            localStorage.setItem('dashboard_preproyecto_abierto', JSON.stringify(this.abierto));
        }
    }"
     class="p-2 sm:p-3 h-full min-h-0 flex flex-col"
>


            <h2 
                @click="toggle()"
                class="text-xl font-bold mb-4 border-b border-gray-300 pb-2 cursor-pointer hover:text-blue-600 transition"
            >
                {{ __('projects.title') }}
                <span class="text-sm text-gray-500 ml-2" x-text="abierto ? @js(__('projects.hide')) : @js(__('projects.show'))"></span>
            </h2>   

            <!-- Contenido del panel -->
            <div x-show="abierto" x-transition>
                <ul class="flex flex-wrap border-b border-gray-200 mb-4 gap-1">
                    @foreach ($this->tabs as $tab)
                        <li>
                            <button
                                wire:click="setTab('{{ $tab }}')"
                                @class([
                                    'px-4 py-2 rounded-t-lg text-sm whitespace-nowrap',
                                    'border-b-2 font-semibold bg-white'           => $activeTab === $tab,
                                    'text-gray-600 hover:text-blue-500'           => $activeTab !== $tab,
                                    'border-blue-500 text-blue-600'               => $activeTab === $tab,
                                    'border-transparent'                          => $activeTab !== $tab,
                                ])
                            >
                                {{ match ($tab) {
                                    'PENDIENTE' => __('projects.status_pending'),
                                    'ASIGNADO' => __('projects.status_assigned'),
                                    'EN PROCESO' => __('projects.status_in_progress'),
                                    'REVISION' => __('projects.status_review'),
                                    'DISEÑO APROBADO' => __('projects.status_design_approved'),
                                    'DISEÑO RECHAZADO' => __('projects.status_design_rejected'),
                                    'RECHAZADO' => __('projects.status_rejected'),
                                    'CANCELADO' => __('projects.status_cancelled'),
                                    'RECONFIGURAR' => __('projects.tab_reconfigure'),
                                    'TODOS' => __('projects.tab_all'),
                                    default => $tab,
                                } }}
                            </button>
                        </li>
                    @endforeach
                </ul>

                    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <!-- Lado izquierdo: (deja lo que ya tengas: botones, chips, etc.) -->
                        <div class="flex flex-wrap items-center gap-2">
                            {{-- ... tus botones/acciones ... --}}

                            {{-- <button
                                type="button"
                                class="w-full sm:w-auto px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700"
                                wire:click="exportExcel"
                                wire:loading.attr="disabled"
                                wire:target="exportExcel"
                            >
                                <span wire:loading.remove wire:target="exportExcel">Exportar Excel</span>
                                <span wire:loading wire:target="exportExcel">Exportando...</span>
                            </button> --}}
                        </div>

                        <!-- Lado derecho: PerPage -->
                        <div class="flex items-center gap-2">
                            <label for="per-page" class="text-sm text-gray-600">{{ __('projects.records_per_page') }}</label>
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

                    <div x-data="{ selectedProjects: @entangle('selectedProjects') }" class="w-full px-2 sm:px-3">

                        {{-- === TABLA estilo hoja-viewer para manage-projects === --}}
                        @php
                            $arrow = function(string $field) use ($sortField, $sortDir) {
                                if ($sortField !== $field) return '⇵';
                                return $sortDir === 'asc' ? '▲' : '▼';
                            };

                            $statusLabel = function (?string $status) {
                                return match ($status) {
                                    'PENDIENTE' => __('projects.status_pending'),
                                    'ASIGNADO' => __('projects.status_assigned'),
                                    'EN PROCESO' => __('projects.status_in_progress'),
                                    'REVISION' => __('projects.status_review'),
                                    'DISEÑO APROBADO' => __('projects.status_design_approved'),
                                    'DISEÑO RECHAZADO' => __('projects.status_design_rejected'),
                                    'RECHAZADO' => __('projects.status_rejected'),
                                    'CANCELADO' => __('projects.status_cancelled'),
                                    null, '' => __('projects.no_status'),
                                    default => $status,
                                };
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

            <div
                x-data="{
                    selected: @entangle('selectedProjects').live,
                    idsPagina: @entangle('idsPagina').live
                }"
               class="overflow-x-auto bg-white rounded-lg shadow min-h-64 pb-8"
            >
                <table class="w-full table-auto border-collapse border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        {{-- Checkbox maestro (solo para roles permitidos) --}}
                        @hasanyrole('admin|estaf')
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
                                        }
                                    "
                                />
                            </th>
                            @else
                            @role('cliente_principal')
                                @if($isClientePrincipalConSub)
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
                                                }
                                            "
                                        />
                                    </th>
                                @endif
                            @endrole
                        @endhasanyrole

                            {{-- ID --}}
                            <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 align-top">
                            <div class="flex items-center justify-between gap-2 min-w-[10rem]">
                                <button
                                    class="inline-flex items-center gap-1 hover:text-blue-600"
                                    wire:click="sortBy('id')"
                                    title="{{ __('projects.sort_by_id') }}"
                                >
                                    <span>ID</span>
                                    <span class="text-xs">{!! $arrow('id') !!}</span>
                                </button>

                                {{-- Filtro ID + Inactivos en dropdown (teleport al body) --}}
                                <div x-data="dropdownTeleport()" class="relative shrink-0">
                                    <button x-ref="btn" @click="toggle" class="p-1 rounded hover:bg-gray-200" title="{{ __('projects.id_filters') }}">⋮</button>

                                    <template x-teleport="body">
                                        <div
                                            x-show="open"
                                            x-transition
                                            @click.outside="close"
                                            :style="style"
                                            class="fixed z-50 w-64 rounded-lg border bg-white shadow p-3 space-y-3"
                                        >
                                            {{-- Filtro por ID --}}
                                            <div>
                                                <label class="block text-xs text-gray-600 mb-1">
                                                    {{ __('projects.project_id_example') }}
                                                </label>
                                                <input
                                                    type="text"
                                                    class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                    placeholder="ID…"
                                                    wire:model.live.debounce.400ms="filters.id"
                                                />
                                            </div>

                                            {{-- Filtro proyectos inactivos --}}
                                            <div class="border-t pt-2">
                                                <label class="inline-flex items-center space-x-2 text-xs text-gray-700">
                                                    <input
                                                        type="checkbox"
                                                        class="rounded border-gray-300"
                                                        wire:model.live="filters.inactivos" {{-- 👈 sin value, Livewire lo hace bool --}}
                                                    >
                                                    <span>{{ __('projects.show_only_inactive') }}</span>
                                                </label>
                                            </div>

                                            <div class="pt-1 flex justify-end gap-2">
                                                <button
                                                    type="button"
                                                    class="px-2 py-1 text-xs rounded border"
                                                    @click="
                                                        $wire.set('filters.id', '');
                                                        $wire.set('filters.inactivos', false);
                                                    "
                                                >
                                                    {{ __('projects.clear') }}
                                                </button>
                                                <button
                                                    type="button"
                                                    class="px-2 py-1 text-xs rounded border"
                                                    @click="close"
                                                >
                                                    {{ __('projects.close') }}
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
                                        title="{{ __('projects.sort_by_name') }}"
                                    >
                                        <span>{{ __('projects.project_name') }}</span>
                                        <span class="text-xs">{!! $arrow('nombre') !!}</span>
                                    </button>

                                    <div x-data="{ open:false }" class="relative shrink-0">
                                        <button @click="open = !open" class="p-1 rounded hover:bg-gray-200" title="{{ __('projects.filter_name') }}">⋮</button>
                                        <div
                                            x-cloak x-show="open" @click.away="open=false" x-transition
                                            class="absolute right-0 z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3"
                                        >
                                            <label class="block text-xs text-gray-600 mb-1">{{ __('projects.name_contains') }}</label>
                                            <input
                                                type="text"
                                                class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                placeholder="{{ __('projects.search') }}"
                                                wire:model.live.debounce.400ms="filters.nombre"
                                            />
                                            <div class="mt-2 flex justify-end gap-2">
                                                <button type="button" class="px-2 py-1 text-xs rounded border"
                                                        @click="$wire.set('filters.nombre','')">{{ __('projects.clear') }}</button>
                                                <button type="button" class="px-2 py-1 text-xs rounded border"
                                                        @click="open=false">{{ __('projects.close') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </th>

                            {{-- Cliente  Cambiar por premiso--}}

                            @can('tablaProyectos-ver-columna-cliente')
                                                           <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 align-top">
                                <div class="flex items-center justify-between gap-2 min-w-[12rem]">
                                    <span>{{ __('projects.client') }}</span>
                                    <div x-data="{ open:false }" class="relative shrink-0">
                                        <button @click="open = !open" class="p-1 rounded hover:bg-gray-200" title="{{ __('projects.filter_client') }}">⋮</button>
                                        <div
                                            x-cloak x-show="open" @click.away="open=false" x-transition
                                            class="absolute right-0 z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3"
                                        >
                                            <label class="block text-xs text-gray-600 mb-1">{{ __('projects.name_or_email') }}</label>
                                            <input
                                                type="text"
                                                class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                placeholder="{{ __('projects.client_placeholder') }}"
                                                wire:model.live.debounce.400ms="filters.cliente"
                                            />
                                            <div class="mt-2 flex justify-end gap-2">
                                                <button type="button" class="px-2 py-1 text-xs rounded border"
                                                        @click="$wire.set('filters.cliente','')">{{ __('projects.clear') }}</button>
                                                <button type="button" class="px-2 py-1 text-xs rounded border"
                                                        @click="open=false">{{ __('projects.close') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </th> 
                            @endcan



                            {{-- Proveedor (solo si tiene permiso) --}}
                            @can('tablaProyectos-ver-columna-proveedor')
                                <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 align-top">
                                    <span>{{ __('projects.provider') }}</span>
                                </th>
                            @endcan

                            {{-- Pedidos (solo si aplica permiso) --}}
                            @can('tablaProyectos-ver-columna-pedidos')
                                <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">{{ __('projects.orders') }}</th>
                            @endcan

                            {{-- Estado Proyecto (ind_activo) --}}
                            <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">
                                {{ __('projects.project_status') }}
                            </th>

                            {{-- Estado Diseño --}}
                            <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 align-top">
                                <div class="flex items-center gap-2 min-w-[12rem]">
                                    <button
                                        class="inline-flex items-center gap-1 hover:text-blue-600"
                                        wire:click="sortBy('estado')"
                                        title="{{ __('projects.sort_by_design_status') }}"
                                    >
                                        <span>{{ __('projects.design_status') }}</span>
                                        <span class="text-xs">{!! $arrow('estado') !!}</span>
                                    </button>

                                    {{-- Filtro estado en dropdown (opcional) --}}
                                    <div x-data="{ open:false }" class="relative">
                                        <button @click="open = !open" class="p-1 rounded hover:bg-gray-200" title="{{ __('projects.filter_status') }}">⋮</button>
                                        <div
                                            x-cloak x-show="open" @click.away="open=false" x-transition
                                            class="absolute z-50 mt-1 w-60 rounded-lg border bg-white shadow p-3"
                                        >
                                            <label class="block text-xs text-gray-600 mb-1">{{ __('projects.design_status') }}</label>
                                            <select
                                                class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                wire:model.live.debounce.400ms="filters.estado"
                                            >
                                                <option value="">{{ __('projects.all') }}</option>
                                                @foreach(array_keys($coloresEstadoDiseno) as $est)
                                                    <option value="{{ $est }}">{{ $statusLabel($est) }}</option>
                                                @endforeach
                                            </select>
                                            <div class="mt-2 flex justify-end gap-2">
                                                <button type="button" class="px-2 py-1 text-xs rounded border"
                                                        @click="$wire.set('filters.estado','')">{{ __('projects.clear') }}</button>
                                                <button type="button" class="px-2 py-1 text-xs rounded border"
                                                        @click="open=false">{{ __('projects.close') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </th>

                  

                            @can('dashboardDiseñosColumnaTareas')
                                <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 w-[22rem] min-w-[22rem] max-w-[22rem]">
                                    {{ __('projects.tasks') }}
                                </th>
                            @endcan

                            @can('dashboardDiseñosColumnaHistorial')
                        
                                <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">{{ __('projects.history') }}</th>
                            @endcan

                            {{-- Acciones --}}
                            <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">{{ __('projects.actions') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($projects as $project)
                            <tr class="hover:bg-gray-50">
                                {{-- Checkbox fila --}}
                                @hasanyrole('admin|estaf')
                                        <td class="px-3 py-2">
                                            <input
                                                type="checkbox"
                                                :value="{{ $project->id }}"
                                                :checked="selected.includes(Number({{ $project->id }}))"
                                                @change="
                                                    const id = Number({{ $project->id }});
                                                    if ($event.target.checked) {
                                                        if (!selected.includes(id)) selected.push(id)
                                                    } else {
                                                        selected = selected.filter(i => i !== id)
                                                    }
                                                "
                                                wire:key="chk-{{ $project->id }}"
                                            />
                                        </td>
                                    @else
                                        @role('cliente_principal')
                                            @if($isClientePrincipalConSub)
                                                <td class="px-3 py-2">
                                                    <input
                                                        type="checkbox"
                                                        :value="{{ $project->id }}"
                                                        :checked="selected.includes(Number({{ $project->id }}))"
                                                        @change="
                                                            const id = Number({{ $project->id }});
                                                            if ($event.target.checked) {
                                                                if (!selected.includes(id)) selected.push(id)
                                                            } else {
                                                                selected = selected.filter(i => i !== id)
                                                            }
                                                        "
                                                        wire:key="chk-{{ $project->id }}"
                                                    />
                                                </td>
                                            @endif
                                        @endrole
                                @endhasanyrole

                                {{-- ID con link --}}
                                <td class="px-3 py-2 text-sm font-semibold min-w-[6rem]" title="{{ $project->nombre ?? 'Proyecto #'.$project->id }}">
                                    {!! $project->proyecto_link !!}
                                </td>

                                {{-- Nombre --}}
                                <td class="px-3 py-2 text-sm text-gray-700">{{ $project->nombre }}</td>

                                {{-- Cliente --}}

                                @can('tablaProyectos-ver-columna-cliente')
                                
                                    <td class="px-3 py-2 text-sm text-gray-700">
                                        @if($project->user)
                                            <span
                                                class="inline-block cursor-help"
                                                title="{{ $project->user->tooltip_sucursal_empresa }}"
                                            >
                                                {{ $project->user->name }}
                                            </span>
                                        @else
                                            <span class="text-gray-500">{{ __('projects.no_client') }}</span>
                                        @endif
                                    </td>
                                
                                @endcan

                                {{-- Proveedor (solo si tiene permiso) --}}
                                @can('tablaProyectos-ver-columna-proveedor')
                                    <td class="px-3 py-2 text-sm text-gray-700">
                                        @if($project->proveedor)
                                            {{ $project->proveedor->name }}
                                        @else
                                            <span class="text-gray-500">{{ __('projects.no_provider') }}</span>
                                        @endif
                                    </td>
                                @endcan

                                {{-- Pedidos --}}
                                @can('tablaProyectos-ver-columna-pedidos')
                                    <td class="px-3 py-2 text-sm text-gray-700">
                                        @php
                                            $ultimoPedido = \App\Models\Pedido::where('proyecto_id', $project->id)
                                                ->where('tipo', 'PEDIDO')
                                                ->where('estado_id', '1')
                                                ->latest('id')
                                                ->first();
                                        @endphp
                                        @if($ultimoPedido)
                                            <button wire:click="abrirResumenPedidos({{ $project->id }})"
                                                    class="text-blue-600 hover:underline text-xs">{{ __('projects.view_more') }}</button>
                                        @else
                                            <span class="text-gray-500">{{ __('projects.no_orders') }}</span>
                                        @endif
                                    </td>
                                @endcan

                                {{-- Estado Proyecto --}}
                                <td class="px-3 py-2 text-sm">
                                    @if($project->ind_activo)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                            {{ __('projects.active') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">
                                            {{ __('projects.inactive') }}
                                        </span>
                                    @endif
                                </td>                                

                                {{-- Estado Diseño con badge --}}
                                @php
                                    $estado = $project->estado ?? __('projects.no_status');
                                    $badge  = $coloresEstadoDiseno[$estado] ?? 'bg-gray-300 text-gray-700';
                                @endphp
                                <td class="px-3 py-2 text-sm whitespace-nowrap min-w-[10rem]">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap min-w-[10rem] justify-center {{ $badge }}">
                                        {{ $statusLabel($estado) }}
                                    </span>
                                </td>

                                {{-- Tareas / Historial (opcional) --}}
                                @can('dashboardDiseñosColumnaTareas')
                                    <td class="px-3 py-2 text-sm w-[22rem] min-w-[22rem] max-w-[22rem] align-top">
                                        @if($project->tareas->isNotEmpty())
                                            <ul class="list-disc list-inside space-y-1 text-xs text-gray-700 break-words">
                                                @foreach($project->tareas as $tarea)
                                                    <li class="break-words">{{ $tarea->descripcion }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-gray-500">{{ __('projects.no_tasks') }}</span>
                                        @endif
                                    </td>
                                @endcan

                                
                                @can('dashboardDiseñosColumnaHistorial')
                                    <td class="px-3 py-2 text-sm">
                                        @if($project->estados->isNotEmpty())
                                            @foreach($project->estados->sortByDesc('id')->take(1) as $e)
                                                <div class="text-xs text-gray-700">
                                                    <strong>{{ $statusLabel($e->estado) }}</strong>
                                                    ({{ \Carbon\Carbon::parse($e->fecha_inicio)->format('d-m-Y H:i') }})
                                                    {{ __('projects.by') }} {{ $e->usuario->name ?? '—' }}
                                                </div>
                                            @endforeach
                                            @if($project->estados->count() > 2)
                                                <button wire:click="verMas({{ $project->id }})"
                                                        class="text-blue-600 hover:underline text-xs">{{ __('projects.view_more') }}</button>
                                            @endif
                                        @else
                                            <span class="text-gray-500 text-sm">{{ __('projects.no_history') }}</span>
                                        @endif
                                    </td>
                                @endcan

                                {{-- Acciones --}}
                                <td class="px-3 py-2 text-sm">
                                    <x-dropdown>
                                        <x-dropdown.item
                                            :href="route('proyecto.show', $project->id)"
                                            label="{{ __('projects.view_details') }}"
                                        />

                                        @can('dashboardDiseñosBotonAsignarTarea')
                                            @if($project->tareas->isEmpty())
                                                <x-dropdown.item
                                                    separator
                                                    @click="$wire.dispatch('abrir-modal-asignacion', { id: {{ $project->id }} })"
                                                    label="{{ __('projects.assign_task') }}"
                                                />
                                            @endif
                                        @endcan

                                        @can('tablaProyectos-ver-columna-pedidos')
                                            <x-dropdown.item
                                                @click="$wire.dispatch('abrir-resumen', { id: {{ $project->id }} })"
                                                label="{{ __('projects.orders_summary') }}"
                                            />
                                        @endcan

                                        @hasanyrole('admin|estaf')
                                            @if($project->ind_activo)
                                                <x-dropdown.item
                                                    separator
                                                    wire:click="openDeactivateModal({{ $project->id }})"
                                                    label="{{ __('projects.deactivate_project') }}"
                                                    class="text-red-600 hover:bg-red-50"
                                                />
                                            @else
                                                <x-dropdown.item
                                                    separator
                                                    wire:click="openActivateModal({{ $project->id }})"
                                                    label="{{ __('projects.activate_project') }}"
                                                    class="text-emerald-600 hover:bg-emerald-50"
                                                />
                                            @endif
                                        @endhasanyrole
                                    </x-dropdown>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                @php
                                    // columnas visibles según roles/permiso
                                    $cols = 1 /* ID */ + 1 /* Nombre */ + 2 /* Estado proyecto + Estado diseño */ + 1 /* Acciones */;
                                    if(auth()->user()->hasAnyRole(['admin','estaf','jefediseñador','cliente_principal'])) $cols++;
                                    if(auth()->user()->can('tablaProyectos-ver-columna-cliente')) $cols++;
                                    if(auth()->user()->can('tablaProyectos-ver-columna-pedidos')) $cols++;
                                    if(auth()->user()->can('tablaProyectos-ver-columna-proveedor')) $cols++;
                                    // checkbox maestro visible?
                                    if(auth()->user()->hasAnyRole(['admin','estaf']) || (auth()->user()->hasRole('cliente_principal') && ($isClientePrincipalConSub ?? false))) $cols++;
                                @endphp
                                <td colspan="{{ $cols }}" class="px-4 py-6 text-center text-sm text-gray-500">
                                    {{ __('projects.no_projects') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

                        <!-- Paginación -->
                        <div class="mt-4">
                            {{ $projects->links() }}
                        </div>
                    </div>
            </div>


    @if($modalOpen)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded shadow-lg w-full max-w-md p-6">
                <h2 class="text-lg font-semibold mb-4">{{ __('projects.assign_task') }}</h2>
                <label class="block text-sm font-medium text-gray-700">{{ __('projects.user') }}</label>
                <select wire:model="selectedUser" class="w-full p-2 border rounded mb-3">
                    <option value="">{{ __('projects.select_user') }}</option>
                    @foreach($designers as $designer)
                        <option value="{{ $designer->id }}">{{ $designer->name }}</option>
                    @endforeach
                </select>
                @error('selectedUser')
                <div class="bg-red-100 text-red-800 p-3 rounded mb-3">{{ $message }}</div>
                @enderror

                <label class="block text-sm font-medium text-gray-700">{{ __('projects.description') }}</label>
                <textarea wire:model="taskDescription" class="w-full p-2 border rounded mb-3"></textarea>
                @error('taskDescription') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                <div class="flex justify-end space-x-2">
                    <button wire:click="cerrarModal" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded">
                        {{ __('projects.cancel') }}
                    </button>
                    <button wire:click="asignarTarea" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
                        {{ __('projects.assign') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($modalVerMas && $proyectoSeleccionado)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-3xl max-h-[80vh] overflow-y-auto">
            <h3 class="text-xl font-bold mb-4">{{ __('projects.status_history_project', ['id' => $proyectoSeleccionado->id]) }}</h3>
            <table class="table-auto w-full text-sm border">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border px-4 py-2">{{ __('projects.status') }}</th>
                        <th class="border px-4 py-2">{{ __('projects.comment') }}</th>
                        <th class="border px-4 py-2">{{ __('projects.file') }}</th>
                        <th class="border px-4 py-2">{{ __('projects.file_id') }}</th>
                        <th class="border px-4 py-2">{{ __('projects.date') }}</th>
                        <th class="border px-4 py-2">{{ __('projects.user') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($proyectoSeleccionado->estados->sortByDesc('id') as $estado)
                        <tr>
                            <td class="border px-4 py-2">{{ $estado->estado }}</td>
                            <td class="border px-4 py-2">{{ $estado->comentario ?? '-' }}</td>
                            <td class="border px-4 py-2">
                                @if($estado->url)
                                    <a href="{{ asset('storage/' . $estado->url) }}" target="_blank" class="text-blue-600 underline">{{ __('projects.view_file') }}</a>
                                @else
                                    <span class="text-gray-500">{{ __('projects.not_available') }}</span>
                                @endif
                            </td>
                            <td class="border px-4 py-2 text-center">{{ $estado->last_uploaded_file_id ?? '-' }}</td>
                            <td class="border px-4 py-2">{{ \Carbon\Carbon::parse($estado->fecha_inicio)->format('d-m-Y H:i') }}</td>
                            <td class="border px-4 py-2">{{ $estado->usuario->name ?? __('projects.unknown') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4 text-right">
                <button wire:click="cerrarModalVerMas" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    {{ __('projects.close') }}
                </button>
            </div>
        </div>
    </div>
    @endif


    {{-- Modal: Resumen de pedidos --}}
    @if($modalResumen)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl mx-4">
            <div class="p-4 border-b flex items-center justify-between">
                <h3 class="text-lg font-semibold">{{ __('projects.orders_summary_project', ['id' => $proyectoResumen?->id ?? $proyectoResumenId]) }}</h3>
                <button wire:click="cerrarResumenPedidos" class="text-gray-500 hover:text-gray-700 text-xl leading-none">✕</button>
            </div>

            <div class="p-4 space-y-4">
                {{-- Último pedido pendiente --}}
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-bold mb-2">{{ __('projects.latest_order_pending_approval') }}</h4>

                    @if($ultimoPedidoPendiente)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            <div><span class="font-semibold">{{ __('projects.id_label') }}</span> {{ $ultimoPedidoPendiente->id }}</div>
                            <div><span class="font-semibold">{{ __('projects.date_label') }}</span> {{ optional($ultimoPedidoPendiente->created_at)->format('Y-m-d H:i') }}</div>
                            <div><span class="font-semibold">{{ __('projects.product_label') }}</span> {{ $ultimoPedidoPendiente->producto->nombre ?? '—' }}</div>
                            <div><span class="font-semibold">{{ __('projects.category_label') }}</span> {{ $ultimoPedidoPendiente->producto->categoria->nombre ?? '—' }}</div>
                            <div><span class="font-semibold">{{ __('projects.total_label') }}</span> {{ $ultimoPedidoPendiente->total }}</div>
                            <div><span class="font-semibold">{{ __('projects.status_label') }}</span> {{ $ultimoPedidoPendiente->estado }}</div>
                        </div>
                    @else
                        <p class="text-sm text-gray-600 italic">{{ __('projects.no_pending_orders') }}</p>
                    @endif
                </div>

                {{-- Lista compacta (últimos 5 pedidos) --}}
                <div class="bg-white border rounded-lg">
                    <div class="px-4 py-2 border-b">
                        <h4 class="text-sm font-bold">{{ __('projects.latest_orders') }}</h4>
                    </div>
                    <div class="p-2 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-3 py-2 text-left">ID</th>
                                    <th class="px-3 py-2 text-left">{{ __('projects.product') }}</th>
                                    <th class="px-3 py-2 text-left">{{ __('projects.category') }}</th>
                                    <th class="px-3 py-2 text-left">{{ __('projects.total') }}</th>
                                    <th class="px-3 py-2 text-left">{{ __('projects.status') }}</th>
                                    <th class="px-3 py-2 text-left">{{ __('projects.date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ultimosPedidos as $p)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-3 py-2">{{ $p->id }}</td>
                                        <td class="px-3 py-2">{{ $p->producto->nombre ?? '—' }}</td>
                                        <td class="px-3 py-2">{{ $p->producto->categoria->nombre ?? '—' }}</td>
                                        <td class="px-3 py-2">{{ $p->total }}</td>
                                        <td class="px-3 py-2">
                                            <span class="px-2 py-0.5 rounded-full text-xs
                                                @class([
                                                    'bg-yellow-100 text-yellow-800' => $p->estado === 'PENDIENTE',
                                                    'bg-emerald-100 text-emerald-800' => $p->estado === 'PROGRAMADO' || $p->estado === 'APROBADO',
                                                    'bg-blue-100 text-blue-800' => $p->estado === 'POR PROGRAMAR',
                                                    'bg-red-100 text-red-800' => $p->estado === 'CANCELADO',
                                                    'bg-gray-100 text-gray-800' => !in_array($p->estado, ['PENDIENTE','PROGRAMADO','APROBADO','POR PROGRAMAR','CANCELADO']),
                                                ])
                                            ">
                                                {{ $p->estado }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2">{{ optional($p->created_at)->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-3 py-3 text-center text-gray-500">{{ __('projects.no_orders') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Ligas de acción --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <a href="{{ route('proyecto.show', $proyectoResumenId) }}"
                    target="_blank" rel="noopener"
                    class="text-blue-600 hover:underline text-sm">
                        {{ __('projects.view_more_on_project_page') }}
                    </a>

                    <div class="text-right">
                        <button wire:click="cerrarResumenPedidos"
                                class="px-4 py-2 rounded bg-gray-600 text-white hover:bg-gray-700 text-sm">
                            {{ __('projects.close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif



    {{-- Modal INACTIVAR PROYECTO --}}
    <div
        x-cloak
        x-show="showDeactivate"
        x-transition
        class="fixed inset-0 z-40 flex items-center justify-center bg-black/50"
    >
        <div class="bg-white rounded-lg shadow-xl max-w-xl w-full mx-4 p-6 relative">
            <h2 class="text-lg sm:text-xl font-bold mb-4 text-red-600">
                {{ __('projects.confirm_project_deactivation') }}
            </h2>

            <p class="text-sm text-gray-700 mb-4">
                {{ __('projects.you_are_about_to_deactivate_project') }}
                <span class="font-semibold">
                    #{{ $deactivateStats['id'] ?? '' }} - {{ $deactivateStats['nombre'] ?? '' }}
                </span>.
            </p>

            <div class="mb-4 text-sm text-gray-700 space-y-1">
                <p class="font-semibold">
                    {{ __('projects.the_following_will_apply') }}
                </p>
                <ul class="list-disc list-inside space-y-1">
                    <li>{{ __('projects.project_will_be_marked_inactive') }}</li>
                    <li>{{ __('projects.current_design_status') }} <strong>{{ $statusLabel($deactivateStats['estado'] ?? null) }}</strong>.</li>
                    <li>{{ __('projects.associated_active_orders') }}
                        <span class="font-semibold">{{ $deactivateStats['total_pedidos'] ?? 0 }}</span>
                    </li>
                </ul>
            </div>

            <p class="text-xs text-red-500 mb-4">
                {{ __('projects.deactivate_warning') }}
            </p>

            <div class="mt-4 flex flex-col sm:flex-row justify-end gap-2">
                <button
                    type="button"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100"
                    @click="showDeactivate = false"
                >
                    {{ __('projects.cancel') }}
                </button>
                <button
                    type="button"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700"
                    wire:click="inactivarProyectoConfirmado"
                >
                    {{ __('projects.yes_deactivate') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Modal ACTIVAR PROYECTO --}}
    <div
        x-cloak
        x-show="showActivate"
        x-transition
        class="fixed inset-0 z-40 flex items-center justify-center bg-black/50"
    >
        <div class="bg-white rounded-lg shadow-xl max-w-xl w-full mx-4 p-6 relative">
            <h2 class="text-lg sm:text-xl font-bold mb-4 text-emerald-700">
                {{ __('projects.confirm_project_activation') }}
            </h2>

            <p class="text-sm text-gray-700 mb-4">
                {{ __('projects.you_are_about_to_activate_project') }}
                <span class="font-semibold">
                    #{{ $activateStats['id'] ?? '' }} - {{ $activateStats['nombre'] ?? '' }}
                </span>.
            </p>

            <div class="mb-4 text-sm text-gray-700 space-y-1">
                <p class="font-semibold">
                    {{ __('projects.the_following_will_be_done') }}
                </p>
                <ul class="list-disc list-inside space-y-1">
                    <li>{{ __('projects.project_will_be_marked_active') }}</li>
                    <li>{{ __('projects.design_status_label') }} <strong>{{ $statusLabel($activateStats['estado'] ?? null) }}</strong>.</li>
                    <li>{{ __('projects.associated_active_orders') }}
                        <span class="font-semibold">{{ $activateStats['total_pedidos'] ?? 0 }}</span>
                    </li>
                </ul>
            </div>

            <div class="mt-4 flex flex-col sm:flex-row justify-end gap-2">
                <button
                    type="button"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100"
                    @click="showActivate = false"
                >
                    {{ __('projects.cancel') }}
                </button>
                <button
                    type="button"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700"
                    wire:click="activarProyectoConfirmado"
                >
                    {{ __('projects.yes_activate') }}
                </button>
            </div>
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
                // Posición base: debajo del botón, alineado a su borde derecho
                let left = r.right - panelW;
                const top  = r.bottom + gap;

                // Correcciones para no salirte de la ventana
                const vw = window.innerWidth;
                const margin = 8;
                if (left < margin) left = margin;                 // clamp izquierda
                if (left + panelW > vw - margin) left = vw - margin - panelW; // clamp derecha

                this.style = `top:${top}px;left:${left}px`;
                }
            });
        });
    </script>


</div>

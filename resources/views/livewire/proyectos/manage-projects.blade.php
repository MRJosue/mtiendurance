<div 
    x-data="{
        abierto: JSON.parse(localStorage.getItem('dashboard_preproyecto_abierto') ?? 'true'),
        toggle() {
            this.abierto = !this.abierto;
            localStorage.setItem('dashboard_preproyecto_abierto', JSON.stringify(this.abierto));
        }
    }"
    class="container mx-auto p-6"
>
            <h2 
                @click="toggle()"
                class="text-xl font-bold mb-4 border-b border-gray-300 pb-2 cursor-pointer hover:text-blue-600 transition"
            >
                Diseños
                <span class="text-sm text-gray-500 ml-2" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
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
                                {{ $tab }}
                            </button>
                        </li>
                    @endforeach
                </ul>

                    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <!-- Lado izquierdo: (deja lo que ya tengas: botones, chips, etc.) -->
                        <div class="flex flex-wrap items-center gap-2">
                            {{-- ... tus botones/acciones ... --}}
                        </div>

                        <!-- Lado derecho: PerPage -->
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

                    <div x-data="{ selectedProjects: @entangle('selectedProjects') }" class="container mx-auto p-6">

    {{-- === TABLA estilo hoja-viewer para manage-projects === --}}
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

            <div
                x-data="{
                    selected: @entangle('selectedProjects').live,
                    idsPagina: @entangle('idsPagina').live
                }"
                class="overflow-x-auto bg-white rounded-lg shadow"
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
                                    title="Ordenar por ID"
                                >
                                    <span>ID</span>
                                    <span class="text-xs">{!! $arrow('id') !!}</span>
                                </button>

                                {{-- Filtro ID en dropdown (teleport al body) --}}
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
                                            <label class="block text-xs text-gray-600 mb-1">
                                                ID Proyecto (ej. 101 ó 101,102)
                                            </label>
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
                                        <button @click="open = !open" class="p-1 rounded hover:bg-gray-200" title="Filtrar Nombre">⋮</button>
                                        <div
                                            x-cloak x-show="open" @click.away="open=false" x-transition
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
                                                <button type="button" class="px-2 py-1 text-xs rounded border"
                                                        @click="$wire.set('filters.nombre','')">Limpiar</button>
                                                <button type="button" class="px-2 py-1 text-xs rounded border"
                                                        @click="open=false">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </th>

                            {{-- Cliente --}}
                            @role('admin|estaf|jefediseñador|cliente_principal')
                            <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 align-top">
                                <div class="flex items-center justify-between gap-2 min-w-[12rem]">
                                    <span>Cliente</span>
                                    <div x-data="{ open:false }" class="relative shrink-0">
                                        <button @click="open = !open" class="p-1 rounded hover:bg-gray-200" title="Filtrar Cliente">⋮</button>
                                        <div
                                            x-cloak x-show="open" @click.away="open=false" x-transition
                                            class="absolute right-0 z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3"
                                        >
                                            <label class="block text-xs text-gray-600 mb-1">Nombre o correo</label>
                                            <input
                                                type="text"
                                                class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                placeholder="Cliente…"
                                                wire:model.live.debounce.400ms="filters.cliente"
                                            />
                                            <div class="mt-2 flex justify-end gap-2">
                                                <button type="button" class="px-2 py-1 text-xs rounded border"
                                                        @click="$wire.set('filters.cliente','')">Limpiar</button>
                                                <button type="button" class="px-2 py-1 text-xs rounded border"
                                                        @click="open=false">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </th>
                            @endrole

                            {{-- Pedidos (solo si aplica permiso) --}}
                            @can('tablaProyectos-ver-columna-pedidos')
                                <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Pedidos</th>
                            @endcan

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

                                    {{-- Filtro estado en dropdown (opcional) --}}
                                    <div x-data="{ open:false }" class="relative">
                                        <button @click="open = !open" class="p-1 rounded hover:bg-gray-200" title="Filtrar Estado">⋮</button>
                                        <div
                                            x-cloak x-show="open" @click.away="open=false" x-transition
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
                                                <button type="button" class="px-2 py-1 text-xs rounded border"
                                                        @click="$wire.set('filters.estado','')">Limpiar</button>
                                                <button type="button" class="px-2 py-1 text-xs rounded border"
                                                        @click="open=false">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </th>

                            {{-- Extras (si aplica) --}}
                            {{-- @can('dashboardjefediseñadorproyectos') --}}

                            @can('dashboardDiseñosColumnaTareas')
                                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Tareas</th>
                            @endcan
                            @can('dashboardDiseñosColumnaHistorial')
                        
                                <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Historial</th>
                            @endcan

                            {{-- Acciones --}}
                            <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Acciones</th>
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
                                @role('admin|estaf|jefediseñador|cliente_principal')
                                    <td class="px-3 py-2 text-sm text-gray-700">{{ $project->user->name ?? 'Sin Cliente' }}</td>
                                @endrole

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
                                                    class="text-blue-600 hover:underline text-xs">Ver más</button>
                                        @else
                                            <span class="text-gray-500">Sin pedidos</span>
                                        @endif
                                    </td>
                                @endcan

                                {{-- Estado Diseño con badge --}}
                                @php
                                    $estado = $project->estado ?? 'Sin estado';
                                    $badge  = $coloresEstadoDiseno[$estado] ?? 'bg-gray-300 text-gray-700';
                                @endphp
                                <td class="px-3 py-2 text-sm whitespace-nowrap min-w-[10rem]">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap min-w-[10rem] justify-center {{ $badge }}">
                                        {{ $estado }}
                                    </span>
                                </td>

                                {{-- Tareas / Historial (opcional) --}}
                                @can('dashboardDiseñosColumnaTareas')
                                    <td class="px-3 py-2 text-sm">
                                        @if($project->tareas->isNotEmpty())
                                            <ul class="list-disc list-inside space-y-1 text-xs text-gray-700">
                                                @foreach($project->tareas as $tarea)
                                                    <li>{{ $tarea->descripcion }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-gray-500">Sin tareas</span>
                                        @endif
                                    </td>

                                @endcan
                                @can('dashboardDiseñosColumnaHistorial')
                                    <td class="px-3 py-2 text-sm">
                                        @if($project->estados->isNotEmpty())
                                            @foreach($project->estados->sortByDesc('id')->take(1) as $e)
                                                <div class="text-xs text-gray-700">
                                                    <strong>{{ $e->estado }}</strong>
                                                    ({{ \Carbon\Carbon::parse($e->fecha_inicio)->format('d-m-Y H:i') }})
                                                    por {{ $e->usuario->name ?? '—' }}
                                                </div>
                                            @endforeach
                                            @if($project->estados->count() > 2)
                                                <button wire:click="verMas({{ $project->id }})"
                                                        class="text-blue-600 hover:underline text-xs">Ver más</button>
                                            @endif
                                        @else
                                            <span class="text-gray-500 text-sm">Sin historial</span>
                                        @endif
                                    </td>
                                @endcan

                                {{-- Acciones --}}
                                <td class="px-3 py-2 text-sm">
                                    <x-dropdown>
                                        <x-dropdown.item
                                            :href="route('proyecto.show', $project->id)"
                                            label="Ver detalles"
                                        />
                                        @can('dashboardDiseñosBotonAsignarTarea')
                                            @if($project->tareas->isEmpty())
                                                <x-dropdown.item separator
                                                    @click="$wire.dispatch('abrir-modal-asignacion', { id: {{ $project->id }} })"
                                                    label="Asignar Tarea"
                                                />
                                            @endif
                                        @endcan
                                        @can('tablaProyectos-ver-columna-pedidos')
                                            <x-dropdown.item
                                                @click="$wire.dispatch('abrir-resumen', { id: {{ $project->id }} })"
                                                label="Resumen de pedidos"
                                            />
                                        @endcan

                                    </x-dropdown>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                @php
                                    // columnas visibles según roles/permiso
                                    $cols = 1 /* ID */ + 1 /* Nombre */ + 1 /* Estado */ + 1 /* Acciones */;
                                    if(auth()->user()->hasAnyRole(['admin','estaf','jefediseñador','cliente_principal'])) $cols++;
                                    if(auth()->user()->can('tablaProyectos-ver-columna-pedidos')) $cols++;
                                    // checkbox maestro visible?
                                    if(auth()->user()->hasAnyRole(['admin','estaf']) || (auth()->user()->hasRole('cliente_principal') && ($isClientePrincipalConSub ?? false))) $cols++;
                                @endphp
                                <td colspan="{{ $cols }}" class="px-4 py-6 text-center text-sm text-gray-500">
                                    No hay proyectos para mostrar.
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
                <h2 class="text-lg font-semibold mb-4">Asignar Tarea</h2>
                <label class="block text-sm font-medium text-gray-700">Usuario</label>
                <select wire:model="selectedUser" class="w-full p-2 border rounded mb-3">
                    <option value="">Seleccione un usuario</option>
                    @foreach($designers as $designer)
                        <option value="{{ $designer->id }}">{{ $designer->name }}</option>
                    @endforeach
                </select>
                @error('selectedUser')
                <div class="bg-red-100 text-red-800 p-3 rounded mb-3">{{ $message }}</div>
                @enderror

                <label class="block text-sm font-medium text-gray-700">Descripción</label>
                <textarea wire:model="taskDescription" class="w-full p-2 border rounded mb-3"></textarea>
                @error('taskDescription') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                <div class="flex justify-end space-x-2">
                    <button wire:click="cerrarModal" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded">
                        Cancelar
                    </button>
                    <button wire:click="asignarTarea" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
                        Asignar
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($modalVerMas && $proyectoSeleccionado)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-3xl max-h-[80vh] overflow-y-auto">
            <h3 class="text-xl font-bold mb-4">Historial de Estatus - Proyecto #{{ $proyectoSeleccionado->id }}</h3>
            <table class="table-auto w-full text-sm border">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border px-4 py-2">Estatus</th>
                        <th class="border px-4 py-2">Comentario</th>
                        <th class="border px-4 py-2">Archivo</th>
                        <th class="border px-4 py-2">ID Archivo</th>
                        <th class="border px-4 py-2">Fecha</th>
                        <th class="border px-4 py-2">Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($proyectoSeleccionado->estados->sortByDesc('id') as $estado)
                        <tr>
                            <td class="border px-4 py-2">{{ $estado->estado }}</td>
                            <td class="border px-4 py-2">{{ $estado->comentario ?? '-' }}</td>
                            <td class="border px-4 py-2">
                                @if($estado->url)
                                    <a href="{{ asset('storage/' . $estado->url) }}" target="_blank" class="text-blue-600 underline">Ver archivo</a>
                                @else
                                    <span class="text-gray-500">No disponible</span>
                                @endif
                            </td>
                            <td class="border px-4 py-2 text-center">{{ $estado->last_uploaded_file_id ?? '-' }}</td>
                            <td class="border px-4 py-2">{{ \Carbon\Carbon::parse($estado->fecha_inicio)->format('d-m-Y H:i') }}</td>
                            <td class="border px-4 py-2">{{ $estado->usuario->name ?? 'Desconocido' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4 text-right">
                <button wire:click="cerrarModalVerMas" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Cerrar
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
                <h3 class="text-lg font-semibold">
                    Resumen de pedidos · Proyecto #{{ $proyectoResumen?->id ?? $proyectoResumenId }}
                </h3>
                <button wire:click="cerrarResumenPedidos" class="text-gray-500 hover:text-gray-700 text-xl leading-none">✕</button>
            </div>

            <div class="p-4 space-y-4">
                {{-- Último pedido pendiente --}}
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-bold mb-2">Último pedido POR APROBAR</h4>

                    @if($ultimoPedidoPendiente)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            <div><span class="font-semibold">ID:</span> {{ $ultimoPedidoPendiente->id }}</div>
                            <div><span class="font-semibold">Fecha:</span> {{ optional($ultimoPedidoPendiente->created_at)->format('Y-m-d H:i') }}</div>
                            <div><span class="font-semibold">Producto:</span> {{ $ultimoPedidoPendiente->producto->nombre ?? '—' }}</div>
                            <div><span class="font-semibold">Categoría:</span> {{ $ultimoPedidoPendiente->producto->categoria->nombre ?? '—' }}</div>
                            <div><span class="font-semibold">Total:</span> {{ $ultimoPedidoPendiente->total }}</div>
                            <div><span class="font-semibold">Estatus:</span> {{ $ultimoPedidoPendiente->estado }}</div>
                        </div>
                    @else
                        <p class="text-sm text-gray-600 italic">Sin pedidos pendientes.</p>
                    @endif
                </div>

                {{-- Lista compacta (últimos 5 pedidos) --}}
                <div class="bg-white border rounded-lg">
                    <div class="px-4 py-2 border-b">
                        <h4 class="text-sm font-bold">Últimos pedidos (5)</h4>
                    </div>
                    <div class="p-2 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-3 py-2 text-left">ID</th>
                                    <th class="px-3 py-2 text-left">Producto</th>
                                    <th class="px-3 py-2 text-left">Categoría</th>
                                    <th class="px-3 py-2 text-left">Total</th>
                                    <th class="px-3 py-2 text-left">Estatus</th>
                                    <th class="px-3 py-2 text-left">Fecha</th>
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
                                        <td colspan="6" class="px-3 py-3 text-center text-gray-500">Sin pedidos.</td>
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
                        Ver más en la página del proyecto
                    </a>

                    <div class="text-right">
                        <button wire:click="cerrarResumenPedidos"
                                class="px-4 py-2 rounded bg-gray-600 text-white hover:bg-gray-700 text-sm">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

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
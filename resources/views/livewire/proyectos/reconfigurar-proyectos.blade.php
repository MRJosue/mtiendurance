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

                <div x-data="{ selectedProjects: @entangle('selectedProjects') }" class="container mx-auto p-6">

                    <div class="overflow-x-auto bg-white rounded-lg shadow min-h-64 pb-8">

                            @if (session()->has('message'))
                                <div class="bg-green-100 text-green-800 p-3 rounded mb-3">
                                    {{ session('message') }}
                                </div>
                            @endif
                        <table class="min-w-full border-collapse border border-gray-200 rounded-lg">


                            <thead class="bg-gray-100 text-sm">
                                <tr>
                                    {{-- Checkbox maestro --}}
                                    @hasanyrole('admin|estaf')
                                        <th class="border-b px-4 py-2 text-left text-gray-700 font-medium">
                                            <input
                                                type="checkbox"
                                                wire:model="selectAll"
                                                @change="selectedProjects = $event.target.checked ? @js($projects->pluck('id')) : []"
                                            />
                                        </th>
                                    @endhasanyrole

                                    {{-- Columna: ID con filtro compacto --}}
                                    <th class="border-b px-4 py-2 text-left text-gray-700 font-medium">
                                        <div class="flex items-center justify-between">
                                            <span>ID</span>

                                            <div x-data="{ open:false }" class="relative">
                                                <button @click="open = !open" class="p-1 rounded hover:bg-gray-200" title="Filtrar ID">⋮</button>
                                                <div
                                                    x-cloak x-show="open" @click.away="open=false" x-transition
                                                    class="absolute z-50 mt-1 w-56 rounded-lg border bg-white shadow p-3"
                                                >
                                                    <label class="block text-xs text-gray-600 mb-1">ID Proyecto</label>
                                                    <input
                                                        type="text"
                                                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                        placeholder="Ej. 101 o 101,102"
                                                        wire:model.live.debounce.400ms="filters.id"
                                                    />

                                                    <div class="mt-3 pt-2 border-t border-gray-200">
                                                        <label class="flex items-center gap-2 text-xs text-gray-700">
                                                            <input
                                                                type="checkbox"
                                                                wire:model.live="filters.inactivos"
                                                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                                            >
                                                            <span>Mostrar solo proyectos inactivos</span>
                                                        </label>
                                                    </div>

                                                    <div class="mt-2 flex justify-end gap-2">
                                                        <button
                                                            type="button"
                                                            class="px-2 py-1 text-xs rounded border"
                                                            @click="$wire.set('filters.id','')"
                                                        >Limpiar</button>
                                                        <button
                                                            type="button"
                                                            class="px-2 py-1 text-xs rounded border"
                                                            @click="open=false"
                                                        >Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </th>

                                    {{-- Columna: Nombre del Proyecto con filtro compacto --}}
                                    <th class="border-b px-4 py-2 text-left text-gray-700 font-medium">
                                        <div class="flex items-center justify-between">
                                            <span>Nombre del Proyecto</span>

                                            <div x-data="{ open:false }" class="relative">
                                                <button @click="open = !open" class="p-1 rounded hover:bg-gray-200" title="Filtrar Nombre">⋮</button>
                                                <div
                                                    x-cloak x-show="open" @click.away="open=false" x-transition
                                                    class="absolute z-50 mt-1 w-56 rounded-lg border bg-white shadow p-3"
                                                >
                                                    <label class="block text-xs text-gray-600 mb-1">Nombre contiene</label>
                                                    <input
                                                        type="text"
                                                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                        placeholder="Buscar..."
                                                        wire:model.live.debounce.400ms="filters.nombre"
                                                    />
                                                    <div class="mt-2 flex justify-end gap-2">
                                                        <button
                                                            type="button"
                                                            class="px-2 py-1 text-xs rounded border"
                                                            @click="$wire.set('filters.nombre','')"
                                                        >Limpiar</button>
                                                        <button
                                                            type="button"
                                                            class="px-2 py-1 text-xs rounded border"
                                                            @click="open=false"
                                                        >Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </th>

                                    {{-- Columna: Cliente con filtro compacto --}}
                                    @can('tablaReconfigurarar-ver-todos-los-proyectos')
                                        <th class="border-b px-4 py-2 text-left text-gray-700 font-medium">
                                            <div class="flex items-center justify-between">
                                                <span>Cliente</span>

                                                <div x-data="{ open:false }" class="relative">
                                                    <button @click="open = !open" class="p-1 rounded hover:bg-gray-200" title="Filtrar Cliente">⋮</button>
                                                    <div
                                                        x-cloak x-show="open" @click.away="open=false" x-transition
                                                        class="absolute z-50 mt-1 w-56 rounded-lg border bg-white shadow p-3"
                                                    >
                                                        <label class="block text-xs text-gray-600 mb-1">Nombre o correo</label>
                                                        <input
                                                            type="text"
                                                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                                            placeholder="Cliente..."
                                                            wire:model.live.debounce.400ms="filters.cliente"
                                                        />
                                                        <div class="mt-2 flex justify-end gap-2">
                                                            <button
                                                                type="button"
                                                                class="px-2 py-1 text-xs rounded border"
                                                                @click="$wire.set('filters.cliente','')"
                                                            >Limpiar</button>
                                                            <button
                                                                type="button"
                                                                class="px-2 py-1 text-xs rounded border"
                                                                @click="open=false"
                                                            >Cerrar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </th>
                                    @endcan

                                    {{-- Columna: Pedidos (sin filtro) --}}
                                    @can('tablaReconfigurarar-ver-columna-pedidos')
                                        <th class="border-b px-4 py-2 text-left text-gray-700 font-medium">
                                            Pedidos
                                        </th>
                                    @endcan

                                    {{-- Columna: Estado del Diseño con filtro compacto --}}
                                    <th class="border-b px-4 py-2 text-left text-gray-700 font-medium whitespace-nowrap">
                                        <div class="flex items-center justify-between">
                                            <span>Estado Diseño</span>


                                        </div>
                                    </th>

                                    {{-- Columnas adicionales según permisos --}}
                                    @can('tablaReconfigurarar-columna-tareas')
                                        <th class="border-b px-4 py-2 text-gray-700 font-medium">Tareas</th>
                                    @endcan
                                    @can('tablaReconfigurarar-columna-historial')
                                        <th class="border-b px-4 py-2 text-gray-700 font-medium">Historial</th>
                                    @endcan

                                    {{-- Acciones --}}
                                    <th class="border-b px-4 py-2 text-left text-gray-700 font-medium">Acciones</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($projects as $project)
                                    <tr class="hover:bg-gray-50">
                                        @hasanyrole('admin|estaf')
                                        <td class="border-b px-4 py-2 text-gray-700 text-sm">
                                            <input
                                                type="checkbox"
                                                wire:model="selectedProjects"
                                                value="{{ $project->id }}"
                                            />
                                        </td>
                                        @endhasanyrole
                                        <td
                                            class="border-b px-4 py-2 text-gray-700  font-semibold min-w-[6rem]"
                                            title="{{ $project->nombre ?? 'Proyecto #'.$project->id }}"
                                        >
                                            {!! $project->proyecto_link !!}
                                            {{-- Si prefieres sin accessor, usa:
                                            <a href="{{ route('proyecto.show', $project->id) }}" class="text-blue-600 font-semibold hover:underline">
                                                Proyecto #{{ $project->id }}
                                            </a>
                                            --}}
                                        </td>
                                    
                                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $project->nombre }}</td>
                                    
                                        @can('tablaReconfigurarar-ver-todos-los-proyectos')
                                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $project->user->name ?? 'Sin Cliente' }}</td>
                                        @endcan

                                        @can('tablaReconfigurarar-ver-columna-pedidos')
                                            <td class="border-b px-4 py-2 text-gray-700 text-sm">
                                                @if($project->pedidos->isNotEmpty())
                                               
                                                    @php
                                                        $ultimoPedido = \App\Models\Pedido::where('proyecto_id', $project->id)
                                                            ->where('tipo', 'PEDIDO')
                                                            ->where('estado_id', '1')
                                                            ->latest('id')
                                                            ->first();
                                                    @endphp
                                                    @if($ultimoPedido)
                                                        
                                                            {{-- <span class="font-semibold">Categoría:</span> {{ $ultimoPedido->producto->categoria->nombre ?? 'Sin categoría' }}, --}}
                                                            {{-- <span class="font-semibold">Producto:</span> {{ $ultimoPedido->producto->nombre ?? 'Sin producto' }}, --}}
                                                            {{-- <span class="font-semibold">Total:</span> {{ $ultimoPedido->total }}, --}}
                                                            {{-- <span class="font-semibold">Estatus:</span> {{ $ultimoPedido->estado }} --}}
                                                 
                                                                <button  wire:click="abrirResumenPedidos({{ $project->id }})" class="text-blue-500 hover:underline text-xs mt-1">
                                                                    Ver más
                                                            </button>
                                                    @else
                                                        <span class="text-gray-500">Sin pedidos</span>
                                                    @endif


                                                

                                                @else
                                                    <span class="text-gray-500">Sin pedidos</span>
                                                @endif
                                            </td> 
                                        @endcan

                                        @php
                                            $estado = $project->estado ?? 'Sin estado';
                                            $colores = [
                                                'PENDIENTE'         => 'bg-yellow-400 text-black',
                                                'ASIGNADO'          => 'bg-blue-500 text-white',
                                                'EN PROCESO'        => 'bg-orange-500 text-white',
                                                'REVISION'          => 'bg-purple-600 text-white',
                                                'DISEÑO APROBADO'   => 'bg-emerald-600 text-white',
                                                'DISEÑO RECHAZADO'  => 'bg-red-600 text-white',
                                                'CANCELADO'         => 'bg-gray-500 text-white',
                                            ];
                                        @endphp
                                        <td class="border-b px-4 py-2 text-sm whitespace-nowrap min-w-[10rem]">
                                            <span class="inline-block px-2 py-1 rounded-full text-xs font-semibold
                                                        {{ $colores[$estado] ?? 'bg-gray-300 text-gray-700' }}">
                                                {{ $estado }}
                                            </span>
                                        </td>



                                        @can('tablaReconfigurarar-columna-tareas')
                                            <td class="px-4 py-3 border">                                                  
                                                @if($project->tareas->isNotEmpty())
                                                            <ul class="list-disc list-inside space-y-1">
                                                                @foreach($project->tareas as $tarea)
                                                                    <li class="text-xs">
                                                                        {{-- <strong>Usuario:</strong> {{ $tarea->staff->name ?? 'No asignado' }}<br> --}}
                                                                        <strong>Descripción:</strong> {{ $tarea->descripcion }}<br>
                                                                        {{-- <strong>Estado:</strong> {{ $tarea->estado }} --}}
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @else
                                                            <span class="text-gray-500 text-sm">Sin tareas</span>
                                                        @endif
                                            </td>
                                        @endcan
                                        @can('tablaReconfigurarar-columna-historial')
                                            <td class="px-4 py-3 border">                                                    
                                                @if($project->estados->isNotEmpty())
                                                            <ul class="list-disc list-inside text-gray-600 space-y-1 text-xs">
                                                                @foreach($project->estados->sortByDesc('id')->take(1) as $estado)
                                                                    <li>
                                                                        <strong>{{ $estado->estado }}</strong> 
                                                                        ({{ \Carbon\Carbon::parse($estado->fecha_inicio)->format('d-m-Y H:i') }})
                                                                        por {{ $estado->usuario->name ?? 'Desconocido' }}
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                            @if($project->estados->count() > 2)
                                                                <button wire:click="verMas({{ $project->id }})" class="text-blue-500 hover:underline text-xs mt-1">
                                                                    Ver más
                                                                </button>
                                                            @endif
                                                        @else
                                                            <span class="text-gray-500 text-sm">Sin historial</span>
                                                        @endif
                                            </td>  
                                        @endcan

                                        
                                        <td class="px-4 py-2 border text-center space-y-1">
                                        <x-dropdown>
                                                <x-dropdown.item
                                                    
                                                    :href="route('proyecto.show', $project->id)"
                                                    label="Ver detalles"
                                                />

                                        </x-dropdown>

                                        </td>
                                    </tr>
                                @endforeach
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

</div>
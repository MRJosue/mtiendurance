<div 
    x-data="{
        abierto: JSON.parse(localStorage.getItem('dashboard_preproyecto_abierto') ?? 'true'),
        toggle() {
            this.abierto = !this.abierto;
            localStorage.setItem('dashboard_preproyecto_abierto', JSON.stringify(this.abierto));
        }
    }"
    class="container mx-auto p-6 text-gray-900 dark:text-gray-100"
>
            <h2 
                @click="toggle()"
                class="mb-4 cursor-pointer border-b border-gray-300 pb-2 text-xl font-bold transition hover:text-blue-600 dark:border-gray-700 dark:hover:text-blue-400"
            >
                Diseños
            <span class="ml-2 text-sm text-gray-500 dark:text-gray-400" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
            </h2>   

            <!-- Contenido del panel -->
            <div x-show="abierto" x-transition>

                <ul class="mb-4 flex flex-wrap gap-1 border-b border-gray-200 dark:border-gray-700">
                    @foreach ($this->tabs as $tab)
                        <li>
                            <button
                                wire:click="setTab('{{ $tab }}')"
                                @class([
                                    'px-4 py-2 rounded-t-lg text-sm whitespace-nowrap',
                                    'border-b-2 font-semibold bg-white text-blue-600 dark:bg-gray-800 dark:text-blue-400' => $activeTab === $tab,
                                    'text-gray-600 hover:text-blue-500 dark:text-gray-300 dark:hover:text-blue-400'       => $activeTab !== $tab,
                                    'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400'               => $activeTab === $tab,
                                    'border-transparent'                          => $activeTab !== $tab,
                                ])
                            >
                                {{ $tab }}
                            </button>
                        </li>
                    @endforeach
                </ul>

                <div x-data="{ selectedProjects: @entangle('selectedProjects') }" class="container mx-auto p-6">

                    <div class="min-h-64 overflow-x-auto rounded-xl border border-gray-200 bg-white pb-8 shadow-sm dark:border-gray-700 dark:bg-gray-800">

                            @if (session()->has('message'))
                                <div class="mb-3 rounded border border-green-200 bg-green-100 p-3 text-green-800 dark:border-green-800 dark:bg-green-900/30 dark:text-green-200">
                                    {{ session('message') }}
                                </div>
                            @endif
                        <table class="min-w-full rounded-lg border border-gray-200 border-collapse dark:border-gray-700">


                            <thead class="bg-gray-100 text-sm dark:bg-gray-900/70">
                                <tr>
                                    {{-- Checkbox maestro --}}
                                    @hasanyrole('admin|estaf')
                                        <th class="border-b border-gray-200 px-4 py-2 text-left font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">
                                            <input
                                                type="checkbox"
                                                wire:model="selectAll"
                                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:focus:ring-blue-400"
                                                @change="selectedProjects = $event.target.checked ? @js($projects->pluck('id')) : []"
                                            />
                                        </th>
                                    @endhasanyrole

                                    {{-- Columna: ID con filtro compacto --}}
                                    <th class="border-b border-gray-200 px-4 py-2 text-left font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">
                                        <div class="flex items-center justify-between">
                                            <span>ID</span>

                                            <div x-data="{ open:false }" class="relative">
                                                <button @click="open = !open" class="rounded p-1 hover:bg-gray-200 dark:hover:bg-gray-700" title="Filtrar ID">⋮</button>
                                                <div
                                                    x-cloak x-show="open" @click.away="open=false" x-transition
                                                    class="absolute z-50 mt-1 w-56 rounded-lg border border-gray-200 bg-white p-3 shadow dark:border-gray-700 dark:bg-gray-800"
                                                >
                                                    <label class="mb-1 block text-xs text-gray-600 dark:text-gray-300">ID Proyecto</label>
                                                    <input
                                                        type="text"
                                                        class="w-full rounded-lg border-gray-300 bg-white text-sm text-gray-900 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:focus:ring-blue-400"
                                                        placeholder="Ej. 101 o 101,102"
                                                        wire:model.live.debounce.400ms="filters.id"
                                                    />

                                                    <div class="mt-3 border-t border-gray-200 pt-2 dark:border-gray-700">
                                                        <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-200">
                                                            <input
                                                                type="checkbox"
                                                                wire:model.live="filters.inactivos"
                                                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-900 dark:focus:ring-blue-400"
                                                            >
                                                            <span>Mostrar solo proyectos inactivos</span>
                                                        </label>
                                                    </div>

                                                    <div class="mt-2 flex justify-end gap-2">
                                                        <button
                                                            type="button"
                                                            class="rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                                                            @click="$wire.set('filters.id','')"
                                                        >Limpiar</button>
                                                        <button
                                                            type="button"
                                                            class="rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                                                            @click="open=false"
                                                        >Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </th>

                                    {{-- Columna: Nombre del Proyecto con filtro compacto --}}
                                    <th class="border-b border-gray-200 px-4 py-2 text-left font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">
                                        <div class="flex items-center justify-between">
                                            <span>Nombre del Proyecto</span>

                                            <div x-data="{ open:false }" class="relative">
                                                <button @click="open = !open" class="rounded p-1 hover:bg-gray-200 dark:hover:bg-gray-700" title="Filtrar Nombre">⋮</button>
                                                <div
                                                    x-cloak x-show="open" @click.away="open=false" x-transition
                                                    class="absolute z-50 mt-1 w-56 rounded-lg border border-gray-200 bg-white p-3 shadow dark:border-gray-700 dark:bg-gray-800"
                                                >
                                                    <label class="mb-1 block text-xs text-gray-600 dark:text-gray-300">Nombre contiene</label>
                                                    <input
                                                        type="text"
                                                        class="w-full rounded-lg border-gray-300 bg-white text-sm text-gray-900 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:focus:ring-blue-400"
                                                        placeholder="Buscar..."
                                                        wire:model.live.debounce.400ms="filters.nombre"
                                                    />
                                                    <div class="mt-2 flex justify-end gap-2">
                                                        <button
                                                            type="button"
                                                            class="rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                                                            @click="$wire.set('filters.nombre','')"
                                                        >Limpiar</button>
                                                        <button
                                                            type="button"
                                                            class="rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                                                            @click="open=false"
                                                        >Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </th>

                                    {{-- Columna: Cliente con filtro compacto --}}
                                    @can('tablaReconfigurarar-ver-todos-los-proyectos')
                                        <th class="border-b border-gray-200 px-4 py-2 text-left font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">
                                            <div class="flex items-center justify-between">
                                                <span>Cliente</span>

                                                <div x-data="{ open:false }" class="relative">
                                                    <button @click="open = !open" class="rounded p-1 hover:bg-gray-200 dark:hover:bg-gray-700" title="Filtrar Cliente">⋮</button>
                                                    <div
                                                        x-cloak x-show="open" @click.away="open=false" x-transition
                                                        class="absolute z-50 mt-1 w-56 rounded-lg border border-gray-200 bg-white p-3 shadow dark:border-gray-700 dark:bg-gray-800"
                                                    >
                                                        <label class="mb-1 block text-xs text-gray-600 dark:text-gray-300">Nombre o correo</label>
                                                        <input
                                                            type="text"
                                                            class="w-full rounded-lg border-gray-300 bg-white text-sm text-gray-900 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:focus:ring-blue-400"
                                                            placeholder="Cliente..."
                                                            wire:model.live.debounce.400ms="filters.cliente"
                                                        />
                                                        <div class="mt-2 flex justify-end gap-2">
                                                            <button
                                                                type="button"
                                                                class="rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                                                                @click="$wire.set('filters.cliente','')"
                                                            >Limpiar</button>
                                                            <button
                                                                type="button"
                                                                class="rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
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
                                        <th class="border-b border-gray-200 px-4 py-2 text-left font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">
                                            Pedidos
                                        </th>
                                    @endcan

                                    {{-- Columna: Estado del Diseño con filtro compacto --}}
                                    <th class="border-b border-gray-200 px-4 py-2 text-left font-medium whitespace-nowrap text-gray-700 dark:border-gray-700 dark:text-gray-200">
                                        <div class="flex items-center justify-between">
                                            <span>Estado Diseño</span>


                                        </div>
                                    </th>

                                    {{-- Columnas adicionales según permisos --}}
                                    @can('tablaReconfigurarar-columna-tareas')
                                        <th class="border-b border-gray-200 px-4 py-2 font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Tareas</th>
                                    @endcan
                                    @can('tablaReconfigurarar-columna-historial')
                                        <th class="border-b border-gray-200 px-4 py-2 font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Historial</th>
                                    @endcan

                                    {{-- Acciones --}}
                                    <th class="border-b border-gray-200 px-4 py-2 text-left font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Acciones</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($projects as $project)
                                    <tr class="bg-white hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700/50">
                                        @hasanyrole('admin|estaf')
                                        <td class="border-b border-gray-200 px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">
                                            <input
                                                type="checkbox"
                                                wire:model="selectedProjects"
                                                value="{{ $project->id }}"
                                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:focus:ring-blue-400"
                                            />
                                        </td>
                                        @endhasanyrole
                                        <td
                                            class="min-w-[6rem] border-b border-gray-200 px-4 py-2 font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-100"
                                            title="{{ $project->nombre ?? 'Proyecto #'.$project->id }}"
                                        >
                                            {!! $project->proyecto_link !!}
                                            {{-- Si prefieres sin accessor, usa:
                                            <a href="{{ route('proyecto.show', $project->id) }}" class="text-blue-600 font-semibold hover:underline">
                                                Proyecto #{{ $project->id }}
                                            </a>
                                            --}}
                                        </td>
                                    
                                        <td class="border-b border-gray-200 px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ $project->nombre }}</td>
                                    
                                        @can('tablaReconfigurarar-ver-todos-los-proyectos')
                                        <td class="border-b border-gray-200 px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ $project->user->name ?? 'Sin Cliente' }}</td>
                                        @endcan

                                        @can('tablaReconfigurarar-ver-columna-pedidos')
                                            <td class="border-b border-gray-200 px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">
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
                                                 
                                                                <button  wire:click="abrirResumenPedidos({{ $project->id }})" class="mt-1 text-xs text-blue-500 hover:underline dark:text-blue-400">
                                                                    Ver más
                                                            </button>
                                                    @else
                                                        <span class="text-gray-500 dark:text-gray-400">Sin pedidos</span>
                                                    @endif


                                                

                                                @else
                                                    <span class="text-gray-500 dark:text-gray-400">Sin pedidos</span>
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
                                                        {{ $colores[$estado] ?? 'bg-gray-300 text-gray-700 dark:bg-gray-700 dark:text-gray-200' }}">
                                                {{ $estado }}
                                            </span>
                                        </td>



                                        @can('tablaReconfigurarar-columna-tareas')
                                            <td class="border border-gray-200 px-4 py-3 dark:border-gray-700">                                                  
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
                                                            <span class="text-sm text-gray-500 dark:text-gray-400">Sin tareas</span>
                                                        @endif
                                            </td>
                                        @endcan
                                        @can('tablaReconfigurarar-columna-historial')
                                            <td class="border border-gray-200 px-4 py-3 dark:border-gray-700">                                                    
                                                @if($project->estados->isNotEmpty())
                                                            <ul class="list-disc list-inside space-y-1 text-xs text-gray-600 dark:text-gray-300">
                                                                @foreach($project->estados->sortByDesc('id')->take(1) as $estado)
                                                                    <li>
                                                                        <strong>{{ $estado->estado }}</strong> 
                                                                        ({{ \Carbon\Carbon::parse($estado->fecha_inicio)->format('d-m-Y H:i') }})
                                                                        por {{ $estado->usuario->name ?? 'Desconocido' }}
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                            @if($project->estados->count() > 2)
                                                                <button wire:click="verMas({{ $project->id }})" class="mt-1 text-xs text-blue-500 hover:underline dark:text-blue-400">
                                                                    Ver más
                                                                </button>
                                                            @endif
                                                        @else
                                                            <span class="text-sm text-gray-500 dark:text-gray-400">Sin historial</span>
                                                        @endif
                                            </td>  
                                        @endcan

                                        
                                        <td class="border border-gray-200 px-4 py-2 text-center space-y-1 dark:border-gray-700">
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
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-lg dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Asignar Tarea</h2>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Usuario</label>
                <select wire:model="selectedUser" class="mb-3 w-full rounded border border-gray-300 bg-white p-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">Seleccione un usuario</option>
                    @foreach($designers as $designer)
                        <option value="{{ $designer->id }}">{{ $designer->name }}</option>
                    @endforeach
                </select>
                @error('selectedUser')
                <div class="mb-3 rounded border border-red-200 bg-red-100 p-3 text-red-800 dark:border-red-800 dark:bg-red-900/30 dark:text-red-200">{{ $message }}</div>
                @enderror

                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Descripción</label>
                <textarea wire:model="taskDescription" class="mb-3 w-full rounded border border-gray-300 bg-white p-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"></textarea>
                @error('taskDescription') <span class="text-sm text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                <div class="flex justify-end space-x-2">
                    <button wire:click="cerrarModal" class="rounded bg-gray-500 px-4 py-2 font-semibold text-white hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-500">
                        Cancelar
                    </button>
                    <button wire:click="asignarTarea" class="rounded bg-blue-500 px-4 py-2 font-semibold text-white hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-500">
                        Asignar
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($modalVerMas && $proyectoSeleccionado)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="max-h-[80vh] w-full max-w-3xl overflow-y-auto rounded-xl bg-white p-6 shadow-lg dark:bg-gray-800">
            <h3 class="mb-4 text-xl font-bold text-gray-900 dark:text-gray-100">Historial de Estatus - Proyecto #{{ $proyectoSeleccionado->id }}</h3>
            <table class="table-auto w-full border text-sm dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-900/70">
                        <th class="border border-gray-200 px-4 py-2 dark:border-gray-700">Estatus</th>
                        <th class="border border-gray-200 px-4 py-2 dark:border-gray-700">Comentario</th>
                        <th class="border border-gray-200 px-4 py-2 dark:border-gray-700">Archivo</th>
                        <th class="border border-gray-200 px-4 py-2 dark:border-gray-700">ID Archivo</th>
                        <th class="border border-gray-200 px-4 py-2 dark:border-gray-700">Fecha</th>
                        <th class="border border-gray-200 px-4 py-2 dark:border-gray-700">Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($proyectoSeleccionado->estados->sortByDesc('id') as $estado)
                        <tr class="dark:text-gray-200">
                            <td class="border border-gray-200 px-4 py-2 dark:border-gray-700">{{ $estado->estado }}</td>
                            <td class="border border-gray-200 px-4 py-2 dark:border-gray-700">{{ $estado->comentario ?? '-' }}</td>
                            <td class="border border-gray-200 px-4 py-2 dark:border-gray-700">
                                @if($estado->url)
                                    <a href="{{ asset('storage/' . $estado->url) }}" target="_blank" class="text-blue-600 underline dark:text-blue-400">Ver archivo</a>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">No disponible</span>
                                @endif
                            </td>
                            <td class="border border-gray-200 px-4 py-2 text-center dark:border-gray-700">{{ $estado->last_uploaded_file_id ?? '-' }}</td>
                            <td class="border border-gray-200 px-4 py-2 dark:border-gray-700">{{ \Carbon\Carbon::parse($estado->fecha_inicio)->format('d-m-Y H:i') }}</td>
                            <td class="border border-gray-200 px-4 py-2 dark:border-gray-700">{{ $estado->usuario->name ?? 'Desconocido' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4 text-right">
                <button wire:click="cerrarModalVerMas" class="rounded bg-gray-500 px-4 py-2 text-white hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-500">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
    @endif


    {{-- Modal: Resumen de pedidos --}}
    @if($modalResumen)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="mx-4 w-full max-w-2xl rounded-xl bg-white shadow-lg dark:bg-gray-800">
            <div class="flex items-center justify-between border-b border-gray-200 p-4 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Resumen de pedidos · Proyecto #{{ $proyectoResumen?->id ?? $proyectoResumenId }}
                </h3>
                <button wire:click="cerrarResumenPedidos" class="text-xl leading-none text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">✕</button>
            </div>

            <div class="p-4 space-y-4">
                {{-- Último pedido pendiente --}}
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900/60">
                    <h4 class="mb-2 text-sm font-bold text-gray-900 dark:text-gray-100">Último pedido POR APROBAR</h4>

                    @if($ultimoPedidoPendiente)
                        <div class="grid grid-cols-1 gap-3 text-sm text-gray-700 dark:text-gray-200 sm:grid-cols-2">
                            <div><span class="font-semibold">ID:</span> {{ $ultimoPedidoPendiente->id }}</div>
                            <div><span class="font-semibold">Fecha:</span> {{ optional($ultimoPedidoPendiente->created_at)->format('Y-m-d H:i') }}</div>
                            <div><span class="font-semibold">Producto:</span> {{ $ultimoPedidoPendiente->producto->nombre ?? '—' }}</div>
                            <div><span class="font-semibold">Categoría:</span> {{ $ultimoPedidoPendiente->producto->categoria->nombre ?? '—' }}</div>
                            <div><span class="font-semibold">Total:</span> {{ $ultimoPedidoPendiente->total }}</div>
                            <div><span class="font-semibold">Estatus:</span> {{ $ultimoPedidoPendiente->estado }}</div>
                        </div>
                    @else
                        <p class="text-sm italic text-gray-600 dark:text-gray-400">Sin pedidos pendientes.</p>
                    @endif
                </div>

                {{-- Lista compacta (últimos 5 pedidos) --}}
                <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-200 px-4 py-2 dark:border-gray-700">
                        <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">Últimos pedidos (5)</h4>
                    </div>
                    <div class="p-2 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100 dark:bg-gray-900/70">
                                <tr>
                                    <th class="px-3 py-2 text-left">ID</th>
                                    <th class="px-3 py-2 text-left">Producto</th>
                                    <th class="px-3 py-2 text-left">Categoría</th>
                                    <th class="px-3 py-2 text-left">Total</th>
                                    <th class="px-3 py-2 text-left">Estatus</th>
                                    <th class="px-3 py-2 text-left">Fecha</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700 dark:text-gray-200">
                                @forelse($ultimosPedidos as $p)
                                    <tr class="border-b border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/50">
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
                                                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' => !in_array($p->estado, ['PENDIENTE','PROGRAMADO','APROBADO','POR PROGRAMAR','CANCELADO']),
                                                ])
                                            ">
                                                {{ $p->estado }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2">{{ optional($p->created_at)->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-3 py-3 text-center text-gray-500 dark:text-gray-400">Sin pedidos.</td>
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
                    class="text-sm text-blue-600 hover:underline dark:text-blue-400">
                        Ver más en la página del proyecto
                    </a>

                    <div class="text-right">
                        <button wire:click="cerrarResumenPedidos"
                                class="rounded bg-gray-600 px-4 py-2 text-sm text-white hover:bg-gray-700 dark:bg-gray-500 dark:hover:bg-gray-400">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

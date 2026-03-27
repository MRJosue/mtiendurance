<div 
    x-data="{
        abierto: JSON.parse(localStorage.getItem('dashboard_preproyectos_abierto') ?? 'true'),
        toggle() {
            this.abierto = !this.abierto;
            localStorage.setItem('dashboard_preproyectos_abierto', JSON.stringify(this.abierto));
        }
    }"
    class="dashboard-data-widget container mx-auto p-6"
>
    <h2 
        @click="toggle()"
        class="dashboard-data-widget__title cursor-pointer"
    >
        Solicitudes de Proyectos
        <span class="dashboard-data-widget__subtitle" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
    {{-- </h2>
        Mis preproyectos
        <span class="text-sm text-gray-500 ml-2" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
    </h2> --}}
    </h2>
    <!-- Contenido del panel -->
    <div x-show="abierto" x-transition>
        <!-- Botones de acción -->


        <!-- Tabla -->
        <div class="dashboard-table-shell">
            <table class="dashboard-table min-w-full">
                <thead class="dashboard-table-head">
                    <tr>
                        {{-- <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">
                            <input
                                type="checkbox"
                                wire:model="selectAll"
                                @change="selectedProjects = $event.target.checked ? @js($projects->pluck('id')) : []"
                            />
                        </th> --}}
                        <th class="dashboard-table-th border-b border-gray-200 dark:border-gray-700">ID</th>
                        <th class="dashboard-table-th border-b border-gray-200 dark:border-gray-700">Nombre del Preoyecto</th>
                        <th class="dashboard-table-th border-b border-gray-200 dark:border-gray-700">Producto</th>
                        <th class="dashboard-table-th border-b border-gray-200 dark:border-gray-700">Fecha de creacion</th>
                        <th class="dashboard-table-th border-b border-gray-200 dark:border-gray-700">Estado</th>
                        <th class="dashboard-table-th border-b border-gray-200 dark:border-gray-700">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $project)
                        <tr>
                            {{-- <td class="border-b px-4 py-2 text-gray-700 text-sm">
                                <input
                                    type="checkbox"
                                    wire:model="selectedProjects"
                                    value="{{ $project->id }}"
                                />
                            </td> --}}
                            <td class="border-b border-gray-200 px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ $project->id }}</td>
                            <td class="border-b border-gray-200 px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ $project->nombre }}</td>
                            <td class="border-b border-gray-200 px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ collect(json_decode($project->producto_sel, true))->get('nombre', '-') }}</td>
                            <td class="border-b border-gray-200 px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">
                                {{ $project->created_at->format('Y-m-d H:i:s') }} UTC
                            </td>
                        
                            <td class="border-b border-gray-200 px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ $project->estado }}</td>
                            <td class="border-b border-gray-200 px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">
                                <a href="{{ route('preproyectos.show', $project->id) }}" class="dashboard-text-link">
                                    Ver detalles
                                </a>
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

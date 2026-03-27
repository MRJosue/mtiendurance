<div 
    x-data="{
        abierto: JSON.parse(localStorage.getItem('dashboard_preproyectos_abierto') ?? 'true'),
        toggle() {
            this.abierto = !this.abierto;
            localStorage.setItem('dashboard_preproyectos_abierto', JSON.stringify(this.abierto));
        }
    }"
    class="dashboard-data-widget flex h-full min-h-0 flex-col p-2 sm:p-3"
>
    <h2 
        @click="toggle()"
        class="dashboard-data-widget__title cursor-pointer"
    >
        {{ __('preprojects.title') }}
        <span class="dashboard-data-widget__subtitle" x-text="abierto ? @js(__('preprojects.hide')) : @js(__('preprojects.show'))"></span>
    {{-- </h2>
        Mis preproyectos
        <span class="text-sm text-gray-500 ml-2" x-text="abierto ? @js(__('preprojects.hide')) : @js(__('preprojects.show'))"></span>
    </h2> --}}
    </h2>
    <!-- Contenido del panel -->
    <div x-show="abierto" x-transition>

        @can('boton-crear-preproyecto')
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <a class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-white transition hover:bg-blue-700 sm:w-auto" 
                    href="{{ route('preproyectos.create') }}">{{ __('preprojects.create_new') }}</a>
        </div>
        @endcan

        <div x-data="{ selectedProjects: @entangle('selectedProjects') }" class="container mx-auto p-6">
            <!-- Botones de acción -->
            <div class="mb-4 flex flex-wrap space-y-2 sm:space-y-0 sm:space-x-4">
                {{-- <button
                    class="w-full sm:w-auto px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="selectedProjects.length === 0"
                    wire:click="exportSelected"
                >
                    Exportar Seleccionados
                </button>
                <button
                    class="w-full sm:w-auto px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="selectedProjects.length === 0"
                    wire:click="deleteSelected"
                >
                    Eliminar Seleccionados
                </button> --}}
            </div>

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
                            <th class="dashboard-table-th border-b border-gray-200 dark:border-gray-700">{{ __('preprojects.project_name') }}</th>
                            @can('tablaPreproyectos-ver-todos-los-preproyectos')
                            <th class="dashboard-table-th border-b border-gray-200 dark:border-gray-700">{{ __('preprojects.user') }}</th>
                            @endcan
                            <th class="dashboard-table-th border-b border-gray-200 dark:border-gray-700">{{ __('preprojects.status') }}</th>
                            <th class="dashboard-table-th border-b border-gray-200 dark:border-gray-700">{{ __('preprojects.actions') }}</th>
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
                                
                                @can('tablaPreproyectos-ver-todos-los-preproyectos')
                                <td class="border-b border-gray-200 px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ $project->user->name ?? __('preprojects.no_user') }}</td>
                                @endcan
                            
                                <td class="border-b border-gray-200 px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ $project->estado }}</td>
                                <td class="border-b border-gray-200 px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">
                                    <a href="{{ route('preproyectos.show', $project->id) }}" class="dashboard-text-link">
                                        {{ __('preprojects.view_details') }}
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
</div>

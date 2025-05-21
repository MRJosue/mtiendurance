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
        <!-- Botones de acción -->


        <!-- Tabla -->
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full border-collapse border border-gray-200 rounded-lg">
                <thead class="bg-gray-100">
                    <tr>
                        {{-- <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">
                            <input
                                type="checkbox"
                                wire:model="selectAll"
                                @change="selectedProjects = $event.target.checked ? @js($projects->pluck('id')) : []"
                            />
                        </th> --}}
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">ID</th>
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Nombre del Proyecto</th>
                        {{-- <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Usuario</th> --}}
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Producto</th>
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Fecha de creacion</th>
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Estado</th>
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $project)
                        <tr class="hover:bg-gray-50">
                            {{-- <td class="border-b px-4 py-2 text-gray-700 text-sm">
                                <input
                                    type="checkbox"
                                    wire:model="selectedProjects"
                                    value="{{ $project->id }}"
                                />
                            </td> --}}
                            <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $project->id }}</td>
                            <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $project->nombre }}</td>
                            {{-- <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $project->user->name ?? 'Sin usuario' }}</td> --}}
                            <td class="border-b px-4 py-2 text-gray-700 text-sm">
                                {{ collect(json_decode($project->producto_sel, true))->get('nombre', '-') }}
                            </td>
                            <td class="border-b px-4 py-2 text-gray-700 text-sm">  {{ $project->created_at->format('Y-m-d H:i:s') }} UTC</td>
                            <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $project->estado ?? 'Sin estado' }}</td>
                            <td class="border-b px-4 py-2 text-gray-700 text-sm">
                                <a href="{{ route('proyecto.show', $project->id) }}" class="text-blue-500 hover:underline">
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

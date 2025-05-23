<div class="space-y-4 max-h-[85vh] overflow-y-auto pr-1">

    <!-- Botones principales -->
    <div class="flex justify-between items-center">
        <button wire:click="abrirCrearGrupo" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            + Crear Grupo de Permisos
        </button>

        <button wire:click="$set('modalCrearPermiso', true)" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            + Nuevo Permiso
        </button>
    </div>

    <!-- Listado de grupos (en grid de 5 columnas con scroll interno) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
        @foreach($grupos as $grupo)
            <div class="border rounded-lg shadow bg-white">
                <div class="flex justify-between items-center bg-gray-100 px-4 py-2 font-semibold">
                    <span>{{ $grupo->nombre }}</span>
                    <div class="flex items-center gap-2">
                        <button wire:click="asignarPermisosGrupo({{ $grupo->id }})"
                            class="text-green-600 hover:underline text-sm flex items-center gap-1">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                        <button wire:click="revocarPermisosGrupo({{ $grupo->id }})"
                            class="text-red-600 hover:underline text-sm flex items-center gap-1">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" />
                            </svg>
                        </button>
                        <button wire:click="editarGrupo({{ $grupo->id }})" class="text-blue-600 hover:underline text-sm">Editar</button>
                    </div>
                </div>
                <div class="p-4 space-y-2 max-h-64 overflow-y-auto">
                    @forelse($grupo->permissions as $permiso)
                        <div class="flex justify-between items-center border p-2 rounded">
                            <span class="text-sm">{{ $permiso->name }}</span>
                            <button
                                wire:click="quitarPermiso({{ $grupo->id }}, {{ $permiso->id }})"
                                class="text-red-500 hover:text-red-700 text-xs"
                            >
                                Quitar
                            </button>
                        </div>
                    @empty
                        <div class="text-gray-500 text-sm">Sin permisos</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>


    <!-- Modal crear/editar grupo -->
    @if($modalCrearGrupo)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-7xl h-[90vh] overflow-y-auto">
                <h2 class="text-2xl font-bold mb-4">{{ $grupoEditarId ? 'Editar Grupo' : 'Nuevo Grupo' }}</h2>

                <input type="text" wire:model.defer="grupoNombre" placeholder="Nombre del grupo" class="w-full mb-4 border p-2 rounded">
                @error('grupoNombre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                <h3 class="font-semibold mb-2">Selecciona permisos:</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 max-h-[400px] overflow-y-auto">
                    @foreach($permisosTodos as $permiso)
                        <div class="overflow-x-auto">
                            <label class="flex items-center space-x-2 text-sm whitespace-nowrap min-w-full pr-2">
                                <input type="checkbox" value="{{ $permiso->id }}" wire:model="permisosSeleccionados">
                                <span>{{ $permiso->name }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-end mt-6 space-x-2">
                    <button wire:click="$set('modalCrearGrupo', false)" class="bg-gray-300 px-4 py-2 rounded">Cancelar</button>
                    <button wire:click="guardarGrupo" class="bg-blue-600 text-white px-4 py-2 rounded">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif


    <!-- Modal crear permiso -->
    @if($modalCrearPermiso)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-lg">
                <h2 class="text-xl font-bold mb-4">Crear nuevo permiso</h2>

                <input type="text" wire:model.defer="nuevoPermiso" placeholder="Nombre del permiso" class="w-full mb-3 border p-2 rounded">
                @error('nuevoPermiso') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                <div class="flex justify-end space-x-2">
                    <button wire:click="$set('modalCrearPermiso', false)" class="bg-gray-300 px-4 py-2 rounded">Cancelar</button>
                    <button wire:click="crearPermiso" class="bg-green-600 text-white px-4 py-2 rounded">Crear</button>
                </div>
            </div>
        </div>
    @endif
</div>

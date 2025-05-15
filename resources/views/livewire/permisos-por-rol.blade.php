<div class="space-y-4">

    <!-- Botones principales -->
    <div class="flex justify-between items-center">
        <button wire:click="abrirCrearGrupo" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            + Crear Grupo de Permisos
        </button>

        <button wire:click="$set('modalCrearPermiso', true)" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            + Nuevo Permiso
        </button>
    </div>

    <!-- Listado de grupos -->
    @foreach($grupos as $grupo)
        <div class="border rounded-lg shadow">
            <div class="flex justify-between items-center bg-gray-100 px-4 py-2 font-semibold">
                <span>{{ $grupo->nombre }}</span>
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
            <div class="p-4 space-y-2">
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

    <!-- Modal crear/editar grupo -->
    @if($modalCrearGrupo)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
                <h2 class="text-xl font-bold mb-4">{{ $grupoEditarId ? 'Editar Grupo' : 'Nuevo Grupo' }}</h2>

                <input type="text" wire:model.defer="grupoNombre" placeholder="Nombre del grupo" class="w-full mb-4 border p-2 rounded">
                @error('grupoNombre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                <h3 class="font-semibold mb-2">Selecciona permisos:</h3>
                <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto">
                    @foreach($permisosTodos as $permiso)
                        <label class="flex items-center space-x-2 text-sm">
                            <input type="checkbox" value="{{ $permiso->id }}" wire:model="permisosSeleccionados">
                            <span>{{ $permiso->name }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="flex justify-end mt-4 space-x-2">
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
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
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

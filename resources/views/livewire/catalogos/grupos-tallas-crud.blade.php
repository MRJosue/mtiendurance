<div class="container mx-auto p-6 text-gray-900 dark:text-gray-100">
    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center space-x-2">
            <button wire:click="openModal"
                class="rounded-lg bg-blue-500 px-4 py-2 text-white hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-500">
                Nuevo Grupo
            </button>
        </div>
    
        <div class="flex flex-col gap-2 sm:flex-row">

            <input type="text" wire:model="query" placeholder="Buscar por nombre..." class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder-gray-400 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
            <select wire:model="filtroActivo" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
            <button wire:click="buscar" class="rounded-lg bg-gray-500 px-4 py-2 text-white hover:bg-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600">
                Buscar
            </button>
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
        <table class="min-w-full border-collapse">
            <thead class="bg-gray-100 dark:bg-gray-900/70">
                <tr>
                    <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Nombre</th>
                    <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Tallas Asignadas</th>
                    <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($grupos as $grupo)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="border-b border-gray-200 px-4 py-2 text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ $grupo->nombre }}</td>
                        <td class="border-b border-gray-200 px-4 py-2 text-gray-700 dark:border-gray-700 dark:text-gray-200">
                            {{ implode(', ', $grupo->tallas->pluck('nombre')->toArray()) }}
                        </td>
                        <td class="border-b border-gray-200 px-4 py-2 text-gray-700 dark:border-gray-700 dark:text-gray-200">
                            <button wire:click="edit({{ $grupo->id }})" 
                                class="mr-2 text-blue-500 hover:underline dark:text-blue-400">Editar</button>
                            <button wire:click="confirmDelete({{ $grupo->id }})" 
                                class="text-red-500 hover:underline dark:text-red-400">Eliminar</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $grupos->links() }}
    </div>

    @if($modalOpen)
        <div class="fixed inset-0 flex items-center justify-center bg-black/60 p-4">
            <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-lg dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-bold">{{ $grupo_id ? 'Editar' : 'Nuevo' }} Grupo de Tallas</h2>
                <input type="text" wire:model="nombre" placeholder="Nombre del grupo"
                    class="mb-2 w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" />
                <div class="mb-2 flex items-center space-x-2">
                    <input type="checkbox" class="form-checkbox h-5 w-5 rounded border-gray-300 text-blue-600 dark:border-gray-600 dark:bg-gray-900" wire:model="ind_activo">
                    <label class="select-none font-medium text-gray-700 dark:text-gray-300">Grupo activo</label>
                </div>

                <div class="mb-4">
                    <label class="mb-2 block font-bold text-gray-900 dark:text-gray-100">Asignar Tallas:</label>
                    @foreach($tallasDisponibles as $talla)
                        <label class="mr-4 inline-flex items-center text-gray-700 dark:text-gray-300">
                            <input type="checkbox" wire:model="selectedTallas" value="{{ $talla->id }}" class="form-checkbox rounded border-gray-300 text-blue-600 dark:border-gray-600 dark:bg-gray-900">
                            <span class="ml-2">{{ $talla->nombre }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="flex justify-end space-x-2">
                    <button wire:click="$set('modalOpen', false)" 
                        class="rounded-lg bg-gray-500 px-4 py-2 text-white hover:bg-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600">
                        Cancelar
                    </button>
                    <button wire:click="save" 
                        class="rounded-lg bg-green-500 px-4 py-2 text-white hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-500">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($confirmingDelete)
        <div class="fixed inset-0 flex items-center justify-center bg-black/60 p-4">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-bold">¿Eliminar este grupo?</h2>
                <div class="flex justify-end space-x-2">
                    <button wire:click="$set('confirmingDelete', false)" 
                        class="rounded-lg bg-gray-500 px-4 py-2 text-white hover:bg-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600">
                        Cancelar
                    </button>
                    <button wire:click="delete" 
                        class="rounded-lg bg-red-500 px-4 py-2 text-white hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-500">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<div class="max-w-4xl mx-auto p-4 text-gray-900 dark:text-gray-100">
    <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-gray-100">Gestión de Categorías</h2>

    @if (session()->has('message'))
        <div class="mb-3 rounded border border-emerald-200 bg-emerald-50 p-3 text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-3 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <button wire:click="crear" class="rounded bg-blue-500 px-4 py-2 font-semibold text-white hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-500">
            Nueva Categoría
        </button>

        <div class="flex flex-col gap-3 sm:flex-row">
            <input type="text" wire:model="query" placeholder="Buscar por nombre..." class="rounded border border-gray-300 bg-white px-4 py-2 text-gray-900 placeholder-gray-400 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
            <select wire:model="filtroActivo" class="rounded border border-gray-300 bg-white px-2 py-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                <option value="1">Activas</option>
                <option value="0">Inactivas</option>
            </select>
            <button wire:click="buscar" class="rounded bg-gray-500 px-4 py-2 font-semibold text-white hover:bg-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600">
                Buscar
            </button>
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-900/70">
                    <th class="border border-gray-300 p-2 text-left dark:border-gray-700">Nombre</th>
                    <th class="border border-gray-300 p-2 text-center dark:border-gray-700">Formulario de tallas</th>
                    <th class="border border-gray-300 p-2 text-center dark:border-gray-700">Características</th>
                    <th class="border border-gray-300 p-2 text-center dark:border-gray-700">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categorias as $cat)
                    <tr class="dark:hover:bg-gray-700/40">
                        <td class="border border-gray-300 p-2 dark:border-gray-700">{{ $cat->nombre }}</td>
                        <td class="border border-gray-300 p-2 text-center dark:border-gray-700">
                            @if($cat->flag_tallas)
                                ✅
                            @else
                                ❌
                            @endif
                        </td>

                        <td class="border border-gray-300 p-2 dark:border-gray-700">
                            @if($cat->caracteristicas->isNotEmpty())
                                <ul class="list-disc list-inside">
                                    @foreach($cat->caracteristicas as $caracteristica)
                                        <li class="text-gray-600 dark:text-gray-300">{{ $caracteristica->nombre }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-gray-500 dark:text-gray-400">Sin características</span>
                            @endif
                        </td>
                        
                        <td class="border border-gray-300 p-2 dark:border-gray-700">
                            <div class="flex justify-center space-x-2">
                                <button wire:click="editar('{{ $cat->id }}')" class="rounded bg-yellow-500 px-3 py-1 font-semibold text-white hover:bg-yellow-600 dark:bg-yellow-600 dark:hover:bg-yellow-500">
                                    Editar
                                </button>
                                <button wire:click="borrar('{{ $cat->id }}')" class="rounded bg-red-500 px-3 py-1 font-semibold text-white hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-500" onclick="return confirm('¿Estás seguro de eliminar esta categoría?')">
                                    Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $categorias->links() }}
    </div>

    @if($modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
            <div class="w-full max-w-md rounded bg-white shadow-lg dark:bg-gray-800">
                <div class="flex items-center justify-between border-b border-gray-200 p-4 dark:border-gray-700">
                    <h5 class="text-xl font-bold">{{ $categoria_id ? 'Editar Categoría' : 'Crear Nueva Categoría' }}</h5>
                    <button class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" wire:click="cerrarModal">&times;</button>
                </div>

                <div class="p-4">
                    <div class="mb-4 flex flex-col space-y-4 sm:flex-row sm:items-end sm:space-x-4 sm:space-y-0">
                        <div class="w-full sm:w-3/4">
                            <label class="mb-1 block text-gray-700 dark:text-gray-300">Nombre de la Categoría</label>
                            <input
                                type="text"
                                class="w-full rounded border border-gray-300 bg-white p-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                wire:model="nombre"
                                @if($nombreReadonly) readonly title="No se puede editar el nombre porque la categoría está asociada a más de un producto." @endif
                            >
                            @error('nombre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    
                        <div class="mt-2 flex w-full items-center space-x-2 sm:mt-6 sm:w-1/4">
                            <input type="checkbox" class="form-checkbox h-5 w-5 rounded border-gray-300 text-blue-600 dark:border-gray-600 dark:bg-gray-900" wire:model="ind_activo">
                            <label class="select-none font-medium text-gray-700 dark:text-gray-300">Estado activo</label>
                        </div>
                    </div>

                    <div class="mb-4 flex items-center">
                        <input type="checkbox" class="mr-2 rounded border-gray-300 text-blue-600 dark:border-gray-600 dark:bg-gray-900" wire:model="flag_tallas">
                        <label class="text-gray-700 dark:text-gray-300">Captura de tallas</label>
                    </div>

                    <div class="mb-4">
                        <label class="mb-1 block text-gray-700 dark:text-gray-300">Características</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($caracteristicas as $caracteristica)
                                <label class="flex items-center text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" wire:model="caracteristicasSeleccionadas" value="{{ $caracteristica->id }}" class="mr-2 rounded border-gray-300 text-blue-600 dark:border-gray-600 dark:bg-gray-900">
                                    {{ $caracteristica->nombre }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                </div>
                <div class="flex items-center justify-end border-t border-gray-200 p-4 space-x-2 dark:border-gray-700">
                    <button wire:click="cerrarModal" class="rounded bg-gray-200 px-4 py-2 font-semibold text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600">
                        Cancelar
                    </button>
                    <button wire:click="guardar" class="rounded bg-blue-500 px-4 py-2 font-semibold text-white hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-500">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

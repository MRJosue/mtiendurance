<div class="max-w-4xl mx-auto p-4 text-gray-900 dark:text-gray-100">
    <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-gray-100">Gestión de Características</h2>

    @if (session()->has('message'))
        <div class="mb-3 rounded border border-emerald-200 bg-emerald-50 p-3 text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-3 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <button wire:click="crear" class="mb-3 rounded bg-blue-500 px-4 py-2 font-semibold text-white hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-500">
            Nueva Característica
        </button>
        <div class="flex flex-col gap-2 sm:flex-row">
            <input type="text" wire:model="query" placeholder="Buscar por nombre..." class="rounded border border-gray-300 bg-white px-4 py-2 text-gray-900 placeholder-gray-400 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
            <select wire:model="filtroActivo" class="rounded border border-gray-300 bg-white px-4 py-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
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
                    <th class="border border-gray-300 p-2 text-left dark:border-gray-700">Opciones</th>
                    <th class="border border-gray-300 p-2 text-center dark:border-gray-700">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($caracteristicas as $car)
                    <tr class="dark:hover:bg-gray-700/40">
                        <td class="border border-gray-300 p-2 dark:border-gray-700">{{ $car->nombre }}</td>

                        <td class="border border-gray-300 p-2 dark:border-gray-700">
                            @if ($car->opciones->isNotEmpty())
                                <ul>
                                    @foreach ($car->opciones as $opcion)
                                        <li class="dark:text-gray-300">{{ $opcion->nombre }}</li>
                                    @endforeach
                                </ul>
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="border border-gray-300 p-2 text-center dark:border-gray-700">
                            <button wire:click="editar('{{ $car->id }}')" class="rounded bg-yellow-500 px-3 py-1 font-semibold text-white hover:bg-yellow-600 dark:bg-yellow-600 dark:hover:bg-yellow-500">
                                Editar
                            </button>
                            <button wire:click="borrar('{{ $car->id }}')" class="rounded bg-red-500 px-3 py-1 font-semibold text-white hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-500" onclick="return confirm('¿Estás seguro de eliminar esta característica?')">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $caracteristicas->links() }}
    </div>

    @if($modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
            <div class="w-full max-w-md rounded bg-white shadow-lg dark:bg-gray-800">
                <div class="flex items-center justify-between border-b border-gray-200 p-4 dark:border-gray-700">
                    <h5 class="text-xl font-bold">{{ $caracteristica_id ? 'Editar Característica' : 'Crear Nueva Característica' }}</h5>
                    <button class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" wire:click="cerrarModal">&times;</button>
                </div>
                <div class="max-h-[80vh] overflow-y-auto p-4">
                    <div class="mb-4">
                        <label class="mb-1 block text-gray-700 dark:text-gray-300">Nombre</label>
                        <input type="text" class="w-full rounded border border-gray-300 bg-white p-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" wire:model="nombre">
                        @error('nombre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4 flex items-center space-x-2">
                        <input type="checkbox" class="form-checkbox h-5 w-5 rounded border-gray-300 text-blue-600 dark:border-gray-600 dark:bg-gray-900" wire:model="ind_activo">
                        <label class="select-none font-medium text-gray-700 dark:text-gray-300">Característica activa</label>
                    </div>
                    

                    <div class="mb-4">
                        <label class="mb-1 block text-gray-700 dark:text-gray-300">Opciones</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach($opciones as $opcion)
                                <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" wire:model="opcion_id" value="{{ $opcion->id }}" class="rounded border-gray-300 text-blue-600 dark:border-gray-600 dark:bg-gray-900">
                                    <span>{{ $opcion->nombre }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('opcion_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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

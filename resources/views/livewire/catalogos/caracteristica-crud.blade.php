<div class="max-w-4xl mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Gestión de Características</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-3">
            {{ session('message') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-3 space-x-2">
        <button wire:click="crear" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded mb-3">
            Nueva Característica
        </button>
        <div class="flex space-x-2">
            <input type="text" wire:model="query" placeholder="Buscar por nombre..." class="border border-gray-300 rounded px-4 py-2">
            <select wire:model="filtroActivo" class="border border-gray-300 rounded px-4 py-2">
                <option value="1">Activas</option>
                <option value="0">Inactivas</option>
            </select>
            <button wire:click="buscar" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded">
                Buscar
            </button>
        </div>
    </div>

    <table class="w-full border-collapse border border-gray-300">
        <thead>
            <tr class="bg-gray-100">
                <th class="border border-gray-300 p-2 text-left">Nombre</th>
             
                <th class="border border-gray-300 p-2 text-left">Opciones</th>
                <th class="border border-gray-300 p-2 text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($caracteristicas as $car)
                <tr>
                    <td class="border border-gray-300 p-2">{{ $car->nombre }}</td>

                    <td class="border border-gray-300 p-2">
                        @if ($car->opciones->isNotEmpty())
                            <ul>
                                @foreach ($car->opciones as $opcion)
                                    <li>{{ $opcion->nombre }}</li>
                                @endforeach
                            </ul>
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="border border-gray-300 p-2 text-center">
                        <button wire:click="editar('{{ $car->id }}')" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-3 py-1 rounded">
                            Editar
                        </button>
                        <button wire:click="borrar('{{ $car->id }}')" class="bg-red-500 hover:bg-red-600 text-white font-semibold px-3 py-1 rounded" onclick="return confirm('¿Estás seguro de eliminar esta característica?')">
                            Eliminar
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $caracteristicas->links() }}
    </div>

    @if($modal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded shadow-lg w-full max-w-md">
                <div class="flex items-center justify-between border-b border-gray-200 p-4">
                    <h5 class="text-xl font-bold">{{ $caracteristica_id ? 'Editar Característica' : 'Crear Nueva Característica' }}</h5>
                    <button class="text-gray-500 hover:text-gray-700" wire:click="cerrarModal">&times;</button>
                </div>
                <div class="p-4 max-h-[80vh] overflow-y-auto">
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-1">Nombre</label>
                        <input type="text" class="w-full border border-gray-300 rounded p-2" wire:model="nombre">
                        @error('nombre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4 flex items-center space-x-2">
                        <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600" wire:model="ind_activo">
                        <label class="text-gray-700 font-medium select-none">Característica activa</label>
                    </div>
                    

                    <div class="mb-4">
                        <label class="block text-gray-700 mb-1">Opciones</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach($opciones as $opcion)
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" wire:model="opcion_id" value="{{ $opcion->id }}">
                                    <span>{{ $opcion->nombre }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('opcion_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="flex items-center justify-end border-t border-gray-200 p-4 space-x-2">
                    <button wire:click="cerrarModal" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-4 py-2 rounded">
                        Cancelar
                    </button>
                    <button wire:click="guardar" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

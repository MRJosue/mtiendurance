<div class="max-w-4xl mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Gestión de Categorías</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-3">
            {{ session('message') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-3 space-x-2">
        <button wire:click="crear" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
            Nueva Categoría
        </button>
        <div class="flex space-x-2">
            <input type="text" wire:model="query" placeholder="Buscar por nombre..." class="border border-gray-300 rounded px-4 py-2">
            <button wire:click="buscar" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded">
                Buscar
            </button>
        </div>
    </div>

    <table class="w-full border-collapse border border-gray-300">
        <thead>
            <tr class="bg-gray-100">
                <th class="border border-gray-300 p-2 text-left">Nombre</th>
                <th class="border border-gray-300 p-2 text-center">Formulario de tallas</th>
                <th class="border border-gray-300 p-2 text-center">Características</th> 
                <th class="border border-gray-300 p-2 text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categorias as $cat)
                <tr>
                    <td class="border border-gray-300 p-2">{{ $cat->nombre }}</td>
                    <td class="border border-gray-300 p-2 text-center">
                        @if($cat->flag_tallas)
                            ✅
                        @else
                            ❌
                        @endif
                    </td>

                    <td class="border border-gray-300 p-2">
                        @if($cat->caracteristicas->isNotEmpty())
                            <ul class="list-disc list-inside">
                                @foreach($cat->caracteristicas as $caracteristica)
                                    <li class="text-gray-600">{{ $caracteristica->nombre }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-gray-500">Sin características</span>
                        @endif
                    </td>
                    
                    <td class="border border-gray-300 p-2 flex space-x-2 justify-center">
                        <button wire:click="editar('{{ $cat->id }}')" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-3 py-1 rounded">
                            Editar
                        </button>
                        <button wire:click="borrar('{{ $cat->id }}')" class="bg-red-500 hover:bg-red-600 text-white font-semibold px-3 py-1 rounded" onclick="return confirm('¿Estás seguro de eliminar esta categoría?')">
                            Eliminar
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $categorias->links() }}
    </div>

    @if($modal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded shadow-lg w-full max-w-md">
                <div class="flex items-center justify-between border-b border-gray-200 p-4">
                    <h5 class="text-xl font-bold">{{ $categoria_id ? 'Editar Categoría' : 'Crear Nueva Categoría' }}</h5>
                    <button class="text-gray-500 hover:text-gray-700" wire:click="cerrarModal">&times;</button>
                </div>
                <div class="p-4">
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-1">Nombre</label>
                        <input type="text" class="w-full border border-gray-300 rounded p-2" wire:model="nombre">
                        @error('nombre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Checkbox para Flag Tallas -->
                    <div class="mb-4 flex items-center">
                        <input type="checkbox" class="mr-2" wire:model="flag_tallas">
                        <label class="text-gray-700">Captura de tallas</label>
                    </div>


                        <!-- Relación con Características -->
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-1">Características</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($caracteristicas as $caracteristica)
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model="caracteristicasSeleccionadas" value="{{ $caracteristica->id }}" class="mr-2">
                                    {{ $caracteristica->nombre }}
                                </label>
                            @endforeach
                        </div>
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

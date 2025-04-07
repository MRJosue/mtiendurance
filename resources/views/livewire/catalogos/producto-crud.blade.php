<div class="max-w-6xl mx-auto p-4">
    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-3">
            {{ session('message') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-3 space-x-2">
        <button wire:click="crear" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded mb-3">
            Nuevo Producto
        </button>
        <div class="flex space-x-2">
            <input type="text" wire:model="query" placeholder="Buscar por nombre..." class="border border-gray-300 rounded px-4 py-2">
            <select wire:model="categoriaFiltro" class="border border-gray-300 rounded px-4 py-2">
                <option value="">Todas las categorías</option>
                @foreach ($categorias as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                @endforeach
            </select>
            <select wire:model="filtroActivo" class="border border-gray-300 rounded px-4 py-2">
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
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
                <th class="border border-gray-300 p-2 text-left">Días de Producción</th>
                <th class="border border-gray-300 p-2 text-left">Armado</th>
                <th class="border border-gray-300 p-2 text-left">Categoría</th>
                <th class="border border-gray-300 p-2 text-left">Características</th>
                <th class="border border-gray-300 p-2 text-left">Grupos de Tallas</th>
                <th class="border border-gray-300 p-2 text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productos as $prod)
                <tr>
                    <td class="border border-gray-300 p-2">{{ $prod->nombre }}</td>
                    <td class="border border-gray-300 p-2">{{ $prod->dias_produccion }}</td>
                    <td class="border border-gray-300 p-2">{{ $prod->flag_armado ? 'Sí' : 'No' }}</td>
                    <td class="border border-gray-300 p-2">{{ $prod->categoria ? $prod->categoria->nombre : 'Sin categoría' }}</td>

                    <td class="border border-gray-300 p-2">{{ $prod->caracteristicas->pluck('nombre')->join(', ') }}</td>

                    <td class="border border-gray-300 p-2">
                        {{ implode(', ', $prod->gruposTallas->pluck('nombre')->toArray()) }}
                    </td>

                    <td class="border border-gray-300 p-2 text-center">
                        <button wire:click="editar('{{ $prod->id }}')" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-3 py-1 rounded">
                            Editar
                        </button>
                        <button wire:click="borrar('{{ $prod->id }}')" class="bg-red-500 hover:bg-red-600 text-white font-semibold px-3 py-1 rounded"
                            onclick="return confirm('¿Estás seguro de eliminar este producto?')">
                            Eliminar
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $productos->links() }}
    </div>

    @if ($modal)
    <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white rounded shadow-lg w-full max-w-md">
            <div class="flex items-center justify-between border-b border-gray-200 p-4">
                <h5 class="text-xl font-bold">{{ $producto_id ? 'Editar Producto' : 'Crear Nuevo Producto' }}</h5>
                <button class="text-gray-500 hover:text-gray-700" wire:click="cerrarModal">&times;</button>
            </div>
            <div class="p-4">
                <!-- Nombre -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Nombre</label>
                    <input type="text"
                        class="w-full border border-gray-300 rounded p-2 {{ $bloquear_nombre ? 'bg-gray-100 text-gray-500' : '' }}"
                        wire:model="nombre"
                        @if($bloquear_nombre) readonly @endif>
                    @error('nombre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4 flex items-center space-x-2">
                    <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600" wire:model="ind_activo">
                    <label class="text-gray-700 font-medium select-none">Producto activo</label>
                </div>
                

                <!-- Categoría -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Categoría</label>
                    <select wire:change="onCategoriaChange" class="w-full border border-gray-300 rounded p-2" wire:model="categoria_id">
                        <option value="">Seleccione una categoría</option>
                        @foreach ($categorias as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                    @error('categoria_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Días de Producción -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Días de Producción</label>
                    <input type="number" class="w-full border border-gray-300 rounded p-2" wire:model="dias_produccion" min="1">
                    @error('dias_produccion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Flag Armado -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">¿Requiere Armado?</label>
                    <select class="w-full border border-gray-300 rounded p-2" wire:model="flag_armado">
                        <option value="1">Sí</option>
                        <option value="0">No</option>
                    </select>
                    @error('flag_armado') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>


                <!-- Características - Se muestra solo si hay características asociadas -->
                @if($mostrarCaracteristicas)
                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Características</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($caracteristicas as $caracteristica)
                            @if(in_array($caracteristica->id, $caracteristicasDisponibles))
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model="caracteristicasSeleccionadas" value="{{ $caracteristica->id }}" class="mr-2"
                                        {{ in_array($caracteristica->id, $caracteristicasSeleccionadas) ? 'checked' : '' }}>
                                    {{ $caracteristica->nombre }}
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Grupos de Tallas - Se muestra solo si la categoría tiene flag_tallas = 1 -->
                @if($mostrarGruposTallas)
                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Grupos de Tallas</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($gruposTallasDisponibles as $grupoTalla)
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="gruposTallasSeleccionados" value="{{ $grupoTalla->id }}" class="mr-2">
                                {{ $grupoTalla->nombre }}
                            </label>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>


            <div class="flex items-center justify-end border-t border-gray-200 p-4 space-x-2">
                <button wire:click="cerrarModal"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-4 py-2 rounded">
                    Cancelar
                </button>
                <button wire:click="guardar"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
                    Guardar
                </button>
            </div>
        </div>
    </div>
@endif

</div>

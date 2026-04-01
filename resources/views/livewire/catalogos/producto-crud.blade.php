<div class="max-w-6xl mx-auto p-4 text-gray-900 dark:text-gray-100">
    @if (session()->has('message'))
        <div class="mb-3 rounded border border-emerald-200 bg-emerald-50 p-3 text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-3 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <button wire:click="crear" class="mb-3 rounded bg-blue-500 px-4 py-2 font-semibold text-white hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-500">
            Nuevo Producto
        </button>
        <div class="flex flex-col gap-2 xl:flex-row">
            <input type="text" wire:model="query" placeholder="Buscar por nombre..." class="rounded border border-gray-300 bg-white px-4 py-2 text-gray-900 placeholder-gray-400 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
            <select wire:model="categoriaFiltro" class="rounded border border-gray-300 bg-white px-4 py-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                <option value="">Todas las categorías</option>
                @foreach ($categorias as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                @endforeach
            </select>
            <select wire:model="filtroActivo" class="rounded border border-gray-300 bg-white px-4 py-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
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
                    <th class="border border-gray-300 p-2 text-left dark:border-gray-700">Días de Producción</th>
                    <th class="border border-gray-300 p-2 text-left dark:border-gray-700">Armado</th>
                    <th class="border border-gray-300 p-2 text-left dark:border-gray-700">Req. Proveedor</th>
                    <th class="border border-gray-300 p-2 text-left dark:border-gray-700">Categoría</th>
                    <th class="border border-gray-300 p-2 text-left dark:border-gray-700">Características</th>
                    <th class="border border-gray-300 p-2 text-left dark:border-gray-700">Caract. No Armado</th>
                    <th class="border border-gray-300 p-2 text-left dark:border-gray-700">Grupos de Tallas</th>
                    <th class="border border-gray-300 p-2 text-center dark:border-gray-700">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($productos as $prod)
                    <tr class="dark:hover:bg-gray-700/40">
                        <td class="border border-gray-300 p-2 dark:border-gray-700">{{ $prod->nombre }}</td>
                        <td class="border border-gray-300 p-2 dark:border-gray-700">{{ $prod->dias_produccion }}</td>
                        <td class="border border-gray-300 p-2 dark:border-gray-700">{{ $prod->flag_armado ? 'Sí' : 'No' }}</td>
                        <td class="border border-gray-300 p-2 dark:border-gray-700">{{ $prod->flag_requiere_proveedor ? 'Sí' : 'No' }}</td>
                        <td class="border border-gray-300 p-2 dark:border-gray-700">{{ $prod->categoria ? $prod->categoria->nombre : 'Sin categoría' }}</td>
                        <td class="border border-gray-300 p-2 dark:border-gray-700">{{ $prod->caracteristicasArmado->pluck('nombre')->join(', ') }}</td>
                        <td class="border border-gray-300 p-2 dark:border-gray-700">{{ $prod->caracteristicasNoArmado->pluck('nombre')->join(', ') }}</td>
                        <td class="border border-gray-300 p-2 dark:border-gray-700">
                            {{ implode(', ', $prod->gruposTallas->pluck('nombre')->toArray()) }}
                        </td>
                        <td class="border border-gray-300 p-2 text-center dark:border-gray-700">
                            <button wire:click="editar('{{ $prod->id }}')" class="rounded bg-yellow-500 px-3 py-1 font-semibold text-white hover:bg-yellow-600 dark:bg-yellow-600 dark:hover:bg-yellow-500">
                                Editar
                            </button>
                            <button wire:click="borrar('{{ $prod->id }}')" class="rounded bg-red-500 px-3 py-1 font-semibold text-white hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-500"
                                onclick="return confirm('¿Estás seguro de eliminar este producto?')">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $productos->links() }}
    </div>

    @if ($modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
            <div class="w-full max-w-md rounded bg-white shadow-lg dark:bg-gray-800">
                <div class="flex items-center justify-between border-b border-gray-200 p-4 dark:border-gray-700">
                    <h5 class="text-xl font-bold">{{ $producto_id ? 'Editar Producto' : 'Crear Nuevo Producto' }}</h5>
                    <button class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" wire:click="cerrarModal">&times;</button>
                </div>
                <div class="max-h-[80vh] overflow-y-auto p-4">
                    <div class="mb-4">
                        <label class="mb-1 block text-gray-700 dark:text-gray-300">Nombre</label>
                        <input type="text"
                            class="w-full rounded border border-gray-300 bg-white p-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 {{ $bloquear_nombre ? 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' : '' }}"
                            wire:model="nombre"
                            @if($bloquear_nombre) readonly @endif>
                        @error('nombre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4 flex items-center space-x-2">
                        <input type="checkbox" class="form-checkbox h-5 w-5 rounded border-gray-300 text-blue-600 dark:border-gray-600 dark:bg-gray-900" wire:model="ind_activo">
                        <label class="select-none font-medium text-gray-700 dark:text-gray-300">Producto activo</label>
                    </div>
                    
                    <div class="mb-4">
                        <label class="mb-1 block text-gray-700 dark:text-gray-300">Categoría</label>
                        <select wire:change="onCategoriaChange" class="w-full rounded border border-gray-300 bg-white p-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" wire:model="categoria_id">
                            <option value="">Seleccione una categoría</option>
                            @foreach ($categorias as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                        @error('categoria_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="mb-1 block text-gray-700 dark:text-gray-300">Días de Producción</label>
                        <input type="number" class="w-full rounded border border-gray-300 bg-white p-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" wire:model="dias_produccion" min="1">
                        @error('dias_produccion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="mb-1 block text-gray-700 dark:text-gray-300">¿Requiere espesificar Armado?</label>
                        <select class="w-full rounded border border-gray-300 bg-white p-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" wire:model="flag_armado">
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                        @error('flag_armado') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="mb-1 block text-gray-700 dark:text-gray-300">¿Requiere proveedor?</label>
                        <select class="w-full rounded border border-gray-300 bg-white p-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" wire:model="flag_requiere_proveedor">
                            <option value="0">No</option>
                            <option value="1">Sí</option>
                        </select>
                        @error('flag_requiere_proveedor') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="mb-1 block text-gray-700 dark:text-gray-300">Flujo de Producción</label>
                        <select wire:model="flujo_id" class="w-full rounded border border-gray-300 bg-white p-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            <option value="">Sin flujo</option>
                            @foreach ($flujos as $fl)
                                <option value="{{ $fl->id }}">{{ $fl->nombre }}</option>
                            @endforeach
                        </select>
                        @error('flujo_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    @if($mostrarCaracteristicas)
                    <div class="mb-4">
                        <label class="mb-1 block text-gray-700 dark:text-gray-300">Características cuando el producto es armado</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($caracteristicas as $caracteristica)
                                @if(in_array($caracteristica->id, $caracteristicasDisponibles))
                                    <label class="flex items-center text-gray-700 dark:text-gray-300">
                                        <input type="checkbox" wire:model="caracteristicasSeleccionadas" value="{{ $caracteristica->id }}" class="mr-2 rounded border-gray-300 text-blue-600 dark:border-gray-600 dark:bg-gray-900"
                                            {{ in_array($caracteristica->id, $caracteristicasSeleccionadas) ? 'checked' : '' }}>
                                        {{ $caracteristica->nombre }}
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif


                    @if($mostrarCaracteristicas)
                    <div class="mb-4">
                        <label class="mb-1 block text-gray-700 dark:text-gray-300">Características cuando el producto NO es armado</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($caracteristicas as $caracteristica)
                                @if(in_array($caracteristica->id, $caracteristicasDisponibles))
                                    <label class="flex items-center text-gray-700 dark:text-gray-300">
                                        <input type="checkbox" wire:model="caracteristicasNoArmado" value="{{ $caracteristica->id }}" class="mr-2 rounded border-gray-300 text-blue-600 dark:border-gray-600 dark:bg-gray-900"
                                            {{ in_array($caracteristica->id, $caracteristicasNoArmado) ? 'checked' : '' }}>
                                        {{ $caracteristica->nombre }}
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($mostrarGruposTallas)
                    <div class="mb-4">
                        <label class="mb-1 block text-gray-700 dark:text-gray-300">Grupos de Tallas</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($gruposTallasDisponibles as $grupoTalla)
                                <label class="flex items-center text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" wire:model="gruposTallasSeleccionados" value="{{ $grupoTalla->id }}" class="mr-2 rounded border-gray-300 text-blue-600 dark:border-gray-600 dark:bg-gray-900">
                                    {{ $grupoTalla->nombre }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>


                <div class="flex items-center justify-end border-t border-gray-200 p-4 space-x-2 dark:border-gray-700">
                    <button wire:click="cerrarModal"
                        class="rounded bg-gray-200 px-4 py-2 font-semibold text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600">
                        Cancelar
                    </button>
                    <button wire:click="guardar"
                        class="rounded bg-blue-500 px-4 py-2 font-semibold text-white hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-500">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

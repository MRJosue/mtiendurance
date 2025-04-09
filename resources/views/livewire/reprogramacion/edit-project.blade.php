<div class="container mx-auto p-6">




    <h2 class="text-2xl font-semibold mb-4">Editar Proyecto</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-4">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="update">
                <!-- Nombre y Descripción -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input type="text" wire:model="nombre" class="w-full mt-1 border rounded-lg p-2">
                    @error('nombre') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Descripción</label>
                    <textarea wire:model="descripcion" class="w-full mt-1 border rounded-lg p-2"></textarea>
                    @error('descripcion') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>


                <!-- Selección de Categoría -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Categoría</label>
                    <select wire:change="onCategoriaChange" wire:model="categoria_id" class="w-full mt-1 border rounded-lg p-2">
                        <option value="">Seleccionar Categoría</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                    @error('categoria_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Selección de Producto -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Producto</label>
                    <select wire:change="onProductoChange" wire:model="producto_id" class="w-full mt-1 border rounded-lg p-2">
                        <option value="">Seleccionar Producto</option>
                        @foreach ($productos as $producto)
                            <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                        @endforeach
                    </select>
                    @error('producto_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                

                <!-- Características y Opciones -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Características y Opciones</label>
                    @foreach ($caracteristicas_sel as $index => $caracteristica)
                        <div class="mt-2 p-4 border rounded-lg bg-gray-50">
                            <p class="font-semibold">{{ $caracteristica['nombre'] }}</p>

                            @php
                                $opciones = \App\Models\Opcion::whereHas('caracteristicas', function ($query) use ($caracteristica) {
                                    $query->where('caracteristica_id', $caracteristica['id']);
                                })->get();
                            @endphp

                            @if ($opciones->count() === 1)
                                <!-- Si solo hay una opción, seleccionarla automáticamente -->
                                <p class="text-gray-700">{{ $opciones->first()->nombre }} ({{ $opciones->first()->valoru }})</p>
                                <input type="hidden" wire:model="caracteristicas_sel.{{ $index }}.opciones.0.id" value="{{ $opciones->first()->id }}">
                            @else
                                    <!-- Si hay múltiples opciones, mantener el select siempre visible -->
                                    <!-- Selección de Opciones -->
                                    <select wire:change="addOpcion({{ $index }}, $event.target.value)" class="w-full mt-1 border rounded-lg p-2">
                                        <option value="">Seleccionar Opción</option>
                                        @foreach (\App\Models\Opcion::whereHas('caracteristicas', function ($query) use ($caracteristica) {
                                            $query->where('caracteristica_id', $caracteristica['id']);
                                        })->get() as $opcion)
                                            <option value="{{ $opcion->id }}">{{ $opcion->nombre }} ({{ $opcion->valoru }})</option>
                                        @endforeach
                                    </select>

                                    <!-- Lista de Opciones Seleccionadas -->
                                    <ul class="mt-2">
                                        @foreach ($caracteristica['opciones'] as $opcionIndex => $opcion)
                                            <li class="flex justify-between items-center mb-2">
                                                <span>{{ $opcion['nombre'] }} ({{ $opcion['valoru'] }})</span>
                                                <button type="button" wire:click="removeOpcion({{ $index }}, {{ $opcionIndex }})" class="text-red-500 hover:underline">Eliminar</button>
                                            </li>
                                        @endforeach
                                    </ul>
                            @endif
                        </div>
                    @endforeach
                        <!-- 🚨 Mensaje de error si no se seleccionó una opción por característica -->
                        @error('opciones_sel') 
                        <span class="text-red-600 text-sm">{{ $message }}</span> 
                    @enderror
                </div>

                <!-- Selección de Cantidades -->
                <div wire:key="tallas-{{ $producto_id }}">
                    @if ($mostrarFormularioTallas)
                        <div class="mb-4 p-4 border rounded-lg bg-gray-50">
                            <h3 class="text-lg font-semibold mb-2">Cantidad por Tallas</h3>
                        
                            @foreach ($tallas->flatMap->gruposTallas->unique('id') as $grupoTalla)
                                <div class="mb-4">
                                    <p class="font-semibold text-gray-700 border-b pb-2">{{ $grupoTalla->nombre }}</p>
                        
                                    @foreach ($tallas->filter(fn($talla) => $talla->gruposTallas->contains('id', $grupoTalla->id)) as $talla)
                                        <div class="flex items-center space-x-2 mt-2">
                                            <label class="text-sm font-medium text-gray-700 w-1/3">{{ $talla->nombre }}</label>
                                            <input type="number"
                                                {{-- wire:model.defer="tallasSeleccionadas.{{ $grupoTalla->id }}.{{ $talla->id }}" --}}
                                                class="w-2/3 border rounded-lg p-2"
                                                min="0"
                                                value="0" readonly>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div>
                    <span class="text-red-600 text-sm">Nota: Al guardar este formulario habras reconfigurado la solicitud original </span>
                </div>


                <!-- Botones -->
                <div class="flex justify-end gap-4 mt-6">
                    <a href="{{ route('reprogramacion.reprogramacionproyectopedido', ['proyecto' => $ProyectoId]) }}"
                    class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded">
                        Cancelar
                    </a>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        Guardar Cambios
                    </button>
                </div>
    </form>

    @push('scripts')
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Livewire.on('setReadOnlyMode', function () {
                setTimeout(function () {
                    $("input, textarea").attr("readonly", "readonly"); // Hacer los inputs de solo lectura
                    $("select, button").attr("disabled", "disabled"); // Deshabilitar select y botones
                }, 100); // Se ejecuta después de 100ms para evitar que Livewire lo sobrescriba
            });

            Livewire.on('redirect', function (url) {
                     window.location.href = url;
             });
        });
    </script>
    @endpush
</div>


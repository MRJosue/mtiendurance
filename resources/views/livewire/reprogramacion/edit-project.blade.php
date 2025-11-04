<div class="container mx-auto p-6">




    <h2 class="text-2xl font-semibold mb-4">Editar Proyecto</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-4">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="update">


    <div 
        x-data="{
            open:false,
            search: @entangle('usuarioQuery').live,
            selectedId: @entangle('usuario_id_nuevo'),
            get hasResults(){ return (this.$wire.usuariosSugeridos || []).length > 0 },
            select(id){ this.selectedId = id; this.open = false; },
        }"
        class="mb-6"
    >
    <label class="block mb-1 font-medium text-gray-700">Seleccionar Usuario</label>

    <!-- Campo de búsqueda (debounced con Livewire 3) -->
    <input
        x-model="search"
        @focus="open = true"
        @click.outside="open = false"
        placeholder="Buscar por nombre o email…"
        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
        type="text"
    />

    <!-- Dropdown de resultados -->
    <div
        x-show="open"
        x-transition
        class="relative"
    >
        <div
            class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg mt-1 max-h-56 overflow-y-auto shadow"
        >
            <template x-for="user in ($wire.usuariosSugeridos || [])" :key="user.id">
                <div
                    @click="select(user.id)"
                    class="px-3 py-2 cursor-pointer hover:bg-blue-100 text-sm flex items-center justify-between"
                    :class="{'bg-blue-50': user.id === selectedId}"
                >
                    <div class="truncate">
                        <span class="font-medium" x-text="user.name"></span>
                        <span class="text-gray-500 text-xs ml-1" x-text="'(' + user.email + ')'"></span>
                    </div>
                    <template x-if="user.id === {{ auth()->id() }}">
                        <span class="text-blue-500 text-xs ml-2 shrink-0">— Actual</span>
                    </template>
                </div>
            </template>

            <div
                x-show="!hasResults"
                class="px-3 py-2 text-gray-500 text-sm italic"
            >
                No se encontraron usuarios
            </div>
        </div>
    </div>

    <!-- Píldora del seleccionado -->
    <div class="mt-2" x-show="selectedId">
        <span class="inline-flex items-center gap-2 text-sm bg-blue-50 text-blue-700 px-3 py-1 rounded-full">
            Usuario seleccionado:
            <span x-text="( ($wire.usuariosSugeridos || []).find(u => u.id === selectedId)?.name ) || 'ID '+selectedId"></span>
        </span>
    </div>
</div>
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
                
                {{-- El proyecto es armado ?  --}}
                @if ($this->mostrar_selector_armado)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">¿El proyecto será armado?</label>
                        <select wire:model="seleccion_armado" wire:change="despligaformopciones" class="w-full mt-1 border rounded-lg p-2">
                            <option value="">Seleccionar</option>
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                        @error('seleccion_armado') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                @endif

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
                    <a href="{{ route('proyecto.show',$ProyectoId) }}"
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
    document.addEventListener('DOMContentLoaded', () => {
        // Eventos de Livewire v3 usando dispatch/On
        Livewire.on('setReadOnlyMode', () => {
            setTimeout(function () {
                $("input, textarea").attr("readonly", "readonly");
                $("select, button").attr("disabled", "disabled");
            }, 100);
        });

        Livewire.on('redirect', (url) => {
            window.location.href = url;
        });
    });
    </script>
    @endpush
</div>


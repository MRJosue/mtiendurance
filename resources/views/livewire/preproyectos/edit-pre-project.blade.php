<div class="container mx-auto p-6">

    <button type="button" wire:click="setReadOnlyMode" class="mt-4 px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
        Modo Solo Lectura
    </button>


    <h2 class="text-2xl font-semibold mb-4">Editar Preproyecto</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-4">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="update">
        <!-- Nombre -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Nombre</label>
            <input type="text" wire:model="nombre" class="w-full mt-1 border rounded-lg p-2">
        </div>

        <!-- Descripción -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Descripción</label>
            <textarea wire:model="descripcion" class="w-full mt-1 border rounded-lg p-2"></textarea>
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
        
        

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Características y Opciones</label>
            @foreach ($caracteristicas_sel as $index => $caracteristica)
                <div class="mt-2 p-4 border rounded-lg bg-gray-50">
                    <p class="font-semibold">{{ $caracteristica['nombre'] }}</p>

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
                </div>
            @endforeach
        </div>

       

        <!-- Selección de Cantidades -->
        @if ($mostrarFormularioTallas)
            <div class="mb-4 p-4 border rounded-lg bg-gray-50">
                <h3 class="text-lg font-semibold mb-2">Cantidad por Tallas</h3>
                @foreach ($tallas as $talla)
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700 w-1/3">{{ $talla->nombre }}</label>
                        <input type="number" wire:model="tallasSeleccionadas.{{ $talla->id }}" class="w-2/3 border rounded-lg p-2" min="0">
                    </div>
                @endforeach
            </div>
        @else
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Total de Piezas</label>
                <input type="number" wire:model="total_piezas" class="w-full mt-1 border rounded-lg p-2" min="1">
            </div>
        @endif

        <!-- Subir Nuevos Archivos -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Subir Nuevos Archivos</label>
            <input type="file" wire:model="files" multiple class="w-full mt-1 border rounded-lg p-2">
        </div>

        <!-- Vista previa de archivos nuevos -->
        @if ($uploadedFiles)
            <div class="mb-4 p-4 border rounded-lg bg-gray-50">
                <h3 class="text-lg font-semibold mb-2">Vista Previa de Archivos</h3>
                @foreach ($uploadedFiles as $file)
                    <div class="mb-2">
                        @if (str_starts_with($file['preview'], 'data:image'))
                            <img src="{{ $file['preview'] }}" class="w-32 h-32 object-cover rounded-lg">
                        @else
                            <a href="{{ $file['preview'] }}" target="_blank" class="text-blue-500 underline">
                                {{ $file['name'] }}
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Lista de Archivos Cargados -->
        <h3 class="text-lg font-semibold mt-4">Archivos Cargados</h3>
        <ul class="mb-4">
            @foreach ($existingFiles as $file)
                <li class="flex justify-between items-center bg-gray-100 p-2 rounded-lg mb-2">
                    <a href="{{ Storage::url($file->ruta_archivo) }}" target="_blank" class="text-blue-500 underline">
                        {{ $file->nombre_archivo }}
                    </a>
                    <button type="button" wire:click="deleteFile({{ $file->id }})" class="text-red-500 hover:underline">
                        Eliminar
                    </button>
                </li>
            @endforeach
        </ul>

        <!-- Selección de Direcciones -->
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Dirección Fiscal</label>
                <select wire:model="direccion_fiscal_id" class="w-full mt-1 border rounded-lg p-2">
                    <option value="">Seleccionar Dirección Fiscal</option>
                    @foreach ($direccionesFiscales as $direccion)
                        <option value="{{ $direccion->id }}">{{ $direccion->nombre_contacto }} - {{ $direccion->calle }}</option>
                    @endforeach
                </select>
                @error('direccion_fiscal_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Dirección de Entrega</label>
                <select wire:change='cargarTiposEnvio'  wire:model="direccion_entrega_id" class="w-full mt-1 border rounded-lg p-2">
                    <option value="">Selecciona una dirección</option>
                    @foreach ($direccionesEntrega as $direccion)
                        <option value="{{ $direccion->id }}">{{ $direccion->calle }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Tipo de Envío</label>
                <select  wire:change="on_Calcula_Fechas_Entrega" wire:model="id_tipo_envio" class="w-full mt-1 border rounded-lg p-2">
                    <option value="">Selecciona un tipo de envío</option>
                    @foreach ($tiposEnvio as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Fechas -->
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Fecha Producción</label>
                <input type="date" wire:model="fecha_produccion" class="w-full mt-1 border rounded-lg p-2" >
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Fecha Embarque</label>
                <input type="date" wire:model="fecha_embarque" class="w-full mt-1 border rounded-lg p-2" >
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Fecha Entrega</label>
                <input type="date" wire:model="fecha_entrega" class="w-full mt-1 border rounded-lg p-2" >
            </div>
        </div>

        <!-- Botón de Guardar Cambios -->
        <button type="submit" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
            Guardar Cambios
        </button>

        <button type="button" 
            wire:click="preAprobarProyecto" 
            class="mt-4 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
            Pre aprobar proyecto
        </button>
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


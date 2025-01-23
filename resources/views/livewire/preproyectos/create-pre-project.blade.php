<div class="container mx-auto p-6">
    <h2 class="text-2xl font-semibold mb-4">Crear Nuevo Preproyecto</h2>

    <!-- Mensajes de error o éxito -->
    @if (session('message'))
        <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-4">
            {{ session('message') }}
        </div>
    @elseif (session('error'))
        <div class="bg-red-100 text-red-800 p-4 rounded-lg mb-4">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit.prevent="create">
        <!-- Información del Preproyecto -->
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
                @if (!empty($productos))
                    @foreach ($productos as $producto)
                        <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                    @endforeach
                @endif
            </select>
            @error('producto_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Características -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Características</label>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <select wire:model="caracteristica_id" class="w-full mt-1 border rounded-lg p-2">
                        <option value="">Seleccionar Característica</option>
                        @foreach ($caracteristicasDisponibles as $caracteristica)
                            <option value="{{ $caracteristica->id }}">{{ $caracteristica->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="button" wire:click="addCaracteristica" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                        Agregar
                    </button>
                </div>
            </div>

            <ul class="mt-4">
                @foreach ($caracteristicas_sel as $index => $caracteristica)
                    <li class="flex justify-between items-center mb-2">
                        <span>{{ $caracteristica['nombre'] }}</span>
                        <button type="button" wire:click="removeCaracteristica({{ $index }})" class="text-red-500 hover:underline">Eliminar</button>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Opciones -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Opciones</label>
            @foreach ($caracteristicas_sel as $caracteristicaIndex => $caracteristica)
                <div class="mt-2">
                    <p class="font-semibold">{{ $caracteristica['nombre'] }}</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <select wire:model="opcion_id" class="w-full mt-1 border rounded-lg p-2">
                                <option value="">Seleccionar Opción</option>
                                @foreach (\App\Models\Opcion::where('caracteristica_id', $caracteristica['id'])->get() as $opcion)
                                    <option value="{{ $opcion->id }}">{{ $opcion->nombre }} ({{ $opcion->valor }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <button type="button" wire:click="addOpcion({{ $caracteristicaIndex }})" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                                Agregar
                            </button>
                        </div>
                    </div>

                    <ul class="mt-2">
                        @foreach ($opciones_sel[$caracteristica['id']] ?? [] as $opcionIndex => $opcion)
                            <li class="flex justify-between items-center mb-2">
                                <span>{{ $opcion['nombre'] }} ({{ $opcion['valor'] }})</span>
                                <button type="button" wire:click="removeOpcion({{ $caracteristica['id'] }}, {{ $opcionIndex }})" class="text-red-500 hover:underline">Eliminar</button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
        

        

         <!-- Archivos -->
         <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Archivos</label>
            <input type="file" wire:model="files" multiple class="w-full mt-1 border rounded-lg p-2">
            @error('files.*') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>



        <!-- Descripciones de Archivos -->
        @foreach ($files as $index => $file)
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Descripción para: {{ $file->getClientOriginalName() }}</label>
                <input type="text" wire:model="fileDescriptions.{{ $index }}" class="w-full mt-1 border rounded-lg p-2">
            </div>
        @endforeach





        <div class="grid grid-cols-2 gap-4 mb-4">
            <!-- Selección de Dirección Fiscal -->
            <div class="mb-4 ">
                <label class="block text-sm font-medium text-gray-700">Dirección Fiscal</label>
                <select wire:model="direccion_fiscal_id" class="w-full mt-1 border rounded-lg p-2">
                    <option value="">Seleccionar Dirección Fiscal</option>
                    @foreach ($direccionesFiscales as $direccion)
                        <option value="{{ $direccion->id }}">{{ $direccion->nombre_contacto }} - {{ $direccion->calle }}</option>
                    @endforeach
                </select>
                @error('direccion_fiscal_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Selección de Dirección de Entrega -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Dirección de Entrega</label>
                <select wire:model="direccion_entrega_id" class="w-full mt-1 border rounded-lg p-2">
                    <option value="">Seleccionar Dirección de Entrega</option>
                    @foreach ($direccionesEntrega as $direccion)
                        <option value="{{ $direccion->id }}">{{ $direccion->nombre_contacto }} - {{ $direccion->calle }}</option>
                    @endforeach
                </select>
                @error('direccion_entrega_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>


        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Fecha Producción</label>
                <input type="date" wire:model="fecha_produccion" class="w-full mt-1 border rounded-lg p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Fecha Embarque</label>
                <input type="date" wire:model="fecha_embarque" class="w-full mt-1 border rounded-lg p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Fecha Entrega</label>
                <input type="date" wire:model="fecha_entrega" class="w-full mt-1 border rounded-lg p-2">
            </div>
        </div>


        <button type="submit" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
            Crear Preproyecto
        </button>
    </form>
</div>

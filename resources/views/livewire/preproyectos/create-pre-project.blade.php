<div class="container mx-auto p-6">
    <h2 class="text-2xl font-semibold mb-4">Crear Nuevo Preproyecto</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-4">
            {{ session('message') }}
        </div>
    @elseif (session()->has('error'))
        <div class="bg-red-100 text-red-800 p-4 rounded-lg mb-4">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit.prevent="create">
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
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Total de Piezas</label>
            <input 
                type="number" 
                wire:model="total_piezas" 
                class="w-full mt-1 border rounded-lg p-2" 
                min="1" 
                {{ $mostrarFormularioTallas ? 'disabled' : '' }}>
            @error('total_piezas') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Formulario de Tallas (solo si la categoría es Playeras) -->
        @if ($mostrarFormularioTallas)
            <div class="mb-4 p-4 border rounded-lg bg-gray-50">
                <h3 class="text-lg font-semibold mb-2">Cantidad por Tallas</h3>
                @foreach ($tallas as $talla)
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700 w-1/3">{{ $talla->nombre }}</label>
                        <input type="number" 
                            wire:model="tallasSeleccionadas.{{ $talla->id }}" 
                            class="w-2/3 border rounded-lg p-2" 
                            min="0">
                    </div>
                @endforeach
            </div>
        @endif


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
                <input type="date" wire:model="fecha_produccion" class="w-full mt-1 border rounded-lg p-2" readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Fecha Embarque</label>
                <input type="date" wire:model="fecha_embarque" class="w-full mt-1 border rounded-lg p-2" readonly>
            </div>


            <div>
                <label class="block text-sm font-medium text-gray-700">Fecha Entrega</label>
                {{-- <input wire:change="on_Calcula_Fechas_Entrega" type="date" wire:model="fecha_entrega" class="w-full mt-1 border rounded-lg p-2"> --}}
                <input 
                wire:change="on_Calcula_Fechas_Entrega"
                wire:model="fecha_entrega"
                type="date" 
                class="w-full mt-1 border rounded-lg p-2"
                min="<?= date('Y-m-d'); ?>" >

            </div>
        </div>

        <!-- Mensaje de advertencia si la fecha de producción está en el pasado -->
        @if ($mensaje_produccion)
        <div class="bg-yellow-100 text-yellow-800 p-3 rounded mt-2">
            {{ $mensaje_produccion }}
        </div>
        @endif

        <!-- Botón de Enviar -->
        <button type="submit" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
            Crear Preproyecto
        </button>
    </form>
</div>

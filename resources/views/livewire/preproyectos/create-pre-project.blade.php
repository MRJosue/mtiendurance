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

        {{-- El proyecto es armado ?  --}}
        @if ($mostrar_selector_armado)
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
                                        wire:model.defer="tallasSeleccionadas.{{ $grupoTalla->id }}.{{ $talla->id }}"
                                        class="w-2/3 border rounded-lg p-2"
                                        min="0"
                                        value="{{ $tallasSeleccionadas[$grupoTalla->id][$talla->id] ?? 0 }}">
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endif
        

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Total de Piezas</label>
                <input 
                    type="number" 
                    wire:model="total_piezas" 
                    class="w-full mt-1 border rounded-lg p-2" 
                    min="1" >
                @error('total_piezas') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            
        </div>

         <!-- Selección de Archivo -->
         <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Subir Archivos</label>
            <input type="file" wire:model="files" multiple class="w-full mt-1 border rounded-lg p-2">
        </div>

        <!-- Descripciones de Archivos -->
        @foreach ($files as $index => $file)
            <div class="mb-2 p-2 border rounded-lg bg-gray-100">
                <p class="text-sm font-semibold">{{ $file->getClientOriginalName() }}</p>
                <label class="block text-xs text-gray-600">Descripción</label>
                <input type="text" wire:model="fileDescriptions.{{ $index }}" class="w-full border rounded-lg p-1 text-sm">
            </div>
        @endforeach

        <!-- Vista previa de archivos -->
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
                wire:change="validarFechaEntrega"
                wire:model="fecha_entrega"
                type="date" 
                class="w-full mt-1 border rounded-lg p-2"
                min="{{ date('Y-m-d') }}"
                id="fechaEntrega">

            </div>

            @error('error') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
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



    @if($mostrarModalCliente)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
                <div class="flex items-center justify-between border-b border-gray-200 p-4">
                    <h5 class="text-xl font-bold">Agregar Cliente</h5>
                    <button class="text-gray-500 hover:text-gray-700" wire:click="$set('mostrarModalCliente', false)">&times;</button>
                </div>

                <div class="p-4">
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700">Nombre de Empresa</label>
                        <input type="text" wire:model="nuevoCliente.nombre_empresa" class="w-full border border-gray-300 rounded p-2">
                        @error('nuevoCliente.nombre_empresa') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700">Contacto Principal</label>
                        <input type="text" wire:model="nuevoCliente.contacto_principal" class="w-full border border-gray-300 rounded p-2">
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                        <input type="text" wire:model="nuevoCliente.telefono" class="w-full border border-gray-300 rounded p-2">
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" wire:model="nuevoCliente.email" class="w-full border border-gray-300 rounded p-2">
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button wire:click="$set('mostrarModalCliente', false)" class="bg-gray-300 text-gray-800 px-4 py-2 rounded">
                            Cancelar
                        </button>
                        <button wire:click="guardarCliente" class="bg-blue-500 text-white px-4 py-2 rounded">
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif


    @if ($producto_id)
        <livewire:visuales.layout-render :producto_id="$producto_id" />
    @endif

    
</div>

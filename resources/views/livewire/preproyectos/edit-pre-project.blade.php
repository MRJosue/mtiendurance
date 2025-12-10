<div class="container mx-auto p-6">

    {{-- <button type="button" wire:click="setReadOnlyMode" class="mt-4 px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
        Modo Solo Lectura
    </button> --}}


    <h2 class="text-2xl font-semibold mb-4">Editar Preproyecto</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-4">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="preguardado">
        <!-- Nombre y Descripción -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Nombre</label>
            <input type="text" wire:model="nombre" class="w-full mt-1 border rounded-lg p-2" @if($modoLectura) readonly @endif>
            @error('nombre') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Descripción</label>
            <textarea wire:model="descripcion" class="w-full mt-1 border rounded-lg p-2"@if($modoLectura) readonly @endif></textarea>
            @error('descripcion') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>


        <!-- Selección de Categoría -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Categoría</label>
            <select wire:change="onCategoriaChange" wire:model="categoria_id" class="w-full mt-1 border rounded-lg p-2"  @if($modoLectura) disabled @endif>
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
            <select wire:change="onProductoChange" wire:model="producto_id" class="w-full mt-1 border rounded-lg p-2"  @if($modoLectura) disabled @endif>
                <option value="">Seleccionar Producto</option>
                @foreach ($productos as $producto)
                    <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                @endforeach
            </select>
            @error('producto_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        @if($producto_id)
            <div class="mb-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                            {{ $flag_requiere_proveedor ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                    {{ $flag_requiere_proveedor ? 'Este producto REQUIERE proveedor' : 'Este producto NO requiere proveedor' }}
                </span>
            </div>
        @endif
                
                
        {{-- El proyecto es armado ?  --}}
        @if ($this->mostrar_selector_armado)
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">¿El proyecto será armado?</label>
                <select wire:model="seleccion_armado" wire:change="despligaformopciones" class="w-full mt-1 border rounded-lg p-2"  @if($modoLectura) disabled @endif>
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
                        <input type="hidden" wire:model="caracteristicas_sel.{{ $index }}.opciones.0.id" value="{{ $opciones->first()->id }}" @if($modoLectura) readonly @endif>
                    @else
                            <!-- Si hay múltiples opciones, mantener el select siempre visible -->
                            <!-- Selección de Opciones -->
                            <select wire:change="addOpcion({{ $index }}, $event.target.value)" class="w-full mt-1 border rounded-lg p-2"  @if($modoLectura) disabled @endif>
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

                                          @hasanyrole('admin|cliente')
                                            <button type="button" wire:click="removeOpcion({{ $index }}, {{ $opcionIndex }})" class="text-red-500 hover:underline">Eliminar</button>
                                          @endhasanyrole
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

            @error('caracteristicas_sel')
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
                                        value="{{ $tallasSeleccionadas[$grupoTalla->id][$talla->id] ?? 0 }}" @if($modoLectura) readonly @endif>
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
                    min="1"  @if($modoLectura) readonly @endif>
                @error('total_piezas') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            
        </div>


        <div x-data="{ isUploading: @entangle('isUploading') }">
            <!-- Spinner de carga -->
            <template x-if="isUploading">
                <div class="flex items-center justify-center my-4">
                    <svg class="animate-spin h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                        </path>
                    </svg>
                    <span class="ml-2 text-sm text-gray-600">Subiendo archivo...</span>
                </div>
            </template>

            <!-- Descripciones de Archivos -->
            @foreach ($files as $index => $file)
                <div class="mb-2 p-2 border rounded-lg bg-gray-100">
                    <p class="text-sm font-semibold">{{ $file->getClientOriginalName() }}</p>
                    <label class="block text-xs text-gray-600">Descripción</label>
                    <input type="text" wire:model="fileDescriptions.{{ $index }}" class="w-full border rounded-lg p-1 text-sm">
                </div>
            @endforeach
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
                        <div>
                            <a href="{{ Storage::url($file->ruta_archivo) }}" target="_blank" class="text-blue-500 underline">
                                {{ $file->nombre_archivo }}
                            </a>
                            @if (!$file->flag_descarga)
                                <button
                                    type="button"
                                    wire:click.prevent="downloadFile({{ $file->id }})"
                                    class="ml-4 text-green-600 hover:underline"
                                >
                                    Descargar Archivo
                                </button>
                            @else
                                <span class="ml-4 text-sm text-gray-500">(Descargado)</span>
                            @endif
                        </div>
                        <button type="button" wire:click="deleteFile({{ $file->id }})" class="text-red-500 hover:underline">
                            Eliminar
                        </button>
                    </li>
            @endforeach

            @error('archivosPendientes') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror

        </ul>


        <!-- Selección de Direcciones -->
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Dirección Fiscal</label>
                <select wire:model="direccion_fiscal_id" class="w-full mt-1 border rounded-lg p-2"  @if($modoLectura) disabled @endif>
                    <option value="">Seleccionar Dirección Fiscal</option>
                    @foreach ($direccionesFiscales as $direccion)
                        <option value="{{ $direccion->id }}">{{ $direccion->nombre_contacto }} - {{ $direccion->calle }}</option>
                    @endforeach
                </select>
                @error('direccion_fiscal_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Dirección de Entrega</label>
                <select wire:change='cargarTiposEnvio'  wire:model="direccion_entrega_id" class="w-full mt-1 border rounded-lg p-2"  @if($modoLectura) disabled @endif>
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
                <select  wire:change="on_Calcula_Fechas_Entrega" wire:model="id_tipo_envio" class="w-full mt-1 border rounded-lg p-2"  @if($modoLectura) disabled @endif>
                    <option value="">Selecciona un tipo de envío</option>
                    @foreach ($tiposEnvio as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Fechas -->
        <div class="grid grid-cols-3 gap-4 mb-4">

             @can('edit-pre-project-fecha-produccion')
                <div>
                <label class="block text-sm font-medium text-gray-700">Fecha Producción</label>
                <input type="date" wire:model="fecha_produccion" class="w-full mt-1 border rounded-lg p-2" readonly>
                </div>
             @endcan


             @can('edit-pre-project-fecha-embarque')
                <div>
                <label class="block text-sm font-medium text-gray-700">Fecha Embarque</label>
                <input type="date" wire:model="fecha_embarque" class="w-full mt-1 border rounded-lg p-2" readonly>
                </div>
             @endcan

             @can('edit-pre-project-fecha-entrega')
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha Entrega</label>
                    {{-- <input wire:change="on_Calcula_Fechas_Entrega" type="date" wire:model="fecha_entrega" class="w-full mt-1 border rounded-lg p-2"> --}}
                    <input 
                    wire:change="validarFechaEntrega"
                    wire:model="fecha_entrega"
                    type="date" 
                    class="w-full mt-1 border rounded-lg p-2"
                    min="{{ date('Y-m-d') }}"
                    id="fechaEntrega"  @if($modoLectura) readonly @endif>
                </div>
             @endcan
            @error('error') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>


        <!-- Botón de Guardar Cambios -->

         @can('edit-pre-project--boton-guardar-cambios')
        
            <button type="submit" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                Guardar Cambios
            </button>

         @endcan

       
         @can('edit-pre-project--Pre-Aprobar-proyecto')
            <button type="button" 
                wire:click="preAprobarProyecto" 
                class="mt-4 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                Pre aprobar proyecto
            </button>
         @endcan

     
    </form>

    @push('scripts')

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


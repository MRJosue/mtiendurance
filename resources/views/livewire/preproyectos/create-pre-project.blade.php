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

        @php
            $config = auth()->user()->config ?? [];
            $puedeSeleccionarUsuarios = $config['flag-can-user-sel-preproyectos'] ?? false;
        @endphp

        @if ($puedeSeleccionarUsuarios)


            <x-select-usuario
                label="Usuario que crea el proyecto"
                :opciones="$todosLosUsuarios->toArray()"
                entangle="UsuarioSeleccionado"
                wire:model="UsuarioSeleccionado"
                :seleccionado="$UsuarioSeleccionado"
                onchange="usuarioSeleccionadoCambio"
            />
         
        @endif

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
                            <select
                                wire:key="prod-{{ $producto_id }}-car-{{ $index }}"
                                wire:change="addOpcion({{ $index }}, $event.target.value)"
                                class="w-full mt-1 border rounded-lg p-2"
                            >
                                <option value="">Seleccionar Opción</option>
                                @foreach(\App\Models\Opcion::whereHas('caracteristicas', fn($q) => $q->where('caracteristica_id', $caracteristica['id']))->get() as $opcion)
                                    <option value="{{ $opcion->id }}">
                                        {{ $opcion->nombre }} ({{ $opcion->valoru }})
                                    </option>
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

        {{-- 1) Input siempre visible para seleccionar --}}
        <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Seleccionar Archivos</label>
        <input 
            type="file" 
            wire:model="files" 
            multiple 
            accept=".zip,.jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx"
            class="w-full mt-1 border rounded-lg p-2"
        >
        @error('files.*') 
            <span class="text-red-600 text-sm">{{ $message }}</span> 
        @enderror
        </div>

        {{-- 2) Botón para iniciar la carga y procesar previews --}}
        <div class="mb-4">
        <button 
            type="button" 
            wire:click="procesarArchivos"
            wire:loading.attr="disabled" 
            class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 disabled:opacity-50"
        >
            Cargar Archivos
        </button>
        {{-- Spinner integrado con Livewire --}}
        <span wire:loading wire:target="procesarArchivos" class="ml-2 text-blue-600 text-sm">
            Subiendo…
            <svg class="inline w-4 h-4 animate-spin ml-1" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
        </span>
        </div>

        {{-- 3) Post-procesamiento (preview) sólo después de click --}}
        @if ($uploadedFiles)
        <div class="mb-4 p-4 border rounded-lg bg-gray-50">
            <h3 class="text-lg font-semibold mb-2">Vista Previa de Archivos</h3>
            @foreach ($uploadedFiles as $file)
            <div class="mb-2 flex items-center space-x-2">
                @if ($file['preview'])
                <img src="{{ $file['preview'] }}" class="w-16 h-16 object-cover rounded-lg">
                @else
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <a href="{{ $file['preview'] }}" target="_blank" class="text-blue-500 underline">
                    {{ $file['name'] }}
                </a>
                @endif
                <span class="text-sm text-gray-700">{{ $file['name'] }}</span>
            </div>
            @endforeach
        </div>
        @endif



<!-- Selección de Direcciones -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div>
        <div class="flex items-center justify-between">
            <label class="block text-sm font-medium text-gray-700">Dirección Fiscal</label>

            <!-- Botón "+" para crear Dirección Fiscal -->
            <button
                type="button"
                class="inline-flex items-center px-2 py-1 text-sm bg-blue-50 text-blue-600 rounded hover:bg-blue-100"
                wire:click="abrirModalDireccion('fiscal')"
                title="Nueva dirección fiscal"
            >
                <span class="text-lg leading-none">+</span>
            </button>
        </div>

        <select wire:model="direccion_fiscal_id" class="w-full mt-1 border rounded-lg p-2">
            <option value="">Seleccionar Dirección Fiscal</option>
            @foreach ($direccionesFiscales as $direccion)
                <option value="{{ $direccion->id }}">
                    {{ $direccion->rfc ?? '—' }} — {{ $direccion->calle }}
                </option>
            @endforeach
        </select>
        @error('direccion_fiscal_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
    </div>

    <div>
        <div class="flex items-center justify-between">
            <label class="block text-sm font-medium text-gray-700">Dirección de Entrega</label>

            <!-- Botón "+" para crear Dirección de Entrega -->
            <button
                type="button"
                class="inline-flex items-center px-2 py-1 text-sm bg-blue-50 text-blue-600 rounded hover:bg-blue-100"
                wire:click="abrirModalDireccion('entrega')"
                title="Nueva dirección de entrega"
            >
                <span class="text-lg leading-none">+</span>
            </button>
        </div>

        <select wire:change='cargarTiposEnvio' wire:model="direccion_entrega_id" class="w-full mt-1 border rounded-lg p-2">
            <option value="">Selecciona una dirección</option>
            @foreach ($direccionesEntrega as $direccion)
                <option value="{{ $direccion->id }}">
                    {{ $direccion->nombre_contacto ?? '—' }} — {{ $direccion->calle }}
                </option>
            @endforeach
        </select>
        @error('direccion_entrega_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror



    </div>

    <div>

             <label class="block text-sm font-medium text-gray-700">Tipo de Envio</label>

        <select
            wire:model="id_tipo_envio"
            class="w-full mt-1 border rounded-lg p-2"
            @disabled(!$direccion_entrega_id)
        >
            <option value="">Selecciona un tipo de envío</option>
            @foreach ($tiposEnvio as $envio)
                <option value="{{ $envio->id }}">{{ $envio->nombre }} ({{ $envio->dias_envio }} días)</option>
            @endforeach
        </select>
        @error('id_tipo_envio') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
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


    {{-- Modal Crear Dirección (Fiscal / Entrega) --}}
    @if($mostrarModalDireccion)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white w-full max-w-lg rounded-lg shadow">
            <div class="flex items-center justify-between border-b p-4">
                <h3 class="text-lg font-semibold">
                    {{ $tipoDireccion === 'fiscal' ? 'Nueva Dirección Fiscal' : 'Nueva Dirección de Entrega' }}
                </h3>
                <button class="text-gray-500 hover:text-gray-700" wire:click="cerrarModalDireccion">&times;</button>
            </div>

            <div class="p-4 space-y-4">
                {{-- Campos comunes/por tipo --}}
                @if($tipoDireccion === 'fiscal')
                    <div>
                        <label class="block text-sm font-medium text-gray-700">RFC</label>
                        <input type="text" class="w-full border rounded-lg p-2"
                            wire:model.defer="formDireccion.rfc">
                        @error('formDireccion.rfc') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                @else
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre de Contacto</label>
                        <input type="text" class="w-full border rounded-lg p-2"
                            wire:model.defer="formDireccion.nombre_contacto">
                        @error('formDireccion.nombre_contacto') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre de Empresa</label>
                        <input type="text" class="w-full border rounded-lg p-2"
                            wire:model.defer="formDireccion.nombre_empresa">
                        @error('formDireccion.nombre_empresa') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                        <input type="text" class="w-full border rounded-lg p-2"
                            wire:model.defer="formDireccion.telefono">
                        @error('formDireccion.telefono') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700">Calle</label>
                    <input type="text" class="w-full border rounded-lg p-2"
                        wire:model.defer="formDireccion.calle">
                    @error('formDireccion.calle') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">País</label>
                            <!-- País -->
                            <select
                                class="w-full border rounded-lg p-2"
                                wire:model="formDireccion.pais_id"
                                wire:change="onPaisChange"
                            >
                                <option value="">Seleccione</option>
                                @foreach($paises as $pais)
                                    <option value="{{ $pais->id }}">{{ $pais->nombre }}</option>
                                @endforeach
                            </select>
                        @error('formDireccion.pais_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Estado</label>
                            <!-- Estado -->
                            <select
                                class="w-full border rounded-lg p-2"
                                wire:model="formDireccion.estado_id"
                                wire:change="onEstadoChange"
                            >
                                <option value="">Seleccione</option>
                                @foreach($estados as $estado)
                                    <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                                @endforeach
                            </select>
                        @error('formDireccion.estado_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ciudad</label>
                            <select
                                class="w-full border rounded-lg p-2"
                                wire:model="formDireccion.ciudad_id"
                            >
                                <option value="">Seleccione</option>
                                @foreach($ciudades as $ciudad)
                                    <option value="{{ $ciudad->id }}">{{ $ciudad->nombre }}</option>
                                @endforeach
                            </select>
                        @error('formDireccion.ciudad_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Código Postal</label>
                        <input type="text" class="w-full border rounded-lg p-2"
                            wire:model.defer="formDireccion.codigo_postal">
                        @error('formDireccion.codigo_postal') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-2 mt-6">
                        <input id="flag_default" type="checkbox" class="rounded"
                            wire:model="formDireccion.flag_default">
                        <label for="flag_default" class="text-sm text-gray-700">Marcar como predeterminada</label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t p-4">
                <button type="button" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-800"
                        wire:click="cerrarModalDireccion">Cancelar</button>
                <button type="button" class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white"
                        wire:click="guardarDireccion">Guardar</button>
            </div>
        </div>
    </div>
    @endif




    {{-- @if ($producto_id)
        <livewire:visuales.layout-render :producto_id="$producto_id" />
    @endif --}}

    
</div>

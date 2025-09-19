<div>
    <!-- Activador -->
    <p class="text-blue-600" >
       <x-link label=" Versiones anteriores y archivos de cliente" wire:click="$set('modalVerArchivosProyecto', true)" />
    </p>
 
    <!-- Modal -->
    @if($modalVerArchivosProyecto)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-5xl max-h-[90vh] overflow-y-auto relative">

                <!-- Título y botón cerrar -->
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold">Archivos del Proyecto</h2>
                    <button wire:click="$set('modalVerArchivosProyecto', false)" class="text-gray-600 hover:text-gray-900 text-2xl font-bold">&times;</button>
                </div>

                <!-- Mensaje de éxito -->
                @if (session()->has('message'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                        {{ session('message') }}
                    </div>
                @endif

                <!-- Pestañas -->
                <div class="flex border-b mb-4 space-x-4">
                    <button wire:click="$set('tab', 'disenos')"
                        class="px-4 py-2 font-medium border-b-4 transition duration-200"
                        :class="{ 'border-blue-600 text-blue-600': @js($tab) === 'disenos', 'border-transparent text-gray-500 hover:text-blue-600': @js($tab) !== 'disenos' }">
                        Diseños
                    </button>
                    <button wire:click="$set('tab', 'iniciales')"
                        class="px-4 py-2 font-medium border-b-4 transition duration-200"
                        :class="{ 'border-blue-600 text-blue-600': @js($tab) === 'iniciales', 'border-transparent text-gray-500 hover:text-blue-600': @js($tab) !== 'iniciales' }">
                        Archivos Iniciales
                    </button>
                </div>

                <!-- Formulario de carga -->
                @hasanyrole('admin|estaf')
                    <form wire:submit.prevent="uploadFile" class="mb-6 flex flex-wrap items-center gap-4">

                        <div class="w-full md:w-auto flex-1" 
                            x-data="{ uploading: false, progress: 0 }"
                            x-on:livewire-upload-start="uploading = true; progress = 0"
                            x-on:livewire-upload-finish="uploading = false"
                            x-on:livewire-upload-error="uploading = false"
                            x-on:livewire-upload-progress="progress = $event.detail.progress">

                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ $tab === 'iniciales' ? 'Archivo inicial' : 'Archivo de diseño' }}
                            </label>

                            <input 
                                type="file"
                                wire:model="archivo"
                                class="block w-full border rounded px-4 py-2"
                                accept=".jpg,.jpeg,.png,.webp,.svg,.ai,.psd,.pdf,.zip"
                            />

                            @error('archivo')
                                <span class="text-red-500 text-sm block mt-1">{{ $message }}</span>
                            @enderror

                            {{-- Anillo y texto mientras sube a storage temporal --}}
                            <div class="mt-2 flex items-center gap-2" wire:loading wire:target="archivo">
                                <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                                <span class="text-sm text-gray-600">Subiendo archivo…</span>
                            </div>

                            {{-- Barra de progreso (cliente) --}}
                            <div x-show="uploading" class="mt-2">
                                <div class="h-2 bg-gray-200 rounded">
                                    <div class="h-2 bg-blue-600 rounded" :style="`width: ${progress}%;`"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1" x-text="`${progress}%`"></p>
                            </div>

                            {{-- Vista previa si es imagen --}}
                            @if($archivo && str_starts_with($archivo->getMimeType(), 'image/'))
                                <div class="mt-3">
                                    <img src="{{ $archivo->temporaryUrl() }}" alt="Vista previa"
                                        class="h-32 rounded object-cover ring-1 ring-gray-200">
                                </div>
                            @endif

                            {{-- Nombre final que se usará al guardar --}}
                            @if($archivoNombreFinal)
                                <p class="mt-2 text-xs text-gray-600">
                                    Se guardará como: <span class="font-mono">{{ $archivoNombreFinal }}</span>
                                </p>
                            @endif

                            {{-- Aviso de duplicado (improbable por timestamp, pero lo mostramos si sucede) --}}
                            @if($archivoDuplicado)
                                <p class="mt-1 text-xs text-rose-600">
                                    Ya existe un archivo con ese nombre final.
                                </p>
                            @endif
                        </div>

                        <button
                            type="submit"
                            class="w-full sm:w-auto px-4 py-2 text-white bg-blue-500 hover:bg-blue-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            wire:loading.attr="disabled"
                            wire:target="archivo,uploadFile"
                            @disabled($archivoDuplicado)
                            title="{{ $archivoDuplicado ? 'Nombre duplicado' : '' }}"
                        >
                            Subir
                        </button>
                    </form>
                @endhasanyrole

                <!-- Buscador -->
                <div class="mb-4">
                    <input
                        type="text"
                        wire:model.debounce.500ms="search"
                        placeholder="Buscar por nombre de archivo..."
                        class="w-full border px-4 py-2 rounded"
                    />
                </div>

               <!-- Tabla -->
                <div class="overflow-x-auto">
                    <table class="table-auto w-full border-collapse border border-gray-300">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-2 border">Nombre del Archivo</th>
                                <th class="px-4 py-2 border">Cargado Por</th>
                                <th class="px-4 py-2 border">Hora de Subida</th>
                                @if($tab === 'disenos')
                                    <th class="px-4 py-2 border">Versión</th>
                                @endif
                                <th class="px-4 py-2 border">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($archivos as $archivo)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border">{{ $archivo->nombre_archivo }}</td>
                                    <td class="px-4 py-2 border">{{ $archivo->usuario->name ?? 'Desconocido' }}</td>
                                    <td class="px-4 py-2 border text-sm text-gray-500">
                                        {{ $archivo->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    @if($tab === 'disenos')
                                        <td class="px-4 py-2 border text-center text-sm">
                                            {{ $archivo->version }}
                                        </td>
                                    @endif
                                    <td class="px-4 py-2 border flex flex-col md:flex-row gap-2">
                                        <button
                                            wire:click="downloadFile({{ $archivo->id }})"
                                            class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-center"
                                        >
                                            Descargar
                                        </button>

                                        @hasanyrole('admin|estaf')
                                           @if ($archivo->flag_can_delete)
                                            <button wire:click="deleteFile({{ $archivo->id }})"
                                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-center">
                                                Eliminar
                                            </button>
                                           @endif

                                        @endhasanyrole
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    {{-- Ajustamos el colspan según pestaña --}}
                                    <td colspan="{{ $tab === 'disenos' ? 5 : 4 }}" class="px-4 py-2 text-center text-gray-500">
                                        No hay archivos Cargados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="mt-4">
                    {{ $archivos->links() }}
                </div>
            </div>
        </div>
    @endif
</div>

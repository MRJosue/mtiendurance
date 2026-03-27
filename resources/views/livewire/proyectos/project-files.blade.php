<div>
    <!-- Activador -->
    <p class="text-blue-600 dark:text-blue-400" >
       <x-link label=" Versiones anteriores y archivos de cliente" wire:click="$set('modalVerArchivosProyecto', true)" />
    </p>
 
    <!-- Modal -->
    @if($modalVerArchivosProyecto)
        <div class="dashboard-modal-backdrop">
            <div class="dashboard-modal-panel max-w-5xl">

                <!-- Título y botón cerrar -->
                <div class="flex justify-between items-center mb-4">
                    <h2 class="dashboard-modal-title mb-0">Archivos del Proyecto</h2>
                    <button wire:click="$set('modalVerArchivosProyecto', false)" class="dashboard-modal-close static font-bold">&times;</button>
                </div>

                <!-- Mensaje de éxito -->
                @if (session()->has('message'))
                    <div class="project-alert-success" role="alert">
                        {{ session('message') }}
                    </div>
                @endif

                <!-- Pestañas -->
                <div class="project-tab-list space-x-4">
                    <button wire:click="$set('tab', 'disenos')"
                        class="project-tab-button"
                        :class="{ 'project-tab-button--active': @js($tab) === 'disenos', 'project-tab-button--inactive': @js($tab) !== 'disenos' }">
                        Diseños
                    </button>
                    <button wire:click="$set('tab', 'iniciales')"
                        class="project-tab-button"
                        :class="{ 'project-tab-button--active': @js($tab) === 'iniciales', 'project-tab-button--inactive': @js($tab) !== 'iniciales' }">
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

                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                                {{ $tab === 'iniciales' ? 'Archivo inicial' : 'Archivo de diseño' }}
                            </label>

                            <input 
                                type="file"
                                wire:model="archivo"
                                class="dashboard-input block w-full px-4 py-2"
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
                                <span class="text-sm text-gray-600 dark:text-gray-300">Subiendo archivo…</span>
                            </div>

                            {{-- Barra de progreso (cliente) --}}
                            <div x-show="uploading" class="mt-2">
                                <div class="h-2 rounded bg-gray-200 dark:bg-gray-700">
                                    <div class="h-2 bg-blue-600 rounded" :style="`width: ${progress}%;`"></div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-text="`${progress}%`"></p>
                            </div>

                            {{-- Vista previa si es imagen --}}
                            @if($archivo && str_starts_with($archivo->getMimeType(), 'image/'))
                                <div class="mt-3">
                                    <img src="{{ $archivo->temporaryUrl() }}" alt="Vista previa"
                                        class="h-32 rounded object-cover ring-1 ring-gray-200 dark:ring-gray-700">
                                </div>
                            @endif

                            {{-- Nombre final que se usará al guardar --}}
                            @if($archivoNombreFinal)
                                <p class="mt-2 text-xs text-gray-600 dark:text-gray-300">
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
                        class="dashboard-input px-4 py-2"
                    />
                </div>

               <!-- Tabla -->
                <div class="overflow-x-auto">
                    <table class="dashboard-table table-auto w-full border-collapse border border-gray-300 dark:border-gray-700">
                        <thead>
                            <tr class="dashboard-table-head">
                                <th class="dashboard-table-th border border-gray-300 dark:border-gray-700">Nombre del Archivo</th>
                                <th class="dashboard-table-th border border-gray-300 dark:border-gray-700">Cargado Por</th>
                                <th class="dashboard-table-th border border-gray-300 dark:border-gray-700">Hora de Subida</th>
                                @if($tab === 'disenos')
                                    <th class="dashboard-table-th border border-gray-300 dark:border-gray-700">Versión</th>
                                @endif
                                <th class="dashboard-table-th border border-gray-300 dark:border-gray-700">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($archivos as $archivo)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60">
                                    <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">{{ $archivo->nombre_archivo }}</td>
                                    <td class="px-4 py-2 border border-gray-300 dark:border-gray-700">{{ $archivo->usuario->name ?? 'Desconocido' }}</td>
                                    <td class="px-4 py-2 border border-gray-300 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                        {{ $archivo->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    @if($tab === 'disenos')
                                        <td class="px-4 py-2 border border-gray-300 text-center text-sm dark:border-gray-700">
                                            {{ $archivo->version }}
                                        </td>
                                    @endif
                                    <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 flex flex-col md:flex-row gap-2">
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
                                    <td colspan="{{ $tab === 'disenos' ? 5 : 4 }}" class="px-4 py-2 text-center text-gray-500 dark:text-gray-400">
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

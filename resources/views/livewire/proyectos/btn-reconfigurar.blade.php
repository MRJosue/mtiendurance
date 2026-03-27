<div>
    <div class="mb-4 flex flex-wrap gap-2">
                    <button wire:click="$set('modalReconfigurar', true)"
                        class="rounded-md bg-amber-300 px-4 py-2 font-semibold text-amber-900 shadow-sm transition hover:bg-amber-400 dark:bg-amber-500/20 dark:text-amber-200 dark:hover:bg-amber-500/30">
                        Reconfigurar proyecto
                    </button>
    </div>


    @if($modalReconfigurar)
        <div class="dashboard-modal-backdrop">
            <div class="dashboard-modal-panel max-w-md">
                <h2 class="text-xl font-bold mb-4">Subir Archivo de Diseño</h2>

                <div x-data="{ uploading: false, progress: 0 }"
                    x-on:livewire-upload-start="uploading = true; progress = 0"
                    x-on:livewire-upload-finish="uploading = false"
                    x-on:livewire-upload-error="uploading = false"
                    x-on:livewire-upload-progress="progress = $event.detail.progress">

                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Arte (1 archivo)</label>
                    <input
                        type="file"
                        class="dashboard-input px-3 py-2"
                        wire:model="archivo"
                        accept=".jpg,.jpeg,.png,.webp,.svg,.ai,.psd,.pdf,.zip"
                    />
                    @error('archivo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                    <div class="mt-2 flex items-center gap-2" wire:loading wire:target="archivo">
                        <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span class="text-sm text-gray-600 dark:text-gray-300">Subiendo archivo…</span>
                    </div>

                    <div x-show="uploading" class="mt-2">
                        <div class="h-2 rounded bg-gray-200 dark:bg-gray-700">
                            <div class="h-2 bg-blue-600 rounded" :style="`width: ${progress}%;`"></div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-text="`${progress}%`"></p>
                    </div>

                    @if($archivo && str_starts_with($archivo->getMimeType(), 'image/'))
                        <div class="mt-3">
                            <img src="{{ $archivo->temporaryUrl() }}" alt="Vista previa"
                                class="h-32 rounded object-cover ring-1 ring-gray-200 dark:ring-gray-700">
                        </div>
                    @endif

                    @if($archivo)
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200">
                                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414L8.5 14.914a1 1 0 01-1.414 0L3.293 11.12a1 1 0 011.414-1.414L7.5 12.5l7.793-7.793a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Archivo listo (temporal)
                            </span>

                            @if($archivoDuplicado)
                                <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-rose-100 text-rose-800 ring-1 ring-rose-200">
                                    Este nombre ya existe en el proyecto
                                </span>
                            @endif
                        </div>
                    @endif
                </div>

                <textarea wire:model="comentario" placeholder="Comentario (opcional)" class="dashboard-input mb-3 min-h-[96px] p-2"></textarea>
                @error('comentario') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                <div class="flex justify-end space-x-2">
                    <button wire:click="$set('modalSubirArchivoDiseno', false)"
                            class="project-button-secondary">
                        Cancelar
                    </button>
                    <button
                        wire:click="subirArchivoDiseno"
                        class="project-button-primary disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                        wire:target="archivo,subirArchivoDiseno"
                        @disabled($archivoDuplicado)
                        title="{{ $archivoDuplicado ? 'Ya existe un archivo con ese nombre' : '' }}"
                    >
                        Subir
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

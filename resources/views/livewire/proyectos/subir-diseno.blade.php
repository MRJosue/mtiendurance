<div>
<!-- Botones por estado -->
<!-- Botones por estado -->
<div class="flex flex-wrap gap-2 mb-4">

    {{-- Añadir la validacion si el proyecto no tiene archivos iniciales permitir subir arte al usuario  cliente--}}
    @can('proyectodiseñoSubirArte')
           @if($estado === 'PENDIENTE' || $estado === 'ASIGNADO' || $estado === 'EN PROCESO')
                <button wire:click="$set('modalSubirArchivoDiseno', true)"
                    class="px-4 py-2 rounded-md bg-amber-300 text-amber-900 hover:bg-amber-400 transition font-semibold shadow-sm">
                    Subir arte
                </button>
            @endif 
    @endcan


    {{-- si esta en proceso y aparte eres diseñador --}}
    @can('proyectodiseñoSubirArchivoDiseño')
        @if($estado === 'EN PROCESO' || $estado === 'DISEÑO RECHAZADO')
            <button wire:click="$set('modalOpen', true)"
                class="px-4 py-2 rounded-md bg-yellow-300 text-yellow-900 hover:bg-yellow-400 transition font-semibold shadow-sm">
                Subir Archivo de Diseño
            </button>
        @endif 
    @endcan


    @if($estado === 'REVISION' )
        @can('notificaralclienteproyecto')
            <button
                wire:click="notificarEstatus"
                class="px-4 py-2 rounded-md bg-yellow-300 text-yellow-900 hover:bg-yellow-400 transition font-semibold shadow-sm"
            >
                Notificar Estatus
            </button>
        @endcan
    @endif


    {{-- si eres cliente o admin --}}
    @if($estado === 'REVISION')

        @can('proyectodiseñoAprobarDiseño')
            <button
                wire:click="$set('modalAprobar', true)"
                class="px-4 py-2 rounded-md bg-green-300 text-green-900 hover:bg-green-400 transition font-semibold shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                @disabled($bloqueadoPorMuestras)
                title="{{ $bloqueadoPorMuestras ? 'Hay muestras en proceso: no puedes aprobar.' : '' }}"
            >
                Aprobar Diseño
            </button>
        @endcan

        @can('proyectodiseñoRechazarDiseño')
            <button
                wire:click="$set('modalRechazar', true)"
                class="px-4 py-2 rounded-md bg-rose-300 text-rose-900 hover:bg-rose-400 transition font-semibold shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                @disabled($bloqueadoPorMuestras)
                title="{{ $bloqueadoPorMuestras ? 'Hay muestras en proceso: no puedes rechazar.' : '' }}"
            >
                Rechazar Diseño
            </button>
        @endcan

        @can('proyectodiseñoCrearMuestra')
            <button
                wire:click="$set('modalConfirmarMuestra', true)"
                class="px-4 py-2 rounded-md bg-orange-300 text-orange-900 hover:bg-orange-400 transition font-semibold shadow-sm"
            >
                Crear Muestra
            </button>
        @endcan

    @endif


    {{-- Botón Solicitar Reconfiguración --}}
    @if ( (!$flag_solicitud_reconfigurar) && ($flag_reconfigurar || $proyectoIncompleto) )

        @can('proyectodiseñoconfirmarSolicitudReconfiguracion')
            <button wire:click="confirmarSolicitudReconfiguracion"
                class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700 transition font-semibold shadow-sm">
                Solicitar Reconfiguración
            </button>
        @endcan

    @endif

    @if ($flag_solicitud_reconfigurar)

        @can('proyectodiseñoreconfigurarproyecto')
                    <button
                wire:click="$set('modalreconfigurar', true)"
                class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 transition font-semibold shadow-sm"
            >
                Reconfigurar proyecto
            </button>
        @endcan

    @endif

</div>

<!-- Alertas -->

@if ($bloqueadoPorMuestras)
    @hasrole('cliente')
        <div class="bg-amber-100 border border-amber-300 text-amber-900 px-4 py-3 rounded-md mb-4 text-sm shadow-sm">
            <strong class="font-semibold">Acción bloqueada:</strong>
            <span class="ml-1">
                No puedes aprobar o rechazar el diseño mientras existan muestras Pendientes
                <span class="font-semibold">ENTREGADA</span> o <span class="font-semibold">CANCELADA</span>.
                
            </span>
        </div>
    @endhasrole
@endif


@if (session()->has('error'))
    <div class="bg-rose-100 border border-rose-300 text-rose-800 px-4 py-3 rounded-md mb-4 text-sm shadow-sm">
        <strong class="font-semibold">Error:</strong>
        <span class="ml-1">{{ session('error') }}</span>
    </div>
@endif

@if (session()->has('message'))
    <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-md mb-4 text-sm shadow-sm">
        <strong class="font-semibold">Éxito:</strong>
        <span class="ml-1">{{ session('message') }}</span>
    </div>
@endif

    <!-- Modal de subir archivo -->
    @if($modalOpen)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
<h2 class="text-xl font-bold mb-4">Subir Archivo de Diseño</h2>

<div x-data="{ uploading: false, progress: 0 }"
     x-on:livewire-upload-start="uploading = true; progress = 0"
     x-on:livewire-upload-finish="uploading = false"
     x-on:livewire-upload-error="uploading = false"
     x-on:livewire-upload-progress="progress = $event.detail.progress">

    <label class="block text-sm font-medium text-gray-700 mb-1">Archivo de diseño</label>
    <input
        type="file"
        class="w-full border rounded px-3 py-2"
        wire:model="archivo"
        accept=".jpg,.jpeg,.png,.webp,.svg,.ai,.psd,.pdf,.zip"
    />
    @error('archivo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

    {{-- Anillo durante la subida temporal --}}
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

    {{-- Estado cuando ya terminó la subida temporal --}}
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

<textarea wire:model="comentario" placeholder="Comentario (opcional)" class="w-full border rounded p-2 mb-3 mt-3"></textarea>
@error('comentario') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

<div class="flex justify-end space-x-2">
    <button wire:click="$set('modalOpen', false)"
            class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
        Cancelar
    </button>
    <button
        wire:click="subir"
        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded disabled:opacity-50 disabled:cursor-not-allowed"
        wire:loading.attr="disabled"
        wire:target="archivo,subir"
        @disabled($archivoDuplicado)
        title="{{ $archivoDuplicado ? 'Ya existe un archivo con ese nombre' : '' }}"
    >
        Subir
    </button>
</div>
            </div>
        </div>
    @endif

    @if($modalConfirmarMuestra)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <h2 class="text-lg font-semibold mb-4">Confirmar creación de muestra</h2>

                {{-- Mostrar datos del archivo --}}
                <div class="mb-4 space-y-1">
                    <p>
                        <span class="font-semibold">Archivo:</span>
                        {{ $ultimoArchivo?->nombre_archivo ?? '-' }}
                    </p>
                    <p>
                        <span class="font-semibold">Versión:</span>
                        {{ $ultimoArchivo?->version ?? '' }}
                    </p>
                    <p>
                        <span class="font-semibold">Cargado el:</span>
                        {{ optional($ultimoArchivo?->created_at)->format('Y-m-d H:i') ?? '–' }}
                    </p>
                </div>

                {{-- Cantidad solicitada --}}
                <label class="block mb-1 font-medium">Cantidad solicitada</label>
                <input
                    type="number"
                    min="1"
                    max='10'
                    wire:model="cantidadMuestra"
                    class="w-full border rounded p-2 mb-3"
                />
                @error('cantidadMuestra') 
                    <span class="text-red-500 text-sm">{{ $message }}</span> 
                @enderror

                {{-- Instrucciones --}}
                <label class="block mb-1 font-medium">Instrucciones</label>
                <textarea
                    wire:model="instruccionesMuestra"
                    class="w-full border rounded p-2 mb-4"
                    rows="3"
                    placeholder="Describe motivos o detalles adicionales (opcional)"
                ></textarea>
                @error('instruccionesMuestra') 
                    <span class="text-red-500 text-sm">{{ $message }}</span> 
                @enderror

                <div class="flex justify-end space-x-2">
                    <button
                        wire:click="$set('modalConfirmarMuestra', false)"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded"
                    >
                        Cancelar
                    </button>
                    <button
                        wire:click="crearMuestraDesdeDiseno"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded"
                    >
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de aprobar diseño -->
    @if($modalAprobar)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
                <h2 class="text-lg font-semibold mb-4">Confirmar Aprobación</h2>
                <p class="mb-4">Al aprobar el diseño, no podrás hacer modificaciones ni solicitar muestras adicionales.</p>
                <div class="flex justify-end space-x-2">
                    <button wire:click="$set('modalAprobar', false)" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">Cancelar</button>
                    <button wire:click="aprobarDiseno" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">Confirmar</button>
                </div>
            </div>
        </div>
    @endif

        <!-- Modal de aprobar Pedido -->
    @if($modalAprobarPedido)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
                <h2 class="text-lg font-semibold mb-4">¿Aprobar pedido?</h2>
                <p class="mb-4">¿Deseas aprobar y programar el pedido generado para este proyecto?</p>
                <div class="flex justify-end space-x-2">
                    <button wire:click="$set('modalAprobarPedido', false)" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">Cancelar</button>
                    <button wire:click="aprobarUltimoPedido" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Aprobar Pedido</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de rechazar diseño -->
    @if($modalRechazar)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
                <h2 class="text-lg font-semibold mb-4">Motivo de Rechazo</h2>
                <textarea wire:model="comentarioRechazo" placeholder="Escribe el motivo del rechazo" class="w-full border rounded p-2 mb-3"></textarea>
                @error('comentarioRechazo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                <div class="flex justify-end space-x-2">
                    <button wire:click="$set('modalRechazar', false)" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">Cancelar</button>
                    <button wire:click="rechazarDiseno" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">Rechazar</button>
                </div>
            </div>
        </div>
    @endif


    @if($modalSubirArchivoDiseno)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
                <h2 class="text-xl font-bold mb-4">Subir Archivo de Diseño</h2>

                <div x-data="{ uploading: false, progress: 0 }"
                    x-on:livewire-upload-start="uploading = true; progress = 0"
                    x-on:livewire-upload-finish="uploading = false"
                    x-on:livewire-upload-error="uploading = false"
                    x-on:livewire-upload-progress="progress = $event.detail.progress">

                    <label class="block text-sm font-medium text-gray-700 mb-1">Arte (1 archivo)</label>
                    <input
                        type="file"
                        class="w-full border rounded px-3 py-2"
                        wire:model="archivo"
                        accept=".jpg,.jpeg,.png,.webp,.svg,.ai,.psd,.pdf,.zip"
                    />
                    @error('archivo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                    <div class="mt-2 flex items-center gap-2" wire:loading wire:target="archivo">
                        <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span class="text-sm text-gray-600">Subiendo archivo…</span>
                    </div>

                    <div x-show="uploading" class="mt-2">
                        <div class="h-2 bg-gray-200 rounded">
                            <div class="h-2 bg-blue-600 rounded" :style="`width: ${progress}%;`"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1" x-text="`${progress}%`"></p>
                    </div>

                    @if($archivo && str_starts_with($archivo->getMimeType(), 'image/'))
                        <div class="mt-3">
                            <img src="{{ $archivo->temporaryUrl() }}" alt="Vista previa"
                                class="h-32 rounded object-cover ring-1 ring-gray-200">
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

                <textarea wire:model="comentario" placeholder="Comentario (opcional)" class="w-full border rounded p-2 mb-3"></textarea>
                @error('comentario') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                <div class="flex justify-end space-x-2">
                    <button wire:click="$set('modalSubirArchivoDiseno', false)"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                        Cancelar
                    </button>
                    <button
                        wire:click="subirArchivoDiseno"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded disabled:opacity-50 disabled:cursor-not-allowed"
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


    @if($modalsolicitarreconfiguracion)
        <div class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
                <h2 class="text-lg font-semibold mb-3">Confirmar solicitud de reconfiguración</h2>
                <p class="text-gray-600 mb-4">
                    ¿Deseas solicitar la reconfiguración de este proyecto? Se notificará al equipo y se habilitará el proceso.
                </p>

                <div class="flex justify-end gap-2">
                    <button
                        wire:click="$set('modalsolicitarreconfiguracion', false)"
                        class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-800"
                    >Cancelar</button>

                    <button
                        wire:click="confirmarSolicitudReconfiguracion"
                        class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white"
                    >Confirmar</button>
                </div>
            </div>
        </div>
    @endif

    
    @if($modalreconfigurar)
        <div class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
                <h2 class="text-lg font-semibold mb-3">Iniciar reconfiguración</h2>
                <p class="text-gray-600 mb-4">
                    Esto te llevará a la pantalla de reprogramación del proyecto. ¿Deseas continuar?
                </p>

                <div class="flex justify-end gap-2">
                    <button
                        wire:click="$set('modalreconfigurar', false)"
                        class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-800"
                    >Cancelar</button>

                    <button
                        wire:click="confirmarReconfigurar"
                        class="px-4 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white"
                    >Continuar</button>
                </div>
            </div>
        </div>
    @endif

</div>

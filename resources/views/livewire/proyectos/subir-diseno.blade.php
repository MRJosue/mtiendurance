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



    {{-- si eres cliente o admin --}}
    @if($estado === 'REVISION')
        
        @can('proyectodiseñoAprobarDiseño')
            <button wire:click="$set('modalAprobar', true)"
                class="px-4 py-2 rounded-md bg-green-300 text-green-900 hover:bg-green-400 transition font-semibold shadow-sm">
                Aprobar Diseño
            </button>        
        @endcan


        @can('proyectodiseñoRechazarDiseño')
            <button wire:click="$set('modalRechazar', true)"
                class="px-4 py-2 rounded-md bg-rose-300 text-rose-900 hover:bg-rose-400 transition font-semibold shadow-sm">
                Rechazar Diseño
            </button>            
        @endcan

        @can('proyectodiseñoCrearMuestra')
                <button wire:click="$set('modalConfirmarMuestra', true)"
                    class="px-4 py-2 rounded-md bg-orange-300 text-orange-900 hover:bg-orange-400 transition font-semibold shadow-sm">
                    Crear Muestra
                </button>
        @endcan



    @endif
</div>

<!-- Alertas -->
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
                <input type="file" wire:model="archivo" class="mb-3">
                @error('archivo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                <textarea wire:model="comentario" placeholder="Comentario (opcional)" class="w-full border rounded p-2 mb-3"></textarea>
                @error('comentario') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                <div class="flex justify-end space-x-2">
                    <button wire:click="$set('modalOpen', false)" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">Cancelar</button>
                    <button wire:click="subir" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Subir</button>
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
                <input type="file" wire:model="archivo" class="mb-3">
                @error('archivo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                <textarea wire:model="comentario" placeholder="Comentario (opcional)" class="w-full border rounded p-2 mb-3"></textarea>
                @error('comentario') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                <div class="flex justify-end space-x-2">
                    <button wire:click="$set('modalSubirArchivoDiseno', false)" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">Cancelar</button>
                    <button wire:click="subirArchivoDiseno" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Subir</button>
                </div>
            </div>
        </div>
    @endif
</div>

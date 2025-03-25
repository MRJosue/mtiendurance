<div>
    @if($estado === 'EN PROCESO')
        <!-- Botón para subir diseño -->
        <button wire:click="$set('modalOpen', true)" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4">
            Subir Archivo de Diseño
        </button>
    @endif

    @if($estado === 'REVISION')
        <!-- Botón Aprobar Diseño -->
        <button wire:click="$set('modalAprobar', true)" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mb-4 ml-2">
            Aprobar Diseño
        </button>

        <!-- Botón Rechazar Diseño -->
        <button wire:click="$set('modalRechazar', true)" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded mb-4 ml-2">
            Rechazar Diseño
        </button>
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
</div>

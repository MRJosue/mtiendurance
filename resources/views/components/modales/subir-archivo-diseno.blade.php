<div>
    <input type="file" wire:model="archivo" class="mb-3">
    @error('archivo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

    <textarea wire:model="comentario" class="w-full border rounded p-2 mb-3" placeholder="Comentario (opcional)"></textarea>
    @error('comentario') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

    <div class="flex justify-end space-x-2">
        <button wire:click="dispatch('cerrarModalGlobal')" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">Cancelar</button>
        <button wire:click="dispatch('subirArchivoDiseno')" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Subir</button>
    </div>
</div>

<div>
    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 text-sm p-2 rounded mb-3">
            {{ session('message') }}
        </div>
    @endif

    <input
        type="text"
        wire:model.defer="nombrePermiso"
        class="w-full border rounded p-2 mb-2"
        placeholder="Nombre del nuevo permiso"
    >
    @error('nombrePermiso') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

    <div class="flex justify-end">
        <button wire:click="crear" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            Crear
        </button>
    </div>
</div>
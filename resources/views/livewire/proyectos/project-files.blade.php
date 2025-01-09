<div>
    <h2 class="text-lg font-bold mb-4">Archivos del Proyecto</h2>

    <!-- Mensaje de éxito -->
    @if (session()->has('message'))
        <div class="bg-green-200 text-green-800 p-2 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <!-- Formulario para subir archivos -->
    <form wire:submit.prevent="uploadFile" class="mb-4">
        <div class="flex items-center">
            <input type="file" wire:model="archivo" class="border rounded-l px-4 py-2">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-r">
                Subir
            </button>
        </div>
        @error('archivo')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </form>

    <!-- Lista de archivos -->
    <table class="table-auto w-full border">
        <thead>
            <tr class="bg-gray-100">
                <th class="px-4 py-2 border">Nombre del Archivo</th>
                <th class="px-4 py-2 border">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($archivos as $archivo)
                <tr>
                    <td class="px-4 py-2 border">{{ $archivo->nombre_archivo }}</td>
                    <td class="px-4 py-2 border">
                        <a href="{{ Storage::disk('public')->url($archivo->ruta_archivo) }}" 
                           class="text-blue-500 underline" target="_blank">Descargar</a>
                        <button wire:click="deleteFile({{ $archivo->id }})" 
                                class="text-red-500 underline ml-2">Eliminar</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="px-4 py-2 text-center">No hay archivos subidos.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

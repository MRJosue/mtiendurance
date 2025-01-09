<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Archivos del Proyecto</h2>

    <!-- Mensaje de éxito -->
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
            {{ session('message') }}
        </div>
    @endif

    <!-- Formulario para subir archivos -->
    <form wire:submit.prevent="uploadFile" class="mb-6 flex flex-wrap items-center gap-4">
        <input type="file" wire:model="archivo" class="block w-full md:w-auto border rounded px-4 py-2">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            Subir
        </button>
        @error('archivo')
            <span class="text-red-500 text-sm block w-full">{{ $message }}</span>
        @enderror
    </form>

    <!-- Lista de archivos -->
    <div class="overflow-x-auto">
        <table class="table-auto w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 border">Nombre del Archivo</th>
                    <th class="px-4 py-2 border">Subido Por</th>
                    <th class="px-4 py-2 border">Hora de Subida</th>
                    <th class="px-4 py-2 border">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($archivos as $archivo)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 border">{{ $archivo->nombre_archivo }}</td>
                        <td class="px-4 py-2 border">{{ $archivo->usuario->name ?? 'Desconocido' }}</td>
                        <td class="px-4 py-2 border text-gray-500 text-sm">
                            {{ $archivo->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-2 border flex flex-col md:flex-row gap-2">
                            <a href="{{ Storage::disk('public')->url($archivo->ruta_archivo) }}" 
                               class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-center" 
                               target="_blank">Descargar</a>
                            <button wire:click="deleteFile({{ $archivo->id }})" 
                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-center">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-2 text-center text-gray-500">No hay archivos subidos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

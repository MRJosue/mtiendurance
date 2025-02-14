<div class="container mx-auto p-6">
    <h2 class="text-xl font-semibold mb-4">Archivo Actual</h2>

    @if ($ultimoArchivo)
        @php
            // Verificar si el archivo existe en storage
            $rutaArchivo = Storage::disk('public')->exists($ultimoArchivo->ruta_archivo) 
                ? asset('storage/' . $ultimoArchivo->ruta_archivo) 
                : $ultimoArchivo->ruta_archivo;
        @endphp

        <div class="relative group">
            <img id="archivoImagen" src="{{ $rutaArchivo }}" 
                 class="w-full max-w-md mx-auto rounded-lg shadow-lg transition-transform transform hover:scale-110 cursor-pointer"
                 alt="{{ $ultimoArchivo->nombre_archivo }}"
                 onclick="expandirImagen()">
            
            <p class="mt-2 text-gray-700 text-sm text-center">{{ $ultimoArchivo->descripcion }}</p>
        </div>

        <!-- Modal para pantalla completa -->
        <div id="modalImagen" class="fixed inset-0 bg-black bg-opacity-75 hidden items-center justify-center">
            <span class="absolute top-4 right-6 text-white text-3xl cursor-pointer" onclick="cerrarImagen()">&times;</span>
            <img id="imagenExpandida" class="max-w-full max-h-full">
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const img = document.getElementById('archivoImagen');
                img.addEventListener('click', () => {
                    expandirImagen();
                });
            });

            function expandirImagen() {
                const modal = document.getElementById("modalImagen");
                const imgExpandida = document.getElementById("imagenExpandida");
                imgExpandida.src = document.getElementById("archivoImagen").src;
                modal.classList.remove("hidden");
                modal.classList.add("flex");
            }

            function cerrarImagen() {
                document.getElementById("modalImagen").classList.add("hidden");
            }
        </script>
    @else
        <p class="text-gray-500">No se encontraron archivos para este proyecto.</p>
    @endif
</div>

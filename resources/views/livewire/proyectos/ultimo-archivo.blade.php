<div class="container mx-auto p-6">
<div class="flex items-center justify-between mb-4">
    <h2 class="text-lg sm:text-xl font-semibold text-gray-800 flex items-center gap-2">

        Diseño Actual
    </h2>
    
    <!-- Botón discreto para abrir modal -->
  <livewire:proyectos.project-files :proyecto-id="$this->proyectoId" />
</div>

    <div class="mb-6">
        @if ($ultimoArchivo)
            @php
                $rutaArchivo = Storage::disk('public')->exists($ultimoArchivo->ruta_archivo) 
                    ? asset('storage/' . $ultimoArchivo->ruta_archivo) 
                    : $ultimoArchivo->ruta_archivo;
            @endphp

            <div class="relative group transition-all duration-300 ease-in-out">
                <img id="archivoImagen" 
                    src="{{ $rutaArchivo }}" 
                    class="w-full max-w-md mx-auto rounded-lg shadow-lg transform transition-all duration-300 ease-in-out hover:scale-110 cursor-pointer"
                    alt="{{ $ultimoArchivo->nombre_archivo }}"
                    onclick="expandirImagen()">
                
                <p class="mt-2 text-gray-700 text-sm text-center transition-all duration-300 ease-in-out">
                    {{ $ultimoArchivo->descripcion }}
                </p>
            </div>

            <!-- Modal con animación suave -->
            <div id="modalImagen" 
                class="fixed inset-0 bg-black bg-opacity-75 hidden items-center justify-center transition-all duration-300 ease-in-out">
                <div class="relative scale-95 opacity-0 transition-all duration-300 ease-in-out" id="modalContenido">
                    <button 
                        class="absolute top-2 right-2 bg-white text-black rounded-full w-8 h-8 flex items-center justify-center font-bold shadow hover:bg-gray-200 transition-all duration-300 ease-in-out"
                        onclick="cerrarImagen()">
                        &times;
                    </button>
                    <img id="imagenExpandida" 
                        class="max-w-screen max-h-screen rounded-lg transition-all duration-300 ease-in-out">
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const img = document.getElementById('archivoImagen');
                    img.addEventListener('click', expandirImagen);
                });

                function expandirImagen() {
                    const modal = document.getElementById("modalImagen");
                    const contenido = document.getElementById("modalContenido");
                    const imgExpandida = document.getElementById("imagenExpandida");

                    imgExpandida.src = document.getElementById("archivoImagen").src;

                    modal.classList.remove("hidden");
                    modal.classList.add("flex");

                    // Animar aparición
                    setTimeout(() => {
                        contenido.classList.remove("scale-95", "opacity-0");
                        contenido.classList.add("scale-100", "opacity-100");
                    }, 10);
                }

                function cerrarImagen() {
                    const modal = document.getElementById("modalImagen");
                    const contenido = document.getElementById("modalContenido");

                    contenido.classList.remove("scale-100", "opacity-100");
                    contenido.classList.add("scale-95", "opacity-0");

                    setTimeout(() => {
                        modal.classList.remove("flex");
                        modal.classList.add("hidden");
                    }, 300); // debe coincidir con duration-300
                }
            </script>
        @else
            <p class="text-gray-500 transition-all duration-300 ease-in-out">No se encontraron archivos para este proyecto.</p>
        @endif
    </div>
</div>

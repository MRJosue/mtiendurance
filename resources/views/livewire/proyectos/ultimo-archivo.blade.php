<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
  <!-- Encabezado: flex-col en móvil, flex-row en desktop -->
  <div class="flex flex-col sm:flex-row items-center justify-between mb-6">
    <h2 class="text-lg sm:text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4 sm:mb-0 flex items-center gap-2">
      Diseño Actual
    </h2>

  </div>

  <!-- Contenido principal: centrado y tamaño adaptable -->
  <div class="mb-6 flex flex-col items-center">
    @if ($ultimoArchivo)
      @php
        $rutaArchivo = Storage::disk('public')->exists($ultimoArchivo->ruta_archivo)
          ? asset('storage/' . $ultimoArchivo->ruta_archivo)
          : $ultimoArchivo->ruta_archivo;
      @endphp

    <div class="relative group transition-all duration-300 ease-in-out w-full max-w-xs sm:max-w-md md:max-w-lg">
        <img id="archivoImagen"
            src="{{ $rutaArchivo }}"
            class="w-full h-auto max-h-[70vh] rounded-lg shadow-lg cursor-pointer object-contain"
            alt="{{ $ultimoArchivo->nombre_archivo }}"
            onclick="expandirImagen()">
        <!-- Descripción y metadatos -->
        <div class="mt-4 text-center w-full px-2">
            <p class="mt-2 text-gray-600 text-sm sm:text-base">
                <span class="font-semibold">Nombre:</span> {{ $ultimoArchivo->nombre_archivo }}
                <span class="ml-4 font-semibold">Versión:</span> {{ $ultimoArchivo->version }}
            </p>
            <p class="text-gray-700 text-base sm:text-lg">
                Comentario: {{ $ultimoArchivo->descripcion }}
            </p>
        </div>
    </div>


      <!-- Modal con AlpineJS: padding adaptativo -->
      {{-- <div id="modalImagen"
           x-data="imageZoom()"
           x-show="open"
           @open-image.window="openModal($event.detail)"
           x-cloak
           class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 sm:p-8"> --}}
        <div id="modalImagen"
            x-data="imageZoom()"
            x-show="open"
            x-cloak
            @open-image.window="openModal($event.detail)"
            @keydown.window.escape="close()"
            @click.self="close()"
            class="fixed inset-0 bg-black/75 flex items-center justify-center p-4 sm:p-8
                    z-[100]  <!-- Asegura estar por encima de cualquier z-50/60 -->">
        <div class="relative w-full max-w-full">

          <!-- Controles en esquina superior derecha -->
          <div class="absolute top-4 right-4 flex space-x-2 bg-white/80 rounded-md p-3 shadow-lg z-[120]">
              <button @click="close()" 
                  class="w-10 h-10 flex items-center justify-center text-2xl font-bold rounded-full bg-red-500 text-white hover:bg-red-600"
                  title="Cerrar">
                  &times;
              </button>
              {{-- <button @click="zoomIn()" 
                  class="w-10 h-10 flex items-center justify-center text-2xl font-bold rounded-full bg-emerald-500 text-white hover:bg-emerald-600"
                  title="Acercar">
                  ＋
              </button>
              <button @click="zoomOut()" 
                  class="w-10 h-10 flex items-center justify-center text-2xl font-bold rounded-full bg-blue-500 text-white hover:bg-blue-600"
                  title="Alejar">
                  －
              </button>
              <button @click="reset()" 
                  class="px-4 py-2 rounded-md bg-gray-700 text-white hover:bg-gray-800 text-sm font-semibold"
                  title="Reset">
                  Reset
              </button> --}}
          </div>
          <div class="overflow-hidden">
            <img x-ref="img"
                 :src="src"
                 class="max-w-full max-h-[80vh] cursor-grab select-none mx-auto"
                 @wheel.prevent="onWheel($event)"
                 @mousedown.prevent="onMouseDown($event)">
            <!-- Controles: flex-col en móvil, flex-row en desktop -->
            <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex flex-col sm:flex-row items-center space-y-2 sm:space-y-0 sm:space-x-2 bg-white bg-opacity-80 rounded-md p-2 shadow">
              <button @click="close()" class="w-8 h-8 flex items-center justify-center text-xl font-bold rounded-full bg-red-500 text-white hover:bg-red-600" title="Cerrar">&times;</button>
              <button @click="zoomIn()" class="px-3 py-1 text-lg font-bold">＋</button>
              <button @click="zoomOut()" class="px-3 py-1 text-lg font-bold">－</button>
              <button @click="reset()" class="px-3 py-1 text-sm">Reset</button>
            </div>
          </div>
        </div>
      </div>
    @else
      <p class="text-gray-500">No se encontraron archivos para este proyecto.</p>
    @endif
  </div>

  <div class="text-center">
        <livewire:proyectos.project-files :proyecto-id="$this->proyectoId" class="w-full sm:w-auto" />
  </div>

  <!-- Scripts: envueltos en DOMContentLoaded -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      window.expandirImagen = () => {
        const img = document.getElementById('archivoImagen');
        if (img) {
          const src = img.src;
          window.dispatchEvent(new CustomEvent('open-image', { detail: src }));
        }
      };
    });

    function imageZoom() {
      return {
        open: false,
        src: '',
        scale: 1,
        translateX: 0,
        translateY: 0,
        startX: 0,
        startY: 0,
        dragging: false,

        openModal(url) {
          this.src = url;
          this.open = true;
          this.reset();
        },

        onWheel(e) {
          const delta = e.deltaY > 0 ? -0.1 : 0.1;
          this.scale = Math.min(Math.max(this.scale + delta, 1), 5);
          this.applyTransform();
        },

        onMouseDown(e) {
          this.dragging = true;
          this.startX = e.clientX - this.translateX;
          this.startY = e.clientY - this.translateY;
          this.$refs.img.classList.add('cursor-grabbing');
          window.addEventListener('mousemove', this.onMouseMove.bind(this));
          window.addEventListener('mouseup', this.onMouseUp.bind(this));
        },

        onMouseMove(e) {
          if (!this.dragging) return;
          this.translateX = e.clientX - this.startX;
          this.translateY = e.clientY - this.startY;
          this.applyTransform();
        },

        onMouseUp() {
          this.dragging = false;
          this.$refs.img.classList.remove('cursor-grabbing');
          window.removeEventListener('mousemove', this.onMouseMove);
          window.removeEventListener('mouseup', this.onMouseUp);
        },

        zoomIn() {
          this.scale = Math.min(this.scale + 0.2, 5);
          this.applyTransform();
        },

        zoomOut() {
          this.scale = Math.max(this.scale - 0.2, 1);
          this.applyTransform();
        },

        reset() {
          this.scale = 1;
          this.translateX = 0;
          this.translateY = 0;
          this.applyTransform();
        },

        applyTransform() {
          this.$refs.img.style.transform =
            `translate(${this.translateX}px, ${this.translateY}px) scale(${this.scale})`;
        },

        close() {
          this.open = false;
        }
      }
    }
  </script>
</div>

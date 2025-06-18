<div class="container mx-auto p-6">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-lg sm:text-xl font-semibold text-gray-800 flex items-center gap-2">
      Diseño Actual
    </h2>
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
             class="w-full max-w-md mx-auto rounded-lg shadow-lg cursor-pointer"
             alt="{{ $ultimoArchivo->nombre_archivo }}"
             onclick="expandirImagen()">
        <p class="mt-2 text-gray-700 text-sm text-center">
          {{ $ultimoArchivo->descripcion }}
        </p>
      </div>

      <!-- Modal con AlpineJS -->
      <div id="modalImagen"
           x-data="imageZoom()"
           x-show="open"
           @open-image.window="openModal($event.detail)"
           x-cloak
           class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4">
        <div class="relative">
          <div class="overflow-hidden">
            <img x-ref="img"
                 :src="src"
                 class="max-w-full max-h-[80vh] cursor-grab select-none"
                 @wheel.prevent="onWheel($event)"
                 @mousedown.prevent="onMouseDown($event)">
            <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2 bg-white bg-opacity-80 rounded-md p-2 shadow">
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

  <!-- 👇 Este script ahora está fuera del @if -->
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

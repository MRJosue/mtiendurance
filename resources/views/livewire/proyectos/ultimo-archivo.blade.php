<div class="project-card w-full overflow-hidden">
    <div class="flex flex-col gap-3 border-b border-slate-200/80 pb-5 dark:border-slate-700/80">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-600 dark:text-cyan-300">
                    Archivo principal
                </p>
                <h2 class="mt-1 text-xl font-semibold text-slate-900 dark:text-slate-100">
                    Diseno actual
                </h2>
            </div>

            @if ($ultimoArchivo)
                <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-400/30">
                    Version {{ $ultimoArchivo->version }}
                </span>
            @endif
        </div>

        <p class="max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
            Revisa la ultima pieza cargada del proyecto y abre la vista ampliada para inspeccionar detalles con zoom.
        </p>
    </div>

    <div class="mt-6">
        @if ($ultimoArchivo)
            @php
                $rutaArchivo = Storage::disk('public')->exists($ultimoArchivo->ruta_archivo)
                    ? asset('storage/' . $ultimoArchivo->ruta_archivo)
                    : $ultimoArchivo->ruta_archivo;
            @endphp

            <div class="space-y-6">
                <div class="relative overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-gradient-to-br from-slate-100 via-white to-cyan-50 p-4 shadow-sm dark:border-slate-700 dark:from-slate-900 dark:via-slate-900 dark:to-cyan-950/40">
                    <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-cyan-400/10 to-transparent dark:from-cyan-300/10"></div>

                    <button
                        type="button"
                        onclick="expandirImagen()"
                        class="relative block w-full overflow-hidden rounded-[1.25rem] border border-white/70 bg-white/80 p-3 text-left shadow-lg shadow-slate-200/70 transition duration-300 hover:-translate-y-0.5 hover:shadow-xl dark:border-slate-700/80 dark:bg-slate-900/80 dark:shadow-none"
                    >
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <span class="inline-flex items-center rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-white dark:bg-slate-100 dark:text-slate-900">
                                Vista previa
                            </span>
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                                Click para ampliar
                            </span>
                        </div>

                        <img
                            id="archivoImagen"
                            src="{{ $rutaArchivo }}"
                            class="h-auto max-h-[72vh] w-full rounded-2xl border border-slate-200/80 object-contain shadow-sm dark:border-slate-700"
                            alt="{{ $ultimoArchivo->nombre_archivo }}"
                        >
                    </button>
                </div>

                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="rounded-[1.5rem] border border-slate-200/80 bg-white/90 p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900/80">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                            Resumen
                        </p>
                        <h3 class="mt-3 break-words text-lg font-semibold text-slate-900 dark:text-slate-100">
                            {{ $ultimoArchivo->nombre_archivo }}
                        </h3>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full bg-cyan-100 px-3 py-1 text-xs font-semibold text-cyan-700 ring-1 ring-cyan-200 dark:bg-cyan-500/10 dark:text-cyan-200 dark:ring-cyan-400/30">
                                Proyecto #{{ $this->proyectoId }}
                            </span>
                            <span class="inline-flex items-center rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-700 ring-1 ring-violet-200 dark:bg-violet-500/10 dark:text-violet-200 dark:ring-violet-400/30">
                                Version {{ $ultimoArchivo->version }}
                            </span>
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-400/30">
                                Ultimo archivo cargado
                            </span>
                        </div>
                    </div>

                    @if ($tieneAprobado && $registroAprobacion)
                        <div class="rounded-[1.5rem] border border-emerald-200/80 bg-emerald-50/80 p-5 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-500/10">
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700 dark:text-emerald-200">
                                Aprobacion
                            </p>
                            <h3 class="mt-3 text-lg font-semibold text-slate-900 dark:text-slate-100">
                                Diseno aprobado
                            </h3>
                            <div class="mt-4 space-y-3 text-sm text-slate-700 dark:text-slate-200">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                                        Fecha
                                    </p>
                                    <p class="mt-1">{{ \Carbon\Carbon::parse($registroAprobacion->fecha_inicio)->format('d/m/Y H:i') }}</p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                                        Aprobado por
                                    </p>
                                    <p class="mt-1">{{ $registroAprobacion->usuario->name ?? 'Desconocido' }}</p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                                        Archivo asociado
                                    </p>
                                    <p class="mt-1 break-words">{{ $ultimoArchivo->nombre_archivo }}</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Version {{ $ultimoArchivo->version }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[1.5rem] border border-slate-200/80 bg-slate-50/90 p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800/70">
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                                Resumen de aprobacion
                            </p>
                            <p class="mt-3 text-sm leading-6 text-slate-700 dark:text-slate-200">
                                {{ $registroAprobacion->comentario ?: 'Sin comentario registrado en la aprobacion del diseno.' }}
                            </p>

                            <div class="mt-4 rounded-xl border border-dashed border-slate-300 bg-white/80 px-4 py-3 dark:border-slate-600 dark:bg-slate-900/40">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                                    Documento
                                </p>
                                <div class="mt-2">
                                    <livewire:proyectos.pdf-aprobacion :proyecto-id="$this->proyectoId" />
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="rounded-[1.5rem] border border-slate-200/80 bg-slate-50/90 p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800/70">
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                                Comentario
                            </p>
                            <p class="mt-3 text-sm leading-6 text-slate-700 dark:text-slate-200">
                                {{ $ultimoArchivo->descripcion ?: 'Sin comentario registrado para esta version.' }}
                            </p>
                        </div>

                        <div class="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50/70 p-5 dark:border-slate-600 dark:bg-slate-800/40">
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">
                                Consejo
                            </p>
                            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                                Usa la vista ampliada para validar detalles finos del diseno antes de aprobar, rechazar o solicitar cambios.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <div
                id="modalImagen"
                x-data="imageZoom()"
                x-show="open"
                x-cloak
                @open-image.window="openModal($event.detail)"
                @keydown.window.escape="close()"
                @click.self="close()"
                class="fixed inset-0 z-[130] flex items-center justify-center bg-black/80 p-4 sm:p-8"
            >
                <div class="relative w-full max-w-6xl">
                    <div class="absolute right-4 top-4 z-[140] flex items-center gap-2 rounded-2xl bg-white/85 p-3 shadow-lg dark:bg-slate-900/85">
                        <button
                            @click="zoomOut()"
                            type="button"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-xl font-bold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700"
                            title="Alejar"
                        >
                            -
                        </button>
                        <button
                            @click="zoomIn()"
                            type="button"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-cyan-500 text-xl font-bold text-white transition hover:bg-cyan-600"
                            title="Acercar"
                        >
                            +
                        </button>
                        <button
                            @click="reset()"
                            type="button"
                            class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white"
                            title="Restablecer"
                        >
                            Reset
                        </button>
                        <button
                            @click="close()"
                            type="button"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-rose-500 text-2xl font-bold text-white transition hover:bg-rose-600"
                            title="Cerrar"
                        >
                            &times;
                        </button>
                    </div>

                    <div class="overflow-hidden rounded-[2rem] border border-white/15 bg-slate-950/70 p-4 shadow-2xl">
                        <img
                            x-ref="img"
                            :src="src"
                            class="mx-auto max-h-[80vh] max-w-full cursor-grab select-none rounded-2xl object-contain"
                            @wheel.prevent="onWheel($event)"
                            @mousedown.prevent="onMouseDown($event)"
                        >

                        <div class="mt-4 flex flex-wrap items-center justify-center gap-2 text-xs font-medium text-slate-200">
                            <span class="rounded-full bg-white/10 px-3 py-1">Rueda para zoom</span>
                            <span class="rounded-full bg-white/10 px-3 py-1">Arrastra para mover</span>
                            <span class="rounded-full bg-white/10 px-3 py-1">ESC para cerrar</span>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-gradient-to-br from-slate-50 to-cyan-50 p-8 text-center shadow-sm dark:border-slate-700 dark:from-slate-900 dark:to-cyan-950/30">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-cyan-600 dark:text-cyan-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M19.5 14.25v-8.25a2.25 2.25 0 00-2.25-2.25h-10.5a2.25 2.25 0 00-2.25 2.25v12a2.25 2.25 0 002.25 2.25h6.75m6-6 3 3m0 0-3 3m3-3h-9" />
                    </svg>
                </div>

                <h3 class="mt-5 text-lg font-semibold text-slate-900 dark:text-slate-100">
                    Aun no hay un diseno disponible
                </h3>
                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                    Cuando se cargue el primer archivo del proyecto, aqui aparecera la vista principal con su informacion mas reciente.
                </p>
            </div>
        @endif
    </div>

    <div class="mt-6 text-center">
        <livewire:proyectos.project-files :proyecto-id="$this->proyectoId" class="w-full sm:w-auto" />
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.expandirImagen = () => {
                const img = document.getElementById('archivoImagen');
                if (img) {
                    window.dispatchEvent(new CustomEvent('open-image', { detail: img.src }));
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
                moveHandler: null,
                upHandler: null,

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

                    this.moveHandler = (event) => this.onMouseMove(event);
                    this.upHandler = () => this.onMouseUp();

                    window.addEventListener('mousemove', this.moveHandler);
                    window.addEventListener('mouseup', this.upHandler);
                },

                onMouseMove(e) {
                    if (!this.dragging) {
                        return;
                    }

                    this.translateX = e.clientX - this.startX;
                    this.translateY = e.clientY - this.startY;
                    this.applyTransform();
                },

                onMouseUp() {
                    this.dragging = false;
                    this.$refs.img.classList.remove('cursor-grabbing');

                    if (this.moveHandler) {
                        window.removeEventListener('mousemove', this.moveHandler);
                    }

                    if (this.upHandler) {
                        window.removeEventListener('mouseup', this.upHandler);
                    }

                    this.moveHandler = null;
                    this.upHandler = null;
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
                    this.$refs.img.style.transform = `translate(${this.translateX}px, ${this.translateY}px) scale(${this.scale})`;
                },

                close() {
                    this.onMouseUp();
                    this.open = false;
                }
            };
        }
    </script>
</div>

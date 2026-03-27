<div>
    @if($archivoAprobado)
        <button wire:click="abrirModal" class="dashboard-text-link text-xs">
            Ver archivo aprobado
        </button>

        @if($verModal)
            <div class="dashboard-modal-backdrop">
                <div class="dashboard-modal-panel max-w-xl p-4">
                    <button wire:click="cerrarModal"
                            class="dashboard-modal-close top-2 right-2 text-lg font-bold">&times;</button>

                    <h2 class="mb-4 text-md font-semibold text-gray-800 dark:text-gray-100">
                        Archivo aprobado: {{ $archivoAprobado->nombre_archivo }}
                    </h2>

                    @php
                        $ruta = \Storage::disk('public')->exists($archivoAprobado->ruta_archivo)
                            ? asset('storage/' . $archivoAprobado->ruta_archivo)
                            : $archivoAprobado->ruta_archivo;
                    @endphp

                    <div class="w-full max-h-[60vh] overflow-auto rounded-xl border border-gray-200 shadow-sm dark:border-gray-700">
                        <img src="{{ $ruta }}"
                             alt="Archivo aprobado"
                             class="w-full h-auto object-contain rounded">
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

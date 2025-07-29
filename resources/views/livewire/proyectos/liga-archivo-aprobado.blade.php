<div>
    @if($archivoAprobado)
        <button wire:click="abrirModal" class="text-blue-600 hover:underline text-xs">
            Ver archivo aprobado
        </button>

        @if($verModal)
            <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-xl p-4 relative">
                    <button wire:click="cerrarModal"
                            class="absolute top-2 right-2 text-gray-500 hover:text-gray-800 text-lg font-bold">&times;</button>

                    <h2 class="text-md font-semibold text-gray-800 mb-4">
                        Archivo aprobado: {{ $archivoAprobado->nombre_archivo }}
                    </h2>

                    @php
                        $ruta = \Storage::disk('public')->exists($archivoAprobado->ruta_archivo)
                            ? asset('storage/' . $archivoAprobado->ruta_archivo)
                            : $archivoAprobado->ruta_archivo;
                    @endphp

                    <div class="w-full max-h-[60vh] overflow-auto border rounded shadow-sm">
                        <img src="{{ $ruta }}"
                             alt="Archivo aprobado"
                             class="w-full h-auto object-contain rounded">
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

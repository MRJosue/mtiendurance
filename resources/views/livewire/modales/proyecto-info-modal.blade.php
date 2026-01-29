<div>
    @if($open && $infoProyecto)
        <div class="fixed inset-0 flex items-center justify-center bg-black/50 z-50 p-2">
            <div class="bg-white p-4 sm:p-6 rounded-lg shadow-lg w-full max-w-2xl relative overflow-y-auto max-h-[90vh]">
                <h2 class="text-xl font-bold mb-4">Detalles del Proyecto</h2>

                <button wire:click="close"
                    class="absolute top-3 right-4 text-gray-500 hover:text-red-600 text-2xl leading-none"
                    title="Cerrar"
                >&times;</button>

                {{-- ... el resto igual ... --}}
            </div>
        </div>
    @endif
</div>

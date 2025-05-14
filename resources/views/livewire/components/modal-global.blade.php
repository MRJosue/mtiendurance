<!-- Este archivo se incluirá una vez en el layout general -->
<div x-data x-show="$wire.entangle('showGlobalModal')" x-cloak
    class="fixed inset-0 z-[9999] bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
        <!-- Aquí puedes usar slots o propiedades para contenido dinámico -->
        <h2 class="text-xl font-bold mb-4">{{ $modalTitle }}</h2>
        {{ $modalContent }}
        <div class="flex justify-end space-x-2 mt-4">
            <button wire:click="cerrarModalGlobal" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                Cancelar
            </button>
        </div>
    </div>
</div>
@if($mostrarModalCliente)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="w-full max-w-md rounded-lg bg-white shadow-lg">
            <div class="flex items-center justify-between border-b border-gray-200 p-4">
                <h5 class="text-xl font-bold">Agregar Cliente</h5>
                <button class="text-gray-500 hover:text-gray-700" wire:click="$set('mostrarModalCliente', false)">&times;</button>
            </div>

            <div class="p-4">
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700">Nombre de Empresa</label>
                    <input type="text" wire:model="nuevoCliente.nombre_empresa" class="w-full rounded border border-gray-300 p-2">
                    @error('nuevoCliente.nombre_empresa') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700">Contacto Principal</label>
                    <input type="text" wire:model="nuevoCliente.contacto_principal" class="w-full rounded border border-gray-300 p-2">
                </div>

                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                    <input type="text" wire:model="nuevoCliente.telefono" class="w-full rounded border border-gray-300 p-2">
                </div>

                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" wire:model="nuevoCliente.email" class="w-full rounded border border-gray-300 p-2">
                </div>

                <div class="flex justify-end space-x-2">
                    <button wire:click="$set('mostrarModalCliente', false)" class="rounded bg-gray-300 px-4 py-2 text-gray-800">
                        Cancelar
                    </button>
                    <button wire:click="guardarCliente" class="rounded bg-blue-500 px-4 py-2 text-white">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

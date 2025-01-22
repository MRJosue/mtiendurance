<div x-data="{ isEditMode: @entangle('isEditMode') }" class="container mx-auto p-6">
    <div class="mb-4">
    
    </div>

    <div class="mb-4">
        <form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}">
            <div class="mb-2">
                <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre de la Ciudad</label>
                <input type="text" id="nombre" wire:model.defer="nombre" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('nombre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mb-2">
                <label for="estado_id" class="block text-sm font-medium text-gray-700">Estado</label>
                <select id="estado_id" wire:model.defer="estado_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Seleccionar Estado</option>
                    @foreach($estados as $estado)
                        <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                    @endforeach
                </select>
                @error('estado_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mb-2">
                <label for="tipo_envios" class="block text-sm font-medium text-gray-700">Tipos de Envío</label>
                <select id="tipo_envios" wire:model.defer="selectedTiposEnvio" multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @foreach($tiposEnvio as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                    @endforeach
                </select>
                @error('selectedTiposEnvio') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex space-x-4">
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                    {{ $isEditMode ? 'Actualizar' : 'Guardar' }}
                </button>
                <button type="button" x-show="isEditMode" @click="$wire.resetFields()" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                    Cancelar
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full border-collapse border border-gray-200 rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">ID</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Nombre</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Estado</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Tipos de Envío</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ciudades as $ciudad)
                    <tr class="hover:bg-gray-50">
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $ciudad->id }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $ciudad->nombre }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $ciudad->estado->nombre }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">
                            <ul class="list-disc list-inside">
                                @foreach($ciudad->tipoEnvios as $tipoEnvio)
                                    <li>{{ $tipoEnvio->nombre }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">
                            <button wire:click="edit({{ $ciudad->id }})" class="text-blue-500 hover:underline">Editar</button>
                            <button wire:click="delete({{ $ciudad->id }})" class="text-red-500 hover:underline ml-4">Eliminar</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $ciudades->links() }}
    </div>
</div>

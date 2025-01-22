<div x-data="{ isEditMode: @entangle('isEditMode') }" class="container mx-auto p-6">
    <div class="mb-4">
        <h1 class="text-2xl font-semibold">Administrar Tipos de Envío</h1>
    </div>

    <div class="mb-4">
        <form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}">
            <div class="mb-2">
                <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre del Tipo de Envío</label>
                <input type="text" id="nombre" wire:model.defer="nombre" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('nombre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mb-2">
                <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
                <textarea id="descripcion" wire:model.defer="descripcion" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                @error('descripcion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mb-2">
                <label for="dias_envio" class="block text-sm font-medium text-gray-700">Días de Envío</label>
                <input type="number" id="dias_envio" wire:model.defer="dias_envio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('dias_envio') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Descripción</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Días de Envío</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tiposEnvio as $tipo)
                    <tr class="hover:bg-gray-50">
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $tipo->id }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $tipo->nombre }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $tipo->descripcion }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $tipo->dias_envio }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">
                            <button wire:click="edit({{ $tipo->id }})" class="text-blue-500 hover:underline">Editar</button>
                            <button wire:click="delete({{ $tipo->id }})" class="text-red-500 hover:underline ml-4">Eliminar</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $tiposEnvio->links() }}
    </div>
</div>

<div x-data="{ selectedFlujos: @entangle('selectedFlujos'), modalOpen: @entangle('modalOpen') }" class="container mx-auto p-6">
    <!-- Botones de acción -->
    <div class="mb-4 flex flex-wrap space-y-2 sm:space-y-0 sm:space-x-4">
        <button
            class="w-full sm:w-auto px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600"
            wire:click="abrirModal"
        >
            Nuevo Flujo
        </button>
        <button
            class="w-full sm:w-auto px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="selectedFlujos.length === 0"
            wire:click="deleteSelected"
        >
            Eliminar Seleccionados
        </button>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full border-collapse border border-gray-200 rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">
                        <input
                            type="checkbox"
                            wire:model="selectAll"
                            @change="selectedFlujos = $event.target.checked ? @js($flujos->pluck('id')) : []"
                        />
                    </th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">ID</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Nombre</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Descripción</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($flujos as $flujo)
                    <tr class="hover:bg-gray-50">
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">
                            <input
                                type="checkbox"
                                wire:model="selectedFlujos"
                                value="{{ $flujo->id }}"
                            />
                        </td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $flujo->id }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $flujo->nombre }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $flujo->descripcion ?? '—' }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">
                            <button
                                class="text-blue-500 hover:underline"
                                wire:click="abrirModal({{ $flujo->id }})"
                            >
                                Editar
                            </button>
                            <button
                                class="ml-2 text-red-500 hover:underline"
                                wire:click="delete({{ $flujo->id }})"
                            >
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div x-show="modalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6">
            <h2 class="text-xl font-bold mb-4">
                {{ $editMode ? 'Editar Flujo' : 'Nuevo Flujo' }}
            </h2>
            <form wire:submit.prevent="guardar">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input type="text" wire:model.defer="nombre" class="w-full mt-1 border rounded-lg p-2" />
                    @error('nombre') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Descripción</label>
                    <textarea wire:model.defer="descripcion" class="w-full mt-1 border rounded-lg p-2"></textarea>
                    @error('descripcion') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Config (JSON)</label>
                    <textarea wire:model.defer="config" class="w-full mt-1 border rounded-lg p-2" rows="4"></textarea>
                    @error('config') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Aquí puedes manejar eventos de Livewire usando dispatch si lo necesitas
});
</script>
<div>
    <div x-data="{ open: false }">
        <!-- Botón para minimizar o expandir -->
        <button @click="open = !open" class="mb-4 px-4 py-2">
            <span x-show="open">Minimizar</span>
            <span x-show="!open">Crear permiso</span>
        </button>

        <!-- Contenido del formulario -->
        <div x-show="open" x-transition class="border p-4 rounded-md">
            <form wire:submit.prevent="save">
                <!-- Nombre del Permiso -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Permission Name</label>
                    <input type="text" id="name" wire:model="name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm">
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Guard Name -->
                <div class="mb-4">
                    <label for="guard_name" class="block text-sm font-medium text-gray-700">Guard Name</label>
                    <input type="text" id="guard_name" wire:model="guard_name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm">
                    @error('guard_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Botón -->
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">
                    {{ $permissionId ? 'Update Permission' : 'Create Permission' }}
                </button>
            </form>

            @if (session()->has('message'))
                <div class="mt-4 text-green-600">
                    {{ session('message') }}
                </div>
            @endif
        </div>
    </div>
</div>

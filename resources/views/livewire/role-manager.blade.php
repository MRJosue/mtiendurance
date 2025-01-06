<div>
    <div x-data="{ open: false }">
        <!-- Botón para minimizar o expandir -->
        <button @click="open = !open" class="mb-4 px-4 py-2 rounded-md">
            <span x-show="open">Minimizar</span>
            <span x-show="!open">Crear rol</span>
        </button>

        <!-- Contenido del formulario -->
        <div x-show="open" x-transition class="border p-4 rounded-md">
            <form wire:submit.prevent="save">
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Role Name</label>
                    <input type="text" id="name" wire:model="name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm">
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label for="guard_name" class="block text-sm font-medium text-gray-700">Guard Name</label>
                    <input type="text" id="guard_name" wire:model="guard_name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm">
                    @error('guard_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">
                    {{ $roleId ? 'Update Role' : 'Create Role' }}
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


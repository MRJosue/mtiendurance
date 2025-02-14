<div class="container mx-auto p-6">
    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Gestión de Roles y Permisos</h2>

        @if (session()->has('message'))
            <div class="bg-green-100 text-green-700 p-2 rounded-md mb-4">
                {{ session('message') }}
            </div>
        @endif

        <!-- Crear un nuevo rol -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Crear Nuevo Rol</h3>
            <div class="flex space-x-2">
                <input type="text" wire:model="newRole" placeholder="Nombre del rol"
                    class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-200">
                <button wire:click="createRole" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                    Crear
                </button>
            </div>
            @error('newRole') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Crear un nuevo permiso -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Crear Nuevo Permiso</h3>
            <div class="flex space-x-2">
                <input type="text" wire:model="newPermission" placeholder="Nombre del permiso"
                    class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-200">
                <button wire:click="createPermission" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                    Crear
                </button>
            </div>
            @error('newPermission') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Asignar permisos a roles -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Asignar/Editar Permisos de un Rol</h3>
            <div class="space-y-2">
                <select wire:model="selectedRole" wire:change="loadPermissions" class="w-full px-3 py-2 border rounded-lg">
                    <option value="">Selecciona un rol</option>
                    @foreach ($roles as $id => $role)
                        <option value="{{ $id }}">{{ $role }}</option>
                    @endforeach
                </select>

                <div class="flex flex-wrap gap-2">
                    @foreach ($permissions as $id => $permission)
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission }}">
                            <span>{{ $permission }}</span>
                        </label>
                    @endforeach
                </div>

                <button wire:click="assignPermissionsToRole"
                    class="mt-2 px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600">
                    Guardar Permisos
                </button>
            </div>
        </div>

    </div>
</div>

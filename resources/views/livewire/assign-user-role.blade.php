<div class="container mx-auto p-6">
    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Asignar Rol a Usuario</h2>

        @if (session()->has('message'))
            <div class="bg-green-100 text-green-700 p-2 rounded-md mb-4">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 text-red-700 p-2 rounded-md mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Seleccionar usuario -->
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Seleccionar Usuario</h3>
            <select wire:model="selectedUser" class="w-full px-3 py-2 border rounded-lg">
                <option value="">Selecciona un usuario</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
        </div>

        <!-- Seleccionar rol -->
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Seleccionar Rol</h3>
            <select wire:model="selectedRole" class="w-full px-3 py-2 border rounded-lg">
                <option value="">Selecciona un rol</option>
                @foreach ($roles as $id => $role)
                    <option value="{{ $id }}">{{ $role }}</option>
                @endforeach
            </select>
        </div>



        
        <!-- Botón para asignar rol -->
        <button wire:click="assignRole" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
            Asignar Rol
        </button>
    </div>
</div>

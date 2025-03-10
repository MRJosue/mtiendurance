<div >
    <h2 class="text-xl font-bold mb-4">Crear Nuevo Usuario</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="createUser" class="space-y-4">
        <div>
            <label class="block text-gray-700">Nombre</label>
            <input type="text" wire:model="name" class="w-full p-2 border rounded">
            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-gray-700">Correo Electrónico</label>
            <input type="email" wire:model="email" class="w-full p-2 border rounded">
            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-gray-700">Contraseña</label>
            <input type="password" wire:model="password" class="w-full p-2 border rounded">
            @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-gray-700">Confirmar Contraseña</label>
            <input type="password" wire:model="password_confirmation" class="w-full p-2 border rounded">
        </div>

        <div>
            <label class="block text-gray-700">Rol</label>
            <select wire:model="role" class="w-full p-2 border rounded">
                <option value="">Seleccione un rol</option>
                @foreach($rolesDisponibles as $rol)
                    <option value="{{ $rol }}">{{ ucfirst($rol) }}</option>
                @endforeach
            </select>
            @error('role') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <button type="submit"
            class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
            Crear Usuario
        </button>
    </form>
</div>

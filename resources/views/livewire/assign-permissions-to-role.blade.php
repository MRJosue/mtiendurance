<div>
    <h2 class="text-xl font-bold mb-4">Asignar Permisos</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-700 p-2 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-4">
        @foreach ($permissions as $permission)
            <label class="block mb-2">
                <input
                    type="checkbox"
                    value="{{ $permission->id }}"
                    wire:model.defer="selectedPermissions"
                >
                {{ $permission->name }}
            </label>
        @endforeach
    </div>

    <button
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        wire:click="savePermissions"
    >
        Guardar Cambios
    </button>
</div>

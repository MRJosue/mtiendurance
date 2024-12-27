<div>
    <h2 class="text-xl font-bold mb-4">Asignar Roles</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-700 p-2 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <div>
        @foreach ($roles as $role)
            <label class="block mb-2">
                <input
                    type="checkbox"
                    value="{{ $role->id }}"
                    wire:model.defer="selectedRoles"
                >
                {{ $role->name }}
            </label>
        @endforeach
    </div>

    <button
        class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        wire:click="saveRoles"
    >
        Guardar Cambios
    </button>
</div>

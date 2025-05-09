<div
    x-data="{ abierto: false }"
    class="fixed bottom-9 left-4 z-50 w-80 max-w-full bg-white border border-gray-300 rounded-xl shadow-lg overflow-hidden"
>

    <!-- Cabecera minimizable -->
    <div class="bg-blue-600 text-white px-4 py-2 cursor-pointer flex justify-between items-center" @click="abierto = !abierto">
        <span class="font-semibold">Cambiar Rol</span>
        <svg :class="{ 'rotate-180': abierto }" class="h-4 w-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </div>

    <!-- Contenido -->
    <div x-show="abierto" x-transition class="p-4">
        @if (session()->has('message'))
            <div class="bg-green-100 text-green-800 p-2 rounded mb-4 text-sm">
                {{ session('message') }}
            </div>
        @endif

        <div class="mb-3">
        @livewire('user-roles-permissions')
         </div>
        <div class="mb-3">
            <label for="rolActual" class="block text-sm font-medium text-gray-700">Rol actual</label>
            <select wire:model="rolActual" id="rolActual" class="mt-1 w-full rounded border-gray-300">
                <option value="">-- Selecciona un rol --</option>
                @foreach ($rolesDisponibles as $rol)
                    <option value="{{ $rol }}">{{ $rol }}</option>
                @endforeach
            </select>
        </div>

        <button
            wire:click="actualizarRol"
            class="w-full px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700"
        >
            Cambiar Rol
        </button>
    </div>
</div>

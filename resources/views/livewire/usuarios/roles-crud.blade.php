<div class="max-w-4xl mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Gestión de Roles</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-3">
            {{ session('message') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-3">
        <button wire:click="crear" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
            Nuevo Rol
        </button>
        <div class="flex space-x-2">
            <input type="text" wire:model="query" placeholder="Buscar por nombre..." class="border border-gray-300 rounded px-4 py-2">
            <button wire:click="buscar" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded">
                Buscar
            </button>
        </div>
    </div>

    <table class="w-full border-collapse border border-gray-300">
        <thead>
            <tr class="bg-gray-100">
                <th class="border border-gray-300 p-2 text-left">Nombre</th>
                <th class="border border-gray-300 p-2 text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rolesList as $rol)
                <tr>
                    <td class="border border-gray-300 p-2">{{ $rol->name }}</td>
                    <td class="border border-gray-300 p-2 flex space-x-2 justify-center">
                        <button wire:click="editar('{{ $rol->id }}')" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-3 py-1 rounded">
                            Editar
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $rolesList->links() }}
    </div>

    @if($modal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded shadow-lg w-full max-w-md">
                <div class="flex items-center justify-between border-b border-gray-200 p-4">
                    <h5 class="text-xl font-bold">Editar Rol</h5>
                    <button class="text-gray-500 hover:text-gray-700" wire:click="cerrarModal">&times;</button>
                </div>
                <div class="overflow-y-auto p-4 space-y-4 flex-1">
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-1">Nombre del Rol</label>
                        <input type="text" class="w-full border border-gray-300 rounded p-2" wire:model="nombre">
                        @error('nombre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div x-data="{ mostrar: true }" class="mb-4 border border-gray-200 rounded">
                        <button
                            type="button"
                            class="w-full text-left px-4 py-2 bg-gray-100 hover:bg-gray-200 font-semibold text-gray-700 rounded-t flex justify-between items-center"
                            @click="mostrar = !mostrar"
                        >
                            <span>Permisos Asociados</span>
                            <svg :class="{'transform rotate-180': mostrar}" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="mostrar" x-transition class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-60 overflow-y-auto">
                            @foreach($permisos as $permiso)
                                <label class="flex items-center space-x-2">
                                    <input
                                        type="checkbox"
                                        wire:model="permisosSeleccionados"
                                        value="{{ $permiso->id }}"
                                        class="form-checkbox text-blue-600"
                                    >
                                    <span class="text-gray-700">{{ $permiso->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end border-t border-gray-200 p-4 space-x-2">
                    <button wire:click="cerrarModal" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-4 py-2 rounded">
                        Cancelar
                    </button>
                    <button wire:click="guardar" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

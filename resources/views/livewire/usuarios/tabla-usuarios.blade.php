<div class="max-w-4xl mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4 text-center md:text-left">Usuarios</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-3">
            {{ session('message') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 gap-2">
        <button wire:click="crear" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
            Nuevo Usuario
        </button>

        <div class="flex flex-wrap gap-2">
            <input type="text" wire:model.lazy="search" placeholder="Buscar por nombre o correo..." class="border border-gray-300 rounded px-4 py-2 w-full sm:w-64">
            <button wire:click="$refresh" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded">
                Buscar
            </button>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full border-collapse border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border border-gray-300 px-4 py-2 text-left font-semibold">Nombre</th>
                    <th class="border border-gray-300 px-4 py-2 text-left font-semibold">Correo Electrónico</th>
                  
                    <th class="border border-gray-300 px-4 py-2 text-left font-semibold">Roles</th>
                    <th class="border border-gray-300 px-4 py-2 text-center font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usuario)
                    <tr class="hover:bg-gray-50">
                        <td class="border border-gray-300 px-4 py-2">{{ $usuario->name }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $usuario->email }}</td>

                        <td class="border border-gray-300 px-4 py-2">
                            @foreach($usuario->roles as $rol)
                                <span class="inline-block bg-gray-200 text-gray-800 text-xs font-semibold mr-1 mb-1 px-2 py-1 rounded">
                                    {{ $rol->name }}
                                </span>
                            @endforeach
                        </td>
                        <td class="border border-gray-300 px-4 py-2 text-center space-x-2">
                            <a href="{{ route('usuarios.show', $usuario->id) }}" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded inline-block">Detalles</a>
                            <button wire:click="editarRoles({{ $usuario->id }})" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-4 py-2 rounded inline-block">Roles</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-gray-500 py-4">No se encontraron usuarios.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $usuarios->links() }}
    </div>

    @if($modal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded shadow-lg w-full max-w-md">
                <div class="flex items-center justify-between border-b border-gray-200 p-4">
                    <h5 class="text-xl font-bold">Asignar Roles al Usuario</h5>
                    <button class="text-gray-500 hover:text-gray-700" wire:click="cerrarModal">&times;</button>
                </div>
                <div class="overflow-y-auto p-4 space-y-4 flex-1">
                    <div class="border border-gray-200 rounded">
                        <div class="px-4 py-2 bg-gray-100 font-semibold text-gray-700 rounded-t">
                            Roles Disponibles
                        </div>
                        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-60 overflow-y-auto">
                            @foreach($roles as $rol)
                                <label class="flex items-center space-x-2">
                                    <input
                                        type="checkbox"
                                        wire:model="rolesSeleccionados"
                                        value="{{ $rol->id }}"
                                        class="form-checkbox text-blue-600"
                                    >
                                    <span class="text-gray-700">{{ $rol->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end border-t border-gray-200 p-4 space-x-2">
                    <button wire:click="cerrarModal" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-4 py-2 rounded">
                        Cancelar
                    </button>
                    <button wire:click="guardarRoles" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

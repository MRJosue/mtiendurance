<div class="max-w-4xl mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4 text-center md:text-left">Usuarios</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-3">
            {{ session('message') }}
        </div>
    @endif
    <div class="flex items-center justify-between mb-3 space-x-2">
        <button wire:click="crear" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
            Nuevo Usuario
        </button>

    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 px-4 py-2 text-left font-semibold">Nombre</th>
                    <th class="border border-gray-300 px-4 py-2 text-left font-semibold">Correo Electrónico</th>
                    <th class="border border-gray-300 px-4 py-2 text-left font-semibold">Tipo de Usuario</th>
                    <th class="border border-gray-300 px-4 py-2 text-center font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usuarios as $usuario)
                    <tr class="hover:bg-gray-50">
                        <td class="border border-gray-300 px-4 py-2">{{ $usuario->name }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $usuario->email }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $usuario->tipo_usuario }}</td>
                        <td class="border border-gray-300 px-4 py-2 text-center">
                            <a href="{{ route('usuarios.show', $usuario->id) }}" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded inline-block">Detalles</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $usuarios->links() }}
    </div>
</div>
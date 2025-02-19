<div class="container mx-auto p-6">
    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Roles y Permisos del Usuario</h2>

        <!-- Mostrar Roles -->
        <h3 class="text-lg font-semibold text-gray-700">Roles:</h3>
        <ul class="list-disc list-inside mb-4">
            @forelse ($roles as $role)
                <li class="text-blue-600 font-semibold">{{ $role }}</li>
            @empty
                <li class="text-gray-500">No tiene roles asignados.</li>
            @endforelse
        </ul>

        <!-- Mostrar Permisos -->
        <h3 class="text-lg font-semibold text-gray-700">Permisos:</h3>
        <ul class="list-disc list-inside">
            @forelse ($permissions as $permission)
                <li class="text-green-600">{{ $permission }}</li>
            @empty
                <li class="text-gray-500">No tiene permisos asignados.</li>
            @endforelse
        </ul>
    </div>
</div>

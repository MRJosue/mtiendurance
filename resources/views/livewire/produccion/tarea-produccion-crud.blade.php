<div class="container mx-auto p-6">
    <h2 class="text-2xl font-bold mb-4">Tareas de Producción</h2>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full border-collapse border border-gray-200 rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">ID</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Pedido</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Responsable</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Tipo</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Estado</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Descripción</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tareas as $tarea)
                    <tr class="hover:bg-gray-50">
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $tarea->id }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">#{{ $tarea->pedido_id }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $tarea->staff->name ?? 'Sin usuario' }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $tarea->tipo }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $tarea->estado }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $tarea->descripcion }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">
                            <button wire:click="abrirModal({{ $tarea->id }})" class="text-blue-500 hover:underline">
                                Editar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-gray-500">No hay tareas registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $tareas->links() }}
    </div>
</div>

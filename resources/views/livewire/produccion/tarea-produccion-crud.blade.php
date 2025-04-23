<div class="container mx-auto p-6">
    <h2 class="text-2xl font-bold mb-4">Tareas de Producción</h2>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full border-collapse border border-gray-200 rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">ID</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Pedido/s</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Responsable</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Tipo</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Estado</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Descripción</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Cantidades</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tareas as $tarea)
                    <tr class="hover:bg-gray-50">
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $tarea->id }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">
                            @foreach ($tarea->pedidos as $pedido)
                                <div>
                                   {{ $pedido->proyecto_id }} - {{ $pedido->id }}
                                </div>
                            @endforeach
                        </td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $tarea->usuario->name ?? 'Sin usuario' }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $tarea->tipo }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $tarea->estado }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $tarea->descripcion }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">
                            {{-- Accesor de pedidos --}}
                            @php
                                $tallasAgrupadas = \App\Models\Pedido::combinarTallasDePedidos($tarea->pedidos);
                            @endphp
                        
                            @if($tallasAgrupadas->isNotEmpty())
                                <ul class="list-none">
                                    @foreach($tallasAgrupadas as $grupo)
                                        <li class="font-semibold text-gray-700 border-b pb-1 mt-2">
                                            {{ $grupo['grupo_nombre'] }}
                                        </li>
                                        <ul class="list-disc list-inside text-gray-600">
                                            @foreach($grupo['tallas'] as $talla)
                                                <li>{{ $talla['nombre'] }}: {{ $talla['cantidad'] }}</li>
                                            @endforeach
                                        </ul>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-gray-500">Sin tallas registradas</span>
                            @endif
                        </td>
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

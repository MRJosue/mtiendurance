<div class="overflow-x-auto">
    <table class="min-w-full bg-white border border-gray-300 text-sm text-left">
        <thead class="bg-gray-100">
            <tr>
                <th class="border px-4 py-2">Nombre del Usuario</th>
                <th class="border px-4 py-2">Archivo</th>
                <th class="border px-4 py-2">ID del Archivo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedidos as $pedido)
                <tr class="hover:bg-gray-50">
                    <td class="border px-4 py-2">{{ $pedido->usuario->name ?? 'Sin usuario' }}</td>
                    <td class="border px-4 py-2">
                        @if($pedido->archivo)
                            <a href="{{ Storage::url($pedido->archivo->ruta_archivo) }}" 
                               class="text-blue-600 underline"
                               target="_blank">
                                {{ $pedido->archivo->nombre_archivo }}
                            </a>
                        @else
                            <span class="text-gray-500">Sin archivo</span>
                        @endif
                    </td>
                    <td class="border px-4 py-2">{{ $pedido->archivo->id ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
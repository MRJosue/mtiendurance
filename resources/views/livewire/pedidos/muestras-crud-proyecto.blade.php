<div class="container mx-auto p-6">

    <h2 class="text-2xl font-bold mb-4">Gestión de Muestras</h2>

    <table class="min-w-full table-auto divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-100 text-gray-700">
            <tr>


                <th class="border px-4 py-2">ID</th>
                <th class="border px-4 py-2">Producto</th>
                <th class="border px-4 py-2">Cliente</th>
                <th class="border px-4 py-2">Archivo y version</th>
                <th class="border px-4 py-2">Piezas solicitadas</th>
                <th class="border px-4 py-2">Quien solicita la Muestra</th>
                <th class="border px-4 py-2">Motivos e instrucciones</th>
                <th class="border px-4 py-2">Estatus</th>
                <th class="border px-4 py-2">Acciones</th>



            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($pedidos as $pedido)
                <tr class="hover:bg-gray-50">
                    <td class="p-2 px-4 py-2 font-semibold min-w-[4rem]" title="Proyecto {{ $pedido->proyecto_id }} - Muestra #{{ $pedido->id }}: {{ $pedido->descripcion_corta }}">
                        {{ $pedido->proyecto_id }}-{{ $pedido->id }}
                    </td>
                                            <td class="p-2">
                            <div class="font-medium">{{ $pedido->producto->nombre ?? 'Sin producto' }}</div>
                            <div class="text-xs text-gray-500">{{ $pedido->producto->categoria->nombre ?? 'Sin categoría' }}</div>
                        </td>
                    <td class="p-2 px-4 py-2 font-semibold min-w-[4rem]"> Cliente </td>
                    <td class="p-2 px-4 py-2 font-semibold min-w-[4rem]">
                        @if($pedido->archivo)
                            <a href="{{ Storage::url($pedido->archivo->ruta_archivo) }}" 
                               class="text-blue-600 underline"
                               target="_blank">
                                {{ $pedido->archivo->nombre_archivo }}

                               
                            </a>
                            <p>version: {{  $pedido->archivo->version }}</p>
                            
                        @else
                            <span class="text-gray-500">Sin archivo</span>
                        @endif
                    </td>

                    <td class="p-2 px-4 py-2 font-semibold min-w-[4rem]">{{ $pedido->total ?? 'N/A' }}</td>
                    <td class="p-2 px-4 py-2 font-semibold min-w-[4rem]">{{ $pedido->usuario->name ?? 'Sin usuario' }}</td>

                    
                    <td class="p-2 px-4 py-2 font-semibold min-w-[4rem]">{{ $pedido->instrucciones_muestra ?? 'N/A' }}</td>
                 
                    <td class="p-2 px-4 py-2 font-semibold min-w-[8rem]">
                        @php
                            $estatusMuestra = strtoupper($pedido->estatus_muestra ?? '');
                            $clase = collect([
                                'PENDIENTE'      => 'bg-yellow-400 text-black',
                                'SOLICITADA'     => 'bg-blue-500 text-white',
                                'MUESTRA LISTA'  => 'bg-emerald-600 text-white',
                                'ENTREGADA'      => 'bg-green-600 text-white',
                                'CANCELADA'      => 'bg-gray-500 text-white',
                            ])->get($estatusMuestra, 'bg-gray-300 text-gray-800');
                        @endphp

                        <span class="px-2 py-1 rounded text-xs font-semibold {{ $clase }}">
                            {{ $estatusMuestra ?: 'N/A' }}
                        </span>
                    </td>
                  
                    <td class="p-2 px-4 py-2 font-semibold min-w-[4rem]">Acciones</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
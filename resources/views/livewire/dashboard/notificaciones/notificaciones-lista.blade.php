<div 
    x-data="{
        abierto: JSON.parse(localStorage.getItem('notificaciones_abierto') ?? 'true'),
        toggle() {
            this.abierto = !this.abierto;
            localStorage.setItem('notificaciones_abierto', JSON.stringify(this.abierto));
        }
    }"
    class="container mx-auto p-6"
>
    <h2 
        @click="toggle()" 
        class="text-2xl font-bold mb-4 border-b border-gray-300 pb-2 cursor-pointer hover:text-blue-600 transition"
    >
        Notificaciones del Sistema
        <span class="text-sm text-gray-500 ml-2" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
    </h2>

    <div x-show="abierto" x-transition>
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="w-full border-collapse border border-gray-300 rounded-lg">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-2 text-left">ID</th>
                        <th class="border p-2 text-left">Tipo</th>
                        <th class="border p-2 text-left">Contenido</th>
                        <th class="border p-2 text-left">Estado</th>
                        <th class="border p-2 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($notificaciones as $n)
                        <tr class="hover:bg-gray-50">
                            <td class="border p-2">{{ $n->id }}</td>
                            <td class="border p-2">{{ class_basename($n->type) }}</td>
                            <td class="border p-2">
                                @php
                                    $contenido = is_array($n->data) ? $n->data : json_decode($n->data, true);
                                @endphp

                                <div class="text-sm">
                                    <p class="mb-1"><span class="font-semibold">Mensaje:</span> {{ $contenido['mensaje'] ?? 'Sin mensaje' }}</p>
                                    @if(isset($contenido['liga']))
                                        <p><span class="font-semibold">Liga:</span> <a href="{{ $contenido['liga'] }}" class="text-blue-600 hover:underline" target="_blank">{{ $contenido['liga'] }}</a></p>
                                    @endif
                                </div>
                            </td>
                            <td class="border p-2">
                                @if ($n->read_at)
                                    <span class="text-green-600 font-semibold">Leída</span>
                                @else
                                    <span class="text-red-600 font-semibold">No leída</span>
                                @endif
                            </td>
                            <td class="border p-2 text-center">
                                @if (!$n->read_at)
                                    <button
                                        wire:click="marcarComoLeida('{{ $n->id }}')"
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded"
                                    >
                                        Marcar como leída
                                    </button>
                                @else
                                    <span class="text-gray-500 text-sm">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="border p-4 text-center text-gray-500">No hay notificaciones.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $notificaciones->links() }}
        </div>
    </div>
</div>

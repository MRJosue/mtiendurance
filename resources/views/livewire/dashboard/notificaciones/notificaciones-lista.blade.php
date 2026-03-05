<div 
    x-data="{
        abierto: JSON.parse(localStorage.getItem('notificaciones_abierto') ?? 'true'),
        toggle() {
            this.abierto = !this.abierto;
            localStorage.setItem('notificaciones_abierto', JSON.stringify(this.abierto));
        }
    }"
    class="p-2 sm:p-3 h-full min-h-0 flex flex-col"
>
    <h2 
        @click="toggle()" 
        class="text-xl font-bold mb-4 border-b border-gray-300 pb-2 cursor-pointer hover:text-blue-600 transition"
    >
        Notificaciones del Sistema
        <span class="text-sm text-gray-500 ml-2" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
    </h2>

    <div x-show="abierto" x-transition>
        <div class="overflow-x-auto bg-white rounded-lg shadow min-h-64 pb-2">
            <table class="w-full border-collapse border border-gray-300 rounded-lg">
                <thead class="bg-gray-100">
                    <tr>
                        {{-- ✅ Quitamos ID --}}
                        <th class="border p-2 text-left">Tipo</th>
                        <th class="border p-2 text-left">Contenido</th>
                        <th class="border p-2 text-left">Estado</th>
                        <th class="border p-2 text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($notificaciones as $n)
                        @php
                            $tipo = class_basename($n->type);
                            $contenido = is_array($n->data) ? $n->data : json_decode($n->data, true);

                            $estado = $n->read_at ? 'LEÍDA' : 'NO LEÍDA';
                            $clases = [
                                'LEÍDA'    => 'bg-emerald-100 text-emerald-800 ring-emerald-600/20',
                                'NO LEÍDA' => 'bg-rose-100 text-rose-800 ring-rose-600/20',
                            ];
                            $badge = $clases[$estado] ?? 'bg-gray-100 text-gray-800 ring-gray-600/20';
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="border p-2 text-sm text-gray-700">
                                {{ $tipo }}
                            </td>

                            <td class="border p-2">
                                <div class="text-sm text-gray-700">
                                    <p class="mb-1">
                                        <span class="font-semibold">Mensaje:</span>
                                        {{ $contenido['mensaje'] ?? 'Sin mensaje' }}
                                    </p>

                                    @if(!empty($contenido['liga']))
                                        <p class="break-all">
                                            <span class="font-semibold">Liga:</span>
                                            <a
                                                href="{{ $contenido['liga'] }}"
                                                class="text-blue-600 hover:underline"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                {{ $contenido['liga'] }}
                                            </a>
                                        </p>
                                    @endif
                                </div>
                            </td>

                            <td class="border p-2">
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-semibold ring-1 ring-inset {{ $badge }}">
                                    <span class="h-1.5 w-1.5 rounded-full 
                                        @if($estado==='LEÍDA') bg-emerald-500
                                        @elseif($estado==='NO LEÍDA') bg-rose-500
                                        @else bg-gray-500 @endif">
                                    </span>
                                    {{ $estado }}
                                </span>
                            </td>

                            <td class="border p-2 text-center">
                                @if (!$n->read_at)
                                    <button
                                        wire:click="marcarComoLeida('{{ $n->id }}')"
                                        class="w-full sm:w-auto bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded disabled:opacity-50 disabled:cursor-not-allowed"
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
                            <td colspan="4" class="border p-4 text-center text-gray-500">
                                No hay notificaciones.
                            </td>
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
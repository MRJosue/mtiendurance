<div class="relative" x-data="{ open: false }">
    <!-- Botón de Notificaciones -->
    <button @click="open = !open" class="relative flex items-center px-3 py-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition">
        🔔
        @if(count($notificaciones->whereNull('read_at')) > 0)
            <span class="absolute top-0 right-0 bg-red-600 text-white text-xs px-2 py-1 rounded-full">
                {{ count($notificaciones->whereNull('read_at')) }}
            </span>
        @endif
    </button>

    <!-- Dropdown de Notificaciones -->
    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-64 bg-white shadow-lg rounded-lg p-4 z-50">
        <h3 class="text-lg font-bold text-gray-700">Notificaciones</h3>

        @if($notificaciones->isEmpty())
            <p class="text-gray-500">No tienes notificaciones.</p>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach($notificaciones as $notificacion)
                    <li class="p-2 flex justify-between items-center {{ $notificacion->read_at ? 'text-gray-500' : 'text-black font-bold' }}">
                        <span>{{ $notificacion->data['mensaje'] }}</span>
                        @if(!empty($notificacion->data['liga'])) 
                        <a href="{{ url($notificacion->data['liga']) }}" class="text-blue-500 underline" target="_blank">
                            Ver más
                        </a>
                          @endif

                        @if(!$notificacion->read_at)
                            <button wire:click="marcarComoLeida('{{ $notificacion->id }}')" class="text-blue-500 text-sm">
                                ✓
                            </button>
                        @endif
                    </li>
                @endforeach
            </ul>
            <button wire:click="marcarTodasComoLeidas" class="mt-2 w-full bg-gray-300 text-black py-1 rounded-md text-center">
                Marcar todas como leídas
            </button>
        @endif
    </div>
</div>

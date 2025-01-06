<div class="w-full py-4">
    <!-- Contenedor principal -->
    <div class="flex flex-col sm:flex-row items-center sm:items-start justify-between sm:space-x-4 space-y-4 sm:space-y-0">
        @foreach ($estados as $estado)
            <div class="flex flex-col items-center sm:items-start">
                <!-- Icono o Indicador -->
                <div
                    class="w-8 h-8 flex items-center justify-center rounded-full
                    {{ $estado == $estadoActual ? 'bg-blue-500 text-white' : ($loop->index < array_search($estadoActual, $estados) ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-700') }}">
                    {{ $loop->iteration }}
                </div>
                <!-- Nombre del Estado -->
                <span class="mt-2 text-sm text-center sm:text-left {{ $estado == $estadoActual ? 'text-blue-500 font-bold' : 'text-gray-600' }}">
                    {{ $estado }}
                </span>
            </div>
            @if (!$loop->last)
                <!-- Línea entre los puntos -->
                <div class="flex-1 h-1 hidden sm:block
                    {{ $loop->index < array_search($estadoActual, $estados) ? 'bg-green-500' : 'bg-gray-300' }}">
                </div>
                <div class="h-8 w-1 sm:hidden
                    {{ $loop->index < array_search($estadoActual, $estados) ? 'bg-green-500' : 'bg-gray-300' }}">
                </div>
            @endif
        @endforeach
    </div>
</div>

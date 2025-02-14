<div class="w-full py-6">
    <div class="flex flex-col sm:flex-row items-center sm:items-center justify-between sm:space-x-6 space-y-6 sm:space-y-0">
        @foreach ($estados as $estado)
            <div class="flex flex-col items-center sm:items-center relative">
                <!-- Icono o Indicador -->
                <div class="w-10 h-10 flex items-center justify-center rounded-full shadow-lg transition-all duration-300 
                    {{ $estado == $estadoActual ? 'bg-blue-600 text-white scale-110 font-bold' : ($loop->index < array_search($estadoActual, $estados) ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-700') }}">
                    {{ $loop->iteration }}
                </div>
                <!-- Nombre del Estado -->
                <span class="mt-3 text-sm text-center sm:text-left transition-all duration-300
                    {{ $estado == $estadoActual ? 'text-blue-600 font-semibold' : 'text-gray-600' }}">
                    {{ $estado }}
                </span>
            </div>
            @if (!$loop->last)
                <!-- Línea de progreso -->
                <div class="flex-1 h-1 hidden sm:block rounded-full transition-all duration-300
                    {{ $loop->index < array_search($estadoActual, $estados) ? 'bg-green-500' : 'bg-gray-300' }}">
                </div>
                <div class="h-8 w-1 sm:hidden rounded-full transition-all duration-300
                    {{ $loop->index < array_search($estadoActual, $estados) ? 'bg-green-500' : 'bg-gray-300' }}">
                </div>
            @endif
        @endforeach
    </div>
</div>
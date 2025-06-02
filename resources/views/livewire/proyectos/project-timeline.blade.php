<div class="w-full py-6">
    <div class="flex flex-row items-center justify-between gap-x-12">
        @foreach ($estados as $estado)
            <div class="flex flex-col items-center relative min-w-[100px] p-2">
                <!-- Icono o Indicador -->
                <div class="w-10 h-10 flex items-center justify-center rounded-full shadow-lg transition-all duration-300 
                    {{ $estado == $estadoActual ? 'bg-blue-600 text-white scale-110 font-bold' : ($loop->index < array_search($estadoActual, $estados) ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-700') }}">
                    {{ $loop->iteration }}
                </div>
                <!-- Nombre del Estado -->
                <span class="mt-4 text-sm text-center transition-all duration-300
                    {{ $estado == $estadoActual ? 'text-blue-600 font-semibold' : 'text-gray-600' }}">
                    {{ $estado }}
                </span>
            </div>

            @if (!$loop->last)
                <!-- Línea de progreso -->
                <div class="flex-1 h-1 rounded-full transition-all duration-300
                    {{ $loop->index < array_search($estadoActual, $estados) ? 'bg-green-500' : 'bg-gray-300' }}">
                </div>
            @endif
        @endforeach
    </div>
</div>

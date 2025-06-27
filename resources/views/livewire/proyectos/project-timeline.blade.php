<div class="w-full py-6 overflow-x-auto">
    <div class="flex flex-col ">
        @foreach ($estados as $estado)
            <div class="flex flex-col items-center relative min-w-[80px] md:min-w-[100px] p-2">
                <!-- Icono o Indicador md:flex-row items-center justify-between md:gap-x-12 gap-y-4 md:gap-y-0 -->
                <div class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center rounded-full shadow-lg transition-all duration-300 
                    {{ $estado == $estadoActual ? 'bg-blue-600 text-white scale-110 md:scale-100 font-bold' : ($loop->index < array_search($estadoActual, $estados) ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-700') }}">
                    {{ $loop->iteration }}
                </div>
                <!-- Nombre del Estado -->
                <span class="mt-2 md:mt-4 text-xs md:text-sm text-center transition-all duration-300
                    {{ $estado == $estadoActual ? 'text-blue-600 font-semibold' : 'text-gray-600' }}">
                    {{ $estado }}
                </span>
            </div>

            @if (!$loop->last)
                <!-- Línea de progreso -->
                <div class="flex-1 h-0.5 md:h-1 rounded-full mx-2 md:mx-0 transition-all duration-300
                    {{$loop->index < array_search($estadoActual, $estados) ? 'bg-green-500' : 'bg-gray-300' }}"></div>
            @endif
        @endforeach
    </div>
</div>

<div class="w-full py-6 overflow-x-auto">
    @if (session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-100 text-green-800 p-3">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 rounded-lg bg-red-100 text-red-800 p-3">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex flex-col md:flex-row items-center justify-between md:gap-x-12 gap-y-4 md:gap-y-0 min-w-max">
        @foreach ($estados as $estado)
            @php
                $indiceActual = array_search($estadoActual, $estados);
                $indiceEstado = $loop->index;

                $esActual = $estado == $estadoActual;
                $esCompletado = $indiceActual !== false && $indiceEstado < $indiceActual;
                $puedeClick = $this->esAdmin && !$esActual;
            @endphp

            <div class="flex flex-col items-center relative min-w-[80px] md:min-w-[100px] p-2">
                <button
                    type="button"
                    wire:click="seleccionarEstado('{{ $estado }}')"
                    @disabled(!$puedeClick)
                    class="flex flex-col items-center transition-all duration-300 {{ $puedeClick ? 'cursor-pointer hover:scale-105' : 'cursor-default' }}"
                >
                    <!-- Icono o Indicador -->
                    <div class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center rounded-full shadow-lg transition-all duration-300
                        {{ $esActual ? 'bg-blue-600 text-white scale-110 md:scale-100 font-bold' : ($esCompletado ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-700') }}">
                        {{ $loop->iteration }}
                    </div>

                    <!-- Nombre del Estado -->
                    <span class="mt-2 md:mt-4 text-xs md:text-sm text-center transition-all duration-300
                        {{ $esActual ? 'text-blue-600 font-semibold' : 'text-gray-600' }}">
                        {{ $estado }}
                    </span>
                </button>
            </div>

            @if (!$loop->last)
                <!-- Línea de progreso -->
                <div class="flex-1 h-0.5 md:h-1 rounded-full mx-2 md:mx-0 transition-all duration-300
                    {{ $loop->index < array_search($estadoActual, $estados) ? 'bg-green-500' : 'bg-gray-300' }}">
                </div>
            @endif
        @endforeach
    </div>

    @if ($mostrarModalConfirmacion)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h2 class="text-lg font-bold text-gray-800">
                    Confirmar cambio de estado
                </h2>

                <p class="mt-3 text-sm text-gray-600">
                    ¿Deseas cambiar el estado del proyecto de
                    <span class="font-semibold text-blue-600">{{ $estadoActual }}</span>
                    a
                    <span class="font-semibold text-green-600">{{ $estadoSeleccionado }}</span>?
                </p>

                <div class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-2">
                    <button
                        type="button"
                        wire:click="cancelarCambioEstado"
                        class="w-full sm:w-auto px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        wire:click="confirmarCambioEstado"
                        class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
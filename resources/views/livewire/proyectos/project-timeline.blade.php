<div class="project-card w-full overflow-x-auto py-4">
    @if (session()->has('message'))
        <div class="project-alert-success">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="project-alert-error">
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
                    <div class="flex h-8 w-8 items-center justify-center rounded-full shadow-lg transition-all duration-300 md:h-10 md:w-10
                        {{ $esActual ? 'bg-blue-600 text-white scale-110 md:scale-100 font-bold' : ($esCompletado ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-700 dark:bg-gray-700 dark:text-gray-200') }}">
                        {{ $loop->iteration }}
                    </div>

                    <!-- Nombre del Estado -->
                    <span class="mt-2 md:mt-4 text-xs md:text-sm text-center transition-all duration-300
                        {{ $esActual ? 'text-blue-600 dark:text-blue-400 font-semibold' : 'text-gray-600 dark:text-gray-300' }}">
                        {{ $estado }}
                    </span>
                </button>
            </div>

            @if (!$loop->last)
                <!-- Línea de progreso -->
                <div class="mx-2 h-0.5 flex-1 rounded-full transition-all duration-300 md:mx-0 md:h-1
                    {{ $loop->index < array_search($estadoActual, $estados) ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-700' }}">
                </div>
            @endif
        @endforeach
    </div>

    @if ($mostrarModalConfirmacion)
        <div class="dashboard-modal-backdrop">
            <div class="dashboard-modal-panel max-w-md">
                <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100">
                    Confirmar cambio de estado
                </h2>

                <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                    ¿Deseas cambiar el estado del proyecto de
                    <span class="font-semibold text-blue-600">{{ $estadoActual }}</span>
                    a
                    <span class="font-semibold text-green-600">{{ $estadoSeleccionado }}</span>?
                </p>

                <div class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-2">
                    <button
                        type="button"
                        wire:click="cancelarCambioEstado"
                        class="project-button-secondary w-full sm:w-auto"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        wire:click="confirmarCambioEstado"
                        class="project-button-primary w-full sm:w-auto"
                    >
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

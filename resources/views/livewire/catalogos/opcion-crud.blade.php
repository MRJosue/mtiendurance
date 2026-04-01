<div class="max-w-4xl mx-auto p-4 text-gray-900 dark:text-gray-100">
    <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-gray-100">Gestión de Opciones</h2>

    @if (session()->has('message'))
        <div class="mb-3 rounded border border-emerald-200 bg-emerald-50 p-3 text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-3 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <button wire:click="crear" class="rounded bg-blue-500 px-4 py-2 font-semibold text-white hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-500">
            Nueva Opción
        </button>
        <div class="flex flex-col gap-2 sm:flex-row">
            <input type="text" wire:model="query" placeholder="Buscar por nombre..." class="rounded border border-gray-300 bg-white px-4 py-2 text-gray-900 placeholder-gray-400 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
            <select wire:model="filtroActivo" class="rounded border border-gray-300 bg-white px-4 py-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                <option value="1">Activas</option>
                <option value="0">Inactivas</option>
            </select>
            <button wire:click="buscar" class="rounded bg-gray-500 px-4 py-2 font-semibold text-white hover:bg-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600">
                Buscar
            </button>
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-900/70">
                    <th class="border border-gray-300 p-2 text-left dark:border-gray-700">Nombre</th>
                    <th class="border border-gray-300 p-2 text-left dark:border-gray-700">Pasos</th>
                    <th class="border border-gray-300 p-2 text-left dark:border-gray-700">Minuto/Paso</th>
                    <th class="border border-gray-300 p-2 text-left dark:border-gray-700">Valor Unitario</th>
                    <th class="border border-gray-300 p-2 text-center dark:border-gray-700">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($opciones as $opc)
                    <tr class="dark:hover:bg-gray-700/40">
                        <td class="border border-gray-300 p-2 dark:border-gray-700">{{ $opc->nombre }}</td>
                        <td class="border border-gray-300 p-2 dark:border-gray-700">{{ $opc->pasos }}</td>
                        <td class="border border-gray-300 p-2 dark:border-gray-700">{{ $opc->minutoPaso }}</td>
                        <td class="border border-gray-300 p-2 dark:border-gray-700">${{ number_format($opc->valoru, 2) }}</td>

                        <td class="border border-gray-300 p-2 dark:border-gray-700">
                            <div class="flex justify-center space-x-2">
                                <button wire:click="editar('{{ $opc->id }}')" class="rounded bg-yellow-500 px-3 py-1 font-semibold text-white hover:bg-yellow-600 dark:bg-yellow-600 dark:hover:bg-yellow-500">
                                    Editar
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $opciones->links() }}
    </div>


    @if($mostrarConfirmacion)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-bold">Advertencia</h2>
                <p class="mb-6 text-gray-700 dark:text-gray-300">{{ $mensajeConfirmacion }}</p>
                <div class="flex justify-end space-x-2">
                    <button wire:click="$set('mostrarConfirmacion', false)" class="rounded bg-gray-300 px-4 py-2 text-gray-800 hover:bg-gray-400 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600">
                        Cancelar
                    </button>
                    <button wire:click="ejecutarAccionConfirmada" class="rounded bg-red-600 px-4 py-2 text-white hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
            <div class="w-full max-w-md rounded bg-white shadow-lg dark:bg-gray-800">
                <div class="flex items-center justify-between border-b border-gray-200 p-4 dark:border-gray-700">
                    <h5 class="text-xl font-bold">{{ $opcion_id ? 'Editar Opción' : 'Crear Nueva Opción' }}</h5>
                    <button class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" wire:click="cerrarModal">&times;</button>
                </div>
                <div class="p-4">
                    <div class="flex-1 space-y-4 overflow-y-auto p-4">

                        <div class="mb-4">
                            <label class="mb-1 block text-gray-700 dark:text-gray-300">Nombre</label>
                            <input type="text" class="w-full rounded border border-gray-300 bg-white p-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" wire:model="nombre">
                            @error('nombre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4 flex items-center space-x-2">
                            <input type="checkbox" class="form-checkbox h-5 w-5 rounded border-gray-300 text-blue-600 dark:border-gray-600 dark:bg-gray-900" wire:model="ind_activo">
                            <label class="select-none font-medium text-gray-700 dark:text-gray-300">Opción activa</label>
                        </div>

                        <div x-data="{ mostrar: false }" class="mb-4 rounded border border-gray-200 dark:border-gray-700">
                            <button
                                type="button"
                                class="flex w-full items-center justify-between rounded-t bg-gray-100 px-4 py-2 text-left font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
                                @click="mostrar = !mostrar"
                            >
                                <span>Tiempos (Pasos, Minuto/Paso, Valor Unitario)</span>
                                <svg :class="{'transform rotate-180': mostrar}" class="h-4 w-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="mostrar" x-transition class="space-y-4 p-4 dark:bg-gray-800">
                                <div>
                                    <label class="mb-1 block text-gray-700 dark:text-gray-300">Pasos - Costuras</label>
                                    <input type="number" class="w-full rounded border border-gray-300 bg-white p-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" wire:model="pasos">
                                    @error('pasos') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Horas</label>
                                        <input type="number" wire:model="horas" min="0" class="w-full rounded border border-gray-300 bg-white p-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" placeholder="0">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Minutos</label>
                                        <input type="number" wire:model="minutos" min="0" max="59" class="w-full rounded border border-gray-300 bg-white p-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" placeholder="0">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Segundos</label>
                                        <input type="number" wire:model="segundos" min="0" max="59" class="w-full rounded border border-gray-300 bg-white p-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" placeholder="0">
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-1 block text-gray-700 dark:text-gray-300">Valor Unitario</label>
                                    <input type="number" step="0.01" class="w-full rounded border border-gray-300 bg-white p-2 text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" wire:model="valoru">
                                    @error('valoru') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>


                        <div x-data="{ mostrar: false }" class="mb-4 rounded border border-gray-200 dark:border-gray-700">
                            <button
                                type="button"
                                class="flex w-full items-center justify-between rounded-t bg-gray-100 px-4 py-2 text-left font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
                                @click="mostrar = !mostrar"
                            >
                                <span>Relación con Características</span>
                                <svg :class="{'transform rotate-180': mostrar}" class="h-4 w-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="mostrar" x-transition class="grid max-h-60 grid-cols-1 gap-2 overflow-y-auto p-4 sm:grid-cols-2 dark:bg-gray-800">
                                @foreach($caracteristicas as $caracteristica)
                                    <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300">
                                        <input
                                            type="checkbox"
                                            wire:model="caracteristicasSeleccionadas"
                                            value="{{ $caracteristica->id }}"
                                            class="form-checkbox rounded border-gray-300 text-blue-600 dark:border-gray-600 dark:bg-gray-900"
                                        >
                                        <span class="text-gray-700 dark:text-gray-300">{{ $caracteristica->nombre }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end border-t border-gray-200 p-4 space-x-2 dark:border-gray-700">
                    <button wire:click="cerrarModal" class="rounded bg-gray-200 px-4 py-2 font-semibold text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600">
                        Cancelar
                    </button>
                    <button wire:click="guardar" class="rounded bg-blue-500 px-4 py-2 font-semibold text-white hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-500">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

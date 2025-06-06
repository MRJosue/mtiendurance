<div class="max-w-4xl mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Gestión de Opciones</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-3">
            {{ session('message') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-3">
        <button wire:click="crear" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
            Nueva Opción
        </button>
        <div class="flex space-x-2">
            <input type="text" wire:model="query" placeholder="Buscar por nombre..." class="border border-gray-300 rounded px-4 py-2">
            <select wire:model="filtroActivo" class="border border-gray-300 rounded px-4 py-2">
                <option value="1">Activas</option>
                <option value="0">Inactivas</option>
            </select>
            <button wire:click="buscar" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded">
                Buscar
            </button>
        </div>
    </div>

    <table class="w-full border-collapse border border-gray-300">
        <thead>
            <tr class="bg-gray-100">
                <th class="border border-gray-300 p-2 text-left">Nombre</th>
                <th class="border border-gray-300 p-2 text-left">Pasos</th>
                <th class="border border-gray-300 p-2 text-left">Minuto/Paso</th>
                <th class="border border-gray-300 p-2 text-left">Valor Unitario</th>
                <th class="border border-gray-300 p-2 text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($opciones as $opc)
                <tr>
                    <td class="border border-gray-300 p-2">{{ $opc->nombre }}</td>
                    <td class="border border-gray-300 p-2">{{ $opc->pasos }}</td>
                    <td class="border border-gray-300 p-2">{{ $opc->minutoPaso }}</td>
                    <td class="border border-gray-300 p-2">${{ number_format($opc->valoru, 2) }}</td>

                    <td class="border border-gray-300 p-2 flex space-x-2 justify-center">
                        <button wire:click="editar('{{ $opc->id }}')" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-3 py-1 rounded">
                            Editar
                        </button>
                        {{-- <button wire:click="confirmarDesactivacion('{{ $opc->id }}')" class="bg-red-500 hover:bg-red-600 text-white font-semibold px-3 py-1 rounded">
                            Desactivar
                        </button> --}}
                        {{-- <button wire:click="confirmarEliminacionTotal('{{ $opc->id }}')" class="bg-red-700 hover:bg-red-800 text-white font-semibold px-3 py-1 rounded">
                            Eliminar
                        </button> --}}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $opciones->links() }}
    </div>


    @if($mostrarConfirmacion)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
                <h2 class="text-lg font-bold mb-4">Advertencia</h2>
                <p class="mb-6">{{ $mensajeConfirmacion }}</p>
                <div class="flex justify-end space-x-2">
                    <button wire:click="$set('mostrarConfirmacion', false)" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                        Cancelar
                    </button>
                    <button wire:click="ejecutarAccionConfirmada" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($modal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded shadow-lg w-full max-w-md">
                <div class="flex items-center justify-between border-b border-gray-200 p-4">
                    <h5 class="text-xl font-bold">{{ $opcion_id ? 'Editar Opción' : 'Crear Nueva Opción' }}</h5>
                    <button class="text-gray-500 hover:text-gray-700" wire:click="cerrarModal">&times;</button>
                </div>
                <div class="p-4">
                    <div class="overflow-y-auto p-4 space-y-4 flex-1">

                        <div class="mb-4">
                            <label class="block text-gray-700 mb-1">Nombre</label>
                            <input type="text" class="w-full border border-gray-300 rounded p-2" wire:model="nombre">
                            @error('nombre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4 flex items-center space-x-2">
                            <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600" wire:model="ind_activo">
                            <label class="text-gray-700 font-medium select-none">Opción activa</label>
                        </div>

                        <div x-data="{ mostrar: false }" class="mb-4 border border-gray-200 rounded">
                            <button
                                type="button"
                                class="w-full text-left px-4 py-2 bg-gray-100 hover:bg-gray-200 font-semibold text-gray-700 rounded-t flex justify-between items-center"
                                @click="mostrar = !mostrar"
                            >
                                    <span>Tiempos (Pasos, Minuto/Paso, Valor Unitario)</span>
                                    <svg :class="{'transform rotate-180': mostrar}" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="mostrar" x-transition class="p-4 space-y-4">
                                    <div>
                                        <label class="block text-gray-700 mb-1">Pasos - Costuras</label>
                                        <input type="number" class="w-full border border-gray-300 rounded p-2" wire:model="pasos">
                                        @error('pasos') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="grid grid-cols-3 gap-2">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Horas</label>
                                            <input type="number" wire:model="horas" min="0" class="w-full border border-gray-300 rounded p-2" placeholder="0">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Minutos</label>
                                            <input type="number" wire:model="minutos" min="0" max="59" class="w-full border border-gray-300 rounded p-2" placeholder="0">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Segundos</label>
                                            <input type="number" wire:model="segundos" min="0" max="59" class="w-full border border-gray-300 rounded p-2" placeholder="0">
                                        </div>
                                    </div>

                                    
                                    <div>
                                        <label class="block text-gray-700 mb-1">Valor Unitario</label>
                                        <input type="number" step="0.01" class="w-full border border-gray-300 rounded p-2" wire:model="valoru">
                                        @error('valoru') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                        </div>


                        <div x-data="{ mostrar: false }" class="mb-4 border border-gray-200 rounded">
                            <button
                                type="button"
                                class="w-full text-left px-4 py-2 bg-gray-100 hover:bg-gray-200 font-semibold text-gray-700 rounded-t flex justify-between items-center"
                                @click="mostrar = !mostrar"
                            >
                                <span>Relación con Características</span>
                                <svg :class="{'transform rotate-180': mostrar}" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="mostrar" x-transition class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-60 overflow-y-auto">
                                @foreach($caracteristicas as $caracteristica)
                                    <label class="flex items-center space-x-2">
                                        <input
                                            type="checkbox"
                                            wire:model="caracteristicasSeleccionadas"
                                            value="{{ $caracteristica->id }}"
                                            class="form-checkbox text-blue-600"
                                        >
                                        <span class="text-gray-700">{{ $caracteristica->nombre }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end border-t border-gray-200 p-4 space-x-2">
                    <button wire:click="cerrarModal" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-4 py-2 rounded">
                        Cancelar
                    </button>
                    <button wire:click="guardar" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

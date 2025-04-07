<div class="container mx-auto p-6">

    <div class="mb-4 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
        <div class="flex items-center space-x-2">
            <button wire:click="openModal" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                Nueva Talla
            </button>
    

        </div>
    
        <div class="flex space-x-2">
            <select wire:model="filtroActivo" class="px-3 py-2 border rounded-lg">
                <option value="1">Activas</option>
                <option value="0">Inactivas</option>
            </select>
            
            <input type="text" wire:model="query" placeholder="Buscar por nombre..." class="px-3 py-2 border rounded-lg">
            <button wire:click="buscar" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                Buscar
            </button>
        </div>
    </div>


    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full border-collapse border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Nombre</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Descripción</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tallas as $talla)
                    <tr class="hover:bg-gray-50">
                        <td class="border-b px-4 py-2 text-gray-700">{{ $talla->nombre }}</td>
                        <td class="border-b px-4 py-2 text-gray-700">{{ $talla->descripcion }}</td>
                        <td class="border-b px-4 py-2 text-gray-700">
                            <button wire:click="edit({{ $talla->id }})" 
                                class="text-blue-500 hover:underline mr-2">Editar</button>
                            {{-- <button wire:click="confirmDelete({{ $talla->id }})" 
                                class="text-red-500 hover:underline">Eliminar</button> --}}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $tallas->links() }}
    </div>

    <!-- Modal Formulario -->
    @if($modalOpen)
        <div class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg shadow-lg w-1/3">
                <h2 class="text-lg font-bold mb-4">{{ $talla_id ? 'Editar' : 'Nueva' }} Talla</h2>
                <input type="text" wire:model="nombre" placeholder="Nombre"
                    class="w-full px-4 py-2 border rounded-lg mb-2" />

                             <div class="mb-2 flex items-center space-x-2">
                    <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600" wire:model="ind_activo">
                    <label class="text-gray-700 font-medium select-none">Talla activa</label>
                </div>
                <textarea wire:model="descripcion" placeholder="Descripción"
                    class="w-full px-4 py-2 border rounded-lg mb-2"></textarea>
                <div class="flex justify-end space-x-2">
                    <button wire:click="$set('modalOpen', false)" 
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                        Cancelar
                    </button>
                    <button wire:click="save" 
                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif


    @if($mostrarConfirmacion)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[999]">
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


    <!-- Modal Confirmación Eliminación -->
    @if($confirmingDelete)
        <div class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg shadow-lg w-1/3">
                <h2 class="text-lg font-bold mb-4">¿Eliminar esta talla?</h2>
                <div class="flex justify-end space-x-2">
                    <button wire:click="$set('confirmingDelete', false)" 
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                        Cancelar
                    </button>
                    <button wire:click="delete" 
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

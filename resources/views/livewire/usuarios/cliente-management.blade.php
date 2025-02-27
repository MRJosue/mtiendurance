<div class="container mx-auto p-6">
    @if(session()->has('error'))
        <div class="mb-4 text-red-500">{{ session('error') }}</div>
    @endif

    <button wire:click="create" class="px-4 py-2 bg-blue-500 text-white rounded-lg">Agregar Cliente</button>

    <table class="w-full mt-4 border-collapse border border-gray-300">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">Nombre Empresa</th>
                <th class="border px-4 py-2">Contacto Principal</th>
                <th class="border px-4 py-2">Teléfono</th>
                <th class="border px-4 py-2">Email</th>
                <th class="border px-4 py-2">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $cliente)
                <tr>
                    <td class="border px-4 py-2">{{ $cliente->nombre_empresa }}</td>
                    <td class="border px-4 py-2">{{ $cliente->contacto_principal }}</td>
                    <td class="border px-4 py-2">{{ $cliente->telefono }}</td>
                    <td class="border px-4 py-2">{{ $cliente->email }}</td>
                    <td class="border px-4 py-2">
                        <button wire:click="edit({{ $cliente->id }})" class="px-2 py-1 bg-yellow-500 text-white rounded">Editar</button>
                        <button wire:click="delete({{ $cliente->id }})" class="px-2 py-1 bg-red-500 text-white rounded">Eliminar</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($modalOpen)
        <div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-xl font-bold mb-4">{{ $clienteId ? 'Editar Cliente' : 'Nuevo Cliente' }}</h2>
                
                <input type="text" wire:model="nombre_empresa" placeholder="Nombre de la Empresa" class="w-full px-4 py-2 border rounded mb-2">
                <input type="text" wire:model="contacto_principal" placeholder="Contacto Principal" class="w-full px-4 py-2 border rounded mb-2">
                <input type="text" wire:model="telefono" placeholder="Teléfono" class="w-full px-4 py-2 border rounded mb-2">
                <input type="email" wire:model="email" placeholder="Email" class="w-full px-4 py-2 border rounded mb-2">
                <div class="flex justify-end space-x-2">
                    <button wire:click="$set('modalOpen', false)" class="px-4 py-2 bg-gray-500 text-white rounded">Cancelar</button>
                    <button wire:click="save" class="px-4 py-2 bg-blue-500 text-white rounded">Guardar</button>
                </div>
            </div>
        </div>
    @endif
</div>

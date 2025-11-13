<div class="max-w-4xl mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Gestión de Direcciones de Entrega</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-3">
            {{ session('message') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-3 space-x-2">
        <button wire:click="crear" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded mb-3">
            Nueva Dirección
        </button>
        <div class="flex space-x-2">
            <input type="text" wire:model="query" placeholder="Buscar por contacto..." class="border border-gray-300 rounded px-4 py-2">
            <button wire:click="buscar" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded">Buscar</button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 px-4 py-2 text-left">Nombre de Contacto</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Nombre de Empresa</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Calle</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Ciudad</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Estado</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">País</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Predeterminado</th>
                    <th class="border border-gray-300 px-4 py-2 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($direcciones as $dir)
                    <tr class="hover:bg-gray-50">
                        <td class="border border-gray-300 px-4 py-2">{{ $dir->nombre_contacto }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $dir->nombre_empresa }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $dir->calle }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $dir->ciudad->nombre ?? '-' }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $dir->ciudad->estado->nombre ?? '-' }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $dir->ciudad->estado->pais->nombre ?? '-' }}</td>
                        <td class="border border-gray-300 px-4 py-2 text-center">
                            @if ($dir->flag_default)
                                <span class="text-green-600 font-semibold">Sí</span>
                            @else
                                <button wire:click="establecerDefault({{ $dir->id }})" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-3 py-1 rounded">Establecer</button>
                            @endif
                        </td>
                        <td class="border border-gray-300 px-4 py-2 text-center">
                            <button wire:click="editar('{{ $dir->id }}')" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-3 py-1 rounded">Editar</button>
                            <button wire:click="borrar('{{ $dir->id }}')" class="bg-red-500 hover:bg-red-600 text-white font-semibold px-3 py-1 rounded" onclick="return confirm('¿Estás seguro de eliminar esta dirección?')">Eliminar</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $direcciones->links() }}
    </div>

@if($modal)
<div class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-3xl">
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-gray-200 p-4">
            <h5 class="text-xl font-bold">
                {{ $direccion_id ? 'Editar Dirección' : 'Crear Nueva Dirección' }}
            </h5>
            <button class="text-gray-500 hover:text-gray-700" wire:click="cerrarModal">&times;</button>
        </div>

        {{-- Body scrollable con grid responsivo --}}
        <div class="p-4 max-h-[75vh] overflow-y-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                <div>
                    <label class="block text-gray-700 mb-1">Nombre de Contacto</label>
                    <input type="text" class="w-full border border-gray-300 rounded p-2" wire:model.defer="nombre_contacto">
                    @error('nombre_contacto') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 mb-1">Nombre de Empresa</label>
                    <input type="text" class="w-full border border-gray-300 rounded p-2" wire:model.defer="nombre_empresa">
                    @error('nombre_empresa') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-gray-700 mb-1">Calle</label>
                    <input type="text" class="w-full border border-gray-300 rounded p-2" wire:model.defer="calle">
                    @error('calle') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Cascada País -> Estado -> Ciudad --}}
                <div>
                    <label class="block text-gray-700 mb-1">País</label>
                    <select wire:model.live="pais_id" class="w-full border border-gray-300 rounded p-2">
                        <option value="">Seleccione un País</option>
                        @foreach($paisesList as $pais)
                            <option value="{{ $pais->id }}">{{ $pais->nombre }}</option>
                        @endforeach
                    </select>
                    @error('pais_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 mb-1">Estado</label>
                    <select wire:model.live="estado_id" class="w-full border border-gray-300 rounded p-2" @disabled(!$pais_id)>
                        <option value="">Seleccione un Estado</option>
                        @foreach($estadosList as $estado)
                            <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                        @endforeach
                    </select>
                    @error('estado_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 mb-1">Ciudad</label>
                    <select wire:model.live="ciudad_id" class="w-full border border-gray-300 rounded p-2" @disabled(!$estado_id)>
                        <option value="">Seleccione una Ciudad</option>
                        @foreach($ciudadesList as $ciudad)
                            <option value="{{ $ciudad->id }}">{{ $ciudad->nombre }}</option>
                        @endforeach
                    </select>
                    @error('ciudad_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 mb-1">Código Postal</label>
                    <input type="text" class="w-full border border-gray-300 rounded p-2" wire:model.defer="codigo_postal">
                    @error('codigo_postal') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 mb-1">Teléfono</label>
                    <input type="text" class="w-full border border-gray-300 rounded p-2" wire:model.defer="telefono">
                    @error('telefono') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end border-t border-gray-200 p-4 space-x-2">
            <button wire:click="cerrarModal" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-4 py-2 rounded">Cancelar</button>
            <button wire:click="guardar" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">Guardar</button>
        </div>
    </div>
</div>
@endif

</div>

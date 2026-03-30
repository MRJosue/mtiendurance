<div class="mx-auto max-w-4xl p-4 text-gray-900 dark:text-gray-100">
    <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-gray-100">Gestión de Direcciones Fiscales</h2>

    @if (session()->has('message'))
        <div class="mb-3 rounded bg-green-100 p-3 text-green-800 dark:bg-green-900/40 dark:text-green-200">
            {{ session('message') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-3 space-x-2">
        <button wire:click="crear" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded mb-3">
            Nueva Dirección Fiscal
        </button>
        <div class="flex space-x-2">
            <input type="text" wire:model="query" placeholder="Buscar por RFC..." class="rounded border border-gray-300 bg-white px-4 py-2 text-gray-700 placeholder:text-gray-400 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500">
            <button wire:click="buscar" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded">Buscar</button>
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg bg-white shadow dark:bg-gray-900/80">
        <table class="min-w-full border-collapse border border-gray-300 dark:border-gray-700">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800/90">
                    <th class="border border-gray-300 px-4 py-2 text-left dark:border-gray-700 dark:text-gray-300">Razon Social</th>
                    <th class="border border-gray-300 px-4 py-2 text-left dark:border-gray-700 dark:text-gray-300">RFC</th>
                    <th class="border border-gray-300 px-4 py-2 text-left dark:border-gray-700 dark:text-gray-300">Calle</th>
                    <th class="border border-gray-300 px-4 py-2 text-left dark:border-gray-700 dark:text-gray-300">Ciudad</th>
                    <th class="border border-gray-300 px-4 py-2 text-left dark:border-gray-700 dark:text-gray-300">Estado</th>
                    <th class="border border-gray-300 px-4 py-2 text-left dark:border-gray-700 dark:text-gray-300">País</th>
                    <th class="border border-gray-300 px-4 py-2 text-left dark:border-gray-700 dark:text-gray-300">Predeterminado</th>
                    <th class="border border-gray-300 px-4 py-2 text-center dark:border-gray-700 dark:text-gray-300">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($direcciones as $dir)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/70">
                        <td class="border border-gray-300 px-4 py-2 dark:border-gray-700">{{ $dir->razon_social }}</td>
                        <td class="border border-gray-300 px-4 py-2 dark:border-gray-700">{{ $dir->rfc }}</td>
                        <td class="border border-gray-300 px-4 py-2 dark:border-gray-700">{{ $dir->calle }}</td>
                        <td class="border border-gray-300 px-4 py-2 dark:border-gray-700">{{ $dir->ciudad ?? '-' }}</td>
                        <td class="border border-gray-300 px-4 py-2 dark:border-gray-700">{{ $dir->estado->nombre ?? '-' }}</td>
                        <td class="border border-gray-300 px-4 py-2 dark:border-gray-700">{{ $dir->pais->nombre ?? ($dir->estado->pais->nombre ?? '-') }}</td>
                        <td class="border border-gray-300 px-4 py-2 text-center dark:border-gray-700">
                            @if ($dir->flag_default)
                                <span class="text-green-600 font-semibold">Sí</span>
                            @else
                                <button wire:click="establecerDefault({{ $dir->id }})" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-3 py-1 rounded">Establecer</button>
                            @endif
                        </td>
                        <td class="border border-gray-300 px-4 py-2 text-center dark:border-gray-700">
                            <button wire:click="editar('{{ $dir->id }}')" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-3 py-1 rounded">Editar</button>
                            <button wire:click="borrar('{{ $dir->id }}')" class="bg-red-500 hover:bg-red-600 text-white font-semibold px-3 py-1 rounded" onclick="return confirm('¿Estás seguro de eliminar esta dirección fiscal?')">Eliminar</button>
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
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="w-full max-w-md rounded bg-white text-gray-900 shadow-lg dark:bg-gray-900 dark:text-gray-100">
                <div class="flex items-center justify-between border-b border-gray-200 p-4 dark:border-gray-700">
                    <h5 class="text-xl font-bold">{{ $direccion_id ? 'Editar Dirección Fiscal' : 'Crear Nueva Dirección Fiscal' }}</h5>
                    <button class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" wire:click="cerrarModal">&times;</button>
                </div>




                <div class="p-4">

                    <div class="mb-4">
                        <label class="mb-1 block text-gray-700 dark:text-gray-300">Razón social</label>
                        <input type="text" class="w-full rounded border border-gray-300 bg-white p-2 text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" wire:model="razon_social">
                        @error('razon_social') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>


                    <div class="mb-4">
                        <label class="mb-1 block text-gray-700 dark:text-gray-300">RFC</label>
                        <input type="text" class="w-full rounded border border-gray-300 bg-white p-2 text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" wire:model="rfc">
                        @error('rfc') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="mb-1 block text-gray-700 dark:text-gray-300">Calle</label>
                        <input type="text" class="w-full rounded border border-gray-300 bg-white p-2 text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" wire:model="calle">
                        @error('calle') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>


                    <div class="mb-4">
                        <label class="mb-1 block text-gray-700 dark:text-gray-300">País</label>
                        <select wire:model.live="pais_id" class="w-full rounded border border-gray-300 bg-white p-2 text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">Seleccione un País</option>
                            @foreach($paises as $pais)
                                <option value="{{ $pais->id }}">{{ $pais->nombre }}</option>
                            @endforeach
                        </select>
                        @error('pais_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

<div class="mb-4">
    <label class="mb-1 block text-gray-700 dark:text-gray-300">Estado</label>
    <select
        wire:key="estado-{{ $pais_id ?? 'x' }}"
        wire:model.live="estado_id"
        class="w-full rounded border border-gray-300 bg-white p-2 text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
        @disabled(!$pais_id)
    >
        <option value="">Seleccione un Estado</option>
        @foreach($estados as $estado)
            <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
        @endforeach
    </select>
    @error('estado_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
</div>

<div class="mb-4">
    <label class="mb-1 block text-gray-700 dark:text-gray-300">Ciudad</label>
    <input
        type="text"
        class="w-full rounded border border-gray-300 bg-white p-2 text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
        wire:model.defer="ciudad"
        placeholder="Ej. Puebla"
        @disabled(!$estado_id)
    >
    @error('ciudad') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
</div>


                    <div class="mb-4">
                        <label class="mb-1 block text-gray-700 dark:text-gray-300">Código Postal</label>
                        <input type="text" class="w-full rounded border border-gray-300 bg-white p-2 text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" wire:model="codigo_postal">
                        @error('codigo_postal') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="flex items-center justify-end space-x-2 border-t border-gray-200 p-4 dark:border-gray-700">
                    <button wire:click="cerrarModal" class="rounded bg-gray-200 px-4 py-2 font-semibold text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600">Cancelar</button>
                    <button wire:click="guardar" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">Guardar</button>
                </div>
            </div>
        </div>
    @endif
</div>

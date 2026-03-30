<div 
    x-data="{
        abierto: JSON.parse(localStorage.getItem('configuraciones-usuario-empresa') ?? 'true'),
        toggle() {
            this.abierto = !this.abierto;
            localStorage.setItem('configuraciones-usuario-empresa', JSON.stringify(this.abierto));
        }
    }"
    class="container mx-auto p-6 text-gray-900 dark:text-gray-100"
>
    <h2 
        @click="toggle()"
        class="mb-4 cursor-pointer border-b border-gray-300 pb-2 text-xl font-bold transition hover:text-blue-600 dark:border-gray-700 dark:hover:text-blue-400"
    >
        Organizacion Principal
        <span class="ml-2 text-sm text-gray-500 dark:text-gray-400" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
    </h2>

    <div x-show="abierto" x-transition>
        <div class="flex flex-wrap gap-4 mb-4 items-center">
            <input
                wire:model.debounce.400ms="search"
                type="text"
                placeholder="Buscar empresa..."
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 placeholder:text-gray-400 sm:w-64 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500"
            />

            {{-- Ya no se permite crear organización principal --}}
            {{-- 
            @can('usuarios.configuracion.seccion.administra.Organizacion')
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700" wire:click="nuevaEmpresa">
                    + Nueva Organizacion Principal
                </button>
            @endcan
            --}}
        </div>

        <div class="min-h-64 overflow-x-auto rounded-lg bg-white pb-8 shadow dark:bg-gray-900/80">
            <table class="min-w-full border-collapse border border-gray-200 dark:border-gray-700">
                <thead class="bg-gray-100 dark:bg-gray-800/90">
                    <tr>
                        <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Nombre</th>
                        <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">RFC</th>
                        <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Teléfono</th>
                        <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Dirección</th>
                        <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Propietario</th>
                        <th class="border-b border-gray-200 px-4 py-2 text-center text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($empresas as $empresa)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/70">
                            <td class="border-b border-gray-200 px-4 py-2 dark:border-gray-700">{{ $empresa->nombre }}</td>
                            <td class="border-b border-gray-200 px-4 py-2 dark:border-gray-700">{{ $empresa->rfc }}</td>
                            <td class="border-b border-gray-200 px-4 py-2 dark:border-gray-700">{{ $empresa->telefono }}</td>
                            <td class="border-b border-gray-200 px-4 py-2 dark:border-gray-700">{{ $empresa->direccion }}</td>
                            <td class="border-b border-gray-200 px-4 py-2 dark:border-gray-700">
                                @if($empresa->propietario)
                                    {{ $empresa->propietario->name }} ({{ $empresa->propietario->email }})
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">Sin propietario</span>
                                @endif
                            </td>
                            <td class="border-b border-gray-200 px-4 py-2 text-center dark:border-gray-700">
                                <x-dropdown>
                                    @can('usuarios.configuracion.seccion.administra.Organizacion')
                                        <x-dropdown.item>
                                            <b wire:click="editarEmpresa({{ $empresa->id }})">Editar</b>
                                        </x-dropdown.item>
                                        {{-- <x-dropdown.item separator>
                                            <b wire:click="confirmarEliminar({{ $empresa->id }})">Eliminar</b>
                                        </x-dropdown.item> --}}
                                    @endcan
                                </x-dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-gray-400 dark:text-gray-500">No hay empresas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $empresas->links() }}</div>

        {{-- Modal Nueva/Editar Empresa --}}
        @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div class="relative w-full max-w-lg rounded-lg bg-white p-8 text-gray-900 shadow-lg dark:bg-gray-900 dark:text-gray-100">
                <h2 class="mb-4 text-xl font-semibold">
                    {{-- Solo edición, ya no hay creación --}}
                    Editar Empresa
                </h2>
                <form wire:submit.prevent="guardarEmpresa">
                    <div class="mb-3">
                        <label class="mb-1 block font-medium">Nombre *</label>
                        <input type="text" wire:model.defer="nombre" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" required />
                        @error('nombre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="mb-1 block font-medium">RFC *</label>
                        <input type="text" wire:model.defer="rfc" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" required />
                        @error('rfc') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="mb-1 block font-medium">Teléfono</label>
                        <input type="text" wire:model.defer="telefono" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                    </div>
                    <div class="mb-3">
                        <label class="mb-1 block font-medium">Dirección</label>
                        <input type="text" wire:model.defer="direccion" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                    </div>

                    {{-- Campo propietario eliminado en edición --}}

                    <div class="flex justify-end gap-2 mt-4">
                        <button
                            type="button"
                            wire:click="$set('showModal', false)"
                            class="rounded bg-gray-200 px-4 py-2 text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                        >
                            Guardar
                        </button>
                    </div>
                </form>
                <button
                    wire:click="$set('showModal', false)"
                    class="absolute right-2 top-2 text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                >
                    ✕
                </button>
            </div>
        </div>
        @endif

        {{-- Modal Eliminar --}}
        @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div class="relative w-full max-w-md rounded-lg bg-white p-8 text-gray-900 shadow-lg dark:bg-gray-900 dark:text-gray-100">
                <h2 class="mb-4 text-xl font-semibold">Eliminar Empresa</h2>
                @if($alertaRelacionUsuarios)
                    <div class="mb-3 text-red-600 dark:text-red-400">
                        No puedes eliminar una empresa con propietario asignado. Transfiere primero.
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button
                            type="button"
                            wire:click="$set('showDeleteModal', false)"
                            class="rounded bg-gray-200 px-4 py-2 text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
                        >
                            Cerrar
                        </button>
                    </div>
                @else
                    <div class="mb-3 text-gray-700 dark:text-gray-300">
                        ¿Seguro que deseas eliminar esta empresa? Esta acción no se puede deshacer.
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button
                            type="button"
                            wire:click="$set('showDeleteModal', false)"
                            class="rounded bg-gray-200 px-4 py-2 text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            wire:click="eliminarEmpresa"
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                        >
                            Eliminar
                        </button>
                    </div>
                @endif
                <button
                    wire:click="$set('showDeleteModal', false)"
                    class="absolute right-2 top-2 text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                >
                    ✕
                </button>
            </div>
        </div>
        @endif

        {{-- Toast --}}
        <div
            x-data="{ show: false, message: '', type: '' }"
            x-on:notify.window="
                message = $event.detail.message;
                type = $event.detail.type;
                show = true;
                setTimeout(() => show = false, 2600);
            "
            x-show="show" x-transition
            class="fixed bottom-6 right-6 z-50 flex min-w-[240px] items-center rounded-lg p-4 shadow-lg"
            :class="type === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200'"
            style="display: none;"
        >
            <span x-text="message"></span>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Alpine + Livewire
        });
    </script>
</div>

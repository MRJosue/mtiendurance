<div 
    x-data="{
        abierto: JSON.parse(localStorage.getItem('configuraciones-usuario-empresa') ?? 'true'),
        toggle() {
            this.abierto = !this.abierto;
            localStorage.setItem('configuraciones', JSON.stringify(this.abierto));
        }
    }"
    class="container mx-auto p-6"
>
            <h2 
                @click="toggle()"
                class="text-xl font-bold mb-4 border-b border-gray-300 pb-2 cursor-pointer hover:text-blue-600 transition"
            >
                Mis emrpesas
            <span class="text-sm text-gray-500 ml-2" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
            </h2>   

<!-- Contenido del panel -->
<div x-show="abierto" x-transition>

                        


        {{-- <div x-data class="container mx-auto p-6"> --}}


            <!-- Buscador y botón nueva empresa -->
            <div class="flex flex-wrap gap-4 mb-4 items-center">
                <input
                    wire:model.debounce.400ms="search"
                    type="text"
                    placeholder="Buscar empresa..."
                    class="w-full sm:w-64 px-3 py-2 border rounded-lg"
                />
                <button
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    wire:click="nuevaEmpresa"
                >
                    + Nueva Empresa
                </button>
            </div>

            <!-- Tabla Empresas -->
            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table class="min-w-full border-collapse border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Nombre</th>
                            <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">RFC</th>
                            <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Teléfono</th>
                            <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Dirección</th>
                            <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Clientes Principales</th>
                            <th class="border-b px-4 py-2 text-center text-sm font-medium text-gray-600">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($empresas as $empresa)
                            <tr class="hover:bg-gray-50">
                                <td class="border-b px-4 py-2">{{ $empresa->nombre }}</td>
                                <td class="border-b px-4 py-2">{{ $empresa->rfc }}</td>
                                <td class="border-b px-4 py-2">{{ $empresa->telefono }}</td>
                                <td class="border-b px-4 py-2">{{ $empresa->direccion }}</td>
                                <td class="border-b px-4 py-2">
                                    @if($empresa->clientesPrincipales()->count() > 0)
                                        <ul class="list-disc list-inside">
                                            @foreach($empresa->clientesPrincipales as $cliente)
                                                <li>{{ $cliente->name }} ({{ $cliente->email }})</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-gray-500">Sin clientes</span>
                                    @endif
                                </td>
                                <td class="border-b px-4 py-2 text-center space-x-1">

                                <x-dropdown>
                                        <x-dropdown.item>
                                        <b wire:click="editarEmpresa({{ $empresa->id }})">Editar</b>
                                        </x-dropdown.item>
                                
                                        <x-dropdown.item separator>
                                            <b  wire:click="gestionarUsuarios({{ $empresa->id }})" >Usuarioss</b>
                                        </x-dropdown.item>
                                
                                        <x-dropdown.item separator>
                                            <b  wire:click="confirmarEliminar({{ $empresa->id }})" >Eliminar</b>
                                        </x-dropdown.item>
                                </x-dropdown>


                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-gray-400 py-4">No hay empresas registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-4">
                {{ $empresas->links() }}
            </div>

            {{-- Modal Nueva/Editar Empresa --}}
            @if($showModal)
            <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
                <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-lg relative">
                    <h2 class="text-xl font-semibold mb-4">{{ $empresaId ? 'Editar Empresa' : 'Nueva Empresa' }}</h2>
                    <form wire:submit.prevent="guardarEmpresa">
                        <div class="mb-3">
                            <label class="block mb-1 font-medium">Nombre *</label>
                            <input type="text" wire:model.defer="nombre" class="w-full border rounded-lg px-3 py-2" required />
                            @error('nombre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="block mb-1 font-medium">RFC *</label>
                            <input type="text" wire:model.defer="rfc" class="w-full border rounded-lg px-3 py-2" required />
                            @error('rfc') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="block mb-1 font-medium">Teléfono</label>
                            <input type="text" wire:model.defer="telefono" class="w-full border rounded-lg px-3 py-2" />
                        </div>
                        <div class="mb-3">
                            <label class="block mb-1 font-medium">Dirección</label>
                            <input type="text" wire:model.defer="direccion" class="w-full border rounded-lg px-3 py-2" />
                        </div>
                        <div class="mb-3">
                            <label class="block mb-1 font-medium">Clientes Principales</label>
                            <select multiple wire:model="usuariosSeleccionados" class="w-full border rounded-lg px-3 py-2 h-32">
                                @foreach($usuariosClientePrincipal as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex justify-end gap-2 mt-4">
                            <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancelar</button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Guardar</button>
                        </div>
                    </form>
                    <!-- Cerrar -->
                    <button wire:click="$set('showModal', false)" class="absolute top-2 right-2 text-gray-500 hover:text-gray-800">✕</button>
                </div>
            </div>
            @endif

            {{-- Modal Usuarios --}}
            @if($showUsuariosModal)
            <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
                <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-lg relative">
                    <h2 class="text-xl font-semibold mb-4">Gestionar Clientes Principales</h2>
                    <div>
                        <label class="block mb-2 font-medium">Selecciona los clientes principales de la empresa</label>
                        <select multiple wire:model="usuariosSeleccionados" class="w-full border rounded-lg px-3 py-2 h-40">
                            @foreach($usuariosClientePrincipal as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" wire:click="$set('showUsuariosModal', false)" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancelar</button>
                        <button type="button" wire:click="guardarUsuarios" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Guardar</button>
                    </div>
                    <button wire:click="$set('showUsuariosModal', false)" class="absolute top-2 right-2 text-gray-500 hover:text-gray-800">✕</button>
                </div>
            </div>
            @endif

            {{-- Modal Eliminar --}}
            @if($showDeleteModal)
            <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
                <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md relative">
                    <h2 class="text-xl font-semibold mb-4">Eliminar Empresa</h2>
                    @if($alertaRelacionUsuarios)
                        <div class="mb-3 text-red-600">No puedes eliminar una empresa con usuarios asignados. Primero desasigna los usuarios.</div>
                        <div class="flex justify-end gap-2 mt-4">
                            <button type="button" wire:click="$set('showDeleteModal', false)" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cerrar</button>
                        </div>
                    @else
                        <div class="mb-3">¿Seguro que deseas eliminar esta empresa? Esta acción no se puede deshacer.</div>
                        <div class="flex justify-end gap-2 mt-4">
                            <button type="button" wire:click="$set('showDeleteModal', false)" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancelar</button>
                            <button type="button" wire:click="eliminarEmpresa" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Eliminar</button>
                        </div>
                    @endif
                    <button wire:click="$set('showDeleteModal', false)" class="absolute top-2 right-2 text-gray-500 hover:text-gray-800">✕</button>
                </div>
            </div>
            @endif

            {{-- Notificaciones Livewire/AlpineJS --}}
            <div
                x-data="{ show: false, message: '', type: '' }"
                x-on:notify.window="
                    message = $event.detail.message;
                    type = $event.detail.type;
                    show = true;
                    setTimeout(() => show = false, 2600);
                "
                x-show="show"
                x-transition
                class="fixed bottom-6 right-6 z-50 min-w-[240px] flex items-center p-4 rounded-lg"
                :class="type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                style="display: none;"
            >
                <span x-text="message"></span>
            </div>
</div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // No custom scripts, Alpine y Livewire se encargan de los modales y notificaciones.
        });
    </script>
</div>

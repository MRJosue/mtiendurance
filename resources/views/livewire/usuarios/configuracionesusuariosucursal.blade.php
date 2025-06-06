<div 
    x-data="{
        abierto: JSON.parse(localStorage.getItem('configuracionesusuariosucursal') ?? 'true'),
        toggle() {
            this.abierto = !this.abierto;
            localStorage.setItem('configuracionesusuariosucursal', JSON.stringify(this.abierto));
        }
    }"
    class="container mx-auto p-6"
>
            <h2 
                @click="toggle()"
                class="text-xl font-bold mb-4 border-b border-gray-300 pb-2 cursor-pointer hover:text-blue-600 transition"
            >
                Mis Sucursales
            <span class="text-sm text-gray-500 ml-2" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
            </h2>   

<!-- Contenido del panel -->
    <div x-show="abierto" x-transition>
            <!-- Acciones y Crear/Editar -->
            <div class="mb-6 bg-white rounded-xl shadow p-6">
                <form wire:submit.prevent="{{ $editingId ? 'update' : 'store' }}" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block mb-1 text-gray-700">Empresa</label>
                            <select wire:model="empresa_id" class="w-full border rounded-lg px-3 py-2">
                                <option value="">Seleccione...</option>
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                                @endforeach
                            </select>
                            @error('empresa_id') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-gray-700">Nombre</label>
                            <input type="text" wire:model="nombre" class="w-full border rounded-lg px-3 py-2" />
                            @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-gray-700">Teléfono</label>
                            <input type="text" wire:model="telefono" class="w-full border rounded-lg px-3 py-2" />
                        </div>
                        <div>
                            <label class="block mb-1 text-gray-700">Dirección</label>
                            <input type="text" wire:model="direccion" class="w-full border rounded-lg px-3 py-2" />
                        </div>
                    </div>
                    <div class="flex gap-2 mt-4">
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            {{ $editingId ? 'Actualizar' : 'Crear' }} Sucursal
                        </button>
                        @if($editingId)
                        <button type="button" wire:click="resetInput"
                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
                            Cancelar
                        </button>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Filtro de búsqueda -->
            <div class="mb-4">
                <input type="text" wire:model.debounce.500ms="search" class="border px-4 py-2 rounded-lg w-full sm:w-1/3" placeholder="Buscar sucursal..." />
            </div>

            <!-- Listado -->
            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table class="min-w-full border-collapse border border-gray-200 rounded-lg">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">ID</th>
                            <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Nombre</th>
                            <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Empresa</th>
                            <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Teléfono</th>
                            <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Dirección</th>
                            <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Usuarios Asignados</th>
                            <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sucursales as $sucursal)
                        <tr class="hover:bg-gray-50">
                            <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $sucursal->id }}</td>
                            <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $sucursal->nombre }}</td>
                            <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $sucursal->empresa->nombre ?? '-' }}</td>
                            <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $sucursal->telefono }}</td>
                            <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $sucursal->direccion }}</td>
                            <td class="border-b px-4 py-2 text-gray-700 text-sm">
                                <span class="inline-block bg-blue-100 text-blue-800 rounded px-2 py-1 text-xs">{{ $sucursal->usuarios->count() }}</span>
                                <button wire:click="openUserModal({{ $sucursal->id }})" class="ml-2 px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-700 text-xs">Editar</button>
                            </td>
                            <td class="border-b px-4 py-2 text-gray-700 text-sm">
                                <button wire:click="edit({{ $sucursal->id }})"
                                    class="px-2 py-1 bg-yellow-400 text-gray-800 rounded hover:bg-yellow-500 text-xs">Editar</button>
                                <button wire:click="delete({{ $sucursal->id }})"
                                    onclick="return confirm('¿Eliminar sucursal?')"
                                    class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-700 text-xs ml-1">Eliminar</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 text-gray-500">Sin sucursales</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $sucursales->links() }}</div>

            <!-- MODAL: Usuarios asignados -->
            <div
                x-data="{ open: @entangle('showUserModal') }"
                x-show="open"
                style="display: none"
                class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-40"
            >
                <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-lg mx-auto">
                    <h2 class="text-lg font-bold mb-2 text-gray-700">Asignar usuarios a la sucursal</h2>
                    <div class="mb-4">
                        <label class="block mb-1 text-gray-700">Selecciona usuarios:</label>
                        <select multiple wire:model="selectedUsers" class="w-full border rounded-lg px-3 py-2 h-40">
                            @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2 mt-4">
                        <button wire:click="saveUsersToSucursal"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Guardar
                        </button>
                        <button wire:click="closeUserModal"
                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
    </div>
</div>

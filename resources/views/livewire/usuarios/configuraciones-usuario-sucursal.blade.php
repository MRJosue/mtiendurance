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
        Mis Empresas
        <span class="text-sm text-gray-500 ml-2" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
    </h2>   

    <!-- Contenido del panel -->
    <div x-show="abierto" x-transition>
        <!-- Acciones -->
        <div class="mb-6 flex flex-wrap gap-2">
            <button
                type="button"
                wire:click="openCreateModal"
                class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
                + Crear Empresa
            </button>
        </div>

        <!-- Filtro de búsqueda -->
        <div class="mb-4">
            <input
                type="text"
                wire:model.debounce.500ms="search"
                class="border px-4 py-2 rounded-lg w-full sm:w-1/3"
                placeholder="Buscar sucursal..."
            />
        </div>

        <!-- Listado -->
        <div class="overflow-x-auto bg-white rounded-lg shadow min-h-64 pb-8">
            <table class="min-w-full border-collapse border border-gray-200 rounded-lg">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">ID</th>
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Empresa</th>
                        <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Tipo</th>
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
                            <td class="border-b px-4 py-2 text-gray-700 text-sm">
                                <span class="px-2 py-1 rounded text-xs
                                    {{ $sucursal->tipo == 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $sucursal->tipo == 1 ? 'Principal' : 'Secundaria' }}
                                </span>
                            </td>
                            <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $sucursal->telefono }}</td>
                            <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $sucursal->direccion }}</td>
                            <td class="border-b px-4 py-2 text-gray-700 text-sm">
                                <span class="inline-block bg-blue-100 text-blue-800 rounded px-2 py-1 text-xs">
                                    {{-- Contar por campo sucursal_id --}}
                                    {{ \App\Models\User::where('sucursal_id', $sucursal->id)->count() }}
                                </span>
                                <button
                                    wire:click="openUserModal({{ $sucursal->id }})"
                                    class="ml-2 px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-700 text-xs"
                                >
                                    Editar
                                </button>
                                <button
                                    wire:click="openAssignedOnlyModal({{ $sucursal->id }})"
                                    class="ml-1 px-2 py-1 bg-indigo-500 text-white rounded hover:bg-indigo-600 text-xs"
                                    title="Ver solo asignados"
                                >
                                    Asignados
                                </button>
                            </td>

                            <td class="border-b px-4 py-2 text-gray-700 text-sm">
                                <button
                                    wire:click="openEditModal({{ $sucursal->id }})"
                                    class="px-2 py-1 bg-yellow-400 text-gray-800 rounded hover:bg-yellow-500 text-xs"
                                >
                                    Editar
                                </button>
                                <button
                                    wire:click="delete({{ $sucursal->id }})"
                                    onclick="return confirm('¿Eliminar sucursal?')"
                                    class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-700 text-xs ml-1"
                                >
                                    Eliminar
                                </button>
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

        <div class="mt-4">
            {{ $sucursales->links() }}
        </div>

        <!-- MODAL: Crear/Editar Sucursal -->
        <div
            x-data="{ open: @entangle('showSucursalModal') }"
            x-show="open"
            style="display: none"
            class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-40"
        >
            <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-3xl mx-auto">
                <h2 class="text-lg font-bold mb-4 text-gray-700">
                    {{ $editingId ? 'Editar Sucursal' : 'Crear Sucursal' }}
                </h2>

                <form wire:submit.prevent="{{ $editingId ? 'update' : 'store' }}" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block mb-1 text-gray-700">Empresa</label>
                            <select wire:model="empresa_id" class="w-full border rounded-lg px-3 py-2">
                                <option value="">Seleccione...</option>
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                                @endforeach
                            </select>
                            @error('empresa_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block mb-1 text-gray-700">Tipo</label>
                            <select wire:model="tipo" class="w-full border rounded-lg px-3 py-2">
                                <option value="1">Principal</option>
                                <option value="2">Secundaria</option>
                            </select>
                            @error('tipo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block mb-1 text-gray-700">Nombre</label>
                            <input type="text" wire:model="nombre" class="w-full border rounded-lg px-3 py-2" />
                            @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block mb-1 text-gray-700">Teléfono</label>
                            <input type="text" wire:model="telefono" class="w-full border rounded-lg px-3 py-2" />
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block mb-1 text-gray-700">Dirección</label>
                            <input type="text" wire:model="direccion" class="w-full border rounded-lg px-3 py-2" />
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 mt-4">
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ $editingId ? 'Actualizar' : 'Crear' }} Empresa
                        </button>
                        <button type="button" wire:click="closeSucursalModal"
                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Usuarios asignados / disponibles -->
        <div
            x-data="{ open: @entangle('showUserModal') }"
            x-show="open"
            style="display: none"
            class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-40"
        >
            <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-4xl mx-auto">
                <h2 class="text-lg font-bold mb-4 text-gray-700">Asignar usuarios a la Sucursal</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Columna: Asignados (por sucursal_id) -->
                    <div class="border rounded-lg">
                        <div class="px-4 py-2 border-b bg-gray-50 rounded-t-lg">
                            <span class="font-semibold text-gray-700">Usuarios asignados</span>
                        </div>
                        <div class="max-h-80 overflow-y-auto divide-y">
                            @php
                                $asignados = $selectedSucursal
                                    ? \App\Models\User::where('empresa_id', $selectedSucursal->empresa_id)
                                        ->where('sucursal_id', $selectedSucursal->id)
                                        ->orderBy('name')
                                        ->get()
                                    : collect();
                            @endphp

                            @forelse($asignados as $u)
                                <div class="flex items-center justify-between px-4 py-3">
                                    <div>
                                        <div class="text-sm font-medium text-gray-800">{{ $u->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $u->email }}</div>
                                    </div>
                                    {{-- <button
                                        wire:click="removeUserFromSucursal({{ $u->id }})"
                                        class="px-3 py-1 text-xs rounded-lg bg-red-100 text-red-700 hover:bg-red-200"
                                        title="Quitar de la sucursal"
                                    >
                                        Quitar
                                    </button> --}}
                                </div>
                            @empty
                                <div class="px-4 py-6 text-sm text-gray-500">Sin usuarios asignados.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Columna: Disponibles (misma empresa, sucursal_id = null) -->
                    <div class="border rounded-lg">
                        <div class="px-4 py-2 border-b bg-gray-50 rounded-t-lg">
                            <span class="font-semibold text-gray-700">Usuarios disponibles</span>
                        </div>
                        <div class="max-h-80 overflow-y-auto divide-y">
                            @forelse($usuariosDisponibles as $u)
                                <div class="flex items-center justify-between px-4 py-3">
                                    <div>
                                        <div class="text-sm font-medium text-gray-800">{{ $u->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $u->email }}</div>
                                    </div>
                                    <button
                                        wire:click="assignUserToSucursal({{ $u->id }})"
                                        class="px-3 py-1 text-xs rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200"
                                        title="Asignar a la sucursal"
                                    >
                                        Asignar
                                    </button>
                                </div>
                            @empty
                                <div class="px-4 py-6 text-sm text-gray-500">No hay usuarios disponibles.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 mt-6 justify-end">
                    <button
                        wire:click="closeUserModal"
                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- MODAL: Solo usuarios asignados -->
    <div
        x-data="{ open: @entangle('showAssignedOnlyModal') }"
        x-show="open"
        style="display: none"
        class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-40"
    >
        <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-3xl mx-auto">
            <h2 class="text-lg font-bold mb-4 text-gray-700">
                Usuarios asignados a: {{ $selectedSucursal->nombre ?? '' }}
            </h2>

            <div class="max-h-96 overflow-y-auto divide-y rounded border">
                @php
                    $asignadosSolo = $selectedSucursal
                        ? \App\Models\User::where('sucursal_id', $selectedSucursal->id)->orderBy('name')->get()
                        : collect();
                @endphp

                @forelse($asignadosSolo as $u)
                    <div class="flex items-center justify-between px-4 py-3">
                        <div>
                            <div class="text-sm font-medium text-gray-800">{{ $u->name }}</div>
                            <div class="text-xs text-gray-500">{{ $u->email }}</div>
                        </div>
                        <span class="text-xs px-2 py-1 rounded bg-emerald-100 text-emerald-700">Asignado</span>
                    </div>
                @empty
                    <div class="px-4 py-6 text-sm text-gray-500">Sin usuarios asignados.</div>
                @endforelse
            </div>

            <div class="flex justify-end mt-4">
                <button
                    wire:click="closeAssignedOnlyModal"
                    class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400"
                >
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Scripts encapsulados --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    window.addEventListener('notify', (e) => {
        const msg = e.detail?.message || e.detail || 'Acción realizada';
        const toast = document.createElement('div');
        toast.textContent = msg;
        toast.className = 'fixed top-4 right-4 bg-emerald-600 text-white px-4 py-2 rounded-lg shadow z-[100]';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2500);
    });
});
</script>

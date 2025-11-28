<div 
    x-data="{ abierto: true }"
    class="container mx-auto p-4 sm:p-6"
>
    <h2 class="text-2xl font-bold mb-4">Gestión de Roles y Permisos</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-3">
            {{ session('message') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div class="flex flex-wrap gap-2">
            <button
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                wire:click="nuevoRol"
            >
                Nuevo Rol
            </button>
            <button
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                wire:click="nuevoGrupo"
            >
                Nuevo Grupo
            </button>
            <button
                class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700"
                wire:click="nuevoPermiso"
            >
                Nuevo Permiso
            </button>
        </div>

        <div class="flex gap-2 w-full sm:w-auto">
            <input
                type="text"
                wire:model="query"
                placeholder="Buscar rol..."
                class="w-full sm:w-64 border border-gray-300 rounded px-3 py-2"
            >
            <button
                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
                wire:click="buscar"
            >
                Buscar
            </button>
        </div>
    </div>

    <!-- TABLA ROLES -->
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full border-collapse border border-gray-200 rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Nombre</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Tipo</th>
                    <th class="border-b px-4 py-2 text-center text-sm font-medium text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $mapTiposRol = [
                        1 => 'CLIENTE',
                        2 => 'PROVEEDOR',
                        3 => 'STAFF',
                        4 => 'ADMIN',
                    ];
                @endphp

                @foreach($rolesList as $rol)
                    <tr class="hover:bg-gray-50" wire:key="rol-row-{{ $rol->id }}">
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">
                            {{ $rol->name }}
                        </td>

                        <td class="border-b px-4 py-2 text-gray-700 text-sm whitespace-nowrap">
                            {{ $mapTiposRol[$rol->tipo ?? null] ?? '—' }}
                        </td>

                        <td class="border-b px-4 py-2">
                            <div class="flex flex-wrap gap-2 justify-center">
                                <button
                                    class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white rounded text-sm"
                                    wire:click="editarRol('{{ $rol->id }}')"
                                    wire:loading.attr="disabled"
                                >
                                    Editar
                                </button>
                                <button
                                    class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-sm"
                                    wire:click="confirmarEliminarRol('{{ $rol->id }}')"
                                    wire:loading.attr="disabled"
                                >
                                    Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $rolesList->links() }}
    </div>

    <!-- MODAL: ROL (GRANDE) -->
    @if($modalRol)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="w-[98vw] max-w-6xl max-h-[90vh] bg-white rounded-2xl shadow-xl flex flex-col">
                <div class="flex items-center justify-between border-b px-6 py-4">
                    <h3 class="text-xl font-bold">
                        {{ $role_id ? 'Editar Rol' : 'Nuevo Rol' }}
                    </h3>
                    <button
                        class="text-gray-500 hover:text-gray-700 text-2xl leading-none"
                        wire:click="$set('modalRol', false)"
                    >&times;</button>
                </div>

                <div class="flex-1 overflow-y-auto px-6 py-4 space-y-6">
                    <!-- Datos básicos del rol -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Nombre del Rol</label>
                            <input
                                type="text"
                                class="w-full rounded border-gray-300"
                                wire:model="nombreRol"
                                autocomplete="off"
                            >
                            @error('nombreRol')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Tipo</label>
                            <select class="w-full rounded border-gray-300" wire:model="tipoRol">
                                <option value="">Seleccione tipo…</option>
                                <option value="1">CLIENTE</option>
                                <option value="2">PROVEEDOR</option>
                                <option value="3">STAFF</option>
                                <option value="4">ADMIN</option>
                            </select>
                            @error('tipoRol')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Solo mostramos grupos y permisos si estamos editando un rol existente --}}
                    @if($role_id)
                        <div class="border rounded-lg">
                            <div class="px-4 py-3 bg-gray-100 border-b rounded-t-lg flex items-center justify-between">
                                <h4 class="font-semibold text-gray-800 text-sm sm:text-base">
                                    Permisos por grupo para el rol: <span class="font-bold">{{ $nombreRol }}</span>
                                </h4>
                                <span class="text-xs text-gray-500">
                                    Marca o desmarca permisos por grupo
                                </span>
                            </div>

                            <div class="p-4 space-y-4 max-h-[55vh] overflow-y-auto">
                                @foreach($grupos as $grupo)
                                    <div class="border rounded-lg" wire:key="grupo-modal-{{ $grupo->id }}">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 px-3 py-2 bg-gray-50 border-b">
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-blue-700 text-sm">
                                                    {{ $grupo->nombre }}
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    ({{ $grupo->permissions_count }} permisos)
                                                </span>
                                            </div>

                                            <div class="flex flex-wrap gap-2">
                                                <button
                                                    type="button"
                                                    class="px-2 py-1 text-xs rounded bg-emerald-600 hover:bg-emerald-700 text-white"
                                                    wire:click="syncGrupoConRol({{ $role_id }}, {{ $grupo->id }}, true)"
                                                    wire:loading.attr="disabled"
                                                >
                                                    Asignar todo
                                                </button>
                                                <button
                                                    type="button"
                                                    class="px-2 py-1 text-xs rounded bg-rose-600 hover:bg-rose-700 text-white"
                                                    wire:click="syncGrupoConRol({{ $role_id }}, {{ $grupo->id }}, false)"
                                                    wire:loading.attr="disabled"
                                                >
                                                    Quitar todo
                                                </button>

                                                <button
                                                    type="button"
                                                    class="px-2 py-1 text-xs rounded border border-gray-300 hover:bg-gray-100 text-gray-700"
                                                    wire:click="editarGrupo({{ $grupo->id }})"
                                                >
                                                    Editar grupo
                                                </button>
                                            </div>
                                        </div>

                                        <div x-data="{ open:true }" class="p-3">
                                            <button
                                                type="button"
                                                class="flex items-center justify-between w-full text-xs text-gray-600 mb-2"
                                                @click="open = !open"
                                            >
                                                <span>Ver permisos del grupo</span>
                                                <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>

                                            <div x-show="open" x-transition class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                                @forelse($grupo->permissions as $permiso)
                                                    @php
                                                        $checked = in_array($permiso->id, $role_permissions_ids ?? []);
                                                    @endphp
                                                    <div
                                                        class="flex items-center justify-between gap-2 px-2 py-1 bg-gray-50 rounded border border-gray-100"
                                                        wire:key="perm-modal-{{ $grupo->id }}-{{ $permiso->id }}"
                                                    >
                                                        <div class="flex items-center gap-2">
                                                            <input
                                                                type="checkbox"
                                                                @change="$wire.dispatch('togglePermiso', {
                                                                    role_id: {{ $role_id }},
                                                                    permiso_id: {{ $permiso->id }},
                                                                    checked: $event.target.checked
                                                                })"
                                                                {{ $checked ? 'checked' : '' }}
                                                            >
                                                            <span class="text-xs text-gray-700 font-semibold truncate">
                                                                {{ $permiso->nombre ?? $permiso->name }}
                                                            </span>
                                                            @if(!is_null($permiso->pivot?->orden))
                                                                <span class="text-[10px] text-gray-500">
                                                                    #{{ $permiso->pivot->orden }}
                                                                </span>
                                                            @endif
                                                        </div>

                                                        <div class="flex items-center gap-1 shrink-0">
                                                            <x-mini-button
                                                                rounded icon="pencil" flat gray
                                                                title="Editar permiso en grupo"
                                                                wire:click="editarPermisoDeGrupo({{ $grupo->id }}, {{ $permiso->id }})"
                                                                wire:loading.attr="disabled"
                                                            />
                                                            <x-mini-button
                                                                rounded icon="trash" flat gray interaction="negative"
                                                                title="Quitar permiso de este grupo"
                                                                wire:click="quitarPermisoDeGrupo({{ $grupo->id }}, {{ $permiso->id }})"
                                                                wire:loading.attr="disabled"
                                                            />
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="text-xs text-gray-500">
                                                        Este grupo aún no tiene permisos.
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="rounded-lg border border-dashed border-gray-300 px-4 py-3 text-sm text-gray-600 bg-gray-50">
                            Guarda el rol primero para poder asignar permisos por grupo.
                        </div>
                    @endif
                </div>

                <div class="border-t px-6 py-4 flex justify-end gap-2">
                    <button
                        class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300"
                        wire:click="$set('modalRol', false)"
                    >
                        Cerrar
                    </button>
                    <button
                        class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white"
                        wire:click="guardarRol"
                    >
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL: PERMISO -->
    @if($modalPermiso)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="w-[95vw] max-w-3xl max-h-[90vh] bg-white rounded-2xl shadow-xl flex flex-col">
                <div class="flex items-center justify-between border-b p-5">
                    <h3 class="text-xl font-bold">Permiso</h3>
                    <button class="text-gray-500 hover:text-gray-700" wire:click="$set('modalPermiso', false)">&times;</button>
                </div>
                <div class="p-5 overflow-y-auto grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Name (clave)</label>
                        <input type="text" class="w-full rounded border-gray-300" wire:model="permiso_name" placeholder="proyectos.ver">
                        @error('permiso_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Nombre visible</label>
                        <input type="text" class="w-full rounded border-gray-300" wire:model="permiso_nombre" placeholder="Ver Proyectos">
                        @error('permiso_nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Guard</label>
                        <input type="text" class="w-full rounded border-gray-300" wire:model="permiso_guard" placeholder="web">
                        @error('permiso_guard') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Orden</label>
                        <input type="number" class="w-full rounded border-gray-300" wire:model="permiso_orden" min="0">
                        @error('permiso_orden') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Tipo (nullable)</label>
                        <select class="w-full rounded border-gray-300" wire:model="permiso_type_id">
                            <option value="">— Sin tipo —</option>
                            @foreach($types as $t)
                                <option value="{{ $t->id }}">{{ $t->nombre }}</option>
                            @endforeach
                        </select>
                        @error('permiso_type_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="sm:col-span-2 border rounded p-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Asignar a grupo</label>
                                <select class="w-full rounded border-gray-300" wire:model="permiso_grupo_id">
                                    <option value="">— Ninguno —</option>
                                    @foreach($grupos as $g)
                                        <option value="{{ $g->id }}">{{ $g->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('permiso_grupo_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Orden en el grupo</label>
                                <input type="number" class="w-full rounded border-gray-300" wire:model="permiso_grupo_orden" min="0" placeholder="0">
                                @error('permiso_grupo_orden') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="border-t p-5 flex justify-between items-center">
                    @if($permiso_id)
                        <button class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white"
                            wire:click="confirmarEliminarPermiso('{{ $permiso_id }}')">
                            Eliminar
                        </button>
                    @else
                        <span></span>
                    @endif
                    <div class="flex gap-2">
                        <button class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300" wire:click="$set('modalPermiso', false)">Cancelar</button>
                        <button class="px-4 py-2 rounded bg-emerald-600 hover:bg-emerald-700 text-white" wire:click="guardarPermiso">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL: GRUPO -->
    @if($modalGrupo)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="w-[95vw] max-w-2xl max-h-[90vh] bg-white rounded-2xl shadow-xl flex flex-col">
                <div class="flex items-center justify-between border-b p-5">
                    <h3 class="text-xl font-bold">Grupo de Permisos</h3>
                    <button class="text-gray-500 hover:text-gray-700" wire:click="$set('modalGrupo', false)">&times;</button>
                </div>
                <div class="p-5 overflow-y-auto space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-sm text-gray-600 mb-1">Nombre</label>
                            <input type="text" class="w-full rounded border-gray-300" wire:model="grupo_nombre">
                            @error('grupo_nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Slug</label>
                            <input type="text" class="w-full rounded border-gray-300" wire:model="grupo_slug" placeholder="admin-basicos">
                            @error('grupo_slug') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Orden</label>
                            <input type="number" class="w-full rounded border-gray-300" wire:model="grupo_orden" min="0">
                            @error('grupo_orden') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div x-data="{ abierto: true }" class="border rounded-lg">
                        <button type="button"
                                class="w-full flex items-center justify-between px-4 py-2 bg-gray-100 rounded-t font-semibold"
                                @click="abierto = !abierto">
                            <span>Permisos del grupo (por tipo)</span>
                            <svg :class="{ 'rotate-180': abierto }" class="w-4 h-4 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="abierto" x-transition class="p-3 space-y-4 max-h-[45vh] overflow-y-auto">
                            @forelse($permisosByType as $tipoNombre => $items)
                                <div class="border rounded-lg">
                                    <div class="px-3 py-2 bg-gray-50 border-b font-semibold text-sm text-gray-700">
                                        {{ $tipoNombre }}
                                        <span class="text-xs text-gray-500 ml-2">({{ $items->count() }})</span>
                                    </div>

                                    <div class="p-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach($items as $perm)
                                            <div class="flex items-center justify-between gap-2 px-2 py-1 bg-white rounded border">
                                                <label class="flex items-center gap-2">
                                                    <input
                                                        type="checkbox"
                                                        wire:model="grupo_permisos_sel"
                                                        value="{{ $perm->id }}"
                                                    >
                                                    <span class="text-xs text-gray-700 font-semibold truncate">
                                                        {{ $perm->nombre ?? $perm->name }}
                                                    </span>
                                                </label>

                                                <input
                                                    type="number"
                                                    class="w-16 rounded border-gray-300 text-xs"
                                                    min="0"
                                                    placeholder="0"
                                                    wire:model.lazy="grupo_permisos_orden.{{ $perm->id }}"
                                                >
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="text-sm text-gray-500">No hay permisos configurados.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="border-t p-5 flex justify-between items-center">
                    @if($grupo_id)
                        <button class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white"
                            wire:click="confirmarEliminarGrupo('{{ $grupo_id }}')">
                            Eliminar
                        </button>
                    @else
                        <span></span>
                    @endif
                    <div class="flex gap-2">
                        <button class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300" wire:click="$set('modalGrupo', false)">Cancelar</button>
                        <button class="px-4 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white" wire:click="guardarGrupo">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL: CONFIRMAR ELIMINACIÓN -->
    @if($modalConfirm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="w-[95vw] max-w-lg bg-white rounded-2xl shadow-xl">
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Confirmar eliminación</h3>
                    <p class="text-gray-700">
                        ¿Seguro que deseas eliminar
                        <span class="font-semibold">{{ $confirmName }}</span>?
                        Esta acción no se puede deshacer.
                    </p>
                </div>
                <div class="border-t p-5 flex justify-end gap-2">
                    <button class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300" wire:click="cerrarConfirm">Cancelar</button>
                    @if($confirmType === 'rol')
                        <button class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white" wire:click="eliminarRol">Eliminar</button>
                    @elseif($confirmType === 'permiso')
                        <button class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white" wire:click="eliminarPermiso">Eliminar</button>
                    @elseif($confirmType === 'grupo')
                        <button class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white" wire:click="eliminarGrupo">Eliminar</button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    window.addEventListener('toast', (e) => {
        const { type, msg } = e.detail || {};
        console.log(`[${type}]`, msg);
    });
});
</script>

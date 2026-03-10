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

    {{-- MODAL: ROL --}}
    @if($modalRol)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-3 sm:p-4">
            <div class="w-[99vw] max-w-7xl max-h-[94vh] bg-white rounded-2xl shadow-xl flex flex-col">
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

                    @if($role_id)
                        <div class="border rounded-lg">
                            <div class="px-4 py-3 bg-gray-100 border-b rounded-t-lg flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <h4 class="font-semibold text-gray-800 text-sm sm:text-base">
                                    Permisos por grupo para el rol: <span class="font-bold">{{ $nombreRol }}</span>
                                </h4>
                                <span class="text-xs text-gray-500">
                                    Marca o desmarca permisos por grupo
                                </span>
                            </div>

<div
    class="p-4 space-y-4 max-h-[65vh] overflow-y-auto"
    x-data="{
        roleId: {{ (int) $role_id }},
        checkedIds: @js($role_permissions_ids ?? [])
    }"
>
    @foreach($grupos as $groupIndex => $grupo)
        <div class="border rounded-lg" wire:key="grupo-{{ $grupo->id }}">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 px-3 py-2 bg-gray-50 border-b">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="flex flex-col gap-1 shrink-0">
                        <button
                            type="button"
                            class="w-7 h-7 rounded border border-gray-300 text-xs hover:bg-gray-100"
                            wire:click="moverGrupoArriba({{ $grupo->id }})"
                            title="Subir grupo"
                        >
                            ↑
                        </button>

                        <button
                            type="button"
                            class="w-7 h-7 rounded border border-gray-300 text-xs hover:bg-gray-100"
                            wire:click="moverGrupoAbajo({{ $grupo->id }})"
                            title="Bajar grupo"
                        >
                            ↓
                        </button>
                    </div>

                    <div class="min-w-0">
                        <span class="font-semibold text-blue-700 text-sm block">{{ $grupo->nombre }}</span>
                        <span class="text-xs text-gray-500">
                            Posición: {{ $grupo->orden ?? 0 }} · ({{ $grupo->permissions_count }} permisos)
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="px-2 py-1 text-xs rounded bg-emerald-600 hover:bg-emerald-700 text-white"
                        wire:click="syncGrupoConRol({{ (int) $role_id }}, {{ $grupo->id }}, true)"
                    >
                        Asignar todo
                    </button>

                    <button
                        type="button"
                        class="px-2 py-1 text-xs rounded bg-rose-600 hover:bg-rose-700 text-white"
                        wire:click="syncGrupoConRol({{ (int) $role_id }}, {{ $grupo->id }}, false)"
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

                <div
                    x-show="open"
                    x-transition
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2"
                >
                    @if(!$grupo->permissions || $grupo->permissions->count() === 0)
                        <div class="text-xs text-gray-500">
                            Este grupo aún no tiene permisos.
                        </div>
                    @endif

                    @foreach($grupo->permissions as $permiso)
                        <div class="flex items-start justify-between gap-2 px-3 py-2 bg-gray-50 rounded border border-gray-100">
                            <div class="flex items-start gap-2 min-w-0">
                                <input
                                    type="checkbox"
                                    class="mt-1"
                                    @checked(in_array((int) $permiso->id, $role_permissions_ids ?? []))
                                    @change="$wire.dispatch('togglePermiso', {
                                        role_id: {{ (int) $role_id }},
                                        permiso_id: {{ (int) $permiso->id }},
                                        checked: $event.target.checked
                                    })"
                                >

                                <div class="min-w-0">
                                    <div class="text-xs text-gray-800 font-semibold break-words">
                                        {{ $permiso->nombre ?? $permiso->name }}
                                    </div>

                                    <div class="text-[11px] text-gray-500 break-all">
                                        {{ $permiso->name }}
                                    </div>

                                    @if(optional($permiso->pivot)->orden !== null)
                                        <div class="text-[10px] text-gray-400 mt-1">
                                            Orden grupo: #{{ $permiso->pivot->orden }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col items-center gap-1 shrink-0">
                                <button
                                    type="button"
                                    class="w-7 h-7 rounded border border-gray-300 text-xs hover:bg-gray-100"
                                    title="Editar permiso en grupo"
                                    wire:click="editarPermisoDeGrupo({{ $grupo->id }}, {{ $permiso->id }})"
                                >
                                    ✎
                                </button>

                                <button
                                    type="button"
                                    class="w-7 h-7 rounded border border-red-300 text-xs text-red-600 hover:bg-red-50"
                                    title="Quitar permiso de este grupo"
                                    wire:click="quitarPermisoDeGrupo({{ $grupo->id }}, {{ $permiso->id }})"
                                >
                                    🗑
                                </button>
                            </div>
                        </div>
                    @endforeach
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

    {{-- MODAL: PERMISO --}}
    @if($modalPermiso)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-3 sm:p-4">
            <div class="w-[99vw] max-w-5xl max-h-[94vh] bg-white rounded-2xl shadow-xl flex flex-col">
                <div class="flex items-center justify-between border-b p-5">
                    <h3 class="text-xl font-bold">Permiso</h3>
                    <button class="text-gray-500 hover:text-gray-700 text-2xl leading-none" wire:click="$set('modalPermiso', false)">&times;</button>
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

    {{-- MODAL: GRUPO --}}
    @if($modalGrupo)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-3 sm:p-4">
            <div class="w-[99vw] max-w-6xl max-h-[94vh] bg-white rounded-2xl shadow-xl flex flex-col">
                <div class="flex items-center justify-between border-b p-5">
                    <h3 class="text-xl font-bold">Grupo de Permisos</h3>
                    <button class="text-gray-500 hover:text-gray-700 text-2xl leading-none" wire:click="$set('modalGrupo', false)">&times;</button>
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
                        <button
                            type="button"
                            class="w-full flex items-center justify-between px-4 py-3 bg-gray-100 rounded-t font-semibold"
                            @click="abierto = !abierto"
                        >
                            <span>Permisos del grupo (por tipo)</span>
                            <svg :class="{ 'rotate-180': abierto }" class="w-4 h-4 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="abierto" x-transition class="p-3 space-y-4 max-h-[62vh] overflow-y-auto">
                            @forelse($permisosByType as $tipoNombre => $items)
                                <div class="border rounded-lg">
                                    <div class="px-3 py-2 bg-gray-50 border-b font-semibold text-sm text-gray-700">
                                        {{ $tipoNombre }}
                                        <span class="text-xs text-gray-500 ml-2">({{ $items->count() }})</span>
                                    </div>

                                    <div class="p-3 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                                        @foreach($items as $perm)
                                            <div class="flex items-start justify-between gap-3 px-3 py-3 bg-white rounded border">
                                                <label class="flex items-start gap-3 min-w-0 flex-1 cursor-pointer">
                                                    <input
                                                        type="checkbox"
                                                        class="mt-1"
                                                        wire:model="grupo_permisos_sel"
                                                        value="{{ $perm->id }}"
                                                    >

                                                    <div class="min-w-0">
                                                        <div class="text-sm text-gray-800 font-semibold break-words">
                                                            {{ $perm->nombre ?? $perm->name }}
                                                        </div>
                                                        <div class="text-xs text-gray-500 break-all mt-1">
                                                            {{ $perm->name }}
                                                        </div>
                                                    </div>
                                                </label>

                                                <div class="w-20 shrink-0">
                                                    <label class="block text-[11px] text-gray-500 mb-1">Orden</label>
                                                    <input
                                                        type="number"
                                                        class="w-full rounded border-gray-300 text-xs"
                                                        min="0"
                                                        placeholder="0"
                                                        wire:model.lazy="grupo_permisos_orden.{{ $perm->id }}"
                                                    >
                                                </div>
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

    {{-- MODAL: CONFIRM --}}
    @if($modalConfirm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-3 sm:p-4">
            <div class="w-[99vw] max-w-2xl bg-white rounded-2xl shadow-xl">
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
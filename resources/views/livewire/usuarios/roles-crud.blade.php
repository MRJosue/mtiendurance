<div 
    x-data="{ abierto: true }"
    class="container mx-auto p-4 sm:p-6 text-gray-900 dark:text-gray-100"
>
    <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-gray-100">Gestión de Roles y Permisos</h2>

    @if (session()->has('message'))
        <div class="mb-3 rounded bg-green-100 p-3 text-green-800 dark:bg-green-900/40 dark:text-green-200">
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
                class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:border-blue-400 dark:focus:ring-blue-400 sm:w-64"
            >
            <button
                class="rounded-lg bg-gray-600 px-4 py-2 text-white hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600"
                wire:click="buscar"
            >
                Buscar
            </button>
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-900">
        <table class="min-w-full border-collapse rounded-lg">
            <thead class="bg-gray-100 dark:bg-gray-800">
                <tr>
                    <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Nombre</th>
                    <th class="border-b border-gray-200 px-4 py-2 text-left text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Tipo</th>
                    <th class="border-b border-gray-200 px-4 py-2 text-center text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Acciones</th>
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
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/70" wire:key="rol-row-{{ $rol->id }}">
                        <td class="border-b border-gray-200 px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">
                            {{ $rol->name }}
                        </td>

                        <td class="border-b border-gray-200 px-4 py-2 text-sm text-gray-700 whitespace-nowrap dark:border-gray-700 dark:text-gray-200">
                            {{ $mapTiposRol[$rol->tipo ?? null] ?? '—' }}
                        </td>

                        <td class="border-b border-gray-200 px-4 py-2 dark:border-gray-700">
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
            <div class="flex max-h-[94vh] w-[99vw] max-w-7xl flex-col rounded-2xl bg-white shadow-xl dark:bg-gray-900 dark:ring-1 dark:ring-white/10">
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $role_id ? 'Editar Rol' : 'Nuevo Rol' }}
                    </h3>
                    <button
                        class="text-2xl leading-none text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                        wire:click="$set('modalRol', false)"
                    >&times;</button>
                </div>

                <div class="flex-1 overflow-y-auto px-6 py-4 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm text-gray-600 dark:text-gray-300">Nombre del Rol</label>
                            <input
                                type="text"
                                class="w-full rounded border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400"
                                wire:model="nombreRol"
                                autocomplete="off"
                            >
                            @error('nombreRol')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm text-gray-600 dark:text-gray-300">Tipo</label>
                            <select class="w-full rounded border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400" wire:model="tipoRol">
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
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="flex flex-col gap-2 rounded-t-lg border-b border-gray-200 bg-gray-100 px-4 py-3 dark:border-gray-700 dark:bg-gray-800 sm:flex-row sm:items-center sm:justify-between">
                                <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-100 sm:text-base">
                                    Permisos por grupo para el rol: <span class="font-bold">{{ $nombreRol }}</span>
                                </h4>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    Marca o desmarca permisos por grupo
                                </span>
                            </div>

<div
    class="max-h-[65vh] space-y-4 overflow-y-auto p-4"
    x-data="{
        roleId: {{ (int) $role_id }},
        checkedIds: @js($role_permissions_ids ?? [])
    }"
>
    @foreach($grupos as $groupIndex => $grupo)
        <div class="rounded-lg border border-gray-200 dark:border-gray-700" wire:key="grupo-{{ $grupo->id }}">
            <div class="flex flex-col gap-2 border-b border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-gray-800/60 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="flex flex-col gap-1 shrink-0">
                        <button
                            type="button"
                            class="h-7 w-7 rounded border border-gray-300 text-xs hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                            wire:click="moverGrupoArriba({{ $grupo->id }})"
                            title="Subir grupo"
                        >
                            ↑
                        </button>

                        <button
                            type="button"
                            class="h-7 w-7 rounded border border-gray-300 text-xs hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                            wire:click="moverGrupoAbajo({{ $grupo->id }})"
                            title="Bajar grupo"
                        >
                            ↓
                        </button>
                    </div>

                    <div class="min-w-0">
                        <span class="block text-sm font-semibold text-blue-700 dark:text-blue-400">{{ $grupo->nombre }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
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
                        class="rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                        wire:click="editarGrupo({{ $grupo->id }})"
                    >
                        Editar grupo
                    </button>
                </div>
            </div>

            <div x-data="{ open:true }" class="p-3">
                <button
                    type="button"
                    class="mb-2 flex w-full items-center justify-between text-xs text-gray-600 dark:text-gray-300"
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
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            Este grupo aún no tiene permisos.
                        </div>
                    @endif

                    @foreach($grupo->permissions as $permiso)
                        <div class="flex items-start justify-between gap-2 rounded border border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-gray-800/50">
                            <div class="flex items-start gap-2 min-w-0">
                                <input
                                    type="checkbox"
                                    class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400"
                                    @checked(in_array((int) $permiso->id, $role_permissions_ids ?? []))
                                    @change="$wire.dispatch('togglePermiso', {
                                        role_id: {{ (int) $role_id }},
                                        permiso_id: {{ (int) $permiso->id }},
                                        checked: $event.target.checked
                                    })"
                                >

                                <div class="min-w-0">
                                    <div class="break-words text-xs font-semibold text-gray-800 dark:text-gray-100">
                                        {{ $permiso->nombre ?? $permiso->name }}
                                    </div>

                                    <div class="break-all text-[11px] text-gray-500 dark:text-gray-400">
                                        {{ $permiso->name }}
                                    </div>

                                    @if(optional($permiso->pivot)->orden !== null)
                                        <div class="mt-1 text-[10px] text-gray-400 dark:text-gray-500">
                                            Orden grupo: #{{ $permiso->pivot->orden }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col items-center gap-1 shrink-0">
                                <button
                                    type="button"
                                    class="h-7 w-7 rounded border border-gray-300 text-xs hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
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
                        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:border-gray-600 dark:bg-gray-800/50 dark:text-gray-300">
                            Guarda el rol primero para poder asignar permisos por grupo.
                        </div>
                    @endif
                </div>

                <div class="flex justify-end gap-2 border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                    <button
                        class="rounded bg-gray-200 px-4 py-2 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
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
            <div class="flex max-h-[94vh] w-[99vw] max-w-5xl flex-col rounded-2xl bg-white shadow-xl dark:bg-gray-900 dark:ring-1 dark:ring-white/10">
                <div class="flex items-center justify-between border-b border-gray-200 p-5 dark:border-gray-700">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Permiso</h3>
                    <button class="text-2xl leading-none text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" wire:click="$set('modalPermiso', false)">&times;</button>
                </div>

                <div class="p-5 overflow-y-auto grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-sm text-gray-600 dark:text-gray-300">Name (clave)</label>
                        <input type="text" class="w-full rounded border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400" wire:model="permiso_name" placeholder="proyectos.ver">
                        @error('permiso_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm text-gray-600 dark:text-gray-300">Nombre visible</label>
                        <input type="text" class="w-full rounded border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400" wire:model="permiso_nombre" placeholder="Ver Proyectos">
                        @error('permiso_nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm text-gray-600 dark:text-gray-300">Guard</label>
                        <input type="text" class="w-full rounded border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400" wire:model="permiso_guard" placeholder="web">
                        @error('permiso_guard') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm text-gray-600 dark:text-gray-300">Orden</label>
                        <input type="number" class="w-full rounded border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400" wire:model="permiso_orden" min="0">
                        @error('permiso_orden') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm text-gray-600 dark:text-gray-300">Tipo (nullable)</label>
                        <select class="w-full rounded border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400" wire:model="permiso_type_id">
                            <option value="">— Sin tipo —</option>
                            @foreach($types as $t)
                                <option value="{{ $t->id }}">{{ $t->nombre }}</option>
                            @endforeach
                        </select>
                        @error('permiso_type_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="rounded border border-gray-200 p-3 dark:border-gray-700 sm:col-span-2">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block text-sm text-gray-600 dark:text-gray-300">Asignar a grupo</label>
                                <select class="w-full rounded border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400" wire:model="permiso_grupo_id">
                                    <option value="">— Ninguno —</option>
                                    @foreach($grupos as $g)
                                        <option value="{{ $g->id }}">{{ $g->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('permiso_grupo_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="mb-1 block text-sm text-gray-600 dark:text-gray-300">Orden en el grupo</label>
                                <input type="number" class="w-full rounded border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400" wire:model="permiso_grupo_orden" min="0" placeholder="0">
                                @error('permiso_grupo_orden') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between border-t border-gray-200 p-5 dark:border-gray-700">
                    @if($permiso_id)
                        <button class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white"
                            wire:click="confirmarEliminarPermiso('{{ $permiso_id }}')">
                            Eliminar
                        </button>
                    @else
                        <span></span>
                    @endif

                    <div class="flex gap-2">
                        <button class="rounded bg-gray-200 px-4 py-2 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600" wire:click="$set('modalPermiso', false)">Cancelar</button>
                        <button class="px-4 py-2 rounded bg-emerald-600 hover:bg-emerald-700 text-white" wire:click="guardarPermiso">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL: GRUPO --}}
    @if($modalGrupo)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-3 sm:p-4">
            <div class="flex max-h-[94vh] w-[99vw] max-w-6xl flex-col rounded-2xl bg-white shadow-xl dark:bg-gray-900 dark:ring-1 dark:ring-white/10">
                <div class="flex items-center justify-between border-b border-gray-200 p-5 dark:border-gray-700">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Grupo de Permisos</h3>
                    <button class="text-2xl leading-none text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" wire:click="$set('modalGrupo', false)">&times;</button>
                </div>

                <div class="p-5 overflow-y-auto space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm text-gray-600 dark:text-gray-300">Nombre</label>
                            <input type="text" class="w-full rounded border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400" wire:model="grupo_nombre">
                            @error('grupo_nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm text-gray-600 dark:text-gray-300">Slug</label>
                            <input type="text" class="w-full rounded border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400" wire:model="grupo_slug" placeholder="admin-basicos">
                            @error('grupo_slug') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm text-gray-600 dark:text-gray-300">Orden</label>
                            <input type="number" class="w-full rounded border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400" wire:model="grupo_orden" min="0">
                            @error('grupo_orden') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div x-data="{ abierto: true }" class="rounded-lg border border-gray-200 dark:border-gray-700">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded-t bg-gray-100 px-4 py-3 font-semibold text-gray-800 dark:bg-gray-800 dark:text-gray-100"
                            @click="abierto = !abierto"
                        >
                            <span>Permisos del grupo (por tipo)</span>
                            <svg :class="{ 'rotate-180': abierto }" class="w-4 h-4 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="abierto" x-transition class="max-h-[62vh] space-y-4 overflow-y-auto p-3">
                            @forelse($permisosByType as $tipoNombre => $items)
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div class="border-b border-gray-200 bg-gray-50 px-3 py-2 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-200">
                                        {{ $tipoNombre }}
                                        <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">({{ $items->count() }})</span>
                                    </div>

                                    <div class="p-3 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                                        @foreach($items as $perm)
                                            <div class="flex items-start justify-between gap-3 rounded border border-gray-200 bg-white px-3 py-3 dark:border-gray-700 dark:bg-gray-800/50">
                                                <label class="flex items-start gap-3 min-w-0 flex-1 cursor-pointer">
                                                    <input
                                                        type="checkbox"
                                                        class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400"
                                                        wire:model="grupo_permisos_sel"
                                                        value="{{ $perm->id }}"
                                                    >

                                                    <div class="min-w-0">
                                                        <div class="break-words text-sm font-semibold text-gray-800 dark:text-gray-100">
                                                            {{ $perm->nombre ?? $perm->name }}
                                                        </div>
                                                        <div class="mt-1 break-all text-xs text-gray-500 dark:text-gray-400">
                                                            {{ $perm->name }}
                                                        </div>
                                                    </div>
                                                </label>

                                                <div class="w-20 shrink-0">
                                                    <label class="mb-1 block text-[11px] text-gray-500 dark:text-gray-400">Orden</label>
                                                    <input
                                                        type="number"
                                                        class="w-full rounded border-gray-300 bg-white text-xs text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400"
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
                                <div class="text-sm text-gray-500 dark:text-gray-400">No hay permisos configurados.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between border-t border-gray-200 p-5 dark:border-gray-700">
                    @if($grupo_id)
                        <button class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white"
                            wire:click="confirmarEliminarGrupo('{{ $grupo_id }}')">
                            Eliminar
                        </button>
                    @else
                        <span></span>
                    @endif

                    <div class="flex gap-2">
                        <button class="rounded bg-gray-200 px-4 py-2 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600" wire:click="$set('modalGrupo', false)">Cancelar</button>
                        <button class="px-4 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white" wire:click="guardarGrupo">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL: CONFIRM --}}
    @if($modalConfirm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-3 sm:p-4">
            <div class="w-[99vw] max-w-2xl rounded-2xl bg-white shadow-xl dark:bg-gray-900 dark:ring-1 dark:ring-white/10">
                <div class="p-6">
                    <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-gray-100">Confirmar eliminación</h3>
                    <p class="text-gray-700 dark:text-gray-300">
                        ¿Seguro que deseas eliminar
                        <span class="font-semibold">{{ $confirmName }}</span>?
                        Esta acción no se puede deshacer.
                    </p>
                </div>
                <div class="flex justify-end gap-2 border-t border-gray-200 p-5 dark:border-gray-700">
                    <button class="rounded bg-gray-200 px-4 py-2 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600" wire:click="cerrarConfirm">Cancelar</button>

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

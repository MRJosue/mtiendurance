<div 
    x-data="{
        abierto: JSON.parse(localStorage.getItem('usuarios_tabla_abierto') ?? 'true'),
        toggle() {
            this.abierto = !this.abierto;
            localStorage.setItem('usuarios_tabla_abierto', JSON.stringify(this.abierto));
        }
    }"
   class="p-2 sm:p-3 h-full min-h-0 flex flex-col"
>
    <h2 
        @click="toggle()"
        class="text-2xl font-bold mb-4 border-b border-gray-300 pb-2 cursor-pointer hover:text-blue-600 transition text-center md:text-left"
    >
        Usuarios
        <span class="text-sm text-gray-500 ml-2" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
    </h2>

    <div x-show="abierto" x-transition>
        @if (session()->has('message'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-3">
                {{ session('message') }}
            </div>
        @endif

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 gap-2">
            @if($isPrivileged)
                <button wire:click="crear" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
                    Nuevo Usuario
                </button>
            @else
                <div></div>
            @endif

            <div class="flex flex-wrap gap-2">
                <input
                    type="text"
                    wire:model.debounce.500ms="search"
                    placeholder="Buscar por nombre o correo..."
                    class="border border-gray-300 rounded px-4 py-2 w-full sm:w-64"
                >
                <button wire:click="$refresh" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded">
                    Buscar
                </button>
            </div>
        </div>

        @php
            $arrow = function(string $field) use ($sortField, $sortDir) {
                if ($sortField !== $field) return '⇅';
                return $sortDir === 'asc' ? '▲' : '▼';
            };
        @endphp

        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full border-collapse border border-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        {{-- ID (ordenable) --}}
                        <th class="border border-gray-300 px-4 py-2 text-left font-semibold whitespace-nowrap">
                            <button class="inline-flex items-center gap-1 hover:text-blue-600" wire:click="sortBy('id')">
                                <span>ID</span>
                                <span class="text-xs">{{ $arrow('id') }}</span>
                            </button>
                        </th>

                        <th class="border border-gray-300 px-4 py-2 text-left font-semibold">Nombre</th>
                        <th class="border border-gray-300 px-4 py-2 text-left font-semibold">Correo Electrónico</th>
                        <th class="border border-gray-300 px-4 py-2 text-left font-semibold">Roles</th>
                        <th class="border border-gray-300 px-4 py-2 text-left font-semibold">Empresa</th>
                        <th class="border border-gray-300 px-4 py-2 text-left font-semibold">Organizacion</th>
                        <th class="border border-gray-300 px-4 py-2 text-center font-semibold">Acciones</th>
                    </tr>

                    {{-- Fila de filtros por columna --}}
                    <tr class="border-t border-gray-200">
                        {{-- Filtro ID --}}
                        <th class="border border-gray-300 px-2 py-2">
                            <div x-data="{ open:false }" class="relative inline-flex items-center">
                                <button @click="open = !open" class="px-2 py-1 rounded hover:bg-gray-200 text-sm" title="Filtrar ID">⋮</button>
                                <div x-cloak x-show="open" @click.away="open=false" x-transition class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3">
                                    <label class="block text-xs text-gray-600 mb-1">ID (uno o varios separados por coma)</label>
                                    <input
                                        type="text"
                                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                        placeholder="Ej. 1 o 1,5,8"
                                        wire:model.live.debounce.300ms="filters.id"
                                    />
                                    <div class="mt-2 flex justify-end gap-2">
                                        <button type="button" class="px-2 py-1 text-xs rounded border" @click="$wire.set('filters.id','')">Limpiar</button>
                                        <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </th>

                        {{-- Filtro Nombre --}}
                        <th class="border border-gray-300 px-2 py-2">
                            <div x-data="{ open:false }" class="relative inline-flex items-center">
                                <button @click="open = !open" class="px-2 py-1 rounded hover:bg-gray-200 text-sm" title="Filtrar Nombre">⋮</button>
                                <div x-cloak x-show="open" @click.away="open=false" x-transition class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3">
                                    <label class="block text-xs text-gray-600 mb-1">Nombre</label>
                                    <input
                                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                        placeholder="Nombre…"
                                        wire:model.live.debounce.300ms="filters.name"
                                    >
                                    <div class="mt-2 flex justify-end gap-2">
                                        <button type="button" class="px-2 py-1 text-xs rounded border" @click="$wire.set('filters.name','')">Limpiar</button>
                                        <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </th>

                        {{-- Filtro Email --}}
                        <th class="border border-gray-300 px-2 py-2">
                            <div x-data="{ open:false }" class="relative inline-flex items-center">
                                <button @click="open = !open" class="px-2 py-1 rounded hover:bg-gray-200 text-sm" title="Filtrar Correo">⋮</button>
                                <div x-cloak x-show="open" @click.away="open=false" x-transition class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3">
                                    <label class="block text-xs text-gray-600 mb-1">Correo</label>
                                    <input
                                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                        placeholder="correo@…"
                                        wire:model.live.debounce.300ms="filters.email"
                                    >
                                    <div class="mt-2 flex justify-end gap-2">
                                        <button type="button" class="px-2 py-1 text-xs rounded border" @click="$wire.set('filters.email','')">Limpiar</button>
                                        <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </th>

                        {{-- Filtro Rol --}}
                        <th class="border border-gray-300 px-2 py-2">
                            <div x-data="{ open:false }" class="relative inline-flex items-center">
                                <button @click="open = !open" class="px-2 py-1 rounded hover:bg-gray-200 text-sm" title="Filtrar Rol">⋮</button>
                                <div x-cloak x-show="open" @click.away="open=false" x-transition class="absolute z-50 mt-1 w-56 rounded-lg border bg-white shadow p-3">
                                    <label class="block text-xs text-gray-600 mb-1">Rol</label>
                                    <select
                                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                        wire:model.live="filters.role"
                                    >
                                        <option value="">— Cualquiera —</option>
                                        @foreach($rolesListado as $r)
                                            <option value="{{ $r->name }}">{{ $r->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="mt-2 flex justify-end gap-2">
                                        <button type="button" class="px-2 py-1 text-xs rounded border" @click="$wire.set('filters.role','')">Limpiar</button>
                                        <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </th>

        {{-- ← NUEVO: Filtro Sucursal --}}
        <th class="border border-gray-300 px-2 py-2">
            <div x-data="{ open:false }" class="relative inline-flex items-center">
                <button @click="open = !open" class="px-2 py-1 rounded hover:bg-gray-200 text-sm" title="Filtrar Sucursal">⋮</button>
                <div x-cloak x-show="open" @click.away="open=false" x-transition class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3">
                    <label class="block text-xs text-gray-600 mb-1">Sucursal (nombre)</label>
                    <input
                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                        placeholder="Sucursal…"
                        wire:model.live.debounce.300ms="filters.sucursal"
                    >
                    <div class="mt-2 flex justify-end gap-2">

                        
                        <button type="button" class="px-2 py-1 text-xs rounded border" @click="$wire.set('filters.sucursal','')">Limpiar</button>
                        <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">Cerrar</button>
                    </div>
                </div>
            </div>
        </th>

        {{-- ← NUEVO: Filtro Empresa --}}
        <th class="border border-gray-300 px-2 py-2">
            <div x-data="{ open:false }" class="relative inline-flex items-center">
                <button @click="open = !open" class="px-2 py-1 rounded hover:bg-gray-200 text-sm" title="Filtrar Empresa">⋮</button>
                <div x-cloak x-show="open" @click.away="open=false" x-transition class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3">
                    <label class="block text-xs text-gray-600 mb-1">Empresa (nombre)</label>
                    <input
                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                        placeholder="Empresa…"
                        wire:model.live.debounce.300ms="filters.empresa"
                    >
                    <div class="mt-2 flex justify-end gap-2">
                        <button type="button" class="px-2 py-1 text-xs rounded border" @click="$wire.set('filters.empresa','')">Limpiar</button>
                        <button type="button" class="px-2 py-1 text-xs rounded border" @click="open=false">Cerrar</button>
                    </div>
                </div>
            </div>
        </th>

                        {{-- Acciones (sin filtro) --}}
                        <th class="border border-gray-300 px-2 py-2"></th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($usuarios as $usuario)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-4 py-2 whitespace-nowrap font-semibold">{{ $usuario->id }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ $usuario->name }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ $usuario->email }}</td>
                            <td class="border border-gray-300 px-4 py-2">
                                @foreach($usuario->roles as $rol)
                                    <span class="inline-block bg-gray-200 text-gray-800 text-xs font-semibold mr-1 mb-1 px-2 py-1 rounded">
                                        {{ $rol->name }}
                                    </span>
                                @endforeach
                            </td>

                            <td class="border border-gray-300 px-4 py-2">
                                {{ $usuario->sucursal_nombre ?? '—' }}
                            </td>

                            {{-- ← NUEVO: Empresa (empresa directa o la de la sucursal) --}}
                            <td class="border border-gray-300 px-4 py-2">
                                {{ $usuario->empresa_principal_nombre ?? '—' }}
                            </td>


                            <td class="border border-gray-300 px-4 py-2 text-center space-x-2">


                                        <x-dropdown>
                                                <x-dropdown.item
                                                    
                                                    :href="route('usuarios.show', $usuario->id)"
                                                    label="Ver detalles"
                                                />
                                                @if($isPrivileged)
                                                
                                                    <x-dropdown.item separator>
                                                        <b wire:click="editarRoles({{ $usuario->id }})" >Editar Rol</b>
                                                    </x-dropdown.item>
                                                 
                                                @endif
                                        </x-dropdown>


                                {{-- <a
                                    href="{{ route('usuarios.show', $usuario->id) }}"
                                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded inline-block"
                                >
                                    Detalles
                                </a>

                                @if($isPrivileged)
                                    <button
                                        wire:click="editarRoles({{ $usuario->id }})"
                                        class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-4 py-2 rounded inline-block"
                                    >
                                        Roles
                                    </button> --}}
                                {{-- @endif --}}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-gray-500 py-4">No se encontraron usuarios.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $usuarios->links() }}
        </div>

        {{-- Modal de roles (igual que tenías) --}}
        @if($modal && $isPrivileged)
            <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                <div class="bg-white rounded shadow-lg w-full max-w-md">
                    <div class="flex items-center justify-between border-b border-gray-200 p-4">
                        <h5 class="text-xl font-bold">Asignar Roles al Usuario</h5>
                        <button class="text-gray-500 hover:text-gray-700" wire:click="cerrarModal">&times;</button>
                    </div>
                    <div class="overflow-y-auto p-4 space-y-4 flex-1">
                        <div class="border border-gray-200 rounded">
                            <div class="px-4 py-2 bg-gray-100 font-semibold text-gray-700 rounded-t">
                                Roles Disponibles
                            </div>
                                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-60 overflow-y-auto">
                                    @foreach($roles as $rol)
                                        <label class="flex items-center space-x-2">
                                            <input
                                                type="radio"
                                                wire:model="rolSeleccionado"
                                                value="{{ $rol->id }}"
                                                class="text-blue-600"
                                            >
                                            <span class="text-gray-700">{{ $rol->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-end border-t border-gray-200 p-4 space-x-2">
                        <button wire:click="cerrarModal" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-4 py-2 rounded">
                            Cancelar
                        </button>
                        <button wire:click="guardarRoles" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Encapsular cualquier script extra en DOMContentLoaded si llegas a añadirlo
<script>
document.addEventListener('DOMContentLoaded', () => {
    // tus scripts
});
</script>
--}}

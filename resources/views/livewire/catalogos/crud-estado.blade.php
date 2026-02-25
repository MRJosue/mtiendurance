<div class="container mx-auto p-6">

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Administrar Estados</h1>

        <div class="flex items-center gap-2">
            <select
                wire:model.live="perPage"
                class="rounded-lg border-gray-300 text-sm"
            >
                @foreach($perPageOptions as $opt)
                    <option value="{{ $opt }}">{{ $opt }} / pág</option>
                @endforeach
            </select>

            <button
                wire:click="openCreate"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
                + Nuevo Estado
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800">
            {{ session('message') }}
        </div>
    @endif

    {{-- TABLA --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full border-collapse border border-gray-200 rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">ID</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Nombre</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">País</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Tipos de envío</th>
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Acciones</th>
                </tr>

                {{-- FILTROS POR COLUMNA (excepto tipos de envío) --}}
                <tr class="border-t border-gray-200">
                    {{-- ID --}}
                    <th class="px-4 py-2">
                        <div x-data="{ open:false }" class="relative inline-flex items-center">
                            <button
                                type="button"
                                @click="open = !open"
                                class="px-2 py-1 rounded hover:bg-gray-200 text-sm"
                                title="Filtrar ID"
                            >⋮</button>

                            <div
                                x-cloak
                                x-show="open"
                                @click.away="open=false"
                                x-transition
                                class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3"
                            >
                                <label class="block text-xs text-gray-600 mb-1">ID(s)</label>
                                <input
                                    type="text"
                                    class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                    placeholder="Ej. 5 o 5,6,7"
                                    wire:model.live.debounce.400ms="filters.id"
                                />

                                <div class="mt-2 flex justify-end gap-2">
                                    <button
                                        type="button"
                                        class="px-2 py-1 text-xs rounded border"
                                        @click="$wire.set('filters.id', null)"
                                    >Limpiar</button>

                                    <button
                                        type="button"
                                        class="px-2 py-1 text-xs rounded border"
                                        @click="open=false"
                                    >Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </th>

                    {{-- Nombre --}}
                    <th class="px-4 py-2">
                        <div x-data="{ open:false }" class="relative inline-flex items-center">
                            <button
                                type="button"
                                @click="open = !open"
                                class="px-2 py-1 rounded hover:bg-gray-200 text-sm"
                                title="Filtrar Nombre"
                            >⋮</button>

                            <div
                                x-cloak
                                x-show="open"
                                @click.away="open=false"
                                x-transition
                                class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3"
                            >
                                <label class="block text-xs text-gray-600 mb-1">Nombre</label>
                                <input
                                    type="text"
                                    class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                    placeholder="Buscar…"
                                    wire:model.live.debounce.400ms="filters.nombre"
                                />

                                <div class="mt-2 flex justify-end gap-2">
                                    <button
                                        type="button"
                                        class="px-2 py-1 text-xs rounded border"
                                        @click="$wire.set('filters.nombre', null)"
                                    >Limpiar</button>

                                    <button
                                        type="button"
                                        class="px-2 py-1 text-xs rounded border"
                                        @click="open=false"
                                    >Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </th>

                    {{-- País --}}
                    <th class="px-4 py-2">
                        <div x-data="{ open:false }" class="relative inline-flex items-center">
                            <button
                                type="button"
                                @click="open = !open"
                                class="px-2 py-1 rounded hover:bg-gray-200 text-sm"
                                title="Filtrar País"
                            >⋮</button>

                            <div
                                x-cloak
                                x-show="open"
                                @click.away="open=false"
                                x-transition
                                class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3"
                            >
                                <label class="block text-xs text-gray-600 mb-1">País</label>
                                <select
                                    class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                    wire:model.live="filters.pais_id"
                                >
                                    <option value="">— Cualquiera —</option>
                                    @foreach($paises as $pais)
                                        <option value="{{ $pais->id }}">{{ $pais->nombre }}</option>
                                    @endforeach
                                </select>

                                <div class="mt-2 flex justify-end gap-2">
                                    <button
                                        type="button"
                                        class="px-2 py-1 text-xs rounded border"
                                        @click="$wire.set('filters.pais_id', null)"
                                    >Limpiar</button>

                                    <button
                                        type="button"
                                        class="px-2 py-1 text-xs rounded border"
                                        @click="open=false"
                                    >Cerrar</button>
                                </div>

                                <div class="mt-2 pt-2 border-t flex justify-end">
                                    <button
                                        type="button"
                                        class="px-2 py-1 text-xs rounded border"
                                        wire:click="clearFilters"
                                    >
                                        Limpiar todos
                                    </button>
                                </div>
                            </div>
                        </div>
                    </th>

                    {{-- Tipos de envío (SIN filtro) --}}
                    <th class="px-4 py-2"></th>

                    {{-- Acciones (SIN filtro) --}}
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>

            <tbody>
                @forelse($estados as $estado)
                    <tr class="hover:bg-gray-50">
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $estado->id }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm font-semibold">{{ $estado->nombre }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $estado->pais->nombre ?? '—' }}</td>

                        <td class="border-b px-4 py-2 text-gray-700 text-sm">
                            <div class="flex flex-wrap gap-1">
                                @forelse(($estado->tipoEnvios ?? []) as $te)
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-gray-200 text-gray-700">
                                        {{ $te->nombre }}
                                    </span>
                                @empty
                                    <span class="text-xs text-gray-400">Sin tipos</span>
                                @endforelse
                            </div>
                        </td>

                        <td class="border-b px-4 py-2 text-gray-700 text-sm">
                            <button
                                wire:click="openEdit({{ $estado->id }})"
                                class="text-blue-600 hover:underline"
                            >
                                Editar
                            </button>

                            <button
                                wire:click="delete({{ $estado->id }})"
                                class="text-red-600 hover:underline ml-4"
                                onclick="return confirm('¿Eliminar este estado?')"
                            >
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
                            No hay estados para mostrar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $estados->links() }}
    </div>

    {{-- MODAL --}}
    @if($showModal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white p-6 rounded-xl shadow-lg w-full max-w-2xl relative overflow-y-auto max-h-[90vh]">

                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold">
                        {{ $isEditMode ? 'Editar Estado' : 'Crear Estado' }}
                    </h2>

                    <button
                        wire:click="closeModal"
                        class="text-gray-500 hover:text-red-600 text-2xl leading-none"
                        title="Cerrar"
                    >&times;</button>
                </div>

                <form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}" class="space-y-4">

                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre del Estado</label>
                        <input
                            type="text"
                            id="nombre"
                            wire:model.defer="nombre"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        @error('nombre') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label for="pais_id" class="block text-sm font-medium text-gray-700">País</label>
                        <select
                            id="pais_id"
                            wire:model.defer="pais_id"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">Seleccionar País</option>
                            @foreach($paises as $pais)
                                <option value="{{ $pais->id }}">{{ $pais->nombre }}</option>
                            @endforeach
                        </select>
                        @error('pais_id') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    {{-- CHECKBOXES TIPOS DE ENVÍO --}}
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-semibold text-gray-700">Tipos de envío disponibles</h3>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    class="text-xs px-2 py-1 rounded border hover:bg-white"
                                    wire:click="$set('selectedTiposEnvio', [])"
                                >
                                    Limpiar
                                </button>

                                <button
                                    type="button"
                                    class="text-xs px-2 py-1 rounded border hover:bg-white"
                                    wire:click="$set('selectedTiposEnvio', {{ $tiposEnvio->pluck('id')->values() }})"
                                >
                                    Seleccionar todos
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($tiposEnvio as $tipo)
                                <label class="flex items-center gap-2 p-2 rounded hover:bg-white cursor-pointer">
                                    <input
                                        type="checkbox"
                                        value="{{ $tipo->id }}"
                                        wire:model.defer="selectedTiposEnvio"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    >
                                    <div class="flex flex-col leading-tight">
                                        <span class="text-sm text-gray-800 font-medium">{{ $tipo->nombre }}</span>
                                        <span class="text-xs text-gray-500">
                                            {{ $tipo->descripcion ?? '' }}
                                            @if(!is_null($tipo->dias_envio))
                                                • {{ $tipo->dias_envio }} días
                                            @endif
                                        </span>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        @error('selectedTiposEnvio') <div class="text-red-600 text-sm mt-2">{{ $message }}</div> @enderror
                        @error('selectedTiposEnvio.*') <div class="text-red-600 text-sm mt-2">{{ $message }}</div> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button
                            type="button"
                            wire:click="closeModal"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                        >
                            {{ $isEditMode ? 'Actualizar' : 'Guardar' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    @endif

</div>
<div
    x-data="{
        showModal: @entangle('showModal').live,
        isEditMode: @entangle('isEditMode').live,
        openCreate() { $wire.openCreateModal() },
        close() { $wire.closeModal() },
    }"
    class="container mx-auto p-6"
>
    {{-- Mensajes --}}
    @if (session()->has('message'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 p-3 text-sm">
            {{ session('message') }}
        </div>
    @endif

    {{-- Barra superior --}}
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                class="w-full sm:w-auto px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                @click="openCreate()"
            >
                Nueva Ciudad
            </button>

            <button
                type="button"
                class="w-full sm:w-auto px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 border"
                wire:click="clearFilters"
            >
                Limpiar filtros
            </button>
        </div>

        <div class="flex items-center gap-2">
            <label for="per-page" class="text-sm text-gray-600">Registros por página</label>
            <select
                id="per-page"
                class="w-28 rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500"
                wire:model.live="perPage"
            >
                @foreach($perPageOptions as $n)
                    <option value="{{ $n }}">{{ $n }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full border-collapse border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    {{-- ID --}}
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600 align-top">
                        <div class="flex items-start justify-between gap-2 min-w-[10rem]">
                            <span>ID</span>

                            <div x-data="{ open:false }" class="relative shrink-0">
                                <button @click="open=!open" class="p-1 rounded hover:bg-gray-200" title="Filtrar ID">⋮</button>
                                <div
                                    x-cloak x-show="open" @click.away="open=false" x-transition
                                    class="absolute right-0 z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3"
                                >
                                    <label class="block text-xs text-gray-600 mb-1">ID (ej. 10 ó 10,11)</label>
                                    <input
                                        type="text"
                                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                        placeholder="ID…"
                                        wire:model.live.debounce.400ms="filters.id"
                                    />
                                    <div class="mt-2 flex justify-end gap-2">
                                        <button type="button" class="px-2 py-1 text-xs rounded border"
                                            @click="$wire.set('filters.id','')">Limpiar</button>
                                        <button type="button" class="px-2 py-1 text-xs rounded border"
                                            @click="open=false">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </th>

                    {{-- Nombre --}}
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600 align-top">
                        <div class="flex items-start justify-between gap-2 min-w-[14rem]">
                            <span>Nombre</span>

                            <div x-data="{ open:false }" class="relative shrink-0">
                                <button @click="open=!open" class="p-1 rounded hover:bg-gray-200" title="Filtrar Nombre">⋮</button>
                                <div
                                    x-cloak x-show="open" @click.away="open=false" x-transition
                                    class="absolute right-0 z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3"
                                >
                                    <label class="block text-xs text-gray-600 mb-1">Nombre contiene</label>
                                    <input
                                        type="text"
                                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                        placeholder="Buscar…"
                                        wire:model.live.debounce.400ms="filters.nombre"
                                    />
                                    <div class="mt-2 flex justify-end gap-2">
                                        <button type="button" class="px-2 py-1 text-xs rounded border"
                                            @click="$wire.set('filters.nombre','')">Limpiar</button>
                                        <button type="button" class="px-2 py-1 text-xs rounded border"
                                            @click="open=false">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </th>

                    {{-- Estado --}}
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600 align-top">
                        <div class="flex items-start justify-between gap-2 min-w-[14rem]">
                            <span>Estado</span>

                            <div x-data="{ open:false }" class="relative shrink-0">
                                <button @click="open=!open" class="p-1 rounded hover:bg-gray-200" title="Filtrar Estado">⋮</button>
                                <div
                                    x-cloak x-show="open" @click.away="open=false" x-transition
                                    class="absolute right-0 z-50 mt-1 w-72 rounded-lg border bg-white shadow p-3"
                                >
                                    <label class="block text-xs text-gray-600 mb-1">Estado</label>
                                    <select
                                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                        wire:model.live.debounce.400ms="filters.estado_id"
                                    >
                                        <option value="">Todos</option>
                                        @foreach($estados as $estado)
                                            <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                                        @endforeach
                                    </select>

                                    <div class="mt-2 flex justify-end gap-2">
                                        <button type="button" class="px-2 py-1 text-xs rounded border"
                                            @click="$wire.set('filters.estado_id','')">Limpiar</button>
                                        <button type="button" class="px-2 py-1 text-xs rounded border"
                                            @click="open=false">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </th>

                    {{-- Tipos de envío --}}
                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600 align-top">
                        <div class="flex items-start justify-between gap-2 min-w-[16rem]">
                            <span>Tipos de Envío</span>

                            <div x-data="{ open:false }" class="relative shrink-0">
                                <button @click="open=!open" class="p-1 rounded hover:bg-gray-200" title="Filtrar Tipo envío">⋮</button>
                                <div
                                    x-cloak x-show="open" @click.away="open=false" x-transition
                                    class="absolute right-0 z-50 mt-1 w-72 rounded-lg border bg-white shadow p-3"
                                >
                                    <label class="block text-xs text-gray-600 mb-1">Tipo de envío</label>
                                    <select
                                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                        wire:model.live.debounce.400ms="filters.tipo_envio_id"
                                    >
                                        <option value="">Todos</option>
                                        @foreach($tiposEnvio as $tipo)
                                            <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                        @endforeach
                                    </select>

                                    <div class="mt-2 flex justify-end gap-2">
                                        <button type="button" class="px-2 py-1 text-xs rounded border"
                                            @click="$wire.set('filters.tipo_envio_id','')">Limpiar</button>
                                        <button type="button" class="px-2 py-1 text-xs rounded border"
                                            @click="open=false">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </th>

                    <th class="border-b px-4 py-2 text-left text-sm font-medium text-gray-600">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($ciudades as $ciudad)
                    <tr class="hover:bg-gray-50">
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $ciudad->id }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $ciudad->nombre }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">{{ $ciudad->estado->nombre ?? '—' }}</td>
                        <td class="border-b px-4 py-2 text-gray-700 text-sm">
                            @if($ciudad->tipoEnvios->isNotEmpty())
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($ciudad->tipoEnvios as $tipoEnvio)
                                        <li class="text-gray-600">{{ $tipoEnvio->nombre }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-gray-500">Sin tipos</span>
                            @endif
                        </td>

                        <td class="border-b px-4 py-2 text-gray-700 text-sm">
                            <div class="flex flex-col sm:flex-row gap-2">
                                <button
                                    type="button"
                                    class="text-blue-600 hover:underline"
                                    wire:click="openEditModal({{ $ciudad->id }})"
                                >
                                    Editar
                                </button>

                                <button
                                    type="button"
                                    class="text-red-600 hover:underline"
                                    x-data
                                    @click="if(confirm('¿Eliminar esta ciudad?')) { $wire.deleteCiudad({{ $ciudad->id }}) }"
                                >
                                    Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
                            No hay ciudades para mostrar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    <div class="mt-4">
        {{ $ciudades->links() }}
    </div>

    {{-- Modal Create/Edit --}}
    <div
        x-cloak
        x-show="showModal"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
            <div class="p-4 border-b flex items-center justify-between">
                <h2 class="text-lg font-semibold">
                    <span x-show="!isEditMode">Nueva Ciudad</span>
                    <span x-show="isEditMode">Editar Ciudad</span>
                </h2>
                <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" @click="close()">✕</button>
            </div>

            <form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}" class="p-4 space-y-4">
                {{-- Errores globales --}}
                @if ($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 text-red-800 p-3 text-sm">
                        <p class="font-semibold mb-1">Revisa estos campos:</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre de la Ciudad</label>
                        <input
                            type="text"
                            id="nombre"
                            wire:model.defer="nombre"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        @error('nombre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="estado_id" class="block text-sm font-medium text-gray-700">Estado</label>
                        <select
                            id="estado_id"
                            wire:model.defer="estado_id"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">Seleccionar Estado</option>
                            @foreach($estados as $estado)
                                <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                            @endforeach
                        </select>
                        @error('estado_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tipos de Envío
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($tiposEnvio as $tipo)
                            <label class="flex items-center gap-2 bg-gray-50 hover:bg-gray-100 border rounded-lg p-3 cursor-pointer transition">
                                <input
                                    type="checkbox"
                                    value="{{ $tipo->id }}"
                                    wire:model.defer="selectedTiposEnvio"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                                <span class="text-sm text-gray-700">
                                    {{ $tipo->nombre }}
                                </span>
                            </label>
                        @endforeach
                    </div>

                    @error('selectedTiposEnvio')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="pt-2 flex flex-col sm:flex-row justify-end gap-2">
                    <button
                        type="button"
                        class="w-full sm:w-auto px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100"
                        @click="close()"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="w-full sm:w-auto px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700"
                    >
                        <span x-show="!isEditMode">Guardar</span>
                        <span x-show="isEditMode">Actualizar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

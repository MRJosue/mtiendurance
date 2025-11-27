<div
    x-data="{
        abierto: JSON.parse(localStorage.getItem('cambiar_propietario_empresas_abierto') ?? 'true'),
        modalAbierto: @entangle('showModal'),
        seleccionado: @entangle('nuevoPropietarioId')
    }"
    class="container mx-auto p-6"
>
    <h2
        @click="
            abierto = !abierto;
            localStorage.setItem('cambiar_propietario_empresas_abierto', JSON.stringify(abierto));
        "
        class="text-2xl font-bold mb-4 border-b border-gray-300 pb-2 cursor-pointer hover:text-blue-600 transition flex flex-col md:flex-row md:items-center md:justify-between"
    >
        <span>Administra propietarios de empresas</span>
        <span class="text-sm text-gray-500 mt-2 md:mt-0" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
    </h2>

    <div x-show="abierto" x-transition>
        {{-- PANEL DE FILTROS AVANZADOS (opcional, lo dejamos como en Pedidos) --}}


        {{-- TABLA DE EMPRESAS --}}
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full border-collapse border border-gray-200 rounded-lg text-sm">
                <thead class="bg-gray-100">
                    {{-- Encabezados con ordenamiento --}}
                    <tr>
                        {{-- ID (ordenable) --}}
                        <th class="border-b px-4 py-2 text-left font-medium text-gray-600">
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 hover:text-blue-600"
                                wire:click="sortBy('id')"
                                title="Ordenar por ID"
                            >
                                <span>ID</span>
                                <span class="text-xs">
                                    @if($sortField === 'id')
                                        {{ $sortDir === 'asc' ? '▲' : '▼' }}
                                    @else
                                        ⇵
                                    @endif
                                </span>
                            </button>
                        </th>

                        {{-- Nombre (ordenable) --}}
                        <th class="border-b px-4 py-2 text-left font-medium text-gray-600">
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 hover:text-blue-600"
                                wire:click="sortBy('nombre')"
                                title="Ordenar por nombre"
                            >
                                <span>Nombre</span>
                                <span class="text-xs">
                                    @if($sortField === 'nombre')
                                        {{ $sortDir === 'asc' ? '▲' : '▼' }}
                                    @else
                                        ⇵
                                    @endif
                                </span>
                            </button>
                        </th>

                        {{-- RFC (ordenable) --}}
                        <th class="border-b px-4 py-2 text-left font-medium text-gray-600">
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 hover:text-blue-600"
                                wire:click="sortBy('rfc')"
                                title="Ordenar por RFC"
                            >
                                <span>RFC</span>
                                <span class="text-xs">
                                    @if($sortField === 'rfc')
                                        {{ $sortDir === 'asc' ? '▲' : '▼' }}
                                    @else
                                        ⇵
                                    @endif
                                </span>
                            </button>
                        </th>

                        {{-- Teléfono (ordenable) --}}
                        <th class="border-b px-4 py-2 text-left font-medium text-gray-600">
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 hover:text-blue-600"
                                wire:click="sortBy('telefono')"
                                title="Ordenar por teléfono"
                            >
                                <span>Teléfono</span>
                                <span class="text-xs">
                                    @if($sortField === 'telefono')
                                        {{ $sortDir === 'asc' ? '▲' : '▼' }}
                                    @else
                                        ⇵
                                    @endif
                                </span>
                            </button>
                        </th>

                        {{-- Propietario (ordenable por nombre) --}}
                        <th class="border-b px-4 py-2 text-left font-medium text-gray-600">
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 hover:text-blue-600"
                                wire:click="sortBy('propietario_nombre')"
                                title="Ordenar por propietario"
                            >
                                <span>Propietario</span>
                                <span class="text-xs">
                                    @if($sortField === 'propietario_nombre')
                                        {{ $sortDir === 'asc' ? '▲' : '▼' }}
                                    @else
                                        ⇵
                                    @endif
                                </span>
                            </button>
                        </th>

                        <th class="border-b px-4 py-2 text-center font-medium text-gray-600">
                            Acciones
                        </th>
                    </tr>

                    {{-- FILTROS POR COLUMNA (dropdown tipo Pedidos) --}}
                    <tr class="border-t border-gray-200">
                        {{-- Filtro ID --}}
                        <th class="px-4 py-2">
                            <div x-data="{ open:false }" class="relative inline-flex items-center">
                                <button
                                    @click="open = !open"
                                    class="px-2 py-1 rounded hover:bg-gray-200 text-xs"
                                    title="Filtrar ID"
                                >
                                    ⋮
                                </button>
                                <div
                                    x-cloak
                                    x-show="open"
                                    @click.away="open=false"
                                    x-transition
                                    class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3"
                                >
                                    <label class="block text-xs text-gray-600 mb-1">
                                        ID empresa
                                    </label>
                                    <input
                                        type="text"
                                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                        placeholder="Ej. 10 o 10,11,12"
                                        wire:model.live.debounce.400ms="filters.id"
                                    />
                                    <div class="mt-2 flex justify-end gap-2">
                                        <button
                                            type="button"
                                            class="px-2 py-1 text-xs rounded border"
                                            @click="$wire.set('filters.id','')"
                                        >
                                            Limpiar
                                        </button>
                                        <button
                                            type="button"
                                            class="px-2 py-1 text-xs rounded border"
                                            @click="open=false"
                                        >
                                            Cerrar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </th>

                        {{-- Filtro Nombre --}}
                        <th class="px-4 py-2">
                            <div x-data="{ open:false }" class="relative inline-flex items-center">
                                <button
                                    @click="open = !open"
                                    class="px-2 py-1 rounded hover:bg-gray-200 text-xs"
                                    title="Filtrar nombre"
                                >
                                    ⋮
                                </button>
                                <div
                                    x-cloak
                                    x-show="open"
                                    @click.away="open=false"
                                    x-transition
                                    class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3"
                                >
                                    <label class="block text-xs text-gray-600 mb-1">
                                        Nombre de la empresa
                                    </label>
                                    <input
                                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                        placeholder="Nombre..."
                                        wire:model.live.debounce.400ms="filters.nombre"
                                    />
                                    <div class="mt-2 flex justify-end gap-2">
                                        <button
                                            type="button"
                                            class="px-2 py-1 text-xs rounded border"
                                            @click="$wire.set('filters.nombre','')"
                                        >
                                            Limpiar
                                        </button>
                                        <button
                                            type="button"
                                            class="px-2 py-1 text-xs rounded border"
                                            @click="open=false"
                                        >
                                            Cerrar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </th>

                        {{-- Filtro RFC --}}
                        <th class="px-4 py-2">
                            <div x-data="{ open:false }" class="relative inline-flex items-center">
                                <button
                                    @click="open = !open"
                                    class="px-2 py-1 rounded hover:bg-gray-200 text-xs"
                                    title="Filtrar RFC"
                                >
                                    ⋮
                                </button>
                                <div
                                    x-cloak
                                    x-show="open"
                                    @click.away="open=false"
                                    x-transition
                                    class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3"
                                >
                                    <label class="block text-xs text-gray-600 mb-1">
                                        RFC
                                    </label>
                                    <input
                                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                        placeholder="RFC..."
                                        wire:model.live.debounce.400ms="filters.rfc"
                                    />
                                    <div class="mt-2 flex justify-end gap-2">
                                        <button
                                            type="button"
                                            class="px-2 py-1 text-xs rounded border"
                                            @click="$wire.set('filters.rfc','')"
                                        >
                                            Limpiar
                                        </button>
                                        <button
                                            type="button"
                                            class="px-2 py-1 text-xs rounded border"
                                            @click="open=false"
                                        >
                                            Cerrar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </th>

                        {{-- Filtro Teléfono --}}
                        <th class="px-4 py-2">
                            <div x-data="{ open:false }" class="relative inline-flex items-center">
                                <button
                                    @click="open = !open"
                                    class="px-2 py-1 rounded hover:bg-gray-200 text-xs"
                                    title="Filtrar teléfono"
                                >
                                    ⋮
                                </button>
                                <div
                                    x-cloak
                                    x-show="open"
                                    @click.away="open=false"
                                    x-transition
                                    class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3"
                                >
                                    <label class="block text-xs text-gray-600 mb-1">
                                        Teléfono
                                    </label>
                                    <input
                                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                        placeholder="Teléfono..."
                                        wire:model.live.debounce.400ms="filters.telefono"
                                    />
                                    <div class="mt-2 flex justify-end gap-2">
                                        <button
                                            type="button"
                                            class="px-2 py-1 text-xs rounded border"
                                            @click="$wire.set('filters.telefono','')"
                                        >
                                            Limpiar
                                        </button>
                                        <button
                                            type="button"
                                            class="px-2 py-1 text-xs rounded border"
                                            @click="open=false"
                                        >
                                            Cerrar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </th>

                        {{-- Filtro Propietario --}}
                        <th class="px-4 py-2">
                            <div x-data="{ open:false }" class="relative inline-flex items-center">
                                <button
                                    @click="open = !open"
                                    class="px-2 py-1 rounded hover:bg-gray-200 text-xs"
                                    title="Filtrar propietario"
                                >
                                    ⋮
                                </button>
                                <div
                                    x-cloak
                                    x-show="open"
                                    @click.away="open=false"
                                    x-transition
                                    class="absolute z-50 mt-1 w-64 rounded-lg border bg-white shadow p-3"
                                >
                                    <label class="block text-xs text-gray-600 mb-1">
                                        Propietario (nombre o correo)
                                    </label>
                                    <input
                                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                                        placeholder="Nombre/correo..."
                                        wire:model.live.debounce.400ms="filters.propietario"
                                    />

                                    <div class="mt-3 flex items-center space-x-2">
                                        <input
                                            id="sin_propietario_col"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                            wire:model.live="filters.sin_propietario"
                                        >
                                        <label for="sin_propietario_col" class="text-xs text-gray-700">
                                            Solo sin propietario
                                        </label>
                                    </div>

                                    <div class="mt-2 flex justify-end gap-2">
                                        <button
                                            type="button"
                                            class="px-2 py-1 text-xs rounded border"
                                            @click="
                                                $wire.set('filters.propietario','');
                                                $wire.set('filters.sin_propietario', false);
                                            "
                                        >
                                            Limpiar
                                        </button>
                                        <button
                                            type="button"
                                            class="px-2 py-1 text-xs rounded border"
                                            @click="open=false"
                                        >
                                            Cerrar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </th>

                        {{-- Columna acciones sin filtro --}}
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($empresas as $empresa)
                        <tr class="hover:bg-gray-50">
                            <td class="border-b px-4 py-2 text-gray-700">
                                {{ $empresa->id }}
                            </td>
                            <td class="border-b px-4 py-2 text-gray-800">
                                {{ $empresa->nombre }}
                            </td>
                            <td class="border-b px-4 py-2 text-gray-700">
                                {{ $empresa->rfc ?? '—' }}
                            </td>
                            <td class="border-b px-4 py-2 text-gray-700">
                                {{ $empresa->telefono ?? '—' }}
                            </td>
                            <td class="border-b px-4 py-2 text-gray-700">
                                @if($empresa->propietario)
                                    <div class="flex flex-col">
                                        <span class="font-semibold">{{ $empresa->propietario->name }}</span>
                                        <span class="text-xs text-gray-500">{{ $empresa->propietario->email }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-sm">Sin propietario</span>
                                @endif
                            </td>
                            <td class="border-b px-4 py-2 text-center">
                                <button
                                    type="button"
                                    wire:click="abrirModal({{ $empresa->id }})"
                                    class="inline-flex items-center px-3 py-1.5 bg-blue-500 text-white text-xs sm:text-sm rounded-lg hover:bg-blue-600"
                                >
                                    Cambiar propietario
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="border-b px-4 py-4 text-center text-gray-400">
                                No hay empresas registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="mt-4">
            {{ $empresas->links() }}
        </div>

        {{-- Modal Cambio de Propietario --}}
       @if($showModal && $empresaSeleccionada)
            <div
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
                x-show="modalAbierto"
                x-transition
            >
                <div
                    class="bg-white rounded-lg shadow-lg w-full max-w-3xl mx-4 relative"
                >
                    <div class="border-b px-6 py-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold">
                            Cambiar propietario de: {{ $empresaSeleccionada->nombre }}
                        </h3>
                        <button
                            type="button"
                            class="text-gray-500 hover:text-gray-800"
                            @click="modalAbierto = false; $wire.cerrarModal();"
                        >
                            ✕
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        {{-- Resumen empresa --}}
                        <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <div>
                                    <span class="font-semibold">RFC:</span>
                                    <span>{{ $empresaSeleccionada->rfc ?? 'Sin RFC' }}</span>
                                </div>
                                <div>
                                    <span class="font-semibold">Teléfono:</span>
                                    <span>{{ $empresaSeleccionada->telefono ?? 'Sin teléfono' }}</span>
                                </div>
                                <div class="md:col-span-2">
                                    <span class="font-semibold">Dirección:</span>
                                    <span>{{ $empresaSeleccionada->direccion ?? 'Sin dirección' }}</span>
                                </div>
                                <div class="md:col-span-2 mt-2">
                                    <span class="font-semibold">Propietario actual:</span>
                                    @if($empresaSeleccionada->propietario)
                                        <span class="ml-2">
                                            {{ $empresaSeleccionada->propietario->name }}
                                            <span class="text-xs text-gray-500">
                                                ({{ $empresaSeleccionada->propietario->email }})
                                            </span>
                                        </span>
                                    @else
                                        <span class="ml-2 text-gray-500">Sin propietario asignado</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Buscador de usuarios --}}
                        <div class="flex flex-wrap gap-3 items-center">
                            <div class="flex w-full sm:w-auto sm:flex-1 gap-2">
                                <input
                                    type="text"
                                    wire:model.defer="searchUsuario"
                                    placeholder="Buscar usuario (nombre o correo)..."
                                    class="flex-1 px-3 py-2 border rounded-lg text-sm"
                                />

                                <button
                                    type="button"
                                    wire:click="buscarCandidatos"
                                    class="px-3 py-2 bg-blue-500 text-white text-sm rounded-lg hover:bg-blue-600"
                                >
                                    Buscar
                                </button>

                                <button
                                    type="button"
                                    wire:click="limpiarBusquedaUsuarios"
                                    class="px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200"
                                >
                                    Limpiar
                                </button>
                            </div>

                            <p class="text-xs text-gray-500 w-full">
                                Escribe el nombre o correo y presiona <span class="font-semibold">Buscar</span>.
                            </p>
                        </div>

                        {{-- Tabla de candidatos --}}
                        <div class="overflow-x-auto bg-white rounded-lg border border-gray-200 max-h-[420px]">
                            <table class="min-w-full border-collapse text-sm">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="border-b px-3 py-2 text-left font-medium text-gray-600">Selección</th>
                                        <th class="border-b px-3 py-2 text-left font-medium text-gray-600">Usuario</th>
                                        <th class="border-b px-3 py-2 text-left font-medium text-gray-600">Tipo</th>
                                        <th class="border-b px-3 py-2 text-left font-medium text-gray-600">Organización</th>
                                        <th class="border-b px-3 py-2 text-left font-medium text-gray-600">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($candidatos as $usuario)
                                        <tr
                                            class="hover:bg-gray-50"
                                            :class="seleccionado == {{ $usuario->id }} ? 'bg-blue-50' : ''"
                                        >
                                            <td class="border-b px-3 py-2">
                                                <input
                                                    type="radio"
                                                    :value="{{ $usuario->id }}"
                                                    x-model="seleccionado"
                                                    class="rounded border-gray-300"
                                                />
                                            </td>
                                            <td class="border-b px-3 py-2">
                                                <div class="flex flex-col">
                                                    <span class="text-gray-800">{{ $usuario->name }}</span>
                                                    <span class="text-xs text-gray-500">{{ $usuario->email }}</span>
                                                </div>
                                            </td>
                                            <td class="border-b px-3 py-2 text-gray-600">
                                                {{ $usuario->tipo_texto ?? 'DESCONOCIDO' }}
                                            </td>
                                            <td class="border-b px-3 py-2 text-gray-600">
                                                <span
                                                    class="inline-flex items-center text-xs text-gray-600"
                                                    title="{{ $usuario->tooltip_sucursal_empresa ?? '' }}"
                                                >
                                                    {{ $usuario->empresa_principal_nombre ?? 'Sin organización' }}
                                                    @if(!empty($usuario->sucursal_nombre))
                                                        — {{ $usuario->sucursal_nombre }}
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="border-b px-3 py-2">
                                                @if($usuario->es_propietario && $usuario->empresa_id === $empresaSeleccionada->id)
                                                    <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">
                                                        Propietario actual
                                                    </span>
                                                @elseif($usuario->es_propietario)
                                                    <span class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                                        Propietario de otra empresa
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-700">
                                                        Candidato
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="border-b px-3 py-4 text-center text-gray-400">
                                                No hay usuarios que coincidan con la búsqueda.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Footer botones --}}
                    <div class="border-t px-6 py-4 flex flex-col sm:flex-row justify-end gap-2">
                        <button
                            type="button"
                            class="w-full sm:w-auto px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 text-sm"
                            @click="modalAbierto = false; $wire.cerrarModal();"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            class="w-full sm:w-auto px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="!seleccionado"
                            @click="$wire.actualizarPropietario()"
                        >
                            Guardar propietario
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Toast de notificación --}}
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
            class="fixed bottom-6 right-6 z-50 min-w-[240px] flex items-center p-4 rounded-lg text-sm"
            :class="type === 'success'
                ? 'bg-green-100 text-green-800'
                : (type === 'info' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800')"
            style="display: none;"
        >
            <span x-text="message"></span>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // JS adicional si lo necesitas
        });
    </script>
</div>

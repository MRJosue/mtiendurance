<div
    x-data="{
        abierto: true,
        modalAbierto: @entangle('showModal'),
        seleccionado: @entangle('nuevoPropietarioId')
    }"
    class="container mx-auto p-6"
>
    <h2
        @click="abierto = !abierto"
        class="text-2xl font-bold mb-4 border-b border-gray-300 pb-2 cursor-pointer hover:text-blue-600 transition flex flex-col md:flex-row md:items-center md:justify-between"
    >
        <span>Administra propietarios de empresas</span>
        <span class="text-sm text-gray-500 mt-2 md:mt-0" x-text="abierto ? '(Ocultar)' : '(Mostrar)'"></span>
    </h2>

    <div x-show="abierto" x-transition>
        {{-- Filtro de empresas --}}
        <div class="mb-4 flex flex-wrap gap-3 items-center">
            <input
                type="text"
                wire:model.debounce.400ms="searchEmpresa"
                placeholder="Buscar empresa por nombre o RFC..."
                class="w-full sm:w-80 px-3 py-2 border rounded-lg text-sm"
            />
        </div>

        {{-- Tabla de empresas --}}
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full border-collapse border border-gray-200 rounded-lg text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border-b px-4 py-2 text-left font-medium text-gray-600">ID</th>
                        <th class="border-b px-4 py-2 text-left font-medium text-gray-600">Nombre</th>
                        <th class="border-b px-4 py-2 text-left font-medium text-gray-600">RFC</th>
                        <th class="border-b px-4 py-2 text-left font-medium text-gray-600">Teléfono</th>
                        <th class="border-b px-4 py-2 text-left font-medium text-gray-600">Propietario</th>
                        <th class="border-b px-4 py-2 text-center font-medium text-gray-600">Acciones</th>
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
                            <input
                                type="text"
                                wire:model.debounce.400ms="searchUsuario"
                                placeholder="Buscar usuario (nombre o correo)..."
                                class="w-full sm:flex-1 px-3 py-2 border rounded-lg text-sm"
                            />
                            <p class="text-xs text-gray-500">
                                Puedes seleccionar cualquier usuario del sistema como nuevo propietario.
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

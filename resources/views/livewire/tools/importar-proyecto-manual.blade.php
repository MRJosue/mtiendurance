<div
    x-data="{
        selectedId: @entangle('selectedLegacyId'),
        dryRun: @entangle('dryRun'),
        confirmModal: @entangle('confirmModal'),
    }"
    class="container mx-auto p-6"
>
    {{-- Controles de búsqueda --}}
    <div class="mb-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="sm:col-span-2">
            <label class="block text-sm text-gray-600 mb-1">Buscar por ID o Título (legacy)</label>
            <div class="flex gap-2">
                <input
                    type="text"
                    class="w-full rounded-lg border-gray-300 focus:ring-blue-500"
                    placeholder="Ej. 53123 o 'Playeras'"
                    wire:model.live.debounce.400ms="search"
                />
                <button
                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                    wire:click="buscar"
                >
                    Buscar
                </button>
            </div>
        </div>

        <div class="sm:col-span-1">
            <label class="block text-sm text-gray-600 mb-1">Usuario fallback (si no existe client_id)</label>
            <input
                type="number"
                min="1"
                class="w-full rounded-lg border-gray-300 focus:ring-blue-500"
                wire:model.live="fallbackUserId"
            />
            @error('fallbackUserId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Acciones --}}
    <div class="mb-4 flex flex-wrap space-y-2 sm:space-y-0 sm:space-x-4">
        <button
            class="w-full sm:w-auto px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="!selectedId"
            @click="$wire.confirmar()"
        >
            Confirmar importación
        </button>

        <label class="inline-flex items-center space-x-2 w-full sm:w-auto">
            <input type="checkbox" class="rounded" x-model="dryRun" @change="$wire.set('dryRun', dryRun)" />
            <span class="text-sm text-gray-700">Simular (DRY-RUN)</span>
        </label>
    </div>

    {{-- Resultados --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full border-collapse border border-gray-200 rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border-b px-3 py-2 text-left text-sm font-medium text-gray-600">Sel</th>
                    <th class="border-b px-3 py-2 text-left text-sm font-medium text-gray-600">ID</th>
                    <th class="border-b px-3 py-2 text-left text-sm font-medium text-gray-600">Título</th>
                    <th class="border-b px-3 py-2 text-left text-sm font-medium text-gray-600">Client</th>
                    <th class="border-b px-3 py-2 text-left text-sm font-medium text-gray-600">Aprobado</th>
                    <th class="border-b px-3 py-2 text-left text-sm font-medium text-gray-600">Status</th>
                    <th class="border-b px-3 py-2 text-left text-sm font-medium text-gray-600">Duplicado</th>
                    <th class="border-b px-3 py-2 text-left text-sm font-medium text-gray-600">Usuario</th>
                    <th class="border-b px-3 py-2 text-left text-sm font-medium text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($results as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="border-b px-3 py-2">
                            <input
                                type="radio"
                                name="sel_project"
                                :checked="selectedId === {{ $r['project_id'] }}"
                                @change="$wire.seleccionar({{ $r['project_id'] }})"
                            />
                        </td>
                        <td class="border-b px-3 py-2 text-sm text-gray-700">{{ $r['project_id'] }}</td>
                        <td class="border-b px-3 py-2 text-sm text-gray-700 max-w-[30rem] truncate" title="{{ $r['title'] }}">{{ $r['title'] }}</td>
                        <td class="border-b px-3 py-2 text-sm text-gray-700">{{ $r['client_id'] }}</td>
                        <td class="border-b px-3 py-2 text-sm">
                            <span class="px-2 py-0.5 rounded text-xs {{ (int)$r['aprobado'] === 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ (int)$r['aprobado'] === 1 ? 'Sí' : 'No' }}
                            </span>
                        </td>
                        <td class="border-b px-3 py-2 text-sm text-gray-700">{{ $r['status'] ?? '—' }}</td>
                        <td class="border-b px-3 py-2 text-sm">
                            @if($r['duplicado'])
                                <span class="px-2 py-0.5 rounded text-xs bg-red-100 text-red-700">Existe</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-700">Libre</span>
                            @endif
                        </td>
                        <td class="border-b px-3 py-2 text-sm">
                            @if($r['usuario'])
                                <span class="px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-700">OK</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-xs bg-amber-100 text-amber-700">No existe</span>
                            @endif
                        </td>
                        <td class="border-b px-3 py-2 text-sm">
                            <button
                                class="px-3 py-1 rounded bg-blue-500 text-white hover:bg-blue-600"
                                wire:click="seleccionar({{ $r['project_id'] }})"
                            >Previsualizar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-3 py-6 text-center text-sm text-gray-500">
                            Sin resultados. Escribe algo y pulsa <b>Buscar</b>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Panel de previsualización --}}
    @if(!empty($preview))
        <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Resumen legado</h3>
                <dl class="text-sm text-gray-700 space-y-1">
                    <div class="flex justify-between"><dt>ID (legacy):</dt><dd>{{ $preview['project_id'] }}</dd></div>
                    <div class="flex justify-between"><dt>Cliente (client_id):</dt><dd>{{ $preview['client_id'] }}</dd></div>
                    <div class="flex justify-between"><dt>Título:</dt><dd class="text-right">{{ $preview['title'] ?: '—' }}</dd></div>
                    <div class="flex justify-between"><dt>Descripción:</dt><dd class="text-right">{{ $preview['description'] ?: '—' }}</dd></div>
                    <div class="flex justify-between"><dt>Aprobado:</dt><dd>{{ (int)$preview['aprobado'] === 1 ? 'Sí' : 'No' }}</dd></div>
                    <div class="flex justify-between"><dt>Status:</dt><dd>{{ $preview['status'] ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt>F. creación:</dt><dd>{{ $preview['fecha_creacion'] ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt>F. entrega:</dt><dd>{{ $preview['fecha_entrega'] ?? '—' }}</dd></div>
                </dl>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Validaciones</h3>
                <ul class="text-sm space-y-1">
                    <li>
                        @if($preview['duplicado'])
                            <span class="px-2 py-0.5 rounded bg-red-100 text-red-700">Duplicado: existe en proyectos.</span>
                        @else
                            <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-700">Libre en proyectos.</span>
                        @endif
                    </li>
                    <li>
                        @if($preview['usuario'])
                            <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-700">Usuario existe (users.id = client_id).</span>
                        @else
                            <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-700">Usuario NO existe. Se usará fallback ({{ $fallbackUserId }}).</span>
                        @endif
                    </li>
                    <li>
                        <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-700">Estado mapeado: {{ $preview['estado_final'] }}</span>
                    </li>
                </ul>

                <div class="mt-4 flex gap-2">
                    <button
                        class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        @click="$wire.confirmar()"
                        :disabled="{{ $preview['duplicado'] ? 'true' : 'false' }}"
                    >
                        Continuar
                    </button>
                    <button
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300"
                        @click="$wire.set('selectedLegacyId', null); $wire.set('preview', [])"
                    >
                        Limpiar selección
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de confirmación --}}
    <div
        x-cloak
        x-show="confirmModal"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        @keydown.escape.window="confirmModal=false"
    >
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
            <h3 class="text-lg font-semibold text-gray-800">Confirmar importación</h3>
            <p class="mt-2 text-sm text-gray-600">
                Se importará el proyecto <b>#{{ $preview['project_id'] ?? '—' }}</b> a <b>proyectos</b>.
                @if($dryRun)
                    <br>Nota: <b>DRY-RUN</b> activo (no insertará realmente).
                @endif
            </p>

            <div class="mt-4 flex justify-end gap-2">
                <button
                    class="px-4 py-2 rounded border"
                    @click="confirmModal=false"
                >Cancelar</button>
                <button
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                    @click="confirmModal=false; $wire.importar()"
                >Aceptar</button>
            </div>
        </div>
    </div>
</div>

{{-- Scripts (encapsulados) --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    window.addEventListener('toast', (e) => {
        const {type='info', message=''} = e.detail || {};
        // Ejemplo sencillo con alert; reemplaza por tu sistema de notificaciones global
        // (o dispara un modal/toast de tu preferencia).
        console.log(`[${type}] ${message}`);
    });
});
</script>

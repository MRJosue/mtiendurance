@php /* Blade de HojasCrud: listado + modales */ @endphp

<div x-data class="container mx-auto p-6">
    {{-- Encabezado / acciones --}}
    <div class="mb-4 flex flex-wrap gap-2 items-center">
        <input class="w-full sm:w-72 rounded-lg border-gray-300 focus:ring-blue-500"
               placeholder="Buscar hoja…" wire:model.live.debounce.300ms="search">
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                @click="$wire.openCreate()">
            Nueva Hoja
        </button>

        {{-- Crear filtro en línea (abre el modal de tu CRUD de filtros) --}}
        <button class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700"
                x-on:click="$wire.dispatch('abrir-modal-filtro')">
            Crear filtro…
        </button>
    </div>

    {{-- Tabla --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full border-collapse border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Nombre</th>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Rol</th>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600"># Filtros</th>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Estatus</th>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Visible</th>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Orden</th>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($hojas as $h)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 text-sm">
                            <a href="{{ route('produccion.hojas.show', $h->slug) }}" class="text-blue-600 hover:underline">
                                {{ $h->nombre }}
                            </a>
                        </td>
                        <td class="px-3 py-2 text-sm">{{ $h->rol->name ?? '—' }}</td>
                        <td class="px-3 py-2 text-sm">{{ $h->filtros_count }}</td>
                        <td class="px-3 py-2 text-xs text-gray-600">
                            @php $est = $h->estados_permitidos ?? []; @endphp
                            {{ empty($est) ? 'Todos' : implode(', ', $est) }}
                        </td>
                        <td class="px-3 py-2 text-sm">{{ $h->visible ? 'Sí' : 'No' }}</td>
                        <td class="px-3 py-2 text-sm">{{ $h->orden ?? '—' }}</td>
                        <td class="px-3 py-2 text-sm">
                            <button class="text-blue-600 hover:underline" wire:click="openEdit({{ $h->id }})">
                                Editar
                            </button>
                        </td>
                    </tr>
                @endforeach

                @if($hojas->isEmpty())
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                            No hay hojas aún.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    <div class="mt-4">
        {{ $hojas->links() }}
    </div>

    {{-- =========================
         MODAL CREAR / EDITAR HOJA
         (usa $modalOpen, $editId, $form, $roles, $estados, $filtros, $filtro_ids)
         ========================= --}}
    <div x-data="{ open: @entangle('modalOpen'), tab: 'datos', filtroSearch: '' }"
         x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/40" @click="open=false"></div>

        <div class="relative w-full max-w-5xl bg-white rounded-2xl shadow-xl p-5 sm:p-6">
            {{-- Header --}}
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $editId ? 'Editar Hoja de filtros de producción' : 'Crear Hoja de filtros de producción' }}
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Agrupa filtros en pestañas y define columnas base, rol de acceso y estatus permitidos.
                    </p>
                </div>
                <button class="ml-4 text-gray-500 hover:text-gray-700" @click="open=false">✕</button>
            </div>

            {{-- Tabs --}}
            <div class="mt-4 border-b">
                <nav class="flex flex-wrap -mb-px gap-2">
                    <button :class="tab==='datos' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-800'"
                            class="px-3 py-2 border-b-2 text-sm font-medium"
                            @click="tab='datos'">Datos</button>
                    <button :class="tab==='columnas' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-800'"
                            class="px-3 py-2 border-b-2 text-sm font-medium"
                            @click="tab='columnas'">Columnas base</button>
                    <button :class="tab==='filtros' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-800'"
                            class="px-3 py-2 border-b-2 text-sm font-medium"
                            @click="tab='filtros'">Filtros</button>
                    <button :class="tab==='acceso' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-800'"
                            class="px-3 py-2 border-b-2 text-sm font-medium"
                            @click="tab='acceso'">Acceso</button>
                </nav>
            </div>

            {{-- TAB: DATOS --}}
            <div x-show="tab==='datos'" class="mt-4 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre *</label>
                        <input type="text" wire:model.live="form.nombre"
                               class="mt-1 w-full rounded-lg border-gray-300 focus:ring-blue-500"
                               placeholder="Ej. Hoja General">
                        @error('form.nombre') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Slug</label>
                        <input type="text" wire:model.live="form.slug"
                               class="mt-1 w-full rounded-lg border-gray-300 focus:ring-blue-500"
                               placeholder="se-autogenera-si-lo-dejas-vacío">
                        @error('form.slug') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Descripción</label>
                    <textarea wire:model.live="form.descripcion"
                              class="mt-1 w-full rounded-lg border-gray-300 focus:ring-blue-500"
                              rows="3" placeholder="Descripción breve…"></textarea>
                    @error('form.descripcion') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="flex items-center gap-2">
                        <input id="visible" type="checkbox" class="rounded border-gray-300"
                               wire:model.live="form.visible">
                        <label for="visible" class="text-sm text-gray-700">Visible en el sistema</label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Orden</label>
                        <input type="number" min="0" wire:model.live="form.orden"
                               class="mt-1 w-32 rounded-lg border-gray-300 focus:ring-blue-500">
                        @error('form.orden') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- TAB: COLUMNAS BASE --}}
            <div x-show="tab==='columnas'" class="mt-4">
                <p class="text-sm text-gray-600 mb-3">
                    Las <strong>columnas base</strong> se muestran en todas las pestañas de esta Hoja.
                    <strong>ID</strong> y el checkbox de selección múltiple siempre se muestran.
                </p>

                <div class="overflow-x-auto rounded-lg border">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">Orden</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">Key</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">Label</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">Visible</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">Fija</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($form['base_columnas'] as $i => $c)
                                <tr>
                                    <td class="px-3 py-2">
                                        <input type="number" min="1"
                                               class="w-20 rounded border-gray-300"
                                               wire:model.live="form.base_columnas.{{ $i }}.orden">
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-700">{{ $c['key'] ?? '' }}</td>
                                    <td class="px-3 py-2">
                                        <input type="text" class="w-full rounded border-gray-300"
                                               wire:model.live="form.base_columnas.{{ $i }}.label"
                                               @disabled(($c['key'] ?? '')==='id')>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="checkbox" class="rounded border-gray-300"
                                               wire:model.live="form.base_columnas.{{ $i }}.visible"
                                               @disabled(($c['key'] ?? '')==='id' || ($c['fixed'] ?? false))>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="checkbox" class="rounded border-gray-300"
                                               wire:model.live="form.base_columnas.{{ $i }}.fixed"
                                               @disabled(($c['key'] ?? '')==='id')>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @error('form.base_columnas') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror

                <p class="text-xs text-gray-500 mt-2">
                    Consejo: usa números 1..N para ordenar. El sistema normaliza y asegura que <strong>ID</strong> siempre esté visible.
                </p>
            </div>

            {{-- TAB: FILTROS --}}
            <div x-show="tab==='filtros'" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-700">Filtros disponibles</label>
                        <input x-model="filtroSearch"
                               class="w-40 rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                               placeholder="Buscar…">
                    </div>
                    <div class="max-h-72 overflow-y-auto rounded-lg border p-2">
                        @foreach($filtros as $f)
                            @php $n = $f->nombre; @endphp
                            <template x-if="'{{ strtolower($n) }}'.includes(filtroSearch.toLowerCase())">
                                <label class="flex items-center gap-2 py-1">
                                    <input type="checkbox" class="rounded border-gray-300"
                                           value="{{ $f->id }}"
                                           @checked(in_array($f->id, $filtro_ids))
                                           @change="$event.target.checked
                                                ? @this.filtro_ids.push({{ $f->id }})
                                                : @this.filtro_ids = @this.filtro_ids.filter(i => i !== {{ $f->id }})">
                                    <span class="text-sm text-gray-700">{{ $f->nombre }}</span>
                                </label>
                            </template>
                        @endforeach
                        @if($filtros->isEmpty())
                            <div class="text-sm text-gray-500">No hay filtros aún.</div>
                        @endif
                    </div>

                    <div class="mt-3">
                        <button class="px-3 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700"
                                x-on:click="$wire.dispatch('abrir-modal-filtro')">
                            Crear filtro…
                        </button>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 mb-2 block">Orden de pestañas</label>
                    <div class="max-h-72 overflow-y-auto rounded-lg border divide-y">
                        @forelse($filtro_ids as $idx => $fid)
                            @php $fn = \App\Models\FiltroProduccion::find($fid)?->nombre ?? "Filtro #$fid"; @endphp
                            <div class="flex items-center justify-between px-2 py-2">
                                <span class="text-sm text-gray-700">{{ $fn }}</span>
                                <div class="flex gap-1">
                                    <button type="button" class="px-2 py-1 rounded bg-gray-100 hover:bg-gray-200"
                                            wire:click="moveUpAssign({{ $idx }})" title="Arriba">↑</button>
                                    <button type="button" class="px-2 py-1 rounded bg-gray-100 hover:bg-gray-200"
                                            wire:click="moveDownAssign({{ $idx }})" title="Abajo">↓</button>
                                </div>
                            </div>
                        @empty
                            <div class="px-2 py-3 text-sm text-gray-500">Sin filtros asignados.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- TAB: ACCESO --}}
            <div x-show="tab==='acceso'" class="mt-4 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Rol con acceso</label>
                        <select wire:model.live="form.role_id"
                                class="mt-1 w-full rounded-lg border-gray-300 focus:ring-blue-500">
                            <option value="">— Sin restricción por rol —</option>
                            @foreach($roles as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('form.role_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Estatus de pedidos permitidos</label>
                        <div class="mt-1 rounded-lg border p-2 max-h-40 overflow-y-auto">
                            @forelse($estados as $e)
                                <label class="inline-flex items-center gap-2 mr-3 mb-2">
                                    <input type="checkbox" class="rounded border-gray-300"
                                           value="{{ $e }}"
                                           @checked(in_array($e, $form['estados_permitidos'] ?? []))
                                           @change="$event.target.checked
                                                ? @this.form.estados_permitidos.push('{{ $e }}')
                                                : @this.form.estados_permitidos = @this.form.estados_permitidos.filter(x => x !== '{{ $e }}')">
                                    <span class="text-sm text-gray-700">{{ $e }}</span>
                                </label>
                            @empty
                                <span class="text-sm text-gray-500">No hay estatus en la tabla pedidos.</span>
                            @endforelse
                        </div>
                        @error('form.estados_permitidos') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-gray-500 mt-1">Si no seleccionas ninguno, se mostrarán <em>todos</em> los estatus.</p>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="mt-6 flex justify-end gap-2">
                <button class="px-4 py-2 rounded-lg border" @click="open=false">Cancelar</button>
                <button class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700"
                        wire:click="save">
                    Guardar
                </button>
            </div>
        </div>
    </div>

    {{-- Script encapsulado --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        window.addEventListener('hojas-notify', (e) => {
            console.log(e.detail?.message || 'notificación');
        });
    });
    </script>
</div>

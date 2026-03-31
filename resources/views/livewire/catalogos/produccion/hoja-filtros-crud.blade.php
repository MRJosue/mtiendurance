<div x-data class="container mx-auto p-6 text-gray-900 dark:text-gray-100">
    <div class="mb-4 flex flex-wrap gap-2">
        <input class="w-full sm:w-72 rounded-lg border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:ring-blue-400"
               placeholder="Buscar hoja…" wire:model.live.debounce.300ms="search">
        <button class="rounded-lg bg-blue-600 px-4 py-2 text-white transition hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400"
                @click="$wire.openCreate()">Nueva hoja</button>

        <!-- botón para abrir modal de Filtros y crear en línea -->
        <button class="rounded-lg bg-emerald-600 px-4 py-2 text-white transition hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-400"
                x-on:click="$wire.dispatch('abrir-modal-filtro')">Crear filtro</button>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <table class="min-w-full border-collapse">
            <thead class="bg-gray-100 dark:bg-gray-800">
                <tr>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Nombre</th>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Rol</th>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-300"># Filtros</th>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Visible</th>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Orden</th>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($hojas as $h)
                <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/70">
                    <td class="px-3 py-2 text-sm">
                        <a href="{{ route('produccion.hojas.show', $h->slug) }}" class="text-blue-600 hover:underline dark:text-blue-400">
                            {{ $h->nombre }}
                        </a>
                    </td>
                    <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $h->rol->name ?? '—' }}</td>
                    <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $h->filtros_count }}</td>
                    <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $h->visible ? 'Sí' : 'No' }}</td>
                    <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $h->orden ?? '—' }}</td>
                    <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-200">
                        <button class="text-blue-600 hover:underline dark:text-blue-400" wire:click="openEdit({{ $h->id }})">Editar</button>
                          <span class="mx-1 text-gray-300 dark:text-gray-600">|</span>
                        <button class="text-red-600 hover:underline dark:text-red-400" @click="$wire.openDelete({{ $h->id }})">Eliminar</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $hojas->links() }}</div>


    {{-- =========================
     MODAL CREAR / EDITAR HOJA
     ========================= --}}
    <div x-data="{ open: @entangle('modalOpen'), tab: 'datos', filtroSearch: '' }"
        x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-black/60" @click="open=false"></div>

        <div class="relative max-h-[92vh] w-[98%] max-w-7xl overflow-y-auto rounded-2xl bg-white p-5 shadow-xl dark:bg-gray-900 dark:ring-1 dark:ring-white/10 sm:w-full sm:p-6">
            {{-- Header --}}
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ $editId ? 'Editar Hoja de filtros de producción' : 'Crear Hoja de filtros de producción' }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Agrupa filtros en pestañas y define columnas base, rol de acceso y estatus permitidos.
                    </p>
                </div>
                <button class="ml-4 text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" @click="open=false">✕</button>
            </div>

            {{-- Tabs --}}
            <div class="mt-4 border-b border-gray-200 dark:border-gray-700">
                <nav class="flex flex-wrap -mb-px gap-2">
                    <button :class="tab==='datos' ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'"
                            class="border-b-2 px-3 py-2 text-sm font-medium"
                            @click="tab='datos'">Datos</button>
                    <button :class="tab==='columnas' ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'"
                            class="border-b-2 px-3 py-2 text-sm font-medium"
                            @click="tab='columnas'">Columnas base</button>
                    <button :class="tab==='filtros' ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'"
                            class="border-b-2 px-3 py-2 text-sm font-medium"
                            @click="tab='filtros'">Filtros</button>
                    <button :class="tab==='acceso' ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'"
                            class="border-b-2 px-3 py-2 text-sm font-medium"
                            @click="tab='acceso'">Acceso</button>

                <button :class="tab==='menu' ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'"
                            class="border-b-2 px-3 py-2 text-sm font-medium"
                            @click="tab='menu'">Menu</button>

                <button :class="tab==='acciones' ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'"
                            class="border-b-2 px-3 py-2 text-sm font-medium"
                            @click="tab='acciones'">Acciones</button>
                </nav>
            </div>

            {{-- Contenido: DATOS --}}
            <div x-show="tab==='datos'" class="mt-4 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nombre *</label>
                        <input type="text" wire:model.live="form.nombre"
                            class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:ring-blue-400"
                            placeholder="Ej. Hoja Producción Playeras">
                        @error('form.nombre') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Slug</label>
                        <input type="text" wire:model.live="form.slug"
                            class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:ring-blue-400"
                            placeholder="se-autogenera-si-lo-dejas-vacío">
                        @error('form.slug') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Descripción</label>
                    <textarea wire:model.live="form.descripcion"
                            class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:ring-blue-400"
                            rows="3" placeholder="Descripción breve…"></textarea>
                    @error('form.descripcion') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="flex items-center gap-2">
                        <input id="visible" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400"
                            wire:model.live="form.visible">
                        <label for="visible" class="text-sm text-gray-700 dark:text-gray-200">Visible en el sistema</label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Orden</label>
                        <input type="number" min="0" wire:model.live="form.orden"
                            class="mt-1 w-32 rounded-lg border-gray-300 bg-white text-gray-900 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:ring-blue-400">
                        @error('form.orden') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Contenido: COLUMNAS BASE --}}
            <div x-show="tab==='columnas'" class="mt-4">
                <p class="mb-3 text-sm text-gray-600 dark:text-gray-300">
                    Las <strong>columnas base</strong> se muestran en todas las pestañas de esta Hoja.
                    <strong>ID</strong> y el checkbox de selección múltiple siempre se muestran.
                </p>

                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300">Orden</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300">Key</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300">Label</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300">Visible</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300">Fija</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($form['base_columnas'] as $i => $c)
                                <tr>
                                    <td class="px-3 py-2">
                                        <input type="number" min="1"
                                            class="w-20 rounded border-gray-300 bg-white text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                            wire:model.live="form.base_columnas.{{ $i }}.orden">
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $c['key'] ?? '' }}</td>
                                    <td class="px-3 py-2">
                                        <input type="text" class="w-full rounded border-gray-300 bg-white text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                            wire:model.live="form.base_columnas.{{ $i }}.label"
                                            @disabled(($c['key'] ?? '')==='id')>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400"
                                            wire:model.live="form.base_columnas.{{ $i }}.visible"
                                            @disabled(($c['key'] ?? '')==='id' || ($c['fixed'] ?? false))>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400"
                                            wire:model.live="form.base_columnas.{{ $i }}.fixed"
                                            @disabled(($c['key'] ?? '')==='id')>
                                    </td>

                                    <td class="px-3 py-2">
                                        <div class="flex items-center gap-1">
                                        {{-- ↑ subir --}}
                                        <button type="button"
                                                class="rounded bg-gray-100 px-2 py-1 text-gray-700 transition hover:bg-gray-200 disabled:opacity-40 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
                                                wire:click="baseColUp({{ $i }})"
                                                @disabled($i === 0 || (($c['key'] ?? '') === 'id'))
                                                title="Arriba">↑</button>

                                        {{-- ↓ bajar --}}
                                        <button type="button"
                                                class="rounded bg-gray-100 px-2 py-1 text-gray-700 transition hover:bg-gray-200 disabled:opacity-40 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
                                                wire:click="baseColDown({{ $i }})"
                                                @disabled($i === (count($form['base_columnas']) - 1) || (($c['key'] ?? '') === 'id'))
                                                title="Abajo">↓</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @error('form.base_columnas') <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror

                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Consejo: usa números 1..N para ordenar. El sistema normaliza y asegura que <strong>ID</strong> siempre esté visible.
                </p>
            </div>

            {{-- Contenido: FILTROS --}}
            <div x-show="tab==='filtros'" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Filtros disponibles</label>
                        <input x-model="filtroSearch"
                            class="w-40 rounded-lg border-gray-300 bg-white text-sm text-gray-900 placeholder:text-gray-400 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:ring-blue-400"
                            placeholder="Buscar…">
                    </div>
                        {{-- Filtros disponibles --}}
                        <div class="max-h-72 overflow-y-auto rounded-lg border border-gray-200 p-2 dark:border-gray-700">
                            @foreach($filtros as $f)
                                <template x-if="'{{ Str::lower($f->nombre) }}'.includes(filtroSearch.toLowerCase())">
                                    <div class="flex items-center justify-between py-1">
                                        <label class="flex items-center gap-2">
                                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400"
                                                value="{{ $f->id }}"
                                                @checked(in_array($f->id, $filtro_ids))
                                                @change="$event.target.checked
                                                        ? @this.filtro_ids.push({{ $f->id }})
                                                        : @this.filtro_ids = @this.filtro_ids.filter(i => i !== {{ $f->id }})">
                                            <span class="text-sm text-gray-700 dark:text-gray-200">{{ $f->nombre }}</span>
                                        </label>

                                        <button
                                            type="button"
                                            class="text-xs text-blue-600 hover:underline dark:text-blue-400"
                                            x-on:click="$wire.dispatch('editar-filtro', { id: {{ $f->id }} })"
                                            title="Editar filtro"
                                        >
                                            Editar
                                        </button>
                                    </div>
                                </template>
                            @endforeach
                            @if($filtros->isEmpty())
                                <div class="text-sm text-gray-500 dark:text-gray-400">No hay filtros aún.</div>
                            @endif
                        </div>


                    <div class="mt-3">
                        <button class="rounded-lg bg-emerald-600 px-3 py-2 text-white transition hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-400"
                                x-on:click="$wire.dispatch('abrir-modal-filtro')">
                            Crear filtro…
                        </button>
                    </div>
                </div>


                
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Orden de pestañas</label>

                        <div class="max-h-72 overflow-y-auto rounded-lg border border-gray-200 divide-y divide-gray-200 dark:border-gray-700 dark:divide-gray-700">
                            @forelse($filtro_ids as $idx => $fid)
                                @php $fn = \App\Models\FiltroProduccion::find($fid)?->nombre ?? "Filtro #$fid"; @endphp
                                <div class="flex items-center justify-between px-2 py-2">
                                    <span class="text-sm text-gray-700 dark:text-gray-200">{{ $fn }}</span>

                                    <div class="flex items-center gap-2">
                                        <button type="button" class="text-xs text-blue-600 hover:underline dark:text-blue-400"
                                            x-on:click="$wire.dispatch('editar-filtro', { id: {{ $fid }} })">
                                            Editar
                                        </button>

                                        <button type="button" class="rounded bg-gray-100 px-2 py-1 text-gray-700 transition hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
                                                wire:click="moveUpAssign({{ $idx }})" title="Arriba">↑</button>
                                        <button type="button" class="rounded bg-gray-100 px-2 py-1 text-gray-700 transition hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
                                                wire:click="moveDownAssign({{ $idx }})" title="Abajo">↓</button>
                                    </div>
                                </div>
                            @empty
                                <div class="px-2 py-3 text-sm text-gray-500 dark:text-gray-400">Sin filtros asignados.</div>
                            @endforelse
                        </div>
                </div>
            </div>

            {{-- Contenido: ACCESO --}}
            <div x-show="tab==='acceso'" class="mt-4 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Rol con acceso</label>
                        <select wire:model.live="form.role_id"
                                class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:ring-blue-400">
                            <option value="">— Sin restricción por rol —</option>
                            @foreach($roles as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('form.role_id') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Estatus de pedidos permitidos</label>
                            <div class="mt-1 max-h-40 overflow-y-auto rounded-lg border border-gray-200 p-2 dark:border-gray-700">
                                @forelse($estados as $e)
                                    <label class="inline-flex items-center gap-2 mr-3 mb-2">
                                        <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400"
                                            value="{{ $e['id'] }}"
                                            @checked(in_array($e['id'], $form['estados_permitidos'] ?? []))
                                            @change="$event.target.checked
                                                ? @this.form.estados_permitidos.push({{ $e['id'] }})
                                                : @this.form.estados_permitidos = @this.form.estados_permitidos.filter(x => x !== {{ $e['id'] }})">
                                        <span class="text-sm text-gray-700 dark:text-gray-200">{{ $e['nombre'] }}</span>
                                    </label>
                                @empty
                                    <span class="text-sm text-gray-500 dark:text-gray-400">No hay estados en el catálogo.</span>
                                @endforelse
                            </div>
                            @error('form.estados_permitidos') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Si no seleccionas ninguno, se mostrarán <em>todos</em> los estatus.</p>
                        </div>
                        <div>
                            <label class="mt-4 block text-sm font-medium text-gray-700 dark:text-gray-200">Estatus de Diseños permitidos</label>
                            <div class="mt-1 max-h-40 overflow-y-auto rounded-lg border border-gray-200 p-2 dark:border-gray-700">
                                @foreach($this->estadosDiseno as $status)
                                    <label class="inline-flex items-center gap-2 mr-3 mb-2">
                                        <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400"
                                            value="{{ $status }}"
                                            @checked(in_array($status, $form['estados_diseno_permitidos'] ?? []))
                                            @change="$event.target.checked
                                                    ? @this.form.estados_diseno_permitidos.push('{{ $status }}')
                                                    : @this.form.estados_diseno_permitidos = @this.form.estados_diseno_permitidos.filter(x => x !== '{{ $status }}')">
                                        <span class="text-sm text-gray-700 dark:text-gray-200">{{ $status }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('form.estados_diseno_permitidos') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Si no seleccionas ninguno, se mostrarán <em>todos</em> los estatus de diseño.</p>
                        </div>

                        <div>
                            <label class="mt-4 block text-sm font-medium text-gray-700 dark:text-gray-200">Estados de Producción Permitidos</label>

                            <div class="mt-1 max-h-40 overflow-y-auto rounded-lg border border-gray-200 p-2 dark:border-gray-700">
                                @foreach($this->estadosProduccion as $statusProd)
                                    <label class="inline-flex items-center gap-2 mr-3 mb-2">
                                        <input
                                            type="checkbox"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400"
                                            value="{{ $statusProd }}"
                                            @checked(in_array($statusProd, $form['estado_produccion_permitidos'] ?? []))

                                            @change="$event.target.checked
                                                ? @this.form.estado_produccion_permitidos.push('{{ $statusProd }}')
                                                : @this.form.estado_produccion_permitidos =
                                                    @this.form.estado_produccion_permitidos.filter(x => x !== '{{ $statusProd }}')"
                                        >
                                        <span class="text-sm text-gray-700 dark:text-gray-200">{{ $statusProd }}</span>
                                    </label>
                                @endforeach
                            </div>

                            @error('form.estado_produccion_permitidos') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            @error('form.estado_produccion_permitidos.*') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror

                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Si no seleccionas ninguno, se mostrarán <em>todos</em> los estados de producción.
                            </p>
                        </div>

                        <div>
                            <label class="mt-4 block text-sm font-medium text-gray-700 dark:text-gray-200">Estados de Proveedor Permitidos</label>

                            <div class="mt-1 max-h-40 overflow-y-auto rounded-lg border border-gray-200 p-2 dark:border-gray-700">
                                @foreach($this->estadosProveedor as $statusProveedor)
                                    <label class="inline-flex items-center gap-2 mr-3 mb-2">
                                        <input
                                            type="checkbox"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400"
                                            value="{{ $statusProveedor }}"
                                            @checked(in_array($statusProveedor, $form['estado_proveedor_permitidos'] ?? []))
                                            @change="$event.target.checked
                                                ? @this.form.estado_proveedor_permitidos.push('{{ $statusProveedor }}')
                                                : @this.form.estado_proveedor_permitidos =
                                                    @this.form.estado_proveedor_permitidos.filter(x => x !== '{{ $statusProveedor }}')"
                                        >
                                        <span class="text-sm text-gray-700 dark:text-gray-200">{{ $statusProveedor }}</span>
                                    </label>
                                @endforeach
                            </div>

                            @error('form.estado_proveedor_permitidos') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            @error('form.estado_proveedor_permitidos.*') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror

                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Si no seleccionas ninguno, se mostrarán <em>todos</em> los estados del proveedor.
                            </p>
                        </div>
                    </div>

                </div>
            </div>


            <!-- Contenido: MENÚ -->
            <div x-show="tab==='menu'" class="mt-4 space-y-4">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <!-- Ubicaciones del menú -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">¿En qué menú(s) debe mostrarse?</label>
                        <div class="mt-2 rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                            @foreach($menusDisponibles as $m)
                                <label class="flex items-center gap-2 py-1">
                                    <input type="checkbox"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400"
                                        value="{{ $m['key'] }}"
                                        @checked(in_array($m['key'], $form['menu_config']['ubicaciones'] ?? []))
                                        @change="
                                            $event.target.checked
                                            ? @this.form.menu_config.ubicaciones.push('{{ $m['key'] }}')
                                            : @this.form.menu_config.ubicaciones = (@this.form.menu_config.ubicaciones || []).filter(k => k !== '{{ $m['key'] }}')
                                        ">
                                    <span class="text-sm text-gray-700 dark:text-gray-200">{{ $m['label'] }}</span>
                                </label>
                            @endforeach
                            @error('form.menu_config.ubicaciones.*') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            @if(empty($menusDisponibles))
                                <p class="text-sm text-gray-500 dark:text-gray-400">No hay ubicaciones de menú configuradas.</p>
                            @endif
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Puedes elegir varias ubicaciones. El backoffice usará esta configuración para construir el menú dinámicamente.
                        </p>
                    </div>

                    <!-- Metadatos de menú -->
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Etiqueta en menú (opcional)</label>
                            <input type="text"
                                class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:ring-blue-400"
                                placeholder="Ej. Producción / Playeras"
                                wire:model.live="form.menu_config.etiqueta">
                            @error('form.menu_config.etiqueta') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Ícono (opcional)</label>
                            <input type="text"
                                class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:ring-blue-400"
                                placeholder="Ej. lucide-filter, heroicons:adjustments-vertical"
                                wire:model.live="form.menu_config.icono">
                            @error('form.menu_config.icono') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Orden en el menú</label>
                            <input type="number" min="0"
                                class="mt-1 w-32 rounded-lg border-gray-300 bg-white text-gray-900 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:ring-blue-400"
                                wire:model.live="form.menu_config.orden">
                            @error('form.menu_config.orden') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex items-center gap-2">
                            <input id="menu_activo" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400"
                                wire:model.live="form.menu_config.activo">
                            <label for="menu_activo" class="text-sm text-gray-700 dark:text-gray-200">Activo en menú</label>
                            @error('form.menu_config.activo') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800/60">
                    <h4 class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-200">Vista previa</h4>
                    <div class="flex flex-wrap gap-2">
                        @forelse($form['menu_config']['ubicaciones'] ?? [] as $uk)
                            @php
                                $label = collect($menusDisponibles)->firstWhere('key',$uk)['label'] ?? $uk;
                            @endphp
                            <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-1 text-xs text-blue-800 dark:bg-blue-900/40 dark:text-blue-200">
                                {{ $label }}
                            </span>
                        @empty
                            <span class="text-sm text-gray-500 dark:text-gray-400">Sin ubicaciones seleccionadas.</span>
                        @endforelse
                    </div>
                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Etiqueta: <strong>{{ $form['menu_config']['etiqueta'] ?: '—' }}</strong> •
                        Ícono: <strong>{{ $form['menu_config']['icono'] ?: '—' }}</strong> •
                        Orden: <strong>{{ $form['menu_config']['orden'] ?? '—' }}</strong> •
                        Activo: <strong>{{ ($form['menu_config']['activo'] ?? true) ? 'Sí' : 'No' }}</strong>
                    </div>
                </div>
            </div>

            {{-- Contenido: ACCIONES --}}
            {{-- Contenido: ACCIONES (sin componentes) --}}
            <div x-show="tab==='acciones'" class="mt-4 space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Define qué acciones estarán <strong>visibles/habilitadas</strong> en el HojaViewer para esta Hoja.
                </p>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    {{-- Grupo: General --}}
                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                        <h4 class="mb-2 text-sm font-semibold text-gray-800 dark:text-gray-100">General</h4>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700 dark:text-gray-200">Ver detalle</span>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400" wire:model.live="form.acciones.ver_detalle">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700 dark:text-gray-200">Selección múltiple</span>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400" wire:model.live="form.acciones.seleccion_multiple">
                        </label>
                        {{-- <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Abrir chat del proyecto</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.abrir_chat">
                        </label> --}}
                    </div>

                    {{-- Grupo: Pedidos --}}
                    {{-- <div class="rounded-lg border p-3">
                        <h4 class="text-sm font-semibold text-gray-800 mb-2">Pedidos</h4>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Crear pedido</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.crear_pedido">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Editar pedido</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.editar_pedido">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Eliminar pedido</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.eliminar_pedido">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Duplicar pedido</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.duplicar_pedido">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Exportar a Excel</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.exportar_excel">
                        </label>
                    </div> --}}

                    {{-- Grupo: Flujo / Producción --}}
                    {{-- <div class="rounded-lg border p-3">
                        <h4 class="text-sm font-semibold text-gray-800 mb-2">Flujo / Producción</h4>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Aprobar pedido</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.aprobar_pedido">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Programar pedido</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.programar_pedido">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Entregar pedido</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.entregar_pedido">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Cancelar pedido</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.cancelar_pedido">
                        </label>
                    </div> --}}

                    {{-- Grupo: Diseño --}}
                    {{-- <div class="rounded-lg border p-3">
                        <h4 class="text-sm font-semibold text-gray-800 mb-2">Diseño</h4>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Aprobar diseño</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.aprobar_diseno">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Rechazar diseño</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.rechazar_diseno">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Subir archivos</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.subir_archivos">
                        </label>
                    </div> --}}

                    {{-- Grupo: Tareas --}}
                    {{-- <div class="rounded-lg border p-3">
                        <h4 class="text-sm font-semibold text-gray-800 mb-2">Tareas</h4>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Crear tarea</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.crear_tarea">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Editar tarea</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.editar_tarea">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Eliminar tarea</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.eliminar_tarea">
                        </label>
                    </div> --}}

                    {{-- Grupo: Acciones Individuales --}}
                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                        <h4 class="mb-2 text-sm font-semibold text-gray-800 dark:text-gray-100">Acciones individuales</h4>
                        <label class="flex items items-center justify-between py-1">
                            <span class="text-sm text-gray-700 dark:text-gray-200">ver detalle</span>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400" wire:model.live="form.acciones.ver_detalle">
                        </label>

                        <label class="flex items items-center justify-between py-1">
                            <span class="text-sm text-gray-700 dark:text-gray-200">programar pedido</span>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400" wire:model.live="form.acciones.programar_pedido">
                        </label>    

                        <label for="ver-tallas" class="flex items items-center justify-between py-1">
                            <span class="text-sm text-gray-700 dark:text-gray-200">ver tallas</span>
                            <input type="checkbox" id="ver-tallas" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400" wire:model.live="form.acciones.ver_tallas">
                        </label>

                        <label for="editar-tallas" class="flex items items-center justify-between py-1">
                            <span class="text-sm text-gray-700 dark:text-gray-200">editar tallas</span>
                            <input type="checkbox" id="editar-tallas" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400" wire:model.live="form.acciones.editar_tallas">
                        </label>

                        <label for="editar_total_tallas" class="flex items items-center justify-between py-1">
                            <span class="text-sm text-gray-700 dark:text-gray-200">editar total tallas </span>
                            <input type="checkbox" id="editar_total_tallas" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400" wire:model.live="form.acciones.editar_total_tallas">
                        </label>


                        <label class="flex items items-center justify-between py-1">
                            <span class="text-sm text-gray-700 dark:text-gray-200">aprobar pedido</span>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400" wire:model.live="form.acciones.aprobar_pedido">
                        </label>

                        
                        <label class="flex items items-center justify-between py-1">
                            <span class="text-sm text-gray-700 dark:text-gray-200">duplicar pedido</span>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400" wire:model.live="form.acciones.duplicar_pedido">
                        </label>

                        <label class="flex items items-center justify-between py-1">
                            <span class="text-sm text-gray-700 dark:text-gray-200">eliminar pedido</span>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400" wire:model.live="form.acciones.eliminar_pedido">
                        </label>

                        <label class="flex items items-center justify-between py-1">
                            <span class="text-sm text-gray-700 dark:text-gray-200">cancelar pedido</span>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400" wire:model.live="form.acciones.cancelar_pedido">
                        </label>

                        <label class="flex items items-center justify-between py-1">
                            <span class="text-sm text-gray-700 dark:text-gray-200">exportar excel</span>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400" wire:model.live="form.acciones.exportar_excel">
                        </label>
       
                    </div>

                    {{-- Grupo: Acciones masivas --}}
                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                        <h4 class="mb-2 text-sm font-semibold text-gray-800 dark:text-gray-100">Acciones masivas</h4>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700 dark:text-gray-200">Exportar selección</span>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400" wire:model.live="form.acciones.bulk_exportar">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700 dark:text-gray-200">Eliminar selección</span>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400" wire:model.live="form.acciones.bulk_eliminar">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700 dark:text-gray-200">Programar selección</span>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400" wire:model.live="form.acciones.bulk_programar">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700 dark:text-gray-200">Aprobar selección</span>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400" wire:model.live="form.acciones.bulk_aprobar">
                        </label>
                    </div>


                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                        <h4 class="mb-2 text-sm font-semibold text-gray-800 dark:text-gray-100">Edicion en Linea</h4>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700 dark:text-gray-200">Total</span>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400" wire:model.live="form.acciones.bulk_edit_total">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700 dark:text-gray-200">Estado del Pedido</span>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400" wire:model.live="form.acciones.bulk_edit_estado">
                        </label>

                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700 dark:text-gray-200">Estado de Producción</span>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400" wire:model.live="form.acciones.bulk_edit_estado_produccion">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700 dark:text-gray-200">Estado de Proveedor</span>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400" wire:model.live="form.acciones.bulk_edit_estado_proveedor">
                        </label>

                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700 dark:text-gray-200">Fecha de Producción</span>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400" wire:model.live="form.acciones.bulk_edit_fecha_produccion">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700 dark:text-gray-200">Fecha de Embarque</span>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400" wire:model.live="form.acciones.bulk_edit_fecha_embarque">
                        </label>

                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700 dark:text-gray-200">Fecha de Entrega</span>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400" wire:model.live="form.acciones.bulk_edit_fecha_entrega">
                        </label>
                    </div>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400">El componente <code>HojaViewer</code> debe leer <code>$hoja->acciones_config</code>.</p>
            </div>



            {{-- Footer --}}
            <div class="mt-6 flex justify-end gap-2">
                <button class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800" @click="open=false">Cancelar</button>
                <button class="rounded-lg bg-blue-600 px-4 py-2 text-white transition hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400"
                        wire:click="save">
                    Guardar
                </button>
            </div>


        </div>
    </div>

    {{-- =========================
    MODAL INLINE: NUEVO FILTRO
    ========================= --}}
    <div x-data="{ open: @entangle('modalFiltroOpen'), tab: @entangle('modalFiltroTab') }"
        x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-black/60" @click="open=false"></div>

        <div class="relative max-h-[92vh] w-[98%] max-w-7xl overflow-y-auto rounded-2xl bg-white p-5 shadow-xl dark:bg-gray-900 dark:ring-1 dark:ring-white/10 sm:w-full sm:p-6">
            {{-- Header --}}
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ $filtroEditId ? 'Editar filtro de producción' : 'Nuevo filtro de producción' }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configura datos, productos y columnas.</p>
                </div>
                <button class="ml-4 text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" @click="open=false">✕</button>
            </div>
            {{-- Tabs --}}
            <div class="mt-4 border-b border-gray-200 dark:border-gray-700">
                <nav class="flex flex-wrap -mb-px gap-2">
                    <button :class="tab==='datos' ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'"
                            class="border-b-2 px-3 py-2 text-sm font-medium"
                            @click="tab='datos'">Datos</button>
                    <button :class="tab==='productos' ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'"
                            class="border-b-2 px-3 py-2 text-sm font-medium"
                            @click="tab='productos'">Productos</button>
                    <button :class="tab==='columnas' ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'"
                            class="border-b-2 px-3 py-2 text-sm font-medium"
                            @click="tab='columnas'">Columnas</button>
                </nav>
            </div>

            {{-- TAB: DATOS --}}
            <div x-show="tab==='datos'" class="mt-4 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nombre *</label>
                        <input type="text" wire:model.live="filtroForm.nombre"
                            class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:ring-blue-400"
                            placeholder="Ej. Punto de Cruz">
                        @error('filtroForm.nombre') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Slug</label>
                        <input type="text" wire:model.live="filtroForm.slug"
                            class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:ring-blue-400"
                            placeholder="se-autogenera-si-lo-dejas-vacío">
                        @error('filtroForm.slug') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Descripción</label>
                    <textarea wire:model.live="filtroForm.descripcion" rows="3"
                            class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:ring-blue-400"
                            placeholder="Descripción breve…"></textarea>
                    @error('filtroForm.descripcion') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="flex items-center gap-2">
                        <input id="filtro_visible" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400"
                            wire:model.live="filtroForm.visible">
                        <label for="filtro_visible" class="text-sm text-gray-700 dark:text-gray-200">Visible</label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Orden</label>
                        <input type="number" min="0" wire:model.live="filtroForm.orden"
                            class="mt-1 w-32 rounded-lg border-gray-300 bg-white text-gray-900 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:ring-blue-400">
                        @error('filtroForm.orden') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            
            {{-- TAB: PRODUCTOS --}}
            <div x-show="tab==='productos'" class="mt-4">
                <div class="mb-3 flex flex-col sm:flex-row sm:items-center gap-2">
                    <input
                        type="text"
                        placeholder="Buscar producto…"
                        class="w-full rounded-lg border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:border-blue-400 dark:focus:ring-blue-400"
                        wire:model.live.debounce.300ms="productoSearchFiltro"
                    />

                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="rounded-lg bg-gray-200 px-3 py-2 text-gray-800 transition hover:bg-gray-300 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
                            wire:click="filtroClearProductos"
                            @disabled(count($filtro_producto_ids) === 0)
                        >
                            Limpiar
                        </button>

                        <button
                            type="button"
                            class="rounded-lg bg-blue-600 px-3 py-2 text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-blue-500 dark:hover:bg-blue-400"
                            wire:click="filtroAddAllFilteredProductos"
                            @disabled($productos->count() === 0)
                        >
                            Añadir todos los mostrados
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    {{-- Resultados --}}
                    <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                        <div class="flex items-center justify-between border-b border-gray-200 p-3 dark:border-gray-700">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                Resultados ({{ $productos->count() }})
                            </h4>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Usa “Añadir a la lista”</span>
                        </div>

                        <div class="max-h-[360px] overflow-auto divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($productos as $prod)
                                @php($ya = in_array($prod->id, $filtro_producto_ids, true))

                                <div class="p-3 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate">
                                            {{ $prod->nombre }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $prod->id }}</div>
                                    </div>

                                    <button
                                        type="button"
                                        class="shrink-0 px-3 py-1.5 rounded-lg text-sm
                                            {{ $ya ? 'cursor-default bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200' : 'bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400' }}"
                                        wire:click="filtroAddProducto({{ $prod->id }})"
                                        @disabled($ya)
                                    >
                                        {{ $ya ? 'Añadido' : 'Añadir a la lista' }}
                                    </button>
                                </div>
                            @empty
                                <div class="p-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No hay productos con ese filtro.
                                </div>
                            @endforelse
                        </div>

                        <div class="border-t border-gray-200 p-3 text-xs text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            Tip: ya no necesitas Ctrl/Cmd 👍
                        </div>
                    </div>

                    {{-- Seleccionados --}}
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800/60">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                Seleccionados ({{ count($filtro_producto_ids) }})
                            </h4>

                            <button
                                type="button"
                                class="rounded border border-gray-300 bg-white px-2 py-1 text-xs text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                                wire:click="filtroSortProductosSelected"
                                @disabled(count($filtro_producto_ids) < 2)
                            >
                                Ordenar A–Z
                            </button>
                        </div>

                        <div class="mt-2 max-h-[360px] overflow-auto space-y-2">
                            @forelse($productosSeleccionadosFiltro as $pSel)
                                <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white px-3 py-2 dark:border-gray-700 dark:bg-gray-900">
                                    <div class="min-w-0">
                                        <div class="text-sm text-gray-800 dark:text-gray-100 truncate">{{ $pSel->nombre }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $pSel->id }}</div>
                                    </div>

                                    <button
                                        type="button"
                                        class="text-sm text-red-600 hover:underline dark:text-red-400"
                                        wire:click="filtroRemoveProducto({{ $pSel->id }})"
                                    >
                                        quitar
                                    </button>
                                </div>
                            @empty
                                <div class="text-sm text-gray-400 dark:text-gray-500">
                                    Sin productos seleccionados.
                                </div>
                            @endforelse
                        </div>

                        @error('filtro_producto_ids') <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            {{-- TAB: COLUMNAS --}}
            <div x-show="tab==='columnas'" class="mt-4">
                <div class="mb-3 flex items-center gap-2">
                    <input type="text" placeholder="Buscar característica…"
                        class="w-full rounded-lg border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:border-blue-400 dark:focus:ring-blue-400"
                        wire:model.live.debounce.300ms="caracteristicaSearchFiltro">
                    <select
                        class="rounded-lg border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400"
                        x-on:change="$wire.filtroAddCaracteristica(parseInt($event.target.value)); $event.target.value='';"
                    >
                        <option value="">Añadir característica…</option>
                        @foreach($caracteristicas as $car)
                            <option value="{{ $car->id }}">{{ $car->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                    <table class="min-w-full border-collapse">
                        <thead class="bg-gray-100 dark:bg-gray-800">
                            <tr class="text-sm text-gray-600 dark:text-gray-300">
                                <th class="px-3 py-2 text-left">#</th>
                                <th class="px-3 py-2 text-left">Característica</th>
                                <th class="px-3 py-2 text-left">Etiqueta</th>
                                <th class="px-3 py-2 text-left">Visible</th>
                                <th class="px-3 py-2 text-left">Render</th>
                                <th class="px-3 py-2 text-left">Multivalor</th>
                                <th class="px-3 py-2 text-left">Max</th>
                                <th class="px-3 py-2 text-left">Ancho</th>
                                <th class="px-3 py-2 text-left">Fallback</th>
                                <th class="px-3 py-2 text-left">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($filtro_columnas as $i => $col)
                                <tr class="text-sm transition hover:bg-gray-50 dark:hover:bg-gray-800/70">
                                    <td class="px-3 py-2 align-top">
                                        <input type="number" class="w-16 rounded border-gray-300 bg-white text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                            wire:model="filtro_columnas.{{ $i }}.orden">
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <div class="font-medium text-gray-800 dark:text-gray-100">{{ $col['nombre'] }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $col['caracteristica_id'] }}</div>
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <input type="text" class="w-40 rounded border-gray-300 bg-white text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                            wire:model="filtro_columnas.{{ $i }}.label">
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400"
                                            wire:model="filtro_columnas.{{ $i }}.visible">
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <select class="w-32 rounded border-gray-300 bg-white text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                                wire:model="filtro_columnas.{{ $i }}.render">
                                            <option value="texto">Texto</option>
                                            <option value="badges">Badges</option>
                                            <option value="chips">Chips</option>
                                            <option value="iconos">Íconos</option>
                                            <option value="count">Conteo</option>
                                        </select>
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <select class="w-28 rounded border-gray-300 bg-white text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                                wire:model="filtro_columnas.{{ $i }}.multivalor_modo">
                                            <option value="inline">Inline</option>
                                            <option value="badges">Badges</option>
                                            <option value="count">Conteo</option>
                                        </select>
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <input type="number" class="w-20 rounded border-gray-300 bg-white text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                            wire:model="filtro_columnas.{{ $i }}.max_items" min="1" max="99">
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <input type="text" class="w-28 rounded border-gray-300 bg-white text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                            wire:model="filtro_columnas.{{ $i }}.ancho" placeholder="p.ej. w-32">
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <input type="text" class="w-28 rounded border-gray-300 bg-white text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                            wire:model="filtro_columnas.{{ $i }}.fallback" placeholder="—">
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <div class="flex items-center gap-1">
                                            <button class="rounded bg-gray-200 px-2 py-1 text-gray-700 transition hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
                                                    @click.prevent="$wire.filtroReorderColumna({{ $i }}, {{ max(0, $i-1) }})">↑</button>
                                            <button class="rounded bg-gray-200 px-2 py-1 text-gray-700 transition hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
                                                    @click.prevent="$wire.filtroReorderColumna({{ $i }}, {{ min(count($filtro_columnas)-1, $i+1) }})">↓</button>
                                            <button class="rounded bg-red-500 px-2 py-1 text-white transition hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-500"
                                                    wire:click="filtroRemoveColumna({{ $i }})">Quitar</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">Aún no hay columnas configuradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Consejo: prioriza 3–6 columnas clave para mantener la tabla legible en pantallas pequeñas.
                </p>
            </div>

            {{-- Footer --}}
            <div class="mt-6 flex justify-end gap-2">
                <button class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800" @click="open=false">Cancelar</button>

                <button
                    class="rounded-lg bg-emerald-600 px-4 py-2 text-white transition hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-400"
                    wire:click="{{ $filtroEditId ? 'updateFiltro' : 'saveFiltro' }}"
                >
                    {{ $filtroEditId ? 'Guardar cambios' : 'Crear filtro' }}
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL CONFIRMAR ELIMINACIÓN --}}
    <div x-data="{ open: @entangle('confirmDeleteOpen') }"
        x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-black/60" @click="open=false"></div>

        <div class="relative w-[95%] max-w-lg rounded-2xl bg-white p-5 shadow-xl dark:bg-gray-900 dark:ring-1 dark:ring-white/10 sm:w-full sm:p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Eliminar hoja</h3>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                Esta acción eliminará la hoja seleccionada. Los filtros asociados <strong>no</strong> se eliminarán,
                solo se romperá la relación con esta hoja. ¿Deseas continuar?
            </p>

            <div class="mt-6 flex justify-end gap-2">
                <button class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800" @click="open=false">Cancelar</button>
                <button class="rounded-lg bg-red-600 px-4 py-2 text-white transition hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-400"
                        wire:click="deleteHoja">
                    Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    window.addEventListener('hojas-notify', e => console.log(e.detail?.message));
    
});
</script>

<div x-data class="container mx-auto p-6">
    <div class="mb-4 flex flex-wrap gap-2">
        <input class="w-full sm:w-72 rounded-lg border-gray-300 focus:ring-blue-500"
               placeholder="Buscar hoja…" wire:model.live.debounce.300ms="search">
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg"
                @click="$wire.openCreate()">Nueva hoja</button>

        <!-- botón para abrir modal de Filtros y crear en línea -->
        <button class="px-4 py-2 bg-emerald-600 text-white rounded-lg"
                x-on:click="$wire.dispatch('abrir-modal-filtro')">Crear filtro</button>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full border-collapse border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Nombre</th>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600">Rol</th>
                    <th class="px-3 py-2 text-left text-sm font-medium text-gray-600"># Filtros</th>
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
                    <td class="px-3 py-2 text-sm">{{ $h->visible ? 'Sí' : 'No' }}</td>
                    <td class="px-3 py-2 text-sm">{{ $h->orden ?? '—' }}</td>
                    <td class="px-3 py-2 text-sm">
                        <button class="text-blue-600 hover:underline" wire:click="openEdit({{ $h->id }})">Editar</button>
                          <span class="mx-1 text-gray-300">|</span>
                        <button class="text-red-600 hover:underline" @click="$wire.openDelete({{ $h->id }})">Eliminar</button>
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

                    <button :class="tab==='Menu' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-800'"
                            class="px-3 py-2 border-b-2 text-sm font-medium"
                            @click="tab='menu'">Menu</button>

                    <button :class="tab==='Menu' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-800'"
                            class="px-3 py-2 border-b-2 text-sm font-medium"
                            @click="tab='acciones'">Acciones</button>
                </nav>
            </div>

            {{-- Contenido: DATOS --}}
            <div x-show="tab==='datos'" class="mt-4 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre *</label>
                        <input type="text" wire:model.live="form.nombre"
                            class="mt-1 w-full rounded-lg border-gray-300 focus:ring-blue-500"
                            placeholder="Ej. Hoja Producción Playeras">
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

            {{-- Contenido: COLUMNAS BASE --}}
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
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">Acciones</th>
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

                                    <td class="px-3 py-2">
                                        <div class="flex items-center gap-1">
                                        {{-- ↑ subir --}}
                                        <button type="button"
                                                class="px-2 py-1 rounded bg-gray-100 hover:bg-gray-200 disabled:opacity-40"
                                                wire:click="baseColUp({{ $i }})"
                                                @disabled($i === 0 || (($c['key'] ?? '') === 'id'))
                                                title="Arriba">↑</button>

                                        {{-- ↓ bajar --}}
                                        <button type="button"
                                                class="px-2 py-1 rounded bg-gray-100 hover:bg-gray-200 disabled:opacity-40"
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

                @error('form.base_columnas') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror

                <p class="text-xs text-gray-500 mt-2">
                    Consejo: usa números 1..N para ordenar. El sistema normaliza y asegura que <strong>ID</strong> siempre esté visible.
                </p>
            </div>

            {{-- Contenido: FILTROS --}}
            <div x-show="tab==='filtros'" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-700">Filtros disponibles</label>
                        <input x-model="filtroSearch"
                            class="w-40 rounded-lg border-gray-300 focus:ring-blue-500 text-sm"
                            placeholder="Buscar…">
                    </div>
                        {{-- Filtros disponibles --}}
                        <div class="max-h-72 overflow-y-auto rounded-lg border p-2">
                            @foreach($filtros as $f)
                                <template x-if="'{{ Str::lower($f->nombre) }}'.includes(filtroSearch.toLowerCase())">
                                    <div class="flex items-center justify-between py-1">
                                        <label class="flex items-center gap-2">
                                            <input type="checkbox" class="rounded border-gray-300"
                                                value="{{ $f->id }}"
                                                @checked(in_array($f->id, $filtro_ids))
                                                @change="$event.target.checked
                                                        ? @this.filtro_ids.push({{ $f->id }})
                                                        : @this.filtro_ids = @this.filtro_ids.filter(i => i !== {{ $f->id }})">
                                            <span class="text-sm text-gray-700">{{ $f->nombre }}</span>
                                        </label>

                                        <button
                                            type="button"
                                            class="text-blue-600 hover:underline text-xs"
                                            x-on:click="$wire.dispatch('editar-filtro', { id: {{ $f->id }} })"
                                            title="Editar filtro"
                                        >
                                            Editar
                                        </button>
                                    </div>
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

                                    <div class="flex items-center gap-2">
                                        <button type="button" class="text-blue-600 hover:underline text-xs"
                                            x-on:click="$wire.dispatch('editar-filtro', { id: {{ $fid }} })">
                                            Editar
                                        </button>

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

            {{-- Contenido: ACCESO --}}
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
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Estatus de pedidos permitidos</label>
                            <div class="mt-1 rounded-lg border p-2 max-h-40 overflow-y-auto">
                                @forelse($estados as $e)
                                    <label class="inline-flex items-center gap-2 mr-3 mb-2">
                                        <input type="checkbox" class="rounded border-gray-300"
                                            value="{{ $e['id'] }}"
                                            @checked(in_array($e['id'], $form['estados_permitidos'] ?? []))
                                            @change="$event.target.checked
                                                ? @this.form.estados_permitidos.push({{ $e['id'] }})
                                                : @this.form.estados_permitidos = @this.form.estados_permitidos.filter(x => x !== {{ $e['id'] }})">
                                        <span class="text-sm text-gray-700">{{ $e['nombre'] }}</span>
                                    </label>
                                @empty
                                    <span class="text-sm text-gray-500">No hay estados en el catálogo.</span>
                                @endforelse
                            </div>
                            @error('form.estados_permitidos') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            <p class="text-xs text-gray-500 mt-1">Si no seleccionas ninguno, se mostrarán <em>todos</em> los estatus.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mt-4">Estatus de Diseños permitidos</label>
                            <div class="mt-1 rounded-lg border p-2 max-h-40 overflow-y-auto">
                                @foreach($this->estadosDiseno as $status)
                                    <label class="inline-flex items-center gap-2 mr-3 mb-2">
                                        <input type="checkbox" class="rounded border-gray-300"
                                            value="{{ $status }}"
                                            @checked(in_array($status, $form['estados_diseno_permitidos'] ?? []))
                                            @change="$event.target.checked
                                                    ? @this.form.estados_diseno_permitidos.push('{{ $status }}')
                                                    : @this.form.estados_diseno_permitidos = @this.form.estados_diseno_permitidos.filter(x => x !== '{{ $status }}')">
                                        <span class="text-sm text-gray-700">{{ $status }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('form.estados_diseno_permitidos') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            <p class="text-xs text-gray-500 mt-1">Si no seleccionas ninguno, se mostrarán <em>todos</em> los estatus de diseño.</p>
                        </div>
                    </div>

                </div>
            </div>


            <!-- Contenido: MENÚ -->
            <div x-show="tab==='menu'" class="mt-4 space-y-4">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <!-- Ubicaciones del menú -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">¿En qué menú(s) debe mostrarse?</label>
                        <div class="mt-2 rounded-lg border p-3">
                            @foreach($menusDisponibles as $m)
                                <label class="flex items-center gap-2 py-1">
                                    <input type="checkbox"
                                        class="rounded border-gray-300"
                                        value="{{ $m['key'] }}"
                                        @checked(in_array($m['key'], $form['menu_config']['ubicaciones'] ?? []))
                                        @change="
                                            $event.target.checked
                                            ? @this.form.menu_config.ubicaciones.push('{{ $m['key'] }}')
                                            : @this.form.menu_config.ubicaciones = (@this.form.menu_config.ubicaciones || []).filter(k => k !== '{{ $m['key'] }}')
                                        ">
                                    <span class="text-sm text-gray-700">{{ $m['label'] }}</span>
                                </label>
                            @endforeach
                            @error('form.menu_config.ubicaciones.*') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            @if(empty($menusDisponibles))
                                <p class="text-sm text-gray-500">No hay ubicaciones de menú configuradas.</p>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            Puedes elegir varias ubicaciones. El backoffice usará esta configuración para construir el menú dinámicamente.
                        </p>
                    </div>

                    <!-- Metadatos de menú -->
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Etiqueta en menú (opcional)</label>
                            <input type="text"
                                class="mt-1 w-full rounded-lg border-gray-300 focus:ring-blue-500"
                                placeholder="Ej. Producción / Playeras"
                                wire:model.live="form.menu_config.etiqueta">
                            @error('form.menu_config.etiqueta') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ícono (opcional)</label>
                            <input type="text"
                                class="mt-1 w-full rounded-lg border-gray-300 focus:ring-blue-500"
                                placeholder="Ej. lucide-filter, heroicons:adjustments-vertical"
                                wire:model.live="form.menu_config.icono">
                            @error('form.menu_config.icono') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Orden en el menú</label>
                            <input type="number" min="0"
                                class="mt-1 w-32 rounded-lg border-gray-300 focus:ring-blue-500"
                                wire:model.live="form.menu_config.orden">
                            @error('form.menu_config.orden') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex items-center gap-2">
                            <input id="menu_activo" type="checkbox" class="rounded border-gray-300"
                                wire:model.live="form.menu_config.activo">
                            <label for="menu_activo" class="text-sm text-gray-700">Activo en menú</label>
                            @error('form.menu_config.activo') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-gray-50 p-3">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Vista previa</h4>
                    <div class="flex flex-wrap gap-2">
                        @forelse($form['menu_config']['ubicaciones'] ?? [] as $uk)
                            @php
                                $label = collect($menusDisponibles)->firstWhere('key',$uk)['label'] ?? $uk;
                            @endphp
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                                {{ $label }}
                            </span>
                        @empty
                            <span class="text-sm text-gray-500">Sin ubicaciones seleccionadas.</span>
                        @endforelse
                    </div>
                    <div class="text-xs text-gray-500 mt-2">
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
                <p class="text-sm text-gray-600">
                    Define qué acciones estarán <strong>visibles/habilitadas</strong> en el HojaViewer para esta Hoja.
                </p>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    {{-- Grupo: General --}}
                    <div class="rounded-lg border p-3">
                        <h4 class="text-sm font-semibold text-gray-800 mb-2">General</h4>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Ver detalle</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.ver_detalle">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Selección múltiple</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.seleccion_multiple">
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
                    <div class="rounded-lg border p-3">
                        <h4 class="text-sm font-semibold text-gray-800 mb-2">Acciones individuales</h4>
                        <label class="flex items items-center justify-between py-1">
                            <span class="text-sm text-gray-700">ver detalle</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.ver_detalle">
                        </label>

                        <label class="flex items items-center justify-between py-1">
                            <span class="text-sm text-gray-700">programar pedido</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.programar_pedido">
                        </label>

                        <label class="flex items items-center justify-between py-1">
                            <span class="text-sm text-gray-700">aprobar pedido</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.aprobar_pedido">
                        </label>

                        <label class="flex items items-center justify-between py-1">
                            <span class="text-sm text-gray-700">abrir chat</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.abrir_chat">
                        </label>

                        <label class="flex items items-center justify-between py-1">
                            <span class="text-sm text-gray-700">crear tarea</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.crear_tarea">
                        </label>

                        <label class="flex items items-center justify-between py-1">
                            <span class="text-sm text-gray-700">editar pedido</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.editar_pedido">
                        </label>
                        
                        <label class="flex items items-center justify-between py-1">
                            <span class="text-sm text-gray-700">duplicar pedido</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.duplicar_pedido">
                        </label>

                        <label class="flex items items-center justify-between py-1">
                            <span class="text-sm text-gray-700">eliminar pedido</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.eliminar_pedido">
                        </label>

                        <label class="flex items items-center justify-between py-1">
                            <span class="text-sm text-gray-700">entregar pedido</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.entregar_pedido">
                        </label>

                        <label class="flex items items-center justify-between py-1">
                            <span class="text-sm text-gray-700">cancelar pedido</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.cancelar_pedido">
                        </label>

                        <label class="flex items items-center justify-between py-1">
                            <span class="text-sm text-gray-700">subir archivos</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.subir_archivos">
                        </label>


                        <label class="flex items items-center justify-between py-1">
                            <span class="text-sm text-gray-700">exportar excel</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.exportar_excel">
                        </label>
                        
       
                    </div>

                    {{-- Grupo: Acciones masivas --}}
                    <div class="rounded-lg border p-3">
                        <h4 class="text-sm font-semibold text-gray-800 mb-2">Acciones masivas</h4>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Exportar selección</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.bulk_exportar">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Eliminar selección</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.bulk_eliminar">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Programar selección</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.bulk_programar">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Aprobar selección</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.bulk_aprobar">
                        </label>
                    </div>


                    <div class="rounded-lg border p-3">
                        <h4 class="text-sm font-semibold text-gray-800 mb-2">Edicion en Linea</h4>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Total</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.bulk_edit_total">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Estado del Pedido</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.bulk_edit_estado">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Fecha de Producción</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.bulk_edit_fecha_produccion">
                        </label>
                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Fecha de Embarque</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.bulk_edit_fecha_embarque">
                        </label>

                        <label class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-700">Fecha de Entrega</span>
                            <input type="checkbox" class="rounded border-gray-300" wire:model.live="form.acciones.bulk_edit_fecha_entrega">
                        </label>
                    </div>
                </div>

                <p class="text-xs text-gray-500">El componente <code>HojaViewer</code> debe leer <code>$hoja->acciones_config</code>.</p>
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

    {{-- =========================
    MODAL INLINE: NUEVO FILTRO
    ========================= --}}
    <div x-data="{ open: @entangle('modalFiltroOpen'), tab: @entangle('modalFiltroTab') }"
        x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/40" @click="open=false"></div>

        <div class="relative w-full max-w-5xl bg-white rounded-2xl shadow-xl p-5 sm:p-6">
            {{-- Header --}}
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $filtroEditId ? 'Editar filtro de producción' : 'Nuevo filtro de producción' }}
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">Configura datos, productos y columnas.</p>
                </div>
                <button class="ml-4 text-gray-500 hover:text-gray-700" @click="open=false">✕</button>
            </div>
            {{-- Tabs --}}
            <div class="mt-4 border-b">
                <nav class="flex flex-wrap -mb-px gap-2">
                    <button :class="tab==='datos' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-800'"
                            class="px-3 py-2 border-b-2 text-sm font-medium"
                            @click="tab='datos'">Datos</button>
                    <button :class="tab==='productos' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-800'"
                            class="px-3 py-2 border-b-2 text-sm font-medium"
                            @click="tab='productos'">Productos</button>
                    <button :class="tab==='columnas' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-800'"
                            class="px-3 py-2 border-b-2 text-sm font-medium"
                            @click="tab='columnas'">Columnas</button>
                </nav>
            </div>

            {{-- TAB: DATOS --}}
            <div x-show="tab==='datos'" class="mt-4 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre *</label>
                        <input type="text" wire:model.live="filtroForm.nombre"
                            class="mt-1 w-full rounded-lg border-gray-300 focus:ring-blue-500"
                            placeholder="Ej. Punto de Cruz">
                        @error('filtroForm.nombre') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Slug</label>
                        <input type="text" wire:model.live="filtroForm.slug"
                            class="mt-1 w-full rounded-lg border-gray-300 focus:ring-blue-500"
                            placeholder="se-autogenera-si-lo-dejas-vacío">
                        @error('filtroForm.slug') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Descripción</label>
                    <textarea wire:model.live="filtroForm.descripcion" rows="3"
                            class="mt-1 w-full rounded-lg border-gray-300 focus:ring-blue-500"
                            placeholder="Descripción breve…"></textarea>
                    @error('filtroForm.descripcion') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="flex items-center gap-2">
                        <input id="filtro_visible" type="checkbox" class="rounded border-gray-300"
                            wire:model.live="filtroForm.visible">
                        <label for="filtro_visible" class="text-sm text-gray-700">Visible</label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Orden</label>
                        <input type="number" min="0" wire:model.live="filtroForm.orden"
                            class="mt-1 w-32 rounded-lg border-gray-300 focus:ring-blue-500">
                        @error('filtroForm.orden') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- TAB: PRODUCTOS --}}
            <div x-show="tab==='productos'" class="mt-4">
                <div class="mb-3 flex items-center gap-2">
                    <input type="text" placeholder="Buscar producto…"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        wire:model.live.debounce.300ms="productoSearchFiltro">
                    <span class="text-xs text-gray-500">({{ $productos->count() }} mostrados)</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Selecciona productos</label>
                        <select multiple size="10"
                                class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                wire:model="filtro_producto_ids">
                            @foreach($productos as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->nombre }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Mantén Ctrl/Cmd para selección múltiple.</p>
                        @error('filtro_producto_ids') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="bg-gray-50 rounded-lg p-3">
                        <h4 class="text-sm font-semibold text-gray-700">Seleccionados ({{ count($filtro_producto_ids) }})</h4>
                        <div class="mt-2 text-xs text-gray-600 space-y-1 max-h-48 overflow-auto">
                            @forelse($productos->whereIn('id', $filtro_producto_ids) as $p)
                                <div class="flex items-center justify-between">
                                    <span class="truncate">{{ $p->nombre }}</span>
                                    <button
                                        class="text-red-600 hover:underline"
                                        wire:click="$set('filtro_producto_ids', {{ json_encode(array_values(array_diff($filtro_producto_ids, [$p->id]))) }})"
                                    >quitar</button>
                                </div>
                            @empty
                                <span class="text-gray-400">Sin productos seleccionados.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB: COLUMNAS --}}
            <div x-show="tab==='columnas'" class="mt-4">
                <div class="mb-3 flex items-center gap-2">
                    <input type="text" placeholder="Buscar característica…"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        wire:model.live.debounce.300ms="caracteristicaSearchFiltro">
                    <select
                        class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        x-on:change="$wire.filtroAddCaracteristica(parseInt($event.target.value)); $event.target.value='';"
                    >
                        <option value="">Añadir característica…</option>
                        @foreach($caracteristicas as $car)
                            <option value="{{ $car->id }}">{{ $car->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="overflow-x-auto bg-white rounded-lg border border-gray-200">
                    <table class="min-w-full border-collapse">
                        <thead class="bg-gray-100">
                            <tr class="text-sm text-gray-600">
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
                                <tr class="hover:bg-gray-50 text-sm">
                                    <td class="px-3 py-2 align-top">
                                        <input type="number" class="w-16 rounded border-gray-300"
                                            wire:model="filtro_columnas.{{ $i }}.orden">
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <div class="font-medium text-gray-800">{{ $col['nombre'] }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ $col['caracteristica_id'] }}</div>
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <input type="text" class="w-40 rounded border-gray-300"
                                            wire:model="filtro_columnas.{{ $i }}.label">
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <input type="checkbox"
                                            wire:model="filtro_columnas.{{ $i }}.visible">
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <select class="w-32 rounded border-gray-300"
                                                wire:model="filtro_columnas.{{ $i }}.render">
                                            <option value="texto">Texto</option>
                                            <option value="badges">Badges</option>
                                            <option value="chips">Chips</option>
                                            <option value="iconos">Íconos</option>
                                            <option value="count">Conteo</option>
                                        </select>
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <select class="w-28 rounded border-gray-300"
                                                wire:model="filtro_columnas.{{ $i }}.multivalor_modo">
                                            <option value="inline">Inline</option>
                                            <option value="badges">Badges</option>
                                            <option value="count">Conteo</option>
                                        </select>
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <input type="number" class="w-20 rounded border-gray-300"
                                            wire:model="filtro_columnas.{{ $i }}.max_items" min="1" max="99">
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <input type="text" class="w-28 rounded border-gray-300"
                                            wire:model="filtro_columnas.{{ $i }}.ancho" placeholder="p.ej. w-32">
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <input type="text" class="w-28 rounded border-gray-300"
                                            wire:model="filtro_columnas.{{ $i }}.fallback" placeholder="—">
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <div class="flex items-center gap-1">
                                            <button class="px-2 py-1 rounded bg-gray-200 hover:bg-gray-300"
                                                    @click.prevent="$wire.filtroReorderColumna({{ $i }}, {{ max(0, $i-1) }})">↑</button>
                                            <button class="px-2 py-1 rounded bg-gray-200 hover:bg-gray-300"
                                                    @click.prevent="$wire.filtroReorderColumna({{ $i }}, {{ min(count($filtro_columnas)-1, $i+1) }})">↓</button>
                                            <button class="px-2 py-1 rounded bg-red-500 text-white hover:bg-red-600"
                                                    wire:click="filtroRemoveColumna({{ $i }})">Quitar</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="px-4 py-6 text-center text-gray-500">Aún no hay columnas configuradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <p class="text-xs text-gray-500 mt-2">
                    Consejo: prioriza 3–6 columnas clave para mantener la tabla legible en pantallas pequeñas.
                </p>
            </div>

            {{-- Footer --}}
            <div class="mt-6 flex justify-end gap-2">
                <button class="px-4 py-2 rounded-lg border" @click="open=false">Cancelar</button>

                <button
                    class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700"
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
        class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/40" @click="open=false"></div>

        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl p-5 sm:p-6">
            <h3 class="text-lg font-semibold text-gray-900">Eliminar hoja</h3>
            <p class="mt-2 text-sm text-gray-600">
                Esta acción eliminará la hoja seleccionada. Los filtros asociados <strong>no</strong> se eliminarán,
                solo se romperá la relación con esta hoja. ¿Deseas continuar?
            </p>

            <div class="mt-6 flex justify-end gap-2">
                <button class="px-4 py-2 rounded-lg border" @click="open=false">Cancelar</button>
                <button class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700"
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


<div
    x-data="{ selected: @entangle('selected'), modalOpen: @entangle('modalOpen'), modalTab: @entangle('modalTab') }"
    x-on:filtro-notify.window="console.log($event.detail?.message || '');"
    class="container mx-auto p-6 text-gray-900 dark:text-gray-100"
>
    <!-- Barra superior: búsqueda + acciones -->
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex-1">
            <input
                type="text"
                placeholder="Buscar filtro (nombre, slug, descripción)"
                class="w-full rounded-lg border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:border-blue-400 dark:focus:ring-blue-400"
                wire:model.live.debounce.400ms="search"
            />
        </div>
        <div class="flex flex-wrap gap-2">
            <button
                class="rounded-lg bg-blue-600 px-4 py-2 text-white transition hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400"
                wire:click="openCreate"
            >Nuevo filtro</button>

            <button
                class="rounded-lg bg-emerald-600 px-4 py-2 text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-emerald-500 dark:hover:bg-emerald-400"
                :disabled="selected.length !== 1"
                @click="$wire.openEdit(selected[0])"
            >Editar</button>

            <button
                class="rounded-lg bg-purple-600 px-4 py-2 text-white transition hover:bg-purple-700 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-purple-500 dark:hover:bg-purple-400"
                :disabled="selected.length !== 1"
                @click="$wire.duplicate(selected[0])"
            >Duplicar</button>

            <button
                class="rounded-lg bg-gray-800 px-4 py-2 text-white transition hover:bg-gray-900 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-gray-700 dark:hover:bg-gray-600"
                :disabled="selected.length === 0"
                @click="$wire.toggleVisibleSelected()"
            >Visibilidad ON/OFF</button>

            <button
                class="rounded-lg bg-red-600 px-4 py-2 text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-red-500 dark:hover:bg-red-400"
                :disabled="selected.length === 0"
                @click="$wire.deleteSelected()"
            >Eliminar</button>
        </div>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <table class="min-w-full border-collapse rounded-lg">
            <thead class="bg-gray-100 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-2 text-left">
                        <input type="checkbox"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400"
                               @change="
                                  const checked = $event.target.checked;
                                  const ids = @js($filtros->pluck('id'));
                                  selected = checked ? ids : [];
                               ">
                    </th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-300">ID</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Nombre</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Slug</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Visible</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Orden</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-300"># Productos</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-300"># Columnas</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($filtros as $filtro)
                    <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/70">
                        <td class="px-4 py-2">
                            <input type="checkbox"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400"
                                   :value="{{ $filtro->id }}"
                                   :checked="selected.includes({{ $filtro->id }})"
                                   @change="
                                      const id = {{ $filtro->id }};
                                      if ($event.target.checked) { if (!selected.includes(id)) selected.push(id) }
                                      else { selected = selected.filter(i => i !== id) }
                                   ">
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $filtro->id }}</td>
                        <td class="px-4 py-2 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $filtro->nombre }}</td>
                        <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $filtro->slug }}</td>
                        <td class="px-4 py-2 text-sm">
                            @if($filtro->visible)
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-800 dark:bg-green-900/40 dark:text-green-200">Sí</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-200 px-2 py-0.5 text-xs text-gray-700 dark:bg-gray-700 dark:text-gray-200">No</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $filtro->orden ?? '—' }}</td>
                        <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $filtro->productos_count }}</td>
                        <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $filtro->columnas_count }}</td>
                        <td class="px-4 py-2 text-sm">
                            <button
                                class="rounded bg-blue-500 px-3 py-1 text-white transition hover:bg-blue-600 dark:bg-blue-500 dark:hover:bg-blue-400"
                                wire:click="openEdit({{ $filtro->id }})"
                            >Editar</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">Sin filtros registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-4">
        {{ $filtros->links() }}
    </div>

    <!-- Modal Crear/Editar -->
    <div
        x-show="modalOpen"
        x-transition.opacity
        class="fixed inset-0 z-40 flex items-center justify-center px-4"
        style="display:none"
    >
        <div class="absolute inset-0 bg-black/60" @click="modalOpen=false"></div>

        <div class="relative z-50 max-h-[90vh] w-[95%] overflow-y-auto rounded-2xl bg-white shadow-xl dark:bg-gray-900 dark:ring-1 dark:ring-white/10 sm:w-[900px]">
            <div class="flex items-center justify-between border-b border-gray-200 p-4 dark:border-gray-700 sm:p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                    {{ $editId ? 'Editar filtro' : 'Nuevo filtro' }}
                </h3>
                <button class="text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" @click="modalOpen = false">✕</button>
            </div>

            <!-- Tabs -->
            <div class="px-4 sm:px-6 pt-4">
                <div class="flex flex-wrap gap-2">
                    <button
                        class="px-3 py-1.5 rounded-lg border"
                        :class="modalTab==='datos' ? 'bg-blue-600 text-white border-blue-600 dark:bg-blue-500 dark:border-blue-500' : 'bg-white text-gray-700 border-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600'"
                        @click="modalTab='datos'"
                    >Datos</button>

                    <button
                        class="px-3 py-1.5 rounded-lg border"
                        :class="modalTab==='productos' ? 'bg-blue-600 text-white border-blue-600 dark:bg-blue-500 dark:border-blue-500' : 'bg-white text-gray-700 border-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600'"
                        @click="modalTab='productos'"
                    >Productos</button>

                    <button
                        class="px-3 py-1.5 rounded-lg border"
                        :class="modalTab==='columnas' ? 'bg-blue-600 text-white border-blue-600 dark:bg-blue-500 dark:border-blue-500' : 'bg-white text-gray-700 border-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600'"
                        @click="modalTab='columnas'"
                    >Columnas</button>
                </div>
            </div>

            <!-- Contenido -->
            <div class="p-4 sm:p-6 space-y-6">
                <!-- TAB: Datos -->
                <div x-show="modalTab==='datos'">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nombre</label>
                            <input type="text" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400"
                                   wire:model.live="form.nombre">
                            @error('form.nombre') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Slug (opcional)</label>
                            <input type="text" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400"
                                   wire:model.live="form.slug">
                            @error('form.slug') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Visible</label>
                            <select class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400"
                                    wire:model="form.visible">
                                <option value="1">Sí</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Orden</label>
                            <input type="number" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400"
                                   wire:model="form.orden" placeholder="Ej. 10">
                            @error('form.orden') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Descripción</label>
                            <textarea rows="3" class="mt-1 w-full rounded-lg border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400"
                                      wire:model="form.descripcion"></textarea>
                            @error('form.descripcion') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- TAB: Productos -->
                <!-- TAB: Productos -->
                <div x-show="modalTab==='productos'">
                    <div class="mb-3 flex flex-col sm:flex-row sm:items-center gap-2">
                        <input
                            type="text"
                            placeholder="Buscar producto…"
                            class="w-full rounded-lg border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:border-blue-400 dark:focus:ring-blue-400"
                            wire:model.live.debounce.300ms="productoSearch"
                        />

                        <div class="flex gap-2">
                            <button
                                type="button"
                                class="rounded-lg bg-gray-200 px-3 py-2 text-gray-800 transition hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
                                wire:click="clearProductos"
                                @disabled(count($producto_ids) === 0)
                            >
                                Limpiar
                            </button>

                            <button
                                type="button"
                                class="rounded-lg bg-blue-600 px-3 py-2 text-white transition hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400"
                                wire:click="addAllFilteredProductos"
                                @disabled($productos->count() === 0)
                            >
                                Añadir todos los mostrados
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <!-- Panel izquierdo: catálogo / resultados -->
                        <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                            <div class="flex items-center justify-between border-b border-gray-200 p-3 dark:border-gray-700">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                    Resultados ({{ $productos->count() }})
                                </h4>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    Click en “Añadir” para agregarlos
                                </span>
                            </div>

                            <div class="max-h-[360px] overflow-auto divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($productos as $prod)
                                    <div class="p-3 flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="truncate text-sm font-medium text-gray-800 dark:text-gray-100">
                                                {{ $prod->nombre }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $prod->id }}</div>
                                        </div>

                                        @php($ya = in_array($prod->id, $producto_ids, true))

                                        <button
                                            type="button"
                                            class="shrink-0 px-3 py-1.5 rounded-lg text-sm
                                                {{ $ya ? 'cursor-default bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200' : 'bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400' }}"
                                            wire:click="addProducto({{ $prod->id }})"
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
                                Tip: puedes buscar y añadir de uno en uno sin Ctrl/Cmd 👍
                            </div>
                        </div>

                        <!-- Panel derecho: seleccionados -->
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800/60">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                    Seleccionados ({{ count($producto_ids) }})
                                </h4>

                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        class="rounded border border-gray-300 bg-white px-2 py-1 text-xs text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                                        wire:click="sortProductosSelected"
                                        @disabled(count($producto_ids) < 2)
                                    >
                                        Ordenar A–Z
                                    </button>
                                </div>
                            </div>

                            <div class="mt-2 max-h-[360px] overflow-auto space-y-2">
                                @forelse($productosSeleccionados as $pSel)
                                    <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white px-3 py-2 dark:border-gray-700 dark:bg-gray-900">
                                        <div class="min-w-0">
                                            <div class="truncate text-sm text-gray-800 dark:text-gray-100">{{ $pSel->nombre }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $pSel->id }}</div>
                                        </div>

                                        <button
                                            type="button"
                                            class="text-sm text-red-600 hover:underline dark:text-red-400"
                                            wire:click="removeProducto({{ $pSel->id }})"
                                        >
                                            quitar
                                        </button>
                                    </div>
                                @empty
                                    <div class="text-sm text-gray-400 dark:text-gray-500">
                                        Aún no hay productos seleccionados.
                                    </div>
                                @endforelse
                            </div>

                            @error('producto_ids') <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- TAB: Columnas -->
                <div x-show="modalTab==='columnas'">
                    <div class="mb-3 flex items-center gap-2">
                        <input type="text" placeholder="Buscar característica…"
                               class="w-full rounded-lg border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:border-blue-400 dark:focus:ring-blue-400"
                               wire:model.live.debounce.300ms="caracteristicaSearch">
                        <select
                            class="rounded-lg border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400"
                            x-on:change="$wire.addCaracteristica(parseInt($event.target.value)); $event.target.value='';"
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
                                @forelse($columnas as $i => $col)
                                    <tr class="text-sm transition hover:bg-gray-50 dark:hover:bg-gray-800/70">
                                        <td class="px-3 py-2 align-top">
                                            <input type="number" class="w-16 rounded border-gray-300 bg-white text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                                   wire:model="columnas.{{ $i }}.orden">
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <div class="font-medium text-gray-800 dark:text-gray-100">{{ $col['nombre'] }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $col['caracteristica_id'] }}</div>
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <input type="text" class="w-40 rounded border-gray-300 bg-white text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                                   wire:model="columnas.{{ $i }}.label">
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-400 dark:focus:ring-blue-400"
                                                   wire:model="columnas.{{ $i }}.visible">
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <select class="w-32 rounded border-gray-300 bg-white text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                                    wire:model="columnas.{{ $i }}.render">
                                                <option value="texto">Texto</option>
                                                <option value="badges">Badges</option>
                                                <option value="chips">Chips</option>
                                                <option value="iconos">Íconos</option>
                                                <option value="count">Conteo</option>
                                            </select>
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <select class="w-28 rounded border-gray-300 bg-white text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                                    wire:model="columnas.{{ $i }}.multivalor_modo">
                                                <option value="inline">Inline</option>
                                                <option value="badges">Badges</option>
                                                <option value="count">Conteo</option>
                                            </select>
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <input type="number" class="w-20 rounded border-gray-300 bg-white text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                                   wire:model="columnas.{{ $i }}.max_items" min="1" max="99">
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <input type="text" class="w-28 rounded border-gray-300 bg-white text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                                   wire:model="columnas.{{ $i }}.ancho" placeholder="p.ej. w-32">
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <input type="text" class="w-28 rounded border-gray-300 bg-white text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                                   wire:model="columnas.{{ $i }}.fallback" placeholder="—">
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <div class="flex items-center gap-1">
                                                <button class="rounded bg-gray-200 px-2 py-1 text-gray-700 transition hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
                                                        @click.prevent="$wire.reorderColumna({{ $i }}, {{ max(0, $i-1) }})">↑</button>
                                                <button class="rounded bg-gray-200 px-2 py-1 text-gray-700 transition hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
                                                        @click.prevent="$wire.reorderColumna({{ $i }}, {{ min(count($columnas)-1, $i+1) }})">↓</button>
                                                <button class="rounded bg-red-500 px-2 py-1 text-white transition hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-500"
                                                        wire:click="removeColumna({{ $i }})">Quitar</button>
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
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-2 border-t border-gray-200 px-4 py-4 dark:border-gray-700 sm:px-6">
                <button class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800"
                        @click="modalOpen=false">Cancelar</button>
                <button class="rounded-lg bg-blue-600 px-4 py-2 text-white transition hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400"
                        wire:click="save">Guardar</button>
            </div>
        </div>
    </div>
</div>

{{-- Scripts encapsulados --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Aquí podrías registrar toasts personalizados, etc.
    window.addEventListener('filtro-notify', (e) => {
        // Ejemplo simple: notificar en consola; reemplaza con tu toast
        console.log('Toast:', e.detail?.message || '');
    });
});
</script>

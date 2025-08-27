<div
    x-data="{ selected: @entangle('selected'), modalOpen: @entangle('modalOpen'), modalTab: @entangle('modalTab') }"
    x-on:filtro-notify.window="console.log($event.detail?.message || '');"
    class="container mx-auto p-6"
>
    <!-- Barra superior: búsqueda + acciones -->
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex-1">
            <input
                type="text"
                placeholder="Buscar filtro (nombre, slug, descripción)"
                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                wire:model.live.debounce.400ms="search"
            />
        </div>
        <div class="flex flex-wrap gap-2">
            <button
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                wire:click="openCreate"
            >Nuevo filtro</button>

            <button
                class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="selected.length !== 1"
                @click="$wire.openEdit(selected[0])"
            >Editar</button>

            <button
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="selected.length !== 1"
                @click="$wire.duplicate(selected[0])"
            >Duplicar</button>

            <button
                class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="selected.length === 0"
                @click="$wire.toggleVisibleSelected()"
            >Visibilidad ON/OFF</button>

            <button
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="selected.length === 0"
                @click="$wire.deleteSelected()"
            >Eliminar</button>
        </div>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full border-collapse border border-gray-200 rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left">
                        <input type="checkbox"
                               @change="
                                  const checked = $event.target.checked;
                                  const ids = @js($filtros->pluck('id'));
                                  selected = checked ? ids : [];
                               ">
                    </th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">ID</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Nombre</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Slug</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Visible</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Orden</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600"># Productos</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600"># Columnas</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($filtros as $filtro)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">
                            <input type="checkbox"
                                   :value="{{ $filtro->id }}"
                                   :checked="selected.includes({{ $filtro->id }})"
                                   @change="
                                      const id = {{ $filtro->id }};
                                      if ($event.target.checked) { if (!selected.includes(id)) selected.push(id) }
                                      else { selected = selected.filter(i => i !== id) }
                                   ">
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-700">{{ $filtro->id }}</td>
                        <td class="px-4 py-2 text-sm text-gray-800 font-medium">{{ $filtro->nombre }}</td>
                        <td class="px-4 py-2 text-sm text-gray-600">{{ $filtro->slug }}</td>
                        <td class="px-4 py-2 text-sm">
                            @if($filtro->visible)
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-green-800 text-xs">Sí</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-200 px-2 py-0.5 text-gray-700 text-xs">No</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-700">{{ $filtro->orden ?? '—' }}</td>
                        <td class="px-4 py-2 text-sm text-gray-700">{{ $filtro->productos_count }}</td>
                        <td class="px-4 py-2 text-sm text-gray-700">{{ $filtro->columnas_count }}</td>
                        <td class="px-4 py-2 text-sm">
                            <button
                                class="px-3 py-1 rounded bg-blue-500 text-white hover:bg-blue-600"
                                wire:click="openEdit({{ $filtro->id }})"
                            >Editar</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-6 text-center text-sm text-gray-500">Sin filtros registrados.</td></tr>
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
        class="fixed inset-0 z-40 flex items-center justify-center"
        style="display:none"
    >
        <div class="absolute inset-0 bg-black/40" @click="modalOpen=false"></div>

        <div class="relative z-50 w-[95%] sm:w-[900px] max-h-[90vh] overflow-y-auto bg-white rounded-2xl shadow-xl">
            <div class="p-4 sm:p-6 border-b flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">
                    {{ $editId ? 'Editar filtro' : 'Nuevo filtro' }}
                </h3>
                <button class="text-gray-500 hover:text-gray-700" @click="modalOpen = false">✕</button>
            </div>

            <!-- Tabs -->
            <div class="px-4 sm:px-6 pt-4">
                <div class="flex flex-wrap gap-2">
                    <button
                        class="px-3 py-1.5 rounded-lg border"
                        :class="modalTab==='datos' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300'"
                        @click="modalTab='datos'"
                    >Datos</button>

                    <button
                        class="px-3 py-1.5 rounded-lg border"
                        :class="modalTab==='productos' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300'"
                        @click="modalTab='productos'"
                    >Productos</button>

                    <button
                        class="px-3 py-1.5 rounded-lg border"
                        :class="modalTab==='columnas' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300'"
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
                            <label class="block text-sm font-medium text-gray-700">Nombre</label>
                            <input type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                   wire:model.live="form.nombre">
                            @error('form.nombre') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Slug (opcional)</label>
                            <input type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                   wire:model.live="form.slug">
                            @error('form.slug') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Visible</label>
                            <select class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    wire:model="form.visible">
                                <option value="1">Sí</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Orden</label>
                            <input type="number" class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                   wire:model="form.orden" placeholder="Ej. 10">
                            @error('form.orden') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Descripción</label>
                            <textarea rows="3" class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                      wire:model="form.descripcion"></textarea>
                            @error('form.descripcion') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- TAB: Productos -->
                <div x-show="modalTab==='productos'">
                    <div class="mb-3 flex items-center gap-2">
                        <input type="text" placeholder="Buscar producto…"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                               wire:model.live.debounce.300ms="productoSearch">
                        <span class="text-xs text-gray-500">({{ $productos->count() }} mostrados)</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Selecciona productos</label>
                            <select multiple size="10"
                                    class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    wire:model="producto_ids">
                                @foreach($productos as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Mantén Ctrl/Cmd para selección múltiple.</p>
                            @error('producto_ids') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="bg-gray-50 rounded-lg p-3">
                            <h4 class="text-sm font-semibold text-gray-700">Seleccionados ({{ count($producto_ids) }})</h4>
                            <div class="mt-2 text-xs text-gray-600 space-y-1 max-h-48 overflow-auto">
                                @forelse($productos->whereIn('id', $producto_ids) as $p)
                                    <div class="flex items-center justify-between">
                                        <span class="truncate">{{ $p->nombre }}</span>
                                        <button
                                            class="text-red-600 hover:underline"
                                            wire:click="$set('producto_ids', {{ json_encode(array_values(array_diff($producto_ids, [$p->id]))) }})"
                                        >quitar</button>
                                    </div>
                                @empty
                                    <span class="text-gray-400">Aún no hay productos seleccionados.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB: Columnas -->
                <div x-show="modalTab==='columnas'">
                    <div class="mb-3 flex items-center gap-2">
                        <input type="text" placeholder="Buscar característica…"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                               wire:model.live.debounce.300ms="caracteristicaSearch">
                        <select
                            class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            x-on:change="$wire.addCaracteristica(parseInt($event.target.value)); $event.target.value='';"
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
                                @forelse($columnas as $i => $col)
                                    <tr class="hover:bg-gray-50 text-sm">
                                        <td class="px-3 py-2 align-top">
                                            <input type="number" class="w-16 rounded border-gray-300"
                                                   wire:model="columnas.{{ $i }}.orden">
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <div class="font-medium text-gray-800">{{ $col['nombre'] }}</div>
                                            <div class="text-xs text-gray-500">ID: {{ $col['caracteristica_id'] }}</div>
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <input type="text" class="w-40 rounded border-gray-300"
                                                   wire:model="columnas.{{ $i }}.label">
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <input type="checkbox"
                                                   wire:model="columnas.{{ $i }}.visible">
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <select class="w-32 rounded border-gray-300"
                                                    wire:model="columnas.{{ $i }}.render">
                                                <option value="texto">Texto</option>
                                                <option value="badges">Badges</option>
                                                <option value="chips">Chips</option>
                                                <option value="iconos">Íconos</option>
                                                <option value="count">Conteo</option>
                                            </select>
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <select class="w-28 rounded border-gray-300"
                                                    wire:model="columnas.{{ $i }}.multivalor_modo">
                                                <option value="inline">Inline</option>
                                                <option value="badges">Badges</option>
                                                <option value="count">Conteo</option>
                                            </select>
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <input type="number" class="w-20 rounded border-gray-300"
                                                   wire:model="columnas.{{ $i }}.max_items" min="1" max="99">
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <input type="text" class="w-28 rounded border-gray-300"
                                                   wire:model="columnas.{{ $i }}.ancho" placeholder="p.ej. w-32">
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <input type="text" class="w-28 rounded border-gray-300"
                                                   wire:model="columnas.{{ $i }}.fallback" placeholder="—">
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <div class="flex items-center gap-1">
                                                <button class="px-2 py-1 rounded bg-gray-200 hover:bg-gray-300"
                                                        @click.prevent="$wire.reorderColumna({{ $i }}, {{ max(0, $i-1) }})">↑</button>
                                                <button class="px-2 py-1 rounded bg-gray-200 hover:bg-gray-300"
                                                        @click.prevent="$wire.reorderColumna({{ $i }}, {{ min(count($columnas)-1, $i+1) }})">↓</button>
                                                <button class="px-2 py-1 rounded bg-red-500 text-white hover:bg-red-600"
                                                        wire:click="removeColumna({{ $i }})">Quitar</button>
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
            </div>

            <!-- Footer -->
            <div class="px-4 sm:px-6 py-4 border-t flex justify-end gap-2">
                <button class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100"
                        @click="modalOpen=false">Cancelar</button>
                <button class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700"
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

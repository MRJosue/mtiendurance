<div class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/80">
    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <label class="block text-sm font-semibold text-slate-800 dark:text-slate-100">Características y Opciones</label>
            <p class="text-xs text-slate-500 dark:text-slate-400">Selecciona las opciones necesarias para cada característica del producto.</p>
        </div>
        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
            {{ count($caracteristicas_sel) }} características
        </span>
    </div>

    <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
        @foreach ($caracteristicas_sel as $index => $caracteristica)
            <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3 dark:border-slate-700 dark:bg-slate-800/70">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $caracteristica['nombre'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ count($caracteristicaOpcionesDisponibles[$caracteristica['id']] ?? []) === 1 ? 'Opción asignada automáticamente' : 'Selecciona una o varias opciones' }}
                        </p>
                    </div>
                    <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-[11px] font-medium text-slate-500 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-400 dark:ring-slate-700">
                        {{ count($caracteristica['opciones'] ?? []) }} seleccionadas
                    </span>
                </div>

                @if (count($caracteristicaOpcionesDisponibles[$caracteristica['id']] ?? []) === 1)
                    <div class="mt-3 inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700">
                        {{ $caracteristicaOpcionesDisponibles[$caracteristica['id']][0]['nombre'] }} ({{ $caracteristicaOpcionesDisponibles[$caracteristica['id']][0]['valoru'] }})
                    </div>
                @else
                    <div class="mt-3">
                        <select
                            wire:key="prod-{{ $producto_id }}-car-{{ $index }}"
                            wire:change="addOpcion({{ $index }}, $event.target.value)"
                            class="w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                        >
                            <option value="">Seleccionar opción</option>
                            @foreach($caracteristicaOpcionesDisponibles[$caracteristica['id']] ?? [] as $opcion)
                                <option value="{{ $opcion['id'] }}">
                                    {{ $opcion['nombre'] }} ({{ $opcion['valoru'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(!empty($caracteristica['opciones']))
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($caracteristica['opciones'] as $opcionIndex => $opcion)
                                <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-xs font-medium text-slate-700 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-700">
                                    <span>{{ $opcion['nombre'] }} ({{ $opcion['valoru'] }})</span>
                                    <button
                                        type="button"
                                        wire:click="removeOpcion({{ $index }}, {{ $opcionIndex }})"
                                        class="rounded-full bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-600 hover:bg-rose-100"
                                    >
                                        Quitar
                                    </button>
                                </span>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-3 space-y-1">
        @error('opciones_sel')
            <span class="block text-sm text-red-600">{{ $message }}</span>
        @enderror

        @error('caracteristicas_sel')
            <span class="block text-sm text-red-600">{{ $message }}</span>
        @enderror
    </div>
</div>

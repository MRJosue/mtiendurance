<div wire:key="tallas-{{ $producto_id }}" class="mb-5 space-y-4">
    @if ($mostrarFormularioTallas)
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/80">
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Cantidad por Tallas</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Captura cantidades por grupo de tallas de forma rápida.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                    {{ $tallas->count() }} tallas disponibles
                </span>
            </div>

            <div class="space-y-4">
                @foreach ($tallas->flatMap->gruposTallas->unique('id') as $grupoTalla)
                    <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3 dark:border-slate-700 dark:bg-slate-800/70">
                        <p class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $grupoTalla->nombre }}</p>

                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach ($tallas->filter(fn($talla) => $talla->gruposTallas->contains('id', $grupoTalla->id)) as $talla)
                                <label class="flex items-center justify-between gap-3 rounded-xl bg-white px-3 py-2 ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700">
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $talla->nombre }}</span>
                                    <input
                                        type="number"
                                        wire:model.defer="tallasSeleccionadas.{{ $grupoTalla->id }}.{{ $talla->id }}"
                                        class="w-24 rounded-lg border-slate-300 bg-white px-3 py-1.5 text-right text-sm text-slate-700 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                                        min="0"
                                        value="{{ $tallasSeleccionadas[$grupoTalla->id][$talla->id] ?? 0 }}"
                                    >
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/80">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div class="flex-1">
                <label class="block text-sm font-semibold text-slate-800 dark:text-slate-100">Total de Piezas</label>
                <p class="text-xs text-slate-500 dark:text-slate-400">Resumen total esperado para este preproyecto.</p>
                <input
                    type="number"
                    wire:model="total_piezas"
                    class="mt-2 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                    min="1"
                >
            </div>
            <div class="rounded-xl bg-slate-100 px-4 py-3 text-center sm:min-w-[150px] dark:bg-slate-800">
                <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Total actual</p>
                <p class="text-2xl font-semibold text-slate-800 dark:text-slate-100">{{ $total_piezas ?: 0 }}</p>
            </div>
        </div>
        @error('total_piezas') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
    </div>
</div>

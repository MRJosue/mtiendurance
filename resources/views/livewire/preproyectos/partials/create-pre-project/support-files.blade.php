<div class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <label class="block text-sm font-semibold text-slate-800">Archivos de apoyo</label>
            <p class="text-xs text-slate-500">Sube referencias, artes, documentos o paquetes comprimidos.</p>
        </div>
        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
            ZIP, imágenes, PDF, Office
        </span>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
        <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Seleccionar archivos</label>
            <input
                type="file"
                wire:model="files"
                multiple
                accept=".zip,.jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx"
                class="mt-2 w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
            @error('files.*')
                <span class="mt-2 block text-sm text-red-600">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex items-center gap-3">
            <button
                type="button"
                wire:click="procesarArchivos"
                wire:loading.attr="disabled"
                class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
            >
                Procesar archivos
            </button>

            <span wire:loading wire:target="procesarArchivos" class="inline-flex items-center text-sm text-blue-600">
                Subiendo…
                <svg class="ml-2 inline h-4 w-4 animate-spin" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
            </span>
        </div>
    </div>

    @if ($uploadedFiles)
        <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50/80 p-3">
            <div class="mb-3 flex items-center justify-between gap-3">
                <h3 class="text-sm font-semibold text-slate-800">Vista Previa de Archivos</h3>
                <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-200">
                    {{ count($uploadedFiles) }} cargados
                </span>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($uploadedFiles as $file)
                    <div class="flex items-center gap-3 rounded-xl bg-white p-3 ring-1 ring-slate-200">
                        @if ($file['preview'])
                            <img src="{{ $file['preview'] }}" class="h-14 w-14 rounded-lg object-cover">
                        @else
                            <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                        @endif

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-700">{{ $file['name'] }}</p>
                            @if (!$file['preview'])
                                <a href="{{ $file['preview'] }}" target="_blank" class="text-xs font-medium text-blue-600 hover:underline">
                                    Abrir archivo
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

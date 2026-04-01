<div> 
    @if (!$tieneAprobado || is_null($registro))
        <div class="project-muted-card px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
            El proyecto aún no ha sido aprobado.
        </div>
    @else
        <div class="project-card w-full p-4 sm:p-5">
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-3 border-b border-gray-200 pb-3 dark:border-gray-700 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-600 dark:text-emerald-300">
                            Estado del diseño
                        </p>
                        <h3 class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Diseño aprobado
                        </h3>
                    </div>

                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-400/30">
                        {{ \Carbon\Carbon::parse($registro->fecha_inicio)->format('d/m/Y H:i') }}
                    </span>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-gray-200/80 bg-gray-50/80 px-4 py-3 dark:border-gray-700 dark:bg-gray-800/70">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                            Aprobado por
                        </p>
                        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">
                            {{ $registro->usuario->name ?? 'Desconocido' }}
                        </p>
                    </div>

                    @if($archivoProyecto)
                        <div class="rounded-xl border border-gray-200/80 bg-gray-50/80 px-4 py-3 dark:border-gray-700 dark:bg-gray-800/70">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                                Archivo asociado
                            </p>
                            <p class="mt-1 truncate text-sm font-medium text-gray-800 dark:text-gray-100" title="{{ $archivoProyecto->nombre_archivo }}">
                                {{ $archivoProyecto->nombre_archivo }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Version {{ $archivoProyecto->version }}
                            </p>
                        </div>
                    @endif
                </div>

                @if(!empty($registro->comentario))
                    <div class="rounded-xl border border-gray-200/80 bg-white/80 px-4 py-3 dark:border-gray-700 dark:bg-gray-900/50">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                            Comentario
                        </p>
                        <p class="mt-2 text-sm leading-6 text-gray-700 dark:text-gray-200">
                            {{ $registro->comentario }}
                        </p>
                    </div>
                @endif

                @if(!empty($registro->url))
                    <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50/70 px-4 py-3 dark:border-gray-600 dark:bg-gray-800/40">
                        <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                            Archivo adjunto
                        </p>
                        <livewire:proyectos.pdf-aprobacion :proyecto-id="$proyectoId" />
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

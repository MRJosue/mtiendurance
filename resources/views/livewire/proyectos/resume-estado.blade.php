<div> 
    @if (!$tieneAprobado || is_null($registro))
        <div class="project-muted-card text-center text-gray-500 dark:text-gray-400">
            El proyecto aún no ha sido aprobado.
        </div>
    @else
        <div class="project-card w-full">
            <h3 class="mb-6 border-b border-gray-200 pb-2 text-center text-2xl font-semibold dark:border-gray-700">
                Resumen de Aprobación de Diseño
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Fecha de aprobación -->
                <div class="space-y-1">
                    <p class="text-sm font-medium uppercase text-gray-600 dark:text-gray-400">Fecha de aprobación</p>
                    <p class="text-lg font-medium text-gray-800 dark:text-gray-100">
                        {{ \Carbon\Carbon::parse($registro->fecha_inicio)->format('d-m-Y H:i') }}
                    </p>
                </div>

                <!-- Usuario aprobador -->
                <div class="space-y-1">
                    <p class="text-sm font-medium uppercase text-gray-600 dark:text-gray-400">Aprobado por</p>
                    <p class="text-lg font-medium text-gray-800 dark:text-gray-100">
                        {{ $registro->usuario->name ?? 'Desconocido' }}
                    </p>
                </div>

                <!-- Archivo asociado: nombre y versión -->
                @if($archivoProyecto)
                    <div class="md:col-span-2 space-y-1">
                        <p class="text-sm font-medium uppercase text-gray-600 dark:text-gray-400">Archivo asociado</p>
                        <div class="flex flex-col sm:flex-row sm:space-x-6">
                            <div class="space-y-1">
                                <p class="text-sm font-medium uppercase text-gray-600 dark:text-gray-400">Nombre</p>
                                <p class="text-lg text-gray-800 dark:text-gray-100">
                                    {{ $archivoProyecto->nombre_archivo }}
                                </p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium uppercase text-gray-600 dark:text-gray-400">Versión</p>
                                <p class="text-lg text-gray-800 dark:text-gray-100">
                                    {{ $archivoProyecto->version }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Comentarios (si existen) -->
                @if(!empty($registro->comentario))
                    <div class="md:col-span-2 space-y-1">
                        <p class="text-sm font-medium uppercase text-gray-600 dark:text-gray-400">Comentarios</p>
                        <p class="text-lg text-gray-700 dark:text-gray-200">{{ $registro->comentario }}</p>
                    </div>
                @endif

                <!-- Enlace al archivo original (si existe URL) -->
                @if(!empty($registro->url))
                    <div class="md:col-span-2 space-y-1">
                        <p class="text-sm font-medium uppercase text-gray-600 dark:text-gray-400">Archivo adjunto</p>
                        <livewire:proyectos.pdf-aprobacion :proyecto-id="$proyectoId" />
                    </div>


                @endif
            </div>
        </div>
    @endif
</div>

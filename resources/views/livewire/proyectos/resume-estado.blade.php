<div> 
    @if (!$tieneAprobado || is_null($registro))
        <div class="p-4 text-gray-500 text-center">
            El proyecto aún no ha sido aprobado.
        </div>
    @else
        <div class="container mx-auto p-6 bg-white rounded-2xl shadow-lg">
            <h3 class="text-2xl font-semibold mb-6 text-center border-b pb-2">
                Resumen de Aprobación de Diseño
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Fecha de aprobación -->
                <div class="space-y-1">
                    <p class="text-sm font-medium text-gray-600 uppercase">Fecha de aprobación</p>
                    <p class="text-lg font-medium text-gray-800">
                        {{ \Carbon\Carbon::parse($registro->fecha_inicio)->format('d-m-Y H:i') }}
                    </p>
                </div>

                <!-- Usuario aprobador -->
                <div class="space-y-1">
                    <p class="text-sm font-medium text-gray-600 uppercase">Aprobado por</p>
                    <p class="text-lg font-medium text-gray-800">
                        {{ $registro->usuario->name ?? 'Desconocido' }}
                    </p>
                </div>

                <!-- Archivo asociado: nombre y versión -->
                @if($archivoProyecto)
                    <div class="md:col-span-2 space-y-1">
                        <p class="text-sm font-medium text-gray-600 uppercase">Archivo asociado</p>
                        <div class="flex flex-col sm:flex-row sm:space-x-6">
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-gray-600 uppercase">Nombre</p>
                                <p class="text-lg text-gray-800">
                                    {{ $archivoProyecto->nombre_archivo }}
                                </p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-gray-600 uppercase">Versión</p>
                                <p class="text-lg text-gray-800">
                                    {{ $archivoProyecto->version }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Comentarios (si existen) -->
                @if(!empty($registro->comentario))
                    <div class="md:col-span-2 space-y-1">
                        <p class="text-sm font-medium text-gray-600 uppercase">Comentarios</p>
                        <p class="text-lg text-gray-700">{{ $registro->comentario }}</p>
                    </div>
                @endif

                <!-- Enlace al archivo original (si existe URL) -->
                @if(!empty($registro->url))
                    <div class="md:col-span-2 space-y-1">
                        <p class="text-sm font-medium text-gray-600 uppercase">Archivo adjunto</p>
                        <livewire:proyectos.pdf-aprobacion :proyecto-id="$proyectoId" />
                    </div>


                @endif
            </div>
        </div>
    @endif
</div>

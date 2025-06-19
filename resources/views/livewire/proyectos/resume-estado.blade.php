<div>
  
@if (!$tieneAprobado)
    <div class="p-4 text-gray-500 text-center">
        El proyecto aún no ha sido aprobado.
    </div>
@else
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-2 m-2">
        <h3 class="text-xl font-semibold mb-4 text-center">Resumen de Aprobación de Diseño</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <p class="text-sm font-medium">Fecha de aprobación:</p>
                <p class="text-lg">{{ \Carbon\Carbon::parse($registro->fecha_inicio)->format('d-m-Y H:i') }}</p>
            </div>
            <div>
                <p class="text-sm font-medium">Aprobado por:</p>
                <p class="text-lg">{{ $registro->usuario->name ?? 'Desconocido' }}</p>
            </div>
            @if(!empty($registro->comentario))
            <div class="sm:col-span-2">
                <p class="text-sm font-medium">Comentarios:</p>
                <p class="text-lg">{{ $registro->comentario }}</p>
            </div>
            @endif
            @if(!empty($registro->url))
            <div class="sm:col-span-2">
                <p class="text-sm font-medium">Archivo adjunto:</p>
                <a href="{{ asset('storage/' . $registro->url) }}" target="_blank" class="text-blue-500 hover:underline">
                    Ver archivo
                </a>
            </div>
            @endif
        </div>
    </div>
@endif

</div>

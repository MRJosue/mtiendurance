@php
    use Illuminate\Support\Str;
    // Carga dinámicamente CSS compilado por Vite
    $cssFiles = glob(public_path('build/assets/*.css'));
    $appCss   = collect($cssFiles)->first(fn($f) => Str::contains(basename($f), 'app-'));

    // Prepara imagen Base64
    $imgData = null;
    if (!empty($rutaArchivo) && file_exists($rutaArchivo)) {
        $type    = pathinfo($rutaArchivo, PATHINFO_EXTENSION);
        $data    = file_get_contents($rutaArchivo);
        $base64  = base64_encode($data);
        $imgData = "data:image/{$type};base64,{$base64}";
    }

    // Decode detalles de proyecto
    $categoria = is_array($proyecto->categoria_sel)
        ? $proyecto->categoria_sel
        : json_decode($proyecto->categoria_sel, true) ?? [];
    $producto = is_array($proyecto->producto_sel)
        ? $proyecto->producto_sel
        : json_decode($proyecto->producto_sel, true) ?? [];
    $caracteristicas = is_array($proyecto->caracteristicas_sel)
        ? $proyecto->caracteristicas_sel
        : json_decode($proyecto->caracteristicas_sel, true) ?? [];
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Aprobación Proyecto {{ $proyecto->id }}</title>
    @if($appCss)
    <style>{!! file_get_contents($appCss) !!}</style>
    @endif
</head>
<body class="p-4 font-sans text-gray-800 bg-white">
    <div class="max-w-3xl mx-auto">
        <header class="text-center mb-4">
            <h1 class="text-2xl font-bold">Resumen de Aprobación</h1>
            <p class="text-sm text-gray-600">Proyecto ID: {{ $proyecto->id }}</p>
        </header>

        <main class="space-y-4">
            {{-- Imagen de diseño --}}
            @if ($imgData)
            <div class="text-center">
                <img src="{{ $imgData }}" alt="Diseño proyecto {{ $proyecto->id }}" class="mx-auto rounded-lg border" style="max-width:80%;" />
            </div>
            @endif

            {{-- Datos de Aprobación --}}
            <section class="rounded-lg p-3 border">
                <div class="flex justify-between text-sm">
                    <div><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($registro->fecha_inicio)->format('d-m-Y H:i') }}</div>
                    <div><strong>Aprobado por:</strong> {{ $registro->usuario->name ?? 'Desconocido' }}</div>
                </div>
                @if(!empty($registro->comentario))
                <div class="mt-2 text-sm">
                    <strong>Comentarios:</strong> {{ $registro->comentario }}
                </div>
                @endif
            </section>

            {{-- Detalles del Proyecto --}}
            <section class="rounded-lg p-3 border">
                <h2 class="text-lg font-semibold mb-2">Detalles del Proyecto</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                    <div><strong>Cliente:</strong> {{ $proyecto->user->name ?? '—' }}</div>
                    <div><strong>Nombre:</strong> {{ $proyecto->nombre ?? '—' }}</div>
                    <div class="sm:col-span-2"><strong>Descripción:</strong> {{ $proyecto->descripcion ?? '—' }}</div>
                    <div><strong>Categoría:</strong> {{ $categoria['nombre'] ?? 'Sin categoría' }}</div>
                    <div><strong>Producto:</strong> {{ $producto['nombre'] ?? 'Sin producto' }} (ID: {{ $producto['id'] ?? '—' }})</div>
                </div>
                @if(count($caracteristicas))
                <div class="mt-2">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($caracteristicas as $item)
                        <li><strong>{{ $item['nombre'] }}:</strong> {{ implode(', ', array_column($item['opciones'], 'nombre')) }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </section>

            {{-- Archivo de Diseño --}}
            @if ($archivo)
            <section class="rounded-lg p-3 border">
                <div class="flex justify-between text-sm">
                    <div><strong>Archivo:</strong> {{ $archivo->nombre_archivo }}</div>
                    <div><strong>Versión:</strong> {{ $archivo->version }}</div>
                </div>
            </section>
            @endif
        </main>
    </div>
</body>
</html>

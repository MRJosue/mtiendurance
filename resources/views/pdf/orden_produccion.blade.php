<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Producción #{{ $orden->id }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }

        h1, h2, h3 {
            text-align: center;
            margin-bottom: 20px;
        }

        .section {
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #aaa;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
        }

        ul {
            padding-left: 20px;
        }
    </style>
</head>
<body>

    <h1>Orden de Producción #{{ $orden->id }}</h1>

    <div class="section">
        <p><strong>Tipo:</strong> {{ $orden->tipo }}</p>
        <p><strong>Creado:</strong> {{ $orden->created_at->format('Y-m-d H:i') }}</p>
    </div>

    <div class="section">
        <h3>Pedidos Relacionados</h3>
        <ul>
            @foreach($orden->pedidos as $pedido)
                <li>Pedido #{{ $pedido->id }} – {{ $pedido->producto->nombre ?? 'Sin producto' }}</li>
            @endforeach
        </ul>
    </div>

    @if($orden->ordenCorte)
        <div class="section">
            <h3>Suborden Corte</h3>
            <p><strong>Fecha de Inicio:</strong> {{ $orden->ordenCorte->fecha_inicio?->format('Y-m-d') }}</p>
            <p><strong>Total de piezas:</strong> {{ $orden->ordenCorte->total }}</p>

            @php
                $tallas = is_array($orden->ordenCorte->tallas)
                    ? $orden->ordenCorte->tallas
                    : json_decode($orden->ordenCorte->tallas ?? '[]', true);
            @endphp

            @if(!empty($tallas))
                <table>
                    <thead>
                        <tr>
                            <th>Grupo</th>
                            <th>Talla</th>
                            <th>Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tallas as $t)
                            <tr>
                                <td>{{ $t['grupo'] ?? '-' }}</td>
                                <td>{{ $t['talla'] ?? '-' }}</td>
                                <td>{{ $t['cantidad'] ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No hay tallas registradas.</p>
            @endif
        </div>
    @endif

</body>
</html>

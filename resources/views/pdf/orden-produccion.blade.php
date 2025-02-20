<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Producción - Proyecto {{ $proyecto->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .container { width: 100%; margin: 0 auto; padding: 20px; }
        h2, h3 { margin: 0 0 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .estado { font-weight: bold; color: #d97706; }
        .seccion { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Orden de Producción</h2>
        <h3>Proyecto: {{$proyecto->id}}-{{$proyecto->nombre}}</h3>
        <p><strong>Cliente:</strong> {{ $proyecto->cliente->nombre_empresa ?? 'N/A' }}</p>
        <p><strong>Fecha de Producción:</strong> {{ $proyecto->fecha_produccion }}</p>
        <p><strong>Estado:</strong> <span class="estado">{{ strtoupper($proyecto->estado) }}</span></p>

        @if($proyecto->pedidos->isEmpty())
            <p>No hay pedidos asociados a este proyecto.</p>
        @else
            <h3>Pedidos Asociados</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Categorías</th>
                        <th>Características</th>
                        <th>Opciones</th>
                        <th>Tallas</th>
                        <th>Total</th>
                        <th>Estatus</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($proyecto->pedidos as $pedido)
                        <tr>
                            <td>{{ $pedido->id }}</td>
                            <td>{{ $pedido->producto->nombre ?? 'N/A' }}</td>
                            <td>
                                @foreach ($pedido->producto->categorias as $categoria)
                                    {{ $categoria->nombre }},
                                @endforeach
                            </td>
                            <td>
                                @foreach ($pedido->pedidoCaracteristicas as $caracteristica)
                                    {{ $caracteristica->caracteristica->nombre ?? 'N/A' }},
                                @endforeach
                            </td>
                            <td>
                                @foreach ($pedido->pedidoOpciones as $opcion)
                                    {{ $opcion->opcion->nombre ?? 'N/A' }},
                                @endforeach
                            </td>
                            <td>
                                @foreach ($pedido->pedidoTallas as $talla)
                                    {{ $talla->talla->nombre ?? 'N/A' }} ({{ $talla->cantidad ?? '0' }}),
                                @endforeach
                            </td>
                            <td>${{ number_format($pedido->total, 2) }}</td>
                            <td>{{ strtoupper($pedido->estatus) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</body>
</html>

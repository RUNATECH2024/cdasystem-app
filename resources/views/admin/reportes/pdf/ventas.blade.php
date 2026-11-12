<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de ventas</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 24px; margin: 0 0 10px; }
        .sub { color: #4b5563; margin-bottom: 18px; }
        .cards { margin: 20px 0; }
        .card { display: inline-block; width: 180px; margin: 0 8px 8px 0; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; background: #f9fafb; }
        .label { font-size: 10px; text-transform: uppercase; color: #6b7280; }
        .value { font-size: 16px; font-weight: bold; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 7px; text-align: left; }
        th { background: #f3f4f6; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>Reporte de ventas</h1>
    <div class="sub">Periodo: {{ $desde->format('d/m/Y') }} — {{ $hasta->format('d/m/Y') }}</div>

    <div class="cards">
        <div class="card">
            <div class="label">Ventas</div>
            <div class="value">{{ $ventas['cantidad'] }}</div>
        </div>
        <div class="card">
            <div class="label">Ingresos</div>
            <div class="value">${{ number_format($ventas['total'], 2) }}</div>
        </div>
        <div class="card">
            <div class="label">Promedio</div>
            <div class="value">${{ number_format($ventas['promedio'], 2) }}</div>
        </div>
        <div class="card">
            <div class="label">Impuestos</div>
            <div class="value">${{ number_format($ventas['impuesto'], 2) }}</div>
        </div>
    </div>

    <h2>Ventas por día</h2>
    <table>
        <thead>
            <tr>
                <th>Día</th>
                <th class="right">Ventas</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ventasPorDia as $fila)
                <tr>
                    <td>{{ $fila->dia }}</td>
                    <td class="right">{{ $fila->cantidad }}</td>
                    <td class="right">${{ number_format((float) $fila->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Ventas por método</h2>
    <table>
        <thead>
            <tr>
                <th>Método</th>
                <th class="right">Ventas</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ventasPorMetodo as $fila)
                <tr>
                    <td>{{ $fila->metodo }}</td>
                    <td class="right">{{ $fila->cantidad }}</td>
                    <td class="right">${{ number_format((float) $fila->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Productos más vendidos</h2>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th class="right">Unidades</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topProductos as $producto)
                <tr>
                    <td>{{ $producto->nombre }}</td>
                    <td class="right">{{ $producto->unidades }}</td>
                    <td class="right">${{ number_format((float) $producto->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

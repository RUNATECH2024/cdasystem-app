<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de inventario</title>
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
        .danger { color: #b91c1c; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Reporte de inventario</h1>
    <div class="sub">Generado: {{ now()->format('d/m/Y H:i') }}</div>

    <div class="cards">
        <div class="card">
            <div class="label">Productos</div>
            <div class="value">{{ $inventario['productos'] }}</div>
        </div>
        <div class="card">
            <div class="label">Unidades</div>
            <div class="value">{{ $inventario['unidades'] }}</div>
        </div>
        <div class="card">
            <div class="label">Valor costo</div>
            <div class="value">${{ number_format($inventario['costo'], 2) }}</div>
        </div>
        <div class="card">
            <div class="label">Valor venta</div>
            <div class="value">${{ number_format($inventario['venta'], 2) }}</div>
        </div>
        <div class="card">
            <div class="label">Stock bajo</div>
            <div class="value">{{ $inventario['stock_bajo'] }}</div>
        </div>
    </div>

    <h2>Stock por categoría</h2>
    <table>
        <thead>
            <tr>
                <th>Categoría</th>
                <th class="right">Productos</th>
                <th class="right">Unidades</th>
                <th class="right">Valor costo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stockPorCategoria as $fila)
                <tr>
                    <td>{{ $fila['nombre'] }}</td>
                    <td class="right">{{ $fila['productos'] }}</td>
                    <td class="right">{{ $fila['unidades'] }}</td>
                    <td class="right">${{ number_format($fila['costo'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Productos con stock bajo</h2>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Subcategoría</th>
                <th class="right">Stock</th>
                <th class="right">Mínimo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productosStockBajo as $producto)
                <tr>
                    <td>{{ $producto->nombre }}</td>
                    <td>{{ $producto->subcategoria?->nombre ?? '—' }}</td>
                    <td class="right danger">{{ $producto->stock_actual }}</td>
                    <td class="right">{{ $producto->stock_minimo }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

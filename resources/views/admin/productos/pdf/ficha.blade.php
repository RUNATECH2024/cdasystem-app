<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha técnica - {{ $producto->nombre }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 24px; margin: 0 0 12px; }
        .meta { margin-bottom: 20px; color: #4b5563; }
        .grid { width: 100%; }
        .left, .right { width: 50%; vertical-align: top; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #d1d5db; padding: 7px; text-align: left; }
        th { background: #f3f4f6; width: 35%; }
        .muted { color: #6b7280; }
        .box { border: 1px solid #d1d5db; border-radius: 8px; padding: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>{{ $producto->nombre }}</h1>
    <div class="meta">
        SKU: {{ $producto->sku ?: '—' }} · Marca: {{ $producto->marca ?: '—' }} · Estado: {{ $producto->activo ? 'Activo' : 'Inactivo' }}
    </div>

    <table class="grid">
        <tr>
            <td class="left">
                <table>
                    <tr><th>Categoria</th><td>{{ $producto->subcategoria?->categoria?->nombre ?? '—' }}</td></tr>
                    <tr><th>Subcategoría</th><td>{{ $producto->subcategoria?->nombre ?? '—' }}</td></tr>
                    <tr><th>Condición</th><td>{{ $producto->condicion ?? '—' }}</td></tr>
                    <tr><th>Garantía</th><td>{{ $producto->garantia_meses ? $producto->garantia_meses.' meses' : 'Sin garantía' }}</td></tr>
                    <tr><th>Precio compra</th><td>${{ number_format((float) $producto->precio_compra, 2) }}</td></tr>
                    <tr><th>Precio venta</th><td>${{ number_format((float) $producto->precio_venta, 2) }}</td></tr>
                </table>
            </td>
            <td class="right">
                <table>
                    <tr><th>Stock actual</th><td>{{ $producto->stock_actual }}</td></tr>
                    <tr><th>Stock mínimo</th><td>{{ $producto->stock_minimo }}</td></tr>
                    <tr><th>Fecha de vencimiento</th><td>{{ $producto->fecha_vencimiento ? $producto->fecha_vencimiento->format('d/m/Y') : '—' }}</td></tr>
                    <tr><th>Destacado</th><td>{{ $producto->destacado ? 'Sí' : 'No' }}</td></tr>
                    <tr><th>Imagen</th><td>{{ $producto->imagen_principal ? 'Disponible' : 'Sin imagen' }}</td></tr>
                    <tr><th>Última actualización</th><td>{{ optional($producto->updated_at)->format('d/m/Y H:i') ?? '—' }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="box">
        <strong>Descripción corta:</strong>
        <div>{{ $producto->descripcion_corta ?: 'Sin descripción corta.' }}</div>
    </div>

    <div class="box">
        <strong>Descripción larga:</strong>
        <div>{{ $producto->descripcion_larga ?: 'Sin descripción larga.' }}</div>
    </div>

    <div class="box">
        <strong>Características / atributos:</strong>
        @if(count($atributos) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($atributos as $atributo)
                        <tr>
                            <td>{{ $atributo['nombre'] }}</td>
                            <td>{{ $atributo['valor'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="muted">No hay atributos adicionales para este producto.</div>
        @endif
    </div>
</body>
</html>

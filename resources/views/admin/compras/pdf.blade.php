<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Compra #{{ $compra->id_compra }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; color: #1f2937; }
        h1 { font-size: 22px; margin: 0 0 4px; }
        h2 { font-size: 13px; margin: 18px 0 6px; border-bottom: 1px solid #d1d5db; padding-bottom: 4px; }
        .sub { color: #4b5563; margin-bottom: 14px; }
        .muted { color: #6b7280; }
        .right { text-align: right; }
        .grid { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .grid td { vertical-align: top; padding: 0 8px 0 0; width: 50%; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.data th, table.data td { border: 1px solid #d1d5db; padding: 5px; text-align: left; }
        table.data th { background: #f3f4f6; font-size: 9px; text-transform: uppercase; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 10px; background: #e5e7eb; font-weight: bold; }
        .total { font-size: 13px; font-weight: bold; }
        .danger { color: #b91c1c; font-weight: bold; }
        .success { color: #047857; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Comprobante de compra #{{ $compra->id_compra }}</h1>
    <div class="sub">
        {{ $compra->numero_factura ? 'Factura: '.$compra->numero_factura.' · ' : '' }}
        Fecha: {{ $compra->fecha_compra?->format('d/m/Y') ?? 'Sin fecha' }} ·
        Estado: <span class="badge">{{ $compra->estado ?: 'Sin estado' }}</span> ·
        Generado: {{ now()->format('d/m/Y H:i') }}
    </div>

    <table class="grid">
        <tr>
            <td>
                <h2>Proveedor</h2>
                <strong>{{ $compra->proveedor?->nombre ?? 'Proveedor no disponible' }}</strong><br>
                RUC: {{ $compra->proveedor?->ruc ?: '—' }}<br>
                Teléfono: {{ $compra->proveedor?->telefono ?: '—' }}<br>
                Correo: {{ $compra->proveedor?->email ?: '—' }}<br>
                Dirección: {{ $compra->proveedor?->direccion ?: '—' }}
            </td>
            <td>
                <h2>Datos de la compra</h2>
                Fecha de compra: {{ $compra->fecha_compra?->format('d/m/Y') ?? 'Sin fecha' }}<br>
                Vencimiento: {{ $compra->fecha_vencimiento?->format('d/m/Y') ?? '—' }}<br>
                Registró: {{ $compra->usuario?->nombre ?? 'Sistema' }}<br>
                Ítems: {{ $compra->detalles->count() }}
            </td>
        </tr>
    </table>

    <h2>Productos</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Producto</th>
                <th>SKU</th>
                <th class="right">Cantidad</th>
                <th class="right">Costo unitario</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($compra->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->producto?->nombre ?? 'Producto eliminado' }}</td>
                    <td>{{ $detalle->producto?->sku ?: '—' }}</td>
                    <td class="right">{{ $detalle->cantidad }}</td>
                    <td class="right">${{ number_format((float) $detalle->precio_costo_unitario, 2) }}</td>
                    <td class="right">${{ number_format((float) $detalle->precio_costo_unitario * (int) $detalle->cantidad, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">Esta compra no tiene productos registrados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="grid" style="margin-top: 14px;">
        <tr>
            <td>
                <h2>Abonos</h2>
                <table class="data">
                    <thead>
                        <tr><th>Fecha</th><th>Método</th><th class="right">Monto</th></tr>
                    </thead>
                    <tbody>
                        @forelse($compra->abonos->sortBy('fecha_abono') as $abono)
                            <tr>
                                <td>{{ $abono->fecha_abono?->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ $abono->metodo_pago ?: '—' }}</td>
                                <td class="right">${{ number_format((float) $abono->monto, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="muted">Sin abonos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
            <td>
                <h2>Resumen de pago</h2>
                <table class="data">
                    <tr><td>Subtotal</td><td class="right">${{ number_format((float) $compra->subtotal, 2) }}</td></tr>
                    <tr><td>Impuesto</td><td class="right">${{ number_format((float) $compra->impuesto, 2) }}</td></tr>
                    <tr><td class="total">Total</td><td class="right total">${{ number_format((float) $compra->total, 2) }}</td></tr>
                    <tr><td>Pagado</td><td class="right success">${{ number_format($pagado, 2) }}</td></tr>
                    <tr><td>Saldo</td><td class="right {{ $saldo > 0 ? 'danger' : 'success' }}">${{ number_format($saldo, 2) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

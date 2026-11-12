<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante pedido #{{ $pedido->id_pedido }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 11px; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        h2 { font-size: 13px; margin: 18px 0 7px; border-bottom: 1px solid #d1d5db; padding-bottom: 4px; }
        .muted { color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 7px; text-align: left; }
        th { background: #f3f4f6; }
        .right { text-align: right; }
        .totals { width: 48%; margin-left: auto; }
        .totals td { border: 0; padding: 4px 7px; }
    </style>
</head>
<body>
    <h1>Comprobante de pedido #{{ $pedido->id_pedido }}</h1>
    <p class="muted">Fecha: {{ $pedido->fecha_pedido?->format('d/m/Y H:i') }} · Estado: {{ $pedido->estado_pedido }}</p>

    <h2>Datos del cliente</h2>
    <p>
        <strong>{{ $pedido->nombre_contacto ?: $pedido->cliente?->nombre ?: 'Cliente' }}</strong><br>
        Identificación: {{ $pedido->dni_ruc ?: '—' }}<br>
        Correo: {{ $pedido->email_contacto ?: $pedido->cliente?->email ?: '—' }}<br>
        Teléfono: {{ $pedido->telefono_contacto ?: '—' }}
    </p>

    <h2>Entrega y pago</h2>
    <p>
        Entrega: {{ $pedido->metodo_entrega ?: 'Por coordinar' }}<br>
        Dirección: {{ $pedido->direccion_envio ?: 'Por coordinar' }}<br>
        Ciudad / provincia: {{ collect([$pedido->ciudad, $pedido->provincia])->filter()->join(', ') ?: '—' }}<br>
        Referencia: {{ $pedido->referencia_envio ?: '—' }}<br>
        Método de pago: {{ $pedido->metodo_pago ?: 'Por coordinar' }}
    </p>

    <h2>Detalle del pedido</h2>
    <table>
        <thead><tr><th>Producto</th><th>SKU</th><th class="right">Cantidad</th><th class="right">Precio unitario</th><th class="right">Importe</th></tr></thead>
        <tbody>
            @foreach($pedido->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->producto?->nombre ?? 'Producto no disponible' }}</td>
                    <td>{{ $detalle->producto?->sku ?: '—' }}</td>
                    <td class="right">{{ $detalle->cantidad }}</td>
                    <td class="right">${{ number_format((float) $detalle->precio_unitario, 2) }}</td>
                    <td class="right">${{ number_format((float) $detalle->precio_unitario * $detalle->cantidad, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="right">${{ number_format((float) $pedido->subtotal, 2) }}</td></tr>
        <tr><td>Envío</td><td class="right">${{ number_format((float) $pedido->costo_envio, 2) }}</td></tr>
        <tr><td><strong>Total</strong></td><td class="right"><strong>${{ number_format((float) $pedido->total, 2) }}</strong></td></tr>
    </table>

    <p class="muted">Este documento confirma la recepción del pedido y no sustituye la factura tributaria.</p>
</body>
</html>
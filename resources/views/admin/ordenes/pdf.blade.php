<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de taller #{{ $orden->id_orden }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 10px; }
        h1 { font-size: 20px; margin: 0 0 5px; }
        h2 { font-size: 12px; margin: 16px 0 6px; border-bottom: 1px solid #9ca3af; padding-bottom: 4px; }
        p { margin: 3px 0; }
        .muted { color: #6b7280; }
        .heading { width: 100%; border-bottom: 2px solid #1f2937; padding-bottom: 10px; }
        .heading td { vertical-align: top; }
        .heading-right { text-align: right; }
        .columns { width: 100%; border-collapse: separate; border-spacing: 8px 0; margin: 0 -8px; }
        .columns td { width: 50%; vertical-align: top; }
        .info { border: 1px solid #d1d5db; padding: 8px; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .items th, .items td { border: 1px solid #d1d5db; padding: 6px; text-align: left; vertical-align: top; }
        .items th { background: #f3f4f6; }
        .right { text-align: right !important; white-space: nowrap; }
        .totals { width: 45%; margin: 8px 0 0 auto; border-collapse: collapse; }
        .totals td { padding: 4px 6px; }
        .grand-total { border-top: 1px solid #9ca3af; font-size: 12px; font-weight: bold; }
        .signatures { width: 100%; margin-top: 48px; }
        .signatures td { width: 50%; padding: 0 24px; text-align: center; vertical-align: bottom; }
        .line { border-top: 1px solid #4b5563; padding-top: 5px; }
    </style>
</head>
<body>
    <table class="heading">
        <tr>
            <td>
                <h1>Orden de servicio #{{ $orden->id_orden }}</h1>
                <p class="muted">CDA System · Generada {{ now()->format('d/m/Y H:i') }}</p>
            </td>
            <td class="heading-right">
                <p><strong>Estado:</strong> {{ $orden->estado ?: '—' }}</p>
                <p><strong>Ingreso:</strong> {{ $orden->fecha_ingreso?->format('d/m/Y H:i') ?? '—' }}</p>
                <p><strong>Entrega:</strong> {{ $orden->fecha_entrega?->format('d/m/Y H:i') ?? 'Pendiente' }}</p>
            </td>
        </tr>
    </table>

    <table class="columns">
        <tr>
            <td>
                <h2>Cliente</h2>
                <div class="info">
                    <p><strong>{{ $orden->cliente?->nombre ?? 'Cliente no disponible' }}</strong></p>
                    <p>Documento: {{ $orden->cliente?->dni_ruc ?: '—' }}</p>
                    <p>Teléfono: {{ $orden->cliente?->telefono ?: '—' }}</p>
                    <p>Correo: {{ $orden->cliente?->email ?: '—' }}</p>
                    <p>Dirección: {{ $orden->cliente?->direccion ?: '—' }}</p>
                </div>
            </td>
            <td>
                <h2>Equipo</h2>
                <div class="info">
                    <p><strong>{{ $orden->equipo?->tipo_equipo ?: '—' }}</strong></p>
                    <p>Marca y modelo: {{ trim(($orden->equipo?->marca ?? '').' '.($orden->equipo?->modelo ?? '')) ?: '—' }}</p>
                    <p>Número de serie: {{ $orden->equipo?->numero_serie ?: '—' }}</p>
                    <p>Contador ingreso: {{ $orden->contador_ingreso ?? $orden->equipo?->contador_paginas ?? '—' }}</p>
                    <p>Contador salida: {{ $orden->contador_salida ?? '—' }}</p>
                    <p>Técnico: {{ $orden->tecnico?->nombre ?? 'Sin asignar' }}</p>
                </div>
            </td>
        </tr>
    </table>

    <h2>Reporte y diagnóstico</h2>
    <div class="info">
        <p><strong>Falla reportada</strong></p>
        <p>{{ $orden->falla_reportada ?: 'Sin descripción' }}</p>
        <p style="margin-top: 8px"><strong>Diagnóstico / trabajo realizado</strong></p>
        <p>{{ $orden->diagnostico ?: 'Pendiente' }}</p>
    </div>

    <h2>Repuestos y costos</h2>
    <table class="items">
        <thead><tr><th>Repuesto</th><th class="right">Cantidad</th><th class="right">Precio unitario</th><th class="right">Importe</th></tr></thead>
        <tbody>
            @forelse($orden->repuestos as $repuesto)
                <tr>
                    <td>{{ $repuesto->producto?->nombre ?? 'Repuesto no disponible' }}<br><span class="muted">SKU: {{ $repuesto->producto?->sku ?: '—' }}</span></td>
                    <td class="right">{{ $repuesto->cantidad }}</td>
                    <td class="right">${{ number_format((float) $repuesto->precio_unitario, 2) }}</td>
                    <td class="right">${{ number_format((float) $repuesto->precio_unitario * $repuesto->cantidad, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No se utilizaron repuestos.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Mano de obra</td><td class="right">${{ number_format((float) $orden->total_mano_obra, 2) }}</td></tr>
        <tr><td>Repuestos</td><td class="right">${{ number_format((float) $orden->total_repuestos, 2) }}</td></tr>
        <tr class="grand-total"><td>Total</td><td class="right">${{ number_format((float) $orden->total_general, 2) }}</td></tr>
    </table>

    <table class="signatures">
        <tr><td><div class="line">Firma del cliente</div></td><td><div class="line">Firma del técnico</div></td></tr>
    </table>
</body>
</html>
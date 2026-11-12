<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de clientes</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; color: #1f2937; }
        h1 { font-size: 23px; margin: 0 0 8px; }
        h2 { font-size: 15px; margin: 18px 0 6px; }
        .sub { color: #4b5563; margin-bottom: 14px; }
        .cards { margin: 14px 0; }
        .card { display: inline-block; width: 170px; margin: 0 7px 7px 0; padding: 9px; border: 1px solid #d1d5db; border-radius: 8px; background: #f9fafb; }
        .label { font-size: 9px; text-transform: uppercase; color: #6b7280; }
        .value { font-size: 15px; font-weight: bold; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 5px; text-align: left; }
        th { background: #f3f4f6; font-size: 9px; text-transform: uppercase; }
        .right { text-align: right; }
        .danger { color: #b91c1c; font-weight: bold; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    <h1>Reporte general de clientes</h1>
    <div class="sub">Periodo: {{ $desde->format('d/m/Y') }} — {{ $hasta->format('d/m/Y') }} · Generado: {{ now()->format('d/m/Y H:i') }}</div>

    <div class="cards">
        <div class="card"><div class="label">Clientes</div><div class="value">{{ $resumen['total'] }}</div></div>
        <div class="card"><div class="label">Activos</div><div class="value">{{ $resumen['activos'] }}</div></div>
        <div class="card"><div class="label">Nuevos</div><div class="value">{{ $resumen['nuevos'] }}</div></div>
        <div class="card"><div class="label">Con ventas</div><div class="value">{{ $resumen['con_ventas'] }}</div></div>
        <div class="card"><div class="label">Saldo por cobrar</div><div class="value">${{ number_format($resumen['saldo'], 2) }}</div></div>
    </div>

    <h2>Clientes y ventas del periodo</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>DNI/RUC</th>
                <th>Contacto</th>
                <th class="right">Ventas</th>
                <th class="right">Total vendido</th>
                <th class="right">Órdenes</th>
                <th class="right">Saldo</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clientes as $cliente)
                <tr>
                    <td>{{ $cliente->id_cliente }}</td>
                    <td>{{ $cliente->nombre }}</td>
                    <td>{{ $cliente->dni_ruc ?: '—' }}</td>
                    <td>
                        {{ $cliente->telefono ?: 'Sin teléfono' }}
                        @if($cliente->email)<div class="muted">{{ $cliente->email }}</div>@endif
                    </td>
                    <td class="right">{{ $cliente->ventas_periodo }}</td>
                    <td class="right">${{ number_format((float) $cliente->ventas_total_periodo, 2) }}</td>
                    <td class="right">{{ $cliente->ordenes_periodo }}</td>
                    <td class="right {{ (float) $cliente->saldo_pendiente > 0 ? 'danger' : '' }}">${{ number_format((float) $cliente->saldo_pendiente, 2) }}</td>
                    <td>{{ $cliente->activo ? 'Activo' : 'Inactivo' }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="muted">Sin clientes registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

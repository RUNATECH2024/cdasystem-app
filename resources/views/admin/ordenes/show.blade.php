<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden #{{ $orden->id_orden }} - Admin CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">
    @include('admin.partials.header')
    <main class="max-w-5xl mx-auto px-4 mt-6 pb-10">
        @if(session('success'))<div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="bg-red-100 text-red-800 p-3 rounded mb-4">{{ session('error') }}</div>@endif
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <div>
                <a href="{{ route('admin.ordenes.index') }}" class="text-sm text-blue-700 hover:underline">← Órdenes de taller</a>
                <h1 class="text-2xl font-bold text-gray-800 mt-1">Orden #{{ $orden->id_orden }}</h1>
                <p class="text-sm text-gray-500">Ingreso: {{ $orden->fecha_ingreso?->format('d/m/Y H:i') ?? 'Sin fecha' }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.ordenes.pdf', $orden) }}" class="bg-gray-700 text-white px-4 py-2 rounded text-sm font-medium hover:bg-gray-800">Descargar PDF</a>
                <a href="{{ route('admin.ordenes.edit', $orden) }}" class="bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-800">Editar orden</a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
            <section class="bg-white rounded shadow p-5">
                <h2 class="font-bold text-gray-800 border-b pb-2 mb-3">Cliente y equipo</h2>
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-xs uppercase text-gray-500">Cliente</dt><dd>{{ $orden->cliente?->nombre ?? 'Cliente no disponible' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Contacto</dt><dd>{{ $orden->cliente?->telefono ?? '—' }} · {{ $orden->cliente?->email ?? '—' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Equipo</dt><dd>{{ $orden->equipo?->tipo_equipo }} · {{ $orden->equipo?->marca }} {{ $orden->equipo?->modelo }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Número de serie</dt><dd>{{ $orden->equipo?->numero_serie ?: '—' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Técnico</dt><dd>{{ $orden->tecnico?->nombre ?? 'Sin asignar' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Estado</dt><dd>{{ $orden->estado ?: '—' }}</dd></div>
                </dl>
            </section>

            <section class="bg-white rounded shadow p-5">
                <h2 class="font-bold text-gray-800 border-b pb-2 mb-3">Diagnóstico y fechas</h2>
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-xs uppercase text-gray-500">Falla reportada</dt><dd class="whitespace-pre-line">{{ $orden->falla_reportada }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Diagnóstico</dt><dd class="whitespace-pre-line">{{ $orden->diagnostico ?: 'Pendiente' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Fecha de entrega</dt><dd>{{ $orden->fecha_entrega?->format('d/m/Y H:i') ?? 'Pendiente' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Contador</dt><dd>Ingreso: {{ $orden->contador_ingreso ?? '—' }} · Salida: {{ $orden->contador_salida ?? '—' }}</dd></div>
                </dl>
            </section>
        </div>

        <section class="bg-white rounded shadow overflow-x-auto">
            <h2 class="font-bold text-gray-800 p-5 border-b">Repuestos y costos</h2>
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr><th class="px-5 py-3">Repuesto</th><th class="px-5 py-3">Cantidad</th><th class="px-5 py-3">Precio unitario</th><th class="px-5 py-3 text-right">Importe</th></tr></thead>
                <tbody>
                    @forelse($orden->repuestos as $repuesto)
                        <tr class="border-t"><td class="px-5 py-3">{{ $repuesto->producto?->nombre ?? 'Repuesto eliminado' }}</td><td class="px-5 py-3">{{ $repuesto->cantidad }}</td><td class="px-5 py-3">${{ number_format((float) $repuesto->precio_unitario, 2) }}</td><td class="px-5 py-3 text-right">${{ number_format((float) $repuesto->precio_unitario * $repuesto->cantidad, 2) }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-gray-500">Esta orden no tiene repuestos registrados.</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 border-t text-sm"><tr><th colspan="3" class="px-5 py-2 text-right">Mano de obra</th><td class="px-5 py-2 text-right">${{ number_format((float) $orden->total_mano_obra, 2) }}</td></tr><tr><th colspan="3" class="px-5 py-2 text-right">Repuestos</th><td class="px-5 py-2 text-right">${{ number_format((float) $orden->total_repuestos, 2) }}</td></tr><tr><th colspan="3" class="px-5 py-2 text-right">Total</th><td class="px-5 py-2 text-right font-bold">${{ number_format((float) $orden->total_general, 2) }}</td></tr></tfoot>
            </table>
        </section>
    </main>
</body>
</html>
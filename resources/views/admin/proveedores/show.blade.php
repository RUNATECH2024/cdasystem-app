<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $proveedor->nombre }} - Admin CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">
    @include('admin.partials.header')
    <main class="max-w-6xl mx-auto px-4 mt-6 pb-10">
        <a href="{{ route('admin.proveedores.index') }}" class="text-sm text-blue-700 hover:underline">&larr; Proveedores</a>

        <div class="flex flex-wrap items-end justify-between gap-3 mt-2 mb-5">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold text-gray-800"><x-icon name="truck" class="h-6 w-6 text-blue-600" /> {{ $proveedor->nombre }}</h1>
                <p class="text-sm text-gray-500">Proveedor #{{ $proveedor->id_proveedor }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.compras.create', ['proveedor' => $proveedor->id_proveedor]) }}" class="inline-flex items-center gap-2 bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-800"><x-icon name="plus" /> Nueva compra</a>
                <a href="{{ route('admin.proveedores.edit', $proveedor) }}" class="inline-flex items-center gap-2 bg-white text-gray-700 px-4 py-2 rounded shadow text-sm font-medium hover:bg-gray-50"><x-icon name="settings" /> Editar</a>
                <form action="{{ route('admin.proveedores.estado', $proveedor) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="inline-flex items-center gap-2 bg-gray-800 text-white px-4 py-2 rounded text-sm font-medium hover:bg-gray-900"><x-icon name="refresh" /> {{ $proveedor->activo ? 'Desactivar' : 'Activar' }}</button>
                </form>
            </div>
        </div>

        @if(session('success'))<div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="bg-red-100 text-red-800 p-3 rounded mb-4">{{ session('error') }}</div>@endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
            <section class="bg-white rounded shadow p-5 lg:col-span-2">
                <h2 class="flex items-center gap-2 font-bold text-gray-800 mb-4"><x-icon name="id-card" class="text-blue-600" /> Datos del proveedor</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">RUC</dt><dd class="font-medium text-gray-800">{{ $proveedor->ruc ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Teléfono</dt><dd class="font-medium text-gray-800">{{ $proveedor->telefono ?: 'Sin teléfono' }}</dd></div>
                    <div><dt class="text-gray-500">Correo electrónico</dt><dd class="font-medium text-gray-800 break-all">{{ $proveedor->email ?: 'Sin correo' }}</dd></div>
                    <div><dt class="text-gray-500">Dirección</dt><dd class="font-medium text-gray-800">{{ $proveedor->direccion ?: 'Sin dirección' }}</dd></div>
                    <div><dt class="text-gray-500">Estado</dt><dd><span class="inline-block rounded px-2 py-1 text-xs font-medium {{ $proveedor->activo ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">{{ $proveedor->activo ? 'Activo' : 'Inactivo' }}</span></dd></div>
                    <div><dt class="text-gray-500">Saldo pendiente</dt><dd class="font-medium {{ (float) $proveedor->saldo_pendiente > 0 ? 'text-red-600' : 'text-gray-800' }}">${{ number_format((float) $proveedor->saldo_pendiente, 2) }}</dd></div>
                </dl>
            </section>

            <section class="bg-white rounded shadow p-5">
                <h2 class="flex items-center gap-2 font-bold text-gray-800 mb-4"><x-icon name="chart" class="text-blue-600" /> Resumen</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between"><span class="text-gray-500">Compras registradas</span><span class="font-bold text-gray-800">{{ $totalCompras }}</span></div>
                    <div class="flex items-center justify-between"><span class="text-gray-500">Total comprado</span><span class="font-bold text-gray-800">${{ number_format($totalComprado, 2) }}</span></div>
                    <div class="flex items-center justify-between"><span class="text-gray-500">Saldo pendiente</span><span class="font-bold {{ (float) $proveedor->saldo_pendiente > 0 ? 'text-red-600' : 'text-gray-800' }}">${{ number_format((float) $proveedor->saldo_pendiente, 2) }}</span></div>
                </div>
            </section>
        </div>

        <section class="bg-white rounded shadow">
            <div class="px-4 py-3 border-b flex flex-wrap items-center justify-between gap-2">
                <h2 class="flex items-center gap-2 font-bold text-gray-800"><x-icon name="receipt" class="text-blue-600" /> Compras recientes</h2>
                <span class="text-xs text-gray-500">{{ $totalCompras }} compra(s)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-2">Compra</th>
                            <th class="px-4 py-2">Factura</th>
                            <th class="px-4 py-2">Fecha</th>
                            <th class="px-4 py-2">Vencimiento</th>
                            <th class="px-4 py-2">Estado</th>
                            <th class="px-4 py-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($proveedor->compras as $compra)
                            <tr class="border-b">
                                <td class="px-4 py-3 font-medium text-gray-800"><a href="{{ route('admin.compras.show', $compra) }}" class="text-blue-700 hover:underline">#{{ $compra->id_compra }}</a></td>
                                <td class="px-4 py-3 text-gray-600">{{ $compra->numero_factura ?: '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $compra->fecha_compra?->format('d/m/Y') ?? 'Sin fecha' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $compra->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $compra->estado ?: 'Sin estado' }}</td>
                                <td class="px-4 py-3 text-right font-medium">${{ number_format((float) $compra->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Este proveedor todavía no tiene compras registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compras - Admin CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">
    @include('admin.partials.header')
    <main class="max-w-7xl mx-auto px-4 mt-6 pb-10">
        <div class="flex flex-wrap items-end justify-between gap-3 mb-5">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold text-gray-800"><x-icon name="receipt" class="h-6 w-6 text-blue-600" /> Compras</h1>
                <p class="text-sm text-gray-500">Registra compras, actualiza inventario y controla pagos a proveedores.</p>
            </div>
            <a href="{{ route('admin.compras.create') }}" class="inline-flex items-center gap-2 bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-800"><x-icon name="plus" /> Nueva compra</a>
        </div>

        @if(session('success'))<div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="bg-red-100 text-red-800 p-3 rounded mb-4">{{ session('error') }}</div>@endif

        <form method="GET" action="{{ route('admin.compras.index') }}" class="bg-white rounded shadow p-4 mb-4 grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
            <div class="md:col-span-2">
                <label for="q" class="flex items-center gap-1.5 text-xs font-bold text-gray-600 uppercase mb-1"><x-icon name="search" class="h-3.5 w-3.5" /> Buscar</label>
                <input id="q" type="search" name="q" value="{{ $q }}" placeholder="Compra, factura, proveedor o RUC" class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label for="estado" class="flex items-center gap-1.5 text-xs font-bold text-gray-600 uppercase mb-1"><x-icon name="settings" class="h-3.5 w-3.5" /> Estado</label>
                <select id="estado" name="estado" class="w-full border rounded px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    <option value="Pagada" @selected($estado === 'Pagada')>Pagada</option>
                    <option value="Parcial" @selected($estado === 'Parcial')>Parcial</option>
                    <option value="Pendiente" @selected($estado === 'Pendiente')>Pendiente</option>
                </select>
            </div>
            <div>
                <label for="desde" class="flex items-center gap-1.5 text-xs font-bold text-gray-600 uppercase mb-1"><x-icon name="calendar" class="h-3.5 w-3.5" /> Desde</label>
                <input id="desde" type="date" name="desde" value="{{ $desde }}" class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label for="hasta" class="flex items-center gap-1.5 text-xs font-bold text-gray-600 uppercase mb-1"><x-icon name="calendar" class="h-3.5 w-3.5" /> Hasta</label>
                <input id="hasta" type="date" name="hasta" value="{{ $hasta }}" class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="inline-flex items-center gap-2 bg-gray-800 text-white px-4 py-2 rounded text-sm hover:bg-gray-900"><x-icon name="filter" /> Filtrar</button>
                <a href="{{ route('admin.compras.index') }}" class="text-sm text-gray-500 hover:underline">Limpiar</a>
            </div>
        </form>

        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Compra</th>
                        <th class="px-4 py-3">Proveedor</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3 text-center">Ítems</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right">Pagado</th>
                        <th class="px-4 py-3 text-right">Saldo</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($compras as $compra)
                        @php($pagado = (float) ($compra->abonos_sum_monto ?? 0))
                        @php($saldo = max(0, (float) $compra->total - $pagado))
                        <tr class="border-b align-top hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.compras.show', $compra) }}" class="font-bold text-blue-700 hover:underline">#{{ $compra->id_compra }}</a>
                                <p class="text-xs text-gray-500">{{ $compra->numero_factura ?: 'Sin factura' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.proveedores.show', $compra->proveedor) }}" class="font-medium text-gray-800 hover:underline">{{ $compra->proveedor?->nombre ?? 'Proveedor eliminado' }}</a>
                                <p class="text-xs text-gray-500">{{ $compra->proveedor?->ruc ?: 'Sin RUC' }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $compra->fecha_compra?->format('d/m/Y') ?? 'Sin fecha' }}</td>
                            <td class="px-4 py-3 text-center font-medium">{{ $compra->detalles_count }}</td>
                            <td class="px-4 py-3 text-right font-semibold">${{ number_format((float) $compra->total, 2) }}</td>
                            <td class="px-4 py-3 text-right text-green-700">${{ number_format($pagado, 2) }}</td>
                            <td class="px-4 py-3 text-right font-semibold {{ $saldo > 0 ? 'text-red-600' : 'text-gray-600' }}">${{ number_format($saldo, 2) }}</td>
                            <td class="px-4 py-3">
                                @php($colorEstado = match($compra->estado) {
                                    'Pagada' => 'bg-green-100 text-green-700',
                                    'Parcial' => 'bg-yellow-100 text-yellow-700',
                                    'Pendiente' => 'bg-red-100 text-red-700',
                                    default => 'bg-gray-200 text-gray-600',
                                })
                                <span class="inline-block rounded px-2 py-1 text-xs font-medium {{ $colorEstado }}">{{ $compra->estado ?: 'Sin estado' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap justify-end gap-3 whitespace-nowrap text-xs font-semibold">
                                    <a href="{{ route('admin.compras.show', $compra) }}" class="text-blue-700 hover:underline">Ver</a>
                                    <a href="{{ route('admin.compras.pdf', $compra) }}" class="text-purple-700 hover:underline">PDF</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-4 py-10 text-center text-gray-500">No hay compras que coincidan con la búsqueda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $compras->links() }}</div>
    </main>
</body>
</html>

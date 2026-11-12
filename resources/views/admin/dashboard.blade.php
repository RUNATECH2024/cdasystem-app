<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de administración - CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">
    @include('admin.partials.header')

    <main class="max-w-7xl mx-auto px-4 mt-6 pb-10">
        <div class="flex flex-wrap items-end justify-between gap-3 mb-3">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold text-gray-800"><x-icon name="chart" class="h-6 w-6 text-blue-600" /> Panel de administración</h1>
                <p class="text-sm text-gray-500">Resumen de operación, finanzas e inventario de CDA System.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.compras.create') }}" class="inline-flex items-center gap-2 bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-800"><x-icon name="plus" /> Nueva compra</a>
                <a href="{{ route('admin.productos.create') }}" class="inline-flex items-center gap-2 bg-white text-gray-700 px-4 py-2 rounded shadow text-sm font-medium hover:bg-gray-50"><x-icon name="box" /> Nuevo producto</a>
                <a href="{{ route('admin.clientes.create') }}" class="inline-flex items-center gap-2 bg-white text-gray-700 px-4 py-2 rounded shadow text-sm font-medium hover:bg-gray-50"><x-icon name="user" /> Nuevo cliente</a>
            </div>
        </div>

        <div class="flex flex-wrap gap-x-6 gap-y-1 text-xs text-gray-500 mb-5">
            <span><strong class="text-gray-700">{{ $resumen['categorias'] }}</strong> categorías</span>
            <span><strong class="text-gray-700">{{ $resumen['subcategorias'] }}</strong> subcategorías</span>
            <span><strong class="text-gray-700">{{ $resumen['clientes'] }}</strong> clientes</span>
            <span><strong class="text-gray-700">{{ $resumen['proveedores'] }}</strong> proveedores</span>
            <span><strong class="text-gray-700">{{ $resumen['usuarios'] }}</strong> usuarios del personal</span>
        </div>

        @if($resumen['stock_bajo'] > 0 || $resumen['pedidos_pendientes'] > 0 || $resumen['ordenes_abiertas'] > 0 || $resumen['cuentas_por_pagar'] > 0)
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-900 rounded p-3 mb-5 text-sm">
                <p class="flex items-center gap-2 font-semibold mb-1"><x-icon name="alert" /> Requiere atención</p>
                <div class="flex flex-wrap gap-x-5 gap-y-1">
                    @if($resumen['stock_bajo'] > 0)
                        <a href="{{ route('admin.productos.index') }}" class="hover:underline">{{ $resumen['stock_bajo'] }} producto(s) con stock bajo</a>
                    @endif
                    @if($resumen['pedidos_pendientes'] > 0)
                        <a href="{{ route('admin.pedidos.index') }}" class="hover:underline">{{ $resumen['pedidos_pendientes'] }} pedido(s) web pendiente(s)</a>
                    @endif
                    @if($resumen['ordenes_abiertas'] > 0)
                        <a href="{{ route('admin.ordenes.index') }}" class="hover:underline">{{ $resumen['ordenes_abiertas'] }} orden(es) de taller abierta(s)</a>
                    @endif
                    @if($resumen['cuentas_por_pagar'] > 0)
                        <a href="{{ route('admin.compras.index') }}" class="hover:underline">${{ number_format($resumen['cuentas_por_pagar'], 2) }} por pagar a proveedores</a>
                    @endif
                </div>
            </div>
        @endif

        <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Operación</h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
            <a href="{{ route('admin.productos.index') }}" class="bg-white rounded shadow p-4 hover:ring-2 hover:ring-blue-100">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs text-gray-500 uppercase">Productos</p>
                    <x-icon name="box" class="text-blue-600" />
                </div>
                <p class="text-2xl font-bold text-gray-800">{{ $resumen['productos'] }}</p>
                <p class="text-xs text-gray-500">{{ $resumen['productos_activos'] }} activos</p>
            </a>
            <a href="{{ route('admin.productos.index') }}" class="bg-white rounded shadow p-4 hover:ring-2 hover:ring-blue-100">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs text-gray-500 uppercase">Stock</p>
                    <x-icon name="layers" class="text-blue-600" />
                </div>
                <p class="text-2xl font-bold {{ $resumen['stock_bajo'] > 0 ? 'text-red-600' : 'text-gray-800' }}">{{ $resumen['stock_bajo'] }}</p>
                <p class="text-xs text-gray-500">bajo mínimo · {{ $resumen['stock_total'] }} unidades</p>
            </a>
            <a href="{{ route('admin.pedidos.index') }}" class="bg-white rounded shadow p-4 hover:ring-2 hover:ring-blue-100">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs text-gray-500 uppercase">Pedidos web</p>
                    <x-icon name="cart" class="text-blue-600" />
                </div>
                <p class="text-2xl font-bold {{ $resumen['pedidos_pendientes'] > 0 ? 'text-red-600' : 'text-gray-800' }}">{{ $resumen['pedidos_pendientes'] }}</p>
                <p class="text-xs text-gray-500">pendientes</p>
            </a>
            <a href="{{ route('admin.ordenes.index') }}" class="bg-white rounded shadow p-4 hover:ring-2 hover:ring-blue-100">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs text-gray-500 uppercase">Taller</p>
                    <x-icon name="wrench" class="text-blue-600" />
                </div>
                <p class="text-2xl font-bold {{ $resumen['ordenes_abiertas'] > 0 ? 'text-yellow-600' : 'text-gray-800' }}">{{ $resumen['ordenes_abiertas'] }}</p>
                <p class="text-xs text-gray-500">órdenes abiertas</p>
            </a>
        </div>

        <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Finanzas</h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
            <div class="bg-white rounded shadow p-4">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs text-gray-500 uppercase">Ventas del mes</p>
                    <x-icon name="currency" class="text-green-600" />
                </div>
                <p class="text-2xl font-bold text-green-600">${{ number_format($resumen['ventas_mes'], 2) }}</p>
                <p class="text-xs text-gray-500">{{ $resumen['ventas_mes_cantidad'] }} ventas · ${{ number_format($resumen['ventas_total'], 2) }} histórico</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs text-gray-500 uppercase">Compras del mes</p>
                    <x-icon name="receipt" class="text-blue-600" />
                </div>
                <p class="text-2xl font-bold text-gray-800">${{ number_format($resumen['compras_mes'], 2) }}</p>
                <p class="text-xs text-gray-500">{{ $resumen['compras_mes_cantidad'] }} compras · ${{ number_format($resumen['compras_total'], 2) }} histórico</p>
            </div>
            <a href="{{ route('admin.compras.index') }}" class="bg-white rounded shadow p-4 hover:ring-2 hover:ring-blue-100">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs text-gray-500 uppercase">Cuentas por pagar</p>
                    <x-icon name="cash" class="text-red-600" />
                </div>
                <p class="text-2xl font-bold {{ $resumen['cuentas_por_pagar'] > 0 ? 'text-red-600' : 'text-gray-800' }}">${{ number_format($resumen['cuentas_por_pagar'], 2) }}</p>
                <p class="text-xs text-gray-500">{{ $resumen['proveedores_con_saldo'] }} proveedor(es) con saldo</p>
            </a>
            <a href="{{ route('admin.compras.index') }}" class="bg-white rounded shadow p-4 hover:ring-2 hover:ring-blue-100">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs text-gray-500 uppercase">Compras abiertas</p>
                    <x-icon name="clock" class="text-yellow-600" />
                </div>
                <p class="text-2xl font-bold {{ $resumen['compras_abiertas'] > 0 ? 'text-yellow-600' : 'text-gray-800' }}">{{ $resumen['compras_abiertas'] }}</p>
                <p class="text-xs text-gray-500">de {{ $resumen['compras_cantidad'] }} compras registradas</p>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <section class="bg-white rounded shadow">
                <div class="px-4 py-3 border-b flex items-center justify-between gap-2">
                    <h3 class="flex items-center gap-2 font-bold text-gray-800"><x-icon name="receipt" class="text-blue-600" /> Compras recientes</h3>
                    <a href="{{ route('admin.compras.index') }}" class="text-sm text-blue-600 hover:underline">Ver todas</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-2">Compra</th>
                                <th class="px-4 py-2">Proveedor</th>
                                <th class="px-4 py-2">Fecha</th>
                                <th class="px-4 py-2 text-right">Total</th>
                                <th class="px-4 py-2">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($comprasRecientes as $compra)
                                <tr class="border-b">
                                    <td class="px-4 py-2 font-medium">
                                        <a href="{{ route('admin.compras.show', $compra) }}" class="text-blue-700 hover:underline">#{{ $compra->id_compra }}</a>
                                        <p class="text-xs text-gray-500">{{ $compra->numero_factura ?: 'Sin factura' }}</p>
                                    </td>
                                    <td class="px-4 py-2 text-gray-600">{{ $compra->proveedor?->nombre ?? '—' }}</td>
                                    <td class="px-4 py-2 text-gray-600">{{ $compra->fecha_compra?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-4 py-2 text-right font-medium">${{ number_format((float) $compra->total, 2) }}</td>
                                    <td class="px-4 py-2 text-gray-600">{{ $compra->estado ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Sin compras registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="bg-white rounded shadow">
                <div class="px-4 py-3 border-b flex items-center justify-between gap-2">
                    <h3 class="flex items-center gap-2 font-bold text-gray-800"><x-icon name="cash" class="text-red-600" /> Compras por pagar</h3>
                    <span class="text-xs text-gray-500">{{ $resumen['compras_abiertas'] }} abierta(s)</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-2">Compra</th>
                                <th class="px-4 py-2">Proveedor</th>
                                <th class="px-4 py-2">Vence</th>
                                <th class="px-4 py-2 text-right">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($comprasPorPagar as $compra)
                                @php($saldo = max(0, (float) $compra->total - (float) ($compra->abonos_sum_monto ?? 0)))
                                <tr class="border-b">
                                    <td class="px-4 py-2 font-medium"><a href="{{ route('admin.compras.show', $compra) }}" class="text-blue-700 hover:underline">#{{ $compra->id_compra }}</a></td>
                                    <td class="px-4 py-2 text-gray-600">{{ $compra->proveedor?->nombre ?? '—' }}</td>
                                    <td class="px-4 py-2 text-gray-600">{{ $compra->fecha_vencimiento?->format('d/m/Y') ?? 'Sin fecha' }}</td>
                                    <td class="px-4 py-2 text-right font-semibold text-red-600">${{ number_format($saldo, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">No hay compras pendientes de pago.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <section class="bg-white rounded shadow">
                <div class="px-4 py-3 border-b flex items-center justify-between">
                    <h3 class="flex items-center gap-2 font-bold text-gray-800"><x-icon name="box" class="text-blue-600" /> Productos recientes</h3>
                    <a href="{{ route('admin.productos.index') }}" class="text-sm text-blue-600 hover:underline">Ver todos</a>
                </div>
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-2">Producto</th>
                            <th class="px-4 py-2">Subcategoría</th>
                            <th class="px-4 py-2">Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productosRecientes as $producto)
                            <tr class="border-b">
                                <td class="px-4 py-2 font-medium text-gray-800">{{ $producto->nombre }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $producto->subcategoria?->nombre ?? '—' }}</td>
                                <td class="px-4 py-2">{{ $producto->stock_actual }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">Sin productos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>

            <section class="bg-white rounded shadow">
                <div class="px-4 py-3 border-b flex items-center justify-between">
                    <h3 class="flex items-center gap-2 font-bold text-gray-800"><x-icon name="alert" class="text-red-600" /> Alertas de stock</h3>
                    <span class="text-xs text-gray-500">stock actual ≤ mínimo</span>
                </div>
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-2">Producto</th>
                            <th class="px-4 py-2">Stock</th>
                            <th class="px-4 py-2">Mínimo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productosStockBajo as $producto)
                            <tr class="border-b">
                                <td class="px-4 py-2 font-medium text-gray-800">{{ $producto->nombre }}</td>
                                <td class="px-4 py-2 text-red-600 font-bold">{{ $producto->stock_actual }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $producto->stock_minimo }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">Todo el stock está por encima del mínimo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </div>
    </main>
</body>
</html>

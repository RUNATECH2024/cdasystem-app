<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Admin CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">
    @include('admin.partials.header')

    <div class="max-w-7xl mx-auto px-4 mt-6 pb-12">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Reportes</h2>
                <p class="text-sm text-gray-500">Periodo: {{ $desde->format('d/m/Y') }} — {{ $hasta->format('d/m/Y') }}</p>
            </div>
            <a href="{{ route('admin.productos.index') }}" class="bg-white text-gray-700 px-4 py-2 rounded shadow hover:bg-gray-50 font-medium">Inventario</a>
        </div>

        <form method="GET" action="{{ route('admin.reportes.index') }}" class="bg-white rounded shadow p-4 mb-6 flex flex-wrap items-end gap-3">
            <div>
                <label class="flex items-center gap-1.5 text-xs font-bold text-gray-600 uppercase mb-1"><x-icon name="calendar" class="h-3.5 w-3.5" /> Desde</label>
                <input type="date" name="desde" value="{{ $desde->format('Y-m-d') }}" class="border rounded px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="flex items-center gap-1.5 text-xs font-bold text-gray-600 uppercase mb-1"><x-icon name="calendar" class="h-3.5 w-3.5" /> Hasta</label>
                <input type="date" name="hasta" value="{{ $hasta->format('Y-m-d') }}" class="border rounded px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
            </div>
            <button type="submit" class="inline-flex items-center gap-2 bg-gray-800 text-white px-4 py-2 rounded text-sm hover:bg-gray-900"><x-icon name="refresh" /> Actualizar</button>

            <div class="flex flex-wrap items-center gap-2 md:ml-auto">
                <a href="{{ route('admin.reportes.exportar', ['tipo' => 'inventario']) }}"
                   class="bg-blue-50 text-blue-700 px-3 py-2 rounded text-xs font-semibold hover:bg-blue-100">
                    CSV inventario
                </a>
                <a href="{{ route('admin.reportes.exportar', ['tipo' => 'ventas', 'desde' => $desde->format('Y-m-d'), 'hasta' => $hasta->format('Y-m-d')]) }}"
                   class="bg-green-50 text-green-700 px-3 py-2 rounded text-xs font-semibold hover:bg-green-100">
                    CSV ventas
                </a>
                <a href="{{ route('admin.reportes.pdf', ['tipo' => 'inventario']) }}"
                   class="bg-purple-50 text-purple-700 px-3 py-2 rounded text-xs font-semibold hover:bg-purple-100">
                    PDF inventario
                </a>
                <a href="{{ route('admin.reportes.pdf', ['tipo' => 'ventas', 'desde' => $desde->format('Y-m-d'), 'hasta' => $hasta->format('Y-m-d')]) }}"
                   class="bg-orange-50 text-orange-700 px-3 py-2 rounded text-xs font-semibold hover:bg-orange-100">
                    PDF ventas
                </a>
            </div>
        </form>

        <h3 class="text-lg font-bold text-gray-700 mb-3">Inventario</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-4">
            <div class="bg-white rounded shadow p-4">
                <p class="text-xs text-gray-500 uppercase">Productos</p>
                <p class="text-2xl font-bold text-gray-800">{{ $inventario['productos'] }}</p>
                <p class="text-xs text-gray-500">{{ $inventario['activos'] }} activos</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-xs text-gray-500 uppercase">Unidades</p>
                <p class="text-2xl font-bold text-gray-800">{{ $inventario['unidades'] }}</p>
                <p class="text-xs text-gray-500">en stock</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-xs text-gray-500 uppercase">Valor costo</p>
                <p class="text-2xl font-bold text-gray-800">${{ number_format($inventario['costo'], 2) }}</p>
                <p class="text-xs text-gray-500">inventario</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-xs text-gray-500 uppercase">Valor venta</p>
                <p class="text-2xl font-bold text-gray-800">${{ number_format($inventario['venta'], 2) }}</p>
                <p class="text-xs text-gray-500">potencial</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-xs text-gray-500 uppercase">Margen potencial</p>
                <p class="text-2xl font-bold {{ $inventario['venta'] - $inventario['costo'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    ${{ number_format($inventario['venta'] - $inventario['costo'], 2) }}
                </p>
                <p class="text-xs text-gray-500">venta - costo</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-xs text-gray-500 uppercase">Stock bajo</p>
                <p class="text-2xl font-bold {{ $inventario['stock_bajo'] > 0 ? 'text-red-600' : 'text-gray-800' }}">{{ $inventario['stock_bajo'] }}</p>
                <p class="text-xs text-gray-500">productos</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded shadow overflow-hidden">
                <div class="px-4 py-3 border-b">
                    <h4 class="font-bold text-gray-800">Stock por categoría</h4>
                </div>
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-2">Categoría</th>
                            <th class="px-4 py-2 text-right">Productos</th>
                            <th class="px-4 py-2 text-right">Unidades</th>
                            <th class="px-4 py-2 text-right">Valor costo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stockPorCategoria as $fila)
                            <tr class="border-b">
                                <td class="px-4 py-2 font-medium text-gray-800">{{ $fila['nombre'] }}</td>
                                <td class="px-4 py-2 text-right">{{ $fila['productos'] }}</td>
                                <td class="px-4 py-2 text-right">{{ $fila['unidades'] }}</td>
                                <td class="px-4 py-2 text-right">${{ number_format($fila['costo'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Sin datos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded shadow overflow-hidden">
                <div class="px-4 py-3 border-b flex items-center justify-between">
                    <h4 class="font-bold text-gray-800">Productos con stock bajo</h4>
                    <span class="text-xs text-gray-500">stock ≤ mínimo</span>
                </div>
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-2">Producto</th>
                            <th class="px-4 py-2">Subcategoría</th>
                            <th class="px-4 py-2 text-right">Stock</th>
                            <th class="px-4 py-2 text-right">Mínimo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productosStockBajo as $producto)
                            <tr class="border-b">
                                <td class="px-4 py-2 font-medium text-gray-800">{{ $producto->nombre }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $producto->subcategoria?->nombre ?? '—' }}</td>
                                <td class="px-4 py-2 text-right text-red-600 font-bold">{{ $producto->stock_actual }}</td>
                                <td class="px-4 py-2 text-right text-gray-500">{{ $producto->stock_minimo }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Todo el stock está por encima del mínimo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <h3 class="text-lg font-bold text-gray-700 mb-3">Ventas del periodo</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
            <div class="bg-white rounded shadow p-4">
                <p class="text-xs text-gray-500 uppercase">Ventas</p>
                <p class="text-2xl font-bold text-gray-800">{{ $ventas['cantidad'] }}</p>
                <p class="text-xs text-gray-500">transacciones</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-xs text-gray-500 uppercase">Ingresos</p>
                <p class="text-2xl font-bold text-green-600">${{ number_format($ventas['total'], 2) }}</p>
                <p class="text-xs text-gray-500">total vendido</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-xs text-gray-500 uppercase">Ticket promedio</p>
                <p class="text-2xl font-bold text-gray-800">${{ number_format($ventas['promedio'], 2) }}</p>
                <p class="text-xs text-gray-500">por venta</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-xs text-gray-500 uppercase">Impuestos</p>
                <p class="text-2xl font-bold text-gray-800">${{ number_format($ventas['impuesto'], 2) }}</p>
                <p class="text-xs text-gray-500">recaudados</p>
            </div>
        </div>

        @php $maxDia = max(1, (float) ($ventasPorDia->max('total') ?? 0)); @endphp

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded shadow overflow-hidden">
                <div class="px-4 py-3 border-b">
                    <h4 class="font-bold text-gray-800">Ventas por día</h4>
                </div>
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-2">Día</th>
                            <th class="px-4 py-2 text-right">Ventas</th>
                            <th class="px-4 py-2 text-right">Total</th>
                            <th class="px-4 py-2 w-1/3"> </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ventasPorDia as $fila)
                            <tr class="border-b">
                                <td class="px-4 py-2 text-gray-700">{{ $fila->dia }}</td>
                                <td class="px-4 py-2 text-right">{{ $fila->cantidad }}</td>
                                <td class="px-4 py-2 text-right font-semibold">${{ number_format((float) $fila->total, 2) }}</td>
                                <td class="px-4 py-2">
                                    <div class="h-2 bg-blue-500 rounded" style="width: {{ max(2, round(((float) $fila->total / $maxDia) * 100)) }}%"></div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Sin ventas en el periodo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded shadow overflow-hidden">
                    <div class="px-4 py-3 border-b">
                        <h4 class="font-bold text-gray-800">Ventas por método de pago</h4>
                    </div>
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-2">Método</th>
                                <th class="px-4 py-2 text-right">Ventas</th>
                                <th class="px-4 py-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ventasPorMetodo as $fila)
                                <tr class="border-b">
                                    <td class="px-4 py-2 text-gray-700">{{ $fila->metodo }}</td>
                                    <td class="px-4 py-2 text-right">{{ $fila->cantidad }}</td>
                                    <td class="px-4 py-2 text-right font-semibold">${{ number_format((float) $fila->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">Sin datos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="bg-white rounded shadow overflow-hidden">
                    <div class="px-4 py-3 border-b">
                        <h4 class="font-bold text-gray-800">Top productos vendidos</h4>
                    </div>
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-2">Producto</th>
                                <th class="px-4 py-2 text-right">Unidades</th>
                                <th class="px-4 py-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProductos as $fila)
                                <tr class="border-b">
                                    <td class="px-4 py-2 text-gray-700">{{ $fila->nombre }}</td>
                                    <td class="px-4 py-2 text-right">{{ $fila->unidades }}</td>
                                    <td class="px-4 py-2 text-right font-semibold">${{ number_format((float) $fila->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">Sin ventas en el periodo.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <h3 class="text-lg font-bold text-gray-700 mb-3">Compras, pedidos web y taller</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded shadow p-4">
                <p class="text-xs text-gray-500 uppercase">Compras</p>
                <p class="text-2xl font-bold text-gray-800">{{ $compras['cantidad'] }}</p>
                <p class="text-sm text-gray-600">${{ number_format($compras['total'], 2) }}</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-xs text-gray-500 uppercase">Pedidos web</p>
                <p class="text-2xl font-bold text-gray-800">{{ $pedidos['cantidad'] }}</p>
                <p class="text-sm text-gray-600">${{ number_format($pedidos['total'], 2) }}</p>
                <p class="text-xs {{ $pedidos['pendientes'] > 0 ? 'text-red-600 font-semibold' : 'text-gray-500' }}">{{ $pedidos['pendientes'] }} pendiente(s)</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-xs text-gray-500 uppercase">Órdenes de servicio</p>
                <p class="text-2xl font-bold text-gray-800">{{ $ordenes['cantidad'] }}</p>
                <p class="text-sm text-gray-600">${{ number_format($ordenes['total'], 2) }}</p>
                <p class="text-xs {{ $ordenes['abiertas'] > 0 ? 'text-yellow-600 font-semibold' : 'text-gray-500' }}">{{ $ordenes['abiertas'] }} abierta(s)</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
            <div>
                <h3 class="flex items-center gap-2 text-lg font-bold text-gray-700"><x-icon name="users" class="text-blue-600" /> Clientes</h3>
                <p class="text-sm text-gray-500">Cartera, clientes nuevos y ventas acumuladas en el periodo.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.clientes.index') }}" class="bg-white text-gray-700 px-3 py-2 rounded text-xs font-semibold shadow hover:bg-gray-50">Ver módulo</a>
                <a href="{{ route('admin.reportes.exportar', ['tipo' => 'clientes', 'desde' => $desde->format('Y-m-d'), 'hasta' => $hasta->format('Y-m-d')]) }}" class="bg-green-50 text-green-700 px-3 py-2 rounded text-xs font-semibold hover:bg-green-100">CSV clientes</a>
                <a href="{{ route('admin.reportes.pdf', ['tipo' => 'clientes', 'desde' => $desde->format('Y-m-d'), 'hasta' => $hasta->format('Y-m-d')]) }}" class="bg-orange-50 text-orange-700 px-3 py-2 rounded text-xs font-semibold hover:bg-orange-100">PDF clientes</a>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-4">
            <div class="bg-white rounded shadow p-4">
                <p class="text-xs text-gray-500 uppercase">Clientes</p>
                <p class="text-2xl font-bold text-gray-800">{{ $clientesResumen['total'] }}</p>
                <p class="text-xs text-gray-500">{{ $clientesResumen['activos'] }} activos · {{ $clientesResumen['inactivos'] }} inactivos</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-xs text-gray-500 uppercase">Nuevos</p>
                <p class="text-2xl font-bold text-blue-600">{{ $clientesResumen['nuevos'] }}</p>
                <p class="text-xs text-gray-500">en el periodo</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-xs text-gray-500 uppercase">Con ventas</p>
                <p class="text-2xl font-bold text-gray-800">{{ $clientesResumen['con_ventas'] }}</p>
                <p class="text-xs text-gray-500">en el periodo</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-xs text-gray-500 uppercase">Con saldo</p>
                <p class="text-2xl font-bold {{ $clientesResumen['con_saldo'] > 0 ? 'text-yellow-600' : 'text-gray-800' }}">{{ $clientesResumen['con_saldo'] }}</p>
                <p class="text-xs text-gray-500">clientes</p>
            </div>
            <div class="bg-white rounded shadow p-4 md:col-span-2">
                <p class="text-xs text-gray-500 uppercase">Saldo por cobrar</p>
                <p class="text-2xl font-bold {{ $clientesResumen['saldo'] > 0 ? 'text-red-600' : 'text-gray-800' }}">${{ number_format($clientesResumen['saldo'], 2) }}</p>
                <p class="text-xs text-gray-500">saldo pendiente total</p>
            </div>
        </div>

        <div class="bg-white rounded shadow overflow-x-auto mb-8">
            <div class="px-4 py-3 border-b flex flex-wrap items-center justify-between gap-2">
                <h4 class="font-bold text-gray-800">Clientes por venta en el periodo</h4>
                <span class="text-xs text-gray-500">Top {{ $clientesDetalle->count() }} · las exportaciones incluyen todos</span>
            </div>
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-2">Cliente</th>
                        <th class="px-4 py-2">Contacto</th>
                        <th class="px-4 py-2 text-right">Ventas</th>
                        <th class="px-4 py-2 text-right">Total vendido</th>
                        <th class="px-4 py-2 text-right">Órdenes</th>
                        <th class="px-4 py-2 text-right">Saldo</th>
                        <th class="px-4 py-2">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientesDetalle as $cliente)
                        <tr class="border-b">
                            <td class="px-4 py-2">
                                <a href="{{ route('admin.clientes.show', $cliente) }}" class="font-medium text-blue-700 hover:underline">{{ $cliente->nombre }}</a>
                                <span class="block text-xs text-gray-500">{{ $cliente->dni_ruc ?: 'Sin documento' }}</span>
                            </td>
                            <td class="px-4 py-2 text-gray-600">
                                <span class="block">{{ $cliente->telefono ?: 'Sin teléfono' }}</span>
                                <span class="block text-xs">{{ $cliente->email ?: 'Sin correo' }}</span>
                            </td>
                            <td class="px-4 py-2 text-right">{{ $cliente->ventas_periodo }}</td>
                            <td class="px-4 py-2 text-right font-semibold">${{ number_format((float) $cliente->ventas_total_periodo, 2) }}</td>
                            <td class="px-4 py-2 text-right">{{ $cliente->ordenes_periodo }}</td>
                            <td class="px-4 py-2 text-right {{ (float) $cliente->saldo_pendiente > 0 ? 'text-red-600 font-semibold' : 'text-gray-600' }}">${{ number_format((float) $cliente->saldo_pendiente, 2) }}</td>
                            <td class="px-4 py-2"><span class="rounded px-2 py-1 text-xs font-medium {{ $cliente->activo ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">{{ $cliente->activo ? 'Activo' : 'Inactivo' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">Sin clientes registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
            <div>
                <h3 class="flex items-center gap-2 text-lg font-bold text-gray-700"><x-icon name="truck" class="text-blue-600" /> Proveedores</h3>
                <p class="text-sm text-gray-500">Compras del periodo, saldos por pagar y proveedores más activos.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.proveedores.index') }}" class="bg-white text-gray-700 px-3 py-2 rounded text-xs font-semibold shadow hover:bg-gray-50">Ver módulo</a>
                <a href="{{ route('admin.reportes.exportar', ['tipo' => 'proveedores', 'desde' => $desde->format('Y-m-d'), 'hasta' => $hasta->format('Y-m-d')]) }}" class="bg-green-50 text-green-700 px-3 py-2 rounded text-xs font-semibold hover:bg-green-100">CSV proveedores</a>
                <a href="{{ route('admin.reportes.pdf', ['tipo' => 'proveedores', 'desde' => $desde->format('Y-m-d'), 'hasta' => $hasta->format('Y-m-d')]) }}" class="bg-orange-50 text-orange-700 px-3 py-2 rounded text-xs font-semibold hover:bg-orange-100">PDF proveedores</a>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-4">
            <div class="bg-white rounded shadow p-4">
                <p class="text-xs text-gray-500 uppercase">Proveedores</p>
                <p class="text-2xl font-bold text-gray-800">{{ $proveedoresResumen['total'] }}</p>
                <p class="text-xs text-gray-500">{{ $proveedoresResumen['activos'] }} activos · {{ $proveedoresResumen['inactivos'] }} inactivos</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-xs text-gray-500 uppercase">Compras</p>
                <p class="text-2xl font-bold text-gray-800">{{ $proveedoresResumen['compras'] }}</p>
                <p class="text-xs text-gray-500">en el periodo</p>
            </div>
            <div class="bg-white rounded shadow p-4 md:col-span-2">
                <p class="text-xs text-gray-500 uppercase">Total comprado</p>
                <p class="text-2xl font-bold text-gray-800">${{ number_format($proveedoresResumen['comprado'], 2) }}</p>
                <p class="text-xs text-gray-500">compras del periodo</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-xs text-gray-500 uppercase">Con saldo</p>
                <p class="text-2xl font-bold {{ $proveedoresResumen['con_saldo'] > 0 ? 'text-yellow-600' : 'text-gray-800' }}">{{ $proveedoresResumen['con_saldo'] }}</p>
                <p class="text-xs text-gray-500">proveedores</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-xs text-gray-500 uppercase">Saldo por pagar</p>
                <p class="text-2xl font-bold {{ $proveedoresResumen['saldo'] > 0 ? 'text-red-600' : 'text-gray-800' }}">${{ number_format($proveedoresResumen['saldo'], 2) }}</p>
                <p class="text-xs text-gray-500">saldo pendiente total</p>
            </div>
        </div>

        <div class="bg-white rounded shadow overflow-x-auto">
            <div class="px-4 py-3 border-b flex flex-wrap items-center justify-between gap-2">
                <h4 class="font-bold text-gray-800">Proveedores por compra en el periodo</h4>
                <span class="text-xs text-gray-500">Top {{ $proveedoresDetalle->count() }} · las exportaciones incluyen todos</span>
            </div>
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-2">Proveedor</th>
                        <th class="px-4 py-2">Contacto</th>
                        <th class="px-4 py-2 text-right">Compras</th>
                        <th class="px-4 py-2 text-right">Total comprado</th>
                        <th class="px-4 py-2 text-right">Saldo</th>
                        <th class="px-4 py-2">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proveedoresDetalle as $proveedor)
                        <tr class="border-b">
                            <td class="px-4 py-2">
                                <a href="{{ route('admin.proveedores.show', $proveedor) }}" class="font-medium text-blue-700 hover:underline">{{ $proveedor->nombre }}</a>
                                <span class="block text-xs text-gray-500">{{ $proveedor->ruc ?: 'Sin RUC' }}</span>
                            </td>
                            <td class="px-4 py-2 text-gray-600">
                                <span class="block">{{ $proveedor->telefono ?: 'Sin teléfono' }}</span>
                                <span class="block text-xs">{{ $proveedor->email ?: 'Sin correo' }}</span>
                            </td>
                            <td class="px-4 py-2 text-right">{{ $proveedor->compras_periodo }}</td>
                            <td class="px-4 py-2 text-right font-semibold">${{ number_format((float) $proveedor->compras_total_periodo, 2) }}</td>
                            <td class="px-4 py-2 text-right {{ (float) $proveedor->saldo_pendiente > 0 ? 'text-red-600 font-semibold' : 'text-gray-600' }}">${{ number_format((float) $proveedor->saldo_pendiente, 2) }}</td>
                            <td class="px-4 py-2"><span class="rounded px-2 py-1 text-xs font-medium {{ $proveedor->activo ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">{{ $proveedor->activo ? 'Activo' : 'Inactivo' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Sin proveedores registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

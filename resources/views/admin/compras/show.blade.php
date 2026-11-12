<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra #{{ $compra->id_compra }} - Admin CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">
    @include('admin.partials.header')
    <main class="max-w-6xl mx-auto px-4 mt-6 pb-10">
        <a href="{{ route('admin.compras.index') }}" class="text-sm text-blue-700 hover:underline">&larr; Compras</a>

        @php($colorEstado = match($compra->estado) {
            'Pagada' => 'bg-green-100 text-green-700',
            'Parcial' => 'bg-yellow-100 text-yellow-700',
            'Pendiente' => 'bg-red-100 text-red-700',
            default => 'bg-gray-200 text-gray-600',
        })

        <div class="flex flex-wrap items-end justify-between gap-3 mt-2 mb-5">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold text-gray-800"><x-icon name="receipt" class="h-6 w-6 text-blue-600" /> Compra #{{ $compra->id_compra }}</h1>
                <p class="text-sm text-gray-500">{{ $compra->numero_factura ? 'Factura '.$compra->numero_factura : 'Sin número de factura' }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1 rounded px-3 py-2 text-sm font-medium {{ $colorEstado }}"><x-icon name="check" /> {{ $compra->estado ?: 'Sin estado' }}</span>
                <a href="{{ route('admin.compras.pdf', $compra) }}" class="inline-flex items-center gap-2 bg-purple-700 text-white px-4 py-2 rounded text-sm font-medium hover:bg-purple-800"><x-icon name="download" /> PDF</a>
            </div>
        </div>

        @if(session('success'))<div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="bg-red-100 text-red-800 p-3 rounded mb-4">{{ session('error') }}</div>@endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
            <section class="bg-white rounded shadow p-5">
                <h2 class="flex items-center gap-2 font-bold text-gray-800 mb-4"><x-icon name="truck" class="text-blue-600" /> Proveedor</h2>
                <p class="font-bold text-gray-800">{{ $compra->proveedor?->nombre ?? 'Proveedor no disponible' }}</p>
                <dl class="mt-3 space-y-2 text-sm">
                    <div class="flex items-center justify-between gap-3"><dt class="text-gray-500">RUC</dt><dd class="font-medium">{{ $compra->proveedor?->ruc ?: '—' }}</dd></div>
                    <div class="flex items-center justify-between gap-3"><dt class="text-gray-500">Teléfono</dt><dd class="font-medium">{{ $compra->proveedor?->telefono ?: '—' }}</dd></div>
                    <div class="flex items-center justify-between gap-3"><dt class="text-gray-500">Saldo proveedor</dt><dd class="font-bold {{ (float) ($compra->proveedor?->saldo_pendiente ?? 0) > 0 ? 'text-red-600' : 'text-gray-800' }}">${{ number_format((float) ($compra->proveedor?->saldo_pendiente ?? 0), 2) }}</dd></div>
                </dl>
                @if($compra->proveedor)
                    <a href="{{ route('admin.proveedores.show', $compra->proveedor) }}" class="mt-3 inline-block text-sm text-blue-700 hover:underline">Ver proveedor</a>
                @endif
            </section>

            <section class="bg-white rounded shadow p-5">
                <h2 class="flex items-center gap-2 font-bold text-gray-800 mb-4"><x-icon name="document" class="text-blue-600" /> Datos de la compra</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex items-center justify-between gap-3"><dt class="text-gray-500">Fecha de compra</dt><dd class="font-medium">{{ $compra->fecha_compra?->format('d/m/Y') ?? 'Sin fecha' }}</dd></div>
                    <div class="flex items-center justify-between gap-3"><dt class="text-gray-500">Vencimiento</dt><dd class="font-medium">{{ $compra->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</dd></div>
                    <div class="flex items-center justify-between gap-3"><dt class="text-gray-500">Ítems</dt><dd class="font-medium">{{ $compra->detalles->count() }}</dd></div>
                    <div class="flex items-center justify-between gap-3"><dt class="text-gray-500">Registró</dt><dd class="font-medium">{{ $compra->usuario?->nombre ?? 'Sistema' }}</dd></div>
                </dl>
            </section>

            <section class="bg-white rounded shadow p-5">
                <h2 class="flex items-center gap-2 font-bold text-gray-800 mb-4"><x-icon name="cash" class="text-blue-600" /> Resumen de pago</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex items-center justify-between"><dt class="text-gray-500">Subtotal</dt><dd class="font-medium">${{ number_format((float) $compra->subtotal, 2) }}</dd></div>
                    <div class="flex items-center justify-between"><dt class="text-gray-500">Impuesto</dt><dd class="font-medium">${{ number_format((float) $compra->impuesto, 2) }}</dd></div>
                    <div class="flex items-center justify-between border-t pt-2"><dt class="font-bold text-gray-800">Total</dt><dd class="font-bold text-gray-800">${{ number_format((float) $compra->total, 2) }}</dd></div>
                    <div class="flex items-center justify-between"><dt class="text-gray-500">Pagado</dt><dd class="font-medium text-green-700">${{ number_format($pagado, 2) }}</dd></div>
                    <div class="flex items-center justify-between"><dt class="text-gray-500">Saldo</dt><dd class="font-bold {{ $saldo > 0 ? 'text-red-600' : 'text-green-700' }}">${{ number_format($saldo, 2) }}</dd></div>
                </dl>
            </section>
        </div>

        <section class="bg-white rounded shadow mb-6">
            <div class="px-4 py-3 border-b">
                <h2 class="flex items-center gap-2 font-bold text-gray-800"><x-icon name="box" class="text-blue-600" /> Productos comprados</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-2">Producto</th>
                            <th class="px-4 py-2">SKU</th>
                            <th class="px-4 py-2 text-center">Cantidad</th>
                            <th class="px-4 py-2 text-right">Costo unitario</th>
                            <th class="px-4 py-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($compra->detalles as $detalle)
                            <tr class="border-b">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $detalle->producto?->nombre ?? 'Producto eliminado' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $detalle->producto?->sku ?: '—' }}</td>
                                <td class="px-4 py-3 text-center font-medium">{{ $detalle->cantidad }}</td>
                                <td class="px-4 py-3 text-right">${{ number_format((float) $detalle->precio_costo_unitario, 2) }}</td>
                                <td class="px-4 py-3 text-right font-semibold">${{ number_format((float) $detalle->precio_costo_unitario * (int) $detalle->cantidad, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Esta compra no tiene productos registrados.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 font-semibold">
                        <tr><td colspan="4" class="px-4 py-3 text-right">Total</td><td class="px-4 py-3 text-right">${{ number_format((float) $compra->total, 2) }}</td></tr>
                    </tfoot>
                </table>
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <section class="bg-white rounded shadow lg:col-span-2">
                <div class="px-4 py-3 border-b">
                    <h2 class="flex items-center gap-2 font-bold text-gray-800"><x-icon name="cash" class="text-blue-600" /> Abonos</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-2">Fecha</th>
                                <th class="px-4 py-2">Método</th>
                                <th class="px-4 py-2">Registró</th>
                                <th class="px-4 py-2 text-right">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($compra->abonos->sortByDesc('fecha_abono') as $abono)
                                <tr class="border-b">
                                    <td class="px-4 py-3 text-gray-600">{{ $abono->fecha_abono?->format('d/m/Y H:i') ?? 'Sin fecha' }}</td>
                                    <td class="px-4 py-3">{{ $abono->metodo_pago ?: '—' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $abono->usuario?->nombre ?? 'Sistema' }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-green-700">${{ number_format((float) $abono->monto, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Todavía no se han registrado abonos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="bg-white rounded shadow p-5">
                <h2 class="flex items-center gap-2 font-bold text-gray-800 mb-1"><x-icon name="plus" class="text-blue-600" /> Registrar abono</h2>
                @if($saldo > 0)
                    <p class="text-sm text-gray-500 mb-4">Saldo pendiente: <span class="font-bold text-red-600">${{ number_format($saldo, 2) }}</span></p>

                    @if($errors->any())
                        <div class="bg-red-50 text-red-700 p-2 rounded mb-3 text-sm">
                            <ul class="list-disc ml-4">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.compras.abonos.store', $compra) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label for="monto" class="block text-sm font-medium mb-1">Monto <span class="text-red-700">*</span></label>
                            <input id="monto" type="number" name="monto" value="{{ old('monto') }}" min="0.01" max="{{ $saldo }}" step="0.01" required class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                        </div>
                        <div>
                            <label for="metodo_pago_abono" class="block text-sm font-medium mb-1">Método de pago</label>
                            <select id="metodo_pago_abono" name="metodo_pago" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                                @foreach(['Efectivo', 'Transferencia', 'Tarjeta', 'Cheque'] as $metodo)
                                    <option value="{{ $metodo }}" @selected(old('metodo_pago', 'Efectivo') === $metodo)>{{ $metodo }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="fecha_abono" class="block text-sm font-medium mb-1">Fecha del abono</label>
                            <input id="fecha_abono" type="date" name="fecha_abono" value="{{ old('fecha_abono', now()->toDateString()) }}" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                        </div>
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-800"><x-icon name="check" /> Guardar abono</button>
                    </form>
                @else
                    <div class="mt-3 rounded bg-green-50 text-green-800 p-4 text-sm">
                        <p class="flex items-center gap-2 font-semibold"><x-icon name="check" /> Compra pagada en su totalidad</p>
                        <p class="mt-1">No hay saldo pendiente por abonar.</p>
                    </div>
                @endif
            </section>
        </div>
    </main>
</body>
</html>

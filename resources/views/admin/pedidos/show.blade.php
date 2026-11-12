<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido #{{ $pedido->id_pedido }} - Admin CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">
    @include('admin.partials.header')
    <main class="max-w-5xl mx-auto px-4 mt-6 pb-10">
        @if(session('success'))<div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="bg-red-100 text-red-800 p-3 rounded mb-4">{{ session('error') }}</div>@endif
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <div>
                <a href="{{ route('admin.pedidos.index') }}" class="text-sm text-blue-700 hover:underline">← Pedidos web</a>
                <h1 class="text-2xl font-bold text-gray-800 mt-1">Pedido #{{ $pedido->id_pedido }}</h1>
                <p class="text-sm text-gray-500">{{ $pedido->fecha_pedido?->format('d/m/Y H:i') ?? 'Sin fecha' }}</p>
            </div>
            @if(in_array($pedido->estado_pedido, ['Pendiente', 'Pagado'], true))
                <form action="{{ route('admin.pedidos.atendido', $pedido) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="bg-green-700 text-white px-4 py-2 rounded text-sm font-medium hover:bg-green-800">Iniciar preparación</button>
                </form>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
            <section class="bg-white rounded shadow p-5">
                <h2 class="font-bold text-gray-800 border-b pb-2 mb-3">Cliente y entrega</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-xs uppercase text-gray-500">Contacto</dt><dd>{{ $pedido->nombre_contacto ?: $pedido->cliente?->nombre ?: '—' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Documento</dt><dd>{{ $pedido->dni_ruc ?: '—' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Correo</dt><dd>{{ $pedido->email_contacto ?: $pedido->cliente?->email ?: '—' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Teléfono</dt><dd>{{ $pedido->telefono_contacto ?: '—' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Dirección</dt><dd>{{ $pedido->direccion_envio ?: '—' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Ciudad / provincia</dt><dd>{{ collect([$pedido->ciudad, $pedido->provincia])->filter()->join(', ') ?: '—' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Entrega</dt><dd>{{ $pedido->metodo_entrega ?: '—' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Referencia</dt><dd>{{ $pedido->referencia_envio ?: '—' }}</dd></div>
                </dl>
                @if($pedido->informacion_adicional)<p class="text-sm text-gray-600 mt-4"><strong>Información adicional:</strong> {{ $pedido->informacion_adicional }}</p>@endif
            </section>

            <section class="bg-white rounded shadow p-5">
                <h2 class="font-bold text-gray-800 border-b pb-2 mb-3">Estado y pago</h2>
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-xs uppercase text-gray-500">Estado</dt><dd>{{ $pedido->estado_pedido ?: '—' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Método de pago</dt><dd>{{ $pedido->metodo_pago ?: '—' }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Empresa</dt><dd>{{ $pedido->empresa ?: '—' }}</dd></div>
                    <div class="border-t pt-3"><dt class="text-xs uppercase text-gray-500">Subtotal</dt><dd>${{ number_format((float) $pedido->subtotal, 2) }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Envío</dt><dd>${{ number_format((float) $pedido->costo_envio, 2) }}</dd></div>
                    <div><dt class="text-xs uppercase text-gray-500">Total</dt><dd class="text-lg font-bold">${{ number_format((float) $pedido->total, 2) }}</dd></div>
                </dl>
            </section>
        </div>

        <section class="bg-white rounded shadow overflow-x-auto">
            <h2 class="font-bold text-gray-800 p-5 border-b">Artículos del pedido</h2>
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr><th class="px-5 py-3">Equipo / producto</th><th class="px-5 py-3">Datos técnicos</th><th class="px-5 py-3">Cantidad</th><th class="px-5 py-3">Precio unitario</th><th class="px-5 py-3 text-right">Importe</th></tr></thead>
                <tbody>
                    @forelse($pedido->detalles as $detalle)
                        <tr class="border-t align-top">
                            <td class="px-5 py-3 min-w-52">
                                <p class="font-semibold text-gray-800">{{ $detalle->producto?->nombre ?? 'Producto eliminado' }}</p>
                                @if($detalle->producto?->descripcion_corta)<p class="text-xs text-gray-600 mt-1">{{ $detalle->producto->descripcion_corta }}</p>@endif
                                @if($detalle->producto?->descripcion_larga)<p class="text-xs text-gray-500 mt-1 whitespace-pre-line">{{ $detalle->producto->descripcion_larga }}</p>@endif
                            </td>
                            <td class="px-5 py-3 min-w-56">
                                @if($detalle->producto)
                                    <p class="text-gray-700">SKU: {{ $detalle->producto->sku ?: '—' }} · Código: {{ $detalle->producto->codigo_barras ?: '—' }}</p>
                                    <p class="text-gray-700">Marca: {{ $detalle->producto->marca ?: '—' }} · Condición: {{ $detalle->producto->condicion ?: '—' }}</p>
                                    <p class="text-gray-700">Categoría: {{ $detalle->producto->subcategoria?->categoria?->nombre ?? '—' }} / {{ $detalle->producto->subcategoria?->nombre ?? '—' }}</p>
                                    <p class="text-gray-700">Garantía de fábrica: {{ $detalle->producto->garantia_meses ? $detalle->producto->garantia_meses.' meses' : 'Sin garantía' }}</p>
                                    @foreach($detalle->producto->atributos as $atributo)
                                        <p class="text-xs text-gray-600">{{ $atributo->nombre_atributo }}: {{ $atributo->valor_atributo }}</p>
                                    @endforeach
                                @endif
                                @foreach($detalle->garantias as $garantia)
                                    <p class="text-xs text-green-700 mt-1">Garantía {{ $garantia->estado ?: 'Activa' }} · Serie: {{ $garantia->numero_serie_equipo ?: '—' }} · Vence: {{ $garantia->fecha_fin?->format('d/m/Y') ?? '—' }}</p>
                                    @if($garantia->observaciones)<p class="text-xs text-gray-500">{{ $garantia->observaciones }}</p>@endif
                                @endforeach
                            </td>
                            <td class="px-5 py-3">{{ $detalle->cantidad }}</td>
                            <td class="px-5 py-3 whitespace-nowrap">${{ number_format((float) $detalle->precio_unitario, 2) }}</td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">${{ number_format((float) $detalle->precio_unitario * $detalle->cantidad, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-gray-500">Este pedido no tiene artículos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
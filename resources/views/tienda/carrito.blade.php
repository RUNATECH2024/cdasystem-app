<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('tienda.partials.seo', [
        'seoTitle' => 'Mi carrito | CDA System',
        'seoRobots' => 'noindex, nofollow',
        'seoCanonical' => route('carrito.index'),
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">

    <!-- Header -->
    <header class="bg-white py-4 px-6 md:px-8 shadow-sm">
        <div class="max-w-6xl mx-auto flex items-center justify-between gap-4">
            <a href="/" class="flex items-center space-x-2">
                <div class="w-10 h-10 bg-blue-600 text-white flex items-center justify-center font-bold text-xl rounded">T</div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800 leading-tight">CDA System</h1>
                    <p class="text-xs text-gray-500 italic">Store</p>
                </div>
            </a>

            <div class="flex items-center space-x-5 text-gray-700 text-sm">
                <a href="/" class="text-blue-600 hover:underline font-medium hidden sm:inline">← Seguir comprando</a>

                @include('tienda.partials.auth-links')

                <a href="{{ route('carrito.index') }}" class="relative hover:text-yellow-500" title="Carrito">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="absolute -top-2 -right-2 bg-yellow-500 text-white text-xs rounded-full px-1.5">{{ $cantidadTotal }}</span>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6">Mi Carrito</h2>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">{{ $errors->first() }}</div>
        @endif

        @if($ajustado)
            <div class="bg-yellow-100 text-yellow-800 p-3 rounded mb-4">
                Algunos productos se ajustaron o retiraron porque cambiaron su disponibilidad o stock.
            </div>
        @endif

        @if(empty($lineas))
            <div class="bg-white p-10 rounded-lg shadow-sm text-center">
                <i class="fa-solid fa-cart-shopping text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 mb-4">Tu carrito está vacío.</p>
                <a href="/" class="inline-block bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 transition font-medium">Ir a comprar</a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

                <!-- Productos -->
                <section class="lg:col-span-2 space-y-4">
                    @foreach($lineas as $id => $linea)
                        @php $producto = $linea['producto']; @endphp
                        <article class="bg-white rounded-lg shadow-sm p-4 md:p-5">
                            <div class="flex gap-4">
                                <div class="w-20 h-20 md:w-24 md:h-24 shrink-0 bg-gray-100 rounded flex items-center justify-center overflow-hidden">
                                    @if($producto->imagen_principal)
                                        <img src="{{ asset('productos/'.$producto->imagen_principal) }}" alt="{{ $producto->nombre }}" class="w-full h-full object-contain">
                                    @else
                                        <i class="fa-solid fa-box text-2xl text-gray-300"></i>
                                    @endif
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $producto->sku ?: 'SIN SKU' }}</p>
                                            <a href="{{ route('producto.show', $producto->id_producto) }}" class="block text-sm md:text-base font-semibold text-gray-800 hover:text-blue-600 leading-snug">
                                                {{ $producto->nombre }}
                                            </a>
                                            @if($producto->marca)
                                                <p class="text-xs text-gray-500 mt-1">{{ $producto->marca }}</p>
                                            @endif
                                        </div>
                                        <p class="text-lg font-bold text-gray-800 whitespace-nowrap">${{ number_format($linea['importe'], 2) }}</p>
                                    </div>

                                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                                        <span class="text-sm text-gray-500">${{ number_format($linea['precio'], 2) }} c/u</span>

                                        <div class="flex items-center gap-4">
                                            <div class="flex items-center border border-gray-300 rounded overflow-hidden">
                                                <form action="{{ route('carrito.update', $id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="accion" value="decrementar">
                                                    <button type="submit" class="px-3 py-1.5 text-gray-600 hover:bg-gray-100" aria-label="Disminuir cantidad">−</button>
                                                </form>

                                                <span class="px-4 py-1.5 text-sm font-semibold text-gray-800 border-x border-gray-300 min-w-[2.5rem] text-center">{{ $linea['cantidad'] }}</span>

                                                <form action="{{ route('carrito.update', $id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="accion" value="incrementar">
                                                    <button type="submit" class="px-3 py-1.5 text-gray-600 hover:bg-gray-100" aria-label="Aumentar cantidad">+</button>
                                                </form>
                                            </div>

                                            <form action="{{ route('carrito.remove', $id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="text-sm text-red-500 hover:text-red-700 hover:underline">Remover</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach

                    <a href="/" class="inline-flex items-center gap-2 text-sm text-blue-600 hover:underline">
                        <i class="fa-solid fa-arrow-left"></i> Seguir comprando
                    </a>
                </section>

                <!-- Resumen -->
                <aside class="bg-white rounded-lg shadow-sm p-6 lg:sticky lg:top-6">
                    <h3 class="text-lg font-bold text-gray-800 border-b pb-3">Resumen de Compra</h3>

                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Subtotal</dt>
                            <dd class="font-medium text-gray-800">${{ number_format($subtotal, 2) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Envío</dt>
                            <dd class="font-medium text-gray-800">${{ number_format(0, 2) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600">I.V.A (15%)</dt>
                            <dd class="font-medium text-gray-800">${{ number_format($iva, 2) }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 pt-4 border-t flex justify-between items-center">
                        <span class="text-base font-semibold text-gray-700">Total</span>
                        <span class="text-2xl font-bold text-gray-900">${{ number_format($total, 2) }}</span>
                    </div>

                    <div class="mt-6 space-y-3">
                        <a href="{{ route('checkout.index') }}" class="block w-full text-center bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-bold tracking-wide">
                            PROCEDER CON EL PAGO
                        </a>

                        <form action="{{ route('carrito.checkout') }}" method="POST">
                            @csrf
                            <input type="hidden" name="accion" value="vendedor">
                            <button type="submit" class="w-full bg-yellow-500 text-white px-6 py-3 rounded-lg hover:bg-yellow-600 transition font-semibold">
                                Enviar pedido al vendedor
                            </button>
                        </form>
                    </div>

                    <details class="mt-4 border-t pt-4 text-sm text-gray-600">
                        <summary class="cursor-pointer font-medium text-blue-600 hover:underline">Estimación de cuotas</summary>
                        <ul class="mt-2 space-y-1">
                            @foreach([3, 6, 12] as $numeroCuotas)
                                <li class="flex justify-between">
                                    <span>{{ $numeroCuotas }} cuotas</span>
                                    <span class="font-medium text-gray-800">${{ number_format($total / $numeroCuotas, 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <p class="mt-2 text-xs text-gray-400">Valores referenciales; sujetos a aprobación.</p>
                    </details>

                    <p class="mt-4 text-xs text-gray-400 text-center">Precio final con IVA incluido.</p>
                </aside>
            </div>
        @endif
    </main>

</body>
</html>

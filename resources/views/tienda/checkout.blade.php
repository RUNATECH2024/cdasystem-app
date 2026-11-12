<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('tienda.partials.seo', [
        'seoTitle' => 'Facturación y envío | CDA System',
        'seoRobots' => 'noindex, nofollow',
        'seoCanonical' => route('checkout.index'),
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
                <a href="{{ route('carrito.index') }}" class="text-blue-600 hover:underline font-medium hidden sm:inline">← Volver al carrito</a>
                @include('tienda.partials.auth-links')
                <a href="{{ route('carrito.index') }}" class="relative hover:text-yellow-500" title="Carrito">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="absolute -top-2 -right-2 bg-yellow-500 text-white text-xs rounded-full px-1.5">{{ $cantidadTotal }}</span>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-800">Facturación y envío</h2>
            <a href="{{ route('carrito.index') }}" class="text-sm text-blue-600 hover:underline">← Volver al carrito</a>
        </div>

        @if(session('error'))
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                Revisa los campos marcados: {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('checkout.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            @csrf

            <div class="lg:col-span-2 space-y-6">

                <!-- Datos de facturación -->
                <section class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-800 border-b pb-3">Datos de facturación</h3>

                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="nombre_contacto" class="block text-sm font-medium text-gray-700">Nombre cliente *</label>
                            <input id="nombre_contacto" name="nombre_contacto" type="text" value="{{ old('nombre_contacto', $cliente->nombre) }}" maxlength="150" required class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('nombre_contacto') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="dni_ruc" class="block text-sm font-medium text-gray-700">Cédula / RUC</label>
                            <input id="dni_ruc" name="dni_ruc" type="text" value="{{ old('dni_ruc', $cliente->dni_ruc) }}" maxlength="20" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('dni_ruc') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="empresa" class="block text-sm font-medium text-gray-700">Empresa <span class="text-gray-400 font-normal">(opcional)</span></label>
                            <input id="empresa" name="empresa" type="text" value="{{ old('empresa') }}" maxlength="150" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('empresa') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="telefono_contacto" class="block text-sm font-medium text-gray-700">Teléfono *</label>
                            <input id="telefono_contacto" name="telefono_contacto" type="tel" value="{{ old('telefono_contacto', $cliente->telefono) }}" maxlength="20" required class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('telefono_contacto') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="email_contacto" class="block text-sm font-medium text-gray-700">Email *</label>
                            <input id="email_contacto" name="email_contacto" type="email" value="{{ old('email_contacto', $cliente->email) }}" maxlength="150" required class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('email_contacto') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                <!-- Dirección de envío -->
                <section class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-800 border-b pb-3">Dirección de envío</h3>

                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="provincia" class="block text-sm font-medium text-gray-700">Provincia *</label>
                            <select id="provincia" name="provincia" required class="mt-1 w-full border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecciona una provincia</option>
                                @foreach($provincias as $provincia)
                                    <option value="{{ $provincia }}" @selected(old('provincia') === $provincia)>{{ $provincia }}</option>
                                @endforeach
                            </select>
                            @error('provincia') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="ciudad" class="block text-sm font-medium text-gray-700">Ciudad *</label>
                            <input id="ciudad" name="ciudad" type="text" value="{{ old('ciudad') }}" maxlength="100" required class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('ciudad') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <div class="flex items-center justify-between">
                                <label for="direccion_envio" class="block text-sm font-medium text-gray-700">Dirección domiciliaria (envío) *</label>
                                <span id="contador-direccion_envio" class="text-xs text-gray-400">0/120</span>
                            </div>
                            <input id="direccion_envio" name="direccion_envio" type="text" value="{{ old('direccion_envio', $cliente->direccion) }}" maxlength="120" required data-contador class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('direccion_envio') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <div class="flex items-center justify-between">
                                <label for="referencia_envio" class="block text-sm font-medium text-gray-700">Referencia domiciliaria <span class="text-gray-400 font-normal">(opcional)</span></label>
                                <span id="contador-referencia_envio" class="text-xs text-gray-400">0/100</span>
                            </div>
                            <input id="referencia_envio" name="referencia_envio" type="text" value="{{ old('referencia_envio') }}" maxlength="100" data-contador class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('referencia_envio') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <div class="flex items-center justify-between">
                                <label for="informacion_adicional" class="block text-sm font-medium text-gray-700">Información adicional <span class="text-gray-400 font-normal">(opcional)</span></label>
                                <span id="contador-informacion_adicional" class="text-xs text-gray-400">0/100</span>
                            </div>
                            <textarea id="informacion_adicional" name="informacion_adicional" rows="3" maxlength="100" data-contador class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('informacion_adicional') }}</textarea>
                            @error('informacion_adicional') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                <!-- Método de entrega -->
                <section class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-800 border-b pb-3">Método de Entrega</h3>

                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($metodosEntrega as $valor => $etiqueta)
                            <label class="flex items-start gap-3 border border-gray-300 rounded-lg px-4 py-3 cursor-pointer hover:border-blue-500">
                                <input type="radio" name="metodo_entrega" value="{{ $valor }}" @checked(old('metodo_entrega', 'servientrega') === $valor) class="mt-1 accent-blue-600">
                                <span>
                                    <span class="block text-sm font-medium text-gray-800">{{ $etiqueta }}</span>
                                    <span class="block text-xs text-gray-500">
                                        {{ $valor === 'retiro' ? 'Retira en nuestra oficina sin costo.' : 'Entrega a domicilio a nivel nacional.' }}
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('metodo_entrega') <p class="text-xs text-red-500 mt-2">{{ $message }}</p> @enderror
                </section>

                <!-- Método de pago -->
                <section class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-800 border-b pb-3">Método de Pago</h3>

                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @foreach($metodosPago as $valor => $etiqueta)
                            @php
                                $icono = match ($valor) {
                                    'transferencia' => 'fa-building-columns',
                                    'tarjeta' => 'fa-credit-card',
                                    default => 'fa-mobile-screen-button',
                                };
                            @endphp
                            <label class="flex items-start gap-3 border border-gray-300 rounded-lg px-4 py-3 cursor-pointer hover:border-blue-500">
                                <input type="radio" name="metodo_pago" value="{{ $valor }}" @checked(old('metodo_pago', 'transferencia') === $valor) class="mt-1 accent-blue-600">
                                <span>
                                    <span class="block text-sm font-medium text-gray-800">
                                        <i class="fa-solid {{ $icono }} text-gray-400 mr-1"></i>{{ $etiqueta }}
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('metodo_pago') <p class="text-xs text-red-500 mt-2">{{ $message }}</p> @enderror

                    <p class="mt-3 text-xs text-gray-500">
                        <i class="fa-solid fa-circle-info mr-1"></i>
                        El pedido se procesará cuando se confirme el pago.
                    </p>
                </section>
            </div>

            <!-- Resumen -->
            <aside class="bg-white rounded-lg shadow-sm p-6 lg:sticky lg:top-6">
                <h3 class="text-lg font-bold text-gray-800 border-b pb-3">Detalle de productos</h3>

                <ul class="mt-3 divide-y divide-gray-100">
                    @foreach($lineas as $linea)
                        <li class="py-3 flex gap-3">
                            <span class="shrink-0 w-7 h-7 bg-gray-100 text-gray-700 text-xs font-semibold rounded-full flex items-center justify-center">{{ $linea['cantidad'] }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 leading-snug">{{ $linea['producto']->nombre }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $linea['producto']->sku }}</p>
                            </div>
                            <p class="text-sm font-semibold text-gray-800 whitespace-nowrap">${{ number_format($linea['importe'], 2) }}</p>
                        </li>
                    @endforeach
                </ul>

                <h3 class="text-lg font-bold text-gray-800 border-b pb-3 mt-5">Resumen de Compra</h3>

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

                <button type="submit" class="mt-6 w-full bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-bold tracking-wide">
                    FINALIZAR COMPRA
                </button>

                <p class="mt-3 text-xs text-gray-400 text-center">
                    El pedido se procesará cuando se confirme el pago.
                </p>
            </aside>
        </form>
    </main>

    <script>
        document.querySelectorAll('[data-contador]').forEach(function (campo) {
            var salida = document.getElementById('contador-' + campo.getAttribute('name'));

            if (!salida) {
                return;
            }

            var actualizar = function () {
                salida.textContent = campo.value.length + '/' + campo.getAttribute('maxlength');
            };

            campo.addEventListener('input', actualizar);
            actualizar();
        });
    </script>

</body>
</html>

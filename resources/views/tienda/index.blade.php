<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $seoJsonLd = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => 'CDA System',
                'url' => url('/'),
                'inLanguage' => 'es-EC',
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => route('buscar').'?q={search_term_string}',
                    'query-input' => 'required name=search_term_string',
                ],
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => 'CDA System',
                'url' => url('/'),
                'logo' => asset('favicon.ico'),
                'contactPoint' => [
                    '@type' => 'ContactPoint',
                    'telephone' => '+593980821599',
                    'contactType' => 'customer service',
                    'areaServed' => 'EC',
                    'availableLanguage' => ['es'],
                ],
            ],
        ];
    @endphp
    @include('tienda.partials.seo', [
        'seoTitle' => 'CDA System Store | Tecnología, repuestos y soporte técnico',
        'seoDescription' => 'Compra computadoras, componentes, impresoras, repuestos y accesorios en CDA System. Envíos a todo el país, garantía y soporte técnico especializado.',
        'seoCanonical' => url('/'),
        'seoImage' => asset('Categorias/computadores.webp'),
        'seoJsonLd' => $seoJsonLd,
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">

    @include('tienda.partials.site-header')

    @if(session('success'))
        <div class="max-w-7xl mx-auto mt-4 px-4">
            <div class="bg-green-100 text-green-700 p-3 rounded">{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto mt-4 px-4">
            <div class="bg-red-100 text-red-700 p-3 rounded">{{ session('error') }}</div>
        </div>
    @endif

    <!-- Hero -->
    <section class="max-w-7xl mx-auto px-4 mt-6">
        <div class="rounded-2xl bg-gradient-to-r from-blue-700 to-blue-500 text-white p-8 md:p-12 shadow flex flex-col md:flex-row items-center gap-8">
            <div class="flex-1">
                <p class="uppercase tracking-widest text-xs text-blue-100 font-semibold">Tecnología para tu negocio</p>
                <h2 class="text-3xl md:text-4xl font-bold mt-2">Equipos, componentes y soporte en un solo lugar</h2>
                <p class="text-blue-100 mt-3 max-w-xl">Encuentra computadoras, accesorios, consumibles y soluciones técnicas con garantía y atención especializada.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="#categorias" class="bg-yellow-500 text-white font-semibold px-5 py-2.5 rounded hover:bg-yellow-600 transition">Ver categorías</a>
                    <a href="{{ route('marcas') }}" class="bg-white/10 border border-white/40 text-white font-semibold px-5 py-2.5 rounded hover:bg-white/20 transition">Explorar marcas</a>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-3 text-center text-xs w-full md:w-auto">
                <div class="bg-white/10 rounded-xl px-4 py-5">
                    <i class="fa-solid fa-truck-fast text-2xl mb-2"></i>
                    <p class="font-semibold">Envíos</p>
                    <p class="text-blue-100">A todo el país</p>
                </div>
                <div class="bg-white/10 rounded-xl px-4 py-5">
                    <i class="fa-solid fa-shield-halved text-2xl mb-2"></i>
                    <p class="font-semibold">Garantía</p>
                    <p class="text-blue-100">Productos nuevos</p>
                </div>
                <div class="bg-white/10 rounded-xl px-4 py-5">
                    <i class="fa-solid fa-headset text-2xl mb-2"></i>
                    <p class="font-semibold">Soporte</p>
                    <p class="text-blue-100">Asesoría técnica</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Productos destacados -->
    @if($productosDestacados->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 mt-10">
            <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Productos destacados</h2>
                    <p class="text-sm text-gray-500">Una selección de lo más buscado en CDA System.</p>
                </div>
                <a href="{{ route('buscar') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">Ver todo el catálogo</a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($productosDestacados as $prod)
                    <article class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden flex flex-col">
                        <a href="{{ route('producto.show', $prod->id_producto) }}" class="block h-44 bg-gray-100 flex items-center justify-center overflow-hidden">
                            @if($prod->imagen_principal)
                                <img src="{{ asset('productos/'.$prod->imagen_principal) }}" alt="{{ $prod->nombre }}" class="w-full h-full object-contain p-3" loading="lazy">
                            @else
                                <span class="text-gray-400 text-sm text-center px-6">{{ $prod->nombre }}</span>
                            @endif
                        </a>
                        <div class="p-4 flex flex-col flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded">{{ $prod->marca ?: 'Sin marca' }}</span>
                                @if($prod->condicion)
                                    <span class="bg-blue-50 text-blue-600 text-xs px-2 py-1 rounded">{{ $prod->condicion }}</span>
                                @endif
                            </div>
                            <a href="{{ route('producto.show', $prod->id_producto) }}" class="font-bold text-gray-700 text-sm hover:text-blue-600">
                                {{ $prod->nombre }}
                            </a>
                            @if($prod->descripcion_corta)
                                <p class="text-xs text-gray-500 mt-1">{{ $prod->descripcion_corta }}</p>
                            @endif
                            <div class="mt-auto pt-4 flex items-center justify-between gap-3">
                                <p class="text-lg font-bold text-gray-900">${{ number_format($prod->precio_venta, 2) }}</p>
                                @if($prod->stock_actual > 0)
                                    <form action="{{ route('carrito.add', $prod->id_producto) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-blue-600 text-white text-sm font-medium px-3 py-2 rounded hover:bg-blue-700 transition">Agregar</button>
                                    </form>
                                @else
                                    <span class="text-xs font-semibold text-red-600">Agotado</span>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Categorías -->
    <section id="categorias" class="max-w-7xl mx-auto px-4 mt-10 mb-12 scroll-mt-24">
        <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Categorías</h2>
                <p class="text-sm text-gray-500">Explora nuestro catálogo por tipo de producto.</p>
            </div>
            <a href="{{ route('marcas') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">Buscar por marca</a>
        </div>

        @if($categorias->isEmpty())
            <div class="bg-white p-8 rounded-xl shadow-sm text-center">
                <p class="text-gray-500">Todavía no hay categorías disponibles.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach($categorias as $cat)
                    <a href="{{ route('categoria.show', $cat->id_categoria) }}"
                       class="group bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden flex flex-col">
                        <div class="relative aspect-square bg-white overflow-hidden">
                            @if($cat->imagen)
                                <img src="{{ asset('Categorias/'.$cat->imagen) }}" alt="{{ $cat->nombre }}" width="300" height="300" class="w-full h-full object-contain p-4" loading="lazy">
                            @else
                                <div class="w-full h-full bg-blue-50 text-blue-600 flex items-center justify-center">
                                    <i class="fa-solid {{ $cat->icono ?? 'fa-tag' }} text-4xl"></i>
                                </div>
                            @endif
                            <span class="absolute left-3 top-3 bg-white/90 text-blue-700 text-xs font-semibold px-2 py-1 rounded-full">
                                {{ $cat->subcategorias->count() }} {{ $cat->subcategorias->count() === 1 ? 'subcategoría' : 'subcategorías' }}
                            </span>
                        </div>
                        <div class="p-5 flex flex-col flex-1">
                            <h3 class="font-bold text-gray-700 uppercase">{{ $cat->nombre }}</h3>
                            <p class="text-xs text-gray-500 mt-1">Explora productos, repuestos y accesorios disponibles.</p>
                            <span class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 mt-3 group-hover:text-blue-700">
                                Ver categoría
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    @include('tienda.partials.site-footer')

</body>
</html>

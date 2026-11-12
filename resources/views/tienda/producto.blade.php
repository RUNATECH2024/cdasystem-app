<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $imagenesProducto = collect([$producto->imagen_principal, ...($producto->imagenes_adicionales ?? [])])
            ->filter()
            ->unique()
            ->values();
        $imagenPrincipal = $imagenesProducto->first();

        $seoDescripcion = $producto->descripcion_corta
            ?: ($producto->descripcion_larga ?: 'Producto disponible en CDA System con garantía y soporte técnico.');
        $seoImagenes = $imagenesProducto->map(fn ($imagen) => asset('productos/'.$imagen))->values()->all();

        $seoBreadcrumbs = [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio', 'item' => url('/')],
        ];

        if ($producto->subcategoria?->categoria) {
            $seoBreadcrumbs[] = [
                '@type' => 'ListItem',
                'position' => count($seoBreadcrumbs) + 1,
                'name' => $producto->subcategoria->categoria->nombre,
                'item' => route('categoria.show', $producto->subcategoria->categoria->id_categoria),
            ];
        }

        if ($producto->subcategoria) {
            $seoBreadcrumbs[] = [
                '@type' => 'ListItem',
                'position' => count($seoBreadcrumbs) + 1,
                'name' => $producto->subcategoria->nombre,
                'item' => url('/categoria/'.$producto->subcategoria->id_subcategoria),
            ];
        }

        $seoBreadcrumbs[] = [
            '@type' => 'ListItem',
            'position' => count($seoBreadcrumbs) + 1,
            'name' => $producto->nombre,
            'item' => route('producto.show', $producto->id_producto),
        ];

        $seoCondicion = match ($producto->condicion) {
            'Reacondicionado' => 'https://schema.org/RefurbishedCondition',
            'Usado' => 'https://schema.org/UsedCondition',
            default => 'https://schema.org/NewCondition',
        };

        $seoProducto = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $producto->nombre,
            'description' => strip_tags((string) $seoDescripcion),
            'sku' => $producto->sku,
            'mpn' => $producto->codigo_barras,
            'category' => $producto->subcategoria?->nombre,
            'image' => $seoImagenes,
            'url' => route('producto.show', $producto->id_producto),
            'brand' => [
                '@type' => 'Brand',
                'name' => $producto->marca ?: 'CDA System',
            ],
            'offers' => [
                '@type' => 'Offer',
                'url' => route('producto.show', $producto->id_producto),
                'priceCurrency' => 'USD',
                'price' => number_format((float) $producto->precio_venta, 2, '.', ''),
                'availability' => $producto->stock_actual > 0
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'itemCondition' => $seoCondicion,
                'seller' => [
                    '@type' => 'Organization',
                    'name' => 'CDA System',
                ],
            ],
        ], fn ($valor) => $valor !== null && $valor !== '' && $valor !== []);

        $seoJsonLd = [
            $seoProducto,
            [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => $seoBreadcrumbs,
            ],
        ];
    @endphp
    @include('tienda.partials.seo', [
        'seoTitle' => $producto->nombre.' | CDA System',
        'seoDescription' => $seoDescripcion,
        'seoCanonical' => route('producto.show', $producto->id_producto),
        'seoImage' => $seoImagenes[0] ?? null,
        'seoType' => 'product',
        'seoJsonLd' => $seoJsonLd,
    ])
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">

    <header class="bg-white py-4 px-8 flex items-center justify-between shadow-sm mb-6">
        <a href="/" class="flex items-center space-x-2">
            <div class="w-10 h-10 bg-blue-600 text-white flex items-center justify-center font-bold text-xl rounded">T</div>
            <h1 class="text-xl font-bold text-gray-800 leading-tight">CDA System <span class="text-xs font-normal text-gray-500 italic">Store</span></h1>
        </a>
        <div class="flex items-center space-x-6">
            <a href="/" class="text-blue-600 hover:underline font-medium">← Volver a la tienda</a>

            @include('tienda.partials.auth-links')

            <a href="{{ route('carrito.index') }}" class="relative text-gray-700 hover:text-yellow-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span class="absolute -top-2 -right-2 bg-yellow-500 text-white text-xs rounded-full px-1.5">
                    {{ count(session()->get('carrito', [])) }}
                </span>
            </a>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4">
        <nav class="text-sm text-gray-500 mb-4">
            <a href="/" class="hover:underline">Inicio</a>
            <span class="mx-1">/</span>
            @if($producto->subcategoria?->categoria)
                <a href="{{ route('categoria.show', $producto->subcategoria->categoria->id_categoria) }}" class="hover:underline">
                    {{ $producto->subcategoria->categoria->nombre }}
                </a>
                <span class="mx-1">/</span>
            @endif
            @if($producto->subcategoria)
                <a href="{{ url('/categoria/'.$producto->subcategoria->id_subcategoria) }}" class="hover:underline">
                    {{ $producto->subcategoria->nombre }}
                </a>
                <span class="mx-1">/</span>
            @endif
            <span class="text-gray-700">{{ $producto->nombre }}</span>
        </nav>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded-lg shadow p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="h-80 bg-gray-100 flex items-center justify-center rounded">
                @if($imagenPrincipal)
                    <img id="producto-imagen-principal" src="{{ asset('productos/'.$imagenPrincipal) }}" alt="{{ $producto->nombre }}" class="max-h-80 object-contain">
                @else
                    <span class="text-gray-400 text-sm text-center px-6">{{ $producto->nombre }}</span>
                @endif
            </div>

            <div class="flex flex-col">
                <div class="flex items-center space-x-2 mb-2">
                    <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded">{{ $producto->marca ?: 'Sin marca' }}</span>
                    @if($producto->condicion)
                        <span class="bg-blue-50 text-blue-600 text-xs px-2 py-1 rounded">{{ $producto->condicion }}</span>
                    @endif
                    @if($producto->sku)
                        <span class="bg-gray-100 text-gray-500 text-xs px-2 py-1 rounded">SKU: {{ $producto->sku }}</span>
                    @endif
                </div>

                <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ $producto->nombre }}</h1>

                @if($producto->descripcion_corta)
                    <p class="text-sm text-gray-500 mb-4">{{ $producto->descripcion_corta }}</p>
                @endif

                <p class="text-3xl font-bold text-gray-900 mb-4">${{ number_format($producto->precio_venta, 2) }}</p>

                <div class="space-y-2 text-sm text-gray-600 mb-6">
                    <p>
                        <span class="font-semibold text-gray-700">Disponibilidad:</span>
                        @if($producto->stock_actual > 0)
                            <span class="text-green-600">Disponible ({{ $producto->stock_actual }} unidades)</span>
                        @else
                            <span class="text-red-600">Agotado</span>
                        @endif
                    </p>

                    @if($producto->garantia_meses > 0)
                        <p>
                            <span class="font-semibold text-gray-700">Garantía:</span>
                            {{ $producto->garantia_meses }} meses
                        </p>
                    @endif

                    @if($producto->fecha_vencimiento)
                        <p>
                            <span class="font-semibold text-gray-700">Vence:</span>
                            {{ $producto->fecha_vencimiento->format('d/m/Y') }}
                        </p>
                    @endif
                </div>

                <form action="{{ route('carrito.add', $producto->id_producto) }}" method="POST" class="mt-auto">
                    @csrf
                    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded hover:bg-blue-700 transition font-medium disabled:bg-gray-400 disabled:cursor-not-allowed" @disabled($producto->stock_actual <= 0)>
                        Añadir al carrito
                    </button>
                </form>
            </div>
        </div>

        @if($producto->es_repuesto)
            <div class="mt-6 bg-white rounded-lg shadow p-2" role="tablist" aria-label="Información del repuesto">
                <button type="button" id="producto-tab-informacion" role="tab" aria-selected="true" aria-controls="producto-panel-informacion" data-product-tab="informacion" class="px-4 py-3 border-b-2 border-blue-600 text-blue-700 font-semibold">Información</button>
                <button type="button" id="producto-tab-imagenes" role="tab" aria-selected="false" aria-controls="producto-panel-imagenes" data-product-tab="imagenes" class="px-4 py-3 border-b-2 border-transparent text-gray-600 hover:text-blue-700 font-semibold">Imágenes</button>
                <button type="button" id="producto-tab-soporte" role="tab" aria-selected="false" aria-controls="producto-panel-soporte" data-product-tab="soporte" class="px-4 py-3 border-b-2 border-transparent text-gray-600 hover:text-blue-700 font-semibold">Soporte</button>
            </div>
        @endif

        <div
            id="producto-panel-informacion"
            class="grid grid-cols-1 md:grid-cols-3 gap-6 {{ $producto->es_repuesto ? 'mt-0' : 'mt-6' }}"
            @if($producto->es_repuesto) role="tabpanel" aria-labelledby="producto-tab-informacion" data-product-panel="informacion" @endif
        >
            <div class="md:col-span-2 bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-700 border-b pb-2 mb-4">Descripción</h2>
                <p class="text-sm text-gray-600 leading-relaxed">
                    {{ $producto->descripcion_larga ?: ($producto->descripcion_corta ?: 'Sin descripción disponible.') }}
                </p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-700 border-b pb-2 mb-4">Características</h2>

                @if($producto->atributos->isEmpty() && ! $producto->codigo_barras && ! $producto->marca && ! $producto->subcategoria && ! $producto->sku)
                    <p class="text-sm text-gray-500">Sin características registradas.</p>
                @else
                    <dl class="text-sm divide-y divide-gray-100">
                        @if($producto->codigo_barras)
                            <div class="flex justify-between py-2">
                                <dt class="text-gray-500">Código</dt>
                                <dd class="font-medium text-gray-700 text-right">{{ $producto->codigo_barras }}</dd>
                            </div>
                        @endif
                        @if($producto->marca)
                            <div class="flex justify-between py-2">
                                <dt class="text-gray-500">Marca</dt>
                                <dd class="font-medium text-gray-700 text-right">{{ $producto->marca }}</dd>
                            </div>
                        @endif
                        @if($producto->subcategoria)
                            <div class="flex justify-between py-2">
                                <dt class="text-gray-500">Línea</dt>
                                <dd class="font-medium text-gray-700 text-right">{{ $producto->subcategoria->nombre }}</dd>
                            </div>
                        @endif
                        @if($producto->sku)
                            <div class="flex justify-between py-2">
                                <dt class="text-gray-500">SKU</dt>
                                <dd class="font-medium text-gray-700 text-right">{{ $producto->sku }}</dd>
                            </div>
                        @endif
                        @foreach($producto->atributos as $atributo)
                            <div class="flex justify-between py-2">
                                <dt class="text-gray-500">{{ $atributo->nombre_atributo }}</dt>
                                <dd class="font-medium text-gray-700 text-right">{{ $atributo->valor_atributo }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif
            </div>
        </div>

        @if($producto->es_repuesto)
            <section id="producto-panel-imagenes" role="tabpanel" aria-labelledby="producto-tab-imagenes" data-product-panel="imagenes" hidden class="mt-6 bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-700 border-b pb-2 mb-4">Imágenes del producto</h2>
                @if($imagenesProducto->isEmpty())
                    <p class="text-sm text-gray-500">No hay imágenes disponibles para este repuesto.</p>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                        @foreach($imagenesProducto as $imagen)
                            <button type="button" data-product-image="{{ asset('productos/'.$imagen) }}" class="border border-gray-200 rounded-lg p-2 hover:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <img src="{{ asset('productos/'.$imagen) }}" alt="{{ $producto->nombre }} - imagen {{ $loop->iteration }}" class="w-full h-36 object-contain" loading="lazy">
                            </button>
                        @endforeach
                    </div>
                @endif
            </section>

            <section id="producto-panel-soporte" role="tabpanel" aria-labelledby="producto-tab-soporte" data-product-panel="soporte" hidden class="mt-6 bg-white rounded-lg shadow overflow-hidden">
                <div class="p-5">
                    <h2 class="text-lg font-bold text-gray-700">Centro de Servicios CDA System</h2>
                    <p class="text-sm text-gray-500 mt-1">Consulta el centro de servicios y soporte técnico.</p>
                </div>
                <iframe src="{{ route('quienes-somos') }}" title="Centro de Servicios CDA System" loading="lazy" class="w-full h-[700px] border-t border-gray-200"></iframe>
            </section>
        @endif

        @if($relacionados->isNotEmpty())
            <div class="mt-8">
                <h2 class="text-lg font-bold text-gray-700 mb-4">Productos relacionados</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    @foreach($relacionados as $relacionado)
                        <a href="{{ route('producto.show', $relacionado->id_producto) }}" class="bg-white rounded-lg shadow p-4 hover:shadow-lg transition block">
                            <div class="h-32 bg-gray-100 flex items-center justify-center rounded mb-3 overflow-hidden">
                                @if($relacionado->imagen_principal)
                                    <img src="{{ asset('productos/'.$relacionado->imagen_principal) }}" alt="{{ $relacionado->nombre }}" class="w-full h-full object-contain p-2" loading="lazy">
                                @else
                                    <span class="text-gray-400 text-xs text-center px-2">{{ $relacionado->nombre }}</span>
                                @endif
                            </div>
                            <h3 class="font-bold text-gray-700 text-sm mb-1">{{ $relacionado->nombre }}</h3>
                            <p class="text-lg font-bold text-gray-900">${{ number_format($relacionado->precio_venta, 2) }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @if($producto->es_repuesto)
        <script>
            const productTabs = document.querySelectorAll('[data-product-tab]');
            const productPanels = document.querySelectorAll('[data-product-panel]');

            productTabs.forEach((tab) => {
                tab.addEventListener('click', () => {
                    productTabs.forEach((item) => {
                        const selected = item === tab;
                        item.setAttribute('aria-selected', String(selected));
                        item.classList.toggle('border-blue-600', selected);
                        item.classList.toggle('text-blue-700', selected);
                        item.classList.toggle('border-transparent', !selected);
                        item.classList.toggle('text-gray-600', !selected);
                    });

                    productPanels.forEach((panel) => {
                        panel.hidden = panel.dataset.productPanel !== tab.dataset.productTab;
                    });
                });
            });

            document.querySelectorAll('[data-product-image]').forEach((imageButton) => {
                imageButton.addEventListener('click', () => {
                    const mainImage = document.getElementById('producto-imagen-principal');
                    if (mainImage) {
                        mainImage.src = imageButton.dataset.productImage;
                    }
                });
            });
        </script>
    @endif

</body>
</html>

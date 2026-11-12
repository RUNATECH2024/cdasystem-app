<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $seoCategoriaImagen = $categoria->imagen ? asset('Categorias/'.$categoria->imagen) : null;
        $seoDescripcion = 'Explora '.$categoria->nombre.' en CDA System. Encuentra productos, repuestos y accesorios con garantía y soporte técnico.';

        $seoJsonLd = [
            array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => $categoria->nombre,
                'description' => $seoDescripcion,
                'url' => route('categoria.show', $categoria->id_categoria),
                'image' => $seoCategoriaImagen,
                'isPartOf' => [
                    '@type' => 'WebSite',
                    'name' => 'CDA System',
                    'url' => url('/'),
                ],
            ], fn ($valor) => $valor !== null && $valor !== '' && $valor !== []),
            [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio', 'item' => url('/')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => $categoria->nombre, 'item' => route('categoria.show', $categoria->id_categoria)],
                ],
            ],
        ];
    @endphp
    @include('tienda.partials.seo', [
        'seoTitle' => $categoria->nombre.' | CDA System',
        'seoDescription' => $seoDescripcion,
        'seoCanonical' => route('categoria.show', $categoria->id_categoria),
        'seoImage' => $seoCategoriaImagen,
        'seoJsonLd' => $seoJsonLd,
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
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
        <nav class="text-sm text-gray-500 mb-3">
            <a href="/" class="hover:underline">Inicio</a>
            <span class="mx-1">/</span>
            <span class="text-gray-700">{{ $categoria->nombre }}</span>
        </nav>

        <section class="mb-6 overflow-hidden rounded-xl bg-gradient-to-r from-blue-700 to-blue-500 text-white shadow-sm">
            <div class="flex flex-col sm:flex-row items-center gap-6 p-6 md:p-8">
                @if($categoria->imagen)
                    <img src="{{ asset('Categorias/'.$categoria->imagen) }}" alt="{{ $categoria->nombre }}" width="300" height="300" class="w-32 h-32 md:w-40 md:h-40 object-contain rounded-2xl bg-white p-3 shadow">
                @else
                    <span class="w-32 h-32 md:w-40 md:h-40 flex items-center justify-center rounded-2xl bg-white/10">
                        <i class="fa-solid {{ $categoria->icono ?? 'fa-tag' }} text-5xl"></i>
                    </span>
                @endif
                <div class="flex-1 text-center sm:text-left">
                    <p class="uppercase tracking-widest text-xs text-blue-100 font-semibold">Categoría</p>
                    <h2 class="text-2xl md:text-3xl font-bold flex items-center justify-center sm:justify-start gap-3 mt-1">
                        <i class="fa-solid {{ $categoria->icono ?? 'fa-tag' }}"></i>
                        <span>{{ $categoria->nombre }}</span>
                    </h2>
                    <p class="text-blue-100 mt-2">Explora las subcategorías y productos disponibles.</p>
                </div>
            </div>
        </section>

        @if($categoria->subcategorias->isEmpty())
            <div class="bg-white rounded shadow p-6 text-gray-500">
                Esta categoría todavía no tiene subcategorías.
            </div>
        @else
            <div class="grid grid-cols-3 gap-6">
                @foreach($categoria->subcategorias as $subcategoria)
                    <a href="{{ url('/categoria/'.$subcategoria->id_subcategoria) }}"
                       class="bg-white rounded-lg shadow p-6 text-center hover:shadow-lg transition block">
                        <div class="mx-auto w-14 h-14 bg-blue-50 text-blue-600 flex items-center justify-center rounded-full mb-4">
                            <i class="fa-solid fa-folder-open text-xl"></i>
                        </div>
                        <h3 class="font-bold text-gray-700 uppercase">{{ $subcategoria->nombre }}</h3>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('tienda.partials.seo', [
        'seoTitle' => 'Productos '.$nombre.' | CDA System',
        'seoDescription' => 'Productos y equipos de la marca '.$nombre.' disponibles en CDA System.',
        'seoCanonical' => route('marcas.productos', $nombre),
        'seoJsonLd' => [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => 'Productos '.$nombre,
            'url' => route('marcas.productos', $nombre),
        ],
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
            <a href="{{ route('marcas') }}" class="text-blue-600 hover:underline font-medium">← Volver a Marcas</a>
            
            <a href="{{ route('carrito.index') }}" class="relative text-gray-700 hover:text-yellow-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span class="absolute -top-2 -right-2 bg-yellow-500 text-white text-xs rounded-full px-1.5">
                    {{ count(session()->get('carrito', [])) }}
                </span>
            </a>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Productos de la marca: <span class="text-blue-600">{{ $nombre }}</span></h2>

        @if(count($productos) > 0)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($productos as $prod)
                    <div class="bg-white rounded-lg shadow p-4 flex flex-col justify-between">
                        <a href="{{ route('producto.show', $prod->id_producto) }}" class="block hover:opacity-90">
                            <div class="h-40 bg-gray-100 flex items-center justify-center rounded mb-4">
                                <span class="text-gray-400 text-xs text-center">{{ $prod->nombre }}</span>
                            </div>
                            <h3 class="font-bold text-gray-700 text-sm mb-1">{{ $prod->nombre }}</h3>
                            <p class="text-xs text-gray-500 mb-2">{{ $prod->descripcion_corta }}</p>
                        </a>
                        <div>
                            <p class="text-lg font-bold text-gray-900 mb-3">${{ number_format($prod->precio_venta, 2) }}</p>
                            <form action="{{ route('carrito.add', $prod->id_producto) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition font-medium">
                                    Añadir al carrito
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white p-8 rounded shadow text-center">
                <p class="text-gray-500">No hay productos disponibles de la marca {{ $nombre }} por el momento.</p>
            </div>
        @endif
    </div>

</body>
</html>
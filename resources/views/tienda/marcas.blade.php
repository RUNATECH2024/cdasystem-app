<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('tienda.partials.seo', [
        'seoTitle' => 'Directorio de marcas | CDA System',
        'seoDescription' => 'Explora las marcas de tecnología disponibles en CDA System: HP, Lenovo, Dell, Epson, ASUS, AMD, Intel y más.',
        'seoCanonical' => route('marcas'),
        'seoJsonLd' => [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => 'Directorio de marcas',
            'url' => route('marcas'),
            'description' => 'Marcas de tecnología disponibles en CDA System.',
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
        <h2 class="text-2xl font-bold text-gray-800 mb-2 text-center">Directorio de Marcas</h2>
        <p class="text-gray-500 mb-8 text-center">Haz clic en una marca para ver sus productos disponibles.</p>

        <div class="grid grid-cols-6 gap-6">
            @foreach($marcas as $fila)
                <a href="{{ route('marcas.productos', $fila['marca']) }}" 
                   class="bg-white rounded-lg shadow p-4 flex flex-col items-center justify-center hover:shadow-lg transition cursor-pointer block text-decoration-none">
                    
                    <img src="{{ asset('marcas/' . $fila['imagen']) }}" 
                         alt="{{ $fila['marca'] }}" 
                         class="h-16 object-contain mb-2"
                         onerror="this.src='https://via.placeholder.com/100x50?text=Sin+Logo'">
                    
                    <h3 class="font-bold text-gray-800 text-sm mt-2">{{ $fila['marca'] }}</h3>
                    @if($fila['cantidad'] > 0)
                        <p class="text-xs text-gray-500 mt-1">{{ $fila['cantidad'] }} modelo(s)</p>
                        <p class="text-xs text-blue-600 font-bold mt-1">Stock: {{ $fila['stock_total'] }}</p>
                    @else
                        <p class="text-xs text-gray-400 mt-1 italic">Próximamente</p>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

</body>
</html>
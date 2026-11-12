<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $seoDescripcion = 'Catálogo de repuestos y accesorios para impresoras, computadoras y equipos de oficina. Encuentra disponibilidad y precios en CDA System.';
        $seoJsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => 'Repuestos',
            'description' => $seoDescripcion,
            'url' => route('quienes-somos.repuestos'),
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => 'CDA System',
                'url' => url('/'),
            ],
        ];
    @endphp
    @include('tienda.partials.seo', [
        'seoTitle' => 'Repuestos y accesorios | CDA System',
        'seoDescription' => $seoDescripcion,
        'seoCanonical' => route('quienes-somos.repuestos'),
        'seoJsonLd' => $seoJsonLd,
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">

    @include('tienda.partials.site-header')

    <section class="bg-gradient-to-r from-blue-800 to-blue-600 text-white">
        <div class="max-w-7xl mx-auto px-4 py-10">
            <nav class="text-sm text-blue-100 flex items-center gap-2">
                <a href="{{ url('/') }}" class="hover:text-yellow-400">Inicio</a>
                <span>/</span>
                <a href="{{ route('quienes-somos') }}" class="hover:text-yellow-400">Quiénes somos</a>
                <span>/</span>
                <span>Repuestos</span>
            </nav>
            <h1 class="text-3xl md:text-4xl font-bold mt-3">Repuestos</h1>
            <p class="mt-3 max-w-3xl text-blue-100">Repuestos y accesorios disponibles en CDA System.</p>
        </div>
    </section>

    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 flex gap-6 overflow-x-auto">
            <a href="{{ route('quienes-somos.repuestos') }}" class="py-4 border-b-2 border-blue-600 text-blue-700 font-semibold whitespace-nowrap">
                Repuestos
            </a>
            <a href="{{ route('centro-servicio') }}#servicio-tecnico" class="py-4 border-b-2 border-transparent text-gray-600 hover:text-blue-700 font-semibold whitespace-nowrap">
                Servicios
            </a>
            <a href="{{ route('centro-servicio') }}#ubicacion" class="py-4 border-b-2 border-transparent text-gray-600 hover:text-blue-700 font-semibold whitespace-nowrap">
                CAS
            </a>
        </div>
    </div>

    <main class="max-w-7xl mx-auto px-4 py-8">
        <form action="{{ route('quienes-somos.repuestos') }}" method="GET" class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex flex-col lg:flex-row gap-3">
                <div class="flex flex-1">
                    <input type="text" name="q" value="{{ $q }}" placeholder="buscar producto...." class="w-full border border-gray-300 rounded-l px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" class="bg-blue-600 text-white px-5 py-2.5 rounded-r hover:bg-blue-700 transition" aria-label="Buscar repuesto">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
                <select name="orden" onchange="this.form.submit()" class="border border-gray-300 rounded px-3 py-2.5 bg-white text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Ordenar repuestos">
                    <option value="menor-precio" @selected($orden === 'menor-precio')>Menor precio</option>
                    <option value="mayor-precio" @selected($orden === 'mayor-precio')>Mayor precio</option>
                    <option value="nombre" @selected($orden === 'nombre')>Nombre A-Z</option>
                </select>
                <select name="categoria" onchange="this.form.submit()" class="border border-gray-300 rounded px-3 py-2.5 bg-white text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Filtrar por categoría">
                    <option value="">TODAS</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id_categoria }}" @selected($categoriaId === $categoria->id_categoria)>{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="flex flex-wrap items-center justify-between gap-3 mt-6 mb-4">
            <p class="text-sm text-gray-500">{{ $productos->total() }} repuesto(s) disponible(s)</p>
            @if($q !== '' || $categoriaId > 0 || $orden !== 'menor-precio')
                <a href="{{ route('quienes-somos.repuestos') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">Limpiar filtros</a>
            @endif
        </div>

        @if($productos->isEmpty())
            <div class="bg-white rounded-xl shadow-sm p-10 text-center">
                <i class="fa-solid fa-box-open text-4xl text-gray-300"></i>
                <h2 class="text-xl font-bold text-gray-700 mt-4">No hay repuestos disponibles</h2>
                <p class="text-gray-500 mt-2">Prueba con otra búsqueda o vuelve pronto para consultar el catálogo.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-5">
                @foreach($productos as $producto)
                    <article class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden flex flex-col">
                        <a href="{{ route('producto.show', $producto->id_producto) }}" class="h-48 bg-gray-50 flex items-center justify-center p-4">
                            @if($producto->imagen_principal)
                                <img src="{{ asset('productos/'.$producto->imagen_principal) }}" alt="{{ $producto->nombre }}" class="w-full h-full object-contain" loading="lazy">
                            @else
                                <span class="text-gray-400 text-sm text-center px-4">{{ $producto->nombre }}</span>
                            @endif
                        </a>
                        <div class="p-4 flex flex-col flex-1">
                            <span class="text-xs uppercase tracking-wide text-gray-500">{{ $producto->marca ?: 'Sin marca' }}</span>
                            <a href="{{ route('producto.show', $producto->id_producto) }}" class="font-semibold text-gray-800 text-sm hover:text-blue-600 leading-snug mt-1">
                                {{ $producto->nombre }}
                            </a>
                            <div class="mt-auto pt-4 flex items-center justify-between gap-3">
                                <span class="text-lg font-bold text-gray-900">${{ number_format($producto->precio_venta, 2) }}</span>
                                @if($producto->stock_actual > 0)
                                    <form action="{{ route('carrito.add', $producto->id_producto) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-blue-600 text-white text-xs font-bold uppercase px-3 py-2 rounded hover:bg-blue-700 transition">
                                            Comprar
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs font-bold uppercase text-red-600">Agotado</span>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $productos->links() }}
            </div>
        @endif
    </main>

    @include('tienda.partials.site-footer')

</body>
</html>

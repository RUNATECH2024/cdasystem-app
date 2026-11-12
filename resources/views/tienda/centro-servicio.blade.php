<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('tienda.partials.seo', [
        'seoTitle' => 'Centro de Servicio y garantías | CDA System',
        'seoDescription' => 'Consulta políticas de garantía, estado de garantías, solicitud de soporte y servicio técnico de CDA System.',
        'seoCanonical' => route('centro-servicio'),
        'seoJsonLd' => [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => 'Centro de Servicio CDA System',
            'url' => route('centro-servicio'),
            'description' => 'Políticas de garantía, estado de garantías, solicitud de soporte y servicio técnico de CDA System.',
        ],
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">

    @include('tienda.partials.site-header')

    <section class="bg-gradient-to-r from-blue-800 to-blue-600 text-white">
        <div class="max-w-7xl mx-auto px-4 py-12">
            <nav class="text-sm text-blue-100 flex items-center gap-2">
                <a href="{{ url('/') }}" class="hover:text-yellow-400">Inicio</a>
                <span>/</span>
                <a href="{{ route('quienes-somos') }}" class="hover:text-yellow-400">Quiénes somos</a>
                <span>/</span>
                <span>Centro de Servicio</span>
            </nav>
            <div class="mt-4 flex flex-col lg:flex-row lg:items-end gap-8">
                <div class="flex-1">
                    <p class="uppercase tracking-widest text-xs text-blue-100 font-semibold">Servicio técnico y repuestos</p>
                    <h1 class="text-3xl md:text-4xl font-bold mt-2">Centro de Servicio CDA System</h1>
                    <p class="mt-3 max-w-3xl text-blue-100 leading-relaxed">
                        Mantenimiento, reparación, garantías y repuestos para tus equipos de cómputo e impresión,
                        con el respaldo técnico de CDA System.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ url('/') }}#categorias" class="bg-yellow-500 text-white font-semibold px-5 py-2.5 rounded hover:bg-yellow-600 transition">
                            Ir a producto
                        </a>
                        <a href="{{ route('quienes-somos.repuestos') }}" class="bg-white/10 border border-white/40 font-semibold px-5 py-2.5 rounded hover:bg-white/20 transition">
                            Ver repuestos
                        </a>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 w-full lg:w-auto text-center text-xs">
                    <div class="bg-white/10 rounded-xl px-5 py-4">
                        <i class="fa-solid fa-shield-halved text-2xl mb-2"></i>
                        <p class="font-semibold">Garantías</p>
                        <p class="text-blue-100">Respaldo directo</p>
                    </div>
                    <div class="bg-white/10 rounded-xl px-5 py-4">
                        <i class="fa-solid fa-screwdriver-wrench text-2xl mb-2"></i>
                        <p class="font-semibold">Servicio técnico</p>
                        <p class="text-blue-100">Soporte especializado</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <main class="max-w-7xl mx-auto px-4">
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-8">
            <a href="#politicas-garantia" class="bg-white rounded-xl shadow-sm hover:shadow-md transition p-5 flex items-center gap-4">
                <span class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-file-shield"></i>
                </span>
                <span class="font-semibold text-gray-800">Políticas de garantías</span>
            </a>
            <a href="#estado-garantia" class="bg-white rounded-xl shadow-sm hover:shadow-md transition p-5 flex items-center gap-4">
                <span class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <span class="font-semibold text-gray-800">Estado de garantías</span>
            </a>
            <a href="mailto:garantias@tecnomega.com?subject=Solicitud%20de%20garant%C3%ADa" class="bg-white rounded-xl shadow-sm hover:shadow-md transition p-5 flex items-center gap-4">
                <span class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-envelope-open-text"></i>
                </span>
                <span class="font-semibold text-gray-800">Solicitud de garantías</span>
            </a>
            <a href="#servicio-tecnico" class="bg-white rounded-xl shadow-sm hover:shadow-md transition p-5 flex items-center gap-4">
                <span class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </span>
                <span class="font-semibold text-gray-800">Servicio Técnico</span>
            </a>
        </section>

        <section class="mt-10">
            <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Principales categorías de repuestos</h2>
                    <p class="text-sm text-gray-500">Encuentra repuestos y accesorios para las marcas que atendemos.</p>
                </div>
                <a href="{{ route('quienes-somos.repuestos') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">Ver catálogo de repuestos</a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-4">
                @foreach($categoriasPrincipales as $categoria)
                    <a href="{{ $categoria['url'] }}" class="group bg-white rounded-xl shadow-sm hover:shadow-md transition p-5 text-center flex flex-col items-center gap-3">
                        <span class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition">
                            <i class="fa-solid {{ $categoria['icono'] }}"></i>
                        </span>
                        <span class="text-sm font-semibold text-gray-700 group-hover:text-blue-700">{{ $categoria['nombre'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="mt-12">
            <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Productos destacados</h2>
                    <p class="text-sm text-gray-500">Equipos y repuestos recomendados por nuestro equipo técnico.</p>
                </div>
                <a href="{{ route('buscar') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">Ver más</a>
            </div>

            @if($productosDestacados->isEmpty())
                <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                    <i class="fa-solid fa-box-open text-4xl text-gray-300"></i>
                    <p class="text-gray-500 mt-3">Pronto tendremos productos destacados disponibles.</p>
                    <a href="{{ url('/') }}#categorias" class="inline-block mt-4 text-sm font-semibold text-blue-600 hover:text-blue-700">Explorar el catálogo</a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    @foreach($productosDestacados as $producto)
                        <article class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden flex flex-col">
                            <a href="{{ route('producto.show', $producto->id_producto) }}" class="h-44 bg-gray-50 flex items-center justify-center p-4">
                                @if($producto->imagen_principal)
                                    <img src="{{ asset('productos/'.$producto->imagen_principal) }}" alt="{{ $producto->nombre }}" class="w-full h-full object-contain" loading="lazy">
                                @else
                                    <span class="text-gray-400 text-sm text-center px-4">{{ $producto->nombre }}</span>
                                @endif
                            </a>
                            <div class="p-4 flex flex-col flex-1">
                                <span class="text-xs text-gray-500">{{ $producto->marca ?: 'Sin marca' }}</span>
                                <a href="{{ route('producto.show', $producto->id_producto) }}" class="font-semibold text-gray-800 hover:text-blue-600 leading-snug mt-1">
                                    {{ $producto->nombre }}
                                </a>
                                <div class="mt-auto pt-4 flex items-center justify-between gap-3">
                                    <span class="text-lg font-bold text-gray-900">${{ number_format($producto->precio_venta, 2) }}</span>
                                    <a href="{{ route('producto.show', $producto->id_producto) }}" class="bg-blue-600 text-white text-sm font-medium px-3 py-2 rounded hover:bg-blue-700 transition">
                                        Ver detalle
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="mt-12">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <article id="politicas-garantia" class="bg-white rounded-xl shadow-sm p-6 scroll-mt-24">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-4">
                        <i class="fa-solid fa-file-shield"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800">Políticas de garantías</h2>
                    <p class="text-gray-600 mt-2 leading-relaxed">
                        Todos los equipos y repuestos comercializados por CDA System cuentan con el respaldo del
                        fabricante. Conserva tu factura y el empaque original para gestionar cualquier reclamo.
                    </p>
                </article>

                <article id="estado-garantia" class="bg-white rounded-xl shadow-sm p-6 scroll-mt-24">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-4">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800">Estado de garantías</h2>
                    <p class="text-gray-600 mt-2 leading-relaxed">
                        Para consultar el estado de tu garantía, ten a mano el número de factura, modelo y serie
                        del equipo. Nuestro equipo te confirmará el diagnóstico y los siguientes pasos.
                    </p>
                </article>

                <article class="bg-white rounded-xl shadow-sm p-6">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-4">
                        <i class="fa-solid fa-envelope-open-text"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800">Solicitud de garantías</h2>
                    <p class="text-gray-600 mt-2 leading-relaxed">
                        Envía tu solicitud con tus datos y documentos a
                        <a href="mailto:garantias@tecnomega.com?subject=Solicitud%20de%20garant%C3%ADa" class="font-semibold text-blue-600 hover:text-blue-700">garantias@tecnomega.com</a>
                        o comunícate al <a href="tel:+59322908202" class="font-semibold text-blue-600 hover:text-blue-700">(02) 2908202</a>.
                    </p>
                </article>

                <article id="servicio-tecnico" class="bg-white rounded-xl shadow-sm p-6 scroll-mt-24">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-4">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800">Servicio Técnico</h2>
                    <p class="text-gray-600 mt-2 leading-relaxed">
                        Realizamos mantenimiento preventivo y correctivo, diagnóstico, instalación de repuestos y
                        soporte especializado para equipos de cómputo e impresión.
                    </p>
                </article>
            </div>
        </section>

        <section id="compromiso-ambiental" class="mt-12 scroll-mt-24">
            <div class="bg-green-700 text-white rounded-2xl p-8 flex flex-col md:flex-row md:items-center gap-6">
                <div class="w-14 h-14 bg-white/15 rounded-full flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-leaf text-xl"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-2xl font-bold">CDA System contribuye con el cuidado del medio ambiente</h2>
                    <p class="text-green-100 mt-2">
                        Promovemos el reciclaje de cartuchos, tóneres y componentes electrónicos, y damos un manejo
                        responsable a los equipos que llegan a nuestro centro de servicio.
                    </p>
                </div>
                <a href="#ubicacion" class="inline-flex items-center justify-center gap-2 bg-white text-green-700 font-semibold px-5 py-2.5 rounded hover:bg-green-50 transition">
                    Conocer más
                </a>
            </div>
        </section>

        <section class="mt-12">
            <div class="bg-white rounded-2xl shadow-sm p-8">
                <h2 class="text-2xl font-bold text-gray-800">Somos Centro de Servicio Autorizado para:</h2>
                <p class="text-sm text-gray-500 mt-1">Consulta la cobertura de tu marca con nuestro equipo técnico.</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
                    @foreach(['Dell', 'Epson', 'Dynabook', 'CDP'] as $marca)
                        <div class="border border-gray-200 rounded-xl py-6 text-center text-lg font-bold text-gray-600">
                            {{ $marca }}
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="ubicacion" class="mt-12 mb-12 scroll-mt-24">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">¿Dónde estamos ubicados?</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <article class="bg-white rounded-xl shadow-sm p-6">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-4">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <h3 class="font-bold text-gray-800">Dirección</h3>
                    <p class="text-gray-600 mt-2">Gral. Murgeon OE3-254 &amp; Av. América</p>
                </article>

                <article class="bg-white rounded-xl shadow-sm p-6">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-4">
                        <i class="fa-regular fa-clock"></i>
                    </div>
                    <h3 class="font-bold text-gray-800">Horario de atención</h3>
                    <p class="text-gray-600 mt-2">Lunes a Viernes: 8:30 - 18:00</p>
                </article>

                <article class="bg-white rounded-xl shadow-sm p-6">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-4">
                        <i class="fa-solid fa-phone"></i>
                    </div>
                    <h3 class="font-bold text-gray-800">Comunicación directa</h3>
                    <p class="text-gray-600 mt-2">
                        <a href="tel:+59322908202" class="hover:text-blue-600">(02) 2908202</a><br>
                        <a href="mailto:garantias@tecnomega.com" class="hover:text-blue-600">garantias@tecnomega.com</a>
                    </p>
                </article>
            </div>
        </section>
    </main>

    @include('tienda.partials.site-footer')

</body>
</html>

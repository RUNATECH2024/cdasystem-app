<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('tienda.partials.seo', [
        'seoTitle' => 'Quiénes Somos | CDA System',
        'seoDescription' => 'Conoce CDA System C.A., mayorista de productos informáticos en Ecuador con seis oficinas y cobertura nacional.',
        'seoCanonical' => route('quienes-somos'),
        'seoJsonLd' => [
            '@context' => 'https://schema.org',
            '@type' => 'AboutPage',
            'name' => 'Quiénes Somos - CDA System',
            'url' => route('quienes-somos'),
            'description' => 'CDA System C.A. es un mayorista de productos informáticos en Ecuador con presencia nacional.',
        ],
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">

    @include('tienda.partials.site-header')

    <!-- Hero -->
    <section class="bg-gradient-to-r from-blue-800 to-blue-600 text-white">
        <div class="max-w-7xl mx-auto px-4 py-14">
            <nav class="text-sm text-blue-100 flex items-center gap-2">
                <a href="{{ url('/') }}" class="hover:text-yellow-400">Inicio</a>
                <span>/</span>
                <span>Sobre nosotros</span>
            </nav>
            <h1 class="text-4xl font-bold mt-3">Quiénes Somos</h1>
            <p class="mt-4 max-w-3xl text-blue-100 leading-relaxed">
                Somos una empresa ecuatoriana dedicada a la comercialización de productos informáticos,
                con un equipo humano capacitado y una cobertura nacional que nos permite acompañar a
                nuestros clientes con tecnología, garantía y soporte.
            </p>
        </div>
    </section>

    <!-- Historia -->
    <section class="max-w-7xl mx-auto px-4 mt-10 grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">CDA System C.A.</h2>
            <div class="space-y-4 text-gray-600 leading-relaxed">
                <p>
                    Hoy, CDA System C.A. es conocido en el sector informático del Ecuador como uno de los
                    mayoristas autorizados en la comercialización de productos informáticos. Fue fundado en
                    el año 1999, con un equipo humano capacitado, conformado actualmente por 171 personas a
                    nivel nacional. Contamos con seis oficinas en el Ecuador, manteniendo presencia en todas
                    las provincias del país.
                </p>
                <p>
                    Los cambios e innovaciones que se dan en el mundo de la tecnología son aprovechados por
                    CDA System, mediante un trabajo continuo que contribuye al crecimiento y desarrollo de
                    sus clientes, en este cada vez más competitivo, dinámico y globalizado mundo.
                </p>
                <p>
                    El mantenerse como uno de los mayoristas líderes de tecnología dentro del mercado
                    ecuatoriano compromete a CDA System a satisfacer de manera oportuna las necesidades de
                    sus clientes, otorgándoles productos de calidad, de marcas reconocidas a nivel mundial y
                    con la adecuada garantía y soporte; condiciones y beneficios necesarios para que puedan
                    cumplir con su papel de distribuidores.
                </p>
            </div>
        </div>

        <aside class="bg-white rounded-xl shadow-sm p-6 h-fit">
            <h2 class="text-lg font-bold text-gray-800 mb-4">CDA System en cifras</h2>
            <dl class="space-y-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-gray-500">Fundación</dt>
                        <dd class="text-xl font-bold text-gray-800">1999</dd>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-gray-500">Colaboradores</dt>
                        <dd class="text-xl font-bold text-gray-800">171</dd>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-building"></i>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-gray-500">Oficinas</dt>
                        <dd class="text-xl font-bold text-gray-800">6 en Ecuador</dd>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-gray-500">Cobertura</dt>
                        <dd class="text-xl font-bold text-gray-800">Nacional</dd>
                    </div>
                </div>
            </dl>
        </aside>
    </section>

    <!-- Visítanos en -->
    <section id="visitanos" class="max-w-7xl mx-auto px-4 mt-12 scroll-mt-24">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Visítanos en</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <article class="bg-white rounded-xl shadow-sm p-6 flex flex-col">
                <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-4">
                    <i class="fa-solid fa-screwdriver-wrench text-xl"></i>
                </div>
                <h3 class="font-bold text-gray-800">Centro de Servicio CDA System</h3>
                <p class="text-sm text-gray-500 mt-2 flex-1">Mantenimiento, reparación y soporte técnico especializado para tus equipos.</p>
                <a href="{{ route('centro-servicio') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700">
                    Ver más <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </article>

            <article class="bg-white rounded-xl shadow-sm p-6 flex flex-col">
                <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-4">
                    <i class="fa-solid fa-store text-xl"></i>
                </div>
                <h3 class="font-bold text-gray-800">CDA System Store</h3>
                <p class="text-sm text-gray-500 mt-2 flex-1">Equipos, componentes y accesorios disponibles en nuestra tienda online.</p>
                <a href="{{ url('/') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700">
                    Ver más <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </article>

            <article class="bg-white rounded-xl shadow-sm p-6 flex flex-col">
                <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-4">
                    <i class="fa-solid fa-laptop text-xl"></i>
                </div>
                <h3 class="font-bold text-gray-800">QUASAD</h3>
                <p class="text-sm text-gray-500 mt-2 flex-1">Soluciones informáticas y productos de marcas reconocidas a nivel mundial.</p>
                <a href="https://quasad.tech/" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700">
                    Ver más <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </article>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 mt-10">
        <div class="bg-white rounded-2xl shadow-sm p-8 flex flex-col md:flex-row md:items-center gap-6">
            <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center shrink-0">
                <i class="fa-solid fa-gears text-xl"></i>
            </div>
            <div class="flex-1">
                <h2 class="text-2xl font-bold text-gray-800">Repuestos</h2>
                <p class="text-gray-600 mt-1">Consulta nuestro catálogo de repuestos disponibles para tus equipos.</p>
            </div>
            <a href="{{ route('quienes-somos.repuestos') }}" class="inline-flex items-center justify-center gap-2 bg-blue-600 text-white font-semibold px-5 py-3 rounded hover:bg-blue-700 transition">
                Ver repuestos <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </section>

    <!-- Misión, Visión y Valores -->
    <section class="bg-white border-y border-gray-200 py-12 mt-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <article class="bg-gray-50 rounded-xl p-6 border border-gray-100">
                    <div class="w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center mb-4">
                        <i class="fa-solid fa-bullseye"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 mb-2">Misión</h2>
                    <p class="text-gray-600 leading-relaxed">
                        Proporcionar de manera oportuna equipos, partes y sistemas informáticos de última
                        tecnología y de calidad reconocida; los mismos que contribuyan al desarrollo y
                        mejoramiento de las actividades productivas, comerciales y de servicios, manteniendo
                        un liderazgo activo y comprometido con las necesidades de los clientes, compradores
                        y socios.
                    </p>
                </article>

                <article class="bg-gray-50 rounded-xl p-6 border border-gray-100">
                    <div class="w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center mb-4">
                        <i class="fa-solid fa-eye"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 mb-2">Visión</h2>
                    <p class="text-gray-600 leading-relaxed">
                        Ser una organización principalmente creada para otorgar calidad, no solo en los
                        productos que se distribuyen sino en todas las actividades que realiza, teniendo
                        como principal meta el servicio a los clientes.
                    </p>
                </article>
            </div>

            <article class="bg-gray-50 rounded-xl p-6 border border-gray-100 mt-6">
                <div class="w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center mb-4">
                    <i class="fa-solid fa-handshake"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800 mb-2">Valores</h2>
                <p class="text-gray-600 leading-relaxed max-w-4xl">
                    Nuestra empresa se caracteriza por ejercer y fomentar valores de orden moral,
                    responsabilidad en el cumplimiento de nuestro trabajo y responsabilidad social; lealtad
                    con nuestros principios, honor con nuestras promesas, honestidad en nuestro proceder;
                    verdad en nuestros ofrecimientos; creatividad, valor y perseverancia con nuestras metas.
                </p>
            </article>
        </div>
    </section>

    <!-- Contacto -->
    <section class="max-w-7xl mx-auto px-4 mt-10">
        <div class="bg-gray-800 text-white rounded-2xl p-8 flex flex-col md:flex-row md:items-center gap-6">
            <div class="w-14 h-14 bg-yellow-500 text-white rounded-full flex items-center justify-center shrink-0">
                <i class="fa-solid fa-headset text-xl"></i>
            </div>
            <div class="flex-1">
                <h2 class="text-2xl font-bold">¿Necesitas asesoría?</h2>
                <p class="text-gray-300 mt-1">Nuestro equipo está disponible para ayudarte con productos, soporte y servicios técnicos.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="tel:+593980821599" class="bg-yellow-500 text-white font-semibold px-5 py-2.5 rounded hover:bg-yellow-600 transition">
                    <i class="fa-solid fa-phone mr-2"></i> 098 082 1599
                </a>
                <a href="https://wa.me/593980821599" target="_blank" rel="noopener noreferrer" class="bg-green-600 text-white font-semibold px-5 py-2.5 rounded hover:bg-green-700 transition">
                    <i class="fa-brands fa-whatsapp mr-2"></i> Escribir por WhatsApp
                </a>
                <a href="{{ url('/') }}" class="bg-white/10 border border-white/40 font-semibold px-5 py-2.5 rounded hover:bg-white/20 transition">
                    Ir a la tienda
                </a>
            </div>
        </div>
    </section>

    @include('tienda.partials.site-footer')

</body>
</html>

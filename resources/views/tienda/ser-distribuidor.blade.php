<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('tienda.partials.seo', [
        'seoTitle' => 'Ser distribuidor | CDA System',
        'seoDescription' => 'Forma parte de la red de distribuidores de CDA System y accede a precios mayoristas de productos informáticos.',
        'seoCanonical' => route('distribuidor.create'),
        'seoJsonLd' => [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => 'Ser distribuidor de CDA System',
            'url' => route('distribuidor.create'),
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
                <span>Ser Distribuidor</span>
            </nav>
            <h1 class="text-3xl md:text-4xl font-bold mt-3">Ser Distribuidor</h1>
            <p class="mt-3 max-w-3xl text-blue-100 leading-relaxed">
                Completa el formulario y forma parte de nuestra red de distribuidores autorizados.
                Revisaremos tu información y nos pondremos en contacto contigo.
            </p>
        </div>
    </section>

    <main class="max-w-4xl mx-auto px-4 mt-8 mb-12">
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6 flex items-start gap-3">
                <i class="fa-solid fa-circle-check mt-0.5"></i>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6">
                <p class="font-semibold mb-2">Revisa la información ingresada:</p>
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('distribuidor.store') }}" method="POST" class="bg-white rounded-xl shadow-sm p-6 md:p-8">
            @csrf

            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-building text-lg"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Formulario de solicitud de distribuidor</h2>
                    <p class="text-sm text-gray-500">Los campos marcados con <span class="text-red-500">*</span> son obligatorios.</p>
                </div>
            </div>

            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="ruc" class="block text-sm font-medium text-gray-700">RUC <span class="text-red-500">*</span></label>
                        <input type="text" id="ruc" name="ruc" value="{{ old('ruc') }}" required maxlength="20" autocomplete="off" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('ruc') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="representante_legal" class="block text-sm font-medium text-gray-700">Representante legal <span class="text-red-500">*</span></label>
                        <input type="text" id="representante_legal" name="representante_legal" value="{{ old('representante_legal') }}" required maxlength="150" autocomplete="name" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('representante_legal') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <span class="block text-sm font-medium text-gray-700 mb-2">Su actividad económica está relacionada con la compra y/o venta de tecnología? <span class="text-red-500">*</span></span>
                    <div class="flex flex-wrap gap-6">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="radio" name="actividad_tecnologia" value="1" @checked(old('actividad_tecnologia', '1') === '1') class="text-blue-600 focus:ring-blue-500">
                            <span>SÍ</span>
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="radio" name="actividad_tecnologia" value="0" @checked(old('actividad_tecnologia') === '0') class="text-blue-600 focus:ring-blue-500">
                            <span>NO</span>
                        </label>
                    </div>
                    @error('actividad_tecnologia') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="tiempo_negocio" class="block text-sm font-medium text-gray-700">Tiempo que se encuentra en el negocio <span class="text-red-500">*</span></label>
                        <input type="text" id="tiempo_negocio" name="tiempo_negocio" value="{{ old('tiempo_negocio') }}" required maxlength="100" placeholder="Ej. 5 años" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('tiempo_negocio') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="provincia" class="block text-sm font-medium text-gray-700">Provincia <span class="text-red-500">*</span></label>
                        <select id="provincia" name="provincia" required class="mt-1 w-full border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- seleccione una opción --</option>
                            @foreach($provincias as $provincia)
                                <option value="{{ $provincia }}" @selected(old('provincia') === $provincia)>{{ $provincia }}</option>
                            @endforeach
                        </select>
                        @error('provincia') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="canton" class="block text-sm font-medium text-gray-700">Cantón <span class="text-red-500">*</span></label>
                        <input type="text" id="canton" name="canton" value="{{ old('canton') }}" required maxlength="100" placeholder="Ej. Quito" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('canton') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="parroquia" class="block text-sm font-medium text-gray-700">Parroquia <span class="text-red-500">*</span></label>
                        <input type="text" id="parroquia" name="parroquia" value="{{ old('parroquia') }}" required maxlength="100" placeholder="Ej. Centro histórico" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('parroquia') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="md:col-span-2">
                        <label for="calle_principal" class="block text-sm font-medium text-gray-700">Calle principal <span class="text-red-500">*</span></label>
                        <input type="text" id="calle_principal" name="calle_principal" value="{{ old('calle_principal') }}" required maxlength="150" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('calle_principal') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="numero_predio" class="block text-sm font-medium text-gray-700">Número de predio / casa <span class="text-red-500">*</span></label>
                        <input type="text" id="numero_predio" name="numero_predio" value="{{ old('numero_predio') }}" required maxlength="30" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('numero_predio') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="calle_secundaria" class="block text-sm font-medium text-gray-700">Calle secundaria</label>
                        <input type="text" id="calle_secundaria" name="calle_secundaria" value="{{ old('calle_secundaria') }}" maxlength="150" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('calle_secundaria') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="referencia_adicional" class="block text-sm font-medium text-gray-700">Referencia adicional</label>
                        <input type="text" id="referencia_adicional" name="referencia_adicional" value="{{ old('referencia_adicional') }}" maxlength="255" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('referencia_adicional') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="telefono_local" class="block text-sm font-medium text-gray-700">Teléfono local <span class="text-red-500">*</span></label>
                            <input type="text" id="telefono_local" name="telefono_local" value="{{ old('telefono_local') }}" required maxlength="20" autocomplete="tel" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('telefono_local') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="celular" class="block text-sm font-medium text-gray-700">Celular <span class="text-red-500">*</span></label>
                            <input type="text" id="celular" name="celular" value="{{ old('celular') }}" required maxlength="20" autocomplete="tel" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('celular') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
                        <div>
                            <label for="email_contacto" class="block text-sm font-medium text-gray-700">Email de contacto <span class="text-red-500">*</span></label>
                            <input type="email" id="email_contacto" name="email_contacto" value="{{ old('email_contacto') }}" required maxlength="150" autocomplete="email" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('email_contacto') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="oficina" class="block text-sm font-medium text-gray-700">Seleccione la oficina con la que quisiera trabajar <span class="text-red-500">*</span></label>
                            <select id="oficina" name="oficina" required class="mt-1 w-full border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- seleccione una opción --</option>
                                @foreach($oficinas as $oficina)
                                    <option value="{{ $oficina }}" @selected(old('oficina') === $oficina)>{{ $oficina }}</option>
                                @endforeach
                            </select>
                            @error('oficina') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <label class="inline-flex items-start gap-3 text-sm text-gray-700">
                        <input type="checkbox" name="acepta_terminos" value="1" required @checked(old('acepta_terminos')) class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>Aceptar <a href="{{ route('terminos-condiciones') }}" class="text-blue-600 hover:underline">términos y condiciones</a></span>
                    </label>
                    @error('acepta_terminos') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-8 flex flex-col sm:flex-row sm:items-center gap-3">
                <button type="submit" class="bg-blue-600 text-white font-semibold px-6 py-2.5 rounded hover:bg-blue-700 transition">
                    <i class="fa-solid fa-paper-plane mr-2"></i> Enviar información
                </button>
                <a href="{{ url('/') }}" class="text-sm font-semibold text-gray-500 hover:text-gray-700">Volver a la tienda</a>
            </div>
        </form>
    </main>

    @include('tienda.partials.site-footer')

</body>
</html>

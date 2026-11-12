<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('tienda.partials.seo', [
        'seoTitle' => 'Términos y condiciones | CDA System',
        'seoDescription' => 'Consulta los términos y condiciones de compra, garantía y uso del sitio de CDA System.',
        'seoCanonical' => route('terminos-condiciones'),
    ])
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans text-gray-700">
    @include('tienda.partials.site-header')

    <main class="max-w-4xl mx-auto px-4 py-12">
        <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">
            <nav class="text-sm text-gray-500 mb-6">
                <a href="{{ url('/') }}" class="text-blue-600 hover:underline">Inicio</a>
                <span class="mx-2">/</span>
                <a href="{{ route('distribuidor.create') }}" class="text-blue-600 hover:underline">Ser distribuidor</a>
                <span class="mx-2">/</span>
                <span>Términos y condiciones</span>
            </nav>

            <h1 class="text-3xl font-bold text-gray-900 mb-6">Términos y condiciones</h1>

            <div class="space-y-5 text-sm leading-7">
                <p>Al enviar esta solicitud, el solicitante acepta proporcionar información verídica y completa sobre su empresa, su representante legal y su forma de operar.</p>
                <p>CDA System Store revisará la información recibida para evaluar el interés de participar en la red de distribuidores autorizados. La empresa podrá aceptar, rechazar o solicitar información adicional en cualquier momento.</p>
                <p>La información proporcionada será tratada con confidencialidad y únicamente se utilizará para fines de evaluación comercial y de relación con distribuidores.</p>
                <p>El solicitante declara ser responsable de la veracidad de los datos consignados y autoriza al contacto correspondiente mediante teléfono, correo electrónico o cualquier otro medio informado en esta solicitud.</p>
                <p>La falta de información, la entrega de datos inexactos o la omisión de documentación relevante podrá resultar en la no aprobación de la solicitud.</p>
                <p>Estos términos pueden ser actualizados en cualquier momento y la solicitud en curso se evaluará conforme a las políticas vigentes al momento de la revisión.</p>
            </div>

            <div class="mt-8">
                <a href="{{ route('distribuidor.create') }}" class="inline-flex items-center bg-blue-600 text-white font-semibold px-4 py-2 rounded hover:bg-blue-700 transition">
                    Volver al formulario
                </a>
            </div>
        </div>
    </main>

    @include('tienda.partials.site-footer')
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Producto - Admin CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans pb-12">
    @include('admin.partials.header')

    <div class="max-w-5xl mx-auto px-4 mt-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-3xl font-bold text-gray-800">{{ $titulo }}</h2>
            <a href="{{ route('admin.productos.index') }}" class="text-blue-600 hover:underline font-medium">← Volver al inventario</a>
        </div>

        @include('admin.productos._form')
    </div>
</body>
</html>

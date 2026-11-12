<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - Admin CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans pb-12">
    @include('admin.partials.header')

    <div class="max-w-5xl mx-auto px-4 mt-6">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">{{ $titulo }}</h2>
                <p class="text-sm text-gray-500">{{ $producto->sku ?: 'Sin SKU' }} · ID {{ $producto->id_producto }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ url('/producto/'.$producto->id_producto) }}" target="_blank" class="text-gray-600 hover:underline font-medium">Ver en tienda ↗</a>
                <a href="{{ route('admin.productos.index') }}" class="text-blue-600 hover:underline font-medium">← Volver al inventario</a>
            </div>
        </div>

        @include('admin.productos._form')
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo proveedor - Admin CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">
    @include('admin.partials.header')
    <main class="max-w-3xl mx-auto px-4 mt-6 pb-10">
        <a href="{{ route('admin.proveedores.index') }}" class="text-sm text-blue-700 hover:underline">&larr; Proveedores</a>
        <h1 class="flex items-center gap-2 text-2xl font-bold text-gray-800 mt-2 mb-5"><x-icon name="truck" class="h-6 w-6 text-blue-600" /> Nuevo proveedor</h1>

        @include('admin.proveedores._form', [
            'proveedor' => null,
            'accion' => route('admin.proveedores.store'),
            'metodo' => 'POST',
            'textoBoton' => 'Registrar proveedor',
        ])
    </main>
</body>
</html>

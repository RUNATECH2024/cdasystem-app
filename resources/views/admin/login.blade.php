<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración - CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded shadow-md w-96">
        <h2 class="flex items-center justify-center gap-2 text-2xl font-bold text-gray-800 mb-2"><x-icon name="shield" class="h-6 w-6 text-blue-600" /> Administración</h2>
        <p class="text-center text-sm text-gray-500 mb-6">Acceso para personal de CDA System</p>

        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-2 rounded mb-4 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('admin.login') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="flex items-center gap-2 text-gray-700 text-sm font-bold mb-2"><x-icon name="user" class="text-blue-600" /> <span>Usuario</span></label>
                <input type="text" name="usuario" value="{{ old('usuario') }}" required autofocus
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500">
            </div>
            <div class="mb-6">
                <label class="flex items-center gap-2 text-gray-700 text-sm font-bold mb-2"><x-icon name="lock" class="text-blue-600" /> <span>Contraseña</span></label>
                <input type="password" name="password" required
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500">
            </div>
            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition font-bold">
                <x-icon name="key" /> Ingresar
            </button>
        </form>

        <p class="text-center text-sm text-gray-600 mt-4">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-1 text-blue-500 hover:underline"><x-icon name="home" class="h-3.5 w-3.5" /> Volver a la tienda</a>
        </p>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Admin CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">
    @include('admin.partials.header')
    <main class="max-w-6xl mx-auto px-4 mt-6 pb-10">
        <div class="flex flex-wrap items-end justify-between gap-3 mb-5">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Usuarios del sistema</h1>
                <p class="text-sm text-gray-500">{{ $usuarios->total() }} cuenta(s) registradas.</p>
            </div>
            <a href="{{ route('admin.usuarios.create') }}" class="bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-800">Crear usuario</a>
        </div>

        @if(session('success'))<div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>@endif

        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b text-gray-600 uppercase text-xs">
                    <tr><th class="px-4 py-3">Nombre</th><th class="px-4 py-3">Usuario</th><th class="px-4 py-3">Rol</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3">Creado</th></tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $usuario)
                        <tr class="border-b">
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $usuario->nombre }}</td>
                            <td class="px-4 py-3">{{ $usuario->usuario }}</td>
                            <td class="px-4 py-3">{{ $usuario->rol }}</td>
                            <td class="px-4 py-3"><span class="rounded px-2 py-1 text-xs {{ $usuario->activo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ $usuario->activo ? 'Activo' : 'Inactivo' }}</span></td>
                            <td class="px-4 py-3 text-gray-500">{{ $usuario->fecha_creacion?->format('d/m/Y H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No hay usuarios registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $usuarios->links() }}</div>
    </main>
</body>
</html>
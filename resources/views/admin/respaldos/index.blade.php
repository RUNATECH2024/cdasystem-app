<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respaldos - Admin CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">
    @include('admin.partials.header')
    <main class="max-w-5xl mx-auto px-4 mt-6 pb-10">
        <div class="flex flex-wrap items-end justify-between gap-3 mb-5">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Respaldos de base de datos</h1>
                <p class="text-sm text-gray-500">Los archivos SQL se guardan en almacenamiento privado.</p>
            </div>
            <form action="{{ route('admin.respaldos.create') }}" method="POST" onsubmit="return confirm('¿Crear un respaldo SQL de la base de datos ahora?')">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-800"><x-icon name="database" /> Crear respaldo</button>
            </form>
        </div>

        @if(session('success'))<div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="bg-red-100 text-red-800 p-3 rounded mb-4">{{ session('error') }}</div>@endif

        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b text-gray-600 uppercase text-xs"><tr><th class="px-4 py-3">Archivo</th><th class="px-4 py-3">Fecha</th><th class="px-4 py-3">Tamaño</th><th class="px-4 py-3 text-right">Acción</th></tr></thead>
                <tbody>
                    @forelse($respaldos as $respaldo)
                        <tr class="border-b">
                            <td class="px-4 py-3 font-medium">{{ $respaldo['nombre'] }}</td>
                            <td class="px-4 py-3">{{ date('d/m/Y H:i:s', $respaldo['fecha']) }}</td>
                            <td class="px-4 py-3">{{ number_format($respaldo['bytes'] / 1048576, 2) }} MB</td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('admin.respaldos.download', $respaldo['nombre']) }}" class="inline-flex items-center gap-1 text-blue-700 font-semibold hover:underline"><x-icon name="download" class="h-3.5 w-3.5" /> Descargar</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No hay respaldos guardados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
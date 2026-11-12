<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría - Admin CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">
    @include('admin.partials.header')
    <main class="max-w-7xl mx-auto px-4 mt-6 pb-10">
        <div class="mb-5">
            <h1 class="text-2xl font-bold text-gray-800">Auditoría</h1>
            <p class="text-sm text-gray-500">Accesos y cambios realizados por administradores.</p>
        </div>

        <form method="GET" action="{{ route('admin.auditoria.index') }}" class="bg-white rounded shadow p-4 mb-4 flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-56">
                <label for="q" class="flex items-center gap-1.5 text-xs font-bold text-gray-600 uppercase mb-1"><x-icon name="search" class="h-3.5 w-3.5" /> Buscar</label>
                <input id="q" type="search" name="q" value="{{ $q }}" placeholder="Usuario, acción o registro" class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <button type="submit" class="inline-flex items-center gap-2 bg-gray-800 text-white px-4 py-2 rounded text-sm hover:bg-gray-900"><x-icon name="search" /> Buscar</button>
            <a href="{{ route('admin.auditoria.index') }}" class="text-sm text-gray-500 hover:underline">Limpiar</a>
        </form>

        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b text-gray-600 uppercase text-xs">
                    <tr><th class="px-4 py-3">Fecha</th><th class="px-4 py-3">Administrador</th><th class="px-4 py-3">Acción</th><th class="px-4 py-3">Objetivo</th><th class="px-4 py-3">Resultado</th><th class="px-4 py-3">IP</th></tr>
                </thead>
                <tbody>
                    @forelse($auditorias as $evento)
                        <tr class="border-b align-top">
                            <td class="px-4 py-3 whitespace-nowrap">{{ $evento->created_at?->format('d/m/Y H:i:s') }}</td>
                            <td class="px-4 py-3">{{ $evento->actor_usuario ?: 'No identificado' }}<span class="block text-xs text-gray-500">{{ $evento->actor_rol ?: '—' }}</span></td>
                            <td class="px-4 py-3"><span class="font-medium">{{ $evento->accion }}</span><span class="block text-xs text-gray-500">{{ $evento->metodo_http }} {{ $evento->ruta }}</span>
                                @if($evento->metadata['campos_enviados'] ?? [])<span class="block text-xs text-gray-500">Campos: {{ implode(', ', $evento->metadata['campos_enviados']) }}</span>@endif
                            </td>
                            <td class="px-4 py-3">{{ $evento->objetivo ?: '—' }}</td>
                            <td class="px-4 py-3">{{ $evento->codigo_respuesta ?: '—' }}</td>
                            <td class="px-4 py-3">{{ $evento->ip ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Todavía no hay eventos de auditoría.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $auditorias->links() }}</div>
    </main>
</body>
</html>
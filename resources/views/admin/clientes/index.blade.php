<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Admin CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">
    @include('admin.partials.header')
    <main class="max-w-7xl mx-auto px-4 mt-6 pb-10">
        <div class="flex flex-wrap items-end justify-between gap-3 mb-5">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold text-gray-800"><x-icon name="users" class="h-6 w-6 text-blue-600" /> Clientes</h1>
                <p class="text-sm text-gray-500">Consulta clientes, sus equipos y el historial de atención.</p>
            </div>
            <a href="{{ route('admin.clientes.create') }}" class="inline-flex items-center gap-2 bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-800"><x-icon name="plus" /> Nuevo cliente</a>
        </div>

        @if(session('success'))<div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="bg-red-100 text-red-800 p-3 rounded mb-4">{{ session('error') }}</div>@endif

        <form method="GET" action="{{ route('admin.clientes.index') }}" class="bg-white rounded shadow p-4 mb-4 grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            <div class="md:col-span-2">
                <label for="q" class="flex items-center gap-1.5 text-xs font-bold text-gray-600 uppercase mb-1"><x-icon name="search" class="h-3.5 w-3.5" /> Buscar</label>
                <input id="q" type="search" name="q" value="{{ $q }}" placeholder="Nombre, DNI/RUC, teléfono, correo o serie" class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label for="estado" class="flex items-center gap-1.5 text-xs font-bold text-gray-600 uppercase mb-1"><x-icon name="settings" class="h-3.5 w-3.5" /> Estado</label>
                <select id="estado" name="estado" class="w-full border rounded px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    <option value="activos" @selected($estado === 'activos')>Activos</option>
                    <option value="inactivos" @selected($estado === 'inactivos')>Inactivos</option>
                </select>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="inline-flex items-center gap-2 bg-gray-800 text-white px-4 py-2 rounded text-sm hover:bg-gray-900"><x-icon name="filter" /> Filtrar</button>
                <a href="{{ route('admin.clientes.index') }}" class="text-sm text-gray-500 hover:underline">Limpiar</a>
            </div>
        </form>

        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Documento</th>
                        <th class="px-4 py-3">Contacto</th>
                        <th class="px-4 py-3 text-center">Equipos</th>
                        <th class="px-4 py-3 text-center">Órdenes</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientes as $cliente)
                        <tr class="border-b align-top hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-bold text-gray-800">{{ $cliente->nombre }}</p>
                                <p class="text-xs text-gray-500">Registrado: {{ $cliente->fecha_registro?->format('d/m/Y') ?? 'Sin fecha' }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $cliente->dni_ruc ?: '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                <p class="inline-flex items-center gap-1"><x-icon name="phone" class="h-3.5 w-3.5" /> {{ $cliente->telefono }}</p>
                                <p class="inline-flex items-center gap-1 text-xs"><x-icon name="mail" class="h-3.5 w-3.5" /> {{ $cliente->email ?: 'Sin correo' }}</p>
                            </td>
                            <td class="px-4 py-3 text-center font-medium">{{ $cliente->equipos_count }}</td>
                            <td class="px-4 py-3 text-center font-medium">{{ $cliente->ordenes_count }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-block rounded px-2 py-1 text-xs font-medium {{ $cliente->activo ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                                    {{ $cliente->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap justify-end gap-3 whitespace-nowrap text-xs font-semibold">
                                    <a href="{{ route('admin.clientes.show', $cliente) }}" class="text-blue-700 hover:underline">Ver</a>
                                    <a href="{{ route('admin.clientes.edit', $cliente) }}" class="text-amber-700 hover:underline">Editar</a>
                                    <form action="{{ route('admin.clientes.estado', $cliente) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-gray-600 hover:underline">{{ $cliente->activo ? 'Desactivar' : 'Activar' }}</button>
                                    </form>
                                    @if($cliente->ordenes_count === 0 && $cliente->pedidos_count === 0 && $cliente->ventas_count === 0 && (float) $cliente->saldo_pendiente <= 0)
                                        <form action="{{ route('admin.clientes.destroy', $cliente) }}" method="POST" onsubmit="return confirm('¿Eliminar el cliente {{ $cliente->nombre }}? Esta acción no se puede deshacer.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-700 hover:underline">Eliminar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">No hay clientes que coincidan con la búsqueda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $clientes->links() }}</div>
    </main>
</body>
</html>

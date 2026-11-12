<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Órdenes de taller - Admin CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">
    @include('admin.partials.header')

    <main class="max-w-7xl mx-auto px-4 mt-6 pb-10">
        <div class="flex flex-wrap items-end justify-between gap-3 mb-5">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Órdenes de taller</h1>
                <p class="text-sm text-gray-500">{{ $ordenes->total() }} orden(es) registradas.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.pedidos.index') }}" class="text-sm font-medium text-blue-700 hover:underline">Ver pedidos web</a>
                <a href="{{ route('admin.clientes.create') }}" class="inline-flex items-center gap-1 text-sm font-medium text-blue-700 hover:underline"><x-icon name="plus" class="h-3.5 w-3.5" /> Nuevo cliente</a>
                <a href="{{ route('admin.ordenes.create') }}" class="inline-flex items-center gap-2 bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-800"><x-icon name="plus" /> Nueva orden</a>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.ordenes.index') }}" class="bg-white rounded shadow p-4 mb-4 grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            <div class="md:col-span-2">
                <label for="q" class="flex items-center gap-1.5 text-xs font-bold text-gray-600 uppercase mb-1"><x-icon name="search" class="h-3.5 w-3.5" /> Buscar</label>
                <input id="q" type="search" name="q" value="{{ $q }}" placeholder="Número, cliente, equipo, serie o falla" class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label for="estado" class="flex items-center gap-1.5 text-xs font-bold text-gray-600 uppercase mb-1"><x-icon name="settings" class="h-3.5 w-3.5" /> Estado</label>
                <select id="estado" name="estado" class="w-full border rounded px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    @foreach($estados as $opcion)
                        <option value="{{ $opcion }}" @selected($estado === $opcion)>{{ $opcion }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="inline-flex items-center gap-2 bg-gray-800 text-white px-4 py-2 rounded text-sm hover:bg-gray-900"><x-icon name="filter" /> Filtrar</button>
                <a href="{{ route('admin.ordenes.index') }}" class="text-sm text-gray-500 hover:underline">Limpiar</a>
            </div>
        </form>

        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Orden</th>
                        <th class="px-4 py-3">Cliente y equipo</th>
                        <th class="px-4 py-3">Falla / diagnóstico</th>
                        <th class="px-4 py-3">Repuestos</th>
                        <th class="px-4 py-3">Técnico y estado</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ordenes as $orden)
                        <tr class="border-b align-top hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <p class="font-bold text-gray-800">#{{ $orden->id_orden }}</p>
                                <p class="text-xs text-gray-500">Ingreso: {{ $orden->fecha_ingreso?->format('d/m/Y H:i') ?? 'Sin fecha' }}</p>
                                @if($orden->fecha_entrega)
                                    <p class="text-xs text-gray-500">Entrega: {{ $orden->fecha_entrega->format('d/m/Y H:i') }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 min-w-52">
                                <p class="font-medium text-gray-800">{{ $orden->cliente?->nombre ?? 'Cliente no disponible' }}</p>
                                <p class="text-xs text-gray-600">{{ $orden->equipo?->tipo_equipo ?? 'Equipo' }} · {{ $orden->equipo?->marca }} {{ $orden->equipo?->modelo }}</p>
                                <p class="text-xs text-gray-500">Serie: {{ $orden->equipo?->numero_serie ?: 'Sin número' }}</p>
                            </td>
                            <td class="px-4 py-3 min-w-56">
                                <p class="text-gray-800">{{ $orden->falla_reportada }}</p>
                                @if($orden->diagnostico)
                                    <p class="text-xs text-gray-500 mt-1">Diagnóstico: {{ $orden->diagnostico }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 min-w-48">
                                @forelse($orden->repuestos as $repuesto)
                                    <p class="text-gray-800">{{ $repuesto->cantidad }} × {{ $repuesto->producto?->nombre ?? 'Repuesto eliminado' }}</p>
                                @empty
                                    <span class="text-gray-400">Sin repuestos</span>
                                @endforelse
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-gray-700">{{ $orden->tecnico?->nombre ?? 'Sin asignar' }}</p>
                                <span class="inline-block rounded px-2 py-1 mt-1 text-xs font-medium bg-yellow-100 text-yellow-800">{{ $orden->estado ?: 'Sin estado' }}</span>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-gray-800 whitespace-nowrap">${{ number_format((float) $orden->total_general, 2) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-3 whitespace-nowrap text-xs font-semibold">
                                    <a href="{{ route('admin.ordenes.show', $orden) }}" class="text-blue-700 hover:underline">Ver</a>
                                    <a href="{{ route('admin.ordenes.pdf', $orden) }}" class="text-gray-700 hover:underline">PDF</a>
                                    <a href="{{ route('admin.ordenes.edit', $orden) }}" class="text-amber-700 hover:underline">Editar</a>
                                    <form action="{{ route('admin.ordenes.destroy', $orden) }}" method="POST" onsubmit="return confirm('¿Eliminar la orden #{{ $orden->id_orden }}? Esta acción no se puede deshacer.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-700 hover:underline">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">No hay órdenes de taller que coincidan con la búsqueda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $ordenes->links() }}</div>
    </main>
</body>
</html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos web - Admin CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">
    @include('admin.partials.header')

    <main class="max-w-7xl mx-auto px-4 mt-6 pb-10">
        <div class="flex flex-wrap items-end justify-between gap-3 mb-5">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Pedidos web</h1>
                <p class="text-sm text-gray-500">{{ $pedidos->total() }} pedido(s) registrados.</p>
            </div>
            <a href="{{ route('admin.ordenes.index') }}" class="text-sm font-medium text-blue-700 hover:underline">Ver órdenes de taller</a>
        </div>

        @if(session('success'))<div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="bg-red-100 text-red-800 p-3 rounded mb-4">{{ session('error') }}</div>@endif

        <form method="GET" action="{{ route('admin.pedidos.index') }}" class="bg-white rounded shadow p-4 mb-4 grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            <div class="md:col-span-2">
                <label for="q" class="flex items-center gap-1.5 text-xs font-bold text-gray-600 uppercase mb-1"><x-icon name="search" class="h-3.5 w-3.5" /> Buscar</label>
                <input id="q" type="search" name="q" value="{{ $q }}" placeholder="Número, cliente, correo o teléfono" class="w-full border rounded px-3 py-2 text-sm">
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
                <a href="{{ route('admin.pedidos.index') }}" class="text-sm text-gray-500 hover:underline">Limpiar</a>
            </div>
        </form>

        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Pedido</th>
                        <th class="px-4 py-3">Cliente y entrega</th>
                        <th class="px-4 py-3">Artículos</th>
                        <th class="px-4 py-3">Pago</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidos as $pedido)
                        <tr class="border-b align-top hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <p class="font-bold text-gray-800">#{{ $pedido->id_pedido }}</p>
                                <p class="text-xs text-gray-500">{{ $pedido->fecha_pedido?->format('d/m/Y H:i') ?? 'Sin fecha' }}</p>
                            </td>
                            <td class="px-4 py-3 min-w-52">
                                <p class="font-medium text-gray-800">{{ $pedido->nombre_contacto ?: $pedido->cliente?->nombre ?: 'Cliente no registrado' }}</p>
                                <p class="text-xs text-gray-500">{{ $pedido->email_contacto ?: $pedido->cliente?->email ?: 'Sin correo' }}</p>
                                <p class="text-xs text-gray-500">{{ $pedido->telefono_contacto ?: 'Sin teléfono' }}</p>
                                <p class="text-xs text-gray-600 mt-1">{{ $pedido->direccion_envio ?: 'Sin dirección de envío' }}</p>
                            </td>
                            <td class="px-4 py-3 min-w-56">
                                @forelse($pedido->detalles as $detalle)
                                    <p class="text-gray-800">{{ $detalle->cantidad }} × {{ $detalle->producto?->nombre ?? 'Producto eliminado' }}</p>
                                @empty
                                    <span class="text-gray-400">Sin artículos</span>
                                @endforelse
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $pedido->metodo_pago ?: 'Sin definir' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-block rounded px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800">{{ $pedido->estado_pedido ?: 'Sin estado' }}</span>
                                <p class="text-xs text-gray-500 mt-1">{{ $pedido->metodo_entrega ?: 'Entrega sin definir' }}</p>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-gray-800 whitespace-nowrap">${{ number_format((float) $pedido->total, 2) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-3 whitespace-nowrap text-xs font-semibold">
                                    <a href="{{ route('admin.pedidos.show', $pedido) }}" class="text-blue-700 hover:underline">Ver</a>
                                    @if(in_array($pedido->estado_pedido, ['Pendiente', 'Pagado'], true))
                                        <form action="{{ route('admin.pedidos.atendido', $pedido) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-green-700 hover:underline">Iniciar preparación</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">No hay pedidos web que coincidan con la búsqueda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $pedidos->links() }}</div>
    </main>
</body>
</html>
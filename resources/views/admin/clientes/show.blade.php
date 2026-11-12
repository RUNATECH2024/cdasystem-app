<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $cliente->nombre }} - Admin CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">
    @include('admin.partials.header')
    <main class="max-w-6xl mx-auto px-4 mt-6 pb-10">
        <a href="{{ route('admin.clientes.index') }}" class="text-sm text-blue-700 hover:underline">&larr; Clientes</a>

        <div class="flex flex-wrap items-end justify-between gap-3 mt-2 mb-5">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold text-gray-800"><x-icon name="user" class="h-6 w-6 text-blue-600" /> {{ $cliente->nombre }}</h1>
                <p class="text-sm text-gray-500">Cliente #{{ $cliente->id_cliente }} · Registrado {{ $cliente->fecha_registro?->format('d/m/Y') ?? 'sin fecha' }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.clientes.edit', $cliente) }}" class="inline-flex items-center gap-2 bg-white text-gray-700 px-4 py-2 rounded shadow text-sm font-medium hover:bg-gray-50"><x-icon name="settings" /> Editar</a>
                <a href="{{ route('admin.ordenes.create', ['cliente' => $cliente->id_cliente]) }}" class="inline-flex items-center gap-2 bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-800"><x-icon name="wrench" /> Nueva orden</a>
            </div>
        </div>

        @if(session('success'))<div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="bg-red-100 text-red-800 p-3 rounded mb-4">{{ session('error') }}</div>@endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
            <section class="bg-white rounded shadow p-5 lg:col-span-2">
                <h2 class="flex items-center gap-2 font-bold text-gray-800 mb-4"><x-icon name="id-card" class="text-blue-600" /> Datos de contacto</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">DNI / RUC</dt><dd class="font-medium text-gray-800">{{ $cliente->dni_ruc ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Teléfono</dt><dd class="font-medium text-gray-800">{{ $cliente->telefono }}</dd></div>
                    <div><dt class="text-gray-500">Correo electrónico</dt><dd class="font-medium text-gray-800 break-all">{{ $cliente->email ?: 'Sin correo' }}</dd></div>
                    <div><dt class="text-gray-500">Dirección</dt><dd class="font-medium text-gray-800">{{ $cliente->direccion ?: 'Sin dirección' }}</dd></div>
                    <div><dt class="text-gray-500">Estado</dt><dd><span class="inline-block rounded px-2 py-1 text-xs font-medium {{ $cliente->activo ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">{{ $cliente->activo ? 'Activo' : 'Inactivo' }}</span></dd></div>
                    <div><dt class="text-gray-500">Saldo pendiente</dt><dd class="font-medium {{ (float) $cliente->saldo_pendiente > 0 ? 'text-red-600' : 'text-gray-800' }}">${{ number_format((float) $cliente->saldo_pendiente, 2) }}</dd></div>
                </dl>
            </section>

            <section class="bg-white rounded shadow p-5">
                <h2 class="flex items-center gap-2 font-bold text-gray-800 mb-4"><x-icon name="chart" class="text-blue-600" /> Resumen</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between"><span class="text-gray-500">Equipos</span><span class="font-bold text-gray-800">{{ $cliente->equipos->count() }}</span></div>
                    <div class="flex items-center justify-between"><span class="text-gray-500">Órdenes de taller</span><span class="font-bold text-gray-800">{{ $totalOrdenes }}</span></div>
                    <div class="flex items-center justify-between"><span class="text-gray-500">Pedidos web</span><span class="font-bold text-gray-800">{{ $totalPedidos }}</span></div>
                    <div class="flex items-center justify-between"><span class="text-gray-500">Ventas</span><span class="font-bold text-gray-800">{{ $totalVentas }}</span></div>
                </div>
            </section>
        </div>

        <section class="bg-white rounded shadow mb-6">
            <div class="px-4 py-3 border-b flex flex-wrap items-center justify-between gap-2">
                <h2 class="flex items-center gap-2 font-bold text-gray-800"><x-icon name="laptop" class="text-blue-600" /> Equipos asociados</h2>
                <span class="text-xs text-gray-500">{{ $cliente->equipos->count() }} equipo(s)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-2">Equipo</th>
                            <th class="px-4 py-2">Serie</th>
                            <th class="px-4 py-2">Contador</th>
                            <th class="px-4 py-2 text-center">Órdenes</th>
                            <th class="px-4 py-2 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cliente->equipos as $equipo)
                            <tr class="border-b">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-800">{{ $equipo->tipo_equipo }}</p>
                                    <p class="text-xs text-gray-500">{{ trim(($equipo->marca ?? '').' '.($equipo->modelo ?? '')) ?: 'Sin marca/modelo' }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $equipo->numero_serie ?: '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $equipo->contador_paginas ?? 0 }}</td>
                                <td class="px-4 py-3 text-center font-medium">{{ $equipo->ordenes_count }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.ordenes.create', ['cliente' => $cliente->id_cliente, 'equipo' => $equipo->id_equipo]) }}" class="text-blue-700 text-xs font-semibold hover:underline">Nueva orden</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Este cliente todavía no tiene equipos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <section class="bg-white rounded shadow">
                <div class="px-4 py-3 border-b flex items-center justify-between">
                    <h2 class="flex items-center gap-2 font-bold text-gray-800"><x-icon name="wrench" class="text-blue-600" /> Órdenes recientes</h2>
                    <a href="{{ route('admin.ordenes.index', ['q' => $cliente->nombre]) }}" class="text-xs text-blue-700 hover:underline">Ver todas</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs"><tr><th class="px-4 py-2">Orden</th><th class="px-4 py-2">Estado</th><th class="px-4 py-2 text-right">Total</th></tr></thead>
                        <tbody>
                            @forelse($cliente->ordenes as $orden)
                                <tr class="border-b">
                                    <td class="px-4 py-2"><a href="{{ route('admin.ordenes.show', $orden) }}" class="font-medium text-blue-700 hover:underline">#{{ $orden->id_orden }}</a><span class="block text-xs text-gray-500">{{ $orden->fecha_ingreso?->format('d/m/Y') }} · {{ $orden->equipo?->tipo_equipo ?? 'Equipo' }}</span></td>
                                    <td class="px-4 py-2 text-gray-600">{{ $orden->estado ?: 'Sin estado' }}</td>
                                    <td class="px-4 py-2 text-right font-medium">${{ number_format((float) $orden->total_general, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">Sin órdenes registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="bg-white rounded shadow">
                <div class="px-4 py-3 border-b flex items-center justify-between">
                    <h2 class="flex items-center gap-2 font-bold text-gray-800"><x-icon name="cart" class="text-blue-600" /> Pedidos web recientes</h2>
                    <a href="{{ route('admin.pedidos.index', ['q' => $cliente->nombre]) }}" class="text-xs text-blue-700 hover:underline">Ver todos</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs"><tr><th class="px-4 py-2">Pedido</th><th class="px-4 py-2">Estado</th><th class="px-4 py-2 text-right">Total</th></tr></thead>
                        <tbody>
                            @forelse($cliente->pedidos as $pedido)
                                <tr class="border-b">
                                    <td class="px-4 py-2"><a href="{{ route('admin.pedidos.show', $pedido) }}" class="font-medium text-blue-700 hover:underline">#{{ $pedido->id_pedido }}</a><span class="block text-xs text-gray-500">{{ $pedido->fecha_pedido?->format('d/m/Y') }}</span></td>
                                    <td class="px-4 py-2 text-gray-600">{{ $pedido->estado_pedido ?: 'Sin estado' }}</td>
                                    <td class="px-4 py-2 text-right font-medium">${{ number_format((float) $pedido->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">Sin pedidos web registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</body>
</html>

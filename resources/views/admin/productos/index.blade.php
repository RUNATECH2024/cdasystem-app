<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Admin CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">
    @include('admin.partials.header')

    <div class="max-w-7xl mx-auto px-4 mt-6 pb-10">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Inventario de Productos</h2>
                <p class="text-sm text-gray-500">{{ $productos->total() }} producto(s) registrados.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.reportes.index') }}" class="bg-white text-gray-700 px-4 py-2 rounded shadow hover:bg-gray-50 font-medium">Reportes</a>
                <a href="{{ route('admin.productos.create') }}" class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 font-medium shadow"><x-icon name="plus" /> Nuevo producto</a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4 shadow-sm border-l-4 border-green-500">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('admin.productos.index') }}" class="bg-white rounded shadow p-4 mb-4 grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
            <div class="md:col-span-2">
                <label class="flex items-center gap-1.5 text-xs font-bold text-gray-600 uppercase mb-1"><x-icon name="search" class="h-3.5 w-3.5" /> Buscar</label>
                <input type="text" name="q" value="{{ $q }}" placeholder="Nombre, SKU o marca" class="w-full border rounded px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="flex items-center gap-1.5 text-xs font-bold text-gray-600 uppercase mb-1"><x-icon name="layers" class="h-3.5 w-3.5" /> Categoría</label>
                <select name="id_categoria" class="w-full border rounded px-2 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    <option value="">Todas</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id_categoria }}" @selected((string) $idCategoria === (string) $categoria->id_categoria)>{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="flex items-center gap-1.5 text-xs font-bold text-gray-600 uppercase mb-1"><x-icon name="settings" class="h-3.5 w-3.5" /> Estado</label>
                <select name="estado" class="w-full border rounded px-2 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    <option value="">Todos</option>
                    <option value="activos" @selected($estado === 'activos')>Activos</option>
                    <option value="inactivos" @selected($estado === 'inactivos')>Inactivos</option>
                </select>
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-700 pb-2">
                <input type="checkbox" name="stock_bajo" value="1" class="h-4 w-4" @checked($stockBajo)>
                <x-icon name="alert" class="text-amber-600" /> Solo stock bajo
            </label>
            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-2 bg-gray-800 text-white px-4 py-2 rounded text-sm hover:bg-gray-900"><x-icon name="filter" /> Filtrar</button>
                <a href="{{ route('admin.productos.index') }}" class="text-sm text-gray-500 hover:underline">Limpiar</a>
            </div>
        </form>

        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3">Marca</th>
                        <th class="px-4 py-3">Subcategoría</th>
                        <th class="px-4 py-3">P. Venta</th>
                        <th class="px-4 py-3">Stock</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $producto)
                        <tr class="border-b hover:bg-gray-50 align-top">
                            <td class="px-4 py-3 font-medium text-gray-500">{{ $producto->id_producto }}</td>
                            <td class="px-4 py-3">
                                <p class="font-bold text-gray-800">{{ $producto->nombre }}</p>
                                <p class="text-xs text-gray-500">SKU: {{ $producto->sku ?: '—' }}</p>
                            </td>

                            <td class="px-4 py-3">
                                <form action="{{ route('admin.productos.marca', $producto) }}" method="POST" class="flex items-center gap-1">
                                    @csrf
                                    <input type="text" name="marca" value="{{ $producto->marca }}" maxlength="80" placeholder="Sin marca"
                                           class="w-24 border border-gray-300 rounded px-2 py-1 text-xs focus:border-blue-500 focus:outline-none">
                                    <button type="submit" class="text-xs bg-gray-700 text-white px-2 py-1 rounded hover:bg-gray-800" title="Guardar marca">OK</button>
                                </form>
                            </td>

                            <td class="px-4 py-3 text-gray-600">
                                {{ $producto->subcategoria?->nombre ?? '—' }}
                                @if($producto->subcategoria?->categoria)
                                    <p class="text-xs text-gray-400">{{ $producto->subcategoria->categoria->nombre }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">${{ number_format((float) $producto->precio_venta, 2) }}</td>
                            <td class="px-4 py-3 font-bold {{ $producto->stock_actual <= $producto->stock_minimo ? 'text-red-600' : 'text-green-600' }}">
                                {{ $producto->stock_actual }}
                                <span class="block text-xs font-normal text-gray-400">mín. {{ $producto->stock_minimo }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1">
                                    <span class="px-2 py-1 rounded text-xs text-center {{ $producto->activo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $producto->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                    @if($producto->destacado)
                                        <span class="px-2 py-1 rounded text-xs text-center bg-yellow-100 text-yellow-700">Destacado</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col items-end gap-1 text-xs">
                                    <a href="{{ route('admin.productos.edit', $producto) }}" class="text-blue-600 hover:underline font-semibold">Editar</a>
                                    <a href="{{ route('admin.productos.pdf', $producto) }}" target="_blank" class="text-purple-600 hover:underline font-semibold">Ficha PDF</a>
                                    <a href="{{ url('/producto/'.$producto->id_producto) }}" target="_blank" class="text-gray-500 hover:underline">Ver en tienda ↗</a>

                                    <form action="{{ route('admin.productos.estado', $producto) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-gray-600 hover:underline">{{ $producto->activo ? 'Desactivar' : 'Activar' }}</button>
                                    </form>

                                    <form action="{{ route('admin.productos.destacado', $producto) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-gray-600 hover:underline">{{ $producto->destacado ? 'Quitar destacado' : 'Destacar' }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                                No hay productos que coincidan con los filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $productos->links() }}
        </div>
    </div>
</body>
</html>

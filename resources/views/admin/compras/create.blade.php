<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva compra - Admin CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">
    @include('admin.partials.header')
    <main class="max-w-6xl mx-auto px-4 mt-6 pb-10">
        <a href="{{ route('admin.compras.index') }}" class="text-sm text-blue-700 hover:underline">&larr; Compras</a>
        <div class="flex flex-wrap items-end justify-between gap-3 mt-2 mb-5">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold text-gray-800"><x-icon name="receipt" class="h-6 w-6 text-blue-600" /> Nueva compra</h1>
                <p class="text-sm text-gray-500">Al guardar se incrementará el stock y se actualizará el costo de los productos.</p>
            </div>
        </div>

        @if($errors->any())
            <div role="alert" class="bg-red-50 border-l-4 border-red-600 text-red-800 p-3 rounded mb-4">
                <p class="font-semibold">Hay {{ $errors->count() }} dato(s) que debes corregir.</p>
                <ul class="list-disc ml-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form id="compra-form" action="{{ route('admin.compras.store') }}" method="POST" class="space-y-5">
            @csrf

            <section class="bg-white rounded border border-gray-200 shadow-sm p-5">
                <h2 class="flex items-center gap-2 font-bold text-gray-800 mb-4"><x-icon name="truck" class="text-blue-600" /> Datos de la compra</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="id_proveedor" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="truck" class="text-blue-600" /> <span>Proveedor <span class="text-red-700">*</span></span></label>
                        <select id="id_proveedor" name="id_proveedor" required class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                            <option value="">Seleccione un proveedor</option>
                            @foreach($proveedores as $proveedor)
                                <option value="{{ $proveedor->id_proveedor }}" @selected((string) old('id_proveedor', $proveedorSeleccionado) === (string) $proveedor->id_proveedor)>
                                    {{ $proveedor->nombre }}{{ $proveedor->ruc ? ' · '.$proveedor->ruc : '' }}{{ $proveedor->activo ? '' : ' (inactivo)' }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_proveedor')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="numero_factura" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="document" class="text-blue-600" /> <span>Número de factura</span></label>
                        <input id="numero_factura" name="numero_factura" value="{{ old('numero_factura') }}" maxlength="50" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                        @error('numero_factura')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="fecha_compra" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="calendar" class="text-blue-600" /> <span>Fecha <span class="text-red-700">*</span></span></label>
                            <input id="fecha_compra" type="date" name="fecha_compra" value="{{ old('fecha_compra', now()->toDateString()) }}" required class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                            @error('fecha_compra')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="fecha_vencimiento" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="calendar" class="text-blue-600" /> <span>Vence</span></label>
                            <input id="fecha_vencimiento" type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                            @error('fecha_vencimiento')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded border border-gray-200 shadow-sm p-5">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                    <h2 class="flex items-center gap-2 font-bold text-gray-800"><x-icon name="box" class="text-blue-600" /> Productos</h2>
                    <button type="button" id="agregar-linea" class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 px-3 py-2 rounded text-xs font-semibold hover:bg-blue-100"><x-icon name="plus" /> Añadir línea</button>
                </div>
                @error('productos')<p class="text-sm text-red-600 mb-2">{{ $message }}</p>@enderror

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                            <tr>
                                <th class="px-2 py-2">Producto</th>
                                <th class="px-2 py-2 text-center">Stock</th>
                                <th class="px-2 py-2">Cantidad</th>
                                <th class="px-2 py-2">Costo unitario</th>
                                <th class="px-2 py-2 text-right">Subtotal</th>
                                <th class="px-2 py-2 text-right"> </th>
                            </tr>
                        </thead>
                        <tbody id="lineas"></tbody>
                    </table>
                </div>
                <p class="text-xs text-gray-500 mt-2">El costo unitario actualizará el precio de compra del producto.</p>
            </section>

            <section class="bg-white rounded border border-gray-200 shadow-sm p-5">
                <h2 class="flex items-center gap-2 font-bold text-gray-800 mb-4"><x-icon name="cash" class="text-blue-600" /> Totales y pago inicial</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between"><span class="text-gray-500">Subtotal</span><span id="resumen-subtotal" class="font-semibold">$0.00</span></div>
                        <div class="flex items-center justify-between gap-4">
                            <label for="impuesto" class="text-gray-500">Impuesto / IVA</label>
                            <input id="impuesto" type="number" name="impuesto" value="{{ old('impuesto', '0.00') }}" min="0" step="0.01" class="w-40 border border-gray-300 rounded px-3 py-2 text-right focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                        </div>
                        <div class="flex items-center justify-between border-t pt-3"><span class="font-bold text-gray-800">Total</span><span id="resumen-total" class="text-xl font-bold text-gray-800">$0.00</span></div>
                        <div class="flex items-center justify-between"><span class="text-gray-500">Pagado</span><span id="resumen-pagado" class="font-semibold text-green-700">$0.00</span></div>
                        <div class="flex items-center justify-between"><span class="text-gray-500">Saldo</span><span id="resumen-saldo" class="font-semibold text-red-600">$0.00</span></div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label for="monto_pagado" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="cash" class="text-blue-600" /> <span>Pago inicial</span></label>
                            <input id="monto_pagado" type="number" name="monto_pagado" value="{{ old('monto_pagado', '0.00') }}" min="0" step="0.01" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                            @error('monto_pagado')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                            <p id="pago-error" class="hidden text-sm text-red-600">El pago inicial no puede superar el total.</p>
                        </div>
                        <div>
                            <label for="metodo_pago" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="currency" class="text-blue-600" /> <span>Método de pago</span></label>
                            <select id="metodo_pago" name="metodo_pago" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                                @foreach(['Efectivo', 'Transferencia', 'Tarjeta', 'Cheque', 'Crédito'] as $metodo)
                                    <option value="{{ $metodo }}" @selected(old('metodo_pago', 'Efectivo') === $metodo)>{{ $metodo }}</option>
                                @endforeach
                            </select>
                            @error('metodo_pago')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </section>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.compras.index') }}" class="px-4 py-2 text-sm text-gray-700 hover:underline">Cancelar</a>
                <button type="submit" class="inline-flex items-center gap-2 bg-blue-700 text-white px-5 py-2 rounded text-sm font-medium hover:bg-blue-800"><x-icon name="check" /> Registrar compra</button>
            </div>
        </form>
    </main>

    <template id="linea-plantilla">
        <tr class="border-b">
            <td class="px-2 py-2 min-w-[20rem]">
                <select name="productos[__index__][id_producto]" class="producto-select w-full border border-gray-300 rounded px-2 py-2 text-sm" required></select>
            </td>
            <td class="px-2 py-2 text-center stock-display text-gray-500">—</td>
            <td class="px-2 py-2">
                <input type="number" name="productos[__index__][cantidad]" value="1" min="1" max="1000000" step="1" class="cantidad w-24 border border-gray-300 rounded px-2 py-2 text-sm" required>
            </td>
            <td class="px-2 py-2">
                <input type="number" name="productos[__index__][precio_costo_unitario]" value="0.00" min="0" step="0.01" class="costo w-32 border border-gray-300 rounded px-2 py-2 text-sm" required>
            </td>
            <td class="px-2 py-2 text-right font-medium linea-subtotal whitespace-nowrap">$0.00</td>
            <td class="px-2 py-2 text-right">
                <button type="button" class="quitar-linea text-red-700 hover:underline text-xs font-semibold">Quitar</button>
            </td>
        </tr>
    </template>

    <script>
        (() => {
            const productos = @json($productos);
            const lineasPrevias = @json(old('productos', []));
            const productosPorId = new Map(productos.map((producto) => [String(producto.id_producto), producto]));
            const plantilla = document.getElementById('linea-plantilla');
            const tbody = document.getElementById('lineas');
            let indice = 0;

            const formato = (valor) => '$' + Number(valor || 0).toFixed(2);

            const recalcular = () => {
                let subtotal = 0;

                tbody.querySelectorAll('tr').forEach((fila) => {
                    const cantidad = Number(fila.querySelector('.cantidad').value) || 0;
                    const costo = Number(fila.querySelector('.costo').value) || 0;
                    const linea = Math.round(cantidad * costo * 100) / 100;
                    fila.querySelector('.linea-subtotal').textContent = formato(linea);
                    subtotal += linea;
                });

                subtotal = Math.round(subtotal * 100) / 100;
                const impuesto = Number(document.getElementById('impuesto').value) || 0;
                const total = Math.round((subtotal + impuesto) * 100) / 100;
                const pagado = Number(document.getElementById('monto_pagado').value) || 0;
                const saldo = Math.max(0, Math.round((total - pagado) * 100) / 100);

                document.getElementById('resumen-subtotal').textContent = formato(subtotal);
                document.getElementById('resumen-total').textContent = formato(total);
                document.getElementById('resumen-pagado').textContent = formato(pagado);
                document.getElementById('resumen-saldo').textContent = formato(saldo);
                document.getElementById('pago-error').classList.toggle('hidden', pagado <= total);
            };

            const agregarLinea = (datos = null) => {
                const fila = plantilla.content.firstElementChild.cloneNode(true);
                const posicion = indice++;

                fila.querySelectorAll('[name]').forEach((campo) => {
                    campo.name = campo.name.replace('__index__', posicion);
                });

                const select = fila.querySelector('.producto-select');
                const vacio = document.createElement('option');
                vacio.value = '';
                vacio.textContent = 'Seleccione un producto';
                select.appendChild(vacio);

                productos.forEach((producto) => {
                    const opcion = document.createElement('option');
                    opcion.value = producto.id_producto;
                    opcion.textContent = producto.nombre + (producto.sku ? ' · ' + producto.sku : '') + ' · stock ' + producto.stock_actual;
                    select.appendChild(opcion);
                });

                const cantidad = fila.querySelector('.cantidad');
                const costo = fila.querySelector('.costo');
                const stock = fila.querySelector('.stock-display');

                const actualizarStock = () => {
                    const producto = productosPorId.get(String(select.value));
                    stock.textContent = producto ? producto.stock_actual : '—';
                };

                select.addEventListener('change', () => {
                    const producto = productosPorId.get(String(select.value));
                    actualizarStock();
                    if (producto) {
                        costo.value = Number(producto.precio_compra || 0).toFixed(2);
                    }
                    recalcular();
                });

                if (datos) {
                    select.value = String(datos.id_producto || '');
                    cantidad.value = datos.cantidad || 1;
                    costo.value = Number(datos.precio_costo_unitario || 0).toFixed(2);
                    actualizarStock();
                }

                tbody.appendChild(fila);
                recalcular();
            };

            document.getElementById('agregar-linea').addEventListener('click', () => agregarLinea());
            tbody.addEventListener('input', recalcular);
            tbody.addEventListener('click', (evento) => {
                const boton = evento.target.closest('.quitar-linea');
                if (boton) {
                    boton.closest('tr').remove();
                    recalcular();
                }
            });
            document.getElementById('impuesto').addEventListener('input', recalcular);
            document.getElementById('monto_pagado').addEventListener('input', recalcular);

            if (Array.isArray(lineasPrevias) && lineasPrevias.length > 0) {
                lineasPrevias.forEach((linea) => agregarLinea(linea));
            } else {
                agregarLinea();
            }
        })();
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo cliente - Admin CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">
    @include('admin.partials.header')
    <main class="max-w-3xl mx-auto px-4 mt-6 pb-10">
        <a href="{{ route('admin.ordenes.create') }}" class="text-sm text-blue-700 hover:underline">&larr; Nueva orden de taller</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">Nuevo cliente</h1>
        <p class="text-sm text-gray-500 mt-1 mb-5">Registra al cliente y, si lo deseas, su primer equipo para continuar con la orden de taller.</p>

        @if($errors->any())
            <div role="alert" class="bg-red-50 border-l-4 border-red-600 text-red-800 p-3 rounded mb-4">
                <p class="font-semibold">Hay {{ $errors->count() }} dato(s) que debes corregir.</p>
                <ul class="list-disc ml-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('admin.clientes.store') }}" method="POST" class="bg-white rounded border border-gray-200 shadow-sm p-5 sm:p-7 space-y-6">
            @csrf

            <section>
                <h2 class="flex items-center gap-2 font-semibold text-gray-800 mb-3"><x-icon name="users" class="text-blue-600" /> Datos del cliente</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="nombre" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="user" class="text-blue-600" /> <span>Nombre completo <span class="text-red-700">*</span></span></label>
                        <input id="nombre" name="nombre" value="{{ old('nombre') }}" maxlength="100" required autocomplete="name" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                        @error('nombre')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="dni_ruc" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="id-card" class="text-blue-600" /> <span>DNI / RUC</span></label>
                        <input id="dni_ruc" name="dni_ruc" value="{{ old('dni_ruc') }}" maxlength="20" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                        @error('dni_ruc')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="telefono" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="phone" class="text-blue-600" /> <span>Teléfono <span class="text-red-700">*</span></span></label>
                        <input id="telefono" name="telefono" value="{{ old('telefono') }}" maxlength="20" required autocomplete="tel" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                        @error('telefono')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="mail" class="text-blue-600" /> <span>Correo electrónico</span></label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="100" autocomplete="email" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                        @error('email')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="direccion" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="map-pin" class="text-blue-600" /> <span>Dirección</span></label>
                        <input id="direccion" name="direccion" value="{{ old('direccion') }}" maxlength="150" autocomplete="street-address" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                        @error('direccion')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <section class="border-t pt-5">
                <h2 class="flex items-center gap-2 font-semibold text-gray-800"><x-icon name="laptop" class="text-blue-600" /> <span>Primer equipo <span class="text-gray-500 font-normal">(opcional)</span></span></h2>
                <p class="text-xs text-gray-500 mt-1 mb-3">Sin un equipo registrado no se puede crear la orden de taller. Puedes agregarlo ahora o dejarlo para después.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="tipo_equipo" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="printer" class="text-blue-600" /> <span>Tipo de equipo</span></label>
                        <input id="tipo_equipo" name="tipo_equipo" value="{{ old('tipo_equipo') }}" maxlength="30" list="tipos-equipo" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                        <datalist id="tipos-equipo">
                            <option value="Impresora"></option>
                            <option value="Laptop"></option>
                            <option value="Computadora"></option>
                            <option value="Monitor"></option>
                            <option value="Otro"></option>
                        </datalist>
                        @error('tipo_equipo')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="numero_serie" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="hash" class="text-blue-600" /> <span>Número de serie</span></label>
                        <input id="numero_serie" name="numero_serie" value="{{ old('numero_serie') }}" maxlength="50" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                        @error('numero_serie')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="marca" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="tag" class="text-blue-600" /> <span>Marca</span></label>
                        <input id="marca" name="marca" value="{{ old('marca') }}" maxlength="50" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                        @error('marca')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="modelo" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="settings" class="text-blue-600" /> <span>Modelo</span></label>
                        <input id="modelo" name="modelo" value="{{ old('modelo') }}" maxlength="50" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                        @error('modelo')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="contador_paginas" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="barcode" class="text-blue-600" /> <span>Contador de páginas</span></label>
                        <input id="contador_paginas" type="number" name="contador_paginas" min="0" max="100000000" value="{{ old('contador_paginas') }}" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                        @error('contador_paginas')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="observaciones" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="message" class="text-blue-600" /> <span>Observaciones del equipo</span></label>
                        <textarea id="observaciones" name="observaciones" rows="3" maxlength="2000" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">{{ old('observaciones') }}</textarea>
                        @error('observaciones')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <div class="flex justify-end gap-3 border-t pt-4">
                <a href="{{ route('admin.ordenes.create') }}" class="px-4 py-2 text-sm text-gray-700 hover:underline">Cancelar</a>
                <button type="submit" class="inline-flex items-center gap-2 bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-800"><x-icon name="check" /> Guardar cliente</button>
            </div>
        </form>
    </main>
</body>
</html>

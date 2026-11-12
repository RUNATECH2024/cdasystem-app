<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar cliente - Admin CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">
    @include('admin.partials.header')
    <main class="max-w-3xl mx-auto px-4 mt-6 pb-10">
        <a href="{{ route('admin.clientes.show', $cliente) }}" class="text-sm text-blue-700 hover:underline">&larr; Volver al cliente</a>
        <h1 class="flex items-center gap-2 text-2xl font-bold text-gray-800 mt-2 mb-5"><x-icon name="settings" class="h-6 w-6 text-blue-600" /> Editar cliente</h1>

        @if($errors->any())
            <div role="alert" class="bg-red-50 border-l-4 border-red-600 text-red-800 p-3 rounded mb-4">
                <p class="font-semibold">Hay {{ $errors->count() }} dato(s) que debes corregir.</p>
                <ul class="list-disc ml-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('admin.clientes.update', $cliente) }}" method="POST" class="bg-white rounded border border-gray-200 shadow-sm p-5 sm:p-7 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label for="nombre" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="user" class="text-blue-600" /> <span>Nombre completo <span class="text-red-700">*</span></span></label>
                    <input id="nombre" name="nombre" value="{{ old('nombre', $cliente->nombre) }}" maxlength="100" required autocomplete="name" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                    @error('nombre')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="dni_ruc" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="id-card" class="text-blue-600" /> <span>DNI / RUC</span></label>
                    <input id="dni_ruc" name="dni_ruc" value="{{ old('dni_ruc', $cliente->dni_ruc) }}" maxlength="20" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                    @error('dni_ruc')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="telefono" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="phone" class="text-blue-600" /> <span>Teléfono <span class="text-red-700">*</span></span></label>
                    <input id="telefono" name="telefono" value="{{ old('telefono', $cliente->telefono) }}" maxlength="20" required autocomplete="tel" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                    @error('telefono')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="mail" class="text-blue-600" /> <span>Correo electrónico</span></label>
                    <input id="email" type="email" name="email" value="{{ old('email', $cliente->email) }}" maxlength="100" autocomplete="email" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                    @error('email')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="direccion" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="map-pin" class="text-blue-600" /> <span>Dirección</span></label>
                    <input id="direccion" name="direccion" value="{{ old('direccion', $cliente->direccion) }}" maxlength="150" autocomplete="street-address" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                    @error('direccion')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="flex items-center gap-3 bg-gray-50 border rounded px-4 py-3 cursor-pointer">
                        <input type="hidden" name="activo" value="0">
                        <input type="checkbox" name="activo" value="1" class="h-4 w-4" @checked(old('activo', $cliente->activo))>
                        <span class="flex items-center gap-2 text-sm text-gray-700"><x-icon name="check" class="text-blue-600" /> Cliente activo</span>
                    </label>
                    @error('activo')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            @if($cliente->equipos->isNotEmpty())
                <div class="border-t pt-4">
                    <h2 class="flex items-center gap-2 font-semibold text-gray-800 mb-2"><x-icon name="laptop" class="text-blue-600" /> Equipos registrados</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($cliente->equipos as $equipo)
                            <span class="rounded border border-gray-200 bg-gray-50 px-3 py-1 text-xs text-gray-600">{{ $equipo->tipo_equipo }} · {{ $equipo->numero_serie ?: 'Sin serie' }}</span>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Los equipos se consultan en el detalle del cliente y se crean junto con una nueva orden.</p>
                </div>
            @endif

            <div class="flex justify-end gap-3 border-t pt-4">
                <a href="{{ route('admin.clientes.show', $cliente) }}" class="px-4 py-2 text-sm text-gray-700 hover:underline">Cancelar</a>
                <button type="submit" class="inline-flex items-center gap-2 bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-800"><x-icon name="check" /> Guardar cambios</button>
            </div>
        </form>
    </main>
</body>
</html>

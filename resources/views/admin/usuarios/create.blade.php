<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear usuario - Admin CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">
    @include('admin.partials.header')
    <main class="max-w-3xl mx-auto px-4 mt-6 pb-10">
        <a href="{{ route('admin.usuarios.index') }}" class="text-sm text-blue-700 hover:underline">← Usuarios</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2 mb-5">Crear usuario</h1>

        @if($errors->any())
            <div class="bg-red-100 text-red-800 p-3 rounded mb-4"><p class="font-semibold">Revisa los datos ingresados.</p><ul class="list-disc ml-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form action="{{ route('admin.usuarios.store') }}" method="POST" class="bg-white rounded shadow p-5 space-y-4">
            @csrf
            <div>
                <label for="nombre" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="user" class="text-blue-600" /> <span>Nombre completo</span></label>
                <input id="nombre" name="nombre" value="{{ old('nombre') }}" maxlength="100" required autocomplete="name" class="w-full border rounded px-3 py-2">
                @error('nombre')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="usuario" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="id-card" class="text-blue-600" /> <span>Nombre de usuario</span></label>
                <input id="usuario" name="usuario" value="{{ old('usuario') }}" maxlength="50" required autocomplete="username" class="w-full border rounded px-3 py-2">
                @error('usuario')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="rol" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="shield" class="text-blue-600" /> <span>Rol</span></label>
                <select id="rol" name="rol" required class="w-full border rounded px-3 py-2">
                    @foreach($roles as $rol)
                        <option value="{{ $rol }}" @selected(old('rol', 'Vendedor') === $rol)>{{ $rol }}</option>
                    @endforeach
                </select>
                @error('rol')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="lock" class="text-blue-600" /> <span>Contraseña</span></label>
                <input id="password" type="password" name="password" required minlength="12" autocomplete="new-password" class="w-full border rounded px-3 py-2">
                @error('password')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="lock" class="text-blue-600" /> <span>Confirmar contraseña</span></label>
                <input id="password_confirmation" type="password" name="password_confirmation" required minlength="12" autocomplete="new-password" class="w-full border rounded px-3 py-2">
            </div>
            <div class="flex justify-end gap-3 border-t pt-4">
                <a href="{{ route('admin.usuarios.index') }}" class="px-4 py-2 text-sm text-gray-700 hover:underline">Cancelar</a>
                <button type="submit" class="inline-flex items-center gap-2 bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-800"><x-icon name="check" /> Guardar usuario</button>
            </div>
        </form>
    </main>
</body>
</html>
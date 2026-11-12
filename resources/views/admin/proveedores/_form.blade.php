@php($proveedor = $proveedor ?? null)

@if($errors->any())
    <div role="alert" class="bg-red-50 border-l-4 border-red-600 text-red-800 p-3 rounded mb-4">
        <p class="font-semibold">Hay {{ $errors->count() }} dato(s) que debes corregir.</p>
        <ul class="list-disc ml-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ $accion }}" method="POST" class="bg-white rounded border border-gray-200 shadow-sm p-5 sm:p-7 space-y-5">
    @csrf
    @if($metodo !== 'POST')
        @method($metodo)
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label for="nombre" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="truck" class="text-blue-600" /> <span>Nombre / razón social <span class="text-red-700">*</span></span></label>
            <input id="nombre" name="nombre" value="{{ old('nombre', $proveedor?->nombre) }}" maxlength="100" required class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
            @error('nombre')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="ruc" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="id-card" class="text-blue-600" /> <span>RUC</span></label>
            <input id="ruc" name="ruc" value="{{ old('ruc', $proveedor?->ruc) }}" maxlength="20" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
            @error('ruc')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="telefono" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="phone" class="text-blue-600" /> <span>Teléfono</span></label>
            <input id="telefono" name="telefono" value="{{ old('telefono', $proveedor?->telefono) }}" maxlength="20" autocomplete="tel" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
            @error('telefono')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="email" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="mail" class="text-blue-600" /> <span>Correo electrónico</span></label>
            <input id="email" type="email" name="email" value="{{ old('email', $proveedor?->email) }}" maxlength="100" autocomplete="email" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
            @error('email')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="direccion" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="map-pin" class="text-blue-600" /> <span>Dirección</span></label>
            <input id="direccion" name="direccion" value="{{ old('direccion', $proveedor?->direccion) }}" maxlength="150" autocomplete="street-address" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
            @error('direccion')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="flex items-center gap-3 bg-gray-50 border rounded px-4 py-3 cursor-pointer">
                <input type="hidden" name="activo" value="0">
                <input type="checkbox" name="activo" value="1" class="h-4 w-4" @checked(old('activo', $proveedor?->activo ?? true))>
                <span class="flex items-center gap-2 text-sm text-gray-700"><x-icon name="check" class="text-blue-600" /> Proveedor activo</span>
            </label>
            @error('activo')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <p class="text-xs text-gray-500">El saldo pendiente se actualiza automáticamente con las compras y abonos registrados.</p>

    <div class="flex justify-end gap-3 border-t pt-4">
        <a href="{{ $proveedor ? route('admin.proveedores.show', $proveedor) : route('admin.proveedores.index') }}" class="px-4 py-2 text-sm text-gray-700 hover:underline">Cancelar</a>
        <button type="submit" class="inline-flex items-center gap-2 bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-800"><x-icon name="check" /> {{ $textoBoton }}</button>
    </div>
</form>

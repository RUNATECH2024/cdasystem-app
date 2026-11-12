<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar orden #{{ $orden->id_orden }} - Admin CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">
    @include('admin.partials.header')
    <main class="max-w-4xl mx-auto px-4 mt-6 pb-10">
        <a href="{{ route('admin.ordenes.show', $orden) }}" class="text-sm text-blue-700 hover:underline">← Volver a la orden #{{ $orden->id_orden }}</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2 mb-5">Editar orden de taller</h1>
        @if($errors->any())
            <div class="bg-red-100 text-red-800 p-3 rounded mb-4"><p class="font-semibold">Revisa los datos ingresados.</p><ul class="list-disc ml-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form action="{{ route('admin.ordenes.update', $orden) }}" method="POST" class="bg-white rounded shadow p-5 space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="estado" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="settings" class="text-blue-600" /> <span>Estado</span></label>
                    <select id="estado" name="estado" required class="w-full border rounded px-3 py-2">
                        @foreach(['Pendiente', 'En Revisión', 'Esperando Repuesto', 'Reparado', 'Entregado', 'Cancelado'] as $opcion)
                            <option value="{{ $opcion }}" @selected(old('estado', $orden->estado) === $opcion)>{{ $opcion }}</option>
                        @endforeach
                    </select>
                    @error('estado')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="id_tecnico" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="wrench" class="text-blue-600" /> <span>Técnico asignado</span></label>
                    <select id="id_tecnico" name="id_tecnico" class="w-full border rounded px-3 py-2">
                        <option value="">Sin asignar</option>
                        @foreach($tecnicos as $tecnico)
                            <option value="{{ $tecnico->id_usuario }}" @selected((string) old('id_tecnico', $orden->id_tecnico) === (string) $tecnico->id_usuario)>{{ $tecnico->nombre }}</option>
                        @endforeach
                    </select>
                    @error('id_tecnico')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="fecha_entrega" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="calendar" class="text-blue-600" /> <span>Fecha de entrega</span></label>
                    <input id="fecha_entrega" type="datetime-local" name="fecha_entrega" value="{{ old('fecha_entrega', $orden->fecha_entrega?->format('Y-m-d\TH:i')) }}" class="w-full border rounded px-3 py-2">
                    @error('fecha_entrega')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="contador_salida" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="barcode" class="text-blue-600" /> <span>Contador de salida</span></label>
                    <input id="contador_salida" type="number" min="0" name="contador_salida" value="{{ old('contador_salida', $orden->contador_salida) }}" class="w-full border rounded px-3 py-2">
                    @error('contador_salida')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label for="diagnostico" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="message" class="text-blue-600" /> <span>Diagnóstico</span></label>
                    <textarea id="diagnostico" name="diagnostico" rows="5" class="w-full border rounded px-3 py-2">{{ old('diagnostico', $orden->diagnostico) }}</textarea>
                    @error('diagnostico')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <p class="text-xs text-gray-500">Los importes y repuestos se muestran como registro; no se modifican desde este formulario para conservar el historial de inventario.</p>
            <div class="flex justify-end gap-3 border-t pt-4">
                <a href="{{ route('admin.ordenes.show', $orden) }}" class="px-4 py-2 text-sm text-gray-700 hover:underline">Cancelar</a>
                <button type="submit" class="inline-flex items-center gap-2 bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-800"><x-icon name="check" /> Guardar cambios</button>
            </div>
        </form>
    </main>
</body>
</html>
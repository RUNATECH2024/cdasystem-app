<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva orden de taller - Admin CDA System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">
    @include('admin.partials.header')
    <main class="max-w-4xl mx-auto px-4 mt-6 pb-10">
        <a href="{{ route('admin.ordenes.index') }}" class="text-sm text-blue-700 hover:underline">← Órdenes de taller</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">Nueva orden de taller</h1>
        <p class="text-sm text-gray-500 mt-1 mb-5">Estado inicial: Pendiente</p>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 text-red-800 p-3 rounded mb-4">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div role="alert" class="bg-red-50 border-l-4 border-red-600 text-red-800 p-3 rounded mb-4">
                <p class="font-semibold">Hay {{ $errors->count() }} dato(s) que debes corregir.</p>
                <ul class="list-disc ml-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('admin.ordenes.store') }}" method="POST" class="bg-white rounded border border-gray-200 shadow-sm p-5 sm:p-7 space-y-5">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <label for="id_cliente" class="flex items-center gap-2 text-sm font-medium"><x-icon name="user" class="text-blue-600" /> <span>Cliente <span class="text-red-700">*</span></span></label>
                        <a href="{{ route('admin.clientes.create') }}" class="inline-flex items-center gap-1 text-sm text-blue-700 hover:underline"><x-icon name="plus" class="h-3.5 w-3.5" /> Nuevo cliente</a>
                    </div>
                    <select id="id_cliente" name="id_cliente" required class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                        <option value="">Selecciona un cliente</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id_cliente }}" @selected((string) old('id_cliente', request('cliente')) === (string) $cliente->id_cliente)>
                                {{ $cliente->nombre }}{{ $cliente->dni_ruc ? ' · '.$cliente->dni_ruc : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_cliente')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="id_equipo" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="laptop" class="text-blue-600" /> <span>Equipo <span class="text-red-700">*</span></span></label>
                    <select id="id_equipo" name="id_equipo" required disabled class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none disabled:bg-gray-100">
                        <option value="">Primero selecciona un cliente</option>
                        @foreach($equipos as $equipo)
                            <option value="{{ $equipo->id_equipo }}" data-cliente="{{ $equipo->id_cliente }}" data-tipo="{{ $equipo->tipo_equipo }}" data-marca="{{ $equipo->marca }}" data-modelo="{{ $equipo->modelo }}" data-serie="{{ $equipo->numero_serie }}" data-contador="{{ $equipo->contador_paginas }}" data-observaciones="{{ $equipo->observaciones }}" @selected((string) old('id_equipo', request('equipo')) === (string) $equipo->id_equipo)>
                                #{{ $equipo->id_equipo }} · {{ $equipo->tipo_equipo }} · {{ trim(($equipo->marca ?? '').' '.($equipo->modelo ?? '')) ?: 'Sin marca/modelo' }} · Serie: {{ $equipo->numero_serie ?: '—' }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_equipo')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div id="equipo-resumen" aria-live="polite" class="hidden md:col-span-2 rounded border border-blue-200 bg-blue-50 p-4 text-sm text-gray-700">
                    <p id="equipo-resumen-titulo" class="font-semibold text-gray-900"></p>
                    <p id="equipo-resumen-serie" class="mt-1"></p>
                    <p id="equipo-resumen-contador"></p>
                    <p id="equipo-resumen-observaciones" class="text-gray-600"></p>
                </div>
                <div>
                    <label for="id_tecnico" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="wrench" class="text-blue-600" /> <span>Técnico asignado</span></label>
                    <select id="id_tecnico" name="id_tecnico" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                        <option value="">Sin asignar</option>
                        @foreach($tecnicos as $tecnico)
                            <option value="{{ $tecnico->id_usuario }}" @selected((string) old('id_tecnico') === (string) $tecnico->id_usuario)>{{ $tecnico->nombre }}</option>
                        @endforeach
                    </select>
                    @error('id_tecnico')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="contador_ingreso" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="barcode" class="text-blue-600" /> <span>Contador de ingreso</span></label>
                    <input id="contador_ingreso" type="number" min="0" name="contador_ingreso" value="{{ old('contador_ingreso') }}" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                    @error('contador_ingreso')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label for="falla_reportada" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="alert" class="text-blue-600" /> <span>Falla reportada <span class="text-red-700">*</span></span></label>
                    <textarea id="falla_reportada" name="falla_reportada" required rows="4" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">{{ old('falla_reportada') }}</textarea>
                    @error('falla_reportada')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label for="diagnostico" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="message" class="text-blue-600" /> <span>Diagnóstico inicial <span class="text-gray-500 font-normal">(opcional)</span></span></label>
                    <textarea id="diagnostico" name="diagnostico" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">{{ old('diagnostico') }}</textarea>
                    @error('diagnostico')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="total_mano_obra" class="flex items-center gap-2 text-sm font-medium mb-1"><x-icon name="currency" class="text-blue-600" /> <span>Mano de obra ($) <span class="text-red-700">*</span></span></label>
                    <input id="total_mano_obra" type="number" name="total_mano_obra" min="0" max="99999999.99" step="0.01" inputmode="decimal" value="{{ old('total_mano_obra', '0.00') }}" required class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                    @error('total_mano_obra')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <p class="text-xs text-gray-500">La orden se registra con estado Pendiente. Los repuestos se agregan después desde el flujo de taller para actualizar inventario.</p>
            <div class="flex justify-end gap-3 border-t pt-4">
                <a href="{{ route('admin.ordenes.index') }}" class="px-4 py-2 text-sm text-gray-700 hover:underline">Cancelar</a>
                <button type="submit" class="inline-flex items-center gap-2 bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-800"><x-icon name="check" /> Crear orden</button>
            </div>
        </form>
    </main>
    <script>
        const clienteSelect = document.getElementById('id_cliente');
        const equipoSelect = document.getElementById('id_equipo');
        const equipoOptions = [...equipoSelect.querySelectorAll('option[data-cliente]')];
        const equipoAnterior = @json(old('id_equipo', request('equipo')));
        const equipoResumen = document.getElementById('equipo-resumen');
        const resumenTitulo = document.getElementById('equipo-resumen-titulo');
        const resumenSerie = document.getElementById('equipo-resumen-serie');
        const resumenContador = document.getElementById('equipo-resumen-contador');
        const resumenObservaciones = document.getElementById('equipo-resumen-observaciones');

        function filtrarEquipos(restaurarSeleccion = false) {
            const clienteId = clienteSelect.value;
            equipoSelect.value = '';
            equipoOptions.forEach((option) => {
                option.hidden = option.dataset.cliente !== clienteId;
                option.disabled = option.hidden;
            });
            equipoSelect.disabled = clienteId === '' || !equipoOptions.some((option) => option.dataset.cliente === clienteId);
            equipoSelect.options[0].textContent = clienteId
                ? (equipoSelect.disabled ? 'Este cliente no tiene equipos registrados' : 'Selecciona un equipo')
                : 'Primero selecciona un cliente';

            if (restaurarSeleccion && equipoAnterior) {
                const opcion = equipoOptions.find((option) => option.value === String(equipoAnterior) && option.dataset.cliente === clienteId);
                if (opcion) equipoSelect.value = opcion.value;
            }

            actualizarResumenEquipo();
        }

        function actualizarResumenEquipo() {
            const opcion = equipoSelect.selectedOptions[0];
            const seleccionValida = opcion?.dataset.cliente === clienteSelect.value;
            equipoResumen.classList.toggle('hidden', !seleccionValida);

            if (!seleccionValida) return;

            resumenTitulo.textContent = [opcion.dataset.tipo, opcion.dataset.marca, opcion.dataset.modelo].filter(Boolean).join(' · ');
            resumenSerie.textContent = `Serie: ${opcion.dataset.serie || '—'}`;
            resumenContador.textContent = `Contador registrado: ${opcion.dataset.contador || '—'}`;
            resumenObservaciones.textContent = opcion.dataset.observaciones ? `Observaciones: ${opcion.dataset.observaciones}` : '';
        }

        clienteSelect.addEventListener('change', () => filtrarEquipos());
        equipoSelect.addEventListener('change', actualizarResumenEquipo);
        filtrarEquipos(true);
    </script>
</body>
</html>
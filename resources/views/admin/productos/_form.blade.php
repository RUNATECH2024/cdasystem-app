@php
    $esEdicion = $producto !== null;

    $nombresViejos = old('atributos_nombres');
    if (is_array($nombresViejos)) {
        $valoresViejos = old('atributos_valores', []);
        $filasAtributos = [];
        foreach ($nombresViejos as $i => $nombre) {
            $filasAtributos[] = ['nombre' => $nombre, 'valor' => $valoresViejos[$i] ?? ''];
        }
    } else {
        $filasAtributos = $atributosLibres;
        if (count($filasAtributos) === 0) {
            $filasAtributos = [['nombre' => '', 'valor' => '']];
        }
    }

    $condicionActual = old('condicion', $producto?->condicion ?? 'Nuevo');
@endphp

<form action="{{ $accion }}" method="POST" enctype="multipart/form-data" class="bg-white p-5 sm:p-8 rounded border border-gray-200 shadow-sm">
    @csrf
    @if($metodo !== 'POST')
        @method($metodo)
    @endif

    @if($errors->any())
        <div role="alert" class="bg-red-50 border-l-4 border-red-600 text-red-800 p-4 mb-6 rounded">
            <p class="font-semibold mb-1">Hay {{ $errors->count() }} dato(s) que debes corregir</p>
            <ul class="list-disc pl-5 text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-5">
        <h3 class="flex items-center gap-2 text-lg font-bold text-gray-800"><x-icon name="sparkles" class="h-5 w-5 text-blue-600" /> Información básica</h3>
        <p class="text-sm text-gray-500 mt-1">Los campos marcados con * son obligatorios.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="md:col-span-2">
            <label for="producto-nombre" class="flex items-center gap-2 text-gray-700 text-sm font-bold mb-2"><x-icon name="box" class="text-blue-600" /> <span>Nombre del producto *</span></label>
            <input id="producto-nombre" type="text" name="nombre" required maxlength="150" autocomplete="off" placeholder="Ej: CPU AMD Ryzen 3" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none" value="{{ old('nombre', $producto?->nombre ?? '') }}" @if($errors->has('nombre')) aria-invalid="true" aria-describedby="error-nombre" @endif>
            @error('nombre')<p id="error-nombre" class="text-sm text-red-700 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="producto-sku" class="flex items-center gap-2 text-gray-700 text-sm font-bold mb-2"><x-icon name="barcode" class="text-blue-600" /> <span>SKU (código interno)</span></label>
            <input id="producto-sku" type="text" name="sku" maxlength="50" autocomplete="off" placeholder="Ej: CPU-AMD-01" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none" value="{{ old('sku', $producto?->sku ?? '') }}">
        </div>
        <div>
            <label for="producto-codigo-barras" class="flex items-center gap-2 text-gray-700 text-sm font-bold mb-2"><x-icon name="barcode" class="text-blue-600" /> <span>Código</span></label>
            <div class="flex gap-2">
                <input id="producto-codigo-barras" type="text" name="codigo_barras" maxlength="50" autocomplete="off" placeholder="Ej: REPEPS1529633" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none" value="{{ old('codigo_barras', $producto?->codigo_barras ?? '') }}">
                <button type="button" id="generar-codigo" class="shrink-0 bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 font-medium text-sm transition">Generar</button>
            </div>
            <p class="text-xs text-gray-500 mt-1">Se genera con REP + las 3 primeras letras de la marca + SKU (Ej: REPEPS1529633).</p>
        </div>

        <div>
            <label for="producto-marca" class="flex items-center gap-2 text-gray-700 text-sm font-bold mb-2"><x-icon name="tag" class="text-blue-600" /> <span>Marca *</span></label>
            <input id="producto-marca" type="text" name="marca" required maxlength="80" list="marcas-list" placeholder="Ej: HP, Lenovo, AMD" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none" value="{{ old('marca', $producto?->marca ?? '') }}">
            <datalist id="marcas-list">
                <option value="HP"><option value="Lenovo"><option value="Asus"><option value="Acer">
                <option value="Dell"><option value="Epson"><option value="Apple"><option value="Samsung">
                <option value="AMD"><option value="Intel"><option value="MSI"><option value="Gigabyte">
            </datalist>
        </div>
        <div>
            <label for="select-categoria" class="flex items-center gap-2 text-gray-700 text-sm font-bold mb-2"><x-icon name="layers" class="text-blue-600" /> <span>Categoría *</span></label>
            <select id="select-categoria" name="id_categoria" required class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                <option value="">Seleccione una...</option>
                @foreach($categorias as $cat)
                    <option value="{{ $cat->id_categoria }}" @selected((string) old('id_categoria', $categoriaSeleccionada) === (string) $cat->id_categoria)>{{ $cat->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="select-subcategoria" class="flex items-center gap-2 text-gray-700 text-sm font-bold mb-2"><x-icon name="layers" class="text-blue-600" /> <span>Subcategoría *</span></label>
            <select name="id_subcategoria" id="select-subcategoria" required class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                <option value="">Primero seleccione una categoría</option>
            </select>
        </div>
        <div>
            <label for="producto-condicion" class="flex items-center gap-2 text-gray-700 text-sm font-bold mb-2"><x-icon name="settings" class="text-blue-600" /> <span>Condición *</span></label>
            <select id="producto-condicion" name="condicion" required class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                <option value="Nuevo" @selected($condicionActual === 'Nuevo')>Nuevo (Sellado)</option>
                <option value="Reacondicionado" @selected($condicionActual === 'Reacondicionado')>Reacondicionado</option>
                <option value="Usado" @selected($condicionActual === 'Usado')>Usado</option>
            </select>
        </div>
    </div>

    <h3 class="flex items-center gap-2 text-lg font-bold text-gray-800 border-b pb-2 mb-4"><x-icon name="currency" class="h-5 w-5 text-blue-600" /> Precios e inventario</h3>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-4">
        <div>
            <label for="precio-compra" class="flex items-center gap-2 text-gray-700 text-sm font-bold mb-2"><x-icon name="cash" class="text-blue-600" /> <span>Precio de compra ($) *</span></label>
            <input id="precio-compra" type="number" step="0.01" min="0" max="99999999.99" inputmode="decimal" name="precio_compra" required class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none" value="{{ old('precio_compra', $producto?->precio_compra ?? '') }}">
            @error('precio_compra')<p class="text-sm text-red-700 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="precio-venta" class="flex items-center gap-2 text-gray-700 text-sm font-bold mb-2"><x-icon name="currency" class="text-blue-600" /> <span>Precio de venta ($) *</span></label>
            <input id="precio-venta" type="number" step="0.01" min="0" max="99999999.99" inputmode="decimal" name="precio_venta" required class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none" value="{{ old('precio_venta', $producto?->precio_venta ?? '') }}">
            @error('precio_venta')<p class="text-sm text-red-700 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="stock-actual" class="flex items-center gap-2 text-gray-700 text-sm font-bold mb-2"><x-icon name="box" class="text-blue-600" /> <span>Stock actual *</span></label>
            <input id="stock-actual" type="number" min="0" step="1" inputmode="numeric" name="stock_actual" required class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none" value="{{ old('stock_actual', $producto?->stock_actual ?? 0) }}">
        </div>
        <div>
            <label for="stock-minimo" class="flex items-center gap-2 text-gray-700 text-sm font-bold mb-2"><x-icon name="alert" class="text-blue-600" /> <span>Stock mínimo (alerta)</span></label>
            <input id="stock-minimo" type="number" min="0" step="1" inputmode="numeric" name="stock_minimo" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none" value="{{ old('stock_minimo', $producto?->stock_minimo ?? 5) }}">
        </div>
    </div>

    <div id="stock-alerta" class="hidden mb-8 rounded border px-4 py-3 text-sm font-medium"></div>

    <div id="seccion-campos-categoria" class="hidden">
        <h3 class="flex items-center gap-2 text-lg font-bold text-gray-700 border-b pb-2 mb-4"><x-icon name="layers" class="h-5 w-5 text-blue-600" /> Detalles de la categoría</h3>
        <div id="campos-categoria" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8"></div>
    </div>

    <div id="seccion-detalles">
        <h3 class="flex items-center gap-2 text-lg font-bold text-gray-700 border-b pb-2 mb-4"><x-icon name="shield" class="h-5 w-5 text-blue-600" /> Garantía y Detalles</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div id="campo-garantia">
                <label class="flex items-center gap-2 text-gray-700 text-sm font-bold mb-2"><x-icon name="shield" class="text-blue-600" /> <span>Garantía (Meses)</span></label>
                <input type="number" name="garantia_meses" id="input-garantia" min="0" step="1" inputmode="numeric" placeholder="Ej: 12" class="w-full border rounded px-3 py-2 focus:border-blue-500 focus:outline-none" value="{{ old('garantia_meses', $producto?->garantia_meses ?? 0) }}">
            </div>
            <div id="campo-vencimiento" class="hidden">
                <label class="flex items-center gap-2 text-gray-700 text-sm font-bold mb-2"><x-icon name="calendar" class="text-blue-600" /> <span>Fecha de Vencimiento (Consumibles)</span></label>
                <input type="date" name="fecha_vencimiento" id="input-vencimiento" disabled class="w-full border rounded px-3 py-2 focus:border-blue-500 focus:outline-none" value="{{ old('fecha_vencimiento', $producto?->fecha_vencimiento?->format('Y-m-d')) }}">
                <p class="text-xs text-gray-500 mt-1">Solo para tintas, tóneres y consumibles.</p>
            </div>
        </div>
    </div>

    <h3 class="flex items-center gap-2 text-lg font-bold text-gray-700 border-b pb-2 mb-4"><x-icon name="eye" class="h-5 w-5 text-blue-600" /> Publicación</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <label class="flex items-center gap-3 bg-gray-50 border rounded px-4 py-3 cursor-pointer">
            <input type="hidden" name="activo" value="0">
            <input type="checkbox" name="activo" value="1" class="h-4 w-4" @checked(old('activo', $producto?->activo ?? true))>
            <span class="flex items-center gap-2 text-sm text-gray-700"><x-icon name="eye" class="text-blue-600" /> Producto activo (visible en la tienda)</span>
        </label>
        <label class="flex items-center gap-3 bg-gray-50 border rounded px-4 py-3 cursor-pointer">
            <input type="hidden" name="destacado" value="0">
            <input type="checkbox" name="destacado" value="1" class="h-4 w-4" @checked(old('destacado', $producto?->destacado ?? false))>
            <span class="flex items-center gap-2 text-sm text-gray-700"><x-icon name="sparkles" class="text-blue-600" /> Destacar en la tienda</span>
        </label>
        <label class="flex items-center gap-3 bg-gray-50 border rounded px-4 py-3 cursor-pointer">
            <input type="hidden" name="es_repuesto" value="0">
            <input type="checkbox" name="es_repuesto" value="1" class="h-4 w-4" @checked(old('es_repuesto', $producto?->es_repuesto ?? false))>
            <span class="flex items-center gap-2 text-sm text-gray-700"><x-icon name="wrench" class="text-blue-600" /> Es repuesto</span>
        </label>
    </div>

    <h3 class="flex items-center gap-2 text-lg font-bold text-gray-700 border-b pb-2 mb-4"><x-icon name="image" class="h-5 w-5 text-blue-600" /> Descripción e Imagen</h3>
    <div class="mb-4">
        <label class="flex items-center gap-2 text-gray-700 text-sm font-bold mb-2"><x-icon name="document" class="text-blue-600" /> <span>Descripción Corta</span></label>
        <input type="text" name="descripcion_corta" placeholder="Ej: PC Gamer completa con teclado y mouse" class="w-full border rounded px-3 py-2 focus:border-blue-500 focus:outline-none" value="{{ old('descripcion_corta', $producto?->descripcion_corta ?? '') }}">
    </div>
    <div class="mb-4">
        <label class="flex items-center gap-2 text-gray-700 text-sm font-bold mb-2"><x-icon name="document" class="text-blue-600" /> <span>Descripción Larga</span></label>
        <textarea name="descripcion_larga" rows="2" class="w-full border rounded px-3 py-2 focus:border-blue-500 focus:outline-none">{{ old('descripcion_larga', $producto?->descripcion_larga ?? '') }}</textarea>
    </div>

    @if($esEdicion && $producto?->imagen_principal)
        <div class="mb-4 flex items-center gap-4">
            <img src="{{ asset('productos/'.$producto->imagen_principal) }}" alt="{{ $producto->nombre }}" class="h-20 w-20 object-cover rounded border">
            <label class="flex items-center gap-2 text-sm text-red-600 cursor-pointer">
                <input type="checkbox" name="eliminar_imagen" value="1" class="h-4 w-4">
                <x-icon name="alert" /> Eliminar imagen actual
            </label>
        </div>
    @endif

    <div class="mb-8">
        <label class="flex items-center gap-2 text-gray-700 text-sm font-bold mb-2"><x-icon name="image" class="text-blue-600" /> <span>{{ $esEdicion ? 'Reemplazar imagen principal (JPG, PNG o WEBP)' : 'Imagen principal (JPG, PNG o WEBP)' }}</span></label>
        <input id="input-imagen-producto" type="file" name="imagen" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-50 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
        <div id="preview-imagen-producto" class="hidden mt-4">
            <p class="text-sm text-gray-600 mb-2">Vista previa:</p>
            <img id="imagen-preview" src="" alt="Vista previa del producto" class="h-32 w-32 object-cover rounded border border-gray-200 bg-gray-50">
        </div>
    </div>

    @if($esEdicion && count($producto->imagenes_adicionales ?? []))
        <div class="mb-4">
            <p class="text-sm font-bold text-gray-700 mb-2">Imágenes adicionales actuales</p>
            <div class="flex flex-wrap gap-4">
                @foreach($producto->imagenes_adicionales as $imagenAdicional)
                    <label class="flex items-center gap-2 border rounded p-2 text-sm text-gray-700">
                        <img src="{{ asset('productos/'.$imagenAdicional) }}" alt="Imagen adicional de {{ $producto->nombre }}" class="h-16 w-16 object-cover rounded">
                        <input type="checkbox" name="eliminar_imagenes[]" value="{{ $imagenAdicional }}" class="h-4 w-4">
                        <span>Eliminar</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mb-8">
        <label for="input-imagenes-adicionales" class="flex items-center gap-2 text-gray-700 text-sm font-bold mb-2"><x-icon name="image" class="text-blue-600" /> <span>Galería de imágenes adicionales (opcional)</span></label>
        <input id="input-imagenes-adicionales" type="file" name="imagenes[]" multiple accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-50 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
        <p class="text-xs text-gray-500 mt-1">Puedes agregar hasta 10 imágenes JPG, PNG o WEBP, de máximo 2 MB cada una.</p>
    </div>

    <div id="seccion-atributos">
        <h3 id="titulo-atributos" class="flex items-center gap-2 text-lg font-bold text-gray-700 border-b pb-2 mb-4 mt-8"><x-icon name="list" class="h-5 w-5 text-blue-600" /> Características Técnicas</h3>
        <p id="descripcion-atributos" class="text-sm text-gray-500 mb-4">Agrega las especificaciones del producto.</p>

        <div id="atributos-container" class="space-y-3 mb-4">
            @foreach($filasAtributos as $fila)
                <div class="flex gap-4 items-center mt-3">
                    <input type="text" name="atributos_nombres[]" value="{{ $fila['nombre'] }}" placeholder="Ej: Característica" aria-label="Nombre de la característica" class="w-1/3 border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                    <input type="text" name="atributos_valores[]" value="{{ $fila['valor'] }}" placeholder="Ej: Especificación" aria-label="Valor de la característica" class="w-2/3 border border-gray-300 rounded px-3 py-2 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 focus:outline-none">
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900 font-bold px-2" title="Eliminar característica" aria-label="Eliminar característica">×</button>
                </div>
            @endforeach
        </div>

        <button type="button" onclick="agregarAtributo()" class="mb-8 bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 font-medium text-sm flex items-center gap-2 transition">
            <x-icon name="plus" />
            Añadir otra característica
        </button>
    </div>

    <div class="flex justify-end border-t pt-6">
        <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 font-bold shadow text-lg transition">
            <x-icon name="check" class="h-5 w-5" /> {{ $textoBoton }}
        </button>
    </div>
</form>

<script>
    const subcategoriasPorCategoria = @json($subcategoriasPorCategoria);
    const configPorCategoria = @json($configPorCategoria);
    const selectCategoria = document.getElementById('select-categoria');
    const selectSubcategoria = document.getElementById('select-subcategoria');
    const categoriaSeleccionada = @json(old('id_categoria', $categoriaSeleccionada));
    const subcategoriaSeleccionada = @json(old('id_subcategoria', $subcategoriaSeleccionada));

    const seccionDetalles = document.getElementById('seccion-detalles');
    const campoGarantia = document.getElementById('campo-garantia');
    const inputGarantia = document.getElementById('input-garantia');
    const campoVencimiento = document.getElementById('campo-vencimiento');
    const inputVencimiento = document.getElementById('input-vencimiento');
    const seccionAtributos = document.getElementById('seccion-atributos');
    const contenedorAtributos = document.getElementById('atributos-container');
    const valoresCategoria = @json(old('campos_categoria', $valoresCategoria));
    const seccionCamposCategoria = document.getElementById('seccion-campos-categoria');
    const camposCategoriaContenedor = document.getElementById('campos-categoria');
    const stockAlerta = document.getElementById('stock-alerta');
    const inputStockActual = document.querySelector('input[name="stock_actual"]');
    const inputStockMinimo = document.querySelector('input[name="stock_minimo"]');
    const inputCodigoProducto = document.getElementById('producto-codigo-barras');
    const botonGenerarCodigo = document.getElementById('generar-codigo');
    let categoriaRenderizada = null;

    const ejemplosGenerales = [{ nombre: 'Característica', valor: 'Especificación' }];
    const ejemplosConsumible = [
        { nombre: 'Rendimiento', valor: '2.500 páginas' },
        { nombre: 'Color', valor: 'Negro' },
        { nombre: 'Compatibilidad', valor: 'HP LaserJet' },
    ];
    let ejemplosAtributos = ejemplosGenerales;

    function cargarSubcategorias(categoriaId, seleccion = null) {
        selectSubcategoria.innerHTML = '';

        const opciones = subcategoriasPorCategoria[categoriaId] || [];

        if (opciones.length === 0) {
            selectSubcategoria.innerHTML = '<option value="">Primero seleccione una categoría</option>';
            actualizarFormulario();
            return;
        }

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Seleccione una...';
        selectSubcategoria.appendChild(placeholder);

        opciones.forEach(function (subcategoria) {
            const opcion = document.createElement('option');
            opcion.value = subcategoria.id;
            opcion.textContent = subcategoria.nombre;

            if (seleccion !== null && String(seleccion) === String(subcategoria.id)) {
                opcion.selected = true;
            }

            selectSubcategoria.appendChild(opcion);
        });

        actualizarFormulario();
    }

    function esConsumible() {
        const opcion = selectSubcategoria.options[selectSubcategoria.selectedIndex];
        const nombre = opcion ? opcion.textContent : '';

        return /TÓNER|TONER|TINTA|CONSUMIBLE|SUMINISTRO/i.test(nombre);
    }

    function actualizarFormulario() {
        const config = configPorCategoria[selectCategoria.value] || null;
        const consumible = esConsumible();

        const mostrarGarantia = consumible
            ? false
            : (config ? config.garantia !== false : true);

        campoGarantia.classList.toggle('hidden', !mostrarGarantia);
        inputGarantia.disabled = !mostrarGarantia;

        campoVencimiento.classList.toggle('hidden', !consumible);
        inputVencimiento.disabled = !consumible;

        seccionDetalles.classList.toggle('hidden', !mostrarGarantia && !consumible);

        const mostrarAtributos = config ? config.atributos !== false : true;
        seccionAtributos.classList.toggle('hidden', !mostrarAtributos);
        contenedorAtributos.querySelectorAll('input').forEach(function (input) {
            input.disabled = !mostrarAtributos;
        });

        if (consumible) {
            ejemplosAtributos = ejemplosConsumible;
        } else if (config && Array.isArray(config.ejemplos) && config.ejemplos.length > 0) {
            ejemplosAtributos = config.ejemplos;
        } else {
            ejemplosAtributos = ejemplosGenerales;
        }

        actualizarPlaceholdersAtributos();
        renderCamposCategoria(selectCategoria.value, config);
    }

    function renderCamposCategoria(categoriaId, config) {
        const clave = String(categoriaId || '');

        if (categoriaRenderizada === clave) {
            return;
        }

        categoriaRenderizada = clave;
        camposCategoriaContenedor.innerHTML = '';

        const campos = config && Array.isArray(config.campos) ? config.campos : [];

        if (campos.length === 0) {
            seccionCamposCategoria.classList.add('hidden');
            return;
        }

        campos.forEach(function (campo) {
            const contenedor = document.createElement('div');

            const label = document.createElement('label');
            label.className = 'block text-gray-700 text-sm font-bold mb-2';
            label.textContent = campo.label;
            contenedor.appendChild(label);

            let input;

            if (campo.tipo === 'select') {
                input = document.createElement('select');

                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = 'Seleccione...';
                input.appendChild(placeholder);

                (campo.options || []).forEach(function (opcion) {
                    const item = document.createElement('option');
                    item.value = opcion;
                    item.textContent = opcion;
                    input.appendChild(item);
                });
            } else {
                input = document.createElement('input');
                input.type = campo.tipo === 'number' ? 'number' : 'text';
            }

            input.name = 'campos_categoria[' + campo.name + ']';
            input.className = 'w-full border rounded px-3 py-2 focus:border-blue-500 focus:outline-none';

            const valor = valoresCategoria[campo.name];
            if (valor !== undefined && valor !== null) {
                input.value = valor;
            }

            contenedor.appendChild(input);
            camposCategoriaContenedor.appendChild(contenedor);
        });

        seccionCamposCategoria.classList.remove('hidden');
    }

    function actualizarPlaceholdersAtributos() {
        const nombres = contenedorAtributos.querySelectorAll('input[name="atributos_nombres[]"]');
        const valores = contenedorAtributos.querySelectorAll('input[name="atributos_valores[]"]');
        const ejemplo = ejemplosAtributos[0];

        if (nombres[0] && ejemplo) {
            nombres[0].placeholder = 'Ej: ' + ejemplo.nombre;
        }

        if (valores[0] && ejemplo) {
            valores[0].placeholder = 'Ej: ' + ejemplo.valor;
        }
    }

    function clampNonNegativeNumbers() {
        document.querySelectorAll('input[type="number"]').forEach(function (input) {
            input.addEventListener('input', function () {
                if (input.value === '') {
                    return;
                }

                const numericValue = Number(input.value);
                if (!Number.isNaN(numericValue) && numericValue < 0) {
                    input.value = 0;
                }
            });
        });
    }

    function actualizarIndicadorStock() {
        if (!stockAlerta || !inputStockActual || !inputStockMinimo) {
            return;
        }

        const stockActual = Number(inputStockActual.value || 0);
        const stockMinimo = Number(inputStockMinimo.value || 0);

        if (stockMinimo <= 0 && stockActual <= 0) {
            stockAlerta.className = 'hidden mb-8 rounded border px-4 py-3 text-sm font-medium';
            return;
        }

        stockAlerta.classList.remove('hidden');

        if (stockActual <= stockMinimo) {
            stockAlerta.textContent = '⚠️ Stock bajo: el inventario está al límite o por debajo del mínimo definido.';
            stockAlerta.className = 'mb-8 rounded border border-yellow-300 bg-yellow-50 px-4 py-3 text-sm font-medium text-yellow-800';
            return;
        }

        stockAlerta.textContent = '✅ Stock saludable: el inventario está por encima del mínimo configurado.';
        stockAlerta.className = 'mb-8 rounded border border-green-300 bg-green-50 px-4 py-3 text-sm font-medium text-green-800';
    }

    function agregarAtributo() {
        const ejemplo = ejemplosAtributos[contenedorAtributos.children.length % ejemplosAtributos.length] || ejemplosGenerales[0];
        const oculto = seccionAtributos.classList.contains('hidden');

        const fila = document.createElement('div');
        fila.className = 'flex gap-4 items-center mt-3';

        const nombre = document.createElement('input');
        nombre.type = 'text';
        nombre.name = 'atributos_nombres[]';
        nombre.placeholder = 'Ej: ' + ejemplo.nombre;
        nombre.className = 'w-1/3 border rounded px-3 py-2 focus:border-blue-500 focus:outline-none';
        nombre.disabled = oculto;

        const valor = document.createElement('input');
        valor.type = 'text';
        valor.name = 'atributos_valores[]';
        valor.placeholder = 'Ej: ' + ejemplo.valor;
        valor.className = 'w-2/3 border rounded px-3 py-2 focus:border-blue-500 focus:outline-none';
        valor.disabled = oculto;

        const boton = document.createElement('button');
        boton.type = 'button';
        boton.textContent = 'X';
        boton.title = 'Eliminar';
        boton.className = 'text-red-500 hover:text-red-700 font-bold px-2';
        boton.addEventListener('click', function () {
            fila.remove();
        });

        fila.appendChild(nombre);
        fila.appendChild(valor);
        fila.appendChild(boton);
        contenedorAtributos.appendChild(fila);
    }

    function normalizarParteCodigo(valor) {
        return (valor || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^A-Za-z0-9]/g, '')
            .toUpperCase();
    }

    if (inputCodigoProducto && botonGenerarCodigo) {
        botonGenerarCodigo.addEventListener('click', function () {
            const marca = normalizarParteCodigo(document.getElementById('producto-marca').value);
            const sku = normalizarParteCodigo(document.getElementById('producto-sku').value);

            if (!marca || !sku) {
                alert('Ingresa la marca y el SKU para generar el código.');
                return;
            }

            inputCodigoProducto.value = 'REP' + marca.slice(0, 3) + sku;
        });
    }

    selectCategoria.addEventListener('change', function () {
        cargarSubcategorias(selectCategoria.value);
    });

    selectSubcategoria.addEventListener('change', actualizarFormulario);
    clampNonNegativeNumbers();
    if (inputStockActual && inputStockMinimo) {
        inputStockActual.addEventListener('input', actualizarIndicadorStock);
        inputStockMinimo.addEventListener('input', actualizarIndicadorStock);
    }

    const inputImagenProducto = document.getElementById('input-imagen-producto');
    const previewImagenProducto = document.getElementById('preview-imagen-producto');
    const imagenPreview = document.getElementById('imagen-preview');

    if (inputImagenProducto) {
        inputImagenProducto.addEventListener('change', function (event) {
            const archivo = event.target.files && event.target.files[0];

            if (!archivo) {
                previewImagenProducto.classList.add('hidden');
                imagenPreview.src = '';
                return;
            }

            const lector = new FileReader();
            lector.onload = function (e) {
                imagenPreview.src = e.target.result;
                previewImagenProducto.classList.remove('hidden');
            };
            lector.readAsDataURL(archivo);
        });
    }

    if (categoriaSeleccionada) {
        selectCategoria.value = String(categoriaSeleccionada);
        cargarSubcategorias(String(categoriaSeleccionada), subcategoriaSeleccionada);
    } else if (subcategoriaSeleccionada) {
        Object.keys(subcategoriasPorCategoria).forEach(function (categoriaId) {
            const coincide = subcategoriasPorCategoria[categoriaId].some(function (subcategoria) {
                return String(subcategoria.id) === String(subcategoriaSeleccionada);
            });

            if (coincide && selectCategoria.value === '') {
                selectCategoria.value = categoriaId;
                cargarSubcategorias(categoriaId, subcategoriaSeleccionada);
            }
        });
    }

    actualizarFormulario();
    actualizarIndicadorStock();
</script>

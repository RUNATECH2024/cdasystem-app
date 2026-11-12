<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtributoProducto;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Subcategoria;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class AdminProductoController extends Controller
{
    // Listar productos con búsqueda y filtros
    public function index(Request $request)
    {
        $q = mb_substr(trim((string) $request->input('q')), 0, 100);
        $estado = $request->input('estado');
        $idCategoria = $request->input('id_categoria');
        $stockBajo = $request->boolean('stock_bajo');

        $productos = Producto::with('subcategoria.categoria')
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.mb_strtolower($q).'%';
                $query->where(function ($sub) use ($like) {
                    $sub->whereRaw('LOWER(nombre) LIKE ?', [$like])
                        ->orWhereRaw("LOWER(COALESCE(sku, '')) LIKE ?", [$like])
                        ->orWhereRaw("LOWER(COALESCE(marca, '')) LIKE ?", [$like]);
                });
            })
            ->when($estado === 'activos', fn ($query) => $query->where('activo', true))
            ->when($estado === 'inactivos', fn ($query) => $query->where('activo', false))
            ->when($idCategoria, function ($query) use ($idCategoria) {
                $query->whereHas('subcategoria', fn ($sub) => $sub->where('id_categoria', $idCategoria));
            })
            ->when($stockBajo, fn ($query) => $query->whereColumn('stock_actual', '<=', 'stock_minimo'))
            ->orderByDesc('id_producto')
            ->paginate(10)
            ->withQueryString();

        $categorias = Categoria::orderBy('orden_visual')->get();

        return view('admin.productos.index', compact('productos', 'categorias', 'q', 'estado', 'idCategoria', 'stockBajo'));
    }

    // Mostrar formulario para crear producto
    public function create()
    {
        return view('admin.productos.create', $this->datosFormulario(null, [
            'accion' => route('admin.productos.store'),
            'metodo' => 'POST',
            'titulo' => 'Añadir Nuevo Producto',
            'textoBoton' => 'Guardar Producto',
        ]));
    }

    // Mostrar formulario de edición
    public function edit(Producto $producto)
    {
        return view('admin.productos.edit', $this->datosFormulario($producto, [
            'accion' => route('admin.productos.update', $producto),
            'metodo' => 'PUT',
            'titulo' => 'Editar Producto',
            'textoBoton' => 'Actualizar Producto',
        ]));
    }

    public function store(Request $request)
    {
        $this->validar($request);

        $nombreImagen = $request->hasFile('imagen')
            ? $this->guardarImagen($request->file('imagen'))
            : null;
        $imagenesAdicionales = [];

        foreach ($request->file('imagenes', []) as $imagen) {
            $imagenesAdicionales[] = $this->guardarImagen($imagen);
        }

        $producto = Producto::create(array_merge($this->datosProducto($request, null), [
            'imagen_principal' => $nombreImagen,
            'imagenes_adicionales' => $imagenesAdicionales,
        ]));

        // Características libres (Procesador, RAM, etc.)
        $this->guardarAtributosLibres($request, $producto);

        // Campos propios de la categoría (licencia, capacidad, etc.)
        $this->guardarCamposCategoria($request, $producto);

        return redirect()->route('admin.productos.index')->with('success', '¡Producto y características guardados con éxito!');
    }

    public function update(Request $request, Producto $producto)
    {
        $this->validar($request, $producto);

        $nombreImagen = $producto->imagen_principal;

        if ($request->boolean('eliminar_imagen')) {
            $this->eliminarImagen($nombreImagen);
            $nombreImagen = null;
        }

        if ($request->hasFile('imagen')) {
            $this->eliminarImagen($nombreImagen);
            $nombreImagen = $this->guardarImagen($request->file('imagen'), $producto->id_producto);
        }

        $imagenesAdicionales = $producto->imagenes_adicionales ?? [];
        $imagenesEliminadas = array_values(array_intersect(
            $imagenesAdicionales,
            $request->input('eliminar_imagenes', [])
        ));

        foreach ($imagenesEliminadas as $imagenEliminada) {
            $this->eliminarImagen($imagenEliminada);
        }

        $imagenesAdicionales = array_values(array_diff($imagenesAdicionales, $imagenesEliminadas));
        foreach ($request->file('imagenes', []) as $imagen) {
            $imagenesAdicionales[] = $this->guardarImagen($imagen, $producto->id_producto);
        }

        $producto->update(array_merge($this->datosProducto($request, $producto), [
            'imagen_principal' => $nombreImagen,
            'imagenes_adicionales' => $imagenesAdicionales,
        ]));

        // Reemplazamos las características para reflejar exactamente lo enviado
        $producto->atributos()->delete();
        $this->guardarAtributosLibres($request, $producto);
        $this->guardarCamposCategoria($request, $producto);

        return redirect()->route('admin.productos.index')->with('success', 'Producto actualizado correctamente.');
    }

    // Activar / desactivar desde el listado
    public function actualizarEstado(Producto $producto)
    {
        $producto->update(['activo' => ! $producto->activo]);

        return back()->with('success', $producto->activo ? 'Producto activado.' : 'Producto desactivado.');
    }

    // Destacar / quitar destacado desde el listado
    public function actualizarDestacado(Producto $producto)
    {
        $producto->update(['destacado' => ! $producto->destacado]);

        return back()->with('success', $producto->destacado ? 'Producto destacado.' : 'Producto quitado de destacados.');
    }

    // Actualizar la marca desde la tabla
    public function actualizarMarca(Request $request, Producto $producto)
    {
        $request->validate([
            'marca' => 'nullable|string|max:80',
        ]);

        $producto->update(['marca' => $request->marca]);

        return back()->with('success', 'Marca actualizada correctamente.');
    }

    private function validar(Request $request, ?Producto $producto = null): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:150',
            'codigo_barras' => [
                'nullable', 'string', 'max:50',
                Rule::unique('productos', 'codigo_barras')->ignore($producto?->id_producto, 'id_producto'),
            ],
            'sku' => [
                'nullable', 'string', 'max:50',
                Rule::unique('productos', 'sku')->ignore($producto?->id_producto, 'id_producto'),
            ],
            'id_subcategoria' => 'required|integer|exists:subcategorias,id_subcategoria',
            'marca' => 'required|string|max:80',
            'descripcion_corta' => 'nullable|string|max:255',
            'descripcion_larga' => 'nullable|string|max:5000',
            'precio_compra' => 'required|numeric|min:0|max:99999999.99',
            'precio_venta' => 'required|numeric|min:0|max:99999999.99',
            'stock_actual' => 'required|integer|min:0|max:1000000',
            'stock_minimo' => 'nullable|integer|min:0|max:1000000',
            'condicion' => 'nullable|string|in:Nuevo,Reacondicionado,Usado',
            'garantia_meses' => 'nullable|integer|min:0|max:1200',
            'fecha_vencimiento' => 'nullable|date',
            'activo' => 'nullable|boolean',
            'destacado' => 'nullable|boolean',
            'es_repuesto' => 'nullable|boolean',
            'eliminar_imagen' => 'nullable|boolean',
            'eliminar_imagenes' => 'nullable|array|max:10',
            'eliminar_imagenes.*' => 'string|max:255',
            'atributos_nombres' => 'nullable|array|max:50',
            'atributos_nombres.*' => 'nullable|string|max:50',
            'atributos_valores' => 'nullable|array|max:50',
            'atributos_valores.*' => 'nullable|string|max:100',
            'campos_categoria' => 'nullable|array|max:50',
            'campos_categoria.*' => ['nullable', function ($attribute, $value, $fail) {
                if (is_array($value) || is_object($value)) {
                    $fail('El campo '.$attribute.' no es válido.');

                    return;
                }

                if (is_string($value) && mb_strlen($value) > 100) {
                    $fail('El campo '.$attribute.' es demasiado largo.');
                }
            }],
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048|dimensions:max_width=6000,max_height=6000',
            'imagenes' => 'nullable|array|max:10',
            'imagenes.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048|dimensions:max_width=6000,max_height=6000',
        ]);
    }

    private function datosProducto(Request $request, ?Producto $producto): array
    {
        $esRepuesto = $request->has('es_repuesto') ? $request->boolean('es_repuesto') : ($producto?->es_repuesto ?? false);
        $codigoBarras = $request->filled('codigo_barras')
            ? trim((string) $request->input('codigo_barras'))
            : null;

        if ($codigoBarras === null && $esRepuesto) {
            $codigoBarras = $this->codigoRepuestoUnico($request->input('marca'), $request->input('sku'), $producto);
        }

        return [
            'nombre' => $request->input('nombre'),
            'codigo_barras' => $codigoBarras,
            'id_subcategoria' => $request->input('id_subcategoria'),
            'sku' => $request->input('sku'),
            'marca' => $request->input('marca'),
            'descripcion_corta' => $request->input('descripcion_corta'),
            'descripcion_larga' => $request->input('descripcion_larga'),
            'precio_compra' => $request->input('precio_compra'),
            'precio_venta' => $request->input('precio_venta'),
            'stock_actual' => $request->input('stock_actual'),
            'stock_minimo' => $request->filled('stock_minimo') ? (int) $request->input('stock_minimo') : 5,
            'condicion' => $request->input('condicion', 'Nuevo'),
            'garantia_meses' => $request->filled('garantia_meses') ? (int) $request->input('garantia_meses') : 0,
            'fecha_vencimiento' => $request->filled('fecha_vencimiento') ? $request->input('fecha_vencimiento') : null,
            'activo' => $request->has('activo') ? $request->boolean('activo') : ($producto?->activo ?? true),
            'destacado' => $request->has('destacado') ? $request->boolean('destacado') : ($producto?->destacado ?? false),
            'es_repuesto' => $esRepuesto,
        ];
    }

    private function codigoRepuestoUnico(?string $marca, ?string $sku, ?Producto $producto = null): ?string
    {
        $codigo = Producto::generarCodigoRepuesto($marca, $sku);

        if ($codigo === null) {
            return null;
        }

        $base = $codigo;
        $intento = 2;

        while (Producto::where('codigo_barras', $codigo)
            ->when($producto, fn ($query) => $query->where('id_producto', '!=', $producto->id_producto))
            ->exists()) {
            $codigo = $base.'-'.$intento;
            $intento++;
        }

        return $codigo;
    }

    private function datosFormulario(?Producto $producto, array $extra): array
    {
        $categorias = Categoria::with([
            'subcategorias' => fn ($query) => $query->orderBy('orden_visual'),
        ])->orderBy('orden_visual')->get();

        $subcategoriasPorCategoria = $categorias->mapWithKeys(fn (Categoria $categoria) => [
            $categoria->id_categoria => $categoria->subcategorias->map(fn ($subcategoria) => [
                'id' => $subcategoria->id_subcategoria,
                'nombre' => $subcategoria->nombre,
            ])->values(),
        ]);

        $reglas = $this->reglasPorCategoria();

        $configPorCategoria = $categorias->mapWithKeys(fn (Categoria $categoria) => [
            $categoria->id_categoria => $reglas[$categoria->nombre] ?? $this->reglaPorDefecto(),
        ]);

        $atributosLibres = [];
        $valoresCategoria = [];
        $categoriaSeleccionada = null;
        $subcategoriaSeleccionada = null;

        if ($producto) {
            $producto->loadMissing(['subcategoria.categoria', 'atributos']);
            $categoriaSeleccionada = $producto->subcategoria?->id_categoria;
            $subcategoriaSeleccionada = $producto->id_subcategoria;

            $definiciones = $producto->subcategoria?->categoria
                ? ($reglas[$producto->subcategoria->categoria->nombre]['campos'] ?? [])
                : [];

            $nombrePorLabel = collect($definiciones)->pluck('name', 'label')->all();

            foreach ($producto->atributos as $atributo) {
                if (isset($nombrePorLabel[$atributo->nombre_atributo])) {
                    $valoresCategoria[$nombrePorLabel[$atributo->nombre_atributo]] = $atributo->valor_atributo;
                } else {
                    $atributosLibres[] = [
                        'nombre' => $atributo->nombre_atributo,
                        'valor' => $atributo->valor_atributo,
                    ];
                }
            }
        }

        return array_merge([
            'producto' => $producto,
            'categorias' => $categorias,
            'subcategoriasPorCategoria' => $subcategoriasPorCategoria,
            'configPorCategoria' => $configPorCategoria,
            'atributosLibres' => $atributosLibres,
            'valoresCategoria' => $valoresCategoria,
            'categoriaSeleccionada' => $categoriaSeleccionada,
            'subcategoriaSeleccionada' => $subcategoriaSeleccionada,
        ], $extra);
    }

    private function guardarImagen(UploadedFile $imagen, ?int $idProducto = null): string
    {
        $directorio = public_path('productos');

        if (! is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }

        $extension = strtolower((string) $imagen->getClientOriginalExtension());
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $extension = $imagen->guessExtension() ?: 'jpg';
        }

        $nombre = time().'_'.($idProducto ?? 'nuevo').'_'.uniqid().'.'.$extension;
        $imagen->move($directorio, $nombre);

        return $nombre;
    }

    private function eliminarImagen(?string $nombre): void
    {
        if (! $nombre) {
            return;
        }

        $ruta = public_path('productos/'.basename($nombre));

        if (is_file($ruta)) {
            @unlink($ruta);
        }
    }

    private function guardarAtributosLibres(Request $request, Producto $producto): void
    {
        if (! $request->has('atributos_nombres') || ! $request->has('atributos_valores')) {
            return;
        }

        $nombres = $request->input('atributos_nombres');
        $valores = $request->input('atributos_valores');
        $total = min(count($nombres), count($valores));

        for ($i = 0; $i < $total; $i++) {
            if (! empty($nombres[$i]) && ! empty($valores[$i])) {
                AtributoProducto::create([
                    'id_producto' => $producto->id_producto,
                    'nombre_atributo' => mb_substr((string) $nombres[$i], 0, 50),
                    'valor_atributo' => mb_substr((string) $valores[$i], 0, 100),
                ]);
            }
        }
    }

    private function guardarCamposCategoria(Request $request, Producto $producto): void
    {
        $subcategoria = Subcategoria::with('categoria')->find($request->input('id_subcategoria'));

        if (! $subcategoria || ! $subcategoria->categoria) {
            return;
        }

        $definiciones = $this->reglasPorCategoria()[$subcategoria->categoria->nombre]['campos'] ?? [];
        $valores = $request->input('campos_categoria', []);

        foreach ($definiciones as $campo) {
            $valor = $valores[$campo['name']] ?? null;

            if ($valor === null || $valor === '') {
                continue;
            }

            AtributoProducto::create([
                'id_producto' => $producto->id_producto,
                'nombre_atributo' => $campo['label'],
                'valor_atributo' => mb_substr((string) $valor, 0, 100),
            ]);
        }
    }

    private function reglaPorDefecto(): array
    {
        return [
            'garantia' => true,
            'atributos' => true,
            'ejemplos' => [['nombre' => 'Característica', 'valor' => 'Especificación']],
            'campos' => [],
        ];
    }

    private function reglasPorCategoria(): array
    {
        return [
            'Computadoras' => [
                'garantia' => true,
                'atributos' => true,
                'ejemplos' => [
                    ['nombre' => 'Procesador', 'valor' => 'Intel Core i5'],
                    ['nombre' => 'Memoria RAM', 'valor' => '8 GB DDR4'],
                    ['nombre' => 'Almacenamiento', 'valor' => '512 GB SSD'],
                ],
                'campos' => [
                    ['name' => 'uso', 'label' => 'Uso recomendado', 'tipo' => 'select', 'options' => ['Hogar', 'Oficina', 'Gaming', 'Diseño', 'Empresa', 'Educativo']],
                ],
            ],
            'Componentes' => [
                'garantia' => true,
                'atributos' => true,
                'ejemplos' => [
                    ['nombre' => 'Socket', 'valor' => 'LGA 1700'],
                    ['nombre' => 'Frecuencia', 'valor' => '3200 MHz'],
                    ['nombre' => 'Capacidad', 'valor' => '16 GB'],
                ],
                'campos' => [
                    ['name' => 'tipo_componente', 'label' => 'Tipo de componente', 'tipo' => 'select', 'options' => ['Motherboard', 'Procesador', 'Tarjeta de video', 'Fuente de poder', 'Refrigeración', 'Disco', 'Memoria RAM', 'Case', 'Otro']],
                ],
            ],
            'Periféricos' => [
                'garantia' => true,
                'atributos' => true,
                'ejemplos' => [
                    ['nombre' => 'Conectividad', 'valor' => 'USB / Bluetooth'],
                    ['nombre' => 'Compatibilidad', 'valor' => 'Windows / macOS'],
                    ['nombre' => 'Color', 'valor' => 'Negro'],
                ],
                'campos' => [
                    ['name' => 'tipo_periferico', 'label' => 'Tipo de periférico', 'tipo' => 'select', 'options' => ['Mouse', 'Teclado', 'Webcam', 'Cámara', 'Micrófono', 'Hub', 'Otro']],
                    ['name' => 'conectividad', 'label' => 'Conectividad', 'tipo' => 'text'],
                ],
            ],
            'Monitores' => [
                'garantia' => true,
                'atributos' => true,
                'ejemplos' => [
                    ['nombre' => 'Tamaño', 'valor' => '24 pulgadas'],
                    ['nombre' => 'Resolución', 'valor' => '1920 x 1080'],
                    ['nombre' => 'Tasa de refresco', 'valor' => '165 Hz'],
                ],
                'campos' => [
                    ['name' => 'tipo_panel', 'label' => 'Tipo de panel', 'tipo' => 'select', 'options' => ['IPS', 'VA', 'TN', 'OLED', 'LED']],
                    ['name' => 'tamano', 'label' => 'Tamaño', 'tipo' => 'text'],
                    ['name' => 'resolucion', 'label' => 'Resolución', 'tipo' => 'text'],
                    ['name' => 'tasa_refresco', 'label' => 'Tasa de refresco', 'tipo' => 'text'],
                ],
            ],
            'Impresión' => [
                'garantia' => true,
                'atributos' => true,
                'ejemplos' => [
                    ['nombre' => 'Tecnología', 'valor' => 'Láser'],
                    ['nombre' => 'Velocidad', 'valor' => '30 ppm'],
                    ['nombre' => 'Conectividad', 'valor' => 'Wi-Fi / USB'],
                ],
                'campos' => [
                    ['name' => 'tecnologia', 'label' => 'Tecnología / Tipo', 'tipo' => 'select', 'options' => ['Láser', 'Tinta', 'Matricial', 'Tóner', 'Suministro']],
                    ['name' => 'rendimiento', 'label' => 'Rendimiento', 'tipo' => 'text'],
                    ['name' => 'compatibilidad', 'label' => 'Compatibilidad', 'tipo' => 'text'],
                ],
            ],
            'Móviles' => [
                'garantia' => true,
                'atributos' => true,
                'ejemplos' => [
                    ['nombre' => 'Pantalla', 'valor' => '6.5 pulgadas'],
                    ['nombre' => 'Memoria', 'valor' => '8 GB'],
                    ['nombre' => 'Almacenamiento', 'valor' => '128 GB'],
                ],
                'campos' => [
                    ['name' => 'almacenamiento', 'label' => 'Almacenamiento', 'tipo' => 'text'],
                    ['name' => 'memoria_ram', 'label' => 'Memoria RAM', 'tipo' => 'text'],
                    ['name' => 'red', 'label' => 'Red', 'tipo' => 'select', 'options' => ['4G', '5G', 'Wi-Fi']],
                ],
            ],
            'TV & Video' => [
                'garantia' => true,
                'atributos' => true,
                'ejemplos' => [
                    ['nombre' => 'Tamaño', 'valor' => '55 pulgadas'],
                    ['nombre' => 'Resolución', 'valor' => '4K UHD'],
                    ['nombre' => 'Smart TV', 'valor' => 'Sí'],
                ],
                'campos' => [
                    ['name' => 'tipo_video', 'label' => 'Tipo', 'tipo' => 'select', 'options' => ['Televisor', 'Proyector', 'Accesorio']],
                    ['name' => 'tamano', 'label' => 'Tamaño', 'tipo' => 'text'],
                    ['name' => 'resolucion', 'label' => 'Resolución', 'tipo' => 'text'],
                ],
            ],
            'Audio' => [
                'garantia' => true,
                'atributos' => true,
                'ejemplos' => [
                    ['nombre' => 'Tipo', 'valor' => 'Over-ear'],
                    ['nombre' => 'Conectividad', 'valor' => 'Bluetooth 5.3'],
                    ['nombre' => 'Potencia', 'valor' => '20 W'],
                ],
                'campos' => [
                    ['name' => 'tipo_audio', 'label' => 'Tipo de audio', 'tipo' => 'select', 'options' => ['Headset', 'Auriculares', 'Parlante', 'Micrófono', 'Accesorio']],
                    ['name' => 'conectividad', 'label' => 'Conectividad', 'tipo' => 'text'],
                ],
            ],
            'Adaptadores & Cables' => [
                'garantia' => true,
                'atributos' => true,
                'ejemplos' => [
                    ['nombre' => 'Conector A', 'valor' => 'USB-C'],
                    ['nombre' => 'Conector B', 'valor' => 'HDMI'],
                    ['nombre' => 'Longitud', 'valor' => '2 metros'],
                ],
                'campos' => [
                    ['name' => 'conector_a', 'label' => 'Conector A', 'tipo' => 'text'],
                    ['name' => 'conector_b', 'label' => 'Conector B', 'tipo' => 'text'],
                    ['name' => 'longitud', 'label' => 'Longitud', 'tipo' => 'text'],
                ],
            ],
            'Redes' => [
                'garantia' => true,
                'atributos' => true,
                'ejemplos' => [
                    ['nombre' => 'Estándar', 'valor' => 'Wi-Fi 6'],
                    ['nombre' => 'Puertos', 'valor' => '4 x Gigabit'],
                    ['nombre' => 'Velocidad', 'valor' => '3000 Mbps'],
                ],
                'campos' => [
                    ['name' => 'tipo_red', 'label' => 'Tipo de equipo', 'tipo' => 'select', 'options' => ['Router', 'Switch', 'Access Point', 'Antena', 'NAS', 'Tarjeta de red', 'WiFi Deco', 'Accesorio']],
                    ['name' => 'puertos', 'label' => 'Puertos', 'tipo' => 'number'],
                    ['name' => 'estandar', 'label' => 'Estándar', 'tipo' => 'text'],
                ],
            ],
            'Energía' => [
                'garantia' => true,
                'atributos' => true,
                'ejemplos' => [
                    ['nombre' => 'Capacidad', 'valor' => '1500 VA'],
                    ['nombre' => 'Voltaje', 'valor' => '120 V'],
                    ['nombre' => 'Salidas', 'valor' => '8 tomacorrientes'],
                ],
                'campos' => [
                    ['name' => 'tipo_energia', 'label' => 'Tipo de equipo', 'tipo' => 'select', 'options' => ['UPS', 'UPS Online', 'Regulador', 'Supresor de picos', 'Generador', 'Power Station', 'Energía solar', 'Accesorio']],
                    ['name' => 'capacidad_va', 'label' => 'Capacidad (VA)', 'tipo' => 'text'],
                    ['name' => 'voltaje', 'label' => 'Voltaje', 'tipo' => 'text'],
                    ['name' => 'salidas', 'label' => 'Cantidad de salidas', 'tipo' => 'number'],
                ],
            ],
            'Servidores' => [
                'garantia' => true,
                'atributos' => true,
                'ejemplos' => [
                    ['nombre' => 'Procesador', 'valor' => 'Intel Xeon'],
                    ['nombre' => 'Memoria', 'valor' => '32 GB ECC'],
                    ['nombre' => 'Bahías', 'valor' => '8 x 2.5"'],
                ],
                'campos' => [
                    ['name' => 'formato_servidor', 'label' => 'Formato', 'tipo' => 'select', 'options' => ['Rack', 'Torre', 'Blade', 'Accesorio']],
                    ['name' => 'procesador', 'label' => 'Procesador', 'tipo' => 'text'],
                    ['name' => 'memoria', 'label' => 'Memoria', 'tipo' => 'text'],
                ],
            ],
            'Software' => [
                'garantia' => false,
                'atributos' => false,
                'ejemplos' => [],
                'campos' => [
                    ['name' => 'tipo_licencia', 'label' => 'Tipo de licencia', 'tipo' => 'select', 'options' => ['Perpetua', 'Suscripción', 'OEM', 'Volume', 'Educativa']],
                    ['name' => 'duracion', 'label' => 'Duración', 'tipo' => 'select', 'options' => ['Permanente', '1 año', '2 años', '3 años']],
                    ['name' => 'equipos', 'label' => 'Equipos incluidos', 'tipo' => 'number'],
                    ['name' => 'formato', 'label' => 'Formato', 'tipo' => 'select', 'options' => ['Digital', 'Física']],
                ],
            ],
            'Hogar' => [
                'garantia' => true,
                'atributos' => true,
                'ejemplos' => [
                    ['nombre' => 'Función', 'valor' => 'Aspiradora robot'],
                    ['nombre' => 'Conectividad', 'valor' => 'Wi-Fi'],
                    ['nombre' => 'Consumo', 'valor' => '40 W'],
                ],
                'campos' => [
                    ['name' => 'tipo_hogar', 'label' => 'Tipo', 'tipo' => 'select', 'options' => ['Electrodoméstico', 'Juguete', 'Smartwatch', 'Domótica', 'Accesorio']],
                    ['name' => 'conectividad', 'label' => 'Conectividad', 'tipo' => 'text'],
                ],
            ],
            'Equipos Gaming' => [
                'garantia' => true,
                'atributos' => true,
                'ejemplos' => [
                    ['nombre' => 'Plataforma', 'valor' => 'PlayStation 5'],
                    ['nombre' => 'Conectividad', 'valor' => 'Inalámbrico'],
                    ['nombre' => 'Compatibilidad', 'valor' => 'PC / Consola'],
                ],
                'campos' => [
                    ['name' => 'tipo_gaming', 'label' => 'Tipo', 'tipo' => 'select', 'options' => ['Consola', 'Control', 'Silla', 'Accesorio']],
                    ['name' => 'plataforma', 'label' => 'Plataforma', 'tipo' => 'text'],
                    ['name' => 'compatibilidad', 'label' => 'Compatibilidad', 'tipo' => 'text'],
                ],
            ],
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Subcategoria;
use Illuminate\Http\Request;

class TiendaController extends Controller
{
    public function producto($id)
    {
        $producto = Producto::with(['subcategoria.categoria', 'atributos'])
            ->where('activo', true)
            ->findOrFail((int) $id);

        $relacionados = Producto::where('id_subcategoria', $producto->id_subcategoria)
            ->where('id_producto', '!=', $producto->id_producto)
            ->where('activo', true)
            ->take(4)
            ->get();

        return view('tienda.producto', compact('producto', 'relacionados'));
    }

    public function repuestos(Request $request)
    {
        $q = mb_substr(trim((string) $request->input('q')), 0, 100);
        $categoriaId = (int) $request->input('categoria');
        $orden = (string) $request->input('orden', 'menor-precio');

        if (! in_array($orden, ['menor-precio', 'mayor-precio', 'nombre'], true)) {
            $orden = 'menor-precio';
        }

        $categorias = Categoria::where('activo', true)
            ->whereHas('subcategorias.productos', function ($query) {
                $query->where('productos.activo', true)
                    ->where('productos.es_repuesto', true);
            })
            ->orderBy('orden_visual')
            ->get();

        $productos = Producto::with('subcategoria.categoria')
            ->where('activo', true)
            ->where('es_repuesto', true)
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.mb_strtolower($q).'%';

                $query->where(function ($query) use ($like) {
                    $query->whereRaw('LOWER(nombre) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(sku) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(marca) LIKE ?', [$like]);
                });
            })
            ->when($categoriaId > 0, function ($query) use ($categoriaId) {
                $query->whereHas('subcategoria', fn ($query) => $query->where('id_categoria', $categoriaId));
            });

        $productos = match ($orden) {
            'mayor-precio' => $productos->orderByDesc('precio_venta'),
            'nombre' => $productos->orderBy('nombre'),
            default => $productos->orderBy('precio_venta'),
        };

        $productos = $productos->paginate(20)->withQueryString();

        return view('tienda.repuestos', compact('productos', 'categorias', 'q', 'orden', 'categoriaId'));
    }

    public function centroServicio()
    {
        $definiciones = [
            ['nombre' => 'Computador', 'icono' => 'fa-laptop', 'tipo' => 'categoria', 'clave' => 'Computadoras'],
            ['nombre' => 'Impresora', 'icono' => 'fa-print', 'tipo' => 'categoria', 'clave' => 'Impresión'],
            ['nombre' => 'Movil', 'icono' => 'fa-mobile-screen', 'tipo' => 'categoria', 'clave' => 'Móviles'],
            ['nombre' => 'Plotter', 'icono' => 'fa-scroll', 'tipo' => 'busqueda', 'clave' => 'plotter'],
            ['nombre' => 'Proyector', 'icono' => 'fa-video', 'tipo' => 'subcategoria', 'clave' => 'PROYECTORES'],
            ['nombre' => 'Scanner', 'icono' => 'fa-barcode', 'tipo' => 'subcategoria', 'clave' => 'ESCANERS'],
            ['nombre' => 'Audio & Video', 'icono' => 'fa-headphones', 'tipo' => 'categoria', 'clave' => 'TV & Video'],
        ];

        $categoriasPrincipales = collect($definiciones)->map(function (array $definicion) {
            $url = route('buscar', ['q' => $definicion['clave']]);

            if ($definicion['tipo'] === 'categoria') {
                $categoria = Categoria::where('nombre', $definicion['clave'])->where('activo', true)->first();

                if ($categoria) {
                    $url = route('categoria.show', $categoria->id_categoria);
                }
            }

            if ($definicion['tipo'] === 'subcategoria') {
                $subcategoria = Subcategoria::where('nombre', $definicion['clave'])->where('activo', true)->first();

                if ($subcategoria) {
                    $url = url('/categoria/'.$subcategoria->id_subcategoria);
                }
            }

            return [
                'nombre' => $definicion['nombre'],
                'icono' => $definicion['icono'],
                'url' => $url,
            ];
        });

        $productosDestacados = Producto::where('activo', true)
            ->where('destacado', true)
            ->orderByDesc('id_producto')
            ->take(8)
            ->get();

        return view('tienda.centro-servicio', compact('categoriasPrincipales', 'productosDestacados'));
    }

    public function buscar(Request $request)
    {
        $q = mb_substr(trim((string) $request->input('q')), 0, 100);
        $productos = collect();

        if ($q !== '') {
            $like = '%'.mb_strtolower($q).'%';
            $productos = Producto::where('activo', true)
                ->where(function ($query) use ($like) {
                    $query->whereRaw('LOWER(nombre) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(sku) LIKE ?', [$like]);
                })
                ->get();
        }

        return view('tienda.buscar', compact('q', 'productos'));
    }

    public function marcas()
    {
        // 1. Obtenemos las marcas que SÍ tienen productos
        $marcasConProductos = Producto::where('activo', true)
            ->get()
            ->groupBy(fn (Producto $producto) => $producto->marca ?: 'Sin marca')
            ->map(fn ($productos, $marca) => [
                'marca' => $marca,
                'cantidad' => $productos->count(),
                'stock_total' => $productos->sum('stock_actual'),
            ]);

        // 2. Definimos el diccionario de todas nuestras imágenes (las que vimos en tu captura)
        $diccionario = [
            'General' => '001', 'Abygen' => 'abs', 'Acer' => 'ace', 'Adata' => 'ada', 'AGI' => 'agi',
            'AMD' => 'amd', 'Amazon' => 'amz', 'APC' => 'apc', 'Apple' => 'apl', 'ArgomTech' => 'arg',
            'ASRock' => 'asr', 'ASUS' => 'asu', 'BenQ' => 'ben', 'Bose' => 'bos', 'CDP' => 'cdp',
            'Corsair' => 'cor', 'Dell' => 'del', 'Dynabook' => 'dyn', 'Epson' => 'eps', 'Eset' => 'ese',
            'Gigabyte' => 'gby', 'Genius' => 'gen', 'HP' => 'hp', 'Hyundai' => 'hyu', 'Intel' => 'int',
            'Itly Audio' => 'itl', 'Kaspersky' => 'kas', 'Kingston' => 'kin', 'Lenovo' => 'len',
            'Lexmark' => 'lex', 'LG' => 'lg', 'Logitech' => 'log', 'Mercusys' => 'mer', 'Microsoft' => 'mic',
            'QNAP' => 'qna', 'Q-ONE' => 'qon', 'Quasad' => 'qsa', 'Samsung' => 'sam', 'SAT' => 'sat',
            'SanDisk' => 'sdm', 'Seagate' => 'se1', 'Sony' => 'son', 'TP-Link' => 'tpl', 'Western Digital' => 'wes',
            'Xiaomi' => 'xia', 'Otros' => 'xxx', 'Huntkey' => 'bfx',
        ];

        // 3. Combinamos: Si la marca tiene productos, usamos sus datos. Si no, ponemos 0.
        $todasLasMarcas = collect($diccionario)->map(function ($archivo, $nombreMarca) use ($marcasConProductos) {
            $datos = $marcasConProductos->get($nombreMarca);

            return [
                'marca' => $nombreMarca,
                'imagen' => $archivo.'.webp',
                'cantidad' => $datos ? $datos['cantidad'] : 0,
                'stock_total' => $datos ? $datos['stock_total'] : 0,
            ];
        })->values();

        return view('tienda.marcas', ['marcas' => $todasLasMarcas]);
    }

    // Nueva función optimizada para el clic
    public function productosPorMarca($nombre)
    {
        $nombre = mb_substr(trim((string) $nombre), 0, 80);

        $productos = Producto::where('activo', true)
            ->whereRaw('LOWER(marca) = ?', [mb_strtolower($nombre)])
            ->get();

        return view('tienda.marca_productos', compact('nombre', 'productos'));
    }
}

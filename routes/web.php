<?php

use App\Http\Controllers\Admin\AdminAuditoriaController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminClienteController;
use App\Http\Controllers\Admin\AdminCompraController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminOperacionController;
use App\Http\Controllers\Admin\AdminProductoController;
use App\Http\Controllers\Admin\AdminProveedorController;
use App\Http\Controllers\Admin\AdminReporteController;
use App\Http\Controllers\Admin\AdminRespaldoController;
use App\Http\Controllers\Admin\AdminUsuarioController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\ClienteAuthController;
use App\Http\Controllers\DistribuidorController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TiendaController;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Subcategoria;
use Illuminate\Support\Facades\Route;

// ==========================================
// 1. RUTAS DE LA TIENDA (Públicas)
// ==========================================

Route::get('/', function () {
    $categorias = Categoria::with([
        'subcategorias' => fn ($query) => $query->where('activo', true)->orderBy('orden_visual'),
    ])
        ->where('activo', true)
        ->orderBy('orden_visual')
        ->get();

    $productosDestacados = Producto::where('destacado', true)
        ->where('activo', true)
        ->orderByDesc('id_producto')
        ->take(6)
        ->get();

    return view('tienda.index', compact('categorias', 'productosDestacados'));
});

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::view('/quienes-somos', 'tienda.quienes-somos')->name('quienes-somos');
Route::get('/quienes-somos/repuestos', [TiendaController::class, 'repuestos'])->name('quienes-somos.repuestos');
Route::get('/centro-de-servicio', [TiendaController::class, 'centroServicio'])->name('centro-servicio');

Route::get('/ser-distribuidor', [DistribuidorController::class, 'create'])->name('distribuidor.create');
Route::post('/ser-distribuidor', [DistribuidorController::class, 'store'])->middleware('throttle:10,1')->name('distribuidor.store');
Route::view('/terminos-y-condiciones', 'tienda.terminos-y-condiciones')->name('terminos-condiciones');

Route::get('/categorias/{id}', function ($id) {
    $categoria = Categoria::with([
        'subcategorias' => fn ($query) => $query->where('activo', true)->orderBy('orden_visual'),
    ])->findOrFail($id);

    return view('tienda.categoria', compact('categoria'));
})->whereNumber('id')->name('categoria.show');

Route::get('/categoria/{id}', function ($id) {
    $subcategoria = Subcategoria::findOrFail($id);
    $productos = Producto::where('id_subcategoria', $id)->where('activo', true)->get();

    return view('tienda.productos', compact('subcategoria', 'productos'));
})->whereNumber('id');

Route::get('/producto/{id}', [TiendaController::class, 'producto'])
    ->whereNumber('id')
    ->name('producto.show');

// Rutas del Buscador y Marcas (¡Ojo con la ruta de marcas!)
Route::get('/buscar', [TiendaController::class, 'buscar'])->middleware('throttle:30,1')->name('buscar');
Route::get('/directorio-marcas', [TiendaController::class, 'marcas'])->middleware('throttle:30,1')->name('marcas');
Route::get('/marca/{nombre}', [TiendaController::class, 'productosPorMarca'])->name('marcas.productos');

// ==========================================
// 2. RUTAS DEL CARRITO
// ==========================================
Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito.index');
Route::post('/carrito/add/{id}', [CarritoController::class, 'add'])->whereNumber('id')->middleware('throttle:60,1')->name('carrito.add');
Route::post('/carrito/update/{id}', [CarritoController::class, 'update'])->whereNumber('id')->middleware('throttle:60,1')->name('carrito.update');
Route::post('/carrito/remove/{id}', [CarritoController::class, 'remove'])->whereNumber('id')->middleware('throttle:60,1')->name('carrito.remove');
Route::post('/carrito/checkout', [CarritoController::class, 'checkout'])->middleware('throttle:30,1')->name('carrito.checkout');
Route::get('/checkout', [CarritoController::class, 'checkoutForm'])->name('checkout.index');
Route::post('/checkout', [CarritoController::class, 'checkoutStore'])->middleware('throttle:30,1')->name('checkout.store');

// ==========================================
// 3. RUTAS DE AUTENTICACIÓN DE CLIENTES
// ==========================================
Route::get('/login', [ClienteAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [ClienteAuthController::class, 'login'])->middleware('throttle:5,1');
Route::get('/register', [ClienteAuthController::class, 'showRegister'])->name('register');
Route::post('/register', [ClienteAuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/logout', [ClienteAuthController::class, 'logout'])->name('logout');

// ==========================================
// 4. PANEL DE ADMINISTRACIÓN
// ==========================================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth:admin', 'role:Admin', 'audit.admin'])->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/usuarios', [AdminUsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/crear', [AdminUsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [AdminUsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('/auditoria', [AdminAuditoriaController::class, 'index'])->name('auditoria.index');
        Route::get('/respaldos', [AdminRespaldoController::class, 'index'])->name('respaldos.index');
        Route::post('/respaldos', [AdminRespaldoController::class, 'crear'])->name('respaldos.create');
        Route::get('/respaldos/{nombre}/descargar', [AdminRespaldoController::class, 'descargar'])
            ->where('nombre', '[A-Za-z0-9._-]+')
            ->name('respaldos.download');

        Route::get('/pedidos', [AdminOperacionController::class, 'pedidos'])->name('pedidos.index');
        Route::get('/pedidos/{pedido}', [AdminOperacionController::class, 'verPedido'])->name('pedidos.show');
        Route::patch('/pedidos/{pedido}/atendido', [AdminOperacionController::class, 'marcarPedidoAtendido'])->name('pedidos.atendido');
        Route::get('/clientes', [AdminClienteController::class, 'index'])->name('clientes.index');
        Route::get('/clientes/crear', [AdminClienteController::class, 'create'])->name('clientes.create');
        Route::post('/clientes', [AdminClienteController::class, 'store'])->name('clientes.store');
        Route::get('/clientes/{cliente}', [AdminClienteController::class, 'show'])->whereNumber('cliente')->name('clientes.show');
        Route::get('/clientes/{cliente}/editar', [AdminClienteController::class, 'edit'])->whereNumber('cliente')->name('clientes.edit');
        Route::put('/clientes/{cliente}', [AdminClienteController::class, 'update'])->whereNumber('cliente')->name('clientes.update');
        Route::patch('/clientes/{cliente}/estado', [AdminClienteController::class, 'actualizarEstado'])->whereNumber('cliente')->name('clientes.estado');
        Route::delete('/clientes/{cliente}', [AdminClienteController::class, 'destroy'])->whereNumber('cliente')->name('clientes.destroy');

        Route::get('/proveedores', [AdminProveedorController::class, 'index'])->name('proveedores.index');
        Route::get('/proveedores/crear', [AdminProveedorController::class, 'create'])->name('proveedores.create');
        Route::post('/proveedores', [AdminProveedorController::class, 'store'])->name('proveedores.store');
        Route::get('/proveedores/{proveedor}', [AdminProveedorController::class, 'show'])->whereNumber('proveedor')->name('proveedores.show');
        Route::get('/proveedores/{proveedor}/editar', [AdminProveedorController::class, 'edit'])->whereNumber('proveedor')->name('proveedores.edit');
        Route::put('/proveedores/{proveedor}', [AdminProveedorController::class, 'update'])->whereNumber('proveedor')->name('proveedores.update');
        Route::patch('/proveedores/{proveedor}/estado', [AdminProveedorController::class, 'actualizarEstado'])->whereNumber('proveedor')->name('proveedores.estado');
        Route::delete('/proveedores/{proveedor}', [AdminProveedorController::class, 'destroy'])->whereNumber('proveedor')->name('proveedores.destroy');

        Route::get('/compras', [AdminCompraController::class, 'index'])->name('compras.index');
        Route::get('/compras/crear', [AdminCompraController::class, 'create'])->name('compras.create');
        Route::post('/compras', [AdminCompraController::class, 'store'])->name('compras.store');
        Route::get('/compras/{compra}', [AdminCompraController::class, 'show'])->whereNumber('compra')->name('compras.show');
        Route::get('/compras/{compra}/pdf', [AdminCompraController::class, 'pdf'])->whereNumber('compra')->name('compras.pdf');
        Route::post('/compras/{compra}/abonos', [AdminCompraController::class, 'registrarAbono'])->whereNumber('compra')->name('compras.abonos.store');

        Route::get('/ordenes-taller', [AdminOperacionController::class, 'ordenes'])->name('ordenes.index');
        Route::get('/ordenes-taller/crear', [AdminOperacionController::class, 'crearOrden'])->name('ordenes.create');
        Route::post('/ordenes-taller', [AdminOperacionController::class, 'guardarOrden'])->name('ordenes.store');
        Route::get('/ordenes-taller/{orden}', [AdminOperacionController::class, 'verOrden'])->name('ordenes.show');
        Route::get('/ordenes-taller/{orden}/pdf', [AdminOperacionController::class, 'pdfOrden'])->name('ordenes.pdf');
        Route::get('/ordenes-taller/{orden}/editar', [AdminOperacionController::class, 'editarOrden'])->name('ordenes.edit');
        Route::put('/ordenes-taller/{orden}', [AdminOperacionController::class, 'actualizarOrden'])->name('ordenes.update');
        Route::delete('/ordenes-taller/{orden}', [AdminOperacionController::class, 'eliminarOrden'])->name('ordenes.destroy');

        Route::get('/productos', [AdminProductoController::class, 'index'])->name('productos.index');
        Route::get('/productos/crear', [AdminProductoController::class, 'create'])->name('productos.create');
        Route::post('/productos', [AdminProductoController::class, 'store'])->name('productos.store');
        Route::get('/productos/{producto}/editar', [AdminProductoController::class, 'edit'])->name('productos.edit');
        Route::put('/productos/{producto}', [AdminProductoController::class, 'update'])->name('productos.update');
        Route::patch('/productos/{producto}/estado', [AdminProductoController::class, 'actualizarEstado'])->name('productos.estado');
        Route::patch('/productos/{producto}/destacado', [AdminProductoController::class, 'actualizarDestacado'])->name('productos.destacado');
        Route::post('/productos/{producto}/marca', [AdminProductoController::class, 'actualizarMarca'])->name('productos.marca');

        Route::get('/reportes', [AdminReporteController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/exportar', [AdminReporteController::class, 'exportar'])->name('reportes.exportar');
        Route::get('/reportes/pdf', [AdminReporteController::class, 'pdf'])->name('reportes.pdf');
        Route::get('/productos/{producto}/ficha-pdf', [AdminReporteController::class, 'fichaTecnica'])->name('productos.pdf');
    });
});

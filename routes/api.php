<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogoController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\TallerController;
use App\Http\Controllers\Api\VentaController;
use Illuminate\Support\Facades\Route;

// --- RUTAS PÚBLICAS (Para los clientes de la tienda) ---
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/catalogo/menu', [CatalogoController::class, 'menu']);
    Route::get('/catalogo/productos/{id_subcategoria}', [CatalogoController::class, 'productosPorSubcategoria'])
        ->whereNumber('id_subcategoria');
    Route::get('/catalogo/buscar', [CatalogoController::class, 'buscar']);
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

// --- RUTAS PROTEGIDAS (Requieren login) ---
Route::middleware(['auth:sanctum', 'active', 'throttle:120,1'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Productos: solo administradores
    Route::post('/productos', [ProductoController::class, 'store'])->middleware('role:Admin');

    // Ventas: administradores y vendedores
    Route::post('/ventas', [VentaController::class, 'store'])->middleware('role:Admin,Vendedor');

    // Taller: administradores y técnicos
    Route::post('/taller/ordenes', [TallerController::class, 'crearOrden'])->middleware('role:Admin,Tecnico');
    Route::post('/taller/ordenes/{id}/repuestos', [TallerController::class, 'agregarRepuesto'])
        ->whereNumber('id')
        ->middleware('role:Admin,Tecnico');
});

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\OrdenServicio;
use App\Models\PedidoWeb;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Subcategoria;
use App\Models\Usuario;
use App\Models\Venta;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $resumen = [
            'productos' => Producto::count(),
            'productos_activos' => Producto::where('activo', true)->count(),
            'stock_total' => (int) Producto::sum('stock_actual'),
            'stock_bajo' => Producto::whereColumn('stock_actual', '<=', 'stock_minimo')->count(),
            'categorias' => Categoria::count(),
            'subcategorias' => Subcategoria::count(),
            'clientes' => Cliente::count(),
            'usuarios' => Usuario::count(),
            'ventas_cantidad' => Venta::count(),
            'ventas_mes_cantidad' => Venta::whereYear('fecha', now()->year)
                ->whereMonth('fecha', now()->month)
                ->count(),
            'ventas_mes' => (float) Venta::whereYear('fecha', now()->year)
                ->whereMonth('fecha', now()->month)
                ->sum('total'),
            'ventas_total' => (float) Venta::sum('total'),
            'pedidos_pendientes' => PedidoWeb::where('estado_pedido', 'Pendiente')->count(),
            'ordenes_abiertas' => OrdenServicio::whereNotIn('estado', ['Entregado', 'Cancelado'])->count(),
            'proveedores' => Proveedor::count(),
            'proveedores_con_saldo' => Proveedor::where('saldo_pendiente', '>', 0)->count(),
            'compras_cantidad' => Compra::count(),
            'compras_mes_cantidad' => Compra::whereYear('fecha_compra', now()->year)
                ->whereMonth('fecha_compra', now()->month)
                ->count(),
            'compras_mes' => (float) Compra::whereYear('fecha_compra', now()->year)
                ->whereMonth('fecha_compra', now()->month)
                ->sum('total'),
            'compras_total' => (float) Compra::sum('total'),
            'compras_abiertas' => Compra::whereIn('estado', ['Pendiente', 'Parcial'])->count(),
            'cuentas_por_pagar' => (float) Proveedor::sum('saldo_pendiente'),
        ];

        $productosRecientes = Producto::with('subcategoria')
            ->orderByDesc('id_producto')
            ->take(5)
            ->get();

        $productosStockBajo = Producto::whereColumn('stock_actual', '<=', 'stock_minimo')
            ->orderBy('stock_actual')
            ->take(5)
            ->get();

        $comprasRecientes = Compra::with('proveedor')
            ->withSum('abonos', 'monto')
            ->orderByDesc('fecha_compra')
            ->orderByDesc('id_compra')
            ->take(5)
            ->get();

        $comprasPorPagar = Compra::with('proveedor')
            ->withSum('abonos', 'monto')
            ->whereIn('estado', ['Pendiente', 'Parcial'])
            ->orderBy('fecha_vencimiento')
            ->orderBy('fecha_compra')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'resumen',
            'productosRecientes',
            'productosStockBajo',
            'comprasRecientes',
            'comprasPorPagar'
        ));
    }
}

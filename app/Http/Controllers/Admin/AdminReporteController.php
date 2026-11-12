<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\DetalleVenta;
use App\Models\OrdenServicio;
use App\Models\PedidoWeb;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminReporteController extends Controller
{
    public function index(Request $request)
    {
        [$desde, $hasta] = $this->rango($request);

        $inventario = $this->resumenInventario();
        $stockPorCategoria = $this->stockPorCategoria();
        $productosStockBajo = Producto::with('subcategoria')
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->orderBy('stock_actual')
            ->take(15)
            ->get();

        $ventas = $this->resumenVentas($desde, $hasta);
        $ventasPorDia = $this->ventasPorDia($desde, $hasta);
        $ventasPorMetodo = $this->ventasPorMetodo($desde, $hasta);
        $topProductos = $this->topProductos($desde, $hasta);

        $compras = $this->resumenCompras($desde, $hasta);
        $pedidos = $this->resumenPedidos($desde, $hasta);
        $ordenes = $this->resumenOrdenes($desde, $hasta);

        $clientesResumen = $this->resumenClientes($desde, $hasta);
        $clientesDetalle = $this->clientesPeriodoQuery($desde, $hasta)->take(15)->get();
        $proveedoresResumen = $this->resumenProveedores($desde, $hasta);
        $proveedoresDetalle = $this->proveedoresPeriodoQuery($desde, $hasta)->take(15)->get();

        return view('admin.reportes.index', compact(
            'desde', 'hasta',
            'inventario', 'stockPorCategoria', 'productosStockBajo',
            'ventas', 'ventasPorDia', 'ventasPorMetodo', 'topProductos',
            'compras', 'pedidos', 'ordenes',
            'clientesResumen', 'clientesDetalle', 'proveedoresResumen', 'proveedoresDetalle'
        ));
    }

    public function exportar(Request $request)
    {
        $tipo = (string) $request->input('tipo', 'inventario');
        [$desde, $hasta] = $this->rango($request);

        return match ($tipo) {
            'inventario' => $this->exportarInventario(),
            'ventas' => $this->exportarVentas($desde, $hasta),
            'clientes' => $this->exportarClientes($desde, $hasta),
            'proveedores' => $this->exportarProveedores($desde, $hasta),
            default => abort(404),
        };
    }

    public function pdf(Request $request)
    {
        $tipo = (string) $request->input('tipo', 'inventario');
        [$desde, $hasta] = $this->rango($request);

        return match ($tipo) {
            'inventario' => $this->pdfInventario(),
            'ventas' => $this->pdfVentasReporte($desde, $hasta),
            'clientes' => $this->pdfClientesReporte($desde, $hasta),
            'proveedores' => $this->pdfProveedoresReporte($desde, $hasta),
            default => abort(404),
        };
    }

    public function fichaTecnica(Producto $producto)
    {
        $producto->loadMissing(['subcategoria.categoria', 'atributos']);

        $atributos = $producto->atributos
            ->map(fn ($atributo) => [
                'nombre' => $atributo->nombre_atributo,
                'valor' => $atributo->valor_atributo,
            ])
            ->all();

        $pdf = Pdf::loadView('admin.productos.pdf.ficha', compact('producto', 'atributos'))
            ->setPaper('A4', 'portrait');

        $nombre = Str::slug((string) ($producto->sku ?: 'producto'), '_') ?: 'producto';

        return $pdf->download('ficha_tecnica_'.$nombre.'.pdf');
    }

    private function rango(Request $request): array
    {
        $request->validate([
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date',
        ]);

        $desde = $request->filled('desde')
            ? Carbon::parse($request->input('desde'))
            : now()->startOfMonth();

        $hasta = $request->filled('hasta')
            ? Carbon::parse($request->input('hasta'))
            : now();

        if ($desde->gt($hasta)) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        return [$desde->copy()->startOfDay(), $hasta->copy()->endOfDay()];
    }

    private function resumenInventario(): array
    {
        return [
            'productos' => Producto::count(),
            'activos' => Producto::where('activo', true)->count(),
            'unidades' => (int) Producto::sum('stock_actual'),
            'costo' => (float) DB::table('productos')->selectRaw('COALESCE(SUM(stock_actual * precio_compra), 0) as total')->value('total'),
            'venta' => (float) DB::table('productos')->selectRaw('COALESCE(SUM(stock_actual * precio_venta), 0) as total')->value('total'),
            'stock_bajo' => Producto::whereColumn('stock_actual', '<=', 'stock_minimo')->count(),
        ];
    }

    private function stockPorCategoria()
    {
        return Categoria::with(['subcategorias.productos'])
            ->orderBy('orden_visual')
            ->get()
            ->map(function (Categoria $categoria) {
                $productos = $categoria->subcategorias->flatMap->productos;

                return [
                    'nombre' => $categoria->nombre,
                    'productos' => $productos->count(),
                    'unidades' => (int) $productos->sum('stock_actual'),
                    'costo' => (float) $productos->sum(fn ($p) => (int) $p->stock_actual * (float) $p->precio_compra),
                    'venta' => (float) $productos->sum(fn ($p) => (int) $p->stock_actual * (float) $p->precio_venta),
                ];
            })
            ->sortByDesc('unidades')
            ->values();
    }

    private function ventasQuery(Carbon $desde, Carbon $hasta)
    {
        return Venta::whereDate('fecha', '>=', $desde->toDateString())
            ->whereDate('fecha', '<=', $hasta->toDateString());
    }

    private function resumenVentas(Carbon $desde, Carbon $hasta): array
    {
        $query = $this->ventasQuery($desde, $hasta);
        $cantidad = (clone $query)->count();
        $total = (float) (clone $query)->sum('total');

        return [
            'cantidad' => $cantidad,
            'total' => $total,
            'impuesto' => (float) (clone $query)->sum('impuesto'),
            'promedio' => $cantidad > 0 ? $total / $cantidad : 0.0,
        ];
    }

    private function ventasPorDia(Carbon $desde, Carbon $hasta)
    {
        return $this->ventasQuery($desde, $hasta)
            ->selectRaw('DATE(fecha) as dia, COUNT(*) as cantidad, COALESCE(SUM(total), 0) as total')
            ->groupByRaw('DATE(fecha)')
            ->orderBy('dia')
            ->get();
    }

    private function ventasPorMetodo(Carbon $desde, Carbon $hasta)
    {
        return $this->ventasQuery($desde, $hasta)
            ->selectRaw("COALESCE(metodo_pago, 'Sin definir') as metodo, COUNT(*) as cantidad, COALESCE(SUM(total), 0) as total")
            ->groupByRaw("COALESCE(metodo_pago, 'Sin definir')")
            ->orderByDesc('total')
            ->get();
    }

    private function topProductos(Carbon $desde, Carbon $hasta)
    {
        return DetalleVenta::query()
            ->join('ventas', 'detalle_ventas.id_venta', '=', 'ventas.id_venta')
            ->join('productos', 'detalle_ventas.id_producto', '=', 'productos.id_producto')
            ->whereDate('ventas.fecha', '>=', $desde->toDateString())
            ->whereDate('ventas.fecha', '<=', $hasta->toDateString())
            ->selectRaw('productos.id_producto, productos.nombre, SUM(detalle_ventas.cantidad) as unidades, SUM(detalle_ventas.cantidad * detalle_ventas.precio_unitario) as total')
            ->groupBy('productos.id_producto', 'productos.nombre')
            ->orderByDesc('unidades')
            ->take(10)
            ->get();
    }

    private function resumenCompras(Carbon $desde, Carbon $hasta): array
    {
        $query = Compra::whereDate('fecha_compra', '>=', $desde->toDateString())
            ->whereDate('fecha_compra', '<=', $hasta->toDateString());

        return [
            'cantidad' => (clone $query)->count(),
            'total' => (float) (clone $query)->sum('total'),
        ];
    }

    private function resumenPedidos(Carbon $desde, Carbon $hasta): array
    {
        $query = PedidoWeb::whereDate('fecha_pedido', '>=', $desde->toDateString())
            ->whereDate('fecha_pedido', '<=', $hasta->toDateString());

        return [
            'cantidad' => (clone $query)->count(),
            'total' => (float) (clone $query)->sum('total'),
            'pendientes' => (clone $query)->where('estado_pedido', 'Pendiente')->count(),
        ];
    }

    private function resumenOrdenes(Carbon $desde, Carbon $hasta): array
    {
        $query = OrdenServicio::whereDate('fecha_ingreso', '>=', $desde->toDateString())
            ->whereDate('fecha_ingreso', '<=', $hasta->toDateString());

        return [
            'cantidad' => (clone $query)->count(),
            'abiertas' => (clone $query)->whereNotIn('estado', ['Entregado', 'Cancelado'])->count(),
            'total' => (float) (clone $query)->sum('total_general'),
        ];
    }

    private function resumenClientes(Carbon $desde, Carbon $hasta): array
    {
        $ventasPeriodo = Venta::whereBetween('fecha', [$desde, $hasta])
            ->whereNotNull('id_cliente');

        return [
            'total' => Cliente::count(),
            'activos' => Cliente::where('activo', true)->count(),
            'inactivos' => Cliente::where('activo', false)->count(),
            'nuevos' => Cliente::whereBetween('fecha_registro', [$desde, $hasta])->count(),
            'con_ventas' => (clone $ventasPeriodo)->distinct()->count('id_cliente'),
            'con_saldo' => Cliente::where('saldo_pendiente', '>', 0)->count(),
            'saldo' => (float) Cliente::sum('saldo_pendiente'),
        ];
    }

    private function clientesPeriodoQuery(Carbon $desde, Carbon $hasta)
    {
        $ventasEnPeriodo = fn ($query) => $query->whereBetween('fecha', [$desde, $hasta]);
        $ordenesEnPeriodo = fn ($query) => $query->whereBetween('fecha_ingreso', [$desde, $hasta]);

        return Cliente::query()
            ->withCount(['ventas as ventas_periodo' => $ventasEnPeriodo])
            ->withSum(['ventas as ventas_total_periodo' => $ventasEnPeriodo], 'total')
            ->withCount(['ordenes as ordenes_periodo' => $ordenesEnPeriodo])
            ->orderByDesc('ventas_total_periodo')
            ->orderBy('nombre');
    }

    private function resumenProveedores(Carbon $desde, Carbon $hasta): array
    {
        $comprasPeriodo = Compra::whereBetween('fecha_compra', [$desde, $hasta]);

        return [
            'total' => Proveedor::count(),
            'activos' => Proveedor::where('activo', true)->count(),
            'inactivos' => Proveedor::where('activo', false)->count(),
            'compras' => (clone $comprasPeriodo)->count(),
            'comprado' => (float) (clone $comprasPeriodo)->sum('total'),
            'con_saldo' => Proveedor::where('saldo_pendiente', '>', 0)->count(),
            'saldo' => (float) Proveedor::sum('saldo_pendiente'),
        ];
    }

    private function proveedoresPeriodoQuery(Carbon $desde, Carbon $hasta)
    {
        $comprasEnPeriodo = fn ($query) => $query->whereBetween('fecha_compra', [$desde, $hasta]);

        return Proveedor::query()
            ->withCount(['compras as compras_periodo' => $comprasEnPeriodo])
            ->withSum(['compras as compras_total_periodo' => $comprasEnPeriodo], 'total')
            ->orderByDesc('compras_total_periodo')
            ->orderBy('nombre');
    }

    private function exportarInventario()
    {
        $productos = Producto::with('subcategoria.categoria')->orderBy('id_producto')->get();

        $callback = function () use ($productos) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, [
                'ID', 'Producto', 'SKU', 'Marca', 'Categoria', 'Subcategoria',
                'Stock', 'Stock minimo', 'Costo unitario', 'Valor costo',
                'Precio venta', 'Valor venta', 'Estado',
            ]);

            foreach ($productos as $producto) {
                fputcsv($salida, array_map([$this, 'csvSeguro'], [
                    $producto->id_producto,
                    $producto->nombre,
                    $producto->sku,
                    $producto->marca,
                    $producto->subcategoria?->categoria?->nombre,
                    $producto->subcategoria?->nombre,
                    $producto->stock_actual,
                    $producto->stock_minimo,
                    number_format((float) $producto->precio_compra, 2, '.', ''),
                    number_format((int) $producto->stock_actual * (float) $producto->precio_compra, 2, '.', ''),
                    number_format((float) $producto->precio_venta, 2, '.', ''),
                    number_format((int) $producto->stock_actual * (float) $producto->precio_venta, 2, '.', ''),
                    $producto->activo ? 'Activo' : 'Inactivo',
                ]));
            }

            fclose($salida);
        };

        return response()->streamDownload(
            $callback,
            'inventario_'.now()->format('Ymd_His').'.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    private function pdfInventario()
    {
        $inventario = $this->resumenInventario();
        $stockPorCategoria = $this->stockPorCategoria();
        $productosStockBajo = Producto::with('subcategoria')->whereColumn('stock_actual', '<=', 'stock_minimo')->orderBy('stock_actual')->get();

        $pdf = Pdf::loadView('admin.reportes.pdf.inventario', compact('inventario', 'stockPorCategoria', 'productosStockBajo'))
            ->setPaper('A4', 'landscape');

        return $pdf->download('reporte_inventario_'.now()->format('Ymd_His').'.pdf');
    }

    private function pdfVentasReporte(Carbon $desde, Carbon $hasta)
    {
        $ventas = $this->resumenVentas($desde, $hasta);
        $ventasPorDia = $this->ventasPorDia($desde, $hasta);
        $ventasPorMetodo = $this->ventasPorMetodo($desde, $hasta);
        $topProductos = $this->topProductos($desde, $hasta);

        $pdf = Pdf::loadView('admin.reportes.pdf.ventas', compact('desde', 'hasta', 'ventas', 'ventasPorDia', 'ventasPorMetodo', 'topProductos'))
            ->setPaper('A4', 'portrait');

        return $pdf->download('reporte_ventas_'.$desde->format('Ymd').'_'.$hasta->format('Ymd').'.pdf');
    }

    private function exportarVentas(Carbon $desde, Carbon $hasta)
    {
        $ventas = $this->ventasQuery($desde, $hasta)
            ->with('cliente')
            ->orderBy('id_venta')
            ->get();

        $callback = function () use ($ventas) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, [
                'ID', 'Fecha', 'Cliente', 'Subtotal', 'Impuesto', 'Total', 'Metodo pago', 'Estado',
            ]);

            foreach ($ventas as $venta) {
                fputcsv($salida, array_map([$this, 'csvSeguro'], [
                    $venta->id_venta,
                    $venta->fecha?->format('Y-m-d H:i'),
                    $venta->cliente?->nombre,
                    number_format((float) $venta->subtotal, 2, '.', ''),
                    number_format((float) $venta->impuesto, 2, '.', ''),
                    number_format((float) $venta->total, 2, '.', ''),
                    $venta->metodo_pago,
                    $venta->estado,
                ]));
            }

            fclose($salida);
        };

        return response()->streamDownload(
            $callback,
            'ventas_'.$desde->format('Ymd').'_'.$hasta->format('Ymd').'.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    private function exportarClientes(Carbon $desde, Carbon $hasta)
    {
        $clientes = $this->clientesPeriodoQuery($desde, $hasta)->get();

        $callback = function () use ($clientes) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, [
                'ID', 'Cliente', 'DNI/RUC', 'Telefono', 'Email', 'Estado', 'Registrado',
                'Ventas periodo', 'Total vendido', 'Ordenes periodo', 'Saldo pendiente',
            ]);

            foreach ($clientes as $cliente) {
                fputcsv($salida, array_map([$this, 'csvSeguro'], [
                    $cliente->id_cliente,
                    $cliente->nombre,
                    $cliente->dni_ruc,
                    $cliente->telefono,
                    $cliente->email,
                    $cliente->activo ? 'Activo' : 'Inactivo',
                    $cliente->fecha_registro?->format('Y-m-d'),
                    (int) $cliente->ventas_periodo,
                    number_format((float) $cliente->ventas_total_periodo, 2, '.', ''),
                    (int) $cliente->ordenes_periodo,
                    number_format((float) $cliente->saldo_pendiente, 2, '.', ''),
                ]));
            }

            fclose($salida);
        };

        return response()->streamDownload(
            $callback,
            'clientes_'.$desde->format('Ymd').'_'.$hasta->format('Ymd').'.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    private function exportarProveedores(Carbon $desde, Carbon $hasta)
    {
        $proveedores = $this->proveedoresPeriodoQuery($desde, $hasta)->get();

        $callback = function () use ($proveedores) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, [
                'ID', 'Proveedor', 'RUC', 'Telefono', 'Email', 'Estado',
                'Compras periodo', 'Total comprado', 'Saldo pendiente',
            ]);

            foreach ($proveedores as $proveedor) {
                fputcsv($salida, array_map([$this, 'csvSeguro'], [
                    $proveedor->id_proveedor,
                    $proveedor->nombre,
                    $proveedor->ruc,
                    $proveedor->telefono,
                    $proveedor->email,
                    $proveedor->activo ? 'Activo' : 'Inactivo',
                    (int) $proveedor->compras_periodo,
                    number_format((float) $proveedor->compras_total_periodo, 2, '.', ''),
                    number_format((float) $proveedor->saldo_pendiente, 2, '.', ''),
                ]));
            }

            fclose($salida);
        };

        return response()->streamDownload(
            $callback,
            'proveedores_'.$desde->format('Ymd').'_'.$hasta->format('Ymd').'.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    private function pdfClientesReporte(Carbon $desde, Carbon $hasta)
    {
        $resumen = $this->resumenClientes($desde, $hasta);
        $clientes = $this->clientesPeriodoQuery($desde, $hasta)->get();

        $pdf = Pdf::loadView('admin.reportes.pdf.clientes', compact('desde', 'hasta', 'resumen', 'clientes'))
            ->setPaper('A4', 'landscape');

        return $pdf->download('reporte_clientes_'.$desde->format('Ymd').'_'.$hasta->format('Ymd').'.pdf');
    }

    private function pdfProveedoresReporte(Carbon $desde, Carbon $hasta)
    {
        $resumen = $this->resumenProveedores($desde, $hasta);
        $proveedores = $this->proveedoresPeriodoQuery($desde, $hasta)->get();

        $pdf = Pdf::loadView('admin.reportes.pdf.proveedores', compact('desde', 'hasta', 'resumen', 'proveedores'))
            ->setPaper('A4', 'landscape');

        return $pdf->download('reporte_proveedores_'.$desde->format('Ymd').'_'.$hasta->format('Ymd').'.pdf');
    }

    private function csvSeguro(mixed $valor): mixed
    {
        if (! is_string($valor)) {
            return $valor;
        }

        $limpio = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $valor);

        if ($limpio !== null) {
            $valor = $limpio;
        }

        if ($valor === '' || is_numeric($valor)) {
            return $valor;
        }

        return in_array($valor[0], ['=', '+', '-', '@', "\t", "\r"], true)
            ? "'".$valor
            : $valor;
    }
}

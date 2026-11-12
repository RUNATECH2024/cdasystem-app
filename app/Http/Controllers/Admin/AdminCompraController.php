<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Abono;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Proveedor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminCompraController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'q' => 'nullable|string|max:100',
            'estado' => 'nullable|in:Pagada,Pendiente,Parcial',
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date',
        ]);

        $compras = Compra::query()
            ->with('proveedor')
            ->withCount('detalles')
            ->withSum('abonos', 'monto')
            ->when($filtros['q'] ?? null, function ($query, $termino) {
                $query->where(function ($query) use ($termino) {
                    if (ctype_digit($termino)) {
                        $query->where('id_compra', $termino);
                    }

                    $query->orWhere('numero_factura', 'like', "%{$termino}%")
                        ->orWhereHas('proveedor', fn ($proveedor) => $proveedor
                            ->where('nombre', 'like', "%{$termino}%")
                            ->orWhere('ruc', 'like', "%{$termino}%"));
                });
            })
            ->when($filtros['estado'] ?? null, fn ($query, $estado) => $query->where('estado', $estado))
            ->when($filtros['desde'] ?? null, fn ($query, $desde) => $query->whereDate('fecha_compra', '>=', $desde))
            ->when($filtros['hasta'] ?? null, fn ($query, $hasta) => $query->whereDate('fecha_compra', '<=', $hasta))
            ->orderByDesc('fecha_compra')
            ->orderByDesc('id_compra')
            ->paginate(15)
            ->withQueryString();

        return view('admin.compras.index', [
            'compras' => $compras,
            'q' => $filtros['q'] ?? '',
            'estado' => $filtros['estado'] ?? '',
            'desde' => $filtros['desde'] ?? '',
            'hasta' => $filtros['hasta'] ?? '',
        ]);
    }

    public function create(Request $request)
    {
        $proveedorSeleccionado = $request->integer('proveedor') ?: null;

        return view('admin.compras.create', [
            'proveedores' => Proveedor::orderBy('nombre')->get(['id_proveedor', 'nombre', 'ruc', 'activo']),
            'productos' => Producto::orderBy('nombre')->get(['id_producto', 'nombre', 'sku', 'precio_compra', 'stock_actual']),
            'proveedorSeleccionado' => $proveedorSeleccionado,
        ]);
    }

    public function store(Request $request)
    {
        [$datos, $subtotal, $impuesto, $total, $pagado] = $this->validarCompra($request);

        $compra = DB::transaction(function () use ($datos, $subtotal, $impuesto, $total, $pagado) {
            $proveedor = Proveedor::whereKey($datos['id_proveedor'])->lockForUpdate()->firstOrFail();
            $usuarioId = Auth::guard('admin')->id();

            $compra = Compra::create([
                'id_proveedor' => $proveedor->id_proveedor,
                'id_usuario' => $usuarioId,
                'numero_factura' => $datos['numero_factura'] ?? null,
                'fecha_compra' => $datos['fecha_compra'],
                'fecha_vencimiento' => $datos['fecha_vencimiento'] ?? null,
                'subtotal' => $subtotal,
                'impuesto' => $impuesto,
                'total' => $total,
                'estado' => $this->estadoPago($total, $pagado),
            ]);

            foreach ($datos['productos'] as $linea) {
                $producto = Producto::whereKey($linea['id_producto'])->lockForUpdate()->firstOrFail();
                $cantidad = (int) $linea['cantidad'];
                $costoUnitario = round((float) $linea['precio_costo_unitario'], 2);

                DetalleCompra::create([
                    'id_compra' => $compra->id_compra,
                    'id_producto' => $producto->id_producto,
                    'cantidad' => $cantidad,
                    'precio_costo_unitario' => $costoUnitario,
                ]);

                $stockAnterior = (int) $producto->stock_actual;
                $producto->stock_actual = $stockAnterior + $cantidad;
                $producto->precio_compra = $costoUnitario;
                $producto->save();

                MovimientoInventario::create([
                    'id_producto' => $producto->id_producto,
                    'tipo_movimiento' => 'ENTRADA',
                    'origen' => 'COMPRA',
                    'cantidad' => $cantidad,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $producto->stock_actual,
                    'motivo' => 'Compra #'.$compra->id_compra,
                    'id_referencia' => $compra->id_compra,
                    'id_usuario' => $usuarioId,
                    'fecha_movimiento' => now(),
                ]);
            }

            if ($pagado > 0) {
                Abono::create([
                    'id_compra' => $compra->id_compra,
                    'monto' => $pagado,
                    'fecha_abono' => now(),
                    'metodo_pago' => $datos['metodo_pago'] ?? 'Efectivo',
                    'id_usuario' => $usuarioId,
                ]);
            }

            $proveedor->update([
                'saldo_pendiente' => round((float) $proveedor->saldo_pendiente + ($total - $pagado), 2),
            ]);

            return $compra;
        });

        return redirect()
            ->route('admin.compras.show', $compra)
            ->with('success', 'Compra registrada y stock actualizado correctamente.');
    }

    public function show(Compra $compra)
    {
        $compra->load(['proveedor', 'usuario', 'detalles.producto', 'abonos.usuario']);

        $pagado = (float) $compra->abonos->sum('monto');

        return view('admin.compras.show', [
            'compra' => $compra,
            'pagado' => $pagado,
            'saldo' => max(0, round((float) $compra->total - $pagado, 2)),
        ]);
    }

    public function registrarAbono(Request $request, Compra $compra)
    {
        $datos = $request->validate([
            'monto' => 'required|numeric|min:0.01|max:9999999999',
            'metodo_pago' => 'nullable|string|max:50',
            'fecha_abono' => 'nullable|date',
        ]);

        $monto = round((float) $datos['monto'], 2);

        DB::transaction(function () use ($compra, $datos, $monto) {
            $compra = Compra::whereKey($compra->id_compra)->lockForUpdate()->firstOrFail();
            $pagado = (float) $compra->abonos()->sum('monto');
            $saldo = round((float) $compra->total - $pagado, 2);

            if ($saldo <= 0) {
                throw ValidationException::withMessages([
                    'monto' => 'Esta compra ya está pagada.',
                ]);
            }

            if ($monto > $saldo + 0.001) {
                throw ValidationException::withMessages([
                    'monto' => 'El abono no puede superar el saldo pendiente de $'.number_format($saldo, 2).'.',
                ]);
            }

            $usuarioId = Auth::guard('admin')->id();

            Abono::create([
                'id_compra' => $compra->id_compra,
                'monto' => $monto,
                'fecha_abono' => $datos['fecha_abono'] ?? now(),
                'metodo_pago' => $datos['metodo_pago'] ?? 'Efectivo',
                'id_usuario' => $usuarioId,
            ]);

            $proveedor = Proveedor::whereKey($compra->id_proveedor)->lockForUpdate()->firstOrFail();
            $proveedor->update([
                'saldo_pendiente' => max(0, round((float) $proveedor->saldo_pendiente - $monto, 2)),
            ]);

            $compra->update([
                'estado' => $this->estadoPago((float) $compra->total, round($pagado + $monto, 2)),
            ]);
        });

        return back()->with('success', 'Abono registrado correctamente.');
    }

    public function pdf(Compra $compra)
    {
        $compra->load(['proveedor', 'detalles.producto', 'abonos.usuario']);

        $pagado = (float) $compra->abonos->sum('monto');
        $saldo = max(0, round((float) $compra->total - $pagado, 2));

        $pdf = Pdf::loadView('admin.compras.pdf', compact('compra', 'pagado', 'saldo'))
            ->setPaper('A4', 'portrait');

        return $pdf->download('compra_'.$compra->id_compra.'_'.now()->format('Ymd_His').'.pdf');
    }

    private function validarCompra(Request $request): array
    {
        if (! $request->filled('fecha_compra')) {
            $request->merge(['fecha_compra' => now()->toDateString()]);
        }

        $datos = $request->validate([
            'id_proveedor' => 'required|integer|exists:proveedores,id_proveedor',
            'numero_factura' => 'nullable|string|max:50',
            'fecha_compra' => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_compra',
            'impuesto' => 'nullable|numeric|min:0|max:9999999999',
            'monto_pagado' => 'nullable|numeric|min:0|max:9999999999',
            'metodo_pago' => 'nullable|string|max:50',
            'productos' => 'required|array|min:1|max:100',
            'productos.*.id_producto' => 'required|integer|distinct|exists:productos,id_producto',
            'productos.*.cantidad' => 'required|integer|min:1|max:1000000',
            'productos.*.precio_costo_unitario' => 'required|numeric|min:0|max:9999999999',
        ]);

        $subtotal = 0.0;

        foreach ($datos['productos'] as $linea) {
            $subtotal += (int) $linea['cantidad'] * (float) $linea['precio_costo_unitario'];
        }

        $subtotal = round($subtotal, 2);
        $impuesto = round((float) ($datos['impuesto'] ?? 0), 2);
        $total = round($subtotal + $impuesto, 2);
        $pagado = round((float) ($datos['monto_pagado'] ?? 0), 2);

        if ($pagado > $total + 0.001) {
            throw ValidationException::withMessages([
                'monto_pagado' => 'El pago inicial no puede superar el total de la compra.',
            ]);
        }

        return [$datos, $subtotal, $impuesto, $total, $pagado];
    }

    private function estadoPago(float $total, float $pagado): string
    {
        if ($pagado >= $total) {
            return 'Pagada';
        }

        return $pagado > 0 ? 'Parcial' : 'Pendiente';
    }
}

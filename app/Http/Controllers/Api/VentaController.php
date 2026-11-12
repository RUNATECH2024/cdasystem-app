<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVentaRequest;
use App\Http\Resources\VentaResource;
use App\Models\DetalleVenta;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VentaController extends Controller
{
    public function store(StoreVentaRequest $request)
    {
        $validated = $request->validated();

        $vendedorId = $request->user()->getKey();

        $venta = DB::transaction(function () use ($validated, $vendedorId) {
            $cantidades = [];
            foreach ($validated['items'] as $item) {
                $id = $item['id_producto'];
                $cantidades[$id] = ($cantidades[$id] ?? 0) + $item['cantidad'];
            }

            $subtotal = 0;
            $lineas = [];

            foreach ($cantidades as $idProducto => $cantidad) {
                $producto = Producto::whereKey($idProducto)->lockForUpdate()->firstOrFail();

                if (! $producto->activo) {
                    throw ValidationException::withMessages([
                        'items' => "El producto {$producto->nombre} no está disponible.",
                    ]);
                }

                if ($producto->stock_actual < $cantidad) {
                    throw ValidationException::withMessages([
                        'items' => "Stock insuficiente para {$producto->nombre} (disponible: {$producto->stock_actual}).",
                    ]);
                }

                $precioUnitario = (float) $producto->precio_venta;
                $subtotal += $precioUnitario * $cantidad;

                $lineas[] = [
                    'producto' => $producto,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                ];
            }

            $impuesto = (float) ($validated['impuesto'] ?? 0);

            $venta = Venta::create([
                'id_cliente' => $validated['id_cliente'] ?? null,
                'id_vendedor' => $vendedorId,
                'fecha' => now(),
                'subtotal' => $subtotal,
                'impuesto' => $impuesto,
                'total' => $subtotal + $impuesto,
                'metodo_pago' => $validated['metodo_pago'] ?? 'Efectivo',
                'estado' => $validated['estado'] ?? 'Pagada',
            ]);

            foreach ($lineas as $linea) {
                $producto = $linea['producto'];

                DetalleVenta::create([
                    'id_venta' => $venta->id_venta,
                    'id_producto' => $producto->id_producto,
                    'cantidad' => $linea['cantidad'],
                    'precio_unitario' => $linea['precio_unitario'],
                ]);

                $stockAnterior = $producto->stock_actual;
                $producto->stock_actual = $stockAnterior - $linea['cantidad'];
                $producto->save();

                MovimientoInventario::create([
                    'id_producto' => $producto->id_producto,
                    'tipo_movimiento' => 'SALIDA',
                    'origen' => 'VENTA',
                    'cantidad' => $linea['cantidad'],
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $producto->stock_actual,
                    'motivo' => 'Venta #'.$venta->id_venta,
                    'id_referencia' => $venta->id_venta,
                    'id_usuario' => $vendedorId,
                    'fecha_movimiento' => now(),
                ]);
            }

            return $venta->load('detalles');
        });

        return response()->json([
            'mensaje' => 'Venta registrada correctamente',
            'venta' => new VentaResource($venta),
        ], 201);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrdenServicioRequest;
use App\Http\Requests\StoreRepuestoRequest;
use App\Http\Resources\OrdenServicioResource;
use App\Models\DetalleRepuestosOs;
use App\Models\EquipoCliente;
use App\Models\MovimientoInventario;
use App\Models\OrdenServicio;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TallerController extends Controller
{
    public function crearOrden(StoreOrdenServicioRequest $request)
    {
        $validated = $request->validated();

        $equipo = EquipoCliente::findOrFail($validated['id_equipo']);

        if ((int) $equipo->id_cliente !== (int) $validated['id_cliente']) {
            throw ValidationException::withMessages([
                'id_equipo' => 'El equipo no pertenece al cliente indicado.',
            ]);
        }

        $manoObra = (float) ($validated['total_mano_obra'] ?? 0);
        $idTecnico = $validated['id_tecnico'] ?? null;

        if ($idTecnico === null && $request->user()->rol === 'Tecnico') {
            $idTecnico = $request->user()->getKey();
        }

        $orden = OrdenServicio::create([
            'id_cliente' => $validated['id_cliente'],
            'id_equipo' => $validated['id_equipo'],
            'id_tecnico' => $idTecnico,
            'fecha_ingreso' => now(),
            'falla_reportada' => $validated['falla_reportada'],
            'diagnostico' => $validated['diagnostico'] ?? null,
            'contador_ingreso' => $validated['contador_ingreso'] ?? null,
            'estado' => 'Pendiente',
            'total_mano_obra' => $manoObra,
            'total_repuestos' => 0,
            'total_general' => $manoObra,
        ]);

        return response()->json([
            'mensaje' => 'Orden de servicio creada',
            'orden' => new OrdenServicioResource($orden),
        ], 201);
    }

    public function agregarRepuesto(StoreRepuestoRequest $request, $id)
    {
        $validated = $request->validated();
        $id = (int) $id;

        $usuarioId = $request->user()->getKey();

        $orden = DB::transaction(function () use ($validated, $id, $usuarioId) {
            $orden = OrdenServicio::whereKey($id)->lockForUpdate()->firstOrFail();

            if (! in_array($orden->estado, ['Pendiente', 'En Revisión', 'Esperando Repuesto'], true)) {
                throw ValidationException::withMessages([
                    'id_orden' => 'No se pueden agregar repuestos a una orden completada o cancelada.',
                ]);
            }

            $producto = Producto::whereKey($validated['id_producto'])->lockForUpdate()->firstOrFail();

            if (! $producto->activo) {
                throw ValidationException::withMessages([
                    'id_producto' => "El producto {$producto->nombre} no está disponible.",
                ]);
            }

            if ($producto->stock_actual < $validated['cantidad']) {
                throw ValidationException::withMessages([
                    'cantidad' => "Stock insuficiente para {$producto->nombre} (disponible: {$producto->stock_actual}).",
                ]);
            }

            $precioUnitario = (float) $producto->precio_venta;

            DetalleRepuestosOs::create([
                'id_orden' => $orden->id_orden,
                'id_producto' => $producto->id_producto,
                'cantidad' => $validated['cantidad'],
                'precio_unitario' => $precioUnitario,
            ]);

            $stockAnterior = $producto->stock_actual;
            $producto->stock_actual = $stockAnterior - $validated['cantidad'];
            $producto->save();

            MovimientoInventario::create([
                'id_producto' => $producto->id_producto,
                'tipo_movimiento' => 'SALIDA',
                'origen' => 'TALLER',
                'cantidad' => $validated['cantidad'],
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $producto->stock_actual,
                'motivo' => 'Orden de servicio #'.$orden->id_orden,
                'id_referencia' => $orden->id_orden,
                'id_usuario' => $usuarioId,
                'fecha_movimiento' => now(),
            ]);

            $orden->total_repuestos = (float) $orden->total_repuestos + ($precioUnitario * $validated['cantidad']);
            $orden->total_general = (float) $orden->total_mano_obra + (float) $orden->total_repuestos;
            $orden->save();

            return $orden->load('repuestos');
        });

        return response()->json([
            'mensaje' => 'Repuesto agregado a la orden',
            'orden' => new OrdenServicioResource($orden),
        ], 201);
    }
}

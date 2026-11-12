<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\EquipoCliente;
use App\Models\MovimientoInventario;
use App\Models\OrdenServicio;
use App\Models\PedidoWeb;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminOperacionController extends Controller
{
    public function verPedido(PedidoWeb $pedido)
    {
        $pedido->load([
            'cliente',
            'detalles.producto.atributos',
            'detalles.producto.subcategoria.categoria',
            'detalles.garantias',
        ]);

        return view('admin.pedidos.show', compact('pedido'));
    }

    public function marcarPedidoAtendido(PedidoWeb $pedido)
    {
        abort_unless(in_array($pedido->estado_pedido, ['Pendiente', 'Pagado'], true), 422);

        $pedido->update(['estado_pedido' => 'Preparando']);

        return back()->with('success', 'El pedido pasó a preparación.');
    }

    public function verOrden(OrdenServicio $orden)
    {
        $orden->load(['cliente', 'equipo', 'tecnico', 'repuestos.producto']);

        return view('admin.ordenes.show', compact('orden'));
    }

    public function crearOrden()
    {
        $clientes = Cliente::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id_cliente', 'nombre', 'dni_ruc']);
        $equipos = EquipoCliente::with('cliente')
            ->whereHas('cliente', fn ($query) => $query->where('activo', true))
            ->orderBy('id_equipo')
            ->get();
        $tecnicos = Usuario::query()
            ->where('activo', true)
            ->where('rol', 'Tecnico')
            ->orderBy('nombre')
            ->get(['id_usuario', 'nombre']);

        return view('admin.ordenes.create', compact('clientes', 'equipos', 'tecnicos'));
    }

    public function guardarOrden(Request $request)
    {
        $datos = $request->validate([
            'id_cliente' => 'required|integer|exists:clientes,id_cliente',
            'id_equipo' => 'required|integer|exists:equipos_cliente,id_equipo',
            'id_tecnico' => [
                'nullable',
                Rule::exists('usuarios', 'id_usuario')->where(fn ($query) => $query
                    ->where('rol', 'Tecnico')
                    ->where('activo', true)),
            ],
            'falla_reportada' => 'required|string|max:5000',
            'diagnostico' => 'nullable|string|max:5000',
            'contador_ingreso' => 'nullable|integer|min:0',
            'total_mano_obra' => 'required|numeric|min:0|max:99999999.99',
        ]);

        $equipoValido = EquipoCliente::where('id_equipo', $datos['id_equipo'])
            ->where('id_cliente', $datos['id_cliente'])
            ->exists();

        if (! $equipoValido) {
            throw ValidationException::withMessages([
                'id_equipo' => 'El equipo seleccionado no pertenece al cliente indicado.',
            ]);
        }

        $manoObra = round((float) $datos['total_mano_obra'], 2);
        $orden = OrdenServicio::create([
            'id_cliente' => $datos['id_cliente'],
            'id_equipo' => $datos['id_equipo'],
            'id_tecnico' => $datos['id_tecnico'] ?? null,
            'fecha_ingreso' => now(),
            'falla_reportada' => $datos['falla_reportada'],
            'diagnostico' => $datos['diagnostico'] ?? null,
            'contador_ingreso' => $datos['contador_ingreso'] ?? null,
            'estado' => 'Pendiente',
            'total_mano_obra' => $manoObra,
            'total_repuestos' => 0,
            'total_general' => $manoObra,
        ]);

        return redirect()->route('admin.ordenes.show', $orden)->with('success', 'Orden de taller creada.');
    }

    public function pdfOrden(OrdenServicio $orden)
    {
        $orden->load(['cliente', 'equipo', 'tecnico', 'repuestos.producto']);

        return Pdf::loadView('admin.ordenes.pdf', compact('orden'))
            ->setPaper('A4', 'portrait')
            ->download('orden_taller_'.$orden->id_orden.'.pdf');
    }

    public function editarOrden(OrdenServicio $orden)
    {
        $tecnicos = Usuario::where('activo', true)
            ->where('rol', 'Tecnico')
            ->orderBy('nombre')
            ->get();

        return view('admin.ordenes.edit', compact('orden', 'tecnicos'));
    }

    public function actualizarOrden(Request $request, OrdenServicio $orden)
    {
        $orden->update($request->validate([
            'estado' => ['required', Rule::in(['Pendiente', 'En Revisión', 'Esperando Repuesto', 'Reparado', 'Entregado', 'Cancelado'])],
            'id_tecnico' => [
                'nullable',
                Rule::exists('usuarios', 'id_usuario')->where(fn ($query) => $query
                    ->where('rol', 'Tecnico')
                    ->where('activo', true)),
            ],
            'diagnostico' => 'nullable|string|max:5000',
            'fecha_entrega' => 'nullable|date',
            'contador_salida' => 'nullable|integer|min:0',
        ]));

        return redirect()->route('admin.ordenes.show', $orden)->with('success', 'Orden de taller actualizada.');
    }

    public function eliminarOrden(OrdenServicio $orden)
    {
        $tieneMovimientos = MovimientoInventario::where('origen', 'TALLER')
            ->where('id_referencia', $orden->id_orden)
            ->exists();

        if ($orden->repuestos()->exists() || $tieneMovimientos) {
            return back()->with('error', 'No se puede eliminar esta orden porque tiene repuestos o movimientos de inventario asociados.');
        }

        $orden->delete();

        return redirect()->route('admin.ordenes.index')->with('success', 'Orden de taller eliminada.');
    }

    public function pedidos(Request $request)
    {
        $filtros = $request->validate([
            'q' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:30',
        ]);

        $pedidos = PedidoWeb::with(['cliente', 'detalles.producto'])
            ->when($filtros['q'] ?? null, function ($query, $termino) {
                $query->where(function ($query) use ($termino) {
                    if (ctype_digit($termino)) {
                        $query->where('id_pedido', $termino);
                    }

                    $query->orWhere('nombre_contacto', 'like', "%{$termino}%")
                        ->orWhere('email_contacto', 'like', "%{$termino}%")
                        ->orWhere('telefono_contacto', 'like', "%{$termino}%")
                        ->orWhereHas('cliente', fn ($cliente) => $cliente->where('nombre', 'like', "%{$termino}%"));
                });
            })
            ->when($filtros['estado'] ?? null, fn ($query, $estado) => $query->where('estado_pedido', $estado))
            ->orderByDesc('fecha_pedido')
            ->orderByDesc('id_pedido')
            ->paginate(15)
            ->withQueryString();

        $estados = PedidoWeb::query()
            ->whereNotNull('estado_pedido')
            ->distinct()
            ->orderBy('estado_pedido')
            ->pluck('estado_pedido');

        return view('admin.pedidos.index', [
            'pedidos' => $pedidos,
            'estados' => $estados,
            'q' => $filtros['q'] ?? '',
            'estado' => $filtros['estado'] ?? '',
        ]);
    }

    public function ordenes(Request $request)
    {
        $filtros = $request->validate([
            'q' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:30',
        ]);

        $ordenes = OrdenServicio::with(['cliente', 'equipo', 'tecnico', 'repuestos.producto'])
            ->when($filtros['q'] ?? null, function ($query, $termino) {
                $query->where(function ($query) use ($termino) {
                    if (ctype_digit($termino)) {
                        $query->where('id_orden', $termino);
                    }

                    $query->orWhere('falla_reportada', 'like', "%{$termino}%")
                        ->orWhereHas('cliente', fn ($cliente) => $cliente->where('nombre', 'like', "%{$termino}%"))
                        ->orWhereHas('equipo', fn ($equipo) => $equipo
                            ->where('marca', 'like', "%{$termino}%")
                            ->orWhere('modelo', 'like', "%{$termino}%")
                            ->orWhere('numero_serie', 'like', "%{$termino}%"));
                });
            })
            ->when($filtros['estado'] ?? null, fn ($query, $estado) => $query->where('estado', $estado))
            ->orderByDesc('fecha_ingreso')
            ->orderByDesc('id_orden')
            ->paginate(15)
            ->withQueryString();

        $estados = OrdenServicio::query()
            ->whereNotNull('estado')
            ->distinct()
            ->orderBy('estado')
            ->pluck('estado');

        return view('admin.ordenes.index', [
            'ordenes' => $ordenes,
            'estados' => $estados,
            'q' => $filtros['q'] ?? '',
            'estado' => $filtros['estado'] ?? '',
        ]);
    }
}

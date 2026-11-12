<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\EquipoCliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminClienteController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'q' => 'nullable|string|max:100',
            'estado' => 'nullable|in:activos,inactivos',
        ]);

        $clientes = Cliente::query()
            ->withCount(['equipos', 'ordenes', 'pedidos', 'ventas'])
            ->when($filtros['q'] ?? null, function ($query, $termino) {
                $query->where(function ($query) use ($termino) {
                    $query->where('nombre', 'like', "%{$termino}%")
                        ->orWhere('dni_ruc', 'like', "%{$termino}%")
                        ->orWhere('email', 'like', "%{$termino}%")
                        ->orWhere('telefono', 'like', "%{$termino}%")
                        ->orWhereHas('equipos', fn ($equipo) => $equipo->where('numero_serie', 'like', "%{$termino}%"));
                });
            })
            ->when(($filtros['estado'] ?? null) === 'activos', fn ($query) => $query->where('activo', true))
            ->when(($filtros['estado'] ?? null) === 'inactivos', fn ($query) => $query->where('activo', false))
            ->orderByDesc('id_cliente')
            ->paginate(15)
            ->withQueryString();

        return view('admin.clientes.index', [
            'clientes' => $clientes,
            'q' => $filtros['q'] ?? '',
            'estado' => $filtros['estado'] ?? '',
        ]);
    }

    public function create()
    {
        return view('admin.clientes.create');
    }

    public function show(Cliente $cliente)
    {
        $cliente->load([
            'equipos' => fn ($equipos) => $equipos->withCount('ordenes')->orderBy('id_equipo'),
            'ordenes' => fn ($ordenes) => $ordenes->with('equipo')->latest('fecha_ingreso')->take(10),
            'pedidos' => fn ($pedidos) => $pedidos->latest('fecha_pedido')->take(10),
        ]);

        return view('admin.clientes.show', [
            'cliente' => $cliente,
            'totalOrdenes' => $cliente->ordenes()->count(),
            'totalPedidos' => $cliente->pedidos()->count(),
            'totalVentas' => $cliente->ventas()->count(),
        ]);
    }

    public function edit(Cliente $cliente)
    {
        $cliente->load('equipos');

        return view('admin.clientes.edit', compact('cliente'));
    }

    public function store(Request $request)
    {
        $email = $this->normalizarEmail($request);

        $datos = $request->validate([
            'nombre' => 'required|string|max:100',
            'dni_ruc' => 'nullable|string|max:20|unique:clientes,dni_ruc',
            'telefono' => 'required|string|max:20',
            'email' => [
                'nullable', 'string', 'email', 'max:100',
                Rule::unique('clientes', 'email')
                    ->where(fn ($query) => $query->whereRaw('LOWER(email) = ?', [$email])),
            ],
            'direccion' => 'nullable|string|max:150',
            'tipo_equipo' => 'nullable|required_with:marca,modelo,numero_serie,contador_paginas,observaciones|string|max:30',
            'marca' => 'nullable|string|max:50',
            'modelo' => 'nullable|string|max:50',
            'numero_serie' => 'nullable|string|max:50|unique:equipos_cliente,numero_serie',
            'contador_paginas' => 'nullable|integer|min:0|max:100000000',
            'observaciones' => 'nullable|string|max:2000',
        ]);

        [$cliente, $equipo] = DB::transaction(function () use ($datos) {
            $cliente = Cliente::create([
                'nombre' => $datos['nombre'],
                'dni_ruc' => $datos['dni_ruc'] ?? null,
                'telefono' => $datos['telefono'],
                'email' => $datos['email'] ?? null,
                'direccion' => $datos['direccion'] ?? null,
                'activo' => true,
            ]);

            $equipo = null;

            if (! empty($datos['tipo_equipo'])) {
                $equipo = EquipoCliente::create([
                    'id_cliente' => $cliente->id_cliente,
                    'tipo_equipo' => $datos['tipo_equipo'],
                    'marca' => $datos['marca'] ?? null,
                    'modelo' => $datos['modelo'] ?? null,
                    'numero_serie' => $datos['numero_serie'] ?? null,
                    'contador_paginas' => $datos['contador_paginas'] ?? 0,
                    'observaciones' => $datos['observaciones'] ?? null,
                ]);
            }

            return [$cliente, $equipo];
        });

        $parametros = ['cliente' => $cliente->id_cliente];

        if ($equipo) {
            $parametros['equipo'] = $equipo->id_equipo;
        }

        return redirect()
            ->route('admin.ordenes.create', $parametros)
            ->with('success', 'Cliente registrado. Completa los datos de la orden de taller.');
    }

    public function update(Request $request, Cliente $cliente)
    {
        $email = $this->normalizarEmail($request);

        $datos = $request->validate([
            'nombre' => 'required|string|max:100',
            'dni_ruc' => [
                'nullable', 'string', 'max:20',
                Rule::unique('clientes', 'dni_ruc')->ignore($cliente->id_cliente, 'id_cliente'),
            ],
            'telefono' => 'required|string|max:20',
            'email' => [
                'nullable', 'string', 'email', 'max:100',
                Rule::unique('clientes', 'email')
                    ->ignore($cliente->id_cliente, 'id_cliente')
                    ->where(fn ($query) => $query->whereRaw('LOWER(email) = ?', [$email])),
            ],
            'direccion' => 'nullable|string|max:150',
            'activo' => 'nullable|boolean',
        ]);

        $datos['email'] = $datos['email'] ?? null;
        $datos['activo'] = $request->boolean('activo');

        $cliente->update($datos);

        return redirect()
            ->route('admin.clientes.show', $cliente)
            ->with('success', 'Cliente actualizado correctamente.');
    }

    public function actualizarEstado(Cliente $cliente)
    {
        $cliente->update(['activo' => ! $cliente->activo]);

        return back()->with('success', $cliente->activo
            ? 'Cliente activado correctamente.'
            : 'Cliente desactivado. Ya no aparecerá para nuevas órdenes.');
    }

    public function destroy(Cliente $cliente)
    {
        $tieneHistorial = $cliente->ordenes()->exists()
            || $cliente->pedidos()->exists()
            || $cliente->ventas()->exists()
            || (float) $cliente->saldo_pendiente > 0;

        if ($tieneHistorial) {
            return back()->with('error', 'No se puede eliminar el cliente porque tiene órdenes, pedidos, ventas o saldo pendiente. Puedes desactivarlo para conservar el historial.');
        }

        $cliente->delete();

        return redirect()
            ->route('admin.clientes.index')
            ->with('success', 'Cliente eliminado correctamente.');
    }

    private function normalizarEmail(Request $request): string
    {
        $email = mb_strtolower(trim((string) $request->input('email')));
        $request->merge(['email' => $email !== '' ? $email : null]);

        return $email;
    }
}

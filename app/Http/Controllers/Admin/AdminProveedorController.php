<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminProveedorController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'q' => 'nullable|string|max:100',
            'estado' => 'nullable|in:activos,inactivos',
        ]);

        $proveedores = Proveedor::query()
            ->withCount('compras')
            ->when($filtros['q'] ?? null, function ($query, $termino) {
                $query->where(function ($query) use ($termino) {
                    $query->where('nombre', 'like', "%{$termino}%")
                        ->orWhere('ruc', 'like', "%{$termino}%")
                        ->orWhere('email', 'like', "%{$termino}%")
                        ->orWhere('telefono', 'like', "%{$termino}%");
                });
            })
            ->when(($filtros['estado'] ?? null) === 'activos', fn ($query) => $query->where('activo', true))
            ->when(($filtros['estado'] ?? null) === 'inactivos', fn ($query) => $query->where('activo', false))
            ->orderByDesc('id_proveedor')
            ->paginate(15)
            ->withQueryString();

        return view('admin.proveedores.index', [
            'proveedores' => $proveedores,
            'q' => $filtros['q'] ?? '',
            'estado' => $filtros['estado'] ?? '',
        ]);
    }

    public function create()
    {
        return view('admin.proveedores.create');
    }

    public function show(Proveedor $proveedor)
    {
        $proveedor->load([
            'compras' => fn ($compras) => $compras->latest('fecha_compra')->take(10),
        ]);

        return view('admin.proveedores.show', [
            'proveedor' => $proveedor,
            'totalCompras' => $proveedor->compras()->count(),
            'totalComprado' => (float) $proveedor->compras()->sum('total'),
        ]);
    }

    public function edit(Proveedor $proveedor)
    {
        return view('admin.proveedores.edit', compact('proveedor'));
    }

    public function store(Request $request)
    {
        $email = $this->normalizarDatos($request);

        $datos = $request->validate([
            'ruc' => 'nullable|string|max:20|unique:proveedores,ruc',
            'nombre' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => [
                'nullable', 'string', 'email', 'max:100',
                Rule::unique('proveedores', 'email')
                    ->where(fn ($query) => $query->whereRaw('LOWER(email) = ?', [$email])),
            ],
            'direccion' => 'nullable|string|max:150',
            'activo' => 'nullable|boolean',
        ]);

        $datos['email'] = $datos['email'] ?? null;
        $datos['activo'] = $request->has('activo') ? $request->boolean('activo') : true;

        $proveedor = Proveedor::create($datos);

        return redirect()
            ->route('admin.proveedores.show', $proveedor)
            ->with('success', 'Proveedor registrado correctamente.');
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $email = $this->normalizarDatos($request);

        $datos = $request->validate([
            'ruc' => [
                'nullable', 'string', 'max:20',
                Rule::unique('proveedores', 'ruc')->ignore($proveedor->id_proveedor, 'id_proveedor'),
            ],
            'nombre' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => [
                'nullable', 'string', 'email', 'max:100',
                Rule::unique('proveedores', 'email')
                    ->ignore($proveedor->id_proveedor, 'id_proveedor')
                    ->where(fn ($query) => $query->whereRaw('LOWER(email) = ?', [$email])),
            ],
            'direccion' => 'nullable|string|max:150',
            'activo' => 'nullable|boolean',
        ]);

        $datos['email'] = $datos['email'] ?? null;
        $datos['activo'] = $request->has('activo') ? $request->boolean('activo') : $proveedor->activo;

        $proveedor->update($datos);

        return redirect()
            ->route('admin.proveedores.show', $proveedor)
            ->with('success', 'Proveedor actualizado correctamente.');
    }

    public function actualizarEstado(Proveedor $proveedor)
    {
        $proveedor->update(['activo' => ! $proveedor->activo]);

        return back()->with('success', $proveedor->activo
            ? 'Proveedor activado correctamente.'
            : 'Proveedor desactivado. Ya no aparecerá para nuevas compras.');
    }

    public function destroy(Proveedor $proveedor)
    {
        $tieneHistorial = $proveedor->compras()->exists()
            || (float) $proveedor->saldo_pendiente > 0;

        if ($tieneHistorial) {
            return back()->with('error', 'No se puede eliminar el proveedor porque tiene compras registradas o saldo pendiente. Puedes desactivarlo para conservar el historial.');
        }

        $proveedor->delete();

        return redirect()
            ->route('admin.proveedores.index')
            ->with('success', 'Proveedor eliminado correctamente.');
    }

    private function normalizarDatos(Request $request): string
    {
        $email = mb_strtolower(trim((string) $request->input('email')));

        $request->merge([
            'ruc' => $this->textoOpcional($request->input('ruc')),
            'telefono' => $this->textoOpcional($request->input('telefono')),
            'email' => $email !== '' ? $email : null,
            'direccion' => $this->textoOpcional($request->input('direccion')),
        ]);

        return $email;
    }

    private function textoOpcional(mixed $valor): ?string
    {
        $texto = trim((string) $valor);

        return $texto !== '' ? $texto : null;
    }
}

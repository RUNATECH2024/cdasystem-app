<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUsuarioController extends Controller
{
    private const ROLES = ['Admin', 'Tecnico', 'Vendedor'];

    public function index()
    {
        $usuarios = Usuario::query()
            ->select(['id_usuario', 'nombre', 'usuario', 'rol', 'activo', 'fecha_creacion'])
            ->orderBy('nombre')
            ->paginate(20);

        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('admin.usuarios.create', ['roles' => self::ROLES]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:100',
            'usuario' => 'required|string|alpha_dash|max:50|unique:usuarios,usuario',
            'rol' => ['required', Rule::in(self::ROLES)],
            'password' => 'required|string|min:12|confirmed',
        ]);

        Usuario::create([
            'nombre' => $datos['nombre'],
            'usuario' => $datos['usuario'],
            'rol' => $datos['rol'],
            'password_hash' => Hash::make($datos['password']),
            'activo' => true,
        ]);

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario creado correctamente.');
    }
}

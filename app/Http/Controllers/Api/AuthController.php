<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UsuarioResource;
use App\Models\Usuario;
use App\Support\PasswordVerifier;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $datos = $request->validated();

        $usuario = Usuario::where('usuario', trim($datos['usuario']))->first();

        $passwordValida = PasswordVerifier::check($usuario?->password_hash, $datos['password']);

        if (! $usuario || ! $usuario->activo || ! $passwordValida) {
            return response()->json(['error' => 'Credenciales incorrectas'], 401);
        }

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'mensaje' => 'Login exitoso',
            'token' => $token,
            'usuario' => new UsuarioResource($usuario),
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->user()?->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json(['mensaje' => 'Sesión cerrada']);
    }
}

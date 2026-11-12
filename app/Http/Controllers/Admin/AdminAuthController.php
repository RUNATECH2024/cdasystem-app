<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Usuario;
use App\Support\PasswordVerifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(Request $request)
    {
        $datos = $request->validate([
            'usuario' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $usuario = Usuario::where('usuario', trim($datos['usuario']))->first();

        $passwordValida = PasswordVerifier::check($usuario?->password_hash, $datos['password']);

        if (! $usuario || ! $usuario->activo || $usuario->rol !== 'Admin' || ! $passwordValida) {
            $this->registrarEvento($request, $usuario, 'admin.login.failed', 302);

            return back()
                ->withErrors(['usuario' => 'Usuario o contraseña incorrectos.'])
                ->onlyInput('usuario');
        }

        Auth::guard('admin')->login($usuario);
        $request->session()->regenerate();
        $this->registrarEvento($request, $usuario, 'admin.login.succeeded', 302);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        $usuario = Auth::guard('admin')->user();
        $this->registrarEvento($request, $usuario, 'admin.logout', 302);
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function registrarEvento(Request $request, ?Usuario $usuario, string $accion, int $codigo): void
    {
        AdminAuditLog::create([
            'id_usuario' => $usuario?->id_usuario,
            'actor_usuario' => $usuario?->usuario ?? mb_substr(trim((string) $request->input('usuario')), 0, 50),
            'actor_rol' => $usuario?->rol,
            'accion' => $accion,
            'metodo_http' => $request->method(),
            'ruta' => $request->route()?->uri(),
            'codigo_respuesta' => $codigo,
            'ip' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'created_at' => now(),
        ]);
    }
}

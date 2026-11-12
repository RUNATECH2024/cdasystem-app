<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountsAreActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/*')) {
            $usuario = $request->user();

            if ($usuario && ! $usuario->activo) {
                return response()->json(['error' => 'La cuenta esta desactivada.'], 403);
            }

            return $next($request);
        }

        $guard = $request->is('admin', 'admin/*') ? 'admin' : 'cliente';
        $usuario = Auth::guard($guard)->user();

        if ($usuario && ! $usuario->activo) {
            Auth::guard($guard)->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()
                ->route($guard === 'admin' ? 'admin.login' : 'login')
                ->withErrors([
                    $guard === 'admin' ? 'usuario' : 'email' => 'Tu cuenta ha sido desactivada.',
                ]);
        }

        return $next($request);
    }
}

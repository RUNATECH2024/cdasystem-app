<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if (! $usuario || ! in_array($usuario->rol, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'No autorizado para esta accion.'], 403);
            }

            abort(403);
        }

        return $next($request);
    }
}

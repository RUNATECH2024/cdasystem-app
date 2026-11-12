<?php

namespace App\Http\Middleware;

use App\Models\AdminAuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditAdminActions
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $route = $request->route();
        $descargaRespaldo = $route?->getName() === 'admin.respaldos.download';

        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true) && ! $descargaRespaldo) {
            return $response;
        }

        $actor = $request->user('admin');
        if (! $actor) {
            return $response;
        }

        $parameters = collect($route?->parameters() ?? [])
            ->map(fn ($value) => is_object($value) && method_exists($value, 'getKey')
                ? $value->getKey()
                : (is_scalar($value) ? $value : null))
            ->filter(fn ($value) => $value !== null)
            ->all();
        $target = $parameters ? key($parameters).':'.reset($parameters) : null;

        AdminAuditLog::create([
            'id_usuario' => $actor->id_usuario,
            'actor_usuario' => $actor->usuario,
            'actor_rol' => $actor->rol,
            'accion' => $route?->getName() ?: $request->method().' '.$request->path(),
            'metodo_http' => $request->method(),
            'ruta' => $route?->uri(),
            'objetivo' => $target,
            'metadata' => [
                'campos_enviados' => array_values(array_diff(
                    array_keys($request->except(['_token', '_method', 'password', 'password_confirmation', 'password_hash', 'current_password'])),
                    []
                )),
                'parametros' => $parameters,
            ],
            'codigo_respuesta' => $response->getStatusCode(),
            'ip' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'created_at' => now(),
        ]);

        return $response;
    }
}

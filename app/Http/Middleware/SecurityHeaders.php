<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->remove('X-Powered-By');

        $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '0');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        if ($this->esRutaSensible($request)) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
        }

        return $response;
    }

    private function contentSecurityPolicy(): string
    {
        $script = "'self' 'unsafe-inline'";
        $connect = "'self'";

        if (config('app.env') === 'local') {
            $dev = 'http://localhost:* http://127.0.0.1:* ws://localhost:* ws://127.0.0.1:*';
            $script .= ' '.$dev;
            $connect .= ' '.$dev;
        }

        return implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "object-src 'none'",
            "img-src 'self' data: blob: https://via.placeholder.com",
            "font-src 'self' data: https://cdnjs.cloudflare.com",
            "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com",
            "script-src {$script}",
            "connect-src {$connect}",
            "media-src 'self'",
            "worker-src 'self' blob:",
            "manifest-src 'self'",
        ]);
    }

    private function esRutaSensible(Request $request): bool
    {
        return $request->is(
            'admin',
            'admin/*',
            'login',
            'register',
            'carrito',
            'carrito/*',
            'checkout',
            'checkout/*',
            'api/login',
            'api/logout',
            'api/productos',
            'api/ventas',
            'api/taller/*',
        );
    }
}

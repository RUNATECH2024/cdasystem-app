<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GuardSessionCookie
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('admin', 'admin/*')) {
            config(['session.cookie' => 'admin_session']);
        } else {
            config(['session.cookie' => 'laravel_session']);
        }

        return $next($request);
    }
}

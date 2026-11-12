<?php

use App\Http\Middleware\AuditAdminActions;
use App\Http\Middleware\EnsureAccountsAreActive;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\GuardSessionCookie;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prependToGroup('web', GuardSessionCookie::class);
        $middleware->append(SecurityHeaders::class);
        $middleware->appendToGroup('web', EnsureAccountsAreActive::class);

        $middleware->alias([
            'active' => EnsureAccountsAreActive::class,
            'role' => EnsureUserHasRole::class,
            'audit.admin' => AuditAdminActions::class,
        ]);

        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('admin', 'admin/*')
            ? route('admin.login')
            : route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

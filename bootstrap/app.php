<?php

use App\Http\Middleware\GlobalSettingsMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            '/paypal/webhook',
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'impersonation.check' => \App\Http\Middleware\ImpersonationCheckMiddleware::class,
            'global.settings' => \App\Http\Middleware\GlobalSettingsMiddleware::class,

        ]);

        // Append middleware separately
        $middleware->append(GlobalSettingsMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // --- AÑADE ESTA LÍNEA AQUÍ ---
        // Usamos prepend para que se ejecute antes que cualquier otro middleware.
        $middleware->prepend(\App\Http\Middleware\IdentifyTenant::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })

    ->withCommands([
        \App\Console\Commands\DebugMigrations::class,
    ])

    ->create();

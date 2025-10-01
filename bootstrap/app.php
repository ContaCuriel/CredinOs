<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
// --- AÑADE ESTA LÍNEA EN LA PARTE SUPERIOR ---
use Spatie\Multitenancy\MultitenancyServiceProvider;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withCommands([
        // Ya no necesitamos este comando, pero lo dejamos por ahora
        \App\Console\Commands\DebugMigrations::class,
    ])
    // --- AÑADE ESTA SECCIÓN PARA REGISTRAR EL PAQUETE ---
    ->withProviders([
        MultitenancyServiceProvider::class,
    ])
    // ----------------------------------------------------
    ->create();
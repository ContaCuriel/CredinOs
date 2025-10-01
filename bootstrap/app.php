<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
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
        \App\Console\Commands\MigrateAllTenants::class,
    ])
    ->withProviders([
        MultitenancyServiceProvider::class,
    ])
    // --- AÑADE ESTA SECCIÓN NUCLEAR ---
    ->booting(function (Application $app) {
        // Forzamos al migrador de Laravel a que conozca nuestra ruta de tenants.
        // Esto se ejecuta cada vez que la aplicación arranca.
        $app->make('migrator')->path(database_path('migrations/tenant'));
    })
    // ---------------------------------
    ->create();
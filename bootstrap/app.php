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
    // Hacemos que IdentifyTenant sea un middleware GLOBAL que se ejecuta
    // ANTES que la mayoría de los demás, incluyendo el router.
    $middleware->prepend([
        \App\Http\Middleware\IdentifyTenant::class,
    ]);
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
    
    // ---------------------------------
    ->create();
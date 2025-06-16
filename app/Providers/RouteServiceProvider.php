<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/dashboard';

    public function boot(): void
    {

         Route::resourceParameters([
        'sucursales' => 'sucursal', // La que ya tenías (o deberías tener)
        'patrones'   => 'patron'    // <-- AÑADE ESTA LÍNEA
         ]);
        // Si necesitas personalizar nombres de parámetros de ruta para Route::resource,
        // este es el lugar correcto, por ejemplo:
        // Route::resourceParameters([
        //     'sucursales' => 'sucursal_param_name' // Cambia 'sucursal_param_name' al que necesites
        // ]);

        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
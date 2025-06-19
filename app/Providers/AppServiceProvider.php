<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;       // Necesario para View::composer
use App\View\Composers\PatronLogoComposer;  // Necesario para la clase Composer
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive(); // Esto es para los estilos de paginación

        // Registrar el View Composer usando la clase dedicada
        View::composer('auth.login', PatronLogoComposer::class);
   
// Forzar que todas las URLs se generen con HTTPS si la conexión original era segura (como con ngrok)
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            URL::forceScheme('https');
        }

    }
}
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;       // Necesario para View::composer
use App\View\Composers\PatronLogoComposer;  // Necesario para la clase Composer
use App\Models\Recovery;
use Illuminate\Support\Facades\URL;
use App\Models\Gasto;
use App\Observers\GastoObserver;
use App\Models\Placement; // Importa el nuevo modelo
use App\Observers\PlacementObserver; // Importa el nuevo observador
use App\Observers\RecoveryObserver;

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

         Gasto::observe(GastoObserver::class);
          Placement::observe(PlacementObserver::class);
          Recovery::observe(RecoveryObserver::class); 

    }
}
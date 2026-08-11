<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;       
use App\View\Composers\PatronLogoComposer;  
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
        Paginator::useBootstrapFive(); // Estilos de paginación

        // Registrar el View Composer usando la clase dedicada
        View::composer('auth.login', PatronLogoComposer::class);
   
        // Forzar HTTPS en conexiones seguras (ej. ngrok o proxies)
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            URL::forceScheme('https');
        }

        // 🚫 OBSERVERS DESACTIVADOS 🚫
        // Se migraron a los controladores mediante DB::transaction y AccountingService 
        // para garantizar la integridad contable de partida doble.
        // Gasto::observe(GastoObserver::class);
        // Placement::observe(PlacementObserver::class);
        // Recovery::observe(RecoveryObserver::class); 
    }
}
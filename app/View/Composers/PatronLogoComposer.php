<?php

namespace App\View\Composers;

use Illuminate\View\View;
use App\Models\Patron; // Necesitas el modelo Patron aquí
use Illuminate\Support\Facades\Schema; // Para verificar si la tabla existe

class PatronLogoComposer
{
    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $patronesConLogo = collect(); // Por defecto, una colección vacía

        // Intentar obtener los patrones solo si la tabla 'patrones' existe
        // Esto ayuda a evitar errores durante migraciones o comandos artisan tempranos
        if (Schema::hasTable('patrones')) {
            try {
                $patronesConLogo = Patron::whereNotNull('logo_path')
                                         ->where('logo_path', '!=', '')
                                         ->get();
            } catch (\Illuminate\Database\QueryException $e) {
                // Si hay un error de base de datos (ej: tabla no existe aún durante una migración)
                // No hacer nada, $patronesConLogo seguirá siendo una colección vacía
                // Opcionalmente, loguear el error si no es un comando de consola
                if (!app()->runningInConsole()) {
                    \Log::error('Error en PatronLogoComposer al consultar patrones: ' . $e->getMessage());
                }
            }
        }

        $view->with('patronesConLogo', $patronesConLogo);
    }
}
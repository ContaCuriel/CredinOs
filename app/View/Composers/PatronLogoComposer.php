<?php

namespace App\View\Composers;

use Illuminate\View\View;
use App\Models\Patron; // Asegúrate de importar tu modelo Patron

class PatronLogoComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        // Obtenemos todos los patrones que tengan un logo_path definido
        $patrones = Patron::whereNotNull('logo_path')->get(['logo_path']);
        
        // Extraemos solo la lista de rutas de los logos
        $logos = $patrones->pluck('logo_path');

        // Pasamos la variable $logos a la vista
        $view->with('logos', $logos);
    }
}
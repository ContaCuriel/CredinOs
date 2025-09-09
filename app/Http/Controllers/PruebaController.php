<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PruebaController extends Controller
{
    /**
     * Muestra la vista principal del módulo de prueba.
     */
    public function index()
    {
        // Simplemente devolvemos una vista simple que crearemos a continuación.
        return view('prueba.index');
    }
}

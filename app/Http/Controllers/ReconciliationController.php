<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReconciliationService; // <-- Importamos el servicio
use App\Models\PaymentInstallment;
use Illuminate\Support\Facades\Log;

class ReconciliationController extends Controller
{
    protected $reconciliationService;

    public function __construct(ReconciliationService $reconciliationService)
    {
        $this->reconciliationService = $reconciliationService;
    }

    /**
     * Muestra el formulario para subir el archivo.
     */
    public function create()
    {
        return view('reconciliation.create');
    }

    /**
     * Procesa el archivo subido y muestra las coincidencias.
     */
    public function store(Request $request)
    {
        $request->validate([
            'bank_statement' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('bank_statement');

        // Llamamos a nuestro servicio para que haga el trabajo pesado
        $matches = $this->reconciliationService->findMatchesInStatement($file);

        if (empty($matches)) {
            return redirect()->route('reconciliation.create')
                             ->with('error', 'No se encontraron coincidencias en el archivo.');
        }

        // Guardamos las coincidencias en la sesión para usarlas en el siguiente paso
        session(['reconciliation_matches' => $matches]);

        // Redirigimos a la página de confirmación
        return redirect()->route('reconciliation.confirm');
    }

     public function confirm()
    {
        // Obtenemos las coincidencias que guardamos en la sesión
        $matches = session('reconciliation_matches', []);

        if (empty($matches)) {
            return redirect()->route('reconciliation.create')->with('error', 'No hay coincidencias para confirmar.');
        }

        return view('reconciliation.confirm', compact('matches'));
    }


}
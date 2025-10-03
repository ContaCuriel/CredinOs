<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CodigoPostal;

class CodigoPostalController extends Controller
{
    public function getInfo($cp)
    {
        $resultados = CodigoPostal::where('codigo_postal', $cp)->get();

        if ($resultados->isEmpty()) {
            return response()->json(['error' => true, 'error_message' => 'CP no encontrado'], 404);
        }

        // Formateamos la respuesta para que sea idéntica a la que esperaba el JavaScript
        $formatted = $resultados->map(function ($item) {
            return [
                'response' => [
                    'asentamiento' => $item->colonia,
                    'municipio' => $item->municipio,
                    'estado' => $item->estado,
                ]
            ];
        });

        return response()->json($formatted);
    }
}
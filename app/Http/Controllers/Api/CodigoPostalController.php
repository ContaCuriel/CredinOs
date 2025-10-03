<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB; // <-- AÑADE/ASEGURA ESTE 'USE'

class CodigoPostalController extends Controller
{
    public function getInfo($cp)
    {
        // --- LA CORRECCIÓN DEFINITIVA ---
        // En lugar de usar el modelo Eloquent, usamos el Query Builder
        // y le forzamos a usar la conexión 'pgsql' (la central).
        // Esto es inmune a los problemas de caché del modelo.
        $resultados = DB::connection('pgsql')
                        ->table('codigos_postales')
                        ->where('codigo_postal', $cp)
                        ->get();
        // ---------------------------------

        if ($resultados->isEmpty()) {
            // Devolvemos 404 si no se encuentra, como debe ser.
            return response()->json(['error' => true, 'error_message' => 'CP no encontrado'], 404);
        }

        // El resto del código para formatear la respuesta se queda igual
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
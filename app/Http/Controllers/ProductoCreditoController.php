<?php

namespace App\Http\Controllers;

use App\Models\ProductoCredito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoCreditoController extends Controller
{
    public function index()
    {
        $productos = ProductoCredito::orderBy('nombre')->get();
        return view('productos_credito.index', compact('productos'));
    }

    public function create()
    {
        return view('productos_credito.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo_credito' => 'required|in:individual,grupal',
            'frecuencia_pago' => 'required|in:diario,semanal,catorcenal,quincenal,mensual,pago_al_final',
            'tasa_interes' => 'required|numeric|min:0',
            'tipo_tasa' => 'required|in:global,saldo_insoluto',
            'plazo_minimo' => 'required|integer|min:1',
            'plazo_maximo' => 'required|integer|gte:plazo_minimo',
            'monto_minimo' => 'required|numeric|min:1',
            'monto_maximo' => 'required|numeric|gte:monto_minimo',
            
            // Castigos
            'hora_maxima_pago' => 'nullable|date_format:H:i',
            
            'multa_trigger' => 'required|in:despues_de_hora,despues_de_dia,no_aplica',
            'multa_valor' => 'required|numeric|min:0',
            'multa_calculo' => 'required|in:fijo,porcentaje_pago,porcentaje_saldo,porcentaje_credito',
            
            'mora_trigger' => 'required|in:despues_de_hora,despues_de_dia,no_aplica',
            'mora_valor' => 'required|numeric|min:0',
            'mora_calculo' => 'required|in:fijo,porcentaje_pago,porcentaje_saldo,porcentaje_credito',
            
            'politica_acumulacion' => 'required|in:acumular,solo_mayor,reemplazar',
        ]);

        try {
            // Aseguramos que si no aplica, el valor sea 0
            if ($validatedData['multa_trigger'] == 'no_aplica') $validatedData['multa_valor'] = 0;
            if ($validatedData['mora_trigger'] == 'no_aplica') $validatedData['mora_valor'] = 0;

            ProductoCredito::create($validatedData);

            // Redirigimos a create para que puedas seguir creando si lo deseas (o cambialo a index)
            return redirect()->route('productos_credito.index')->with('success', 'Producto de crédito creado exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al guardar el producto: ' . $e->getMessage())->withInput();
        }
    }
}
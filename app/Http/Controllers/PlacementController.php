<?php

namespace App\Http\Controllers;

use App\Models\Placement;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class PlacementController extends Controller
{
    public function index()
    {
        $placements = Placement::with(['sucursal', 'user', 'journal'])->latest()->paginate(20);
        return view('placements.index', compact('placements'));
    }

    public function create()
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        return view('placements.create', compact('sucursales'));
    }

    public function store(Request $request)
    {
        // PRUEBA DE FUEGO: Si quitas las dos diagonales (//) de la siguiente línea, 
        // la página debe ponerse negra de inmediato al darle a Guardar. 
        // Si no lo hace, el problema NO es PHP, es tu Javascript de la vista.
        // dd('¡SÍ LLEGA AL CONTROLADOR!');

        try {
            $currentYear = Carbon::now()->year;

            $validatedData = $request->validate([
                'sucursal_id' => 'required|exists:sucursales,id_sucursal',
                'year' => 'required|integer|min:2020|max:' . $currentYear,
                'month' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:12',
                    Rule::unique('placements')->where(function ($query) use ($request) {
                        return $query->where('sucursal_id', $request->sucursal_id)
                                     ->where('year', $request->year);
                    }),
                ],
                'amount' => 'required|numeric|min:0.01',
                'notes' => 'nullable|string',
            ]);

            Placement::create([
                'sucursal_id' => $validatedData['sucursal_id'],
                'year'        => $validatedData['year'],
                'month'       => $validatedData['month'],
                'amount'      => $validatedData['amount'],
                'user_id'     => Auth::id() ?? 1,
                'notes'       => $validatedData['notes'],
            ]);

            return redirect()->route('placements.index')->with('success', 'Registro guardado exitosamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) { // <--- ESTO ATRAPA ABSOLUTAMENTE TODO (Incluso Errores Fatales)
            dd([
                '¡ALERTA DE ERROR FATAL!' => 'El sistema falló al guardar.',
                'MENSAJE_EXACTO' => $e->getMessage(),
                'ARCHIVO_DONDE_FALLO' => $e->getFile(),
                'LINEA' => $e->getLine()
            ]);
        }
    }
}
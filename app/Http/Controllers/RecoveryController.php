<?php

namespace App\Http\Controllers;

use App\Models\Recovery;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class RecoveryController extends Controller
{
    public function index()
    {
        $recoveries = Recovery::with(['sucursal', 'user', 'journal'])
                                ->latest()
                                ->paginate(20);

        return view('recoveries.index', compact('recoveries'));
    }

    public function create()
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        return view('recoveries.create', compact('sucursales'));
    }

    public function store(Request $request)
    {
        $currentYear = Carbon::now()->year;

        $validatedData = $request->validate([
            'sucursal_id' => 'required|exists:sucursales,id_sucursal',
            'year' => 'required|integer|min:2020|max:' . $currentYear,
            'month' => [
                'required', 'integer', 'min:1', 'max:12',
                Rule::unique('recoveries')->where(function ($query) use ($request) {
                    return $query->where('sucursal_id', $request->sucursal_id)
                                 ->where('year', $request->year);
                }),
            ],
            'cobro_proyectado' => 'required|numeric|min:0', // <-- Nuevo campo
            'capital_recovered' => 'required|numeric|min:0',
            'interest_collected' => 'required|numeric|min:0',
            'unrecoverable_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ], [
            'month.unique' => 'Ya existe un registro de recuperación para esta sucursal en el mes y año seleccionados.',
        ]);

        // --- MATEMÁTICAS MÁGICAS PARA LA MORA ---
        $proyectado = $validatedData['cobro_proyectado'];
        $capital = $validatedData['capital_recovered'];
        $interes = $validatedData['interest_collected'];
        
        $total_recuperado = $capital + $interes;
        $mora_calculada = $proyectado - $total_recuperado;
        
        // Si por alguna razón cobraron de más (pagos adelantados), la mora es 0, no negativa
        $mora_final = $mora_calculada > 0 ? $mora_calculada : 0;
        // ----------------------------------------

        Recovery::create([
            'sucursal_id' => $validatedData['sucursal_id'],
            'year' => $validatedData['year'],
            'month' => $validatedData['month'],
            'cobro_proyectado' => $proyectado,
            'capital_recovered' => $capital,
            'interest_collected' => $interes,
            'mora_periodo' => $mora_final, // <-- Se guarda solito
            'unrecoverable_amount' => $validatedData['unrecoverable_amount'],
            'user_id' => Auth::id(),
            'notes' => $validatedData['notes'],
        ]);

        return redirect()->route('recoveries.index')->with('success', 'Registro de recuperación guardado exitosamente. La mora se calculó de forma automática y la póliza ha sido generada.');
    }
}
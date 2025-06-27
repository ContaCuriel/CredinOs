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
    /**
     * Muestra una lista de los registros de recuperación.
     */
    public function index()
    {
        $recoveries = Recovery::with(['sucursal', 'user', 'journal'])
                                ->latest()
                                ->paginate(20);

        return view('recoveries.index', compact('recoveries'));
    }

    /**
     * Muestra el formulario para crear un nuevo registro de recuperación.
     */
    public function create()
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        return view('recoveries.create', compact('sucursales'));
    }

    /**
     * Guarda un nuevo registro de recuperación en la base de datos.
     */
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
            'capital_recovered' => 'required|numeric|min:0',
            'interest_collected' => 'required|numeric|min:0',
            'unrecoverable_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ], [
            'month.unique' => 'Ya existe un registro de recuperación para esta sucursal en el mes y año seleccionados.',
        ]);

        Recovery::create([
            'sucursal_id' => $validatedData['sucursal_id'],
            'year' => $validatedData['year'],
            'month' => $validatedData['month'],
            'capital_recovered' => $validatedData['capital_recovered'],
            'interest_collected' => $validatedData['interest_collected'],
            'unrecoverable_amount' => $validatedData['unrecoverable_amount'],
            'user_id' => Auth::id(),
            'notes' => $validatedData['notes'],
        ]);

        return redirect()->route('recoveries.index')->with('success', 'Registro de recuperación guardado exitosamente. La póliza contable ha sido generada.');
    }
}

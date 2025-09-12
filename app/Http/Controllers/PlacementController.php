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
    /**
     * Muestra una lista de los registros de colocación.
     */
    public function index()
    {
        $placements = Placement::with(['sucursal', 'user', 'journal'])
                                ->latest()
                                ->paginate(20);

        return view('placements.index', compact('placements'));
    }

    /**
     * Muestra el formulario para crear un nuevo registro de colocación.
     */
    public function create()
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        return view('placements.create', compact('sucursales'));
    }

    /**
     * Guarda un nuevo registro de colocación en la base de datos.
     */
    public function store(Request $request)
    {
        $currentYear = Carbon::now()->year;

        $validatedData = $request->validate([
            'sucursal_id' => 'required|exists:sucursales,id_sucursal',
            'year' => 'required|integer|min:2020|max:' . $currentYear,
            'month' => [
                'required',
                'integer',
                'min:1',
                'max:12',
                // Regla para asegurar que la combinación de sucursal, año y mes sea única.
                Rule::unique('placements')->where(function ($query) use ($request) {
                    return $query->where('sucursal_id', $request->sucursal_id)
                                 ->where('year', $request->year);
                }),
            ],
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ], [
            // Mensajes de error personalizados
            'month.unique' => 'Ya existe un registro de colocación para esta sucursal en el mes y año seleccionados.',
        ]);

        Placement::create([
            'sucursal_id' => $validatedData['sucursal_id'],
            'year' => $validatedData['year'],
            'month' => $validatedData['month'],
            'amount' => $validatedData['amount'],
            'user_id' => Auth::id(),
            'notes' => $validatedData['notes'],
        ]);

        return redirect()->route('placements.index')->with('success', 'Registro de colocación guardado exitosamente. La póliza contable ha sido generada.');
    }
}

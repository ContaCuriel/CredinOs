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
        // Cargamos sucursal, user y journal (vital para ver la póliza creada)
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
            ], [
                'month.unique' => 'Ya existe un registro de colocación para esta sucursal en el mes y año seleccionados.',
            ]);

            // Forzamos la creación del registro
            Placement::create([
                'sucursal_id' => $validatedData['sucursal_id'],
                'year' => $validatedData['year'],
                'month' => $validatedData['month'],
                'amount' => $validatedData['amount'],
                'user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1, // Previene error si la sesión se pierde
                'notes' => $validatedData['notes'],
            ]);

            return redirect()->route('placements.index')
                ->with('success', 'Registro de colocación guardado exitosamente. La póliza contable ha sido generada.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Si es error de validación del formulario, lo regresa normalmente
            return redirect()->back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            // AQUI ESTÁ LA MAGIA: Si hay un error fatal, rompe la pantalla y te lo muestra
            dd([
                '¡ALERTA DE ERROR FATAL!' => 'El sistema falló al guardar.',
                'MENSAJE DEL SERVIDOR' => $e->getMessage(),
                'ARCHIVO' => $e->getFile(),
                'LINEA' => $e->getLine()
            ]);
        }
    }
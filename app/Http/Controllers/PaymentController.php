<?php

namespace App\Http\Controllers;

use App\Models\Placement;
use App\Models\Sucursal;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    public function store(Request $request, AccountingService $accountingService)
    {
        try {
            $currentYear = Carbon::now()->year;

            $validatedData = $request->validate([
                'sucursal_id' => 'required|exists:sucursales,id_sucursal',
                'year' => 'required|integer|min:2020|max:' . $currentYear,
                'month' => [
                    'required', 'integer', 'min:1', 'max:12',
                    Rule::unique('placements')->where(function ($query) use ($request) {
                        return $query->where('sucursal_id', $request->sucursal_id)
                                     ->where('year', $request->year);
                    }),
                ],
                'amount' => 'required|numeric|min:0.01',
                'notes' => 'nullable|string',
            ]);

            DB::beginTransaction();

            // 1. Guardar Colocación
            $placement = Placement::create([
                'sucursal_id' => $validatedData['sucursal_id'],
                'year'        => $validatedData['year'],
                'month'       => $validatedData['month'],
                'amount'      => $validatedData['amount'],
                'user_id'     => Auth::id() ?? 1,
                'notes'       => $validatedData['notes'],
            ]);

            // 2. Crear Póliza Contable usando el Servicio
            $accountingService->createJournalFromPlacement($placement);

            DB::commit();
            return redirect()->route('placements.index')->with('success', 'Colocación guardada y póliza contable generada exitosamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error fatal al guardar colocación: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error en el sistema al guardar (Falta configuración de cuentas o error de DB).')->withInput();
        }
    }
}
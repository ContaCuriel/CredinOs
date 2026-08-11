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

            DB::beginTransaction();

            // 1. Guardar la colocación operativa
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
            return redirect()->back()->with('error', 'Ocurrió un error en el sistema al intentar guardar. El área de soporte ha sido notificada.')->withInput();
        }
    }

    public function edit(Placement $placement)
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        return view('placements.edit', compact('placement', 'sucursales'));
    }

    public function update(Request $request, Placement $placement, AccountingService $accountingService)
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
                    // Ignoramos el ID actual para que permita guardar si no cambian el mes/año
                    Rule::unique('placements')->where(function ($query) use ($request) {
                        return $query->where('sucursal_id', $request->sucursal_id)
                                     ->where('year', $request->year);
                    })->ignore($placement->id),
                ],
                'amount' => 'required|numeric|min:0.01',
                'notes' => 'nullable|string',
            ]);

            DB::beginTransaction();

            // 1. Actualizar el registro
            $placement->update([
                'sucursal_id' => $validatedData['sucursal_id'],
                'year'        => $validatedData['year'],
                'month'       => $validatedData['month'],
                'amount'      => $validatedData['amount'],
                'notes'       => $validatedData['notes'],
            ]);

            // 2. Destruir póliza vieja y crear la nueva
            $accountingService->updateJournalFromPlacement($placement);

            DB::commit();
            return redirect()->route('placements.index')->with('success', 'Colocación actualizada y póliza regenerada exitosamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error fatal al actualizar colocación: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al actualizar. El área de soporte ha sido notificada.')->withInput();
        }
    }

    public function destroy(Placement $placement, AccountingService $accountingService)
    {
        DB::beginTransaction();
        try {
            // 1. Destruimos la póliza contable primero para evitar referencias huérfanas
            $accountingService->deleteJournalForModel($placement);
            
            // 2. Destruimos el registro operativo
            $placement->delete();

            DB::commit();
            return redirect()->route('placements.index')->with('success', 'Colocación y póliza contable eliminadas exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error fatal al eliminar colocación: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al eliminar. El área de soporte ha sido notificada.');
        }
    }
}
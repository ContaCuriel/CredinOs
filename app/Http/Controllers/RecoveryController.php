<?php

namespace App\Http\Controllers;

use App\Models\Recovery;
use App\Models\Sucursal;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class RecoveryController extends Controller
{
    public function index()
    {
        $recoveries = Recovery::with(['sucursal', 'user', 'journal'])->latest()->paginate(20);
        return view('recoveries.index', compact('recoveries'));
    }

    public function create()
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        return view('recoveries.create', compact('sucursales'));
    }

    public function store(Request $request, AccountingService $accountingService)
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
            'cobro_proyectado' => 'required|numeric|min:0',
            'capital_recovered' => 'required|numeric|min:0',
            'interest_collected' => 'required|numeric|min:0',
            'unrecoverable_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $proyectado = $validatedData['cobro_proyectado'];
        $capital = $validatedData['capital_recovered'];
        $interes = $validatedData['interest_collected'];
        
        $total_recuperado = $capital + $interes;
        $mora_calculada = $proyectado - $total_recuperado;
        $mora_final = $mora_calculada > 0 ? $mora_calculada : 0;

        DB::beginTransaction();

        try {
            // 1. Guardar Recuperación
            $recovery = Recovery::create([
                'sucursal_id' => $validatedData['sucursal_id'],
                'year' => $validatedData['year'],
                'month' => $validatedData['month'],
                'cobro_proyectado' => $proyectado,
                'capital_recovered' => $capital,
                'interest_collected' => $interes,
                'mora_periodo' => $mora_final,
                'unrecoverable_amount' => $validatedData['unrecoverable_amount'],
                'user_id' => Auth::id() ?? 1,
                'notes' => $validatedData['notes'],
            ]);

            // 2. Crear Póliza Contable usando el Servicio
            $accountingService->createJournalFromRecovery($recovery);

            DB::commit();
            return redirect()->route('recoveries.index')->with('success', 'Recuperación guardada exitosamente y póliza generada.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error fatal al guardar recuperación: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error en el sistema al guardar la cobranza (Revisa el catálogo de cuentas).')->withInput();
        }
    }

    public function edit(Recovery $recovery)
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        return view('recoveries.edit', compact('recovery', 'sucursales'));
    }

    public function update(Request $request, Recovery $recovery, AccountingService $accountingService)
    {
        $currentYear = Carbon::now()->year;

        $validatedData = $request->validate([
            'sucursal_id' => 'required|exists:sucursales,id_sucursal',
            'year' => 'required|integer|min:2020|max:' . $currentYear,
            'month' => [
                'required', 'integer', 'min:1', 'max:12',
                // Ignoramos el ID actual para permitir actualizar en el mismo mes/año
                Rule::unique('recoveries')->where(function ($query) use ($request) {
                    return $query->where('sucursal_id', $request->sucursal_id)
                                 ->where('year', $request->year);
                })->ignore($recovery->id),
            ],
            'cobro_proyectado' => 'required|numeric|min:0',
            'capital_recovered' => 'required|numeric|min:0',
            'interest_collected' => 'required|numeric|min:0',
            'unrecoverable_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $proyectado = $validatedData['cobro_proyectado'];
        $capital = $validatedData['capital_recovered'];
        $interes = $validatedData['interest_collected'];
        
        $total_recuperado = $capital + $interes;
        $mora_calculada = $proyectado - $total_recuperado;
        $mora_final = $mora_calculada > 0 ? $mora_calculada : 0;

        DB::beginTransaction();

        try {
            // 1. Actualizar el registro de Recuperación
            $recovery->update([
                'sucursal_id' => $validatedData['sucursal_id'],
                'year' => $validatedData['year'],
                'month' => $validatedData['month'],
                'cobro_proyectado' => $proyectado,
                'capital_recovered' => $capital,
                'interest_collected' => $interes,
                'mora_periodo' => $mora_final,
                'unrecoverable_amount' => $validatedData['unrecoverable_amount'],
                'notes' => $validatedData['notes'],
            ]);

            // 2. Destruir la póliza vieja y crear la nueva
            $accountingService->updateJournalFromRecovery($recovery);

            DB::commit();
            return redirect()->route('recoveries.index')->with('success', 'Recuperación actualizada y póliza regenerada exitosamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error fatal al actualizar recuperación: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al actualizar la cobranza. El área de soporte ha sido notificada.')->withInput();
        }
    }

    public function destroy(Recovery $recovery, AccountingService $accountingService)
    {
        DB::beginTransaction();
        try {
            // 1. Borramos la póliza contable primero para mantener la integridad
            $accountingService->deleteJournalForModel($recovery);
            
            // 2. Borramos el registro operativo
            $recovery->delete();

            DB::commit();
            return redirect()->route('recoveries.index')->with('success', 'Registro de recuperación y póliza eliminados exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error fatal al eliminar recuperación: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al eliminar. El área de soporte ha sido notificada.');
        }
    }
}
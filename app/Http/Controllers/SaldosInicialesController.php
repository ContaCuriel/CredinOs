<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Journal;
use App\Models\Sucursal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SaldosInicialesController extends Controller
{
    public function create()
    {
        // Revisamos si ya existe una póliza
        $existePoliza = Journal::where('concept', 'like', '%Póliza de Apertura%')->exists();
        // Traemos las sucursales
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        
        return view('saldos_iniciales.create', compact('existePoliza', 'sucursales'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha_apertura' => 'required|date',
            'sucursal_id' => 'required|exists:sucursales,id_sucursal',
            'bancos' => 'required|numeric|min:0',
            'clientes' => 'required|numeric|min:0',
            'activo_fijo' => 'required|numeric|min:0',
            'pasivos' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Buscamos las cuentas maestras en el catálogo
            $ctaBancos = Account::where('code', '102.01')->firstOrFail();
            $ctaClientes = Account::where('code', '105.01')->firstOrFail();
            $ctaActivoFijo = Account::where('code', '152.01')->firstOrFail();
            $ctaPasivos = Account::where('code', '205.01')->firstOrFail();
            $ctaCapital = Account::where('code', '304.01')->firstOrFail();

            // 1. Creamos el encabezado de la póliza con la sucursal dinámica
            $journal = Journal::create([
                'date' => $request->fecha_apertura,
                'concept' => "Póliza de Apertura - Saldos Iniciales",
                'sucursal_id' => $request->sucursal_id, 
                'user_id' => Auth::id() ?? 1,
                // --- LA MAGIA PARA SALTAR EL ERROR DE LA BASE DE DATOS ---
                'sourceable_id' => $request->sucursal_id,
                'sourceable_type' => \App\Models\Sucursal::class,
            ]);

            // 2. Cargos (ACTIVOS)
            if ($request->bancos > 0) {
                $journal->entries()->create(['account_id' => $ctaBancos->id, 'debit' => $request->bancos, 'credit' => 0]);
            }
            if ($request->clientes > 0) {
                $journal->entries()->create(['account_id' => $ctaClientes->id, 'debit' => $request->clientes, 'credit' => 0]);
            }
            if ($request->activo_fijo > 0) {
                $journal->entries()->create(['account_id' => $ctaActivoFijo->id, 'debit' => $request->activo_fijo, 'credit' => 0]);
            }

            // 3. Abonos (PASIVOS)
            if ($request->pasivos > 0) {
                $journal->entries()->create(['account_id' => $ctaPasivos->id, 'debit' => 0, 'credit' => $request->pasivos]);
            }

            // 4. El Cuadre Mágico (CAPITAL CONTABLE)
            $totalActivos = $request->bancos + $request->clientes + $request->activo_fijo;
            $capital = $totalActivos - $request->pasivos;

            if ($capital > 0) {
                $journal->entries()->create(['account_id' => $ctaCapital->id, 'debit' => 0, 'credit' => $capital]);
            } elseif ($capital < 0) {
                $journal->entries()->create(['account_id' => $ctaCapital->id, 'debit' => abs($capital), 'credit' => 0]);
            }

            DB::commit();
            return redirect()->route('journals.index')->with('success', '¡Póliza de Apertura generada! Los saldos iniciales han sido registrados con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al generar la póliza. Detalles: ' . $e->getMessage())->withInput();
        }
    }
}
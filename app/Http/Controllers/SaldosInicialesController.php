<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Journal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SaldosInicialesController extends Controller
{
    public function create()
    {
        // Revisamos si ya existe una póliza de apertura para no duplicarla por accidente
        $existePoliza = Journal::where('concept', 'like', '%Póliza de Apertura%')->exists();
        
        return view('saldos_iniciales.create', compact('existePoliza'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha_apertura' => 'required|date',
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
            $ctaActivoFijo = Account::where('code', '152.01')->firstOrFail(); // Mobiliario y Equipo
            $ctaPasivos = Account::where('code', '205.01')->firstOrFail(); // Acreedores/Deudas varias
            $ctaCapital = Account::where('code', '304.01')->firstOrFail(); // Resultados de ejercicios anteriores

            // 1. Creamos el encabezado de la póliza
            $journal = Journal::create([
                'date' => $request->fecha_apertura,
                'concept' => "Póliza de Apertura - Saldos Iniciales",
                'sucursal_id' => 1, // O asigna la sucursal matriz
                'user_id' => Auth::id() ?? 1,
            ]);

            // 2. Cargos (Todo lo que tienes a tu favor / ACTIVOS)
            if ($request->bancos > 0) {
                $journal->entries()->create(['account_id' => $ctaBancos->id, 'debit' => $request->bancos, 'credit' => 0]);
            }
            if ($request->clientes > 0) {
                $journal->entries()->create(['account_id' => $ctaClientes->id, 'debit' => $request->clientes, 'credit' => 0]);
            }
            if ($request->activo_fijo > 0) {
                $journal->entries()->create(['account_id' => $ctaActivoFijo->id, 'debit' => $request->activo_fijo, 'credit' => 0]);
            }

            // 3. Abonos (Lo que debes / PASIVOS)
            if ($request->pasivos > 0) {
                $journal->entries()->create(['account_id' => $ctaPasivos->id, 'debit' => 0, 'credit' => $request->pasivos]);
            }

            // 4. El Cuadre Mágico (CAPITAL CONTABLE)
            // La fórmula contable universal: Capital = Activos - Pasivos
            $totalActivos = $request->bancos + $request->clientes + $request->activo_fijo;
            $capital = $totalActivos - $request->pasivos;

            if ($capital > 0) {
                // Si la empresa está sana, el capital se abona
                $journal->entries()->create(['account_id' => $ctaCapital->id, 'debit' => 0, 'credit' => $capital]);
            } elseif ($capital < 0) {
                // Si la empresa debe más de lo que tiene (números rojos), el capital se carga
                $journal->entries()->create(['account_id' => $ctaCapital->id, 'debit' => abs($capital), 'credit' => 0]);
            }

            DB::commit();
            return redirect()->route('journals.index')->with('success', '¡Póliza de Apertura generada! Los saldos iniciales han sido registrados con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al generar la póliza: Asegúrate de que las cuentas existan en el catálogo. Detalles: ' . $e->getMessage());
        }
    }
}
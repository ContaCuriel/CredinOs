<?php

namespace App\Services;

use App\Models\Gasto;
use App\Models\Account;
use App\Models\Journal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Placement;
use App\Models\Recovery;
use Carbon\Carbon;
use Throwable;

class AccountingService
{
    // --- PÓLIZA DE GASTO ---
    public function createJournalFromExpense(Gasto $gasto): ?Journal
    {
        if ($gasto->journal()->exists()) return null;

        $bancoAccount = Account::where('code', '102.01')->first();
        
        if (!$bancoAccount) {
            throw new \Exception("ERROR CONTABLE: No se encontró la cuenta de Bancos (102.01).");
        }
        if (!isset($gasto->categoria->account_id)) {
            throw new \Exception("ERROR CONTABLE: La categoría de este gasto no tiene una cuenta contable asignada.");
        }

        return DB::transaction(function () use ($gasto, $bancoAccount) {
            $journal = Journal::create([
                'date' => $gasto->fecha_gasto,
                'concept' => "Gasto: " . ($gasto->categoria->nombre ?? 'S/N') . " - " . ($gasto->proveedor->nombre ?? ''),
                'sourceable_id' => $gasto->id,
                'sourceable_type' => Gasto::class,
                'sucursal_id' => $gasto->sucursal_id, // CORREGIDO
                'user_id' => $gasto->usuario_registra_id, // CORREGIDO
            ]);

            // CARGO al gasto (Entra el gasto)
            $journal->entries()->create([
                'account_id' => $gasto->categoria->account_id, 
                'debit' => $gasto->monto_total, 
                'credit' => 0
            ]);
            
            // ABONO a bancos (Sale el dinero)
            $journal->entries()->create([
                'account_id' => $bancoAccount->id, 
                'debit' => 0, 
                'credit' => $gasto->monto_total
            ]);

            return $journal;
        });
    }

    // --- PÓLIZA DE COLOCACIÓN ---
    public function createJournalFromPlacement(Placement $placement): ?Journal
    {
        if (!$placement || $placement->journal()->exists()) return null;

        $clientesAccount = Account::where('code', '105.01')->first();
        $bancoAccount = Account::where('code', '102.01')->first();

        // Si faltan las cuentas, forzamos un error fatal para que te des cuenta
        if (!$clientesAccount || !$bancoAccount) {
            throw new \Exception("ERROR CONTABLE: Faltan las cuentas 105.01 o 102.01 en el catálogo de esta empresa.");
        }

        return DB::transaction(function () use ($placement, $clientesAccount, $bancoAccount) {
            $fecha = Carbon::create($placement->year, $placement->month, 1)->endOfMonth()->format('Y-m-d');
            $nombreSuc = $placement->sucursal ? $placement->sucursal->nombre_sucursal : "Sucursal";

            $journal = Journal::create([
                'date'            => $fecha,
                'concept'         => "Colocación Mensual: $nombreSuc ({$placement->month}/{$placement->year})",
                'sourceable_id'   => $placement->id,
                'sourceable_type' => Placement::class,
                'sucursal_id'     => $placement->sucursal_id,
                'user_id'         => $placement->user_id,
            ]);

            $journal->entries()->create(['account_id' => $clientesAccount->id, 'debit' => $placement->amount, 'credit' => 0]);
            $journal->entries()->create(['account_id' => $bancoAccount->id, 'debit' => 0, 'credit' => $placement->amount]);

            return $journal;
        });
    }
    // --- PÓLIZA DE RECUPERACIÓN ---
    public function createJournalFromRecovery(Recovery $recovery): ?Journal
    {
        try {
            if ($recovery->journal()->exists()) return null;

            $bancoAccount = Account::where('code', '102.01')->first();
            $clientesAccount = Account::where('code', '105.01')->first();
            $interesesAccount = Account::where('code', '401.32')->first();
            $castigosAccount = Account::where('code', '601.10')->first();

            if (!$bancoAccount || !$clientesAccount) return null;

            return DB::transaction(function () use ($recovery, $bancoAccount, $clientesAccount, $interesesAccount, $castigosAccount) {
                $nombreSuc = $recovery->sucursal->nombre_sucursal ?? "Sucursal";
                
                $journal = Journal::create([
                    'date' => Carbon::create($recovery->year, $recovery->month, 1)->endOfMonth()->format('Y-m-d'),
                    'concept' => "Recuperación Mensual: $nombreSuc ({$recovery->month}/{$recovery->year})",
                    'sourceable_id' => $recovery->id,
                    'sourceable_type' => Recovery::class,
                    'sucursal_id' => $recovery->sucursal_id, // <--- CORREGIDO AQUÍ
                    'user_id' => $recovery->user_id,
                ]);

                $totalCashIn = $recovery->capital_recovered + $recovery->interest_collected;
                if ($totalCashIn > 0) $journal->entries()->create(['account_id' => $bancoAccount->id, 'debit' => $totalCashIn, 'credit' => 0]);
                if ($recovery->interest_collected > 0) $journal->entries()->create(['account_id' => $interesesAccount->id, 'debit' => 0, 'credit' => $recovery->interest_collected]);
                if ($recovery->capital_recovered > 0) $journal->entries()->create(['account_id' => $clientesAccount->id, 'debit' => 0, 'credit' => $recovery->capital_recovered]);
                
                return $journal;
            });
        } catch (Throwable $e) {
            Log::error("Error Recovery: " . $e->getMessage());
            return null;
        }
    }
}
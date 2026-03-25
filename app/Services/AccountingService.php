<?php

namespace App\Services;

use App\Models\Gasto;
use App\Models\Account;
use App\Models\Journal;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\Placement;
use App\Models\Recovery;
use Carbon\Carbon;

class AccountingService
{
    // --- PÓLIZA DE GASTO ---
    public function createJournalFromGasto(Gasto $gasto): ?Journal
    {
        try {
            if ($gasto->journal()->exists()) return null;

            $bancoAccount = Account::where('code', '102.01')->first();
            if (!$bancoAccount || !isset($gasto->categoria->account_id)) return null;

            return DB::transaction(function () use ($gasto, $bancoAccount) {
                return Journal::create([
                    'date' => $gasto->fecha_gasto,
                    'concept' => "Gasto: " . ($gasto->categoria->nombre ?? 'S/N'),
                    'sourceable_id' => $gasto->id,
                    'sourceable_type' => Gasto::class,
                    'id_sucursal' => $gasto->id_sucursal,
                    'user_id' => $gasto->user_id,
                ])->entries()->createMany([
                    ['account_id' => $gasto->categoria->account_id, 'debit' => $gasto->monto_total, 'credit' => 0],
                    ['account_id' => $bancoAccount->id, 'debit' => 0, 'credit' => $gasto->monto_total],
                ]);
            });
        } catch (Exception $e) {
            Log::error("Error Gasto: " . $e->getMessage());
            return null;
        }
    }

    // --- PÓLIZA DE COLOCACIÓN (TU ERROR ESTABA AQUÍ) ---
    public function createJournalFromPlacement(Placement $placement): ?Journal
    {
        try {
            // 1. Verificación básica
            if (!$placement || $placement->journal()->exists()) return null;

            // 2. Buscar cuentas (Asegúrate que estos códigos existan en tu DB)
            $clientesAccount = Account::where('code', '105.01')->first();
            $bancoAccount = Account::where('code', '102.01')->first();

            if (!$clientesAccount || !$bancoAccount) {
                Log::error("[ACCOUNTING] No se hallaron cuentas 105.01/102.01 para Placement ID: " . $placement->id);
                return null;
            }

            // 3. Ejecutar transacción
            return DB::transaction(function () use ($placement, $clientesAccount, $bancoAccount) {
                
                // Calculamos la fecha de póliza
                $fecha = Carbon::create($placement->year, $placement->month, 1)->endOfMonth()->format('Y-m-d');
                
                // Intentamos obtener el nombre de la sucursal de forma segura
                $nombreSuc = "Sucursal";
                if ($placement->sucursal) {
                    $nombreSuc = $placement->sucursal->nombre_sucursal;
                }

                // Creamos el Journal
                $journal = Journal::create([
                    'date'            => $fecha,
                    'concept'         => "Colocación Mensual: $nombreSuc ({$placement->month}/{$placement->year})",
                    'sourceable_id'   => $placement->id,
                    'sourceable_type' => Placement::class,
                    'id_sucursal'     => $placement->sucursal_id,
                    'user_id'         => $placement->user_id,
                ]);

                // Creamos los asientos uno por uno (es más seguro que createMany para debuggear)
                $journal->entries()->create([
                    'account_id' => $clientesAccount->id,
                    'debit'      => $placement->amount,
                    'credit'     => 0,
                ]);

                $journal->entries()->create([
                    'account_id' => $bancoAccount->id,
                    'debit'      => 0,
                    'credit'     => $placement->amount,
                ]);

                Log::info("[ACCOUNTING] Póliza creada con éxito para Placement ID: " . $placement->id);
                return $journal;
            });

        } catch (\Exception $e) {
            Log::error("[ACCOUNTING] ERROR CRÍTICO en Placement ID {$placement->id}: " . $e->getMessage());
            return null;
        }
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
                $nombreSuc = $recovery->sucursal->nombre_sucursal ?? "Sucursal #".$recovery->sucursal_id;
                
                $journal = Journal::create([
                    'date' => Carbon::create($recovery->year, $recovery->month, 1)->endOfMonth()->format('Y-m-d'),
                    'concept' => "Recuperación Mensual: $nombreSuc ({$recovery->month}/{$recovery->year})",
                    'sourceable_id' => $recovery->id,
                    'sourceable_type' => Recovery::class,
                    'id_sucursal' => $recovery->sucursal_id,
                    'user_id' => $recovery->user_id,
                ]);

                $totalCashIn = $recovery->capital_recovered + $recovery->interest_collected;
                if ($totalCashIn > 0) $journal->entries()->create(['account_id' => $bancoAccount->id, 'debit' => $totalCashIn, 'credit' => 0]);
                if ($recovery->interest_collected > 0) $journal->entries()->create(['account_id' => $interesesAccount->id, 'debit' => 0, 'credit' => $recovery->interest_collected]);
                if ($recovery->capital_recovered > 0) $journal->entries()->create(['account_id' => $clientesAccount->id, 'debit' => 0, 'credit' => $recovery->capital_recovered]);
                
                return $journal;
            });
        } catch (Exception $e) {
            Log::error("Error Recovery: " . $e->getMessage());
            return null;
        }
    }
}
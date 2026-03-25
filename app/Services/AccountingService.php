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
    public function createJournalFromGasto(Gasto $gasto): ?Journal
    {
        Log::info("[ACCOUNTING_SERVICE] Iniciando proceso para gasto ID: " . $gasto->id);

        if ($gasto->journal()->exists()) {
            Log::info("[ACCOUNTING_SERVICE] El gasto ID: {$gasto->id} ya tiene una póliza. Proceso detenido.");
            return null;
        }

        if (!isset($gasto->categoria) || !isset($gasto->categoria->account_id)) {
            Log::warning("[ACCOUNTING_SERVICE] FALLO: La categoría del gasto ID: {$gasto->id} no tiene una cuenta contable asignada.");
            return null;
        }

        $bancoAccount = Account::where('code', '102.01')->first();
        if (!$bancoAccount) {
            Log::error("[ACCOUNTING_SERVICE] FALLO CRÍTICO: No se encontró la cuenta de Bancos (102.01).");
            return null;
        }

        try {
            return DB::transaction(function () use ($gasto, $bancoAccount) {
                $proveedorNombre = isset($gasto->proveedor->nombre) ? $gasto->proveedor->nombre : 'N/A';

                $journal = Journal::create([
                    'date' => $gasto->fecha_gasto,
                    'concept' => "Gasto: " . $gasto->categoria->nombre . " | Proveedor: " . $proveedorNombre,
                    'sourceable_id' => $gasto->id,
                    'sourceable_type' => Gasto::class,
                    'id_sucursal' => $gasto->id_sucursal, // Vinculamos la sucursal del gasto
                    'user_id' => $gasto->user_id,
                ]);

                $journal->entries()->create([
                    'account_id' => $gasto->categoria->account_id,
                    'debit' => $gasto->monto_total,
                    'credit' => 0,
                ]);

                $journal->entries()->create([
                    'account_id' => $bancoAccount->id,
                    'debit' => 0,
                    'credit' => $gasto->monto_total,
                ]);

                return $journal;
            });
        } catch (Exception $e) {
            Log::error("[ACCOUNTING_SERVICE] EXCEPCIÓN en gasto ID {$gasto->id}: " . $e->getMessage());
            return null;
        }
    }

    public function createJournalFromPlacement(Placement $placement): ?Journal
    {
        if ($placement->journal()->exists()) {
            return null;
        }

        // Usamos first() y validamos para evitar el bloqueo del sistema si no existe el código
        $clientesAccount = Account::where('code', '105.01')->first();
        $bancoAccount = Account::where('code', '102.01')->first();

        if (!$clientesAccount || !$bancoAccount) {
            Log::error("[ACCOUNTING_SERVICE] No se encontraron cuentas 105.01 o 102.01 para Colocación.");
            return null;
        }

        return DB::transaction(function () use ($placement, $clientesAccount, $bancoAccount) {
            $journal = Journal::create([
                // CORRECCIÓN: Fecha establecida como el último día del mes/año del registro
                'date' => Carbon::create($placement->year, $placement->month, 1)->endOfMonth()->format('Y-m-d'),
                'concept' => "Colocación de créditos Suc. {$placement->sucursal->nombre_sucursal} - {$placement->month}/{$placement->year}",
                'sourceable_id' => $placement->id,
                'sourceable_type' => Placement::class,
                'id_sucursal' => $placement->sucursal_id, // Vital para reportes
                'user_id' => $placement->user_id,
            ]);

            $journal->entries()->create([
                'account_id' => $clientesAccount->id,
                'debit' => $placement->amount,
                'credit' => 0,
            ]);

            $journal->entries()->create([
                'account_id' => $bancoAccount->id,
                'debit' => 0,
                'credit' => $placement->amount,
            ]);

            return $journal;
        });
    }

    public function createJournalFromRecovery(Recovery $recovery): ?Journal
    {
        if ($recovery->journal()->exists()) {
            return null;
        }

        $bancoAccount = Account::where('code', '102.01')->first();
        $clientesAccount = Account::where('code', '105.01')->first();
        $interesesAccount = Account::where('code', '401.32')->first();
        $castigosAccount = Account::where('code', '601.10')->first();

        if (!$bancoAccount || !$clientesAccount || !$interesesAccount || !$castigosAccount) {
            Log::error("[ACCOUNTING_SERVICE] Faltan cuentas del SAT para Recuperación.");
            return null;
        }

        return DB::transaction(function () use ($recovery, $bancoAccount, $clientesAccount, $interesesAccount, $castigosAccount) {
            $journal = Journal::create([
                // CORRECCIÓN: Fecha establecida como el último día del mes/año del registro
                'date' => Carbon::create($recovery->year, $recovery->month, 1)->endOfMonth()->format('Y-m-d'),
                'concept' => "Recuperación de cartera Suc. {$recovery->sucursal->nombre_sucursal} - {$recovery->month}/{$recovery->year}",
                'sourceable_id' => $recovery->id,
                'sourceable_type' => Recovery::class,
                'id_sucursal' => $recovery->sucursal_id, // Vital para reportes
                'user_id' => $recovery->user_id,
            ]);

            $totalCashIn = $recovery->capital_recovered + $recovery->interest_collected;

            if ($totalCashIn > 0) {
                $journal->entries()->create(['account_id' => $bancoAccount->id, 'debit' => $totalCashIn, 'credit' => 0]);
            }

            if ($recovery->interest_collected > 0) {
                $journal->entries()->create(['account_id' => $interesesAccount->id, 'debit' => 0, 'credit' => $recovery->interest_collected]);
            }
            
            if ($recovery->capital_recovered > 0) {
                $journal->entries()->create(['account_id' => $clientesAccount->id, 'debit' => 0, 'credit' => $recovery->capital_recovered]);
            }

            if ($recovery->unrecoverable_amount > 0) {
                $journal->entries()->create(['account_id' => $castigosAccount->id, 'debit' => $recovery->unrecoverable_amount, 'credit' => 0]);
                $journal->entries()->create(['account_id' => $clientesAccount->id, 'debit' => 0, 'credit' => $recovery->unrecoverable_amount]);
            }

            return $journal;
        });
    }
}
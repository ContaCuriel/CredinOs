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
        Log::info("[ACCOUNTING_SERVICE] Gasto ID: {$gasto->id} tiene la cuenta contable asignada: " . $gasto->categoria->account_id);

        $bancoAccount = Account::where('code', '102.01')->first();
        if (!$bancoAccount) {
            Log::error("[ACCOUNTING_SERVICE] FALLO CRÍTICO: No se encontró la cuenta de Bancos (102.01).");
            // En un caso real, podrías lanzar una excepción o notificar a un admin.
            return null;
        }
        Log::info("[ACCOUNTING_SERVICE] Cuenta de Bancos (102.01) encontrada con ID: " . $bancoAccount->id);

        try {
            return DB::transaction(function () use ($gasto, $bancoAccount) {
                Log::info("[ACCOUNTING_SERVICE] Iniciando transacción para gasto ID: {$gasto->id}");

                $proveedorNombre = isset($gasto->proveedor->nombre) ? $gasto->proveedor->nombre : 'N/A';

                $journal = Journal::create([
                    'date' => $gasto->fecha_gasto,
                    'concept' => "Gasto: " . $gasto->categoria->nombre . " | Proveedor: " . $proveedorNombre,
                    'sourceable_id' => $gasto->id,
                    'sourceable_type' => Gasto::class,
                ]);
                Log::info("[ACCOUNTING_SERVICE] Póliza (Journal) ID: {$journal->id} creada.");

                $journal->entries()->create([
                    'account_id' => $gasto->categoria->account_id,
                    'debit' => $gasto->monto_total,
                    'credit' => 0,
                ]);
                Log::info("[ACCOUNTING_SERVICE] Asiento de CARGO creado para cuenta ID: " . $gasto->categoria->account_id);

                $journal->entries()->create([
                    'account_id' => $bancoAccount->id,
                    'debit' => 0,
                    'credit' => $gasto->monto_total,
                ]);
                Log::info("[ACCOUNTING_SERVICE] Asiento de ABONO creado para cuenta ID: " . $bancoAccount->id);
                
                Log::info("[ACCOUNTING_SERVICE] ÉXITO: Proceso completado para gasto ID: {$gasto->id}");
                return $journal;
            });
        } catch (Exception $e) {
            Log::error("[ACCOUNTING_SERVICE] EXCEPCIÓN en la transacción para gasto ID {$gasto->id}: " . $e->getMessage());
            return null;
        }
    }
 public function createJournalFromPlacement(Placement $placement): ?Journal
    {
        if ($placement->journal()->exists()) {
            return null;
        }

        $clientesAccount = Account::where('code', '105.01')->firstOrFail();
        $bancoAccount = Account::where('code', '102.01')->firstOrFail();

        return DB::transaction(function () use ($placement, $clientesAccount, $bancoAccount) {
            $journal = Journal::create([
                'date' => Carbon::create($placement->year, $placement->month)->endOfMonth(),
                'concept' => "Colocación de créditos Suc. {$placement->sucursal->nombre_sucursal} - {$placement->month}/{$placement->year}",
                'sourceable_id' => $placement->id,
                'sourceable_type' => Placement::class,
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

        // Obtenemos las cuentas del catálogo del SAT que vamos a necesitar.
        $bancoAccount = Account::where('code', '102.01')->firstOrFail(); // Bancos
        $clientesAccount = Account::where('code', '105.01')->firstOrFail(); // Clientes
        $interesesAccount = Account::where('code', '401.32')->firstOrFail(); // Ingresos por intereses
        $castigosAccount = Account::where('code', '601.10')->firstOrFail(); // Gastos por castigos (incobrables)

        return DB::transaction(function () use ($recovery, $bancoAccount, $clientesAccount, $interesesAccount, $castigosAccount) {
            $journal = Journal::create([
                'date' => Carbon::create($recovery->year, $recovery->month)->endOfMonth(),
                'concept' => "Recuperación de cartera Suc. {$recovery->sucursal->nombre_sucursal} - {$recovery->month}/{$recovery->year}",
                'sourceable_id' => $recovery->id,
                'sourceable_type' => Recovery::class,
            ]);

            $totalCashIn = $recovery->capital_recovered + $recovery->interest_collected;

            // CARGO a Bancos por el total de dinero que entró.
            if ($totalCashIn > 0) {
                $journal->entries()->create(['account_id' => $bancoAccount->id, 'debit' => $totalCashIn, 'credit' => 0]);
            }

            // ABONO a Ingresos por los intereses cobrados.
            if ($recovery->interest_collected > 0) {
                $journal->entries()->create(['account_id' => $interesesAccount->id, 'debit' => 0, 'credit' => $recovery->interest_collected]);
            }
            
            // ABONO a Clientes por el capital recuperado (disminuye la deuda del cliente).
            if ($recovery->capital_recovered > 0) {
                $journal->entries()->create(['account_id' => $clientesAccount->id, 'debit' => 0, 'credit' => $recovery->capital_recovered]);
            }

            // Asiento por los préstamos castigados como incobrables.
            if ($recovery->unrecoverable_amount > 0) {
                // CARGO a Gastos por el monto que se da por perdido.
                $journal->entries()->create(['account_id' => $castigosAccount->id, 'debit' => $recovery->unrecoverable_amount, 'credit' => 0]);
                // ABONO a Clientes para cancelar esa deuda del balance.
                $journal->entries()->create(['account_id' => $clientesAccount->id, 'debit' => 0, 'credit' => $recovery->unrecoverable_amount]);
            }

            return $journal;
        });
    }



}
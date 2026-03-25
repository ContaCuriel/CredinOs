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
        Log::info("[ACCOUNTING_SERVICE] Iniciando póliza para colocación ID: {$placement->id}");

        if ($placement->journal()->exists()) {
            Log::info("[ACCOUNTING_SERVICE] Colocación ID: {$placement->id} ya tiene póliza.");
            return null;
        }

        // Buscamos las cuentas (asegúrate que existan estos códigos en tu catálogo)
        $clientesAccount = Account::where('code', '105.01')->first();
        $bancoAccount = Account::where('code', '102.01')->first();

        if (!$clientesAccount || !$bancoAccount) {
            Log::error("[ACCOUNTING_SERVICE] Faltan cuentas 105.01 o 102.01 para Colocación.");
            return null;
        }

        // Usamos un bloque try-catch GENERAL para que la contabilidad no truene el guardado.
        try {
            return DB::transaction(function () use ($placement, $clientesAccount, $bancoAccount) {
                Log::info("[ACCOUNTING_SERVICE] Iniciando transacción para colocación ID: {$placement->id}");

                // Fabricamos la fecha con seguridad, usando el día 1 y capturando el final del mes.
                $fechaPpoliza = Carbon::create($placement->year, $placement->month, 1)->endOfMonth()->format('Y-m-d');

                $journal = Journal::create([
                    'date' => $fechaPpoliza, // Fecha del último día del mes contable
                    'concept' => "Colocación de créditos Suc. {$placement->sucursal->nombre_sucursal} - {$placement->month}/{$placement->year}",
                    'sourceable_id' => $placement->id,
                    'sourceable_type' => Placement::class,
                    'id_sucursal' => $placement->sucursal_id, // Vital para reportes
                    'user_id' => $placement->user_id,
                ]);
                Log::info("[ACCOUNTING_SERVICE] Póliza (Journal) ID: {$journal->id} creada.");

                // Asiento 1: CARGO a Clientes/Cartera (Activo aumenta)
                $journal->entries()->create([
                    'account_id' => $clientesAccount->id,
                    'debit' => $placement->amount,
                    'credit' => 0,
                ]);

                // Asiento 2: ABONO a Bancos/Caja (Activo disminuye)
                $journal->entries()->create([
                    'account_id' => $bancoAccount->id,
                    'debit' => 0,
                    'credit' => $placement->amount,
                ]);

                Log::info("[ACCOUNTING_SERVICE] ÉXITO: Póliza completada para colocación ID: {$placement->id}");
                return $journal;
            });
        } catch (Exception $e) {
            // Si la contabilidad falla, registramos el error en el log pero liberamos la pantalla.
            Log::error("[ACCOUNTING_SERVICE] EXCEPCIÓN en colocación ID {$placement->id}: " . $e->getMessage());
            return null;
        }
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
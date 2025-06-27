<?php

namespace App\Services;

use App\Models\Gasto;
use App\Models\Account;
use App\Models\Journal;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

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
}
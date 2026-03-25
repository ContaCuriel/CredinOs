<?php

namespace App\Observers;

use App\Models\Recovery;
use App\Services\AccountingService;
use Illuminate\Support\Facades\Log;

class RecoveryObserver
{
    /**
     * Handle the Recovery "created" event.
     */
    public function created(Recovery $recovery): void
    {
        Log::info("Iniciando Observer para Recuperación ID: " . $recovery->id);

        try {
            // Llamamos al servicio contable
            (new AccountingService())->createJournalFromRecovery($recovery);
            
            Log::info("Póliza procesada exitosamente para Recuperación ID: " . $recovery->id);
        } catch (\Exception $e) {
            // Si la contabilidad falla, logueamos pero NO bloqueamos al usuario
            Log::error("ERROR en RecoveryObserver ID {$recovery->id}: " . $e->getMessage());
        }
    }

    /**
     * Handle the Recovery "updated" event.
     */
    public function updated(Recovery $recovery): void
    {
        //
    }

    /**
     * Handle the Recovery "deleted" event.
     */
    public function deleted(Recovery $recovery): void
    {
        //
    }
}
<?php

namespace App\Observers;

use App\Models\Gasto;
use App\Services\AccountingService;

class GastoObserver
{
    /**
     * Handle the Gasto "created" event.
     */
    public function created(Gasto $gasto): void
    {
        // Si un gasto se crea directamente como "Aprobado",
        // generamos su póliza de inmediato.
        if ($gasto->estado === 'Aprobado') {
            $accountingService = new AccountingService();
            $accountingService->createJournalFromGasto($gasto);
        }
    }

    /**
     * Handle the Gasto "updated" event.
     */
    public function updated(Gasto $gasto): void
    {
        // Verificamos si el estado cambió a "Aprobado".
        if ($gasto->isDirty('estado') && $gasto->estado === 'Aprobado') {
            $accountingService = new AccountingService();
            $accountingService->createJournalFromGasto($gasto);
        }
    }
}

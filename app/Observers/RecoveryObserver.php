<?php

namespace App\Observers;

use App\Models\Recovery;
use App\Services\AccountingService;

class RecoveryObserver
{
    public $afterCommit = true;

    /**
     * Handle the Recovery "created" event.
     */
    public function created(Recovery $recovery): void
    {
        (new AccountingService())->createJournalFromRecovery($recovery);
    }
}

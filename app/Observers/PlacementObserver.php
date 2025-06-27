<?php

namespace App\Observers;

use App\Models\Placement;
use App\Services\AccountingService;

class PlacementObserver
{
    /**
     * Handle the Placement "created" event.
     */
    public function created(Placement $placement): void
    {
        (new AccountingService())->createJournalFromPlacement($placement);
    }

    /**
     * Handle the Placement "updated" event.
     */
    public function updated(Placement $placement): void
    {
        //
    }

    /**
     * Handle the Placement "deleted" event.
     */
    public function deleted(Placement $placement): void
    {
        //
    }

    /**
     * Handle the Placement "restored" event.
     */
    public function restored(Placement $placement): void
    {
        //
    }

    /**
     * Handle the Placement "force deleted" event.
     */
    public function forceDeleted(Placement $placement): void
    {
        //
    }
}

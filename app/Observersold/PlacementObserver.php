<?php

namespace App\Observers;

use App\Models\Placement;
use App\Services\AccountingService;
use Carbon\Carbon;

class PlacementObserver
{
    public function created(Placement $placement): void
    {
        if (!$placement->placement_date) {
            $year = $placement->year ?? date('Y');
            $month = $placement->month ?? date('m');
            $placement->placement_date = Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');
        }

        // SIN TRY-CATCH: Si esto falla, el controlador atrapará la explosión
        (new AccountingService())->createJournalFromPlacement($placement);
    }
}
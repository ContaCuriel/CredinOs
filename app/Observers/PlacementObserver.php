<?php

namespace App\Observers;

use App\Models\Placement;
use App\Services\AccountingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PlacementObserver
{
    /**
     * Handle the Placement "created" event.
     */
    public function created(Placement $placement): void
    {
        Log::info("Iniciando Observer para Placement ID: " . $placement->id);

        try {
            if (!$placement->placement_date) {
                $year = $placement->year ?? date('Y');
                $month = $placement->month ?? date('m');
                
                $placement->placement_date = Carbon::create($year, $month, 1)
                    ->endOfMonth()
                    ->format('Y-m-d');
            }

             (new AccountingService())->createJournalFromPlacement($placement);

            Log::info("Póliza procesada para Placement ID: " . $placement->id);

        } catch (\Exception $e) {
            Log::error("ERROR CRÍTICO en PlacementObserver: " . $e->getMessage());
        }
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
}
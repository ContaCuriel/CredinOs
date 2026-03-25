<?php

namespace App\Observers;

use App\Models\Placement;
use App\Services\AccountingService;
use Carbon\Carbon;

class PlacementObserver
{
    /**
     * Handle the Placement "created" event.
     */
    public function created(Placement $placement): void
    {
        // Si el registro viene del módulo mensual (tiene year y month)
        if ($placement->year && $placement->month) {
            // Creamos la fecha del último día de ese mes
            // Ahora Carbon funcionará correctamente porque ya lo importamos arriba
            $fechaFicticia = Carbon::create($placement->year, $placement->month, 1)
                                   ->endOfMonth()
                                   ->format('Y-m-d');
            
            $placement->placement_date = $fechaFicticia;
        }

        try {
            (new AccountingService())->createJournalFromPlacement($placement);
        } catch (\Exception $e) {
            \Log::error("Error al crear póliza de colocación: " . $e->getMessage());
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

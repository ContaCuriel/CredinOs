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
            // Aseguramos que el objeto tenga una fecha válida aunque no esté en la DB
            // para que el AccountingService no truene al leerla.
            if (!$placement->placement_date) {
                $year = $placement->year ?? date('Y');
                $month = $placement->month ?? date('m');
                
                // Seteamos la propiedad en el objeto (en memoria)
                $placement->placement_date = Carbon::create($year, $month, 1)
                    ->endOfMonth()
                    ->format('Y-m-d');
            }

            // Llamamos al servicio
            $service = new AccountingService();
            $service->createJournalFromPlacement($placement);

            Log::info("Póliza procesada para Placement ID: " . $placement->id);

        } catch (\Exception $e) {
            Log::error("ERROR CRÍTICO en PlacementObserver: " . $e->getMessage());
            // No relanzamos la excepción para que al menos el registro de colocación se guarde
            // y el usuario no se quede con la pantalla trabada.
        }
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

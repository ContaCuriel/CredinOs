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
        // Si el registro viene del módulo mensual (tiene year y month)
        // fabricamos una fecha real para que la contabilidad no explote.
        if ($placement->year && $placement->month) {
            // Creamos la fecha del último día de ese mes
            $fechaFicticia = Carbon::create($placement->year, $placement->month, 1)
                                   ->endOfMonth()
                                   ->format('Y-m-d');
            
            // Le "inyectamos" la fecha al objeto temporalmente para el servicio
            $placement->placement_date = $fechaFicticia;
        }

        // Ahora sí llamamos al servicio contable
        try {
            (new AccountingService())->createJournalFromPlacement($placement);
        } catch (\Exception $e) {
            // Si la contabilidad falla, registramos el error en el log para que no se trabe la pantalla
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

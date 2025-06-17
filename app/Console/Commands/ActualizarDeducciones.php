<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DeduccionEmpleado;
use Carbon\Carbon;

class ActualizarDeducciones extends Command
{
    protected $signature = 'deducciones:actualizar';
    protected $description = 'Procesa las deducciones activas (préstamos, ahorros) según las fechas de corte quincenales.';

    public function handle()
    {
        $this->info('-> Iniciando proceso de actualización de deducciones...');
        $hoy = Carbon::today();
        
        DeduccionEmpleado::where('status', 'Activo')->chunkById(100, function ($deduccionesActivas) use ($hoy) {
            foreach ($deduccionesActivas as $deduccion) {
                $fechaInicio = Carbon::parse($deduccion->fecha_solicitud);
                $ultimaReferencia = $deduccion->fecha_ultimo_descuento 
                    ? Carbon::parse($deduccion->fecha_ultimo_descuento) 
                    : $fechaInicio->copy()->subDay();

                if ($hoy->isAfter($ultimaReferencia)) {
                    $this->procesarQuincenasPendientes($deduccion, $ultimaReferencia, $hoy);
                }
            }
        });

        $this->info('-> ¡Actualización de deducciones completada!');
        return 0;
    }

    private function procesarQuincenasPendientes(DeduccionEmpleado $deduccion, Carbon $desde, Carbon $hasta)
    {
        $fechaIterador = $desde->copy();
        $fechaSolicitud = Carbon::parse($deduccion->fecha_solicitud);

        while ($fechaIterador->lessThan($hasta)) {
            $fechaIterador->addDay();

            if (($fechaIterador->day == 15 || $fechaIterador->isLastOfMonth()) && $fechaIterador >= $fechaSolicitud) {
                $this->line("  - Procesando deducción #{$deduccion->id} en fecha {$fechaIterador->toDateString()}");
                switch ($deduccion->tipo_deduccion) {
                    case 'Préstamo':
                        $deduccion->saldo_pendiente -= $deduccion->monto_quincenal;
                        $deduccion->quincenas_pagadas += 1;
                        if ($deduccion->saldo_pendiente <= 0) {
                            $deduccion->saldo_pendiente = 0;
                            $deduccion->status = 'Pagado';
                            $this->warn("    ¡Préstamo #{$deduccion->id} LIQUIDADO!");
                        }
                        break;
                    case 'Caja de Ahorro':
                        $deduccion->monto_acumulado += $deduccion->monto_quincenal;
                        break;
                }
                $deduccion->fecha_ultimo_descuento = $fechaIterador->copy();
                $deduccion->save(); 
            }
        }
    }
}
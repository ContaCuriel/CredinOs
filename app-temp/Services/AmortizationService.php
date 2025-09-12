<?php

namespace App\Services;

use App\Models\Credito;
use App\Models\PaymentInstallment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AmortizationService
{
    /**
     * Genera la tabla de amortización completa para un crédito dado.
     */
    public function generateSchedule(Credito $credito): void
    {
        // Asegurarnos de que no generamos un calendario duplicado
        if ($credito->paymentInstallments()->exists()) {
            return;
        }

        // --- Lógica de Cálculo (Modelo Simple) ---
        // Nota: Este es un cálculo de interés simple/fijo. Se puede ajustar en el futuro.
        $montoPrincipal = $credito->monto_autorizado;
        // El interés total se calcula sobre el monto autorizado por el plazo completo
        $interesTotal = $montoPrincipal * ($credito->tasa_interes / 100);
        $montoTotalAPagar = $montoPrincipal + $interesTotal;

        $pagoRegular = $montoTotalAPagar / $credito->plazo;
        $pagoCapital = $montoPrincipal / $credito->plazo;
        $pagoInteres = $interesTotal / $credito->plazo;

        $fechaDeVencimiento = Carbon::parse($credito->fecha_desembolso);

        // Usamos una transacción para asegurar que se creen todos los pagos o ninguno
        DB::transaction(function () use ($credito, $pagoRegular, $pagoCapital, $pagoInteres, $fechaDeVencimiento) {
            for ($i = 1; $i <= $credito->plazo; $i++) {
                // Para créditos semanales, añadimos 7 días a la fecha del pago anterior
                $fechaDeVencimiento->addWeek();

                PaymentInstallment::create([
                    'credito_id'        => $credito->id_credito,
                    'numero_pago'       => $i,
                    'monto_pago'        => round($pagoRegular, 2),
                    'monto_capital'     => round($pagoCapital, 2),
                    'monto_interes'     => round($pagoInteres, 2),
                    'fecha_vencimiento' => $fechaDeVencimiento->toDateString(),
                    'status'            => 'Pendiente',
                ]);
            }
        });
    }
}
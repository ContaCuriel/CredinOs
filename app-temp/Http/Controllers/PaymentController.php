<?php

namespace App\Http\Controllers;

use App\Models\PaymentInstallment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Marca una cuota como pagada.
     */
    public function store(Request $request, PaymentInstallment $installment)
    {
        // Actualizamos el estatus y la fecha de pago de la cuota
        $installment->update([
            'status' => 'Pagado',
            'fecha_pago' => now(),
        ]);

        // TODO: Módulo Contable
        // Aquí es donde en el futuro llamaremos a nuestro AccountingService
        // para generar la póliza contable de forma automática.
        // Ejemplo: $this->accountingService->recordLoanPayment($installment);

        // Redirigimos de vuelta a la página de detalles del crédito
        return redirect()->route('creditos.show', $installment->credito_id)
                         ->with('success', 'Pago registrado exitosamente.');
    }
}
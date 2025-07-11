<?php

namespace App\Services;

use App\Models\Credito;
use Illuminate\Http\UploadedFile;

class ReconciliationService
{
    /**
     * Procesa un archivo de estado de cuenta CSV y busca coincidencias con pagos pendientes.
     *
     * @param UploadedFile $file
     * @return array
     */
    public function findMatchesInStatement(UploadedFile $file): array
    {
        $matches = [];
        $filePath = $file->getRealPath();
        
        // Abrimos el archivo CSV para leerlo
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            // Omitimos la primera línea si es de encabezados
            fgetcsv($handle);

            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // --- Lógica de Detección de Pagos ---
                // NOTA: Esto es un ejemplo y debe ser ajustado al formato de tu banco.
                // Asumimos:
                // Columna 2: Descripción de la transacción
                // Columna 4: Monto del depósito (crédito)
                $description = $row[2] ?? '';
                $depositAmount = floatval($row[4] ?? 0);

                // Si no es un depósito, continuamos a la siguiente línea
                if ($depositAmount <= 0) {
                    continue;
                }

                // Intentamos encontrar un número de referencia en la descripción
                preg_match('/CR-[A-Z0-9]+/', $description, $referenceFound);

                if (!empty($referenceFound)) {
                    $referenceNumber = $referenceFound[0];
                    
                    // Buscamos un crédito con esa referencia
                    $credito = Credito::where('reference_number', $referenceNumber)->first();

                    if ($credito) {
                        // Buscamos una cuota pendiente en ese crédito con un monto similar
                        $installment = $credito->paymentInstallments()
                            ->where('status', 'Pendiente')
                            // Buscamos un monto cercano para tolerar pequeñas variaciones
                            ->whereBetween('monto_pago', [$depositAmount - 1, $depositAmount + 1])
                            ->orderBy('fecha_vencimiento', 'asc')
                            ->first();

                        if ($installment) {
                            // ¡Encontramos una coincidencia!
                            $matches[] = [
                                'csv_row'     => $row,
                                'installment' => $installment,
                                'credito'     => $credito,
                            ];
                        }
                    }
                }
            }
            fclose($handle);
        }

        return $matches;
    }
}
<?php

namespace App\Services;

class CalculadoraImpuestosService
{
    /**
     * Tabla Quincenal de ISR 2024/2025 (Ejemplo aproximado Art. 96)
     * Estructura: [Limite Inferior, Limite Superior, Cuota Fija, Porcentaje Excedente]
     */
    protected $tablaIsrQuincenal = [
        [0.01, 368.10, 0.00, 1.92],
        [368.11, 3124.35, 7.05, 6.40],
        [3124.36, 5490.75, 183.45, 10.88],
        [5490.76, 6382.80, 441.00, 16.00],
        [6382.81, 7641.90, 583.65, 17.92],
        [7641.91, 15412.80, 809.25, 21.36],
        [15412.81, 24292.65, 2469.15, 23.52],
        // ... (Aquí puedes completar con la tabla oficial del SAT)
        [24292.66, 9999999.99, 4557.90, 30.00] 
    ];

    /**
     * Calcula los impuestos (ISR e IMSS) partiendo del Sueldo Bruto
     * @param float $sueldoBruto
     * @param bool $aplicaImss (Falso para Honorarios)
     * @return array
     */
    public function calcularDesdeBruto(float $sueldoBruto, bool $aplicaImss = true): array
    {
        $isr = $this->calcularISR($sueldoBruto);
        
        // Simulación de cuota obrero IMSS (~2.775% sobre sueldo base para cálculos estándar)
        // Aquí podrías meter el cálculo real con la UMA y el Salario Base de Cotización (SBC)
        $imss = $aplicaImss ? round($sueldoBruto * 0.02775, 2) : 0.00; 

        $neto = $sueldoBruto - $isr - $imss;

        return [
            'bruto' => round($sueldoBruto, 2),
            'isr'   => round($isr, 2),
            'imss  '=> round($imss, 2),
            'neto'  => round($neto, 2)
        ];
    }

    /**
     * Piramidación (Gross-up): Encuentra el Sueldo Bruto necesario para llegar a un Neto exacto
     * Utiliza un algoritmo de búsqueda binaria para precisión centesimal.
     * 
     * @param float $netoDeseado
     * @param bool $aplicaImss
     * @return array
     */
    public function calcularDesdeNeto(float $netoDeseado, bool $aplicaImss = true): array
    {
        // Rango de búsqueda para el salario bruto
        $limiteInferior = $netoDeseado;
        $limiteSuperior = $netoDeseado * 2; // El bruto rara vez será más del doble del neto
        $margenError = 0.01; // Tolerancia de 1 centavo
        $brutoEstimado = 0;

        // Búsqueda binaria iterativa
        while (($limiteSuperior - $limiteInferior) > $margenError) {
            $brutoEstimado = ($limiteInferior + $limiteSuperior) / 2;
            
            // Calculamos cuánto neto da este bruto estimado
            $calculo = $this->calcularDesdeBruto($brutoEstimado, $aplicaImss);
            $netoEstimado = $calculo['neto'];

            if ($netoEstimado < $netoDeseado) {
                // Si el neto estimado no alcanza, el bruto debe ser mayor
                $limiteInferior = $brutoEstimado;
            } else {
                // Si el neto estimado se pasa, el bruto debe ser menor
                $limiteSuperior = $brutoEstimado;
            }
        }

        // Devolvemos el desglose final usando el bruto exacto encontrado
        return $this->calcularDesdeBruto($brutoEstimado, $aplicaImss);
    }

    /**
     * Calcula el ISR quincenal según las tablas del SAT Art. 96
     */
    private function calcularISR(float $baseGravable): float
    {
        if ($baseGravable <= 0) return 0.00;

        foreach ($this->tablaIsrQuincenal as $rango) {
            $limiteInferior = $rango[0];
            $limiteSuperior = $rango[1];
            $cuotaFija = $rango[2];
            $porcentaje = $rango[3] / 100;

            if ($baseGravable >= $limiteInferior && $baseGravable <= $limiteSuperior) {
                $excedente = $baseGravable - $limiteInferior;
                $impuestoMarginal = $excedente * $porcentaje;
                return round($cuotaFija + $impuestoMarginal, 2);
            }
        }

        return 0.00;
    }
}
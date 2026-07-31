<?php

namespace App\Services;

class CalculadoraImpuestosService
{
    /**
     * Tabla Quincenal de ISR (Anexo 8 RMF)
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
        [24292.66, 46378.50, 4557.90, 30.00],
        [46378.51, 61838.10, 11183.55, 32.00],
        [61838.11, 185514.30, 16130.70, 34.00],
        [185514.31, 9999999.99, 58180.65, 35.00]
    ];

    /**
     * Parámetros para el Subsidio al Empleo (Decreto Mayo 2024, aplicable en 2026)
     * - Aplica solo a sueldos mensuales menores o iguales a $9,081.00 ($4,540.50 quincenal).
     * - El subsidio mensual es el 11.82% de la UMA mensual.
     * Nota: Actualiza el valor de la UMA Mensual vigente para 2026 según el INEGI.
     */
    protected $topeSueldoMensualSubsidio = 9081.00;
    protected $umaMensualVigente = 3457.40; // <-- Cambiar al valor oficial de la UMA mensual 2026 cuando se publique

    /**
     * Calcula los impuestos (ISR e IMSS) y el Subsidio partiendo del Sueldo Bruto Quincenal
     * 
     * @param float $sueldoBruto Quincenal
     * @param bool $aplicaImss (Falso para Honorarios)
     * @return array
     */
    public function calcularDesdeBruto(float $sueldoBruto, bool $aplicaImss = true): array
    {
        // 1. Cálculo del ISR Previo
        $isrCalculado = $this->calcularISR($sueldoBruto);
        
        // 2. Cálculo del Subsidio al Empleo (Quincenal)
        $subsidioAplicado = 0.00;
        $sueldoMensualProyectado = $sueldoBruto * 2; // Proyección mensual
        
        if ($sueldoMensualProyectado <= $this->topeSueldoMensualSubsidio) {
            $subsidioMensual = $this->umaMensualVigente * 0.1182;
            $subsidioAplicado = round($subsidioMensual / 2, 2);
        }

        // 3. ISR a Retener (Restando el subsidio, sin que sea negativo)
        $isrARetener = $isrCalculado - $subsidioAplicado;
        if ($isrARetener < 0) {
            $isrARetener = 0.00;
        }

        // 4. Cálculo del IMSS (Cuota Obrera Simplificada)
        // Nota para XML oficial: En el futuro esto debe calcularse sobre el Salario Base de Cotización (SBC)
        $imss = $aplicaImss ? round($sueldoBruto * 0.02775, 2) : 0.00; 

        // 5. Total Neto
        $neto = $sueldoBruto - $isrARetener - $imss;

        return [
            'bruto'            => round($sueldoBruto, 2),
            'isr_calculado'    => round($isrCalculado, 2),
            'subsidio_empleo'  => round($subsidioAplicado, 2),
            'isr_a_retener'    => round($isrARetener, 2),
            'imss'             => round($imss, 2),
            'neto'             => round($neto, 2)
        ];
    }

    /**
     * Piramidación (Gross-up): Encuentra el Sueldo Bruto necesario para llegar a un Neto exacto
     * Utiliza un algoritmo de búsqueda binaria.
     * 
     * @param float $netoDeseado Quincenal
     * @param bool $aplicaImss
     * @return array
     */
    public function calcularDesdeNeto(float $netoDeseado, bool $aplicaImss = true): array
    {
        $limiteInferior = $netoDeseado;
        $limiteSuperior = $netoDeseado * 2; 
        $margenError = 0.01;
        $brutoEstimado = 0;

        while (($limiteSuperior - $limiteInferior) > $margenError) {
            $brutoEstimado = ($limiteInferior + $limiteSuperior) / 2;
            
            $calculo = $this->calcularDesdeBruto($brutoEstimado, $aplicaImss);
            $netoEstimado = $calculo['neto'];

            if ($netoEstimado < $netoDeseado) {
                $limiteInferior = $brutoEstimado;
            } else {
                $limiteSuperior = $brutoEstimado;
            }
        }

        return $this->calcularDesdeBruto($brutoEstimado, $aplicaImss);
    }

    /**
     * Calcula el ISR quincenal según las tablas vigentes
     */
    private function calcularISR(float $baseGravable): float
    {
        if ($baseGravable <= 0) return 0.00;

        foreach ($this->tablaIsrQuincenal as $rango) {
            $limiteInferior = $rango[0];
            $limiteSuperior = $rango[1];
            $cuotaFija      = $rango[2];
            $porcentaje     = $rango[3] / 100;

            if ($baseGravable >= $limiteInferior && $baseGravable <= $limiteSuperior) {
                $excedente = $baseGravable - $limiteInferior;
                $impuestoMarginal = $excedente * $porcentaje;
                return round($cuotaFija + $impuestoMarginal, 2);
            }
        }

        return 0.00;
    }
}
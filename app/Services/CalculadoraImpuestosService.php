<?php

namespace App\Services;

class CalculadoraImpuestosService
{
    /**
     * Tabla Quincenal de ISR VIGENTE 2026 (Anexo 8 RMF)
     * Actualizada por inflación.
     * Estructura: [Limite Inferior, Limite Superior, Cuota Fija, Porcentaje Excedente]
     */
    protected $tablaIsrQuincenal = [
        [0.01,       416.70,       0.00,      1.92],
        [416.71,     3537.15,      7.95,      6.40],
        [3537.16,    6216.15,      207.75,    10.88],
        [6216.16,    7225.95,      499.20,    16.00],
        [7225.96,    8651.40,      660.75,    17.92],
        [8651.41,    17448.75,     916.20,    21.36],
        [17448.76,   27501.60,     2795.25,   23.52],
        [27501.61,   52505.25,     5159.70,   30.00],
        [52505.26,   70006.95,     12660.75,  32.00],
        [70006.96,   210020.70,    18261.30,  34.00],
        [210020.71,  9999999.99,   65866.05,  35.00]
    ];

    /**
     * Parámetros para el Subsidio al Empleo (Vigentes para 2026)
     * - Aplica solo a sueldos mensuales menores o iguales a $9,081.00 ($4,540.50 quincenal).
     * - El subsidio mensual es el 11.82% de la UMA mensual.
     */
    protected $topeSueldoMensualSubsidio = 9081.00;
    
    // 🔥 ACTUALIZADO: UMA Mensual Oficial 2026 publicada por el INEGI ($117.31 diaria * 30.4)
    protected $umaMensualVigente = 3566.22; 

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
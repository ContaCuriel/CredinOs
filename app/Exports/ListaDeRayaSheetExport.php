<?php

namespace App\Exports;

use App\Models\Empleado;
use App\Models\Asistencia;
use App\Models\DeduccionEmpleado;
use App\Models\Sucursal;
use App\Models\AsistenciaCierre;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison; 
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ListaDeRayaSheetExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithEvents, WithColumnFormatting, WithStrictNullComparison
{
    protected string $periodo;
    protected int $sucursal_id;
    protected string $sucursal_nombre;
    protected Collection $resultados;
    protected string $periodoTexto;
    protected int $rowNumber = 1;

    public function __construct(string $periodo, int $sucursal_id)
    {
        $this->periodo = $periodo;
        $this->sucursal_id = $sucursal_id;

        $sucursal = Sucursal::find($sucursal_id);
        $this->sucursal_nombre = $sucursal ? Str::limit(preg_replace('/[\\*\\?\\:\\/\\\\]/', '', $sucursal->nombre_sucursal), 31) : 'Desconocida';

        list($fechaInicioStr, $fechaFinStr) = explode('_', $this->periodo);
        $inicio = Carbon::parse($fechaInicioStr)->locale('es');
        $fin = Carbon::parse($fechaFinStr)->locale('es');
        $this->periodoTexto = "DEL " . strtoupper($inicio->translatedFormat('d \DE F')) . " AL " . strtoupper($fin->translatedFormat('d \DE F \DE Y'));

        $this->calculateResults();
    }

    private function calculateResults(): void
    {
        // ---------------------------------------------------------
        // 1. INTENTAR LEER DESDE EL HISTÓRICO (LA FOTOGRAFÍA)
        // ---------------------------------------------------------
        $periodoGuardado = \App\Models\ListaRayaPeriodo::with('detalles.empleado')
            ->where('periodo_rango', $this->periodo)
            ->where('id_sucursal', $this->sucursal_id)
            ->first();

        $this->resultados = collect();

        // Si existe en la BD, llenamos con los datos guardados
        if ($periodoGuardado && $periodoGuardado->detalles->isNotEmpty()) {
            foreach ($periodoGuardado->detalles as $detalle) {
                $this->resultados->push([
                    'id_empleado' => $detalle->id_empleado,
                    'sueldo_mensual' => (float)$detalle->sueldo_mensual_historico,
                    'sueldo_diario' => (float)$detalle->sueldo_diario_historico,
                    'dias_periodo' => $detalle->dias_periodo,
                    
                    'retardos_reporte' => $detalle->retardos_acumulados ?? 0,
                    'faltas_reporte' => $detalle->faltas_directas ?? 0,
                    
                    'empleado_nombre' => strtoupper($detalle->empleado ? $detalle->empleado->nombre_completo : 'DESCONOCIDO'),
                    'fecha_ingreso' => $detalle->empleado ? $detalle->empleado->fecha_ingreso : null,
                    'puesto' => $detalle->puesto_historico,
                    'sueldo_quincenal' => (float)($detalle->sueldo_diario_historico * $detalle->dias_periodo), 
                    
                    // 🔥 LEYENDO COLUMNAS DESGLOSADAS DE LA BASE DE DATOS 🔥
                    'bono_permanencia' => (float)($detalle->bono_permanencia ?? 0), 
                    'bono_cumpleanos' => (float)($detalle->bono_cumpleanos ?? 0),
                    'prima_vacacional' => (float)($detalle->prima_vacacional ?? 0), 
                    'total_percepciones' => (float)(
                        ($detalle->sueldo_diario_historico * $detalle->dias_periodo) + 
                        ($detalle->bono_permanencia ?? 0) + 
                        ($detalle->bono_cumpleanos ?? 0) + 
                        ($detalle->prima_vacacional ?? 0) + 
                        ($detalle->percepciones_extra ?? 0)
                    ), 
                    
                    'deduccion_faltas' => (float)($detalle->descuento_por_faltas ?? 0),
                    'deduccion_prestamo' => (float)($detalle->deduccion_prestamo ?? 0), 
                    'deduccion_prevision' => 0, // En histórico está agrupado en "otras_deducciones"
                    'deduccion_caja_ahorro' => (float)($detalle->deduccion_caja_ahorro ?? 0), 
                    'deduccion_infonavit' => (float)($detalle->deduccion_infonavit ?? 0),
                    'deduccion_isr' => (float)($detalle->deduccion_isr ?? 0),  // 🔥 AHORA SÍ LEE EL ISR
                    'deduccion_imss' => (float)($detalle->deduccion_imss ?? 0), // 🔥 AHORA SÍ LEE EL IMSS
                    'deduccion_otro' => (float)($detalle->otras_deducciones ?? 0),
                    'total_deducciones' => (float)(
                        ($detalle->descuento_por_faltas ?? 0) + 
                        ($detalle->deduccion_prestamo ?? 0) + 
                        ($detalle->deduccion_caja_ahorro ?? 0) + 
                        ($detalle->deduccion_infonavit ?? 0) + 
                        ($detalle->deduccion_isr ?? 0) + 
                        ($detalle->deduccion_imss ?? 0) + 
                        ($detalle->otras_deducciones ?? 0)
                    ), 
                    
                    'neto_a_pagar' => (float)$detalle->total_neto,
                ]);
            }
            return; 
        }

        // ---------------------------------------------------------
        // 2. CÁLCULO AL VUELO (AQUÍ LEEMOS LA TABLA PUENTE)
        // ---------------------------------------------------------
        list($fechaInicioStr, $fechaFinStr) = explode('_', $this->periodo);
        $fechaInicioPeriodo = Carbon::parse($fechaInicioStr);
        $fechaFinPeriodo = Carbon::parse($fechaFinStr);

        $empleados = Empleado::where('status', 'Alta')
            ->where('id_sucursal', $this->sucursal_id)
            ->with(['puesto', 'horario'])
            ->get();

        $cierresAsistencia = AsistenciaCierre::where('periodo', $this->periodo)
            ->where('id_sucursal', $this->sucursal_id)
            ->get()
            ->keyBy('id_empleado');

        foreach ($empleados as $empleado) {
            $salarioDiario = $empleado->puesto ? ($empleado->puesto->salario_mensual / 30) : 0;
            
            $fechaIngresoEmpleado = Carbon::parse($empleado->fecha_ingreso);
            $diasAPagar = 15;

            if ($fechaIngresoEmpleado->between($fechaInicioPeriodo, $fechaFinPeriodo)) {
                $diasAPagar = $fechaIngresoEmpleado->diffInDays($fechaFinPeriodo) + 1;
            }
            
            $sueldoQuincenalBruto = $salarioDiario * $diasAPagar;

            $bonoPermanencia = 0; $bonoCumpleanos = 0; $primaVacacional = 0;
            if ($empleado->fecha_ingreso) {
                $fechaIngreso = Carbon::parse($empleado->fecha_ingreso);
                $aniversarioEnAnoDelPeriodo = $fechaIngreso->copy()->year($fechaInicioPeriodo->year);
                if ($fechaInicioPeriodo->month == 1 && $fechaIngreso->month == 12) { $aniversarioEnAnoDelPeriodo->subYear(); }
                if ($aniversarioEnAnoDelPeriodo->between($fechaInicioPeriodo, $fechaFinPeriodo)) {
                    $anosCompletados = $aniversarioEnAnoDelPeriodo->year - $fechaIngreso->year;
                    if ($anosCompletados >= 1) {
                        if ($anosCompletados == 1) $bonoPermanencia = 3000;
                        elseif ($anosCompletados == 2) $bonoPermanencia = 4000;
                        elseif ($anosCompletados >= 3) $bonoPermanencia = 5000;
                        $diasVacacionesLFT = $empleado->getDiasVacacionesParaAnoDeServicio($anosCompletados);
                        $primaVacacional = ($salarioDiario * $diasVacacionesLFT) * 0.25;
                    }
                }
            }
            if ($empleado->fecha_nacimiento) {
                $cumpleanosEsteAno = Carbon::parse($empleado->fecha_nacimiento)->year($fechaInicioPeriodo->year);
                if ($fechaInicioPeriodo->month == 1 && Carbon::parse($empleado->fecha_nacimiento)->month == 12) { $cumpleanosEsteAno->subYear(); }
                $antiguedadEnMeses = $empleado->fecha_ingreso ? Carbon::parse($empleado->fecha_ingreso)->diffInMonths($cumpleanosEsteAno) : 0;
                if ($cumpleanosEsteAno->between($fechaInicioPeriodo, $fechaFinPeriodo) && $antiguedadEnMeses > 6) {
                    $bonoCumpleanos = 500;
                }
            }
            $totalPercepciones = $sueldoQuincenalBruto + $bonoPermanencia + $bonoCumpleanos + $primaVacacional;

            // ---------------------------------------------------------
            // 🔥 AQUÍ OCURRE LA MAGIA DEL CIERRE DE ASISTENCIA
            // ---------------------------------------------------------
            $cierre = $cierresAsistencia->get($empleado->id_empleado);
            
            $retardosCrudos = $cierre ? $cierre->retardos : 0; // Para la Columna D
            $faltasCrudas = $cierre ? (float)$cierre->faltas : 0; // Para la Columna E
            
            $reglaRetardos = $empleado->horario ? ($empleado->horario->retardos_por_falta ?? 0) : 0;
            $faltasPorRetardos = $reglaRetardos > 0 ? floor($retardosCrudos / $reglaRetardos) : 0;
            
            $diasADescontar = $faltasCrudas + $faltasPorRetardos;

            $deduccionesActivas = DeduccionEmpleado::where('id_empleado', $empleado->id_empleado)
                ->where('status', 'Activo')
                ->whereDate('fecha_solicitud', '<=', $fechaFinPeriodo->toDateString())
                ->get();

            $deduccionFaltasManuales = $deduccionesActivas->where('tipo_deduccion', 'Retardo/Falta Manual')->sum('monto_quincenal');
            
            $deduccionFaltas = ($diasADescontar * $salarioDiario) + $deduccionFaltasManuales;
            // ---------------------------------------------------------

            $deduccionPrestamo = $deduccionesActivas->where('tipo_deduccion', 'Préstamo')->sum('monto_quincenal');
            $deduccionPrevision = $deduccionesActivas->where('tipo_deduccion', 'Previsión')->sum('monto_quincenal'); 
            $deduccionCajaAhorro = $deduccionesActivas->where('tipo_deduccion', 'Caja de Ahorro')->sum('monto_quincenal');
            $deduccionInfonavit = $deduccionesActivas->where('tipo_deduccion', 'Infonavit')->sum('monto_quincenal');
            $deduccionISR = $deduccionesActivas->where('tipo_deduccion', 'ISR')->sum('monto_quincenal');
            $deduccionIMSS = $deduccionesActivas->where('tipo_deduccion', 'IMSS')->sum('monto_quincenal');
            $deduccionOtro = $deduccionesActivas->whereIn('tipo_deduccion', ['Otro', 'Fijo Sin Plazo'])->sum('monto_quincenal');
            
            $totalDeducciones = $deduccionFaltas + $deduccionPrestamo + $deduccionPrevision + $deduccionCajaAhorro + $deduccionInfonavit + $deduccionISR + $deduccionIMSS + $deduccionOtro;

            $netoAPagar = $totalPercepciones - $totalDeducciones;

            $this->resultados->push([
                'id_empleado' => $empleado->id_empleado,
                'sueldo_mensual' => $empleado->puesto ? $empleado->puesto->salario_mensual : 0,
                'sueldo_diario' => $salarioDiario,
                'dias_periodo' => $diasAPagar,
                
                'retardos_reporte' => $retardosCrudos,
                'faltas_reporte' => $faltasCrudas,
                'faltas_por_retardos_historico' => $faltasPorRetardos, 
                
                'empleado_nombre' => strtoupper($empleado->nombre_completo),
                'fecha_ingreso' => $empleado->fecha_ingreso,
                'puesto' => $empleado->puesto ? $empleado->puesto->nombre_puesto : 'N/A',
                'sueldo_quincenal' => (float)$sueldoQuincenalBruto, 
                'bono_permanencia' => (float)$bonoPermanencia, 
                'bono_cumpleanos' => (float)$bonoCumpleanos,
                'prima_vacacional' => (float)$primaVacacional, 
                'total_percepciones' => (float)$totalPercepciones, 
                'deduccion_faltas' => (float)$deduccionFaltas,
                'deduccion_prestamo' => (float)$deduccionPrestamo, 
                'deduccion_prevision' => (float)$deduccionPrevision, 
                'deduccion_caja_ahorro' => (float)$deduccionCajaAhorro, 
                'deduccion_infonavit' => (float)$deduccionInfonavit,
                'deduccion_isr' => (float)$deduccionISR, 
                'deduccion_imss' => (float)$deduccionIMSS, 
                'deduccion_otro' => (float)$deduccionOtro,
                'total_deducciones' => (float)$totalDeducciones, 
                'neto_a_pagar' => (float)$netoAPagar,
            ]);
        }
    }

    public function collection(): Collection { return $this->resultados; }

    public function title(): string { return $this->sucursal_nombre; }

    public function headings(): array
    {
        return [
            'Empleado', 'Fecha Ingreso', 'Puesto',
            'R', 'F',
            'Sueldo Quincenal', 'Bono Permanencia', 'Bono Cumpleaños', 'Prima Vacacional',
            'Total Percepciones', 'Ded. Faltas', 'Ded. Préstamo', 'Ded. Previsión', 'Ded. Caja Ahorro', 'Ded. Infonavit', 'Ded. ISR', 'Ded. IMSS', 'Ded. Otros',
            'Total Deducciones', 'Neto a Pagar',
        ];
    }

    public function map($filaResultado): array
    {
        $filaActual = $this->rowNumber + 1;
        $this->rowNumber++;

        $rangoPercepciones    = "F{$filaActual}:I{$filaActual}";
        $colTotalPercepciones   = "J{$filaActual}";
        $rangoDeducciones       = "K{$filaActual}:R{$filaActual}";
        $colTotalDeducciones    = "S{$filaActual}";
        $colNeto                = "T{$filaActual}";

        return [
            $filaResultado['empleado_nombre'],
            $filaResultado['fecha_ingreso'] ? Carbon::parse($filaResultado['fecha_ingreso'])->format('d/m/Y') : 'N/A',
            $filaResultado['puesto'],
            
            $filaResultado['retardos_reporte'], // D (Retardos)
            $filaResultado['faltas_reporte'],   // E (Faltas)
            
            (float) $filaResultado['sueldo_quincenal'],      // F
            (float) $filaResultado['bono_permanencia'],      // G
            (float) $filaResultado['bono_cumpleanos'],       // H
            (float) $filaResultado['prima_vacacional'],      // I
            "=SUM({$rangoPercepciones})",                      // J
            (float) $filaResultado['deduccion_faltas'],      // K
            (float) $filaResultado['deduccion_prestamo'],    // L
            (float) $filaResultado['deduccion_prevision'],   // M 
            (float) $filaResultado['deduccion_caja_ahorro'], // N
            (float) $filaResultado['deduccion_infonavit'],   // O
            (float) $filaResultado['deduccion_isr'],         // P
            (float) $filaResultado['deduccion_imss'],        // Q
            (float) $filaResultado['deduccion_otro'],        // R
            "=SUM({$rangoDeducciones})",                      // S
            "={$colTotalPercepciones}-{$colTotalDeducciones}", // T
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F:T' => '"$" #,##0.00;[Red]-"$" #,##0.00;"$" 0.00',
            'B' => NumberFormat::FORMAT_DATE_DDMMYYYY 
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $sheet->getSheetView()->setShowZeros(true);
                
                $sheet->insertNewRowBefore(1, 1);
                $tituloCompleto = 'NÓMINA ' . $this->periodoTexto;
                $sheet->setCellValue('A1', $tituloCompleto);
                
                $lastColumn = 'T'; 
                $sheet->mergeCells('A1:'.$lastColumn.'1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);

                $sheet->getStyle('A2:'.$lastColumn.'2')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F81BD']]
                ]);
                
                $sheet->getStyle('K2:S2')->applyFromArray([ 
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9534F']]
                ]);

                if ($this->resultados->count() > 0) {
                    $lastDataRow = $this->resultados->count() + 2;
                    
                    $sheet->getStyle('A2:'.$lastColumn . $lastDataRow)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                    ]);

                    $sheet->getStyle("D3:E{$lastDataRow}")->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                    ]);

                    $totalsRow = $lastDataRow + 2;
                    $sheet->setCellValue("A{$totalsRow}", 'TOTALES:');

                    $columnsToSum = ['F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T'];
                    foreach ($columnsToSum as $column) {
                        $sheet->setCellValue("{$column}{$totalsRow}", "=SUM({$column}3:{$column}{$lastDataRow})");
                    }

                    $sheet->getStyle("A{$totalsRow}:{$lastColumn}{$totalsRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'borders' => ['top' => ['borderStyle' => Border::BORDER_THICK]]
                    ]);
                    
                    $sheet->getStyle("F{$totalsRow}:{$lastColumn}{$totalsRow}")->getNumberFormat()
                          ->setFormatCode('"$" #,##0.00;[Red]-"$" #,##0.00;"$" 0.00');
                    
                    $columnsToCheck = [
                        'G' => 'Bono Permanencia', 'H' => 'Bono Cumpleaños', 'I' => 'Prima Vacacional',
                        'K' => 'Ded. Faltas', 'L' => 'Ded. Préstamo', 'M' => 'Ded. Previsión', 'N' => 'Ded. Caja Ahorro',
                        'O' => 'Ded. Infonavit', 'P' => 'Ded. ISR', 'Q' => 'Ded. IMSS', 'R' => 'Ded. Otros'
                    ];

                    foreach ($columnsToCheck as $columnLetter => $columnName) {
                        $totalValue = $sheet->getCell("{$columnLetter}{$totalsRow}")->getCalculatedValue();
                        if (is_numeric($totalValue) && abs($totalValue) < 0.01) {
                            $event->sheet->getColumnDimension($columnLetter)->setVisible(false);
                        }
                    }
                }
            },
        ];
    }

    public function getNetoAPagarTotal(): float
    {
        return (float) $this->resultados->sum('neto_a_pagar');
    }
}
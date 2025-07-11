<?php

namespace App\Exports;

use App\Models\Empleado;
use App\Models\Asistencia;
use App\Models\DeduccionEmpleado;
use App\Models\Sucursal;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ListaDeRayaSheetExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithEvents, WithColumnFormatting
{
    protected string $periodo;
    protected int $sucursal_id;
    protected string $sucursal_nombre;
    protected Collection $resultados;
    protected string $periodoTexto;
    // CORRECCIÓN: El contador de filas debe empezar en 1.
    // El mapeo de datos ocurre ANTES de que se inserte la fila del título en el evento AfterSheet.
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
        list($fechaInicioStr, $fechaFinStr) = explode('_', $this->periodo);
        $fechaInicioPeriodo = Carbon::parse($fechaInicioStr);
        $fechaFinPeriodo = Carbon::parse($fechaFinStr);

        $empleados = Empleado::where('status', 'Alta')
            ->where('id_sucursal', $this->sucursal_id)
            ->with(['puesto'])
            ->get();

        $this->resultados = collect();

        foreach ($empleados as $empleado) {
            $salarioDiario = $empleado->puesto ? ($empleado->puesto->salario_mensual / 30) : 0;
            
            $fechaIngresoEmpleado = Carbon::parse($empleado->fecha_ingreso);
            $diasAPagar = 15;

            if ($fechaIngresoEmpleado->between($fechaInicioPeriodo, $fechaFinPeriodo)) {
                $diasAPagar = $fechaIngresoEmpleado->diffInDays($fechaFinPeriodo) + 1;
            }
            
            $sueldoQuincenalBruto = $salarioDiario * $diasAPagar;

            $bonoPermanencia = 0;
            $bonoCumpleanos = 0;
            $primaVacacional = 0;

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

            $diasFalta = Asistencia::where('id_empleado', $empleado->id_empleado)->where('status_asistencia', 'Falta')->whereBetween('fecha', [$fechaInicioPeriodo, $fechaFinPeriodo])->count();
            $deduccionFaltas = $diasFalta * $salarioDiario;
            $deduccionesActivas = DeduccionEmpleado::where('id_empleado', $empleado->id_empleado)->where('status', 'Activo')->get();
            $deduccionPrestamo = $deduccionesActivas->where('tipo_deduccion', 'Préstamo')->sum('monto_quincenal');
            $deduccionCajaAhorro = $deduccionesActivas->where('tipo_deduccion', 'Caja de Ahorro')->sum('monto_quincenal');
            $deduccionInfonavit = $deduccionesActivas->where('tipo_deduccion', 'Infonavit')->sum('monto_quincenal');
            $deduccionISR = $deduccionesActivas->where('tipo_deduccion', 'ISR')->sum('monto_quincenal');
            $deduccionIMSS = $deduccionesActivas->where('tipo_deduccion', 'IMSS')->sum('monto_quincenal');
            $deduccionOtro = $deduccionesActivas->where('tipo_deduccion', 'Otro')->sum('monto_quincenal');
            $totalDeducciones = $deduccionFaltas + $deduccionPrestamo + $deduccionCajaAhorro + $deduccionInfonavit + $deduccionISR + $deduccionIMSS + $deduccionOtro;

            $netoAPagar = $totalPercepciones - $totalDeducciones;

            $this->resultados->push([
                'empleado_nombre' => strtoupper($empleado->nombre_completo),
                'fecha_ingreso' => $empleado->fecha_ingreso,
                'puesto' => $empleado->puesto ? $empleado->puesto->nombre_puesto : 'N/A',
                'sueldo_quincenal' => $sueldoQuincenalBruto, 'bono_permanencia' => $bonoPermanencia, 'bono_cumpleanos' => $bonoCumpleanos,
                'prima_vacacional' => $primaVacacional, 'total_percepciones' => $totalPercepciones, 'deduccion_faltas' => $deduccionFaltas,
                'deduccion_prestamo' => $deduccionPrestamo, 'deduccion_caja_ahorro' => $deduccionCajaAhorro, 'deduccion_infonavit' => $deduccionInfonavit,
                'deduccion_isr' => $deduccionISR, 'deduccion_imss' => $deduccionIMSS, 'deduccion_otro' => $deduccionOtro,
                'total_deducciones' => $totalDeducciones, 'neto_a_pagar' => $netoAPagar,
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
            'Total Percepciones', 'Ded. Faltas', 'Ded. Préstamo', 'Ded. Caja Ahorro', 'Ded. Infonavit', 'Ded. ISR', 'Ded. IMSS', 'Ded. Otros',
            'Total Deducciones', 'Neto a Pagar',
        ];
    }

    public function map($filaResultado): array
    {
        // CORRECCIÓN: Se restaura la lógica original para calcular la fila actual.
        // La fila actual es el número de fila en la hoja ANTES de que se inserte la fila del título.
        // Excel ajustará automáticamente las referencias de celda (ej. de F2 a F3) después de la inserción.
        $filaActual = $this->rowNumber + 1;
        $this->rowNumber++;

        // Las letras de las columnas y los rangos ahora son correctos para la nueva estructura.
        $rangoPercepciones    = "F{$filaActual}:I{$filaActual}";
        $colTotalPercepciones   = "J{$filaActual}";
        $rangoDeducciones       = "K{$filaActual}:Q{$filaActual}";
        $colTotalDeducciones    = "R{$filaActual}";

        return [
            $filaResultado['empleado_nombre'],
            $filaResultado['fecha_ingreso'] ? Carbon::parse($filaResultado['fecha_ingreso'])->format('d/m/Y') : 'N/A',
            $filaResultado['puesto'],
            '', '', // Columnas D y E
            (float) $filaResultado['sueldo_quincenal'],     // F
            (float) $filaResultado['bono_permanencia'],     // G
            (float) $filaResultado['bono_cumpleanos'],      // H
            (float) $filaResultado['prima_vacacional'],     // I
            "=SUM({$rangoPercepciones})",                   // J
            (float) $filaResultado['deduccion_faltas'],     // K
            (float) $filaResultado['deduccion_prestamo'],   // L
            (float) $filaResultado['deduccion_caja_ahorro'],// M
            (float) $filaResultado['deduccion_infonavit'],  // N
            (float) $filaResultado['deduccion_isr'],        // O
            (float) $filaResultado['deduccion_imss'],       // P
            (float) $filaResultado['deduccion_otro'],       // Q
            "=SUM({$rangoDeducciones})",                    // R
            "={$colTotalPercepciones}-{$colTotalDeducciones}", // S
        ];
    }

    public function columnFormats(): array
    {
        $formatoMonedaConCero = '$ #,##0.00;[Red]-$ #,##0.00;"$ "0.00';
        return [
            'F:S' => $formatoMonedaConCero, // El rango de formato de moneda es de la F a la S
            'B' => NumberFormat::FORMAT_DATE_DDMMYYYY // La columna B ahora es la fecha
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Inserta la fila del título, moviendo todo el contenido una fila hacia abajo.
                $sheet->insertNewRowBefore(1, 1);
                $tituloCompleto = 'NÓMINA ' . $this->periodoTexto;
                $sheet->setCellValue('A1', $tituloCompleto);
                
                $lastColumn = 'S'; // La última columna ahora es la S
                $sheet->mergeCells('A1:'.$lastColumn.'1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);

                $sheet->getStyle('A2:'.$lastColumn.'2')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F81BD']]
                ]);
                
                // El color rojo para las deducciones ahora va de la K a la R
                $sheet->getStyle('K2:R2')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9534F']]
                ]);

                if ($this->resultados->count() > 0) {
                    // Los datos ahora terminan en la fila count + 2 (1 para header, 1 para título)
                    $lastDataRow = $this->resultados->count() + 2;
                    $sheet->getStyle('A2:'.$lastColumn . $lastDataRow)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                    ]);

                    $totalsRow = $lastDataRow + 2;
                    $sheet->setCellValue("A{$totalsRow}", 'TOTALES:');

                    // Las columnas a sumar ahora van de la F a la S
                    $columnsToSum = ['F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S'];
                    foreach ($columnsToSum as $column) {
                        // El rango de suma es correcto, desde la fila 3 (primer dato) hasta la última
                        $sheet->setCellValue("{$column}{$totalsRow}", "=SUM({$column}3:{$column}{$lastDataRow})");
                    }

                    $sheet->getStyle("A{$totalsRow}:{$lastColumn}{$totalsRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'borders' => ['top' => ['borderStyle' => Border::BORDER_THICK]]
                    ]);
                    $sheet->getStyle("F{$totalsRow}:{$lastColumn}{$totalsRow}")->getNumberFormat()
                          ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
                    
                    // Las letras de las columnas a ocultar también se ajustan
                    $columnsToCheck = [
                        'G' => 'Bono Permanencia', 'H' => 'Bono Cumpleaños', 'I' => 'Prima Vacacional',
                        'K' => 'Ded. Faltas', 'L' => 'Ded. Préstamo', 'M' => 'Ded. Caja Ahorro',
                        'N' => 'Ded. Infonavit', 'O' => 'Ded. ISR', 'P' => 'Ded. IMSS', 'Q' => 'Ded. Otros'
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

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

            // --- LÓGICA DE DEDUCCIONES ACTUALIZADA ---
            $deduccionesActivas = DeduccionEmpleado::where('id_empleado', $empleado->id_empleado)->where('status', 'Activo')->get();
            
            // 1. Faltas (Asistencia normal + Falta/Retardo manual)
            $diasFalta = Asistencia::where('id_empleado', $empleado->id_empleado)->where('status_asistencia', 'Falta')->whereBetween('fecha', [$fechaInicioPeriodo, $fechaFinPeriodo])->count();
            $deduccionFaltasManuales = $deduccionesActivas->where('tipo_deduccion', 'Retardo/Falta Manual')->sum('monto_quincenal');
            $deduccionFaltas = ($diasFalta * $salarioDiario) + $deduccionFaltasManuales;
            
            // 2. Otras Deducciones Separadas
            $deduccionPrestamo = $deduccionesActivas->where('tipo_deduccion', 'Préstamo')->sum('monto_quincenal');
            $deduccionPrevision = $deduccionesActivas->where('tipo_deduccion', 'Previsión')->sum('monto_quincenal'); // NUEVA COLUMNA
            $deduccionCajaAhorro = $deduccionesActivas->where('tipo_deduccion', 'Caja de Ahorro')->sum('monto_quincenal');
            $deduccionInfonavit = $deduccionesActivas->where('tipo_deduccion', 'Infonavit')->sum('monto_quincenal');
            $deduccionISR = $deduccionesActivas->where('tipo_deduccion', 'ISR')->sum('monto_quincenal');
            $deduccionIMSS = $deduccionesActivas->where('tipo_deduccion', 'IMSS')->sum('monto_quincenal');
            
            // 3. Otros (Agrupa 'Otro' y 'Fijo Sin Plazo')
            $deduccionOtro = $deduccionesActivas->whereIn('tipo_deduccion', ['Otro', 'Fijo Sin Plazo'])->sum('monto_quincenal');
            
            $totalDeducciones = $deduccionFaltas + $deduccionPrestamo + $deduccionPrevision + $deduccionCajaAhorro + $deduccionInfonavit + $deduccionISR + $deduccionIMSS + $deduccionOtro;

            $netoAPagar = $totalPercepciones - $totalDeducciones;

            $this->resultados->push([
                'empleado_nombre' => strtoupper($empleado->nombre_completo),
                'fecha_ingreso' => $empleado->fecha_ingreso,
                'puesto' => $empleado->puesto ? $empleado->puesto->nombre_puesto : 'N/A',
                'sueldo_quincenal' => $sueldoQuincenalBruto, 'bono_permanencia' => $bonoPermanencia, 'bono_cumpleanos' => $bonoCumpleanos,
                'prima_vacacional' => $primaVacacional, 'total_percepciones' => $totalPercepciones, 
                'deduccion_faltas' => $deduccionFaltas,
                'deduccion_prestamo' => $deduccionPrestamo, 
                'deduccion_prevision' => $deduccionPrevision, // NUEVO
                'deduccion_caja_ahorro' => $deduccionCajaAhorro, 
                'deduccion_infonavit' => $deduccionInfonavit,
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
        $rangoDeducciones       = "K{$filaActual}:R{$filaActual}"; // Ahora llega hasta la R
        $colTotalDeducciones    = "S{$filaActual}";
        $colNeto                = "T{$filaActual}";

        return [
            $filaResultado['empleado_nombre'],
            $filaResultado['fecha_ingreso'] ? Carbon::parse($filaResultado['fecha_ingreso'])->format('d/m/Y') : 'N/A',
            $filaResultado['puesto'],
            '', '', // Columnas D y E
            (float) $filaResultado['sueldo_quincenal'],      // F
            (float) $filaResultado['bono_permanencia'],      // G
            (float) $filaResultado['bono_cumpleanos'],       // H
            (float) $filaResultado['prima_vacacional'],      // I
            "=SUM({$rangoPercepciones})",                      // J
            (float) $filaResultado['deduccion_faltas'],      // K
            (float) $filaResultado['deduccion_prestamo'],    // L
            (float) $filaResultado['deduccion_prevision'],   // M (NUEVO)
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
        $formatoMonedaConCero = '$ #,##0.00;[Red]-$ #,##0.00;"$ "0.00';
        return [
            'F:T' => $formatoMonedaConCero, // Actualizado hasta la columna T
            'B' => NumberFormat::FORMAT_DATE_DDMMYYYY 
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $sheet->insertNewRowBefore(1, 1);
                $tituloCompleto = 'NÓMINA ' . $this->periodoTexto;
                $sheet->setCellValue('A1', $tituloCompleto);
                
                $lastColumn = 'T'; // Actualizado hasta la T
                $sheet->mergeCells('A1:'.$lastColumn.'1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);

                $sheet->getStyle('A2:'.$lastColumn.'2')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F81BD']]
                ]);
                
                $sheet->getStyle('K2:S2')->applyFromArray([ // Fondo rojo para deducciones
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9534F']]
                ]);

                if ($this->resultados->count() > 0) {
                    $lastDataRow = $this->resultados->count() + 2;
                    $sheet->getStyle('A2:'.$lastColumn . $lastDataRow)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
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
                          ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
                    
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

// =========================================================================

namespace App\Exports;

use App\Models\Sucursal;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;

class ListaDeRayaMultiSucursalExport implements WithMultipleSheets
{
    use Exportable;

    protected string $periodo;

    public function __construct(string $periodo)
    {
        $this->periodo = $periodo;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $resumenData = collect();
        $sucursales = Sucursal::where('status', 'Activa')->orderBy('nombre_sucursal')->get();

        foreach ($sucursales as $sucursal) {
            $sheetExport = new ListaDeRayaSheetExport($this->periodo, $sucursal->id_sucursal);
            $sheets[] = $sheetExport;

            // Obtenemos los datos necesarios para construir la fórmula
            $sheetName = $sheetExport->title();
            $rowCount = $sheetExport->collection()->count();
            
            // Si hay datos, calculamos la fila del total. Si no, el total es 0.
            if ($rowCount > 0) {
                // Fila del título (1) + Fila de cabeceras (1) + Filas de datos + 2 filas de espacio = Fila de totales
                $totalRow = 1 + 1 + $rowCount + 2;
                // --- CORRECCIÓN AQUÍ: Cambiamos la S por la T, porque ahora el Total Neto está en la columna T ---
                $netoTotalFormula = "='" . $sheetName . "'!T" . $totalRow; 
            } else {
                // Si no hay empleados en esa sucursal, el total es 0.
                $netoTotalFormula = 0;
            }

            $resumenData->push([
                'sucursal' => $sucursal->nombre_sucursal,
                'neto_formula' => $netoTotalFormula, // Pasamos la fórmula en lugar del valor
            ]);
        }

        $resumenSheet = new ResumenNetosExport($resumenData);
        array_unshift($sheets, $resumenSheet);

        return $sheets;
    }
}
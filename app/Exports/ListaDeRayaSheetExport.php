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
        $this->periodoTexto = "DEL " . $inicio->translatedFormat('d \DE F') . " AL " . $fin->translatedFormat('d \DE F \DE Y');

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
            $sueldoQuincenalBruto = $salarioDiario * 15;
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
                'empleado_nombre' => $empleado->nombre_completo, 'puesto' => $empleado->puesto ? $empleado->puesto->nombre_puesto : 'N/A',
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
            'Empleado', 'Puesto',
            'R', 'F',
            'Sueldo Quincenal', 'Bono Permanencia', 'Bono Cumpleaños', 'Prima Vacacional',
            'Total Percepciones', 'Ded. Faltas', 'Ded. Préstamo', 'Ded. Caja Ahorro', 'Ded. Infonavit', 'Ded. ISR', 'Ded. IMSS', 'Ded. Otros',
            'Total Deducciones', 'Neto a Pagar',
        ];
    }

    public function map($filaResultado): array
    {
        $filaActual = $this->rowNumber + 1; 
        $this->rowNumber++;
        $rangoPercepciones      = "E{$filaActual}:H{$filaActual}";
        $colTotalPercepciones   = "I{$filaActual}";
        $rangoDeducciones       = "J{$filaActual}:P{$filaActual}";
        $colTotalDeducciones    = "Q{$filaActual}";

        return [
            $filaResultado['empleado_nombre'],
            $filaResultado['puesto'],
            '', '',
            (float) $filaResultado['sueldo_quincenal'],
            (float) $filaResultado['bono_permanencia'],
            (float) $filaResultado['bono_cumpleanos'],
            (float) $filaResultado['prima_vacacional'],
            "=SUM({$rangoPercepciones})", 
            (float) $filaResultado['deduccion_faltas'],
            (float) $filaResultado['deduccion_prestamo'],
            (float) $filaResultado['deduccion_caja_ahorro'],
            (float) $filaResultado['deduccion_infonavit'],
            (float) $filaResultado['deduccion_isr'],
            (float) $filaResultado['deduccion_imss'],
            (float) $filaResultado['deduccion_otro'],
            "=SUM({$rangoDeducciones})",
            "={$colTotalPercepciones}-{$colTotalDeducciones}",
        ];
    }
    
    public function columnFormats(): array
    {
        $formatoMonedaConCero = '$ #,##0.00;[Red]-$ #,##0.00;"$ "0.00';
        return ['E:R' => $formatoMonedaConCero];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->insertNewRowBefore(1, 1);
                $tituloCompleto = 'NÓMINA ' . strtoupper($this->periodoTexto);
                $sheet->setCellValue('A1', $tituloCompleto);
                $sheet->mergeCells('A1:R1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);

                $sheet->getStyle('A2:R2')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F81BD']]
                ]);
                $sheet->getStyle('J2:Q2')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9534F']]
                ]);

                if ($this->resultados->count() > 0) {
                    $lastDataRow = $this->resultados->count() + 2;
                    $sheet->getStyle('A2:R' . $lastDataRow)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                    ]);

                    $totalsRow = $lastDataRow + 2;
                    $sheet->setCellValue("A{$totalsRow}", 'TOTALES:');
                    
                    $columnsToSum = ['E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R'];
                    foreach ($columnsToSum as $column) {
                        $sheet->setCellValue("{$column}{$totalsRow}", "=SUM({$column}3:{$column}{$lastDataRow})");
                    }

                    $sheet->getStyle("A{$totalsRow}:R{$totalsRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'borders' => ['top' => ['borderStyle' => Border::BORDER_THICK]]
                    ]);
                    $sheet->getStyle("E{$totalsRow}:R{$totalsRow}")->getNumberFormat()
                          ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

                    $columnsToCheck = [
                        'F' => 'Bono Permanencia', 'G' => 'Bono Cumpleaños', 'H' => 'Prima Vacacional',
                        'J' => 'Ded. Faltas', 'K' => 'Ded. Préstamo', 'L' => 'Ded. Caja Ahorro',
                        'M' => 'Ded. Infonavit', 'N' => 'Ded. ISR', 'O' => 'Ded. IMSS', 'P' => 'Ded. Otros'
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
    
    /**
     * --- NUEVA FUNCIÓN ---
     * Devuelve el total neto a pagar calculado para esta hoja.
     * La usaremos para alimentar la hoja de resumen.
     */
    public function getNetoAPagarTotal(): float
    {
        return (float) $this->resultados->sum('neto_a_pagar');
    }
}

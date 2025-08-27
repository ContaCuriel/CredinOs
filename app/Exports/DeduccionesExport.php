<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border; // <-- Importar clase para bordes

class DeduccionesExport implements FromView, WithTitle, WithStyles, WithColumnWidths
{
    protected $deducciones;

    public function __construct($deducciones)
    {
        $this->deducciones = $deducciones;
    }

    public function view(): View
    {
        return view('deducciones.excel', [
            'deducciones' => $this->deducciones
        ]);
    }

    public function title(): string
    {
        return 'Reporte de Deducciones';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 40, // Empleado
            'B' => 25, // Tipo de Deducción
            'C' => 15, // Fecha Inicio
            'D' => 20, // Monto Quincenal
            'E' => 35, // Monto Acumulado / Saldo
            'F' => 22, // Monto Total Préstamo
            'G' => 18, // Plazo (Quincenas)
            'H' => 20, // Quincenas Pagadas
            'I' => 15, // Status
            'J' => 50, // Descripción
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Poner en negritas la fila de encabezados
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);

        // Formato de moneda para las columnas D, E y F
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("D2:F{$lastRow}")->getNumberFormat()->setFormatCode('$#,##0.00');

        // === NUEVO: APLICAR BORDES A TODA LA TABLA ===
        $sheet->getStyle('A1:J' . $lastRow)
              ->getBorders()
              ->getAllBorders()
              ->setBorderStyle(Border::BORDER_THIN);
    }
}
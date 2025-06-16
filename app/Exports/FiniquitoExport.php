<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FiniquitoExport implements FromView, WithTitle, WithStyles, WithColumnWidths
{
    protected $data;

    /**
     * Recibimos los datos calculados desde el controlador.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Apunta a una vista de Blade que se convertirá en la hoja de cálculo.
     */
    public function view(): View
    {
        return view('finiquitos.excel', $this->data);
    }

    /**
     * Define el título de la hoja de cálculo.
     */
    public function title(): string
    {
        // Usa el título dinámico que ya teníamos, pero sin espacios.
        return str_replace(' ', '_', $this->data['titulo_documento']);
    }

    /**
     * Define anchos de columna específicos.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 45, // Columna para conceptos
            'B' => 20, // Columna para montos
        ];
    }

    /**
     * Aplica estilos a la hoja de cálculo para que se vea profesional.
     */
    public function styles(Worksheet $sheet)
    {
        // Estilo para el título principal (ej: RECIBO DE LIQUIDACIÓN)
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        // Estilo para el nombre del empleado
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells('A2:B2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

        // Estilo para los títulos de sección
        $sheet->getStyle('A4')->getFont()->setBold(true);
        $sheet->getStyle('A8')->getFont()->setBold(true);

        // Aplicar bordes a la tabla de desglose (ej: A9 hasta B20)
        $sheet->getStyle('A9:B20')->getBorders()->getAllBorders()->setBorderStyle('thin');
        
        // Formato de moneda para la columna B
        $sheet->getStyle('B9:B20')->getNumberFormat()->setFormatCode('$#,##0.00');
        
        // Estilo para la fila del total a pagar
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A'.$lastRow.':B'.$lastRow)->getFont()->setBold(true);
        $sheet->getStyle('A'.$lastRow.':B'.$lastRow)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFE9ECEF');
    }
}

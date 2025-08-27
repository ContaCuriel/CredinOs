<?php

namespace App\Exports;

use App\Models\Patron;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class FiniquitoExport implements FromView, WithTitle, WithStyles, WithColumnWidths, WithDrawings
{
    // ... constructor y otros métodos ...
    protected $data;
    protected $patron;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->patron = $data['patron'] ?? null;
    }

    public function view(): View
    {
        return view('finiquitos.excel_simple', $this->data);
    }

    public function title(): string
    {
        return str_replace(' ', '_', $this->data['titulo_documento']);
    }

    public function drawings()
    {
        if ($this->patron && $this->patron->logo_path) {
            $rutaCorrecta = 'storage/' . $this->patron->logo_path;
            $pathDelLogo = public_path($rutaCorrecta);
            if (file_exists($pathDelLogo)) {
                $drawing = new Drawing();
                $drawing->setName('Logo Patrón');
                $drawing->setDescription($this->patron->razon_social);
                $drawing->setPath($pathDelLogo);
                $drawing->setHeight(50);
                $drawing->setOffsetX(80);
                $drawing->setOffsetY(10);
                $drawing->setCoordinates('A1');
                return $drawing;
            }
        }
        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 45,
            'B' => 20,
        ];
    }


    public function styles(Worksheet $sheet)
    {
        // Coordenadas
        $tituloPrincipalRange = 'A8:B8';
        $nombreEmpleadoRange = 'A9:B9';
        $tituloInfo = 'A11';
        $tituloDesglose = 'A15';
        $encabezadosConceptosRange = 'A16:B16'; // Rango para CONCEPTO y MONTO
        $inicioConceptos = 16;
        $primerMontoFila = $inicioConceptos + 1;
        
        // Estilos del título principal
        $sheet->mergeCells($tituloPrincipalRange);
        $sheet->getStyle($tituloPrincipalRange)->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle($tituloPrincipalRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($tituloPrincipalRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE9ECEF');
        $sheet->getStyle($tituloPrincipalRange)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        
        // Estilos del nombre del empleado
        $sheet->mergeCells($nombreEmpleadoRange);
        $sheet->getStyle($nombreEmpleadoRange)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle($nombreEmpleadoRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Estilos de los subtítulos y encabezados de tabla
        $sheet->getStyle($tituloInfo)->getFont()->setBold(true);
        $sheet->getStyle($tituloDesglose)->getFont()->setBold(true);
        // --- NUEVO AJUSTE: Negritas para CONCEPTO y MONTO ---
        $sheet->getStyle($encabezadosConceptosRange)->getFont()->setBold(true);

        // Rango de la tabla de desglose (sin la firma)
        $totalRow = $sheet->getHighestRow() - 6; // Ajustado por las nuevas filas finales
        $rangeDesglose = "A{$inicioConceptos}:B{$totalRow}";
        $sheet->getStyle($rangeDesglose)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        // Formato de moneda para los montos
        $sheet->getStyle("B{$inicioConceptos}:B{$totalRow}")->getNumberFormat()->setFormatCode('$#,##0.00');
        
        // Fórmula de SUMA
        $celdaTotal = 'B' . $totalRow;
        $rangoSuma = 'B' . $primerMontoFila . ':B' . ($totalRow - 1);
        $sheet->getCell($celdaTotal)->setValue('=SUM(' . $rangoSuma . ')');

        // Estilos de la fila del total
        $sheet->getStyle("A{$totalRow}:B{$totalRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$totalRow}:B{$totalRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE9ECEF');

        // Estilos de la sección de Firma
        $lastRow = $sheet->getHighestRow() - 2; // Ajustado por las filas de espacio
        $lineaFirmaRow = $lastRow - 2;
        
        $sheet->mergeCells("A{$lineaFirmaRow}:B{$lineaFirmaRow}");
        $sheet->getStyle("A{$lineaFirmaRow}:B{$lineaFirmaRow}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);

        for ($i = $lineaFirmaRow + 1; $i <= $lastRow; $i++) {
            $sheet->mergeCells("A{$i}:B{$i}");
            $sheet->getStyle("A{$i}:B{$i}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
    }
}
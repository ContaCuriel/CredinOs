<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ResumenNetosExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents, WithColumnFormatting
{
    protected Collection $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function title(): string
    {
        return 'Resumen General';
    }

    public function headings(): array
    {
        return [
            'Sucursal',
            'Neto a Pagar',
        ];
    }
    
    public function columnFormats(): array
    {
        return [
            'B' => '$ #,##0.00;[Red]-$ #,##0.00;"$ "0.00',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Estilo para encabezados
                $sheet->getStyle('A1:B1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2E75B6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);

                if ($this->data->count() > 0) {
                    $lastRow = $this->data->count() + 1;
                    $sheet->getStyle('A1:B' . $lastRow)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                    ]);

                    // Fila de Total General
                    $totalsRow = $lastRow + 1;
                    $sheet->setCellValue("A{$totalsRow}", 'TOTAL GENERAL:');
                    $sheet->setCellValue("B{$totalsRow}", "=SUM(B2:B{$lastRow})");

                    $sheet->getStyle("A{$totalsRow}:B{$totalsRow}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12],
                        'borders' => ['top' => ['borderStyle' => Border::BORDER_THICK]]
                    ]);
                }
            },
        ];
    }
}
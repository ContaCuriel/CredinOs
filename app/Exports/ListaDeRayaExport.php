<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ListaDeRayaExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $resultados;

    public function __construct($resultados)
    {
        $this->resultados = $resultados;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Le pasamos la colección de resultados que ya calculamos en el controlador
        return $this->resultados;
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        // Define las cabeceras de las columnas en el Excel
        return [
            'Empleado',
            'Puesto',
            'Sueldo Quincenal',
            'Bonos (Perm./Cump.)',
            'Prima Vacacional',
            'Total Percepciones',
            'Deducción Faltas',
            'Deducción Préstamo',
            'Otras Deducciones',
            'Total Deducciones',
            'Neto a Pagar',
        ];
    }

    /**
    * @param mixed $filaResultado El item de la colección de resultados
    * @return array
    */
    public function map($filaResultado): array
    {
        // Mapea cada fila de resultados al orden de las cabeceras
        return [
            $filaResultado['empleado_nombre'],
            $filaResultado['puesto'],
            $filaResultado['sueldo_quincenal'],
            $filaResultado['total_bonos'],
            $filaResultado['prima_vacacional'],
            $filaResultado['total_percepciones'],
            $filaResultado['deduccion_faltas'],
            $filaResultado['deduccion_prestamo'],
            $filaResultado['deduccion_otras'],
            $filaResultado['total_deducciones'],
            $filaResultado['neto_a_pagar'],
        ];
    }

    /**
     * Aplica estilos a la hoja de Excel.
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        // Poner en negrita la primera fila (cabeceras)
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
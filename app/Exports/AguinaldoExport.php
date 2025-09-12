<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AguinaldoExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $resultados;

    public function __construct(array $resultados)
    {
        $this->resultados = $resultados;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect($this->resultados);
    }

    /**
     * Define los encabezados de las columnas.
     */
    public function headings(): array
    {
        return [
            'Nombre Empleado',
            'Puesto',
            'Sucursal',
            'Fecha Ingreso',
            'Salario Diario',
            'Días Trabajados (Año)',
            'Aguinaldo a Pagar',
        ];
    }

    /**
     * Mapea los datos a las columnas correspondientes.
     */
    public function map($resultado): array
    {
        return [
            $resultado['nombre_completo'],
            $resultado['nombre_puesto'],
            $resultado['nombre_sucursal'],
            date('d/m/Y', strtotime($resultado['fecha_ingreso'])),
            $resultado['salario_diario'],
            $resultado['dias_trabajados'],
            $resultado['aguinaldo_a_pagar'],
        ];
    }
}
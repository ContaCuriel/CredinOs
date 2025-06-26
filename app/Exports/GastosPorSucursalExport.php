<?php
// app/Exports/GastosPorSucursalExport.php

namespace App\Exports;

use App\Models\Gasto;
use App\Models\Sucursal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class GastosPorSucursalExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $fechaInicio;
    protected $fechaFin;

    // Usamos el constructor para pasar los filtros de fecha desde el controlador
    public function __construct($fechaInicio, $fechaFin)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // 1. Obtenemos los datos base (igual que en el controlador)
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $gastos = Gasto::with(['categoria', 'sucursal'])
                       ->whereBetween('fecha_gasto', [$this->fechaInicio, $this->fechaFin])
                       ->get();

        $categoriasConGastos = \App\Models\Categoria::whereIn('id', $gastos->pluck('categoria_id'))->orderBy('nombre')->get();
        
        $datosPivoteados = $gastos->groupBy('categoria.nombre')->map(function ($gastosPorCategoria) {
            return $gastosPorCategoria->groupBy('sucursal.nombre_sucursal')->map(function ($gastos) {
                return $gastos->sum('monto_total');
            });
        });

        // 2. Construimos la colección para la hoja de cálculo
        $collection = collect();

        // 3. Añadimos las filas de datos
        foreach ($categoriasConGastos as $categoria) {
            $fila = ['categoria' => $categoria->nombre];
            $totalFila = 0;

            foreach ($sucursales as $sucursal) {
                $monto = $datosPivoteados[$categoria->nombre][$sucursal->nombre_sucursal] ?? 0;
                $fila[$sucursal->nombre_sucursal] = $monto;
                $totalFila += $monto;
            }
            $fila['total_categoria'] = $totalFila;
            $collection->push($fila);
        }

        // 4. Añadimos la fila de totales al final
        $filaTotales = ['categoria' => 'TOTAL POR SUCURSAL'];
        $granTotal = 0;
        foreach ($sucursales as $sucursal) {
            $totalColumna = $collection->sum($sucursal->nombre_sucursal);
            $filaTotales[$sucursal->nombre_sucursal] = $totalColumna;
            $granTotal += $totalColumna;
        }
        $filaTotales['total_categoria'] = $granTotal;
        $collection->push($filaTotales);

        return $collection;
    }

    /**
     * Define los encabezados de la hoja de cálculo.
     */
    public function headings(): array
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get()->pluck('nombre_sucursal')->toArray();
        return array_merge(['Categoría de Gasto'], $sucursales, ['TOTAL POR CATEGORÍA']);
    }

    /**
     * Aplica estilos a la hoja de cálculo.
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para toda la fila 1 (encabezados)
            1    => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '343a40']]],

            // Poner en negrita la última fila (fila de totales)
            ($sheet->getHighestRow()) => ['font' => ['bold' => true]],

            // Poner en negrita la primera columna
            'A'  => ['font' => ['bold' => true]],
            
            // Poner en negrita la última columna (columna de totales)
            ($sheet->getHighestColumn()) => ['font' => ['bold' => true]],
        ];
    }
}
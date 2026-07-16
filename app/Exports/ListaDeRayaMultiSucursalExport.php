<?php

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
                $totalRow = 1 + 1 + $rowCount + 2;
                // --- CORRECCIÓN AQUÍ: Cambiamos la S por la T ---
                $netoTotalFormula = "='" . $sheetName . "'!T" . $totalRow; 
            } else {
                $netoTotalFormula = 0;
            }

            $resumenData->push([
                'sucursal' => $sucursal->nombre_sucursal,
                'neto_formula' => $netoTotalFormula, 
            ]);
        }

        $resumenSheet = new ResumenNetosExport($resumenData);
        array_unshift($sheets, $resumenSheet);

        return $sheets;
    }
}
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

            $sheetName = $sheetExport->title();
            $rowCount = $sheetExport->collection()->count();
            
            if ($rowCount > 0) {
                $totalRow = 1 + 1 + $rowCount + 2;
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
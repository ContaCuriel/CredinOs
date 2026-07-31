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
                // Cálculo de la fila exacta donde cae el Total en la otra pestaña
                $totalRow = 4 + $rowCount;
                
                // 🔥 AQUÍ ESTABA EL ERROR: Apuntaba a la T (Deducciones). Ahora es U (Neto a Pagar)
                $netoTotalFormula = "='" . $sheetName . "'!U" . $totalRow; 
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
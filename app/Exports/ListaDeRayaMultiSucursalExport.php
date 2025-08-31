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

            // --- INICIO DE LA MODIFICACIÓN ---
            // Obtenemos los datos necesarios para construir la fórmula
            $sheetName = $sheetExport->title();
            $rowCount = $sheetExport->collection()->count();
            
            // Si hay datos, calculamos la fila del total. Si no, el total es 0.
            if ($rowCount > 0) {
                // Fila del título (1) + Fila de cabeceras (1) + Filas de datos + 2 filas de espacio = Fila de totales
                $totalRow = 1 + 1 + $rowCount + 2;
                $netoTotalFormula = "='" . $sheetName . "'!S" . $totalRow;
            } else {
                // Si no hay empleados en esa sucursal, el total es 0.
                $netoTotalFormula = 0;
            }

            $resumenData->push([
                'sucursal' => $sucursal->nombre_sucursal,
                'neto_formula' => $netoTotalFormula, // Pasamos la fórmula en lugar del valor
            ]);
            // --- FIN DE LA MODIFICACIÓN ---
        }

        $resumenSheet = new ResumenNetosExport($resumenData);
        array_unshift($sheets, $resumenSheet);

        return $sheets;
    }
}

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
        // Colección para guardar los datos del resumen
        $resumenData = collect();

        // Obtenemos las sucursales tal como en tu código original.
        // Si en el futuro solo quieres procesar sucursales activas, puedes añadir ->where('status', 'Alta')
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();

        foreach ($sucursales as $sucursal) {
            // Creamos la hoja para la sucursal, usando 'id_sucursal' que es el correcto.
            $sheetExport = new ListaDeRayaSheetExport($this->periodo, $sucursal->id_sucursal);
            $sheets[] = $sheetExport;

            // Obtenemos el total y lo guardamos para el resumen
            $resumenData->push([
                'sucursal' => $sucursal->nombre_sucursal,
                'neto' => $sheetExport->getNetoAPagarTotal(),
            ]);
        }

        // Creamos la hoja de resumen con los datos recopilados
        $resumenSheet = new ResumenNetosExport($resumenData);
        // La insertamos al principio del array para que sea la primera hoja
        array_unshift($sheets, $resumenSheet);

        return $sheets;
    }
}
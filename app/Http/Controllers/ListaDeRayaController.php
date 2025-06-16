<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sucursal;
use App\Models\Empleado;
use App\Models\Asistencia;
use App\Models\DeduccionEmpleado;
use Carbon\Carbon;
use App\Exports\ListaDeRayaSheetExport;
use App\Exports\ListaDeRayaMultiSucursalExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class ListaDeRayaController extends Controller
{
    /**
     * Muestra la interfaz para generar la lista de raya.
     */
    public function index(Request $request)
    {
        // ... (código para obtener $opcionesPeriodo y $sucursales, se queda igual)
        $opcionesPeriodo = $this->getOpcionesPeriodo();
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $resultados = collect();
        $sucursalSeleccionada = null;

        if ($request->filled('periodo') && $request->filled('id_sucursal')) {
            $periodoSeleccionado = $request->input('periodo');
            $idSucursal = $request->input('id_sucursal');
            
            if ($idSucursal == 'todas') {
                $sucursalSeleccionada = (object)['nombre_sucursal' => 'Todas las Sucursales (Solo para Exportar)'];
            } else {
                $sucursalSeleccionada = Sucursal::find($idSucursal);
                // Usamos la misma clase de exportación para obtener los resultados para la vista,
                // asegurando que la lógica sea idéntica.
                $export = new ListaDeRayaSheetExport($periodoSeleccionado, (int)$idSucursal);
                $resultados = $export->collection();
            }
        }

        return view('lista-de-raya.index', compact(
            'sucursales',
            'opcionesPeriodo',
            'resultados',
            'sucursalSeleccionada'
        ));
    }

    /**
     * Genera y descarga un reporte de la lista de raya en formato Excel.
     */
    public function exportarExcel(Request $request)
    {
        $request->validate([
            'periodo' => 'required|string',
            'id_sucursal' => 'required|string', // Acepta el valor 'todas'
        ]);

        $periodo = $request->input('periodo');
        $idSucursal = $request->input('id_sucursal');

        list($fechaInicioStr, $fechaFinStr) = explode('_', $periodo);
        $nombrePeriodo = Carbon::parse($fechaInicioStr)->format('Y_m_d') . '_al_' . Carbon::parse($fechaFinStr)->format('Y_m_d');
        
        if ($idSucursal == 'todas') {
            // Exportar todas las sucursales en hojas separadas
            $fileName = "lista_de_raya_todas_{$nombrePeriodo}.xlsx";
            return Excel::download(new ListaDeRayaMultiSucursalExport($periodo), $fileName);
        } else {
            // Exportar una sola sucursal
            $sucursal = Sucursal::findOrFail($idSucursal);
            $nombreSucursal = Str::slug($sucursal->nombre_sucursal);
            $fileName = "lista_de_raya_{$nombreSucursal}_{$nombrePeriodo}.xlsx";
            return Excel::download(new ListaDeRayaSheetExport($periodo, (int)$idSucursal), $fileName);
        }
    }

    /**
     * Helper para generar las opciones de periodo.
     */
    private function getOpcionesPeriodo(): array
    {
        $opcionesPeriodo = [];
        $fechaActual = Carbon::now();
        for ($i = 0; $i < 13; $i++) {
            $fecha = $fechaActual->copy()->subMonths($i);
            // 1ra Quincena
            $inicioQuincena1 = $fecha->copy()->startOfMonth();
            $finQuincena1 = $fecha->copy()->startOfMonth()->addDays(14);
            $valor1 = $inicioQuincena1->toDateString() . '_' . $finQuincena1->toDateString();
            $texto1 = '1ra Quincena ' . $inicioQuincena1->translatedFormat('F Y');
            if(!in_array($texto1, array_column($opcionesPeriodo, 'texto'))) {
                $opcionesPeriodo[] = ['valor' => $valor1, 'texto' => $texto1];
            }
            // 2da Quincena
            $inicioQuincena2 = $fecha->copy()->startOfMonth()->addDays(15);
            $finQuincena2 = $fecha->copy()->endOfMonth();
            $valor2 = $inicioQuincena2->toDateString() . '_' . $finQuincena2->toDateString();
            $texto2 = '2da Quincena ' . $inicioQuincena2->translatedFormat('F Y');
            if(!in_array($texto2, array_column($opcionesPeriodo, 'texto'))) {
                 $opcionesPeriodo[] = ['valor' => $valor2, 'texto' => $texto2];
            }
        }
        return $opcionesPeriodo;
    }
}
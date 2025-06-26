<?php
// app/Http/Controllers/ReporteController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gasto;
use App\Models\Sucursal;
use App\Models\Categoria;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GastosPorSucursalExport;

class ReporteController extends Controller
{
    /**
     * Muestra el reporte de gastos pivoteado por sucursal y categoría.
     */
    public function gastosPorSucursal(Request $request)
    {
        // --- 1. Obtener los filtros y los datos base ---
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', now()->endOfMonth()->toDateString());

        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        
        // --- 2. Realizar la consulta principal ---
        $gastos = Gasto::with(['categoria', 'sucursal'])
                       ->whereBetween('fecha_gasto', [$fechaInicio, $fechaFin])
                       ->get();

        // --- 3. Procesar y pivotar los datos ---
        // Agrupamos primero por categoría, luego por sucursal, y sumamos los montos.
        $datosPivoteados = $gastos->groupBy('categoria.nombre')->map(function ($gastosPorCategoria) {
            return $gastosPorCategoria->groupBy('sucursal.nombre_sucursal')->map(function ($gastos) {
                return $gastos->sum('monto_total');
            });
        });

        // Obtenemos una lista única de las categorías que tuvieron gastos en este periodo
        $categoriasConGastos = Categoria::whereIn('id', $gastos->pluck('categoria_id'))->orderBy('nombre')->get();
        
        // --- 4. Pasar todo a la vista ---
        return view('reportes.gastos_por_sucursal', [
            'sucursales' => $sucursales,
            'categorias' => $categoriasConGastos,
            'datosPivoteados' => $datosPivoteados,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
        ]);
    }

     public function exportarGastosPorSucursal(Request $request)
    {
        // Obtenemos los mismos filtros de fecha que en el reporte principal
        $fechaInicio = $request->query('fecha_inicio', now()->startOfMonth()->toDateString());
        $fechaFin = $request->query('fecha_fin', now()->endOfMonth()->toDateString());
        
        $nombreArchivo = 'ReporteGastosPorSucursal_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new GastosPorSucursalExport($fechaInicio, $fechaFin), $nombreArchivo);
    }
}
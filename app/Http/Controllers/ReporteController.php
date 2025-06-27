<?php
// app/Http/Controllers/ReporteController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gasto;
use App\Models\Sucursal;
use App\Models\Categoria;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GastosPorSucursalExport;
use App\Models\Account;
use Carbon\Carbon;

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

 public function trialBalance(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $accounts = Account::with('children')->whereNull('parent_id')->orderBy('code')->get();

        // CAMBIO CLAVE: Apuntamos a la carpeta 'reportes'
        return view('reportes.trial_balance', compact('accounts', 'startDate', 'endDate'));
    }

    public function incomeStatement(Request $request)
    {
        // Validar las fechas de entrada, usando el mes actual por defecto.
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // --- CÁLCULO DE INGRESOS ---
        // Obtenemos la cuenta principal de Ingresos (código 400 del catálogo SAT).
        $incomeAccount = Account::where('code', '400')->first();
        $totalIncome = 0;
        if ($incomeAccount) {
            $movements = $incomeAccount->getMovements($startDate, $endDate);
            // Los ingresos son de naturaleza Acreedora (Haber - Deber).
            $totalIncome = $movements['credits'] - $movements['debits'];
        }

        // --- CÁLCULO DE GASTOS ---
        // Obtenemos las cuentas principales de Gastos (códigos 600 y 800 del catálogo SAT).
        $expenseAccounts = Account::whereIn('code', ['600', '800'])->get();
        $totalExpenses = 0;
        foreach ($expenseAccounts as $account) {
            $movements = $account->getMovements($startDate, $endDate);
            // Los gastos son de naturaleza Deudora (Deber - Haber).
            $totalExpenses += $movements['debits'] - $movements['credits'];
        }

        // --- UTILIDAD NETA ---
        $netIncome = $totalIncome - $totalExpenses;

        return view('reportes.income_statement', compact(
            'totalIncome',
            'totalExpenses',
            'netIncome',
            'startDate',
            'endDate'
        ));
    }



}
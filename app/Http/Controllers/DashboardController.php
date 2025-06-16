<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; // Aunque no la uses directamente en index, es bueno tenerla si la añades a la firma.
use App\Models\Empleado;
use App\Models\Contrato;
use App\Models\Patron; // Asegúrate de que este 'use' esté presente
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Para la subconsulta de contratosPorVencer

class DashboardController extends Controller
{
    public function index() // Si no usas $request, puedes quitarla de los parámetros
    {
        $hoy = Carbon::now();
        $mesActual = $hoy->month;

        // Empleados que cumplen años este mes
        $cumpleanerosDelMes = Empleado::where('status', 'Alta')
            ->whereMonth('fecha_nacimiento', $mesActual)
            ->orderByRaw('DAY(fecha_nacimiento) ASC')
            ->get();

        // Empleados que cumplen aniversario de ingreso este mes
        $aniversariosDelMes = Empleado::where('status', 'Alta')
            ->whereMonth('fecha_ingreso', $mesActual)
            ->orderByRaw('DAY(fecha_ingreso) ASC')
            ->get();
        
        // LÓGICA PARA CONTRATOS POR VENCER
        $fechaHoyParaComparar = Carbon::today();
        $fechaLimiteVencimiento = Carbon::today()->addDays(15);

        $latestContractIdsSubquery = Contrato::select('id_empleado', DB::raw('MAX(fecha_fin) as max_fecha_fin'))
            ->whereHas('empleado', function ($query) {
                $query->where('status', 'Alta');
            })
            ->whereNotNull('fecha_fin')
            ->where('fecha_fin', '>=', $fechaHoyParaComparar)
            ->groupBy('id_empleado');

        $contratosPorVencer = Contrato::with('empleado.puesto', 'empleado.sucursal')
            ->joinSub($latestContractIdsSubquery, 'latest_contracts', function ($join) {
                $join->on('contratos.id_empleado', '=', 'latest_contracts.id_empleado')
                     ->on('contratos.fecha_fin', '=', 'latest_contracts.max_fecha_fin');
            })
            ->whereBetween('contratos.fecha_fin', [$fechaHoyParaComparar, $fechaLimiteVencimiento])
            ->orderBy('contratos.fecha_fin', 'asc')
            ->get();
            
        // =====> NUEVA LÓGICA PARA EL WIDGET DE IMSS (AHORA DENTRO DEL MÉTODO INDEX) <=====
        $patronesTodos = Patron::orderBy('razon_social')->get();
        $patronesConteoImss = [];

        foreach ($patronesTodos as $patron) {
            $conteo = Empleado::where('status', 'Alta') // Empleados activos en la empresa
                              ->where('id_patron_imss', $patron->id_patron) // Vinculados a este patrón para IMSS
                              ->where('estado_imss', 'Alta') // Con estado IMSS 'Alta'
                              ->count();
            
            if ($conteo > 0) {
                $patronesConteoImss[] = [
                    'patron' => $patron,
                    'conteo_imss_alta' => $conteo,
                ];
            }
        }
        // ==========================================================================
        
        return view('dashboard', compact(
            'cumpleanerosDelMes', 
            'aniversariosDelMes', 
            'contratosPorVencer',
            'patronesConteoImss' // Pasamos la nueva variable a la vista
        ));
    } // <--- Esta es la llave de cierre del método index()

} // <--- Esta es la llave de cierre de la clase DashboardController
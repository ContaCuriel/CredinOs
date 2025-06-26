<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empleado;
use App\Models\Contrato;
use App\Models\Patron;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard principal de la aplicación.
     */
    public function index()
    {
        $hoy = Carbon::now();
        $mesActual = $hoy->month;
        $anoActual = $hoy->year; // <-- AÑADIDO: Obtenemos el año actual para la comparación.

        // Empleados que cumplen años este mes
        $cumpleanerosDelMes = Empleado::where('status', 'Alta')
            ->whereMonth('fecha_nacimiento', $mesActual)
            ->orderByRaw('DAY(fecha_nacimiento) ASC')
            ->get();

        // Empleados que cumplen aniversario de ingreso este mes (CON CORRECCIÓN)
        $aniversariosDelMes = Empleado::where('status', 'Alta')
            ->whereMonth('fecha_ingreso', $mesActual)
            // =====> LÍNEA CLAVE AÑADIDA <=====
            // Filtra para que el año de ingreso sea anterior al año actual.
            // Esto excluye a los que ingresaron este mismo año (aniversario de 0 años).
            ->whereYear('fecha_ingreso', '<', $anoActual)
            ->orderByRaw('DAY(fecha_ingreso) ASC')
            ->get();
        
        // LÓGICA PARA CONTRATOS POR VENCER
        $fechaHoyParaComparar = Carbon::today();
        $fechaLimiteVencimiento = Carbon::today()->addDays(15);

        // Subconsulta para obtener el último contrato de cada empleado activo
        $latestContractIdsSubquery = Contrato::select('id_empleado', DB::raw('MAX(fecha_fin) as max_fecha_fin'))
            ->whereHas('empleado', function ($query) {
                $query->where('status', 'Alta');
            })
            ->whereNotNull('fecha_fin')
            ->where('fecha_fin', '>=', $fechaHoyParaComparar)
            ->groupBy('id_empleado');

        // Consulta principal que se une con la subconsulta para obtener los contratos por vencer
        $contratosPorVencer = Contrato::with('empleado.puesto', 'empleado.sucursal')
            ->joinSub($latestContractIdsSubquery, 'latest_contracts', function ($join) {
                $join->on('contratos.id_empleado', '=', 'latest_contracts.id_empleado')
                     ->on('contratos.fecha_fin', '=', 'latest_contracts.max_fecha_fin');
            })
            ->whereBetween('contratos.fecha_fin', [$fechaHoyParaComparar, $fechaLimiteVencimiento])
            ->orderBy('contratos.fecha_fin', 'asc')
            ->get();
            
        // LÓGICA PARA EL WIDGET DE IMSS
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
        
        // --- INICIO: Lógica para el Widget de Gastos ---
$gastosPendientes = collect(); // Creamos una colección vacía por defecto

// Solo ejecutamos la consulta si el usuario tiene el permiso de aprobar
if (auth()->user()->can('aprobar-gastos')) {
    $gastosPendientes = \App\Models\Gasto::with('sucursal')
                                          ->where('estado', 'En Aprobación')
                                          ->latest()
                                          ->take(5) // Tomamos los 5 más recientes
                                          ->get();
}
// --- FIN: Lógica para el Widget de Gastos ---

        // Pasamos todas las variables a la vista
        return view('dashboard', compact(
            'cumpleanerosDelMes', 
            'aniversariosDelMes', 
            'contratosPorVencer',
            'gastosPendientes',
            'patronesConteoImss'
        ));
    }
}

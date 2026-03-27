<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Sucursal;
use App\Models\Recovery;
use App\Models\Gasto;
use App\Models\Contrato;
use App\Models\Empleado;
use App\Models\Patron;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $data = [];

        // --- LÓGICA PARA EL SALUDO PERSONALIZADO (SIMPLIFICADO) ---
        $horaActual = Carbon::now('America/Mexico_City')->hour;
        if ($horaActual < 12) {
            $data['saludo'] = 'Buenos días';
        } elseif ($horaActual < 19) {
            $data['saludo'] = 'Buenas tardes';
        } else {
            $data['saludo'] = 'Buenas noches';
        }
        
        $data['nombreUsuario'] = explode(' ', trim($user->name))[0];
        $data['mensajeEspecial'] = null; 
        // --- FIN DE LA LÓGICA DEL SALUDO ---

        // Widget: Contratos por Vencer (Hoy a +15 días)
        if ($user->can('ver-widget-contratos-vencer')) {
            $data['contratosPorVencer'] = Contrato::whereNotNull('fecha_fin')
                ->whereBetween('fecha_fin', [Carbon::today(), Carbon::today()->addDays(15)])
                ->whereHas('empleado', function ($query) {
                    $query->where('status', 'Alta');
                })
                ->with('empleado.puesto', 'empleado.sucursal')
                ->orderBy('fecha_fin', 'asc')
                ->get();
        }

        // Widget: Contratos Vencidos (Hace 7 días hasta AYER)
        if ($user->can('ver-widget-contratos-vencer')) {
            $haceSieteDias = Carbon::today()->subDays(7)->startOfDay();
            $ayer = Carbon::yesterday()->endOfDay();

            $empleadosActivos = Empleado::where('status', 'Alta')
                ->with(['puesto', 'sucursal', 'ultimoContrato'])
                ->get();

            $data['contratosVencidosRecientemente'] = $empleadosActivos->filter(function ($empleado) use ($haceSieteDias, $ayer) {
                if (!$empleado->ultimoContrato || !$empleado->ultimoContrato->fecha_fin) {
                    return false;
                }
                $fechaFin = Carbon::parse($empleado->ultimoContrato->fecha_fin);
                return $fechaFin->isBetween($haceSieteDias, $ayer);
            });
        }

        // Widget: Cumpleaños del Mes
        if ($user->can('ver-widget-cumpleanos')) {
            $hoy = Carbon::today();
            $cumpleaneros = Empleado::with('sucursal')
                ->where('status', 'Alta')
                ->whereMonth('fecha_nacimiento', $hoy->month)
                ->orderByRaw('EXTRACT(DAY FROM fecha_nacimiento) ASC')
                ->get();

            $cumpleaneros->map(function ($empleado) use ($hoy) {
                if ($empleado->fecha_ingreso) {
                    $fechaCumpleanos = Carbon::parse($empleado->fecha_nacimiento)->year($hoy->year);
                    $antiguedadEnMeses = Carbon::parse($empleado->fecha_ingreso)->diffInMonths($fechaCumpleanos);
                    $empleado->esElegibleParaBono = $antiguedadEnMeses > 6;
                } else {
                    $empleado->esElegibleParaBono = false;
                }
                return $empleado;
            });
            $data['cumpleanerosDelMes'] = $cumpleaneros;
        }

        // Widget: Aniversarios Laborales del Mes
        if ($user->can('ver-widget-aniversarios')) {
            $hoy = Carbon::today();
            $aniversarios = Empleado::where('status', 'Alta')
                ->whereMonth('fecha_ingreso', $hoy->month)
                ->orderByRaw('EXTRACT(DAY FROM fecha_ingreso) ASC')
                ->get()
                ->map(function ($empleado) use ($hoy) {
                    $fechaIngreso = Carbon::parse($empleado->fecha_ingreso);
                    $anosCelebrando = $hoy->year - $fechaIngreso->year;
                    $empleado->anosCelebrando = $anosCelebrando;
                    return $empleado;
                })
                ->filter(function ($empleado) {
                    return $empleado->anosCelebrando > 0;
                });

            $data['aniversariosDelMes'] = $aniversarios;
        }

        // Widget: Ingresos y Bajas de la Quincena
        if ($user->can('ver-widget-nuevos-ingresos')) {
            $now = Carbon::now();
            if ($now->day <= 15) {
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->day(15)->endOfDay();
                $data['fortnightTitle'] = "1ra Quincena";
            } else {
                $startDate = $now->copy()->day(16)->startOfDay();
                $endDate = $now->copy()->endOfMonth()->endOfDay();
                $data['fortnightTitle'] = "2da Quincena";
            }
            
            $data['nuevosIngresos'] = Empleado::where('status', 'Alta')
                ->whereBetween('fecha_ingreso', [$startDate, $endDate])
                ->with(['puesto', 'sucursal'])
                ->orderBy('fecha_ingreso', 'desc')
                ->get();

            $data['bajasQuincena'] = Empleado::where('status', 'Baja')
                ->whereBetween('fecha_baja', [$startDate, $endDate])
                ->with(['puesto', 'sucursal'])
                ->orderBy('fecha_baja', 'desc')
                ->get();
        }

        // Widget: Empleados con IMSS por Patrón
        if ($user->can('ver-widget-imss')) {
            $patrones = Patron::withCount(['empleados as conteo_imss_alta' => function ($query) {
                $query->where('estado_imss', 'Alta');
            }])->get();

            $data['patronesConteoImss'] = $patrones->map(function ($patron) {
                return [
                    'patron' => $patron,
                    'conteo_imss_alta' => $patron->conteo_imss_alta
                ];
            })->filter(function ($item) {
                return $item['conteo_imss_alta'] > 0;
            });
        }

        // Widget: Gastos Pendientes de Aprobación
        if ($user->can('aprobar-gastos')) {
            $data['gastosPendientes'] = Gasto::where('estado', 'En Aprobación')
                ->with(['sucursal', 'categoria'])
                ->latest()
                ->take(5)
                ->get();
        }

        // --- DASHBOARD GERENCIAL FINANCIERO (Capital vs Interés) ---
        if ($user->can('ver-widget-rentabilidad-sucursales')) {
            
            $dashStartDate = $request->input('start_date', now()->startOfMonth()->toDateString());
            $dashEndDate = $request->input('end_date', now()->endOfMonth()->toDateString());

            $startMonth = Carbon::parse($dashStartDate)->month;
            $startYear = Carbon::parse($dashStartDate)->year;
            $endMonth = Carbon::parse($dashEndDate)->month;
            $endYear = Carbon::parse($dashEndDate)->year;

            $sucursales = Sucursal::all();
            $rentabilidad = [];
            $totalInteresesEmpresa = 0;
            $totalCapitalEmpresa = 0;
            $totalGastosEmpresa = 0;

            foreach ($sucursales as $sucursal) {
                $recoveryData = Recovery::where('sucursal_id', $sucursal->id_sucursal)
                    ->whereBetween('year', [$startYear, $endYear])
                    ->whereBetween('month', [$startMonth, $endMonth])
                    ->select(
                        // AQUÍ ESTÁ EL CAMBIO: 'capital_recovered' en lugar de 'capital_collected'
                        DB::raw('SUM(capital_recovered) as total_capital'),
                        DB::raw('SUM(interest_collected) as total_interest')
                    )->first();

                $capRecup = $recoveryData->total_capital ?? 0;
                $intCobrado = $recoveryData->total_interest ?? 0;

                $gastos = Gasto::where('sucursal_id', $sucursal->id_sucursal)
                    ->where('estado', 'Aprobado')
                    ->whereBetween('fecha_gasto', [$dashStartDate, $dashEndDate])
                    ->sum('monto_total');

                $utilidadNeta = $intCobrado - $gastos;

                $rentabilidad[] = [
                    'nombre' => $sucursal->nombre_sucursal,
                    'capital' => (float)$capRecup,
                    'ingresos' => (float)$intCobrado,
                    'gastos' => (float)$gastos,
                    'utilidad' => (float)$utilidadNeta,
                ];

                $totalInteresesEmpresa += $intCobrado;
                $totalCapitalEmpresa += $capRecup;
                $totalGastosEmpresa += $gastos;
            }

            usort($rentabilidad, function($a, $b) {
                return $b['utilidad'] <=> $a['utilidad'];
            });

            $data['gastosPorCategoria'] = Gasto::with('categoria')
                ->where('estado', 'Aprobado')
                ->whereBetween('fecha_gasto', [$dashStartDate, $dashEndDate])
                ->get()
                ->groupBy('categoria.nombre')
                ->map(fn($row) => $row->sum('monto_total'));

            $data['rentabilidad'] = $rentabilidad;
            $data['totalInteresesEmpresa'] = $totalInteresesEmpresa;
            $data['totalCapitalEmpresa'] = $totalCapitalEmpresa;
            $data['totalGastosEmpresa'] = $totalGastosEmpresa;
            $data['startDate'] = $dashStartDate;
            $data['endDate'] = $dashEndDate;
        }

        return view('dashboard', $data);
    }
}
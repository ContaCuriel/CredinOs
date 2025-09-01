<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Contrato;
use App\Models\Empleado;
use App\Models\Patron;
use App\Models\Gasto;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = [];

        // --- LÓGICA PARA EL SALUDO PERSONALIZADO ---
        $horaActual = Carbon::now('America/Mexico_City')->hour;
        if ($horaActual < 12) {
            $data['saludo'] = 'Buenos días';
        } elseif ($horaActual < 19) {
            $data['saludo'] = 'Buenas tardes';
        } else {
            $data['saludo'] = 'Buenas noches';
        }
        
        $data['nombreUsuario'] = $user->name;
        $data['mensajeEspecial'] = null;

        // Buscamos al empleado que corresponde al usuario logueado
        $empleadoLogueado = Empleado::where('nombre_completo', $user->name)->first();

        if ($empleadoLogueado) {
            $hoy = Carbon::today();
            // Verificar cumpleaños
            if ($empleadoLogueado->fecha_nacimiento && Carbon::parse($empleadoLogueado->fecha_nacimiento)->isBirthday($hoy)) {
                $data['mensajeEspecial'] = '¡Feliz Cumpleaños! Te deseamos un día increíble.';
            }
            // Verificar aniversario (y que no sea el primer día de trabajo)
            $fechaIngreso = Carbon::parse($empleadoLogueado->fecha_ingreso);
            if ($fechaIngreso->month == $hoy->month && $fechaIngreso->day == $hoy->day && !$fechaIngreso->isToday()) {
                $anos = $fechaIngreso->diffInYears($hoy);
                $data['mensajeEspecial'] = "¡Feliz Aniversario! Hoy cumples {$anos} " . ($anos == 1 ? 'año' : 'años') . " con nosotros.";
            }
        }
        // --- FIN DE LA LÓGICA DEL SALUDO ---

        // Widget: Contratos por Vencer
        if ($user->can('ver-widget-contratos-vencer')) {
            $data['contratosPorVencer'] = Contrato::whereNotNull('fecha_fin')
                ->whereBetween('fecha_fin', [Carbon::today(), Carbon::today()->addDays(15)])
                // Filtramos para que solo muestre contratos de empleados cuyo estado es 'Alta'.
                ->whereHas('empleado', function ($query) {
                    $query->where('status', 'Alta');
                })
                ->with('empleado.puesto', 'empleado.sucursal')
                ->orderBy('fecha_fin', 'asc')
                ->get();
        }

        // Widget: Contratos Vencidos No Renovados (Últimos 7 días)
        if ($user->can('ver-widget-contratos-vencer')) { // Usamos el mismo permiso
            $haceSieteDias = Carbon::today()->subDays(7);
            $hoy = Carbon::today();

            $data['contratosVencidosRecientemente'] = Empleado::where('status', 'Alta')
                ->whereHas('ultimoContrato', function ($query) use ($haceSieteDias, $hoy) {
                    $query->whereBetween('fecha_fin', [$haceSieteDias, $hoy]);
                })
                ->with(['puesto', 'sucursal', 'ultimoContrato'])
                ->get();
        }

        // --- INICIO DE LA CORRECCIÓN DEL WIDGET DE CUMPLEAÑOS ---
        // Widget: Cumpleaños del Mes
        if ($user->can('ver-widget-cumpleanos')) {
            $hoy = Carbon::today();
            $cumpleaneros = Empleado::with('sucursal') // Cargamos la sucursal para evitar N+1 queries
                ->where('status', 'Alta')
                ->whereMonth('fecha_nacimiento', $hoy->month)
                ->orderByRaw('EXTRACT(DAY FROM fecha_nacimiento) ASC')
                ->get();

            // Añadimos la lógica para saber si son elegibles para el bono
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
        // --- FIN DE LA CORRECCIÓN ---


        // Widget: Aniversarios Laborales del Mes
        if ($user->can('ver-widget-aniversarios')) {
            $hoy = Carbon::today();
            
            // 1. Obtenemos a todos los que cumplen años este mes
            $aniversarios = Empleado::where('status', 'Alta')
                ->whereMonth('fecha_ingreso', $hoy->month)
                ->orderByRaw('EXTRACT(DAY FROM fecha_ingreso) ASC')
                ->get()
                // 2. Mapeamos la colección para añadir el cálculo correcto de años
                ->map(function ($empleado) use ($hoy) {
                    $fechaIngreso = Carbon::parse($empleado->fecha_ingreso);
                    // Calculamos los años que CUMPLIRÁ este mes
                    $anosCelebrando = $hoy->year - $fechaIngreso->year;
                    $empleado->anosCelebrando = $anosCelebrando; // Añadimos la propiedad al objeto
                    return $empleado;
                })
                // 3. Filtramos para quitar a los que cumplen 0 años (recién ingresados)
                ->filter(function ($empleado) {
                    return $empleado->anosCelebrando > 0;
                });

            $data['aniversariosDelMes'] = $aniversarios;
        }

        // Widget: Nuevos Ingresos de la Quincena
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
            $data['nuevosIngresos'] = Empleado::whereBetween('fecha_ingreso', [$startDate, $endDate])
                ->with(['puesto', 'sucursal'])
                ->orderBy('fecha_ingreso', 'desc')
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
            $data['gastosPendientes'] = Gasto::where('estado', 'Pendiente')
                ->with(['sucursal', 'categoria'])
                ->latest()
                ->take(5)
                ->get();
        }

        return view('dashboard', $data);
    }
}

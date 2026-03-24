<?php

namespace App\Http\Controllers;

use App\Models\PeriodoVacacional;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Sucursal;

class VacacionController extends Controller
{
    /**
     * Muestra la lista de resumen de vacaciones.
     */
    public function index(Request $request)
    {
        $search_nombre_empleado = $request->input('search_nombre_empleado');
        $id_sucursal_filter = $request->input('id_sucursal_filter');
        $status_filter = $request->input('status_filter', 'Alta');

        $query = Empleado::query()->whereNotNull('fecha_ingreso');

        if (!empty($search_nombre_empleado)) {
            $query->where('nombre_completo', 'like', '%' . $search_nombre_empleado . '%');
        } else {
            if ($status_filter === 'Baja') {
                $query->where('status', 'Baja');
            } elseif ($status_filter === 'Todos') {
                // No se aplica filtro
            } else {
                $query->where('status', 'Alta');
            }

            if (!empty($id_sucursal_filter)) {
                $query->where('id_sucursal', $id_sucursal_filter);
            }
        }

        $empleados = $query->with(['sucursal'])
            ->orderBy('nombre_completo')
            ->paginate(15)
            ->withQueryString();

        foreach ($empleados as $empleado) {
            $fechaIngreso = Carbon::parse($empleado->fecha_ingreso);
            $fechaCorte = ($empleado->status === 'Baja' && $empleado->fecha_baja)
                ? Carbon::parse($empleado->fecha_baja)
                : Carbon::now();

            $anosCompletosServicio = (int) $fechaIngreso->diffInYears($fechaCorte);
            $empleado->anos_servicio_completados = $anosCompletosServicio;
            
            $vacacionesDetallado = $empleado->getVacacionesDetallado($fechaCorte);
            $empleado->total_dias_restantes = $vacacionesDetallado['total_a_pagar'];
        }

        $todasLasSucursales = Sucursal::orderBy('nombre_sucursal')->get();

        return view('vacaciones.index', compact(
            'empleados',
            'todasLasSucursales',
            'search_nombre_empleado',
            'id_sucursal_filter',
            'status_filter'
        ));
    }

    /**
     * Devuelve el historial en JSON para el popover de finiquitos.
     */
    public function historialJson($id_empleado)
{
    try {
        $empleado = \App\Models\Empleado::findOrFail($id_empleado);
        
        // Obtenemos los periodos tomados (Igual que en tu vista de historial)
        $periodosTomados = \App\Models\PeriodoVacacional::where('id_empleado', $empleado->id_empleado)
                            ->orderBy('fecha_inicio', 'asc')
                            ->get();

        $historialVacacional = [];
        
        if ($empleado->fecha_ingreso) {
            $fechaIngreso = \Carbon\Carbon::parse($empleado->fecha_ingreso);
            
            // Fecha de corte (Baja o Hoy)
            $fechaCorte = ($empleado->status === 'Baja' && $empleado->fecha_baja)
                ? \Carbon\Carbon::parse($empleado->fecha_baja)
                : \Carbon\Carbon::now();
            
            $anosCompletosServicio = $fechaIngreso->diffInYears($fechaCorte);

            // 1. CÁLCULO DE AÑOS COMPLETADOS
            for ($anoDeServicio = 1; $anoDeServicio <= $anosCompletosServicio; $anoDeServicio++) {
                $diasCorrespondientes = $empleado->getDiasVacacionesParaAnoDeServicio($anoDeServicio);
                $diasTomadosEsteAno = $periodosTomados->where('ano_servicio_correspondiente', $anoDeServicio)->sum('dias_tomados');
                
                $inicioAnoServicio = $fechaIngreso->copy()->addYears($anoDeServicio - 1);
                $finAnoServicio = $fechaIngreso->copy()->addYears($anoDeServicio)->subDay();

                $historialVacacional[] = [
                    'ano_servicio' => $anoDeServicio,
                    'periodo' => $inicioAnoServicio->format('d/m/Y') . ' - ' . $finAnoServicio->format('d/m/Y'),
                    'dias_correspondientes' => $diasCorrespondientes,
                    'dias_tomados' => $diasTomadosEsteAno,
                    'dias_restantes' => number_format($diasCorrespondientes - $diasTomadosEsteAno, 2),
                    'estado' => 'Completado'
                ];
            }

            // 2. CÁLCULO PROPORCIONAL DEL AÑO EN CURSO
            $anoDeServicioEnCurso = (int)$anosCompletosServicio + 1;
            $diasTotalesAnoEnCurso = $empleado->getDiasVacacionesParaAnoDeServicio($anoDeServicioEnCurso);
            $inicioAnoEnCurso = $fechaIngreso->copy()->addYears((int)$anosCompletosServicio);
            
            // Calculamos proporcional por meses como lo hace tu historial
            $mesesCompletosEnAnoEnCurso = $inicioAnoEnCurso->diffInMonths($fechaCorte); 
            $diasProporcionalesVac = ($diasTotalesAnoEnCurso / 12) * $mesesCompletosEnAnoEnCurso;
            
            $diasTomadosAnoEnCurso = $periodosTomados->where('ano_servicio_correspondiente', $anoDeServicioEnCurso)->sum('dias_tomados');
            $saldoProporcional = $diasProporcionalesVac - $diasTomadosAnoEnCurso;

            $historialVacacional[] = [
                'ano_servicio' => $anoDeServicioEnCurso,
                'periodo' => $inicioAnoEnCurso->format('d/m/Y') . ' - En curso',
                'dias_correspondientes' => number_format($diasProporcionalesVac, 2),
                'dias_tomados' => $diasTomadosAnoEnCurso,
                'dias_restantes' => number_format($saldoProporcional, 2),
                'estado' => 'En Curso'
            ];
        }

        return response()->json($historialVacacional);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'line' => $e->getLine()
        ], 500);
    }
}

    /**
     * Muestra el historial detallado de vacaciones (Vista HTML).
     */
    public function historialPorEmpleado(Request $request, Empleado $empleado)
    {
        $periodosTomados = PeriodoVacacional::where('id_empleado', $empleado->id_empleado)
                                            ->orderBy('fecha_inicio', 'asc')
                                            ->get();

        $historialVacacional = [];
        $totalDiasRestantesGeneral = 0;

        if ($empleado->fecha_ingreso && Carbon::parse($empleado->fecha_ingreso)->isPast()) {
            $fechaIngreso = Carbon::parse($empleado->fecha_ingreso);
            $fechaCorte = ($empleado->status === 'Baja' && $empleado->fecha_baja)
                ? Carbon::parse($empleado->fecha_baja)
                : Carbon::now();
            
            $anosCompletosServicio = $fechaIngreso->diffInYears($fechaCorte);

            $totalDiasGanadosAnosCompletos = 0;
            $totalDiasTomadosDeAnosCompletos = 0;

            for ($anoDeServicio = 1; $anoDeServicio <= $anosCompletosServicio; $anoDeServicio++) {
                $diasCorrespondientes = $empleado->getDiasVacacionesParaAnoDeServicio($anoDeServicio);
                $totalDiasGanadosAnosCompletos += $diasCorrespondientes;
                $diasTomadosEsteAno = $periodosTomados->where('ano_servicio_correspondiente', $anoDeServicio)->sum('dias_tomados');
                $totalDiasTomadosDeAnosCompletos += $diasTomadosEsteAno;
                
                $inicioAnoServicio = $fechaIngreso->copy()->addYears($anoDeServicio - 1);
                $finAnoServicio = $fechaIngreso->copy()->addYears($anoDeServicio)->subDay();

                $historialVacacional[] = [
                    'ano_servicio' => $anoDeServicio,
                    'periodo_servicio_label' => $inicioAnoServicio->translatedFormat('d M Y') . ' - ' . $finAnoServicio->translatedFormat('d M Y'),
                    'dias_correspondientes' => $diasCorrespondientes,
                    'dias_tomados' => $diasTomadosEsteAno,
                    'dias_restantes' => $diasCorrespondientes - $diasTomadosEsteAno,
                    'estado' => ($empleado->status === 'Baja' && $fechaCorte->isAfter($finAnoServicio)) ? 'Finalizado' : 'Completado',
                ];
            }
            
            $saldoDiasAnteriores = $totalDiasGanadosAnosCompletos - $totalDiasTomadosDeAnosCompletos;

            $anoDeServicioEnCurso = (int)$anosCompletosServicio + 1;
            $diasTotalesAnoEnCurso = $empleado->getDiasVacacionesParaAnoDeServicio($anoDeServicioEnCurso);
            $inicioAnoEnCurso = $fechaIngreso->copy()->addYears((int)$anosCompletosServicio);
            
            $mesesCompletosEnAnoEnCurso = $inicioAnoEnCurso->diffInMonths($fechaCorte); 
            $diasProporcionalesVac = ($diasTotalesAnoEnCurso / 12) * $mesesCompletosEnAnoEnCurso;
            
            $diasTomadosAnoEnCurso = $periodosTomados->where('ano_servicio_correspondiente', $anoDeServicioEnCurso)->sum('dias_tomados');
            $saldoProporcional = $diasProporcionalesVac - $diasTomadosAnoEnCurso;

            $finAnoServicioEnCurso = $inicioAnoEnCurso->copy()->addYear()->subDay();
            $historialVacacional[] = [
                'ano_servicio' => $anoDeServicioEnCurso,
                'periodo_servicio_label' => $inicioAnoEnCurso->translatedFormat('d M Y') . ' - ' . $finAnoServicioEnCurso->translatedFormat('d M Y'),
                'dias_correspondientes' => round($diasProporcionalesVac, 2),
                'dias_tomados' => $diasTomadosAnoEnCurso,
                'dias_restantes' => round($saldoProporcional, 2),
                'estado' => ($empleado->status === 'Baja') ? 'Finalizado' : 'En Curso',
            ];
            
            $totalDiasRestantesGeneral = max(0, $saldoDiasAnteriores) + max(0, $saldoProporcional);
        }
        
        return view('vacaciones.historial_empleado', compact('empleado', 'historialVacacional', 'periodosTomados', 'totalDiasRestantesGeneral'));
    }

    /**
     * Muestra el formulario para crear un nuevo periodo vacacional.
     */
    public function create(Request $request)
    {
        $empleados = Empleado::where('status', 'Alta')->orderBy('nombre_completo')->get();
        $preseleccionado_empleado_id = $request->query('id_empleado', old('id_empleado'));
        return view('vacaciones.create', compact('empleados', 'preseleccionado_empleado_id'));
    }

    /**
     * Guarda un nuevo periodo vacacional.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'ano_servicio_correspondiente' => 'required|integer|min:1',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'comentarios' => 'nullable|string|max:1000',
        ]);

        $fechaInicio = Carbon::parse($validatedData['fecha_inicio']);
        $fechaFin = Carbon::parse($validatedData['fecha_fin']);
        $diasTomadosCalculados = $fechaInicio->diffInDays($fechaFin) + 1;

        PeriodoVacacional::create(array_merge($validatedData, ['dias_tomados' => $diasTomadosCalculados]));

        return redirect()->route('vacaciones.create')->with('success', '¡Periodo vacacional registrado!');
    }
}
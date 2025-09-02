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
     * Display a listing of the resource.
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
                // No filter
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
            // --- INICIO DE LA CORRECCIÓN ---
            // Determinamos la fecha de corte para el cálculo de vacaciones.
            // Si el empleado está de baja y tiene una fecha de baja, esa será la fecha final.
            // De lo contrario, usamos la fecha de hoy.
            $fechaCorte = ($empleado->status === 'Baja' && $empleado->fecha_baja)
                ? Carbon::parse($empleado->fecha_baja)
                : Carbon::now();
            // --- FIN DE LA CORRECCIÓN ---

            $fechaIngreso = Carbon::parse($empleado->fecha_ingreso);
            $anosCompletosServicio = (int) $fechaIngreso->diffInYears($fechaCorte);
            $empleado->anos_servicio_completados = $anosCompletosServicio;
            
            // Pasamos la fecha de corte correcta al método de cálculo.
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
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $empleados = Empleado::where('status', 'Alta')
                            ->orderBy('nombre_completo')
                            ->get();

        $preseleccionado_empleado_id = $request->query('id_empleado', old('id_empleado'));

        return view('vacaciones.create', compact('empleados', 'preseleccionado_empleado_id'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'ano_servicio_correspondiente' => 'required|integer|min:1',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'comentarios' => 'nullable|string|max:1000',
        ],[
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ]);

        $fechaInicio = Carbon::parse($validatedData['fecha_inicio']);
        $fechaFin = Carbon::parse($validatedData['fecha_fin']);
        $diasTomadosCalculados = $fechaInicio->diffInDays($fechaFin) + 1;

        if ($diasTomadosCalculados < 1) {
            return back()->withErrors(['dias_tomados' => 'El periodo de fechas no es válido o resulta en 0 días.'])->withInput();
        }

        $datosParaGuardar = array_merge($validatedData, ['dias_tomados' => $diasTomadosCalculados]);
        PeriodoVacacional::create($datosParaGuardar);

        return redirect()->route('vacaciones.create')
                         ->with('success', '¡Periodo vacacional registrado exitosamente!');
    }

    /**
     * Muestra el historial detallado de vacaciones de un empleado específico.
     */
     public function historialPorEmpleado(Request $request, Empleado $empleado)
    {
        $periodosTomados = PeriodoVacacional::where('id_empleado', $empleado->id_empleado)
            ->orderBy('fecha_inicio', 'asc')
            ->get();

        $historialVacacional = [];
        $totalDiasRestantesGeneral = 0;

        if ($empleado->fecha_ingreso && Carbon::parse($empleado->fecha_ingreso)->isPast()) {
            
            $fechaCorte = ($empleado->status === 'Baja' && $empleado->fecha_baja)
                ? Carbon::parse($empleado->fecha_baja)
                : Carbon::now();

            // Usamos la función del modelo que ya está corregida para obtener el total final.
            $vacacionesDetallado = $empleado->getVacacionesDetallado($fechaCorte);
            $totalDiasRestantesGeneral = $vacacionesDetallado['total_a_pagar'];

            $fechaIngreso = Carbon::parse($empleado->fecha_ingreso);
            $anosCompletosServicio = $fechaIngreso->diffInYears($fechaCorte);

            // 1. Mostramos los años de servicio ya completados.
            for ($anoDeServicio = 1; $anoDeServicio <= $anosCompletosServicio; $anoDeServicio++) {
                $diasCorrespondientes = $empleado->getDiasVacacionesParaAnoDeServicio($anoDeServicio);
                $diasTomadosEsteAno = $periodosTomados->where('ano_servicio_correspondiente', $anoDeServicio)->sum('dias_tomados');
                
                $inicioAnoServicio = $fechaIngreso->copy()->addYears($anoDeServicio - 1);
                $finAnoServicio = $fechaIngreso->copy()->addYears($anoDeServicio)->subDay();

                $historialVacacional[] = [
                    'ano_servicio' => $anoDeServicio,
                    'periodo_servicio_label' => $inicioAnoServicio->translatedFormat('d M Y') . ' - ' . $finAnoServicio->translatedFormat('d M Y'),
                    'dias_correspondientes' => $diasCorrespondientes,
                    'dias_tomados' => $diasTomadosEsteAno,
                    'dias_restantes' => $diasCorrespondientes - $diasTomadosEsteAno,
                    'estado' => 'Completado',
                ];
            }
            
            // 2. Mostramos siempre el periodo actual o final para dar contexto.
            $anoDeServicioEnCurso = $anosCompletosServicio + 1;
            $inicioAnoEnCurso = $fechaIngreso->copy()->addYears($anosCompletosServicio);
            $finAnoServicioEnCurso = $inicioAnoEnCurso->copy()->addYear()->subDay();

            $diasProporcionalesVac = $vacacionesDetallado['proporcional_actual'];
            $diasTomadosAnoEnCurso = $periodosTomados->where('ano_servicio_correspondiente', $anoDeServicioEnCurso)->sum('dias_tomados');
            $saldoProporcional = $diasProporcionalesVac - $diasTomadosAnoEnCurso;
            
            $estado = ($empleado->status === 'Baja') ? 'Finalizado' : 'En Curso';
            
            $historialVacacional[] = [
                'ano_servicio' => $anoDeServicioEnCurso,
                'periodo_servicio_label' => $inicioAnoEnCurso->translatedFormat('d M Y') . ' - ' . $finAnoServicioEnCurso->translatedFormat('d M Y'),
                'dias_correspondientes' => round($diasProporcionalesVac, 2),
                'dias_tomados' => $diasTomadosAnoEnCurso,
                'dias_restantes' => round($saldoProporcional, 2),
                'estado' => $estado,
            ];
        }
        
        return view('vacaciones.historial_empleado', compact('empleado', 'historialVacacional', 'periodosTomados', 'totalDiasRestantesGeneral'));
    }

    /**
     * Display the specified resource.
     */
    public function show(PeriodoVacacional $periodoVacacional)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PeriodoVacacional $periodoVacacional)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PeriodoVacacional $periodoVacacional)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PeriodoVacacional $periodoVacacional)
    {
        //
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\PeriodoVacacional;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Sucursal;
// CarbonPeriod no es necesario importarlo directamente aquí si solo se usa dentro de un método.
// Pero no hace daño tenerlo si lo usas en otros métodos o quieres ser explícito.
// use Carbon\CarbonPeriod; 


class VacacionController extends Controller
{
    /**
     * Display a listing of the resource.
     * (Aún no implementado - sería para una lista general de todos los periodos vacacionales)
     */
     public function index(Request $request)
    {
        $search_nombre_empleado = $request->input('search_nombre_empleado');
        $id_sucursal_filter = $request->input('id_sucursal_filter');
        $status_filter = $request->input('status_filter', 'Alta'); // Por defecto muestra a los 'Alta'

        // La consulta base ya no restringe por antigüedad
        $query = Empleado::query()->whereNotNull('fecha_ingreso');

        // Si se busca por nombre, se ignoran los otros filtros para encontrar al empleado
        if (!empty($search_nombre_empleado)) {
            $query->where('nombre_completo', 'like', '%' . $search_nombre_empleado . '%');
        } else {
            // Si no hay búsqueda por nombre, se aplican los filtros de sucursal y estatus
            if ($status_filter === 'Baja') {
                $query->where('status', 'Baja');
            } elseif ($status_filter === 'Todos') {
                // No se aplica filtro de estatus, muestra ambos
            } else {
                // Por defecto, o si se selecciona explícitamente, muestra solo 'Alta'
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

        // El cálculo de los días se mantiene, ya que maneja correctamente a los empleados
        // con menos de un año de servicio (les asignará 0, lo cual es correcto).
        foreach ($empleados as $empleado) {
            $fechaIngreso = Carbon::parse($empleado->fecha_ingreso);
            $hoy = Carbon::now();

            $anosCompletosServicio = (int) $fechaIngreso->diffInYears($hoy); 
            $empleado->anos_servicio_completados = $anosCompletosServicio;
            
            // Lógica para obtener el total de días restantes (más preciso)
            // Esta lógica considera el saldo de años anteriores y el proporcional del actual.
            $vacacionesDetallado = $empleado->getVacacionesDetallado($hoy);
            $empleado->total_dias_restantes = $vacacionesDetallado['total_a_pagar'];
        }

        $todasLasSucursales = Sucursal::orderBy('nombre_sucursal')->get();

        return view('vacaciones.index', compact(
            'empleados',
            'todasLasSucursales',
            'search_nombre_empleado',
            'id_sucursal_filter',
            'status_filter' // Se pasa el filtro de estatus a la vista
        ));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request) // Asegúrate que Request esté aquí
{
    $empleados = Empleado::where('status', 'Alta')
                        ->orderBy('nombre_completo')
                        ->get();

    // Obtener el id_empleado de la URL si se pasó (para preselección)
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
        // 'dias_tomados' ya no se valida aquí porque lo calcularemos
        'comentarios' => 'nullable|string|max:1000',
    ],[
        // ... tus mensajes de error existentes ...
        // Quita los mensajes para 'dias_tomados' si los tenías
        'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
    ]);

    // Calcular días tomados (total de días calendario, inclusivo)
    $fechaInicio = Carbon::parse($validatedData['fecha_inicio']);
    $fechaFin = Carbon::parse($validatedData['fecha_fin']);

    // diffInDays cuenta días completos entre las fechas. Sumamos 1 para incluir ambos extremos.
    $diasTomadosCalculados = $fechaInicio->diffInDays($fechaFin) + 1; 

    if ($diasTomadosCalculados < 1) {
        // Esto no debería pasar si fecha_fin >= fecha_inicio, pero por si acaso.
        // O podrías lanzar un error de validación aquí con ->after()
        return back()->withErrors(['dias_tomados' => 'El periodo de fechas no es válido o resulta en 0 días.'])->withInput();
    }

    // Añadimos los días calculados a los datos validados para guardarlos
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
            $fechaIngreso = Carbon::parse($empleado->fecha_ingreso);
            $hoy = Carbon::now();
            $anosCompletosServicio = $fechaIngreso->diffInYears($hoy);

            // --- 1. CÁLCULO DE AÑOS COMPLETADOS ---
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
                    'ano_servicio' => $anoDeServicio, // Siempre será un entero
                    'periodo_servicio_label' => $inicioAnoServicio->translatedFormat('d M Y') . ' - ' . $finAnoServicio->translatedFormat('d M Y'),
                    'dias_correspondientes' => $diasCorrespondientes, // El total para ese año
                    'dias_tomados' => $diasTomadosEsteAno,
                    'dias_restantes' => $diasCorrespondientes - $diasTomadosEsteAno,
                    'estado' => 'Completado',
                ];
            }
            
            $saldoDiasAnteriores = $totalDiasGanadosAnosCompletos - $totalDiasTomadosDeAnosCompletos;

            // --- 2. CÁLCULO PROPORCIONAL DEL AÑO EN CURSO (TU LÓGICA) ---
            $anoDeServicioEnCurso = $anosCompletosServicio + 1;
            $diasTotalesAnoEnCurso = $empleado->getDiasVacacionesParaAnoDeServicio($anoDeServicioEnCurso);
            
            $inicioAnoEnCurso = $fechaIngreso->copy()->addYears($anosCompletosServicio);
           $mesesCompletosEnAnoEnCurso = $inicioAnoEnCurso->diffInMonths($hoy); 
            
            $diasPorMes = $diasTotalesAnoEnCurso / 12;
            $diasProporcionalesVac = $diasPorMes * $mesesCompletosEnAnoEnCurso;
            
            $diasTomadosAnoEnCurso = $periodosTomados->where('ano_servicio_correspondiente', $anoDeServicioEnCurso)->sum('dias_tomados');
            $saldoProporcional = $diasProporcionalesVac - $diasTomadosAnoEnCurso;

            // --- 3. AÑADIR LA FILA DEL AÑO EN CURSO AL HISTORIAL ---
            $finAnoServicioEnCurso = $inicioAnoEnCurso->copy()->addYear()->subDay();
            $historialVacacional[] = [
                'ano_servicio' => $anoDeServicioEnCurso,
                'periodo_servicio_label' => $inicioAnoEnCurso->translatedFormat('d M Y') . ' - ' . $finAnoServicioEnCurso->translatedFormat('d M Y'),
                'dias_correspondientes' => round($diasProporcionalesVac, 2), // Solo el proporcional, sin el "(de 16)"
                'dias_tomados' => $diasTomadosAnoEnCurso,
                'dias_restantes' => round($saldoProporcional, 2),
                'estado' => 'En Curso',
            ];
            
            // --- 4. CALCULAR EL TOTAL FINAL ---
            $totalDiasRestantesGeneral = max(0, $saldoDiasAnteriores) + max(0, $saldoProporcional);
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
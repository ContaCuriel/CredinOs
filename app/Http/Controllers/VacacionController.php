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
            $fechaCorte = ($empleado->status === 'Baja' && $empleado->fecha_baja)
                ? Carbon::parse($empleado->fecha_baja)
                : Carbon::now();

            $fechaIngreso = Carbon::parse($empleado->fecha_ingreso);
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
     * Muestra el formulario para crear un nuevo periodo vacacional.
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
        $fechaIngreso = Carbon::parse($empleado->fecha_ingreso);
        
        // 1. Se determina una única "fecha de corte" que será la fuente de verdad para todos los cálculos.
        $fechaCorte = ($empleado->status === 'Baja' && $empleado->fecha_baja)
            ? Carbon::parse($empleado->fecha_baja)
            : Carbon::now();

        // 2. Usamos la robusta función del modelo para obtener los totales correctos. Esto no cambia.
        $vacacionesDetallado = $empleado->getVacacionesDetallado($fechaCorte);
        $totalDiasRestantesGeneral = $vacacionesDetallado['total_a_pagar'];

        // 3. Calculamos los años de servicio completos basados en la fecha de corte.
        $anosCompletosServicio = (int) $fechaIngreso->diffInYears($fechaCorte);
        
        // --- Lógica de Desglose por Año (MÁS CLARA) ---

        // Bucle para todos los años de servicio COMPLETADOS.
        for ($ano = 1; $ano <= $anosCompletosServicio; $ano++) {
            $diasCorrespondientes = $empleado->getDiasVacacionesParaAnoDeServicio($ano);
            $diasTomadosEsteAno = $periodosTomados->where('ano_servicio_correspondiente', $ano)->sum('dias_tomados');
            
            $inicioAnoServicio = $fechaIngreso->copy()->addYears($ano - 1);
            $finAnoServicio = $inicioAnoServicio->copy()->addYear()->subDay();

            $historialVacacional[] = [
                'ano_servicio' => $ano,
                'periodo_servicio_label' => $inicioAnoServicio->translatedFormat('d M Y') . ' - ' . $finAnoServicio->translatedFormat('d M Y'),
                'dias_correspondientes' => $diasCorrespondientes,
                'dias_tomados' => $diasTomadosEsteAno,
                'dias_restantes' => $diasCorrespondientes - $diasTomadosEsteAno,
                'estado' => 'Completado',
            ];
        }
        
        // Lógica para el último periodo (parcial), que aplica tanto a empleados activos como a los de baja.
        $inicioUltimoPeriodo = $fechaIngreso->copy()->addYears($anosCompletosServicio);
        
        // Solo mostramos esta fila si el empleado trabajó al menos un día en este nuevo periodo.
        // Usamos isSameDayOrAfter para incluir el caso de que la baja sea el mismo día del aniversario.
        if ($fechaCorte->isSameDayOrAfter($inicioUltimoPeriodo)) {
            $anoDeServicioFinal = $anosCompletosServicio + 1;
            $finPeriodoLabel = $inicioUltimoPeriodo->copy()->addYear()->subDay();

            // Usamos el valor proporcional que ya calculó el modelo. ¡Esta es la clave!
            $diasProporcionales = $vacacionesDetallado['proporcional_actual'];
            $diasTomadosUltimoAno = $periodosTomados->where('ano_servicio_correspondiente', $anoDeServicioFinal)->sum('dias_tomados');
            
            $historialVacacional[] = [
                'ano_servicio' => $anoDeServicioFinal,
                'periodo_servicio_label' => $inicioUltimoPeriodo->translatedFormat('d M Y') . ' - ' . $finPeriodoLabel->translatedFormat('d M Y'),
                'dias_correspondientes' => round($diasProporcionales, 2),
                'dias_tomados' => $diasTomadosUltimoAno,
                'dias_restantes' => round($diasProporcionales - $diasTomadosUltimoAno, 2),
                'estado' => ($empleado->status === 'Baja') ? 'Finalizado' : 'En Curso',
            ];
        }
    }
    
    return view('vacaciones.historial_empleado', compact('empleado', 'historialVacacional', 'periodosTomados', 'totalDiasRestantesGeneral'));
}
    // ... otros métodos ...
}
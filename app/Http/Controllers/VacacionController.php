<?php

namespace App\Http\Controllers;

use App\Models\PeriodoVacacional;
use App\Models\Empleado;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
            $fechaCorte = ($empleado->status === 'Baja' && $empleado->fecha_baja)
                ? Carbon::parse($empleado->fecha_baja)
                : Carbon::now();

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
     * Devuelve el historial en JSON para la calculadora de finiquitos.
     */
    public function historialJson($id_empleado)
    {
        // Usamos \Log para que si falla, podamos ver el error en el archivo de logs
        try {
            $empleado = Empleado::findOrFail($id_empleado);
            $periodosTomados = PeriodoVacacional::where('id_empleado', $empleado->id_empleado)->get();
            
            $historial = [];
            $fechaIngreso = Carbon::parse($empleado->fecha_ingreso);
            $fechaCorte = ($empleado->status === 'Baja' && $empleado->fecha_baja)
                ? Carbon::parse($empleado->fecha_baja)
                : Carbon::now();

            $anosCompletos = (int) $fechaIngreso->diffInYears($fechaCorte);

            // 1. Años completados
            for ($i = 1; $i <= $anosCompletos; $i++) {
                $diasDerecho = $empleado->getDiasVacacionesParaAnoDeServicio($i);
                $tomados = $periodosTomados->where('ano_servicio_correspondiente', $i)->sum('dias_tomados');
                $inicio = $fechaIngreso->copy()->addYears($i - 1);
                $fin = $fechaIngreso->copy()->addYears($i)->subDay();

                $historial[] = [
                    'ano_servicio' => $i,
                    'periodo' => $inicio->format('d/m/y') . ' - ' . $fin->format('d/m/y'),
                    'dias_restantes' => number_format($diasDerecho - $tomados, 2),
                    'estado' => 'Completado'
                ];
            }

            // 2. Año en curso (Proporcional)
            $anoActual = $anosCompletos + 1;
            $vacDetalle = $empleado->getVacacionesDetallado($fechaCorte);

            $historial[] = [
                'ano_servicio' => $anoActual,
                'periodo' => $fechaIngreso->copy()->addYears($anosCompletos)->format('d/m/y') . ' - Corte',
                'dias_restantes' => number_format($vacDetalle['proporcional_actual'], 2),
                'estado' => 'En Curso'
            ];

            return response()->json($historial);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
        ]);

        $fechaInicio = Carbon::parse($validatedData['fecha_inicio']);
        $fechaFin = Carbon::parse($validatedData['fecha_fin']);
        $diasTomados = $fechaInicio->diffInDays($fechaFin) + 1;

        PeriodoVacacional::create(array_merge($validatedData, ['dias_tomados' => $diasTomados]));

        return redirect()->route('vacaciones.create')->with('success', '¡Periodo registrado!');
    }

    /**
     * Historial HTML (El que ya te funcionaba)
     */
    public function historialPorEmpleado(Request $request, Empleado $empleado)
{
    // 1. Obtenemos los datos base
    $periodosTomados = PeriodoVacacional::where('id_empleado', $empleado->id_empleado)
                        ->orderBy('fecha_inicio', 'asc')
                        ->get();

    $fechaCorte = ($empleado->status === 'Baja' && $empleado->fecha_baja) 
        ? Carbon::parse($empleado->fecha_baja) 
        : Carbon::now();

    $fechaIngreso = Carbon::parse($empleado->fecha_ingreso);
    $anosCompletos = (int) $fechaIngreso->diffInYears($fechaCorte);
    
    $historialVacacional = [];
    $totalDiasGanados = 0;
    $totalDiasTomados = 0;

    // 2. Reconstruimos el array que la vista espera
    for ($i = 1; $i <= $anosCompletos; $i++) {
        $diasDerecho = $empleado->getDiasVacacionesParaAnoDeServicio($i);
        $tomados = $periodosTomados->where('ano_servicio_correspondiente', $i)->sum('dias_tomados');
        
        $inicio = $fechaIngreso->copy()->addYears($i - 1);
        $fin = $fechaIngreso->copy()->addYears($i)->subDay();

        $historialVacacional[] = [
            'ano_servicio' => $i,
            'periodo_servicio_label' => $inicio->translatedFormat('d M Y') . ' - ' . $fin->format('d M Y'),
            'dias_correspondientes' => $diasDerecho,
            'dias_tomados' => $tomados,
            'dias_restantes' => $diasDerecho - $tomados,
            'estado' => 'Completado'
        ];
        
        $totalDiasGanados += $diasDerecho;
        $totalDiasTomados += $tomados;
    }

    // 3. Añadimos el proporcional actual
    $detalle = $empleado->getVacacionesDetallado($fechaCorte);
    $anoCurso = $anosCompletos + 1;
    $tomadosCurso = $periodosTomados->where('ano_servicio_correspondiente', $anoCurso)->sum('dias_tomados');

    $historialVacacional[] = [
        'ano_servicio' => $anoCurso,
        'periodo_servicio_label' => $fechaIngreso->copy()->addYears($anosCompletos)->translatedFormat('d M Y') . ' - Corte',
        'dias_correspondientes' => $detalle['proporcional_actual'],
        'dias_tomados' => $tomadosCurso,
        'dias_restantes' => $detalle['proporcional_actual'] - $tomadosCurso,
        'estado' => 'En Curso'
    ];

    $totalDiasRestantesGeneral = $detalle['total_a_pagar'];

    return view('vacaciones.historial_empleado', compact(
        'empleado', 
        'historialVacacional', 
        'periodosTomados', 
        'totalDiasRestantesGeneral'
    ));
}}
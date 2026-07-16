<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sucursal;
use App\Models\Empleado;
use App\Models\Asistencia;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Barryvdh\DomPDF\Facade\Pdf;

class AsistenciaController extends Controller
{
    /**
     * Muestra la página principal de registro de asistencia (POR PERIODO).
     * Reemplaza a la antigua vista diaria.
     */
    public function index(Request $request)
    {
        $sucursales = Sucursal::where('status', 'Activa')->orderBy('nombre_sucursal')->get();
        $id_sucursal_seleccionada = $request->input('id_sucursal_seleccionada');
        $fechaReferenciaNavegacion = $request->input('fecha_ref', Carbon::today()->toDateString());
        $tipoPeriodo = $request->input('tipo_periodo', 'semana');
        $fechaReferencia = Carbon::parse($fechaReferenciaNavegacion);

        // Lógica de cálculo de periodos (Ya incluye la opción 'dia')
        if ($tipoPeriodo == 'semana') {
            $inicioPeriodo = $fechaReferencia->copy()->startOfWeek(Carbon::MONDAY);
            $finPeriodo = $fechaReferencia->copy()->endOfWeek(Carbon::SUNDAY);
        } elseif ($tipoPeriodo == 'quincena') {
            if ($fechaReferencia->day <= 15) {
                $inicioPeriodo = $fechaReferencia->copy()->startOfMonth();
                $finPeriodo = $fechaReferencia->copy()->startOfMonth()->addDays(14);
            } else {
                $inicioPeriodo = $fechaReferencia->copy()->startOfMonth()->addDays(15);
                $finPeriodo = $fechaReferencia->copy()->endOfMonth();
            }
        } elseif ($tipoPeriodo == 'dia') {
            $inicioPeriodo = $fechaReferencia->copy();
            $finPeriodo = $fechaReferencia->copy();
        } else {
            $inicioPeriodo = $fechaReferencia->copy()->startOfMonth();
            $finPeriodo = $fechaReferencia->copy()->endOfMonth();
        }

        $fechasDelPeriodo = collect();
        foreach (CarbonPeriod::create($inicioPeriodo, $finPeriodo) as $date) {
            $fechasDelPeriodo->push($date->copy());
        }

        $empleadosDeSucursal = collect();
        $asistenciaProcesada = collect();
        $sucursalSeleccionadaNombre = null;

        if ($id_sucursal_seleccionada) {
            if ($id_sucursal_seleccionada === 'todas') {
                $sucursalSeleccionadaNombre = 'TODAS LAS SUCURSALES';
                
                // 🔥 CORRECCIÓN: Ordenamos primero por Sucursal y luego por Nombre
                $empleadosDeSucursal = Empleado::with('sucursal')
                    ->select('empleados.*')
                    ->join('sucursales', 'empleados.id_sucursal', '=', 'sucursales.id_sucursal')
                    ->where('empleados.status', 'Alta')
                    ->orderBy('sucursales.nombre_sucursal', 'asc')
                    ->orderBy('empleados.nombre_completo', 'asc')
                    ->get();
                    
            } else {
                $sucursalActual = Sucursal::find($id_sucursal_seleccionada);
                if ($sucursalActual) $sucursalSeleccionadaNombre = $sucursalActual->nombre_sucursal;
                
                $empleadosDeSucursal = Empleado::with('sucursal')
                    ->where('status', 'Alta')
                    ->where('id_sucursal', $id_sucursal_seleccionada)
                    ->orderBy('nombre_completo', 'asc')
                    ->get();
            }

            $asistencias = Asistencia::whereIn('id_empleado', $empleadosDeSucursal->pluck('id_empleado'))
                                      ->whereBetween('fecha', [$inicioPeriodo->toDateString(), $finPeriodo->toDateString()])->get();
            
            foreach ($empleadosDeSucursal as $emp) {
                $asistenciaProcesada[$emp->id_empleado] = $asistencias->where('id_empleado', $emp->id_empleado)->keyBy(fn($i) => Carbon::parse($i->fecha)->toDateString());
            }
        }
        
        return view('asistencia.index', compact('sucursales', 'id_sucursal_seleccionada', 'sucursalSeleccionadaNombre', 'empleadosDeSucursal', 'asistenciaProcesada', 'fechasDelPeriodo', 'tipoPeriodo', 'fechaReferencia'));
    }

    /**
     * Registra la entrada de un empleado aplicando la regla simple de tolerancia.
     */
    public function registrarEntrada(Request $request)
    {
        $validatedData = $request->validate([
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'id_sucursal_seleccionada' => 'required', 
            'hora_llegada_manual' => 'nullable|date_format:H:i', 
            'fecha_registro' => 'nullable|date' // Añadido por si registran retardo de un día anterior en la tabla
        ]);

        $empleado = Empleado::with('horario')->find($validatedData['id_empleado']);
        $fechaRegistro = $validatedData['fecha_registro'] ?? Carbon::today()->toDateString();

        if (!$empleado || !$empleado->horario) {
            return back()->with('error', 'Error: El empleado no tiene un horario asignado.');
        }

        $datosAsistencia = $this->determinarEstatusAsistencia($empleado, $validatedData['hora_llegada_manual'], $fechaRegistro);

        Asistencia::updateOrCreate(
            ['id_empleado' => $validatedData['id_empleado'], 'fecha' => $fechaRegistro],
            $datosAsistencia
        );

        $mensajeExito = '¡Registro guardado exitosamente! Estatus: ' . $datosAsistencia['status_asistencia'];
        return back()->with('success', $mensajeExito);
    }

    /**
     * Determina el estatus de manera simplificada: Presente o Retardo/Falta.
     */
    private function determinarEstatusAsistencia(Empleado $empleado, ?string $horaManual, ?string $fecha = null): array
    {
        $fechaObjetivo = $fecha ? Carbon::parse($fecha) : Carbon::today();
        $mapaDias = [1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sabado', 7 => 'domingo'];
        $nombreDia = $mapaDias[$fechaObjetivo->dayOfWeekIso];

        $horaLlegadaString = $horaManual ?? Carbon::now()->format('H:i:s');
        $horaLlegada = Carbon::createFromTimeString($horaLlegadaString);

        $esLaborable = $empleado->horario->{$nombreDia};
        $horaEntradaOficialString = $empleado->horario->{$nombreDia.'_entrada'};

        if (!$esLaborable || !$horaEntradaOficialString) {
            return ['hora_llegada' => $horaLlegadaString, 'status_asistencia' => 'Presente', 'notas_incidencia' => 'Día de descanso (Registro manual)'];
        }

        $horario = $empleado->horario;
        $horaEntradaOficial = Carbon::createFromTimeString($horaEntradaOficialString);
        $minutosTarde = $horaLlegada->diffInMinutes($horaEntradaOficial, false);

        // Validar tolerancia simple
        $tolerancia = $horario->aplicar_reglas_avanzadas ? ($horario->tolerancia_minutos ?? 0) : 0;

        if ($minutosTarde <= $tolerancia) {
            return ['hora_llegada' => $horaLlegadaString, 'status_asistencia' => 'Presente', 'notas_incidencia' => null];
        }

        // Si pasó la tolerancia, es un retardo
        return ['hora_llegada' => $horaLlegadaString, 'status_asistencia' => 'Retardo', 'notas_incidencia' => "Retardo de {$minutosTarde} minutos."];
    }

    public function resumenIncidencias(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::today()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', Carbon::today()->toDateString());
        $resumen = $this->obtenerDatosResumen($fechaInicio, $fechaFin);

        return view('asistencia.resumen_incidencias', compact('resumen', 'fechaInicio', 'fechaFin'));
    }

    public function exportarResumenPDF(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $resumen = $this->obtenerDatosResumen($fechaInicio, $fechaFin); 
        $pdf = Pdf::loadView('asistencia.pdf_resumen', compact('resumen', 'fechaInicio', 'fechaFin'));
        return $pdf->download("Resumen_Incidencias_{$fechaInicio}_al_{$fechaFin}.pdf");
    }

    /**
     * Resumen simplificado: Solo cuenta Faltas y Retardos directos.
     */
    private function obtenerDatosResumen($fechaInicio, $fechaFin)
    {
        $empleados = Empleado::with(['sucursal', 'asistencias' => function($query) use ($fechaInicio, $fechaFin) {
            $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }])->where('status', 'Alta')->get();

        $resumen = [];
        foreach ($empleados as $empleado) {
            $faltas = 0;
            $retardos = 0;

            foreach ($empleado->asistencias as $asistencia) {
                if ($asistencia->status_asistencia == 'Falta') { 
                    $faltas++; 
                } elseif ($asistencia->status_asistencia == 'Retardo') { 
                    $retardos++; 
                }
            }

            $resumen[] = [
                'empleado' => $empleado->nombre_completo,
                'sucursal' => $empleado->sucursal->nombre_sucursal ?? 'N/A',
                'faltas_directas' => $faltas,
                'retardos_acumulados' => $retardos,
                'total_incidencias' => $faltas + $retardos
            ];
        }
        return $resumen;
    }
}
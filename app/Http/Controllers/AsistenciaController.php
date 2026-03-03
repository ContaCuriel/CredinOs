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
     * Muestra la página principal de registro de asistencia diaria.
     */
    public function index(Request $request)
    {
        // CORREGIDO: Obtenemos solo las sucursales con estado 'Activa'
        $sucursales = Sucursal::where('status', 'Activa')->orderBy('nombre_sucursal')->get();
        
        $id_sucursal_seleccionada = $request->input('id_sucursal_seleccionada');
        
        $empleadosDeSucursal = collect(); 
        $asistenciasHoy = collect();      
        $sucursalSeleccionadaNombre = null;

        if ($id_sucursal_seleccionada) {
            $sucursalActual = Sucursal::find($id_sucursal_seleccionada);
            if ($sucursalActual) {
                $sucursalSeleccionadaNombre = $sucursalActual->nombre_sucursal;
            }

            $empleadosDeSucursal = Empleado::where('status', 'Alta')
                                          ->where('id_sucursal', $id_sucursal_seleccionada)
                                          ->orderBy('nombre_completo')
                                          ->get();

            if ($empleadosDeSucursal->isNotEmpty()) {
                $fechaHoy = Carbon::today()->toDateString();
                $asistenciasHoy = Asistencia::where('fecha', $fechaHoy)
                                            ->whereIn('id_empleado', $empleadosDeSucursal->pluck('id_empleado'))
                                            ->get()
                                            ->keyBy('id_empleado');
            }
        }
        
        return view('asistencia.index', compact(
            'sucursales', 
            'id_sucursal_seleccionada',
            'sucursalSeleccionadaNombre',
            'empleadosDeSucursal',
            'asistenciasHoy'
        ));
    }

    /**
     * Registra la entrada de un empleado y determina si es retardo.
     */
    public function registrarEntrada(Request $request)
    {
        $validatedData = $request->validate([
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'id_sucursal_seleccionada' => 'required|exists:sucursales,id_sucursal',
            'hora_llegada_manual' => 'nullable|date_format:H:i', 
        ]);

        $empleado = Empleado::with('horario')->find($validatedData['id_empleado']);

        if (!$empleado || !$empleado->horario) {
            return redirect()->route('asistencia.index', ['id_sucursal_seleccionada' => $validatedData['id_sucursal_seleccionada']])
                             ->with('error', 'Error: El empleado no tiene un horario asignado.');
        }

        $datosAsistencia = $this->determinarEstatusAsistencia($empleado, $request->hora_llegada_manual);

        Asistencia::updateOrCreate(
            ['id_empleado' => $validatedData['id_empleado'], 'fecha' => Carbon::today()->toDateString()],
            $datosAsistencia
        );

        $mensajeExito = '¡Entrada registrada a las ' . Carbon::parse($datosAsistencia['hora_llegada'])->format('h:i A') . '! Estatus: ' . $datosAsistencia['status_asistencia'];
        return redirect()->route('asistencia.index', ['id_sucursal_seleccionada' => $validatedData['id_sucursal_seleccionada']])
                         ->with('success', $mensajeExito);
    }

    /**
     * Guarda o actualiza la asistencia desde la vista de periodo.
     */
    public function guardarAsistenciaDia(Request $request)
    {
        $validatedData = $request->validate([
            'id_empleado_asistencia_dia' => 'required|exists:empleados,id_empleado',
            'fecha_asistencia_dia' => 'required|date_format:Y-m-d',
            'status_asistencia_dia' => 'required|string',
            'hora_llegada_dia' => 'nullable|required_if:status_asistencia_dia,Presente|date_format:H:i',
            'notas_incidencia_dia' => 'nullable|required_if:status_asistencia_dia,Incidencia|string|max:1000',
            // ... resto de validaciones de redirección
        ]);

        $datosAsistencia = [];

        if ($validatedData['status_asistencia_dia'] == 'Presente') {
            $empleado = Empleado::with('horario')->find($validatedData['id_empleado_asistencia_dia']);
            if (!$empleado || !$empleado->horario) {
                 return back()->with('error', 'El empleado no tiene un horario asignado.');
            }
            $datosAsistencia = $this->determinarEstatusAsistencia($empleado, $validatedData['hora_llegada_dia'], $validatedData['fecha_asistencia_dia']);
        } else {
            $datosAsistencia = [
                'status_asistencia' => $validatedData['status_asistencia_dia'],
                'hora_llegada' => null, 
                'notas_incidencia' => ($validatedData['status_asistencia_dia'] == 'Incidencia') ? $validatedData['notas_incidencia_dia'] : null,
            ];
        }
        
        Asistencia::updateOrCreate(
            ['id_empleado' => $validatedData['id_empleado_asistencia_dia'], 'fecha' => $validatedData['fecha_asistencia_dia']],
            $datosAsistencia
        );

        // ... lógica de redirección
        return redirect()->route('asistencia.vistaPeriodo', /* ... */)->with('success', '¡Asistencia guardada!');
    }
    
    /**
     * Método privado que contiene la lógica para determinar si es retardo.
     */
    private function determinarEstatusAsistencia(Empleado $empleado, ?string $horaManual, ?string $fecha = null): array
    {
        $fechaObjetivo = $fecha ? Carbon::parse($fecha) : Carbon::today();
        
        // --- INICIO DE LA CORRECCIÓN ---
        // Mapeo de días de la semana (ISO 8601: Lunes=1, Domingo=7) a los nombres de tus columnas.
        $mapaDias = [
            1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves',
            5 => 'viernes', 6 => 'sabado', 7 => 'domingo'
        ];
        $nombreDia = $mapaDias[$fechaObjetivo->dayOfWeekIso];
        // --- FIN DE LA CORRECCIÓN ---

        $horaLlegadaString = $horaManual ?? Carbon::now()->format('H:i:s');
        $horaLlegada = Carbon::createFromTimeString($horaLlegadaString);

        $esLaborable = $empleado->horario->{$nombreDia};
        $horaEntradaOficialString = $empleado->horario->{$nombreDia.'_entrada'};

        if (!$esLaborable || !$horaEntradaOficialString) {
            return ['hora_llegada' => $horaLlegadaString, 'status_asistencia' => 'Presente', 'notas_incidencia' => 'Registro en día no laborable.'];
        }

        $horaEntradaOficial = Carbon::createFromTimeString($horaEntradaOficialString);
        // Tolerancia de 10 minutos. Después de las 8:20:59 ya es retardo para una entrada a las 8:10.
        $horaEntradaConTolerancia = $horaEntradaOficial->copy()->addMinutes(10); 

        if ($horaLlegada->gt($horaEntradaConTolerancia)) {
            $minutosTarde = $horaLlegada->diffInMinutes($horaEntradaOficial);
            return ['hora_llegada' => $horaLlegadaString, 'status_asistencia' => 'Retardo', 'notas_incidencia' => "Retardo de {$minutosTarde} minutos."];
        }

        return ['hora_llegada' => $horaLlegadaString, 'status_asistencia' => 'Presente', 'notas_incidencia' => null];
    }

    /**
     * Registra una falta para un empleado.
     */
    public function registrarFalta(Request $request)
    {
        $validatedData = $request->validate([
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'id_sucursal_seleccionada' => 'required|exists:sucursales,id_sucursal',
        ]);
        Asistencia::updateOrCreate(
            ['id_empleado' => $validatedData['id_empleado'], 'fecha' => Carbon::today()],
            ['hora_llegada' => null, 'status_asistencia' => 'Falta', 'notas_incidencia' => null]
        );
        return redirect()->route('asistencia.index', ['id_sucursal_seleccionada' => $validatedData['id_sucursal_seleccionada']])
                         ->with('success', '¡Falta registrada exitosamente para el empleado!');
    }

    /**
     * Registra una baja del día para un empleado en la asistencia.
     */
    public function registrarBajaDia(Request $request)
    {
        $validatedData = $request->validate([
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'id_sucursal_seleccionada' => 'required|exists:sucursales,id_sucursal',
        ]);
        Asistencia::updateOrCreate(
            ['id_empleado' => $validatedData['id_empleado'], 'fecha' => Carbon::today()],
            ['hora_llegada' => null, 'status_asistencia' => 'Baja_Dia', 'notas_incidencia' => 'Empleado marcado como baja en esta fecha desde el módulo de asistencia.']
        );
        return redirect()->route('asistencia.index', ['id_sucursal_seleccionada' => $validatedData['id_sucursal_seleccionada']])
                         ->with('success', '¡Baja del día registrada exitosamente para el empleado! Por favor, procesar en RH.');
    }

    /**
     * Registra una incidencia para un empleado.
     */
    public function registrarIncidencia(Request $request)
    {
        $validatedData = $request->validate([
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'id_sucursal_seleccionada' => 'required|exists:sucursales,id_sucursal',
            'notas_incidencia_modal' => 'required|string|max:1000',
        ]);
        Asistencia::updateOrCreate(
            ['id_empleado' => $validatedData['id_empleado'], 'fecha' => Carbon::today()],
            ['hora_llegada' => null, 'status_asistencia' => 'Incidencia', 'notas_incidencia' => $validatedData['notas_incidencia_modal']]
        );
        return redirect()->route('asistencia.index', ['id_sucursal_seleccionada' => $validatedData['id_sucursal_seleccionada']])
                         ->with('success', '¡Incidencia registrada exitosamente para el empleado!');
    }

    /**
     * Muestra la vista de asistencia por periodo (semanal, quincenal, mensual).
     */
    public function vistaPeriodo(Request $request)
    {
        // CORREGIDO: Obtenemos solo las sucursales con estado 'Activa'
        $sucursales = Sucursal::where('status', 'Activa')->orderBy('nombre_sucursal')->get();
        
        $id_sucursal_seleccionada = $request->input('id_sucursal_seleccionada');
        
        $empleadosDeSucursal = collect();
        $asistenciaProcesada = collect();
        $sucursalSeleccionadaNombre = null;
        $fechasDelPeriodo = collect();
        $nombrePeriodo = "";
        $fechaReferenciaNavegacion = $request->input('fecha_ref', Carbon::today()->toDateString());
        $tipoPeriodo = $request->input('tipo_periodo', 'semana');
        $fechaReferencia = Carbon::parse($fechaReferenciaNavegacion);

        if ($tipoPeriodo == 'semana') {
            $inicioPeriodo = $fechaReferencia->copy()->startOfWeek(Carbon::MONDAY);
            $finPeriodo = $fechaReferencia->copy()->endOfWeek(Carbon::SUNDAY);
            $nombrePeriodo = "Semana del " . $inicioPeriodo->translatedFormat('d M') . " al " . $finPeriodo->translatedFormat('d M Y');
        } elseif ($tipoPeriodo == 'quincena') {
            if ($fechaReferencia->day <= 15) {
                $inicioPeriodo = $fechaReferencia->copy()->startOfMonth();
                $finPeriodo = $fechaReferencia->copy()->startOfMonth()->addDays(14);
                $nombrePeriodo = "1ra Quincena de " . $inicioPeriodo->translatedFormat('F Y');
            } else {
                $inicioPeriodo = $fechaReferencia->copy()->startOfMonth()->addDays(15);
                $finPeriodo = $fechaReferencia->copy()->endOfMonth();
                $nombrePeriodo = "2da Quincena de " . $inicioPeriodo->translatedFormat('F Y');
            }
        } elseif ($tipoPeriodo == 'mes') {
            $inicioPeriodo = $fechaReferencia->copy()->startOfMonth();
            $finPeriodo = $fechaReferencia->copy()->endOfMonth();
            $nombrePeriodo = ucfirst($inicioPeriodo->translatedFormat('F Y'));
        } else {
            $inicioPeriodo = $fechaReferencia->copy()->startOfWeek(Carbon::MONDAY);
            $finPeriodo = $fechaReferencia->copy()->endOfWeek(Carbon::SUNDAY);
            $nombrePeriodo = "Semana del " . $inicioPeriodo->translatedFormat('d M') . " al " . $finPeriodo->translatedFormat('d M Y');
            $tipoPeriodo = 'semana';
        }

        if (isset($inicioPeriodo) && isset($finPeriodo)) {
            $periodo = CarbonPeriod::create($inicioPeriodo, $finPeriodo);
            foreach ($periodo as $date) {
                $fechasDelPeriodo->push($date->copy());
            }
        }

        if ($id_sucursal_seleccionada) {
            $sucursalActual = Sucursal::find($id_sucursal_seleccionada);
            if ($sucursalActual) {
                $sucursalSeleccionadaNombre = $sucursalActual->nombre_sucursal;
            }
            $empleadosDeSucursal = Empleado::where('status', 'Alta')->where('id_sucursal', $id_sucursal_seleccionada)->orderBy('nombre_completo')->get();
            if ($empleadosDeSucursal->isNotEmpty() && isset($inicioPeriodo) && isset($finPeriodo)) {
                $asistencias = Asistencia::whereIn('id_empleado', $empleadosDeSucursal->pluck('id_empleado'))
                                          ->whereBetween('fecha', [$inicioPeriodo->toDateString(), $finPeriodo->toDateString()])->get();
                foreach ($empleadosDeSucursal as $empleado) {
                    $asistenciaProcesada[$empleado->id_empleado] = $asistencias
                        ->where('id_empleado', $empleado->id_empleado)
                        ->keyBy(function ($item) {
                            return Carbon::parse($item->fecha)->toDateString();
                        });
                }
            }
        }
        
        return view('asistencia.vista_periodo', compact(
            'sucursales', 
            'id_sucursal_seleccionada',
            'sucursalSeleccionadaNombre',
            'empleadosDeSucursal',
            'asistenciaProcesada',
            'fechasDelPeriodo',
            'nombrePeriodo',
            'tipoPeriodo',
            'fechaReferencia'
        ));
    }

    public function resumenIncidencias(Request $request)
{
    $fechaInicio = $request->input('fecha_inicio', Carbon::today()->startOfOfMonth()->toDateString());
    $fechaFin = $request->input('fecha_fin', Carbon::today()->toDateString());

    // Traemos empleados con su horario y sus asistencias en el rango
    $empleados = Empleado::with(['horario', 'sucursal', 'asistencias' => function($query) use ($fechaInicio, $fechaFin) {
        $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
    }])->where('status', 'Alta')->get();

    $resumen = [];

    foreach ($empleados as $empleado) {
        $faltas = 0;
        $retardosMenores = 0;
        $mediosDias = 0;
        $horario = $empleado->horario;

        if (!$horario || !$horario->aplicar_reglas_avanzadas) continue;

        foreach ($empleado->asistencias as $asistencia) {
            if ($asistencia->status_asistencia == 'Falta') {
                $faltas++;
                continue;
            }

            if ($asistencia->hora_llegada) {
                $llegada = Carbon::parse($asistencia->hora_llegada);
                $diaSemana = strtolower(Carbon::parse($asistencia->fecha)->translatedFormat('l'));
                $entradaOficialStr = $horario->{$diaSemana . '_entrada'};
                
                if ($entradaOficialStr) {
                    $entradaOficial = Carbon::createFromTimeString($entradaOficialStr);
                    $minutosTarde = $llegada->diffInMinutes($entradaOficial, false);

                    if ($minutosTarde > 0) {
                        if ($minutosTarde >= $horario->falta_minutos_inicio) {
                            $faltas++;
                        } elseif ($minutosTarde >= $horario->medio_dia_minutos_inicio) {
                            $mediosDias += 0.5;
                        } elseif ($minutosTarde >= $horario->retardo_menor_minutos_inicio) {
                            $retardosMenores++;
                        }
                    }
                }
            }
        }

        // Lógica de 3 retardos = 1 falta
        $faltasPorRetardos = floor($retardosMenores / ($horario->retardos_para_falta ?? 3));
        
        $resumen[] = [
            'empleado' => $empleado->nombre_completo,
            'sucursal' => $empleado->sucursal->nombre_sucursal ?? 'N/A',
            'faltas_directas' => $faltas,
            'retardos_acumulados' => $retardosMenores,
            'faltas_por_retardos' => $faltasPorRetardos,
            'medios_dias' => $mediosDias,
            'total_faltas_final' => $faltas + $faltasPorRetardos + $mediosDias
        ];
    }

    return view('asistencia.resumen_incidencias', compact('resumen', 'fechaInicio', 'fechaFin'));
}

public function exportarResumenPDF(Request $request)
{
    $fechaInicio = $request->input('fecha_inicio');
    $fechaFin = $request->input('fecha_fin');
    
    // Obtenemos los mismos datos que en la vista de resumen
    // (Para no repetir código, lo ideal sería mover la lógica a un método privado)
    $resumen = $this->obtenerDatosResumen($fechaInicio, $fechaFin); 

    $pdf = Pdf::loadView('asistencia.pdf_resumen', compact('resumen', 'fechaInicio', 'fechaFin'));
    
    return $pdf->download("Resumen_Incidencias_{$fechaInicio}_al_{$fechaFin}.pdf");
}

// Método auxiliar para no duplicar código
private function obtenerDatosResumen($fechaInicio, $fechaFin)
{
    $empleados = Empleado::with(['horario', 'sucursal', 'asistencias' => function($query) use ($fechaInicio, $fechaFin) {
        $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
    }])->where('status', 'Alta')->get();

    $resumen = [];
    foreach ($empleados as $empleado) {
        $faltas = 0; $retardosMenores = 0; $mediosDias = 0;
        $horario = $empleado->horario;
        if (!$horario || !$horario->aplicar_reglas_avanzadas) continue;

        foreach ($empleado->asistencias as $asistencia) {
            if ($asistencia->status_asistencia == 'Falta') { $faltas++; continue; }
            if ($asistencia->hora_llegada) {
                $llegada = Carbon::parse($asistencia->hora_llegada);
                $diaSemana = strtolower(Carbon::parse($asistencia->fecha)->translatedFormat('l'));
                $entradaOficialStr = $horario->{$diaSemana . '_entrada'};
                if ($entradaOficialStr) {
                    $entradaOficial = Carbon::createFromTimeString($entradaOficialStr);
                    $minutosTarde = $llegada->diffInMinutes($entradaOficial, false);
                    if ($minutosTarde > 0) {
                        if ($minutosTarde >= $horario->falta_minutos_inicio) { $faltas++; }
                        elseif ($minutosTarde >= $horario->medio_dia_minutos_inicio) { $mediosDias += 0.5; }
                        elseif ($minutosTarde >= $horario->retardo_menor_minutos_inicio) { $retardosMenores++; }
                    }
                }
            }
        }
        $faltasPorRetardos = floor($retardosMenores / ($horario->retardos_para_falta ?? 3));
        $resumen[] = [
            'empleado' => $empleado->nombre_completo,
            'sucursal' => $empleado->sucursal->nombre_sucursal ?? 'N/A',
            'faltas_directas' => $faltas,
            'retardos_acumulados' => $retardosMenores,
            'faltas_por_retardos' => $faltasPorRetardos,
            'medios_dias' => $mediosDias,
            'total_faltas_final' => $faltas + $faltasPorRetardos + $mediosDias
        ];
    }
    return $resumen;
}
}
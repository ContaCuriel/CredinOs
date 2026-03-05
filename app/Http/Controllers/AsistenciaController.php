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
        $sucursales = Sucursal::where('status', 'Activa')->orderBy('nombre_sucursal')->get();
        
        $id_sucursal_seleccionada = $request->input('id_sucursal_seleccionada');
        
        $empleadosDeSucursal = collect(); 
        $asistenciasHoy = collect();      
        $sucursalSeleccionadaNombre = null;

        if ($id_sucursal_seleccionada) {
            // CORRECCIÓN: Si es 'todas', no buscamos ID en la tabla sucursales para evitar error de BigInt
            if ($id_sucursal_seleccionada === 'todas') {
                $sucursalSeleccionadaNombre = 'TODAS LAS SUCURSALES';
                $empleadosDeSucursal = Empleado::with('sucursal', 'horario')->where('status', 'Alta')
                                              ->orderBy('nombre_completo')
                                              ->get();
            } else {
                $sucursalActual = Sucursal::find($id_sucursal_seleccionada);
                if ($sucursalActual) {
                    $sucursalSeleccionadaNombre = $sucursalActual->nombre_sucursal;
                }
                $empleadosDeSucursal = Empleado::with('sucursal', 'horario')->where('status', 'Alta')
                                              ->where('id_sucursal', $id_sucursal_seleccionada)
                                              ->orderBy('nombre_completo')
                                              ->get();
            }

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
     * Registra la entrada de un empleado aplicando reglas dinámicas de horario.
     */
    public function registrarEntrada(Request $request)
    {
        $validatedData = $request->validate([
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'id_sucursal_seleccionada' => 'required', // Quitamos exists para permitir 'todas'
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
     * Determina el estatus basado en las reglas del Horario.
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
            return ['hora_llegada' => $horaLlegadaString, 'status_asistencia' => 'Presente', 'notas_incidencia' => 'Día no laborable'];
        }

        $horario = $empleado->horario;
        $horaEntradaOficial = Carbon::createFromTimeString($horaEntradaOficialString);
        $minutosTarde = $horaLlegada->diffInMinutes($horaEntradaOficial, false);

        // Si no hay reglas avanzadas, aplicamos la tolerancia estándar de 10 min
        if (!$horario->aplicar_reglas_avanzadas) {
            if ($minutosTarde > 10) {
                return ['hora_llegada' => $horaLlegadaString, 'status_asistencia' => 'Retardo', 'notas_incidencia' => "Retardo de {$minutosTarde} min."];
            }
            return ['hora_llegada' => $horaLlegadaString, 'status_asistencia' => 'Presente', 'notas_incidencia' => null];
        }

        // --- LÓGICA CON REGLAS AVANZADAS ---
        if ($minutosTarde <= ($horario->tolerancia_minutos ?? 0)) {
            return ['hora_llegada' => $horaLlegadaString, 'status_asistencia' => 'Presente', 'notas_incidencia' => null];
        }

        if ($minutosTarde >= ($horario->falta_minutos_inicio ?? 31)) {
            return ['hora_llegada' => $horaLlegadaString, 'status_asistencia' => 'Falta', 'notas_incidencia' => "Llegada tardía (Falta)"];
        }

        if ($minutosTarde >= ($horario->medio_dia_minutos_inicio ?? 16)) {
            return ['hora_llegada' => $horaLlegadaString, 'status_asistencia' => 'Retardo', 'notas_incidencia' => "Descuento de medio día"];
        }

        return ['hora_llegada' => $horaLlegadaString, 'status_asistencia' => 'Retardo', 'notas_incidencia' => "Retardo menor"];
    }

    public function vistaPeriodo(Request $request)
    {
        $sucursales = Sucursal::where('status', 'Activa')->orderBy('nombre_sucursal')->get();
        $id_sucursal_seleccionada = $request->input('id_sucursal_seleccionada');
        $fechaReferenciaNavegacion = $request->input('fecha_ref', Carbon::today()->toDateString());
        $tipoPeriodo = $request->input('tipo_periodo', 'semana');
        $fechaReferencia = Carbon::parse($fechaReferenciaNavegacion);

        // Lógica de cálculo de periodos
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
                $empleadosDeSucursal = Empleado::with('sucursal')->where('status', 'Alta')->orderBy('nombre_completo')->get();
            } else {
                $sucursalActual = Sucursal::find($id_sucursal_seleccionada);
                if ($sucursalActual) $sucursalSeleccionadaNombre = $sucursalActual->nombre_sucursal;
                $empleadosDeSucursal = Empleado::with('sucursal')->where('status', 'Alta')->where('id_sucursal', $id_sucursal_seleccionada)->get();
            }

            $asistencias = Asistencia::whereIn('id_empleado', $empleadosDeSucursal->pluck('id_empleado'))
                                      ->whereBetween('fecha', [$inicioPeriodo->toDateString(), $finPeriodo->toDateString()])->get();
            foreach ($empleadosDeSucursal as $emp) {
                $asistenciaProcesada[$emp->id_empleado] = $asistencias->where('id_empleado', $emp->id_empleado)->keyBy(fn($i) => Carbon::parse($i->fecha)->toDateString());
            }
        }
        
        return view('asistencia.vista_periodo', compact('sucursales', 'id_sucursal_seleccionada', 'sucursalSeleccionadaNombre', 'empleadosDeSucursal', 'asistenciaProcesada', 'fechasDelPeriodo', 'tipoPeriodo', 'fechaReferencia'));
    }

    public function resumenIncidencias(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::today()->startOfOfMonth()->toDateString());
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

    private function obtenerDatosResumen($fechaInicio, $fechaFin)
    {
        $empleados = Empleado::with(['horario', 'sucursal', 'asistencias' => function($query) use ($fechaInicio, $fechaFin) {
            $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }])->where('status', 'Alta')->get();

        $resumen = [];
        foreach ($empleados as $empleado) {
            $faltasDirectas = 0; $retardosMenores = 0; $mediosDias = 0;
            $horario = $empleado->horario;
            if (!$horario || !$horario->aplicar_reglas_avanzadas) continue;

            foreach ($empleado->asistencias as $asistencia) {
                if ($asistencia->status_asistencia == 'Falta') { $faltasDirectas++; continue; }
                if ($asistencia->hora_llegada) {
                    $llegada = Carbon::parse($asistencia->hora_llegada);
                    $diaSemana = [1=>'lunes',2=>'martes',3=>'miercoles',4=>'jueves',5=>'viernes',6=>'sabado',7=>'domingo'][Carbon::parse($asistencia->fecha)->dayOfWeekIso];
                    $entradaOficialStr = $horario->{$diaSemana . '_entrada'};
                    if ($entradaOficialStr) {
                        $entradaOficial = Carbon::createFromTimeString($entradaOficialStr);
                        $minutosTarde = $llegada->diffInMinutes($entradaOficial, false);
                        if ($minutosTarde > ($horario->tolerancia_minutos ?? 0)) {
                            if ($minutosTarde >= ($horario->falta_minutos_inicio ?? 31)) { $faltasDirectas++; }
                            elseif ($minutosTarde >= ($horario->medio_dia_minutos_inicio ?? 16)) { $mediosDias += 0.5; }
                            else { $retardosMenores++; }
                        }
                    }
                }
            }
            $faltasPorRetardos = floor($retardosMenores / ($horario->retardos_para_falta ?? 3));
            $resumen[] = [
                'empleado' => $empleado->nombre_completo,
                'sucursal' => $empleado->sucursal->nombre_sucursal ?? 'N/A',
                'faltas_directas' => $faltasDirectas,
                'retardos_acumulados' => $retardosMenores,
                'faltas_por_retardos' => $faltasPorRetardos,
                'medios_dias' => $mediosDias,
                'total_faltas_final' => $faltasDirectas + $faltasPorRetardos + $mediosDias
            ];
        }
        return $resumen;
    }
}
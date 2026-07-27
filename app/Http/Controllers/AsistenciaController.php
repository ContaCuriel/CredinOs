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
     * Muestra la página principal de registro de asistencia (POR PERIODO / DÍA).
     */
    public function index(Request $request)
    {
        $sucursales = Sucursal::where('status', 'Activa')->orderBy('nombre_sucursal')->get();
        $id_sucursal_seleccionada = $request->input('id_sucursal_seleccionada');
        $fechaReferenciaNavegacion = $request->input('fecha_ref', Carbon::today()->toDateString());
        $tipoPeriodo = $request->input('tipo_periodo', 'semana');
        $fechaReferencia = Carbon::parse($fechaReferenciaNavegacion);

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
                $empleadosDeSucursal = Empleado::with('sucursal')
                    ->select('empleados.*')
                    ->join('sucursales', 'empleados.id_sucursal', '=', 'sucursales.id_sucursal')
                    ->where('empleados.status', 'Alta')
                    // 🔥 FILTRO DE VIAJE EN EL TIEMPO: Solo empleados ingresados hasta el fin del periodo consultado
                    ->whereDate('empleados.fecha_ingreso', '<=', $finPeriodo->toDateString()) 
                    ->orderBy('sucursales.nombre_sucursal', 'asc')
                    ->orderBy('empleados.nombre_completo', 'asc')
                    ->get();
            } else {
                $sucursalActual = Sucursal::find($id_sucursal_seleccionada);
                if ($sucursalActual) $sucursalSeleccionadaNombre = $sucursalActual->nombre_sucursal;
                
                $empleadosDeSucursal = Empleado::with('sucursal')
                    ->where('status', 'Alta')
                    ->where('id_sucursal', $id_sucursal_seleccionada)
                    // 🔥 FILTRO DE VIAJE EN EL TIEMPO
                    ->whereDate('fecha_ingreso', '<=', $finPeriodo->toDateString())
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
     * Guardador Universal Inteligente (Celda Directa)
     */
    public function registrarEntrada(Request $request)
    {
        $validatedData = $request->validate([
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'id_sucursal_seleccionada' => 'required', 
            'fecha_registro' => 'required|date',
            'status_asistencia' => 'required|string',
            'hora_llegada_manual' => 'nullable|date_format:H:i', 
            'notas_incidencia' => 'nullable|string'
        ]);

        $empleado = Empleado::with('horario')->find($validatedData['id_empleado']);
        $fechaRegistro = $validatedData['fecha_registro'];

        if (!$empleado || !$empleado->horario) {
            return back()->with('error', 'Error: El empleado no tiene un horario asignado.');
        }

        $status = $validatedData['status_asistencia'];
        $hora = null;
        $notas = $validatedData['notas_incidencia'] ?? null;

        if ($status === 'Presente') {
            if (empty($validatedData['hora_llegada_manual'])) {
                return back()->with('error', 'Debe ingresar la hora de llegada.');
            }
            $calculo = $this->determinarEstatusAsistencia($empleado, $validatedData['hora_llegada_manual'], $fechaRegistro);
            $status = $calculo['status_asistencia'];
            $hora = $calculo['hora_llegada'];
            if (empty($notas)) {
                $notas = $calculo['notas_incidencia'];
            }
        } elseif ($status === 'Incidencia') {
            // 🔥 AHORA PERMITE GUARDAR LA HORA MANUAL TAMBIÉN PARA INCIDENCIAS
            $hora = $validatedData['hora_llegada_manual'] ?? null;
        }

        Asistencia::updateOrCreate(
            ['id_empleado' => $validatedData['id_empleado'], 'fecha' => $fechaRegistro],
            [
                'status_asistencia' => $status,
                'hora_llegada' => $hora,
                'notas_incidencia' => $notas
            ]
        );

        return back()->with('success', 'Registro actualizado correctamente.');
    }

    private function determinarEstatusAsistencia(Empleado $empleado, string $horaManual, string $fecha): array
    {
        $fechaObjetivo = Carbon::parse($fecha);
        $mapaDias = [1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sabado', 7 => 'domingo'];
        $nombreDia = $mapaDias[$fechaObjetivo->dayOfWeekIso];

        $esLaborable = $empleado->horario->{$nombreDia};
        $horaEntradaOficialString = $empleado->horario->{$nombreDia.'_entrada'};

        if (!$esLaborable || !$horaEntradaOficialString) {
            return ['hora_llegada' => $horaManual, 'status_asistencia' => 'Presente', 'notas_incidencia' => 'Día de descanso'];
        }

        $horaLlegada = Carbon::parse($fechaObjetivo->format('Y-m-d') . ' ' . $horaManual);
        $horaEntradaOficial = Carbon::parse($fechaObjetivo->format('Y-m-d') . ' ' . $horaEntradaOficialString);

        if ($horaLlegada->lessThanOrEqualTo($horaEntradaOficial)) {
            $minutosTarde = 0;
        } else {
            $minutosTarde = $horaEntradaOficial->diffInMinutes($horaLlegada);
        }

        $tolerancia = (int) ($empleado->horario->tolerancia_minutos ?? 0);

        if ($minutosTarde <= $tolerancia) {
            return ['hora_llegada' => $horaManual, 'status_asistencia' => 'Presente', 'notas_incidencia' => null];
        }

        return ['hora_llegada' => $horaManual, 'status_asistencia' => 'Retardo', 'notas_incidencia' => "Retardo de {$minutosTarde} min."];
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
     * Panel Interactivo de Pre-Cierre de Asistencias
     */
    public function preCierre(Request $request)
    {
        $sucursales = Sucursal::where('status', 'Activa')->orderBy('nombre_sucursal')->get();
        $id_sucursal = $request->input('id_sucursal');
        $periodo = $request->input('periodo');

        // Generar opciones de periodo (Quincenas) igual que en Lista de Raya
        $opcionesPeriodo = [];
        $fechaActual = Carbon::now();
        for ($i = 0; $i < 6; $i++) {
            $fecha = $fechaActual->copy()->subMonths($i);
            // 1ra Quincena
            $inicioQ1 = $fecha->copy()->startOfMonth();
            $finQ1 = $fecha->copy()->startOfMonth()->addDays(14);
            $opcionesPeriodo[] = [
                'valor' => $inicioQ1->toDateString() . '_' . $finQ1->toDateString(),
                'texto' => '1ra Quincena ' . $inicioQ1->translatedFormat('F Y')
            ];
            // 2da Quincena
            $inicioQ2 = $fecha->copy()->startOfMonth()->addDays(15);
            $finQ2 = $fecha->copy()->endOfMonth();
            $opcionesPeriodo[] = [
                'valor' => $inicioQ2->toDateString() . '_' . $finQ2->toDateString(),
                'texto' => '2da Quincena ' . $inicioQ2->translatedFormat('F Y')
            ];
        }

        $empleadosData = collect();
        $sucursalSeleccionada = null;

        if ($periodo && $id_sucursal) {
            list($fechaInicioStr, $fechaFinStr) = explode('_', $periodo);
            $fechaInicio = Carbon::parse($fechaInicioStr);
            $fechaFin = Carbon::parse($fechaFinStr);

            $sucursalSeleccionada = Sucursal::find($id_sucursal);

            $empleados = Empleado::with(['horario', 'asistencias' => function($q) use ($fechaInicioStr, $fechaFinStr) {
                $q->whereBetween('fecha', [$fechaInicioStr, $fechaFinStr]);
            }])
            ->where('id_sucursal', $id_sucursal)
            ->where('status', 'Alta')
            ->whereDate('fecha_ingreso', '<=', $fechaFinStr) // Filtro de viaje en el tiempo
            ->orderBy('nombre_completo')
            ->get();

            foreach ($empleados as $empleado) {
                $horario = $empleado->horario;
                if (!$horario) continue; // Si no tiene horario, lo saltamos por seguridad

                // Variables para acumular las incidencias crudas de la quincena
                $retardos_crudos = 0;
                $medios_dias_crudos = 0;
                $faltas_directas_crudas = 0;
                $dias_castigo_acumulados = 0; // Por multiplicador

                $detalles_dias = []; // Para saber qué pasó cada día y poder "perdonarlo"

                // Evaluamos día por día
                for ($date = $fechaInicio->copy(); $date->lte($fechaFin); $date->addDay()) {
                    $fechaStr = $date->toDateString();
                    $mapaDias = [1=>'lunes', 2=>'martes', 3=>'miercoles', 4=>'jueves', 5=>'viernes', 6=>'sabado', 7=>'domingo'];
                    $nombreDia = $mapaDias[$date->dayOfWeekIso];
                    
                    $esLaborable = $horario->{$nombreDia};
                    if (!$esLaborable) continue; // Si descansa, no hay penalización

                    $asistencia = $empleado->asistencias->where('fecha', $fechaStr)->first();

                    if (!$asistencia || $asistencia->status_asistencia == 'Falta') {
                        // FALTA DIRECTA O NO VINO
                        $faltas_directas_crudas++;
                        $multiplicador = 1;
                        if ($horario->aplica_castigo_multiplicador) {
                            if (in_array($date->dayOfWeekIso, [1, 5])) { // Lunes o Viernes
                                $multiplicador = $horario->multiplicador_lunes_viernes ?? 1;
                            } else {
                                $multiplicador = $horario->multiplicador_dias_regulares ?? 1;
                            }
                        }
                        $dias_castigo_acumulados += $multiplicador;
                        $detalles_dias[] = ['fecha' => $fechaStr, 'tipo' => 'falta', 'penalizacion' => $multiplicador, 'perdonado' => false];
                    
                    } elseif ($asistencia->status_asistencia == 'Retardo') {
                        // EVALUAR RETARDO VS MEDIO DÍA usando el nuevo motor de Horarios
                        $horaOficial = Carbon::parse($fechaStr . ' ' . $horario->{$nombreDia.'_entrada'});
                        $horaLlegada = Carbon::parse($asistencia->hora_llegada);
                        $minutosTarde = $horaOficial->diffInMinutes($horaLlegada);

                        if ($horario->aplica_medio_dia && $minutosTarde > ($horario->minutos_limite_retardo ?? 15) && $minutosTarde <= ($horario->minutos_limite_medio_dia ?? 30)) {
                            $medios_dias_crudos++;
                            $detalles_dias[] = ['fecha' => $fechaStr, 'tipo' => 'medio_dia', 'penalizacion' => 0.5, 'perdonado' => false];
                        } 
                        elseif ($horario->aplica_medio_dia && $minutosTarde > ($horario->minutos_limite_medio_dia ?? 30)) {
                            // Se pasó del medio día, es Falta Directa
                            $faltas_directas_crudas++;
                            $multiplicador = 1;
                            if ($horario->aplica_castigo_multiplicador) {
                                $multiplicador = in_array($date->dayOfWeekIso, [1, 5]) ? ($horario->multiplicador_lunes_viernes ?? 1) : ($horario->multiplicador_dias_regulares ?? 1);
                            }
                            $dias_castigo_acumulados += $multiplicador;
                            $detalles_dias[] = ['fecha' => $fechaStr, 'tipo' => 'falta_por_retardo_extremo', 'penalizacion' => $multiplicador, 'perdonado' => false];
                        }
                        else {
                            // Es un retardo normal
                            $retardos_crudos++;
                            $detalles_dias[] = ['fecha' => $fechaStr, 'tipo' => 'retardo', 'penalizacion' => 0, 'perdonado' => false];
                        }
                    }
                }

                // Cálculo de faltas por acumulación de retardos
                $regla_retardos = $horario->retardos_por_falta ?? 0;
                $faltas_por_retardos = 0;
                if ($regla_retardos > 0) {
                    $faltas_por_retardos = floor($retardos_crudos / $regla_retardos);
                }

                $total_dias_descuento = $dias_castigo_acumulados + ($medios_dias_crudos * 0.5) + $faltas_por_retardos;

                $empleadosData->push([
                    'id_empleado' => $empleado->id_empleado,
                    'nombre' => $empleado->nombre_completo,
                    'puesto' => $empleado->puesto->nombre_puesto ?? 'General',
                    'regla_retardos' => $regla_retardos,
                    'retardos_crudos' => $retardos_crudos,
                    'medios_dias_crudos' => $medios_dias_crudos,
                    'faltas_directas' => $faltas_directas_crudas,
                    'faltas_por_retardos' => $faltas_por_retardos,
                    'total_dias_descuento_inicial' => $total_dias_descuento,
                    'detalles' => $detalles_dias
                ]);
            }
        }

        return view('asistencia.pre_cierre', compact('sucursales', 'opcionesPeriodo', 'empleadosData', 'periodo', 'id_sucursal', 'sucursalSeleccionada'));
    }

    private function obtenerDatosResumen($fechaInicio, $fechaFin)
    {
        $empleados = Empleado::with(['sucursal', 'asistencias' => function($query) use ($fechaInicio, $fechaFin) {
            $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }])->where('status', 'Alta')->get();

        $resumen = [];
        foreach ($empleados as $empleado) {
            $faltas = 0; $retardos = 0;
            foreach ($empleado->asistencias as $asistencia) {
                if ($asistencia->status_asistencia == 'Falta') { $faltas++; } 
                elseif ($asistencia->status_asistencia == 'Retardo') { $retardos++; }
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
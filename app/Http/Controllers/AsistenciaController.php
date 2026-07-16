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
     * Guardador Universal (Registro Directo en Celda)
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

        // Si se eligió capturar hora, evaluamos si es Presente o Retardo
        if ($status === 'Presente') {
            if (empty($validatedData['hora_llegada_manual'])) {
                return back()->with('error', 'Debe ingresar la hora de llegada.');
            }
            $calculo = $this->determinarEstatusAsistencia($empleado, $validatedData['hora_llegada_manual'], $fechaRegistro);
            $status = $calculo['status_asistencia'];
            $hora = $calculo['hora_llegada'];
            // Si no escribieron nota manual, ponemos la generada por el sistema (ej. Retardo de 15 min)
            if (empty($notas)) {
                $notas = $calculo['notas_incidencia'];
            }
        }

        Asistencia::updateOrCreate(
            ['id_empleado' => $validatedData['id_empleado'], 'fecha' => $fechaRegistro],
            [
                'status_asistencia' => $status,
                'hora_llegada' => $hora,
                'notas_incidencia' => $notas
            ]
        );

        return back()->with('success', 'Registro guardado exitosamente: ' . $status);
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

        // CÁLCULO INFALIBLE: 
        // Si llegó antes o a la hora en punto, los minutos tarde son 0.
        if ($horaLlegada->lessThanOrEqualTo($horaEntradaOficial)) {
            $minutosTarde = 0;
        } else {
            // Si llegó después, calculamos la diferencia absoluta (positiva) en minutos.
            $minutosTarde = $horaEntradaOficial->diffInMinutes($horaLlegada);
        }

        // Leemos directamente los minutos de tolerancia de la base de datos forzándolo a Entero
        $tolerancia = (int) ($empleado->horario->tolerancia_minutos ?? 0);

        if ($minutosTarde <= $tolerancia) {
            return ['hora_llegada' => $horaManual, 'status_asistencia' => 'Presente', 'notas_incidencia' => null];
        }

        return ['hora_llegada' => $horaManual, 'status_asistencia' => 'Retardo', 'notas_incidencia' => "Retardo de {$minutosTarde} minutos."];
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
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Caja;
use App\Models\CorteCaja;
use Carbon\Carbon;

class CajaController extends Controller
{
    /**
     * Pantalla principal: Muestra las cajas y si el usuario tiene un turno abierto.
     */
    public function index()
    {
        // 1. Revisamos si el admin ya tiene un turno abierto
        $turnoActivo = CorteCaja::where('usuario_id', auth()->id())
                                ->where('estatus', 'abierto')
                                ->with('caja')
                                ->first();

        if ($turnoActivo) {
            return redirect()->route('cajas.operacion');
        }

        // 2. 🔥 AUTOMATIZACIÓN: 1 CAJA POR SUCURSAL 🔥
        // Traemos todas las sucursales (tu Global Scope ya filtra y solo trae las Activas)
        $sucursalesActivas = \App\Models\Sucursal::all();

        foreach ($sucursalesActivas as $sucursal) {
            // firstOrCreate busca si la sucursal ya tiene caja. Si no la tiene, la crea al instante.
            \App\Models\Caja::firstOrCreate(
                ['sucursal_id' => $sucursal->id_sucursal],
                [
                    'nombre' => 'Caja Principal',
                    'estatus' => 'cerrada',
                    'saldo_actual' => 0.00
                ]
            );
        }

        // 3. Ahora sí, mandamos a la vista las cajas asegurándonos que su sucursal siga activa
        $cajas = \App\Models\Caja::whereHas('sucursal')->with('sucursal')->get(); 

        return view('cajas.index', compact('cajas'));
    }

    /**
     * Abre un nuevo turno para el cajero.
     */
    public function abrirTurno(Request $request)
    {
        $request->validate([
            'caja_id' => 'required|exists:cajas,id',
            'saldo_inicial' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $caja = Caja::findOrFail($request->caja_id);

            if ($caja->estatus === 'abierta') {
                return back()->with('error', 'La caja ya está abierta. Si hubo un error de sesión, cierra el turno anterior primero.');
            }

            // 1. Crear el corte de caja (Turno)
            $corte = CorteCaja::create([
                'caja_id' => $caja->id,
                'usuario_id' => auth()->id(),
                'fecha_apertura' => now(),
                'saldo_inicial' => $request->saldo_inicial,
                'saldo_teorico' => $request->saldo_inicial,
                'estatus' => 'abierto'
            ]);

            // 2. Actualizar el estatus
            $caja->update([
                'estatus' => 'abierta',
                'saldo_actual' => $request->saldo_inicial
            ]);

            DB::commit();

            return redirect()->route('cajas.operacion')->with('success', 'Turno abierto exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al abrir la caja: ' . $e->getMessage());
        }
    }

    /**
     * Pantalla de operación (Donde estará el buscador, alertas de mora y cobro).
     * La construiremos en el siguiente paso.
     */
    public function operacion()
    {
        $turnoActivo = \App\Models\CorteCaja::where('usuario_id', auth()->id())
                                ->where('estatus', 'abierto')
                                ->with('caja')
                                ->firstOrFail();

        // 🔥 1. MOTOR GLOBAL: Actualizar todas las moras ANTES de cargar los datos al cajero
        $this->actualizarMoratoriosGlobales();

        // 2. Traer los créditos ya con las multas frescas y reales
        $creditos = \App\Models\Credito::with([
            'cliente', 
            'grupo', 
            'integrantes',
            'amortizaciones' => function($q) {
                $q->where('estatus', '!=', 'pagado')->orderBy('numero_cuota', 'asc');
            }
        ])->where('estatus', 'desembolsado')->get();

        return view('cajas.operacion', compact('turnoActivo', 'creditos'));
    }

    /**
     * Procesa el cobro de una cuota
     */
    public function cobrar(Request $request)
    {
        $request->validate([
            'credito_id' => 'required|exists:creditos,id',
            'monto_recibido' => 'required|numeric|min:0',
            'metodo_pago' => 'required|string',
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $turnoActivo = \App\Models\CorteCaja::where('usuario_id', auth()->id())
                                    ->where('estatus', 'abierto')
                                    ->with('caja')
                                    ->firstOrFail();

            $credito = \App\Models\Credito::with('integrantes')->findOrFail($request->credito_id);
            $cuota = \App\Models\Amortizacion::where('credito_id', $credito->id)
                                             ->where('estatus', '!=', 'pagado')
                                             ->orderBy('numero_cuota', 'asc')
                                             ->first();

            if (!$cuota) {
                return back()->with('error', 'Este crédito no tiene cuotas pendientes.');
            }

            // 1. Registro de Transacción a la Caja General
            if ($request->monto_recibido > 0) {
                \App\Models\TransaccionCaja::create([
                    'corte_caja_id' => $turnoActivo->id,
                    'tipo' => 'ingreso',
                    'concepto' => 'Pago Cuota #' . $cuota->numero_cuota . ' - Folio: ' . $credito->folio,
                    'monto' => $request->monto_recibido,
                    'metodo_pago' => $request->metodo_pago,
                    'referencia_id' => $cuota->id,
                    'descripcion' => $request->referencia_pago ?? null
                ]);

                if ($request->metodo_pago === 'efectivo') {
                    $turnoActivo->ingresos += $request->monto_recibido;
                    $turnoActivo->saldo_teorico += $request->monto_recibido;
                    $turnoActivo->save();

                    $turnoActivo->caja->saldo_actual += $request->monto_recibido;
                    $turnoActivo->caja->save();
                }
            }

            // 2. GUARDAR DETALLE INDIVIDUAL (Solo si se activó el Switch de Desglose)
            if ($request->has('pagos_individuales')) {
                $totalAprobado = $credito->monto_aprobado > 0 ? $credito->monto_aprobado : ($credito->monto_solicitado ?: 1);

                foreach ($request->pagos_individuales as $cliente_id => $monto_pagado) {
                    $integrante = $credito->integrantes->where('id_cliente', $cliente_id)->first();
                    
                    $montoEsperado = 0;
                    if ($integrante) {
                        // Calcula cuánto debió pagar en base a su porcentaje del crédito
                        $montoEsperado = ($integrante->pivot->monto_individual / $totalAprobado) * $cuota->total_cuota;
                    }

                    // Lo insertamos en la tabla de amortizacion_detalles que creamos ayer
                    \Illuminate\Support\Facades\DB::table('amortizacion_detalles')->insert([
                        'amortizacion_id' => $cuota->id,
                        'cliente_id' => $cliente_id,
                        'monto_esperado' => round($montoEsperado, 2),
                        'monto_pagado' => $monto_pagado,
                        'estatus' => $monto_pagado >= ($montoEsperado - 0.5) ? 'pagado' : ($monto_pagado > 0 ? 'parcial' : 'atrasado'),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            // 3. Actualizar la Cuota Global
            $cuota->monto_pagado += $request->monto_recibido;
            if ($cuota->monto_pagado >= ($cuota->total_cuota - 0.5)) { // Tolerancia de 50 centavos
                $cuota->estatus = 'pagado';
            } else {
                $cuota->estatus = 'parcial';
            }
            $cuota->fecha_pago_real = now();
            $cuota->save();

            // 4. Liquidar el crédito si pagó la última cuota
            $cuotasPendientes = \App\Models\Amortizacion::where('credito_id', $credito->id)
                                                        ->where('estatus', '!=', 'pagado')
                                                        ->count();
            if ($cuotasPendientes === 0) {
                $credito->estatus = 'liquidado';
                $credito->save();
            }

            \Illuminate\Support\Facades\DB::commit();
            return back()->with('success', '¡Pago de $' . number_format($request->monto_recibido, 2) . ' aplicado correctamente!');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Error al procesar el pago: ' . $e->getMessage());
        }
    }

    // --- MOTOR INTELIGENTE DE MORATORIOS GLOBAL ---
    private function actualizarMoratoriosGlobales()
    {
        $hoy = \Carbon\Carbon::now()->toDateString();
        $horaActual = \Carbon\Carbon::now()->format('H:i'); 

        // Traemos TODAS las cuotas atrasadas o que vencen hoy y no están pagadas de la BD
        $cuotasAtrasadas = \Illuminate\Support\Facades\DB::table('credito_amortizaciones')
            ->join('creditos', 'credito_amortizaciones.credito_id', '=', 'creditos.id')
            ->select(
                'credito_amortizaciones.id', 
                'credito_amortizaciones.fecha_pago',
                'credito_amortizaciones.moratorios_generados',
                'creditos.monto_aprobado',
                'creditos.monto_solicitado'
            )
            ->where('credito_amortizaciones.estatus', '!=', 'pagado')
            ->where('credito_amortizaciones.fecha_pago', '<=', $hoy)
            ->where('creditos.estatus', 'desembolsado') // Solo créditos activos
            ->get();

        foreach($cuotasAtrasadas as $cuota) {
            $multa = 0;
            $fechaVencimiento = \Carbon\Carbon::parse($cuota->fecha_pago)->toDateString();
            $valorCredito = $cuota->monto_aprobado > 0 ? $cuota->monto_aprobado : ($cuota->monto_solicitado ?? 0);

            if ($fechaVencimiento == $hoy) {
                if ($horaActual >= '10:00') {
                    $multa = 500.00;
                }
            } elseif ($fechaVencimiento < $hoy) {
                $multa = 500.00 + ($valorCredito * 0.10); 
            }

            // Solo hace el UPDATE si la multa es diferente a la que ya estaba guardada.
            // Esto hace que sea absurdamente rápido (milisegundos) aunque tengas miles de créditos.
            if ($multa > 0 && round($multa, 2) != $cuota->moratorios_generados) {
                \Illuminate\Support\Facades\DB::table('credito_amortizaciones')
                    ->where('id', $cuota->id)
                    ->update(['moratorios_generados' => round($multa, 2)]);
            }
        }
    }
}
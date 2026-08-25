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
            'monto_cuota' => 'required|numeric|min:0',
            'monto_mora' => 'nullable|numeric|min:0',
            'metodo_pago' => 'required|string',
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $turnoActivo = \App\Models\CorteCaja::where('usuario_id', auth()->id())
                                    ->where('estatus', 'abierto')
                                    ->with('caja')
                                    ->firstOrFail();

            $credito = \App\Models\Credito::with('integrantes')->findOrFail($request->credito_id);
            
            // 🔥 CORRECCIÓN: Usamos tu modelo CreditoAmortizacion real
            $cuota = \App\Models\CreditoAmortizacion::where('credito_id', $credito->id)
                                             ->where('estatus', '!=', 'pagado')
                                             ->orderBy('numero_cuota', 'asc')
                                             ->first();

            if (!$cuota) {
                return back()->with('error', 'Este crédito no tiene cuotas pendientes.');
            }

            $monto_mora = $request->monto_mora ?? 0;
            $monto_total_ingreso = $request->monto_cuota + $monto_mora;

            if ($monto_total_ingreso <= 0) {
                return back()->with('error', 'Debes ingresar un monto mayor a cero para cobrar.');
            }

            $tickets_generados = []; // Aquí guardaremos los IDs de los tickets

            // 1. TICKET / TRANSACCIÓN 1: PAGO DE LA CUOTA NORMAL
            if ($request->monto_cuota > 0) {
                $t1 = \App\Models\TransaccionCaja::create([
                    'corte_caja_id' => $turnoActivo->id,
                    'tipo' => 'ingreso',
                    'concepto' => 'Pago Cuota #' . $cuota->numero_cuota . ' - Folio: ' . $credito->folio,
                    'monto' => $request->monto_cuota,
                    'metodo_pago' => $request->metodo_pago,
                    'referencia_id' => $cuota->id,
                    'descripcion' => $request->referencia_pago ?? null
                ]);
                $tickets_generados[] = $t1->id;
            }

            // 2. TICKET / TRANSACCIÓN 2: PAGO DE MORATORIOS
            if ($monto_mora > 0) {
                $t2 = \App\Models\TransaccionCaja::create([
                    'corte_caja_id' => $turnoActivo->id,
                    'tipo' => 'ingreso',
                    'concepto' => 'Pago Multa/Mora Cuota #' . $cuota->numero_cuota . ' - Folio: ' . $credito->folio,
                    'monto' => $monto_mora,
                    'metodo_pago' => $request->metodo_pago,
                    'referencia_id' => $cuota->id,
                    'descripcion' => 'Penalización por atraso'
                ]);
                $tickets_generados[] = $t2->id;
            }

            // 3. ACTUALIZAR SALDOS DE LA CAJA FÍSICA
            if ($request->metodo_pago === 'efectivo') {
                $turnoActivo->ingresos += $monto_total_ingreso;
                $turnoActivo->saldo_teorico += $monto_total_ingreso;
                $turnoActivo->save();

                $turnoActivo->caja->saldo_actual += $monto_total_ingreso;
                $turnoActivo->caja->save();
            }

            // 4. ACTUALIZAR LA CUOTA
            $cuota->monto_pagado += $request->monto_cuota;
            
            \Illuminate\Support\Facades\DB::table('credito_amortizaciones')
                ->where('id', $cuota->id)
                ->increment('moratorios_pagados', $monto_mora);

            if ($cuota->monto_pagado >= ($cuota->total_cuota - 0.5)) { 
                $cuota->estatus = 'pagado';
            } else {
                $cuota->estatus = 'parcial';
            }
            $cuota->fecha_pago_real = now();
            $cuota->save();

            // 🔥 CORRECCIÓN: Usamos tu modelo CreditoAmortizacion real
            $cuotasPendientes = \App\Models\CreditoAmortizacion::where('credito_id', $credito->id)
                                                        ->where('estatus', '!=', 'pagado')
                                                        ->count();
            if ($cuotasPendientes === 0) {
                $credito->estatus = 'liquidado';
                $credito->save();
            }

            \Illuminate\Support\Facades\DB::commit();
            
            // RETORNAMOS CON LOS TICKETS GENERADOS
            return back()->with('success', '¡Se registraron los cobros exitosamente! Total recibido: $' . number_format($monto_total_ingreso, 2))
                         ->with('tickets_generados', $tickets_generados);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Error al procesar el pago: ' . $e->getMessage());
        }
    }

    // --- MOTOR INTELIGENTE DE MORATORIOS (CONECTADO A PRODUCTOS) ---
    private function actualizarMoratoriosGlobales()
    {
        $hoy = \Carbon\Carbon::now()->toDateString();
        $horaActual = \Carbon\Carbon::now()->format('H:i'); 

        // Unimos 3 tablas de golpe (Amortizaciones + Creditos + Productos) para leer las reglas dinámicas
        $cuotasAtrasadas = \Illuminate\Support\Facades\DB::table('credito_amortizaciones')
            ->join('creditos', 'credito_amortizaciones.credito_id', '=', 'creditos.id')
            ->join('productos_credito', 'creditos.producto_id', '=', 'productos_credito.id')
            ->select(
                'credito_amortizaciones.id', 
                'credito_amortizaciones.fecha_pago',
                'credito_amortizaciones.total_cuota',
                'credito_amortizaciones.moratorios_generados',
                'creditos.monto_aprobado',
                'creditos.monto_solicitado',
                'productos_credito.hora_maxima_pago',
                'productos_credito.multa_valor',
                'productos_credito.mora_valor',
                'productos_credito.mora_calculo',
                'productos_credito.politica_acumulacion'
            )
            ->where('credito_amortizaciones.estatus', '!=', 'pagado')
            ->where('credito_amortizaciones.fecha_pago', '<=', $hoy)
            ->where('creditos.estatus', 'desembolsado') 
            ->get();

        foreach($cuotasAtrasadas as $cuota) {
            $multa = 0;
            $fechaVencimiento = \Carbon\Carbon::parse($cuota->fecha_pago)->toDateString();
            $valorCredito = $cuota->monto_aprobado > 0 ? $cuota->monto_aprobado : ($cuota->monto_solicitado ?? 0);

            // 1. Leemos las reglas del producto (Con valores por defecto de seguridad)
            $horaLimite = $cuota->hora_maxima_pago ? \Carbon\Carbon::parse($cuota->hora_maxima_pago)->format('H:i') : '10:00';
            $valorMulta = floatval($cuota->multa_valor ?? 500); 
            $porcentajeMora = floatval($cuota->mora_valor ?? 10) / 100; // Ej. 10 -> 0.10

            // 2. Evaluamos HOY
            if ($fechaVencimiento == $hoy) {
                if ($horaActual >= $horaLimite) {
                    $multa = $valorMulta; // Aplica la multa fija (Ej. $500)
                }
            } 
            // 3. Evaluamos DÍAS DE ATRASO
            elseif ($fechaVencimiento < $hoy) {
                $moraCalculada = 0;

                // ¿La mora es sobre el crédito total o sobre la cuota?
                if ($cuota->mora_calculo == 'porcentaje_cuota') {
                    $moraCalculada = $cuota->total_cuota * $porcentajeMora;
                } else {
                    // Por defecto: Porcentaje sobre el valor total del crédito
                    $moraCalculada = $valorCredito * $porcentajeMora;
                }

                // ¿Se acumula a la multa de $500 o la reemplaza?
                if ($cuota->politica_acumulacion == 'suma_multa') {
                    $multa = $valorMulta + $moraCalculada; 
                } else {
                    // Reemplaza multa (Solo cobra el 10%)
                    $multa = $moraCalculada;
                }
            }

            // 4. Guardamos si hubo cambios
            if ($multa > 0 && round($multa, 2) != $cuota->moratorios_generados) {
                \Illuminate\Support\Facades\DB::table('credito_amortizaciones')
                    ->where('id', $cuota->id)
                    ->update(['moratorios_generados' => round($multa, 2)]);
            }
        }
    }

    public function imprimirTicket($id)
    {
        // 1. Traemos la transacción sencilla (sin forzar relaciones en el modelo)
        $transaccion = \App\Models\TransaccionCaja::findOrFail($id);
        
        // 2. Buscamos el corte de caja manualmente para saber quién cobró y en qué sucursal
        $corte = \App\Models\CorteCaja::with(['usuario', 'caja.sucursal'])->find($transaccion->corte_caja_id);
        
        // Inyectamos el corte a la transacción para que tu vista ticket.blade.php lo lea perfecto
        $transaccion->setRelation('corteCaja', $corte);

        // 3. Traemos la cuota (Usando tu modelo CORRECTO: CreditoAmortizacion)
        $cuota = \App\Models\CreditoAmortizacion::with(['credito.cliente', 'credito.grupo', 'credito.patron'])->find($transaccion->referencia_id);
        $credito = $cuota->credito ?? null;

        // 4. Convertir Logo a Base64
        $logo_base64 = null;
        if ($credito && $credito->patron && $credito->patron->logo_path) {
            $path = public_path('storage/' . $credito->patron->logo_path);
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $logo_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        $letras = $this->convertirALetras($transaccion->monto);

        $data = [
            'transaccion' => $transaccion,
            'cuota' => $cuota,
            'credito' => $credito,
            'logo_base64' => $logo_base64,
            'letras' => $letras
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('cajas.pdf.ticket', $data);
        
        // Formato Ticket Térmico 80mm de ancho (alto dinámico)
        $pdf->setPaper([0, 0, 226.77, 600], 'portrait'); 

        return $pdf->stream('Ticket_' . $transaccion->id . '.pdf');
    }

    // --- FUNCIÓN PARA CONVERTIR NÚMEROS A LETRAS EN EL TICKET ---
    private function convertirALetras($numero)
    {
        $numero = floor($numero);
        if ($numero == 0) return 'CERO';
        
        $unidades = ['', 'UN ', 'DOS ', 'TRES ', 'CUATRO ', 'CINCO ', 'SEIS ', 'SIETE ', 'OCHO ', 'NUEVE ', 'DIEZ ', 'ONCE ', 'DOCE ', 'TRECE ', 'CATORCE ', 'QUINCE ', 'DIECISEIS ', 'DIECISIETE ', 'DIECIOCHO ', 'DIECINUEVE ', 'VEINTE '];
        $decenas = ['', 'DIEZ ', 'VEINTI', 'TREINTA ', 'CUARENTA ', 'CINCUENTA ', 'SESENTA ', 'SETENTA ', 'OCHENTA ', 'NOVENTA '];
        $centenas = ['', 'CIENTO ', 'DOSCIENTOS ', 'TRESCIENTOS ', 'CUATROCIENTOS ', 'QUINIENTOS ', 'SEISCIENTOS ', 'SETECIENTOS ', 'OCHOCIENTOS ', 'NOVECIENTOS '];

        $convertir = function ($n) use (&$convertir, $unidades, $decenas, $centenas) {
            if ($n <= 20) return $unidades[$n];
            if ($n < 100) return $decenas[floor($n / 10)] . ($n % 10 != 0 ? ($n < 30 ? '' : 'Y ') . $unidades[$n % 10] : '');
            if ($n == 100) return 'CIEN ';
            if ($n < 1000) return $centenas[floor($n / 100)] . $convertir($n % 100);
            if ($n < 2000) return 'MIL ' . $convertir($n % 1000);
            if ($n < 1000000) return $convertir(floor($n / 1000)) . 'MIL ' . $convertir($n % 1000);
            if ($n == 1000000) return 'UN MILLON ' . $convertir($n % 1000000);
            if ($n < 1000000000) return $convertir(floor($n / 1000000)) . 'MILLONES ' . $convertir($n % 1000000);
            return '';
        };

        return trim($convertir($numero)) . ' PESOS 00/100 M.N.';
    }
}
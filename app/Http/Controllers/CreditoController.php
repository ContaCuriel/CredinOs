<?php

namespace App\Http\Controllers;

use App\Models\Credito;
use App\Models\Cliente;
use App\Models\Grupo;
use App\Models\ProductoCredito;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sucursal;
use Barryvdh\DomPDF\Facade\Pdf;
use NumberFormatter;

class CreditoController extends Controller
{
    public function index()
    {
        // Traemos los créditos con sus relaciones para no saturar la base de datos
        $creditos = Credito::with(['producto', 'asesor', 'cliente', 'grupo', 'integrantes'])
                            ->orderBy('created_at', 'desc')
                            ->paginate(15);
                            
        return view('creditos.index', compact('creditos'));
    }

    public function show($id)
    {
        // Traemos el crédito con todas sus relaciones
        $credito = Credito::with(['producto', 'asesor', 'cliente', 'grupo', 'integrantes', 'cuentasDesembolso', 'garantia'])->findOrFail($id);
        
        // Traemos los catálogos para el Modal de Aprobación
        $patrones = \App\Models\Patron::orderBy('nombre_comercial')->get();
        $cuentasEmpresa = \App\Models\CuentaBancaria::where('activa', true)->get();
        $sucursales = \App\Models\Sucursal::orderBy('nombre_sucursal')->get(); // O como se llame la columna en tu BD
        
        return view('creditos.show', compact('credito', 'patrones', 'cuentasEmpresa', 'sucursales'));
    }

    public function create()
    {
        $productos = ProductoCredito::where('activo', true)->orderBy('nombre')->get();
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get(); // <-- NUEVO
        
        $asesores = Empleado::with('sucursal')
            ->whereHas('puesto', function ($query) {
                $query->where('nombre_puesto', 'ILIKE', 'ASESOR%')
                      ->orWhere('nombre_puesto', 'ILIKE', 'GERENTE%');
            })
            ->whereIn('status', ['Alta', 'ALTA', 'alta', 'Activo', 'ACTIVO'])
            ->orderBy('nombre_completo')->get();

        // Manda las sucursales en el compact
        return view('creditos.create', compact('productos', 'asesores', 'sucursales')); 
    }

    public function store(Request $request)
    {
        // 1. VALIDACIÓN MAESTRA
        $validated = $request->validate([
            'sucursal_id'      => 'required|exists:sucursales,id_sucursal',
            'producto_id'      => 'required|exists:productos_credito,id',
            'asesor_id'        => 'required|exists:empleados,id_empleado',
            'monto_solicitado' => 'required|numeric|min:1',
            'nombre_credito'   => 'nullable|string|max:255',
            'nombre_grupo'     => 'nullable|string|max:255', 
            'clientes'         => 'required|array|min:1',
            'clientes.*.id'    => 'required|exists:clientes,id_cliente',
            'clientes.*.monto' => 'required|numeric|min:0',
            'lider_id'         => 'nullable|exists:clientes,id_cliente',
            'cuentas'             => 'required|array|min:1',
            'cuentas.*.banco'     => 'required|string|max:100',
            'cuentas.*.titular'   => 'required|string|max:255',
            'cuentas.*.cuenta'    => 'required|string|max:50',
            // Validaciones de la garantía (es un array que puede o no venir)
            'garantia'         => 'nullable|array',
            'garantia.tipo'    => 'nullable|in:vehiculo,propiedad',
        ]);

        try {
            DB::beginTransaction();

            $producto = ProductoCredito::findOrFail($validated['producto_id']);
            
            $grupo_id = null;
            $cliente_id_individual = null;

            $nombre_grupo = $validated['nombre_grupo'] ?? null;
            $nombre_credito = $validated['nombre_credito'] ?? null;
            $lider_id_seleccionado = $validated['lider_id'] ?? null;

            // 2. ¿ES GRUPAL O INDIVIDUAL?
            if ($producto->tipo_credito == 'grupal') {
                if (empty($nombre_grupo)) {
                    throw new \Exception("El nombre del grupo es obligatorio para este tipo de producto.");
                }
                $grupo = Grupo::create(['nombre_grupo' => $nombre_grupo]);
                $grupo_id = $grupo->id;
            } else {
                $cliente_id_individual = $validated['clientes'][0]['id'];
            }

            // 3. CREAMOS EL CRÉDITO
            $credito = Credito::create([
                'folio' => 'CR-' . strtoupper(uniqid()), 
                'nombre_credito' => $nombre_credito,
                'sucursal_id' => $validated['sucursal_id'],
                'cliente_id' => $cliente_id_individual,
                'grupo_id' => $grupo_id,
                'producto_id' => $producto->id,
                'monto_solicitado' => $validated['monto_solicitado'],
                'plazo_solicitado' => $producto->plazo_maximo, 
                'tasa_interes_aplicada' => $producto->tasa_interes,
                'comision_apertura_aplicada' => $producto->cobro_comision_apertura,
                'estatus' => 'solicitado',
                'fecha_solicitud' => now(),
                'asesor_id' => $validated['asesor_id'],
            ]);

            // 4. ATAMOS A LOS CLIENTES
            $syncData = [];
            foreach ($validated['clientes'] as $cliente) {
                $es_lider = ($lider_id_seleccionado == $cliente['id']) ? true : false;
                if ($producto->tipo_credito == 'individual') {
                    $es_lider = true;
                }
                $syncData[$cliente['id']] = [
                    'es_lider' => $es_lider,
                    'monto_individual' => $cliente['monto']
                ];
            }
            $credito->integrantes()->sync($syncData);

            // 5. GUARDAMOS LAS CUENTAS BANCARIAS
            foreach ($validated['cuentas'] as $cuenta) {
                $credito->cuentasDesembolso()->create([
                    'banco' => $cuenta['banco'],
                    'titular' => $cuenta['titular'],
                    'numero_cuenta' => $cuenta['cuenta'],
                ]);
            }


            // 6. GUARDAMOS LA GARANTÍA (SI APLICA)
            if ($producto->requiere_garantia && !empty($request->input('garantia'))) {
                $garantiaData = $request->input('garantia');
                
                $credito->garantia()->create([
                    'tipo_garantia' => $garantiaData['tipo'],
                    'vehiculo_documento' => $garantiaData['vehiculo_documento'] ?? null,
                    'vehiculo_tipo' => $garantiaData['vehiculo_tipo'] ?? null,
                    'vehiculo_marca' => $garantiaData['vehiculo_marca'] ?? null,
                    'vehiculo_modelo' => $garantiaData['vehiculo_modelo'] ?? null,
                    'vehiculo_anio' => $garantiaData['vehiculo_anio'] ?? null,
                    'vehiculo_motor' => $garantiaData['vehiculo_motor'] ?? null,
                    'vehiculo_color' => $garantiaData['vehiculo_color'] ?? null,
                    'vehiculo_serie' => $garantiaData['vehiculo_serie'] ?? null,
                    
                    // --- AQUÍ ENTRA LO NUEVO DEL SEGURO ---
                    'tiene_seguro' => $garantiaData['tiene_seguro'] ?? false,
                    'vigencia_seguro' => $garantiaData['vigencia_seguro'] ?? null,
                    // --------------------------------------

                    'propiedad_documento' => $garantiaData['propiedad_documento'] ?? null,
                    'propiedad_ubicacion' => $garantiaData['propiedad_ubicacion'] ?? null,
                    'propiedad_medidas' => $garantiaData['propiedad_medidas'] ?? null,
                    'propiedad_superficie' => $garantiaData['propiedad_superficie'] ?? null,
                    'estatus_resguardo' => 'En Bóveda Sucursal',
                ]);
            }

            DB::commit();
            return redirect()->route('creditos.index')->with('success', '¡Solicitud de crédito creada y enviada a autorización exitosamente!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function search(Request $request)
    {
        $term = $request->term;
        
        if (!$term) {
            return response()->json([]);
        }

        // Buscamos coincidencias en nombre o apellidos
        $clientes = Cliente::where('nombre', 'ILIKE', "%$term%")
                    ->orWhere('apellido_paterno', 'ILIKE', "%$term%")
                    ->orWhere('apellido_materno', 'ILIKE', "%$term%")
                    ->take(15)
                    ->get();

        $results = [];
        foreach ($clientes as $cliente) {
            $results[] = [
                'id' => $cliente->id_cliente, // Asegúrate de que sea tu llave primaria correcta
                'text' => trim($cliente->nombre . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno)
            ];
        }

        return response()->json($results);
    }

    public function destroy($id)
    {
        try {
            $credito = Credito::findOrFail($id);
            
            // Solo se pueden borrar si no han sido aprobados ni desembolsados
            if ($credito->estatus != 'solicitado') {
                return back()->with('error', 'No puedes eliminar un crédito que ya fue procesado.');
            }

            DB::beginTransaction();
            
            // Limpiamos las tablas relacionadas
            $credito->integrantes()->detach(); // Quita a los clientes de la tabla pivote
            $credito->cuentasDesembolso()->delete(); // Borra las cuentas
            if($credito->garantia) {
                $credito->garantia()->delete(); // Borra la garantía
            }
            
            // Borramos el crédito
            $credito->delete();
            
            DB::commit();
            return redirect()->route('creditos.index')->with('success', '¡Solicitud eliminada y limpiada correctamente!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    public function aprobar(Request $request, $id)
    {
        $request->validate([
            'monto_aprobado' => 'required|numeric|min:1',
            'comision_apertura' => 'required|numeric|min:0',
            'retencion_seguro' => 'required|numeric|min:0',
            'patron_id' => 'required|exists:patrones,id_patron',
            'fecha_primer_pago' => 'required|date',
            'cuentas_pago' => 'nullable|array',
            'sucursales_pago' => 'nullable|array',
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            // Nos traemos el crédito y su producto para leer las reglas financieras
            $credito = \App\Models\Credito::with('producto')->findOrFail($id);

            if ($credito->estatus != 'solicitado') {
                return back()->with('error', 'El crédito ya no se encuentra en estatus de solicitud.');
            }

            // 1. Actualizamos el Crédito
            $credito->update([
                'monto_aprobado' => $request->monto_aprobado,
                'plazo_aprobado' => $credito->plazo_solicitado, 
                'comision_apertura_aplicada' => $request->comision_apertura,
                'retencion_seguro_aplicada' => $request->retencion_seguro,
                'patron_id' => $request->patron_id,
                'fecha_primer_pago' => $request->fecha_primer_pago,
                'fecha_aprobacion' => now(),
                'estatus' => 'aprobado',
            ]);

            // 2. Guardamos Lugares de Pago autorizados
            if ($request->has('cuentas_pago')) {
                $credito->cuentasParaPago()->sync($request->cuentas_pago);
            }
            if ($request->has('sucursales_pago')) {
                $credito->sucursalesParaPago()->sync($request->sucursales_pago);
            }

            // =========================================================
            // 3. 🔥 MOTOR MATEMÁTICO INTELIGENTE DE AMORTIZACIÓN 🔥
            // =========================================================
            $monto = $credito->monto_aprobado;
            $plazo = $credito->plazo_aprobado;
            $tasaPeriodo = $credito->tasa_interes_aplicada / 100; // Ej: 5% se vuelve 0.05
            
            // Leemos cómo quiere el producto que le cobremos
            $tipoTasa = strtolower($credito->producto->tipo_tasa); // 'global', 'saldos_insolutos', 'francesa'
            $frecuencia = strtolower($credito->producto->frecuencia_pago);
            
            $saldoRestante = $monto;
            $fechaPago = \Carbon\Carbon::parse($request->fecha_primer_pago);

            // Si es sistema Francés (Pagos fijos iguales), calculamos la cuota maestra
            $cuotaFrancesa = 0;
            if ($tipoTasa == 'francesa' || $tipoTasa == 'pagos_fijos') {
                if ($tasaPeriodo > 0) {
                    $cuotaFrancesa = $monto * ($tasaPeriodo * pow(1 + $tasaPeriodo, $plazo)) / (pow(1 + $tasaPeriodo, $plazo) - 1);
                } else {
                    $cuotaFrancesa = $monto / $plazo;
                }
            }

            // Iterador para crear cada fila de la tabla
            for ($i = 1; $i <= $plazo; $i++) {
                
                $capitalCuota = 0;
                $interesCuota = 0;

                // 🧮 APLICAMOS LA FÓRMULA SEGÚN EL TIPO DE TASA
                switch ($tipoTasa) {
                    case 'saldos_insolutos': // Capital Fijo, Interés va bajando
                        $capitalCuota = $monto / $plazo;
                        $interesCuota = $saldoRestante * $tasaPeriodo;
                        break;
                        
                    case 'francesa':
                    case 'pagos_fijos': // Interés va bajando, Capital va subiendo (Pago Total siempre es igual)
                        $interesCuota = $saldoRestante * $tasaPeriodo;
                        $capitalCuota = $cuotaFrancesa - $interesCuota;
                        break;

                    case 'global':
                    default: // Interés Fijo sobre el monto original inicial (El más común en microfinancieras)
                        $capitalCuota = $monto / $plazo;
                        $interesCuota = $monto * $tasaPeriodo;
                        break;
                }

                // Ajuste exacto de centavos en la última cuota para evitar saldos de $0.01
                if ($i == $plazo) {
                    $capitalCuota = $saldoRestante; 
                }

                // Redondeos y cálculos finales de la cuota
                $capitalCuota = round($capitalCuota, 2);
                $interesCuota = round($interesCuota, 2);
                $ivaCuota = round($interesCuota * 0.16, 2); // 16% de IVA sobre el interés (Ajusta si ustedes no cobran IVA)
                $totalCuota = $capitalCuota + $interesCuota + $ivaCuota;

                // Guardamos en la base de datos
                \App\Models\CreditoAmortizacion::create([
                    'credito_id' => $credito->id,
                    'numero_cuota' => $i,
                    'fecha_pago' => $fechaPago->format('Y-m-d'),
                    'saldo_inicial' => $saldoRestante,
                    'capital' => $capitalCuota,
                    'interes' => $interesCuota,
                    'iva' => $ivaCuota,
                    'total_cuota' => $totalCuota,
                    'saldo_final' => round($saldoRestante - $capitalCuota, 2),
                    'estatus' => 'pendiente'
                ]);

                // Actualizamos el saldo restante para la siguiente iteración
                $saldoRestante = round($saldoRestante - $capitalCuota, 2);

                // 🗓️ AVANZAMOS LA FECHA DE PAGO
                if ($frecuencia == 'semanal') {
                    $fechaPago->addWeek();
                } elseif ($frecuencia == 'catorcenal') {
                    $fechaPago->addWeeks(2);
                } elseif ($frecuencia == 'quincenal') {
                    $fechaPago->addDays(15); // Avanza 15 días exactos
                } elseif ($frecuencia == 'mensual') {
                    $fechaPago->addMonth();
                }
            }

            \Illuminate\Support\Facades\DB::commit();
            return redirect()->route('creditos.show', $credito->id)->with('success', '¡Crédito dictaminado y APROBADO exitosamente! Se han generado las cuotas según el producto seleccionado.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Error al aprobar el crédito: ' . $e->getMessage());
        }
    }

    public function imprimirContrato($id)
    {
        $credito = \App\Models\Credito::with(['cliente', 'garantia', 'producto', 'patron', 'amortizaciones'])->findOrFail($id);

        if (!$credito->garantia) {
            return back()->with('error', 'El crédito no tiene una garantía registrada para generar el contrato.');
        }

        // Obtener la cuota regular (usamos la primera como referencia)
        $primeraCuota = $credito->amortizaciones->first();
        $cuota_monto = $primeraCuota ? $primeraCuota->total_cuota : 0;

        // Obtener el día de la semana de pago (Ej. "Lunes")
        $dia_pago = \Carbon\Carbon::parse($credito->fecha_primer_pago)->locale('es')->isoFormat('dddd');
        
        // Obtener el nombre de la sucursal autorizada (tomamos la primera o ponemos una por defecto)
        $sucursal_nombre = $credito->sucursalesParaPago->first()->nombre_sucursal ?? 'TEXCOCO';

        // Convertidor de números a letras (Requiere la extensión 'intl' de PHP activada)
        $formatter = new NumberFormatter("es", NumberFormatter::SPELLOUT);

        $data = [
            'credito' => $credito,
            'cuota_monto' => $cuota_monto,
            'dia_pago' => ucfirst($dia_pago),
            'sucursal_nombre' => strtoupper($sucursal_nombre),
            
            // Textos en letras
            'letras_monto_aprobado' => strtoupper($formatter->format($credito->monto_aprobado)),
            'letras_comision' => strtoupper($formatter->format($credito->comision_apertura_aplicada)),
            'letras_cuota' => strtoupper($formatter->format($cuota_monto)),
            'letras_multa' => strtoupper($formatter->format($credito->producto->multa_valor ?? 500)),
            'letras_mora' => strtoupper($formatter->format($credito->producto->mora_valor ?? 1000)),
        ];

        $pdf = Pdf::loadView('creditos.pdf.contrato', $data);
        
        // return $pdf->download(...) para descargar directo, o stream(...) para previsualizar en el navegador
        return $pdf->stream('Contrato_' . $credito->folio . '.pdf');
    }
}
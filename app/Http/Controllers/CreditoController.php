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
    public function index(Request $request)
{
    $query = Credito::with(['cliente', 'grupo', 'asesor', 'sucursal'])
                ->whereIn('estatus', ['solicitado', 'aprobado']);

    // Filtro por Nombre (Grupo, Cliente o Titular)
    if ($request->filled('nombre')) {
        $nombre = $request->nombre;
        $query->where(function($q) use ($nombre) {
            $q->where('nombre_credito', 'like', "%{$nombre}%")
              ->orWhereHas('grupo', function($qG) use ($nombre) {
                  $qG->where('nombre_grupo', 'like', "%{$nombre}%");
              })
              ->orWhereHas('cliente', function($qC) use ($nombre) {
                  $qC->where('nombre', 'like', "%{$nombre}%")
                     ->orWhere('apellido_paterno', 'like', "%{$nombre}%");
              })
              ->orWhereHas('integrantes', function($qI) use ($nombre) {
                  $qI->where('nombre', 'like', "%{$nombre}%")
                     ->orWhere('apellido_paterno', 'like', "%{$nombre}%");
              });
        });
    }

    // Filtro por Sucursal
    if ($request->filled('sucursal_id')) {
        $query->where('sucursal_id', $request->sucursal_id);
    }

    // Filtro por Fecha de Solicitud
    if ($request->filled('fecha')) {
        $query->whereDate('fecha_solicitud', $request->fecha);
    }

    $creditos = $query->orderBy('created_at', 'desc')->paginate(15);
    $sucursales = \App\Models\Sucursal::all();

    return view('creditos.index', compact('creditos', 'sucursales'));
}

    public function show($id)
    {
        // Traemos el crédito con todas sus relaciones (agregamos 'amortizaciones' para el Estado de Cuenta)
        $credito = Credito::with(['producto', 'asesor', 'cliente', 'grupo', 'integrantes', 'cuentasDesembolso', 'garantia', 'amortizaciones'])->findOrFail($id);
        
        // 🔥 NUEVA LÓGICA: Si ya se desembolsó (activo), mostramos el Estado de Cuenta
        if ($credito->estatus === 'desembolsado') {
            return view('creditos.estado_cuenta', compact('credito'));
        }

        // Si sigue en solicitud o aprobado, traemos los catálogos para el Modal de Aprobación
        $patrones = \App\Models\Patron::orderBy('nombre_comercial')->get();
        $cuentasEmpresa = \App\Models\CuentaBancaria::where('activa', true)->get();
        $sucursales = \App\Models\Sucursal::orderBy('nombre_sucursal')->get();
        
        // Retornamos la vista original de revisión/autorización
        return view('creditos.show', compact('credito', 'patrones', 'cuentasEmpresa', 'sucursales'));
    }

    public function create()
    {
        $productos = ProductoCredito::where('activo', true)->orderBy('nombre')->get();
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        
        $asesores = Empleado::with('sucursal')
            ->whereHas('puesto', function ($query) {
                $query->where('nombre_puesto', 'ILIKE', 'ASESOR%')
                      ->orWhere('nombre_puesto', 'ILIKE', 'GERENTE%');
            })
            ->whereIn('status', ['Alta', 'ALTA', 'alta', 'Activo', 'ACTIVO'])
            ->orderBy('nombre_completo')->get();

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
            'fecha_desembolso' => 'required|date',
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
                'fecha_desembolso' => $validated['fecha_desembolso'],
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
                    
                    'tiene_seguro' => $garantiaData['tiene_seguro'] ?? false,
                    'vigencia_seguro' => $garantiaData['vigencia_seguro'] ?? null,

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

        $clientes = Cliente::where('nombre', 'ILIKE', "%$term%")
                    ->orWhere('apellido_paterno', 'ILIKE', "%$term%")
                    ->orWhere('apellido_materno', 'ILIKE', "%$term%")
                    ->take(15)
                    ->get();

        $results = [];
        foreach ($clientes as $cliente) {
            $results[] = [
                'id' => $cliente->id_cliente, 
                'text' => trim($cliente->nombre . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno)
            ];
        }

        return response()->json($results);
    }

    public function destroy($id)
    {
        try {
            $credito = Credito::findOrFail($id);
            
            if ($credito->estatus != 'solicitado') {
                return back()->with('error', 'No puedes eliminar un crédito que ya fue procesado.');
            }

            DB::beginTransaction();
            
            $credito->integrantes()->detach(); 
            $credito->cuentasDesembolso()->delete(); 
            if($credito->garantia) {
                $credito->garantia()->delete(); 
            }
            
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
            'fecha_desembolso' => 'required|date',
            'fecha_primer_pago' => 'required|date|after_or_equal:fecha_desembolso',
            'cuentas_pago' => 'nullable|array',
            'sucursales_pago' => 'nullable|array',
            'descuenta_primer_pago' => 'nullable|boolean', // <-- NUEVO CAMPO
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $credito = \App\Models\Credito::with('producto')->findOrFail($id);

            if ($credito->estatus != 'solicitado') {
                return back()->with('error', 'El crédito ya no se encuentra en estatus de solicitud.');
            }

            $descuenta_primer_pago = $request->boolean('descuenta_primer_pago');

            // 1. Actualizamos el Crédito
            $credito->update([
                'monto_aprobado' => $request->monto_aprobado,
                'plazo_aprobado' => $credito->plazo_solicitado, 
                'comision_apertura_aplicada' => $request->comision_apertura,
                'retencion_seguro_aplicada' => $request->retencion_seguro,
                'patron_id' => $request->patron_id,
                'fecha_desembolso' => $request->fecha_desembolso,
                'fecha_primer_pago' => $request->fecha_primer_pago,
                'fecha_aprobacion' => now(),
                'estatus' => 'aprobado',
                'descuenta_primer_pago' => $descuenta_primer_pago, 
            ]);

            // 2. Guardamos Lugares de Pago autorizados
            if ($request->has('cuentas_pago')) {
                $credito->cuentasParaPago()->sync($request->cuentas_pago);
            }
            if ($request->has('sucursales_pago')) {
                $credito->sucursalesParaPago()->sync($request->sucursales_pago);
            }

            // =========================================================
            // 3. 🔥 MOTOR MATEMÁTICO INTELIGENTE (SIN CENTAVOS) 🔥
            // =========================================================
            $monto = $credito->monto_aprobado;
            $plazo = $credito->plazo_aprobado;
            $tasaPeriodo = $credito->tasa_interes_aplicada / 100; 
            
            $tipoTasa = strtolower($credito->producto->tipo_tasa); 
            $frecuencia = strtolower($credito->producto->frecuencia_pago);
            
            $saldoRestante = $monto;
            $fechaPago = \Carbon\Carbon::parse($request->fecha_primer_pago);

            $cuotaBaseTeorica = 0;
            $interesPeriodoFijo = 0;

            if ($tipoTasa == 'francesa' || $tipoTasa == 'pagos_fijos') {
                if ($tasaPeriodo > 0) {
                    $cuotaBaseTeorica = $monto * ($tasaPeriodo * pow(1 + $tasaPeriodo, $plazo)) / (pow(1 + $tasaPeriodo, $plazo) - 1);
                } else {
                    $cuotaBaseTeorica = $monto / $plazo;
                }
            } elseif ($tipoTasa == 'global' || empty($tipoTasa)) {
                $interesTotal = $monto * $tasaPeriodo;
                $interesPeriodoFijo = $interesTotal / $plazo;
                $cuotaBaseTeorica = ($monto / $plazo) + $interesPeriodoFijo;
            }

            $cuotaRedondeada = round($cuotaBaseTeorica, 0);

            for ($i = 1; $i <= $plazo; $i++) {
                
                $capitalCuota = 0;
                $interesCuota = 0;
                $ivaCuota = 0; 
                $totalCuota = 0;

                if ($tipoTasa == 'saldos_insolutos') {
                    $capitalBase = $monto / $plazo;
                    $interesBase = $saldoRestante * $tasaPeriodo;
                    $totalCuota = round($capitalBase + $interesBase, 0); 
                    $interesCuota = $interesBase; 
                    $capitalCuota = $totalCuota - $interesCuota; 
                } 
                elseif ($tipoTasa == 'francesa' || $tipoTasa == 'pagos_fijos') {
                    $totalCuota = $cuotaRedondeada; 
                    $interesCuota = $saldoRestante * $tasaPeriodo;
                    $capitalCuota = $totalCuota - $interesCuota;
                } 
                else {
                    $totalCuota = $cuotaRedondeada;
                    $interesCuota = $interesPeriodoFijo;
                    $capitalCuota = $totalCuota - $interesCuota;
                }

                if ($i == $plazo) {
                    $capitalCuota = $saldoRestante; 
                    $totalCuota = round($capitalCuota + $interesCuota + $ivaCuota, 0);
                    $interesCuota = $totalCuota - $capitalCuota - $ivaCuota;
                }

                $capitalCuota = round($capitalCuota, 2);
                $interesCuota = round($interesCuota, 2);

                // 🔥 MAGIA: Si se marca el checkbox y es la cuota 1, nace "pagada"
                $estatusCuota = ($i == 1 && $descuenta_primer_pago) ? 'pagado' : 'pendiente';
                
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
                    'estatus' => $estatusCuota
                ]);

                $saldoRestante = round($saldoRestante - $capitalCuota, 2);

                if ($frecuencia == 'semanal') {
                    $fechaPago->addWeek();
                } elseif ($frecuencia == 'catorcenal') {
                    $fechaPago->addWeeks(2);
                } elseif ($frecuencia == 'quincenal') {
                    $fechaPago->addDays(15); 
                } elseif ($frecuencia == 'mensual') {
                    $fechaPago->addMonth();
                }
            }

            \Illuminate\Support\Facades\DB::commit();
            return redirect()->route('creditos.show', $credito->id)->with('success', '¡Crédito APROBADO! Se han generado las cuotas en pesos cerrados.');

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

        $primeraCuota = $credito->amortizaciones->first();
        $cuota_monto = $primeraCuota ? $primeraCuota->total_cuota : 0;

        $dia_pago = \Carbon\Carbon::parse($credito->fecha_primer_pago)->locale('es')->isoFormat('dddd');
        $sucursal_nombre = $credito->sucursalesParaPago->first()->nombre_sucursal ?? 'TEXCOCO';

        // Calculamos el dinero real de la comisión y la mora
        $monto_comision_calculado = ($credito->monto_aprobado * $credito->comision_apertura_aplicada) / 100;
        
        // Sacamos el valor real de la mora basado en el porcentaje del producto (asumiendo 10% si está vacío)
        $mora_porcentaje = $credito->producto->mora_valor ?? 10; 
        $monto_mora_calculado = ($credito->monto_aprobado * $mora_porcentaje) / 100;

        $data = [
            'credito' => $credito,
            'cuota_monto' => $cuota_monto,
            'dia_pago' => ucfirst($dia_pago),
            'sucursal_nombre' => strtoupper($sucursal_nombre),
            'monto_comision_calculado' => $monto_comision_calculado,
            'monto_mora_calculado' => $monto_mora_calculado, 
            
            // Usamos la función interna segura
            'letras_monto_aprobado' => $this->convertirALetras($credito->monto_aprobado),
            'letras_comision' => $this->convertirALetras($monto_comision_calculado),
            'letras_cuota' => $this->convertirALetras($cuota_monto),
            'letras_multa' => $this->convertirALetras($credito->producto->multa_valor ?? 500),
            'letras_mora' => $this->convertirALetras($monto_mora_calculado), 
        ];

        $pdf = Pdf::loadView('creditos.pdf.contrato', $data);
        return $pdf->stream('Contrato_' . $credito->folio . '.pdf');
    }

    // --- FUNCIÓN NATIVA Y SEGURA PARA NÚMEROS A LETRAS ---
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

        return trim($convertir($numero)) . ' PESOS';
    }

    public function imprimirTabla($id)
    {
        $credito = \App\Models\Credito::with(['cliente', 'grupo', 'producto', 'asesor', 'sucursal', 'patron', 'amortizaciones'])->findOrFail($id);

        if ($credito->amortizaciones->isEmpty()) {
            return back()->with('error', 'El crédito no tiene una tabla de amortización generada.');
        }

        $primeraCuota = $credito->amortizaciones->first();
        $ultimaCuota = $credito->amortizaciones->last();

        // 🖼️ Convertir Logo a Base64 para que DomPDF lo lea sin errores (USANDO logo_path)
        $logo_base64 = null;
        if ($credito->patron && $credito->patron->logo_path) {
            $path = public_path('storage/' . $credito->patron->logo_path);
            
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $logo_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        $data = [
            'credito' => $credito,
            'monto_pago' => $primeraCuota->total_cuota,
            'fecha_fin' => $ultimaCuota->fecha_pago,
            'logo_base64' => $logo_base64,
        ];

        $pdf = Pdf::loadView('creditos.pdf.tabla', $data);
        return $pdf->stream('Control_Pagos_' . $credito->folio . '.pdf');
    }

    public function imprimirActa($id)
    {
        // Traemos también las amortizaciones para leer la primera cuota
        $credito = \App\Models\Credito::with(['cliente', 'asesor', 'patron', 'amortizaciones'])->findOrFail($id);

        $logo_base64 = null;
        if ($credito->patron && $credito->patron->logo_path) {
            $path = public_path('storage/' . $credito->patron->logo_path);
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $logo_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        $monto_credito = $credito->monto_aprobado;
        $comision = ($monto_credito * $credito->comision_apertura_aplicada) / 100;
        $retencion_seguro = $credito->retencion_seguro_aplicada ?? 0;
        
        // 🔥 MAGIA: Extraemos el monto del pago adelantado si el campo es true
        $pago_adelantado = 0;
        if ($credito->descuenta_primer_pago && $credito->amortizaciones->isNotEmpty()) {
            $pago_adelantado = $credito->amortizaciones->first()->total_cuota;
        }

        // Sumamos todas las deducciones, incluyendo el primer pago si aplica
        $total_deducciones = $comision + $retencion_seguro + $pago_adelantado;
        $total_fondear = $monto_credito - $total_deducciones;

        $calle = $credito->cliente->calle ?? '';
        $numero = $credito->cliente->numero ?? '';
        $colonia = $credito->cliente->colonia ?? '';
        $direccion = trim("$calle $numero, Col: $colonia", ', ');
        $telefono = $credito->cliente->telefono_celular ?? ($credito->cliente->telefono_fijo ?? 'N/A');

        $data = [
            'credito' => $credito,
            'logo_base64' => $logo_base64,
            'monto_credito' => $monto_credito,
            'comision' => $comision,
            'retencion_seguro' => $retencion_seguro,
            'pago_adelantado' => $pago_adelantado, 
            'total_deducciones' => $total_deducciones,
            'total_fondear' => $total_fondear,
            'direccion' => $direccion,
            'telefono' => $telefono,
        ];

        $pdf = Pdf::loadView('creditos.pdf.acta', $data);
        return $pdf->stream('Acta_Instalacion_' . $credito->folio . '.pdf');
    }

    public function imprimirAcuse($id)
    {
        $credito = \App\Models\Credito::with(['cliente', 'grupo', 'asesor', 'sucursal', 'patron'])->findOrFail($id);

        // 🖼️ Convertir Logo a Base64
        $logo_base64 = null;
        if ($credito->patron && $credito->patron->logo_path) {
            $path = public_path('storage/' . $credito->patron->logo_path);
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $logo_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        $data = [
            'credito' => $credito,
            'logo_base64' => $logo_base64,
        ];

        $pdf = Pdf::loadView('creditos.pdf.acuse', $data);
        return $pdf->stream('Acuse_Desembolso_' . $credito->folio . '.pdf');
    }

    public function imprimirCarta($id)
    {
        $credito = \App\Models\Credito::with(['cliente', 'grupo', 'asesor', 'sucursal', 'patron', 'amortizaciones', 'integrantes', 'cuentasDesembolso'])->findOrFail($id);

        if ($credito->amortizaciones->isEmpty()) {
            return back()->with('error', 'El crédito no tiene una tabla de amortización generada.');
        }

        // 🖼️ Convertir Logo a Base64
        $logo_base64 = null;
        if ($credito->patron && $credito->patron->logo_path) {
            $path = public_path('storage/' . $credito->patron->logo_path);
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $logo_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        $primeraCuota = $credito->amortizaciones->first();
        $cuota_monto = $primeraCuota ? $primeraCuota->total_cuota : 0;
        
        $mora_porcentaje = $credito->producto->mora_valor ?? 10; 
        $monto_mora_calculado = ($credito->monto_aprobado * $mora_porcentaje) / 100;

        $cuenta = $credito->cuentasDesembolso->first(); // Tomamos la primera cuenta registrada (si hay)

        $data = [
            'credito' => $credito,
            'logo_base64' => $logo_base64,
            'cuota_monto' => $cuota_monto,
            'monto_mora_calculado' => $monto_mora_calculado,
            'cuenta' => $cuenta,
            'letras_monto_aprobado' => $this->convertirALetras($credito->monto_aprobado),
            'letras_multa' => $this->convertirALetras($credito->producto->multa_valor ?? 500),
        ];

        $pdf = Pdf::loadView('creditos.pdf.carta', $data);
        return $pdf->stream('Carta_Compromiso_' . $credito->folio . '.pdf');
    }

    public function imprimirReferencias($id)
    {
        $credito = \App\Models\Credito::with([
            'cliente', 'grupo', 'asesor', 'patron', 
            'amortizaciones', 'cuentasParaPago', 'sucursalesParaPago'
        ])->findOrFail($id);

        if ($credito->amortizaciones->isEmpty()) {
            return back()->with('error', 'El crédito no tiene una tabla de amortización generada.');
        }

        // 🖼️ Convertir Logo a Base64
        $logo_base64 = null;
        if ($credito->patron && $credito->patron->logo_path) {
            $path = public_path('storage/' . $credito->patron->logo_path);
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $logo_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        // Obtenemos el monto de la cuota fija
        $primeraCuota = $credito->amortizaciones->first();
        $cuota_monto = $primeraCuota ? $primeraCuota->total_cuota : 0;

        $data = [
            'credito' => $credito,
            'logo_base64' => $logo_base64,
            'cuota_monto' => $cuota_monto,
        ];

        $pdf = Pdf::loadView('creditos.pdf.referencias', $data);
        return $pdf->stream('Referencias_Pago_' . $credito->folio . '.pdf');
    }

    public function imprimirSolicitud($id)
    {
        $credito = \App\Models\Credito::with([
            'cliente.referencias', // Cargamos las referencias del cliente
            'grupo', 'asesor', 'sucursal', 'patron'
        ])->findOrFail($id);

        // 🖼️ Convertir Logo a Base64
        $logo_base64 = null;
        if ($credito->patron && $credito->patron->logo_path) {
            $path = public_path('storage/' . $credito->patron->logo_path);
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $logo_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        $data = [
            'credito' => $credito,
            'cliente' => $credito->cliente,
            'logo_base64' => $logo_base64,
        ];

        $pdf = Pdf::loadView('creditos.pdf.solicitud', $data);
        return $pdf->stream('Solicitud_Credito_' . $credito->folio . '.pdf');
    }

// --- MÓDULO DE DESEMBOLSOS CON FILTROS ---
public function desembolsos(Request $request)
{
    $query = Credito::with(['cliente', 'grupo', 'asesor', 'sucursal', 'integrantes', 'cuentasDesembolso'])
                ->where('estatus', 'aprobado');

    // Filtro por Nombre (Grupo, Cliente o Titular)
    if ($request->filled('nombre')) {
        $nombre = $request->nombre;
        $query->where(function($q) use ($nombre) {
            $q->where('nombre_credito', 'like', "%{$nombre}%")
              ->orWhereHas('grupo', function($qG) use ($nombre) {
                  $qG->where('nombre_grupo', 'like', "%{$nombre}%");
              })
              ->orWhereHas('cliente', function($qC) use ($nombre) {
                  $qC->where('nombre', 'like', "%{$nombre}%")
                     ->orWhere('apellido_paterno', 'like', "%{$nombre}%");
              })
              ->orWhereHas('integrantes', function($qI) use ($nombre) {
                  $qI->where('nombre', 'like', "%{$nombre}%")
                     ->orWhere('apellido_paterno', 'like', "%{$nombre}%");
              });
        });
    }

    // Filtro por Sucursal
    if ($request->filled('sucursal_id')) {
        $query->where('sucursal_id', $request->sucursal_id);
    }

    // Filtro por Fecha de Desembolso
    if ($request->filled('fecha')) {
        $query->whereDate('fecha_desembolso', $request->fecha);
    }

    $creditos = $query->orderBy('fecha_desembolso', 'asc')->get();

    // Agrupamos la colección por el nombre de la sucursal
    $agrupados = $creditos->groupBy(function($credito) {
        return $credito->sucursal->nombre_sucursal ?? 'SIN SUCURSAL';
    });

    // Obtenemos las sucursales para el select del filtro
    $sucursales = \App\Models\Sucursal::all();

    return view('creditos.desembolsos', compact('agrupados', 'sucursales'));
}

    // --- ACCIÓN: MARCAR COMO FONDEADO ---
    public function fondear($id)
    {
        $credito = Credito::findOrFail($id);
        
        // Cambiamos el estatus para que nazca oficialmente y pase a Cartera Activa / Cajas
        $credito->estatus = 'desembolsado';
        $credito->save();

        return redirect()->route('desembolsos.index')->with('success', '¡El crédito ' . $credito->folio . ' ha sido fondeado exitosamente! Ya se encuentra activo en Cajas.');
    }

    // --- MÓDULO DE CARTERA ACTIVA ---
public function carteraActiva(Request $request)
{
    // Eager load de amortizaciones y producto para obtener la cuota y su frecuencia
    $query = Credito::with(['cliente', 'grupo', 'asesor', 'sucursal', 'integrantes', 'amortizaciones', 'producto'])
                ->where('estatus', 'desembolsado');

    // Filtro por Nombre (Grupo, Cliente o Titular)
    if ($request->filled('nombre')) {
        $nombre = $request->nombre;
        $query->where(function($q) use ($nombre) {
            $q->where('nombre_credito', 'like', "%{$nombre}%")
              ->orWhereHas('grupo', function($qG) use ($nombre) {
                  $qG->where('nombre_grupo', 'like', "%{$nombre}%");
              })
              ->orWhereHas('cliente', function($qC) use ($nombre) {
                  $qC->where('nombre', 'like', "%{$nombre}%")
                     ->orWhere('apellido_paterno', 'like', "%{$nombre}%");
              })
              ->orWhereHas('integrantes', function($qI) use ($nombre) {
                  $qI->where('nombre', 'like', "%{$nombre}%")
                     ->orWhere('apellido_paterno', 'like', "%{$nombre}%");
              });
        });
    }

    // Filtro por Sucursal
    if ($request->filled('sucursal_id')) {
        $query->where('sucursal_id', $request->sucursal_id);
    }

    $creditos = $query->orderBy('fecha_desembolso', 'desc')->paginate(15);
    $sucursales = \App\Models\Sucursal::all();

    return view('creditos.activos', compact('creditos', 'sucursales'));
}
}
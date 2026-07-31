<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ListaRayaPeriodo;
use App\Models\ListaRayaDetalle;
use App\Models\NominaTimbrada;
use App\Models\Sucursal;
use App\Services\CalculadoraImpuestosService;
use App\Services\FacturamaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NominaTimbradoController extends Controller
{
    protected $calculadoraImpuestos;
    protected $facturama;

    public function __construct(CalculadoraImpuestosService $calculadoraImpuestos, FacturamaService $facturama)
    {
        $this->calculadoraImpuestos = $calculadoraImpuestos;
        $this->facturama = $facturama;
    }

    /**
     * Muestra la vista principal del Módulo de Nómina y Timbrado.
     */
    public function index(Request $request)
    {
        $opcionesPeriodo = $this->getOpcionesPeriodo();
        $sucursales = Sucursal::where('status', 'Activa')->orderBy('nombre_sucursal')->get();

        $resultados = collect();
        $sucursalSeleccionada = null;

        $periodoRango = $request->input('periodo');
        $sucursalId = $request->input('id_sucursal');
        $modoTrabajo = $request->input('modo_trabajo', 'interna'); 
        $baseCalculo = $request->input('base_calculo', 'bruto');  

        if ($periodoRango && $sucursalId) {
            
            $queryPeriodos = ListaRayaPeriodo::where('periodo_rango', $periodoRango);

            if ($sucursalId !== 'todas') {
                $queryPeriodos->where('id_sucursal', $sucursalId);
                $sucursalSeleccionada = Sucursal::find($sucursalId);
            } else {
                $sucursalSeleccionada = (object)['nombre_sucursal' => 'Todas las Sucursales (Procesamiento Masivo)'];
            }

            $periodosIds = $queryPeriodos->pluck('id_periodo_lista');

            if ($periodosIds->isNotEmpty()) {
                $detalles = ListaRayaDetalle::with(['empleado', 'nominaTimbrada', 'periodo'])
                    ->whereIn('id_periodo_lista', $periodosIds)
                    ->get();

                $resultados = $detalles->map(function ($det) use ($modoTrabajo, $baseCalculo) {
                    $emp = $det->empleado;
                    $montoNetoInterno = floatval($det->total_neto);

                    $datos = [
                        'id_detalle_lista'    => $det->id_detalle_lista,
                        'id_empleado'         => $det->id_empleado,
                        'empleado_nombre'     => $emp ? $emp->nombre_completo : 'Empleado no encontrado',
                        'puesto'              => $det->puesto_historico ?? ($emp->puesto->nombre_puesto ?? 'Sin puesto'),
                        'tipo_contrato'       => $emp->tipo_contrato ?? 'Indeterminado',
                        
                        'nombre_fiscal'       => $emp->nombre_fiscal ?? null,
                        'rfc'                 => $emp->rfc ?? null,
                        'curp'                => $emp->curp ?? null,
                        'nss'                 => $emp->nss ?? null,
                        'cp_fiscal'           => $emp->cp_fiscal ?? $emp->codigo_postal ?? null,
                        'regimen_fiscal'      => $emp->regimen_fiscal ?? '605',
                        
                        'retardos_reporte'    => $det->retardos_acumulados ?? 0,
                        'faltas_reporte'      => $det->faltas_directas ?? 0,
                        'sueldo_quincenal'    => $det->sueldo_mensual_historico ? ($det->sueldo_mensual_historico / 2) : 0,
                        
                        'bono_permanencia'    => $det->bono_permanencia ?? 0,
                        'bono_cumpleanos'     => $det->bono_cumpleanos ?? 0,
                        'prima_vacacional'    => $det->prima_vacacional ?? 0,
                        'deduccion_faltas'    => $det->descuento_por_faltas ?? 0,
                        'deduccion_prestamo'  => $det->deduccion_prestamo ?? 0,
                        'deduccion_caja_ahorro' => $det->deduccion_caja_ahorro ?? 0,
                        'deduccion_infonavit' => $det->deduccion_infonavit ?? 0,
                        
                        'neto_a_pagar'        => $montoNetoInterno,
                        
                        'estado_timbrado'     => $det->nominaTimbrada->estado_timbrado ?? 'pendiente',
                        'uuid_cfdi'           => $det->nominaTimbrada->uuid_cfdi ?? null,
                        'xml_path'            => $det->nominaTimbrada->xml_path ?? null,
                        'pdf_path'            => $det->nominaTimbrada->pdf_path ?? null,
                        'mensaje_error_sat'   => $det->nominaTimbrada->mensaje_error_sat ?? null,
                    ];

                    if ($modoTrabajo === 'fiscal') {
                        $aplicaImss = !in_array(strtolower($emp->tipo_contrato ?? ''), ['honorarios', 'asimilados']);

                        // 🔥 LÓGICA DE ESQUEMA MIXTO (SDI vs REAL)
                        if ($aplicaImss && floatval($emp->sdi) > 0) {
                            // Trabajadores con IMSS: Usamos su SDI x 15 días
                            $sueldoBrutoBase = floatval($emp->sdi) * 15;
                            $calculoFiscal = $this->calculadoraImpuestos->calcularDesdeBruto($sueldoBrutoBase, $aplicaImss);
                        } else {
                            // Honorarios/Asimilados: Usamos su sueldo interno/real
                            $sueldoBrutoBase = floatval($det->sueldo_mensual_historico) / 2;
                            
                            if ($baseCalculo === 'neto') {
                                $calculoFiscal = $this->calculadoraImpuestos->calcularDesdeNeto($montoNetoInterno, $aplicaImss);
                            } else {
                                $calculoFiscal = $this->calculadoraImpuestos->calcularDesdeBruto($sueldoBrutoBase, $aplicaImss);
                            }
                        }

                        $datos['sueldo_bruto']   = $calculoFiscal['bruto'];
                        $datos['deduccion_isr']  = $calculoFiscal['isr_a_retener'];
                        $datos['subsidio_empleo']= $calculoFiscal['subsidio_empleo'];
                        $datos['deduccion_imss'] = $calculoFiscal['imss'];
                        $datos['neto_a_pagar']   = $calculoFiscal['neto'];
                    } else {
                        $datos['sueldo_bruto']   = 0;
                        $datos['deduccion_isr']  = 0;
                        $datos['subsidio_empleo']= 0;
                        $datos['deduccion_imss'] = 0;
                    }

                    return $datos;
                });
            }
        }

        return view('nomina.timbrado.index', compact(
            'opcionesPeriodo',
            'sucursales',
            'resultados',
            'sucursalSeleccionada'
        ));
    }

    private function getOpcionesPeriodo(): array
    {
        $opcionesPeriodo = [];
        $fechaActual = Carbon::now();
        for ($i = 0; $i < 13; $i++) {
            $fecha = $fechaActual->copy()->subMonths($i);
            
            $inicioQuincena1 = $fecha->copy()->startOfMonth();
            $finQuincena1 = $fecha->copy()->startOfMonth()->addDays(14);
            $valor1 = $inicioQuincena1->toDateString() . '_' . $finQuincena1->toDateString();
            $texto1 = '1ra Quincena ' . $inicioQuincena1->translatedFormat('F Y');
            if(!in_array($texto1, array_column($opcionesPeriodo, 'texto'))) {
                $opcionesPeriodo[] = ['valor' => $valor1, 'texto' => $texto1];
            }
            
            $inicioQuincena2 = $fecha->copy()->startOfMonth()->addDays(15);
            $finQuincena2 = $fecha->copy()->endOfMonth();
            $valor2 = $inicioQuincena2->toDateString() . '_' . $finQuincena2->toDateString();
            $texto2 = '2da Quincena ' . $inicioQuincena2->translatedFormat('F Y');
            if(!in_array($texto2, array_column($opcionesPeriodo, 'texto'))) {
                 $opcionesPeriodo[] = ['valor' => $valor2, 'texto' => $texto2];
            }
        }
        return $opcionesPeriodo;
    }

    public function procesarTimbrado(Request $request)
    {
        $request->validate([
            'empleados_timbrar' => 'required|array|min:1',
        ]);

        $idsDetalles = $request->input('empleados_timbrar');
        
        // Obtenemos los detalles con toda la info necesaria para el timbrado
        $detalles = ListaRayaDetalle::with(['empleado', 'empleado.ultimoContrato.patron', 'periodo'])
                    ->whereIn('id_detalle_lista', $idsDetalles)
                    ->get();

        $timbradosCorrectos = 0;
        $errores = [];

        foreach ($detalles as $detalle) {
            $fiscal = []; // Inicializamos la variable por si falla antes del cálculo
            
            try {
                $emp = $detalle->empleado;
                $patron = $emp->ultimoContrato->patron ?? null;
                $periodo = $detalle->periodo;

                if (!$patron) {
                    throw new \Exception("El empleado no tiene un patrón/empresa asignado.");
                }

                // 1. RECALCULAR VALORES (Modo Fiscal)
                $sueldoBrutoBase = floatval($detalle->sueldo_mensual_historico) / 2;
                $aplicaImss = !in_array(strtolower($emp->tipo_contrato ?? ''), ['honorarios', 'asimilados']);
                
                // Aplicar SDI si lo tiene
                if ($aplicaImss && floatval($emp->sdi) > 0) {
                    $sueldoBrutoBase = floatval($emp->sdi) * 15;
                }
                
                $fiscal = $this->calculadoraImpuestos->calcularDesdeBruto($sueldoBrutoBase, $aplicaImss);

                // Percepciones
                $percepcionesArr = [];
                $totalPercepcionesGravadas = $fiscal['bruto'];
                $totalPercepcionesExentas = 0;

                // Sueldo (Clave SAT: 001)
                $percepcionesArr[] = [
                    "TipoPercepcion" => "001",
                    "Clave" => "001",
                    "Concepto" => "Sueldo",
                    "ImporteGravado" => round($fiscal['bruto'], 2),
                    "ImporteExento" => 0.0
                ];

                $bonos = floatval($detalle->bono_permanencia) + floatval($detalle->bono_cumpleanos);
                if ($bonos > 0) {
                    $percepcionesArr[] = [
                        "TipoPercepcion" => "038",
                        "Clave" => "038",
                        "Concepto" => "Bonos Extra",
                        "ImporteGravado" => round($bonos, 2),
                        "ImporteExento" => 0.0
                    ];
                    $totalPercepcionesGravadas += $bonos;
                }

                // Deducciones
                $deduccionesArr = [];
                $totalImpuestosRetenidos = 0;
                $totalOtrasDeducciones = 0;

                if ($fiscal['isr_a_retener'] > 0) {
                    $deduccionesArr[] = [
                        "TipoDeduccion" => "002",
                        "Clave" => "002",
                        "Concepto" => "ISR",
                        "Importe" => round($fiscal['isr_a_retener'], 2)
                    ];
                    $totalImpuestosRetenidos += $fiscal['isr_a_retener'];
                }

                if ($fiscal['imss'] > 0) {
                    $deduccionesArr[] = [
                        "TipoDeduccion" => "001",
                        "Clave" => "001",
                        "Concepto" => "IMSS",
                        "Importe" => round($fiscal['imss'], 2)
                    ];
                    $totalImpuestosRetenidos += $fiscal['imss'];
                }

                $faltas = floatval($detalle->descuento_por_faltas);
                if ($faltas > 0) {
                    $deduccionesArr[] = [
                        "TipoDeduccion" => "020",
                        "Clave" => "020",
                        "Concepto" => "Faltas y Retardos",
                        "Importe" => round($faltas, 2)
                    ];
                    $totalOtrasDeducciones += $faltas;
                }

                $otrosPagosArr = [];
                $totalOtrosPagos = 0;
                if ($fiscal['subsidio_empleo'] > 0) {
                    $otrosPagosArr[] = [
                        "TipoOtroPago" => "002",
                        "Clave" => "002",
                        "Concepto" => "Subsidio al Empleo",
                        "Importe" => round($fiscal['subsidio_empleo'], 2),
                        "SubsidioAlEmpleo" => [
                            "SubsidioCausado" => round($fiscal['subsidio_empleo'], 2)
                        ]
                    ];
                    $totalOtrosPagos += $fiscal['subsidio_empleo'];
                }

                // 2. TOTALES DEL CFDI
                $subtotalCfdi = $totalPercepcionesGravadas + $totalPercepcionesExentas + $totalOtrosPagos;
                $descuentoCfdi = $totalImpuestosRetenidos + $totalOtrasDeducciones;
                $totalCfdi = $subtotalCfdi - $descuentoCfdi;

                $fechaTimbrado = Carbon::now()->format('Y-m-d\TH:i:s');
                $fechaInicio = explode('_', $periodo->periodo_rango)[0];
                $fechaFin = explode('_', $periodo->periodo_rango)[1];

                // 3. ARMADO DEL JSON PARA FACTURAMA
                $payloadFacturama = [
                    "Serie" => "NOM",
                    "Date" => $fechaTimbrado,
                    "PaymentMethod" => "PUE", 
                    "PaymentForm" => "99",    
                    "Currency" => "MXN",
                    "Exportation" => "01",
                    // 🔥 AQUI AGREGAMOS EL LUGAR DE EXPEDICIÓN (C.P. del Patrón)
                    "ExpeditionPlace" => $patron->codigo_postal ?? "00000", 
                    
                    "Receiver" => [
                        "Rfc" => strtoupper($emp->rfc),
                        "Name" => strtoupper($emp->nombre_fiscal),
                        "CfdiUse" => "CN01", 
                        "FiscalRegime" => $emp->regimen_fiscal ?? "605",
                        "TaxZipCode" => $emp->cp_fiscal
                    ],
                    "Items" => [
                        [
                            "ProductCode" => "84111505",
                            "IdentificationNumber" => "NOM",
                            "Description" => "Pago de nómina",
                            "Unit" => "ACT",
                            "UnitCode" => "ACT",
                            "UnitPrice" => round($subtotalCfdi, 2),
                            "Quantity" => 1.0,
                            "Subtotal" => round($subtotalCfdi, 2),
                            "Discount" => round($descuentoCfdi, 2),
                            "Total" => round($totalCfdi, 2)
                        ]
                    ],
                    "Complemento" => [
                        "Nomina" => [
                            "TipoNomina" => "O",
                            "FechaPago" => $fechaFin,
                            "FechaInicialPago" => $fechaInicio,
                            "FechaFinalPago" => $fechaFin,
                            "NumDiasPagados" => 15,
                            "Emisor" => [
                                "RegistroPatronal" => $patron->registro_patronal ?? "00000000000"
                            ],
                            "Receptor" => [
                                "Curp" => strtoupper($emp->curp),
                                "TipoContrato" => "01", 
                                "TipoJornada" => "01",  
                                "PeriodicidadPago" => "04", 
                                "ClaveEntFed" => "MEX", 
                                "NumEmpleado" => (string) $emp->id_empleado,
                                "Departamento" => "GENERAL",
                                "Puesto" => $detalle->puesto_historico ?? "EMPLEADO",
                                "RiesgoPuesto" => "1", 
                                "SalarioBaseCotApor" => round($sueldoBrutoBase / 15, 2),
                                "SalarioDiarioIntegrado" => floatval($emp->sdi) > 0 ? floatval($emp->sdi) : round(($sueldoBrutoBase / 15) * 1.0452, 2)
                            ],
                            "Percepciones" => [
                                "TotalSueldos" => round($totalPercepcionesGravadas + $totalPercepcionesExentas, 2),
                                "TotalGravado" => round($totalPercepcionesGravadas, 2),
                                "TotalExento" => round($totalPercepcionesExentas, 2),
                                "Percepcion" => $percepcionesArr
                            ]
                        ]
                    ]
                ];

                if (count($deduccionesArr) > 0) {
                    $payloadFacturama["Complemento"]["Nomina"]["Deducciones"] = [
                        "TotalOtrasDeducciones" => round($totalOtrasDeducciones, 2),
                        "TotalImpuestosRetenidos" => round($totalImpuestosRetenidos, 2),
                        "Deduccion" => $deduccionesArr
                    ];
                }

                if (count($otrosPagosArr) > 0) {
                    $payloadFacturama["Complemento"]["Nomina"]["OtrosPagos"] = $otrosPagosArr;
                }

                $response = $this->facturama->createInvoice($payloadFacturama);

                if ($response->successful()) {
                    $facturamaData = $response->json();
                    
                    // 🔥 AGREGAMOS LOS MONTOS PARA QUE NO DE ERROR DE "NULL" EN LA BD
                    NominaTimbrada::updateOrCreate(
                        ['id_detalle_lista' => $detalle->id_detalle_lista],
                        [
                            'id_empleado' => $emp->id_empleado,
                            'uuid_cfdi' => $facturamaData['Complement']['TaxStamp']['Uuid'],
                            'facturama_id' => $facturamaData['Id'],
                            'estado_timbrado' => 'timbrado',
                            'mensaje_error_sat' => null,
                            'fecha_timbrado' => Carbon::now(),
                            'sueldo_bruto' => $fiscal['bruto'] ?? 0,
                            'deduccion_isr' => $fiscal['isr_a_retener'] ?? 0,
                            'deduccion_imss' => $fiscal['imss'] ?? 0,
                        ]
                    );

                    $timbradosCorrectos++;

                } else {
                    $errorResponse = $response->json();
                    $msjError = $errorResponse['Message'] ?? ($errorResponse['message'] ?? 'Error desconocido del PAC');
                    if (isset($errorResponse['ModelState'])) {
                        $msjError = json_encode($errorResponse['ModelState']);
                    }
                    throw new \Exception($msjError);
                }

            } catch (\Exception $e) {
                // 🔥 AQUÍ TAMBIÉN AGREGAMOS LOS MONTOS PARA GUARDAR EL ERROR SIN ROMPER LA BD
                NominaTimbrada::updateOrCreate(
                    ['id_detalle_lista' => $detalle->id_detalle_lista],
                    [
                        'id_empleado' => $detalle->id_empleado,
                        'estado_timbrado' => 'error',
                        'mensaje_error_sat' => Str::limit($e->getMessage(), 250),
                        'sueldo_bruto' => $fiscal['bruto'] ?? 0,
                        'deduccion_isr' => $fiscal['isr_a_retener'] ?? 0,
                        'deduccion_imss' => $fiscal['imss'] ?? 0,
                    ]
                );
                $errores[] = "Error con " . $detalle->empleado->nombre_completo . ": " . $e->getMessage();
            }
        }

        if (count($errores) > 0) {
            Log::error("Errores en Timbrado de Nómina: ", $errores);
            return back()->with('error', 'Se encontraron errores al timbrar: ' . implode(" | ", $errores));
        }

        return back()->with('success', "Se han timbrado exitosamente $timbradosCorrectos recibos de nómina.");
    }}
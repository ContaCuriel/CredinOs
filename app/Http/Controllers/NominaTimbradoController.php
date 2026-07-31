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

                        if ($aplicaImss && floatval($emp->sdi) > 0) {
                            $sueldoBrutoBase = floatval($emp->sdi) * 15;
                            $calculoFiscal = $this->calculadoraImpuestos->calcularDesdeBruto($sueldoBrutoBase, $aplicaImss);
                        } else {
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
                        
                        $totalOtrasDed = floatval($det->descuento_por_faltas) + floatval($det->deduccion_prestamo) + floatval($det->deduccion_caja_ahorro) + floatval($det->deduccion_infonavit);
                        $datos['neto_a_pagar']   = $calculoFiscal['neto'] - $totalOtrasDed;
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
        
        $detalles = ListaRayaDetalle::with(['empleado', 'empleado.ultimoContrato.patron', 'periodo'])
                    ->whereIn('id_detalle_lista', $idsDetalles)
                    ->get();

        $timbradosCorrectos = 0;
        $errores = [];

        foreach ($detalles as $detalle) {
            $fiscal = []; 
            
            try {
                $emp = $detalle->empleado;
                $patron = $emp->ultimoContrato->patron ?? null;
                $periodo = $detalle->periodo;

                if (!$patron) {
                    throw new \Exception("El empleado no tiene un patrón/empresa asignado.");
                }

                $sueldoBrutoBase = floatval($detalle->sueldo_mensual_historico) / 2;
                $aplicaImss = !in_array(strtolower($emp->tipo_contrato ?? ''), ['honorarios', 'asimilados']);
                
                if ($aplicaImss && floatval($emp->sdi) > 0) {
                    $sueldoBrutoBase = floatval($emp->sdi) * 15;
                }
                
                $fiscal = $this->calculadoraImpuestos->calcularDesdeBruto($sueldoBrutoBase, $aplicaImss);

                // --- MAPEO CON FORMATEO ESTRICTO DE 2 DECIMALES (Anti-Error de PHP) ---
                $perceptionsDetails = [];

                $perceptionsDetails[] = [
                    "PerceptionType" => "001",
                    "Code" => "001",
                    "Description" => "Sueldo",
                    "TaxedAmount" => number_format($fiscal['bruto'], 2, '.', ''),
                    "ExemptAmount" => "0.00"
                ];

                $bonos = floatval($detalle->bono_permanencia) + floatval($detalle->bono_cumpleanos);
                if ($bonos > 0) {
                    $perceptionsDetails[] = [
                        "PerceptionType" => "038",
                        "Code" => "038",
                        "Description" => "Bonos Extra",
                        "TaxedAmount" => number_format($bonos, 2, '.', ''),
                        "ExemptAmount" => "0.00"
                    ];
                }

                $deductionsDetails = [];

                if ($fiscal['isr_a_retener'] > 0) {
                    $deductionsDetails[] = [
                        "DeduccionType" => "002",
                        "Code" => "002",
                        "Description" => "ISR",
                        "Amount" => number_format($fiscal['isr_a_retener'], 2, '.', '')
                    ];
                }

                if ($fiscal['imss'] > 0) {
                    $deductionsDetails[] = [
                        "DeduccionType" => "001",
                        "Code" => "001",
                        "Description" => "IMSS",
                        "Amount" => number_format($fiscal['imss'], 2, '.', '')
                    ];
                }

                $faltas = floatval($detalle->descuento_por_faltas);
                if ($faltas > 0) {
                    $deductionsDetails[] = [
                        "DeduccionType" => "020",
                        "Code" => "020",
                        "Description" => "Faltas y Retardos",
                        "Amount" => number_format($faltas, 2, '.', '')
                    ];
                }

                $cajaAhorro = floatval($detalle->deduccion_caja_ahorro);
                if ($cajaAhorro > 0) {
                    $deductionsDetails[] = [
                        "DeduccionType" => "004",
                        "Code" => "004",
                        "Description" => "Caja de Ahorro",
                        "Amount" => number_format($cajaAhorro, 2, '.', '')
                    ];
                }

                $infonavit = floatval($detalle->deduccion_infonavit);
                if ($infonavit > 0) {
                    $deductionsDetails[] = [
                        "DeduccionType" => "009",
                        "Code" => "009",
                        "Description" => "Préstamo Infonavit",
                        "Amount" => number_format($infonavit, 2, '.', '')
                    ];
                }

                $otherPayments = [];
                $subsidio = floatval($fiscal['subsidio_empleo'] ?? 0);

                if ($aplicaImss || $subsidio > 0) {
                    $otherPayments[] = [
                        "OtherPaymentType" => "002",
                        "Code" => "002",
                        "Description" => "Subsidio al Empleo",
                        "Amount" => number_format($subsidio, 2, '.', ''), 
                        "EmploymentSubsidy" => [
                            "Amount" => number_format($subsidio, 2, '.', '')
                        ]
                    ];
                }

                $fechaTimbrado = Carbon::now()->format('Y-m-d\TH:i:s');
                $fechaInicio = explode('_', $periodo->periodo_rango)[0];
                $fechaFin = explode('_', $periodo->periodo_rango)[1];
                
                // --- REGLA SAT/IMSS PARA FECHA INICIO RELACIÓN LABORAL ---
                if ($aplicaImss && !empty($emp->fecha_alta_imss)) {
                    $fechaBaseLaboral = $emp->fecha_alta_imss;
                } else {
                    $fechaBaseLaboral = $emp->fecha_ingreso;
                }
                
                $fechaIngresoFormat = $fechaBaseLaboral ? Carbon::parse($fechaBaseLaboral)->format('Y-m-d\TH:i:s') : $fechaInicio . 'T00:00:00';

                // --- PREPARAR EMISOR DE NÓMINA ---
                $nominaIssuer = [
                    "EmployerRegistration" => $patron->registro_patronal ?? "00000000000"
                ];

                if (strlen(trim($patron->rfc ?? '')) === 13) {
                    if (empty($patron->curp)) {
                        throw new \Exception("El Patrón '{$patron->razon_social}' es Persona Física y el SAT exige su CURP. Por favor, agregue la CURP en el perfil de la empresa.");
                    }
                    $nominaIssuer["Curp"] = strtoupper(trim($patron->curp));
                }

                // Cálculo estricto de salario base y diario para 2 decimales
                $baseSalaryFormatted = number_format($sueldoBrutoBase / 15, 2, '.', '');
                $dailySalaryCalculated = floatval($emp->sdi) > 0 ? floatval($emp->sdi) : ($sueldoBrutoBase / 15) * 1.0452;
                $dailySalaryFormatted = number_format($dailySalaryCalculated, 2, '.', '');

                // 🔥 PAYLOAD DEFINITIVO CFDI 4.0 🔥
                $payloadFacturama = [
                    "NameId" => "16", 
                    "ExpeditionPlace" => $patron->codigo_postal ?? "00000",
                    "CfdiType" => "N", 
                    "PaymentMethod" => "PUE",
                    "Exportation" => "01",
                    "Folio" => (string) $detalle->id_detalle_lista,
                    "Currency" => "MXN",
                    
                    "Issuer" => [
                        "Rfc" => strtoupper($patron->rfc ?? ''),
                        "Name" => strtoupper($patron->razon_social ?? ''),
                        "FiscalRegime" => $patron->regimen_fiscal ?? "601"
                    ],

                    "Receiver" => [
                        "Rfc" => strtoupper($emp->rfc),
                        "Name" => strtoupper($emp->nombre_fiscal),
                        "CfdiUse" => "CN01", 
                        "FiscalRegime" => $emp->regimen_fiscal ?? "605",
                        "TaxZipCode" => $emp->cp_fiscal
                    ],
                    
                    "Complemento" => [
                        "Payroll" => [
                            "Type" => "O",
                            "PaymentDate" => $fechaFin . 'T12:00:00',
                            "InitialPaymentDate" => $fechaInicio . 'T00:00:00',
                            "FinalPaymentDate" => $fechaFin . 'T23:59:59',
                            "DaysPaid" => 15,
                            "Issuer" => $nominaIssuer,
                            "Employee" => [
                                "Curp" => strtoupper($emp->curp),
                                "SocialSecurityNumber" => $emp->nss ?? "00000000000",
                                "StartDateLaborRelations" => $fechaIngresoFormat,
                                "ContractType" => $aplicaImss ? "01" : "09", 
                                "RegimeType" => $aplicaImss ? "02" : "09", 
                                "Unionized" => false, 
                                "TypeOfJourney" => "01",  
                                "FrequencyPayment" => "04", 
                                "EmployeeNumber" => (string) $emp->id_empleado,
                                "Department" => "GENERAL",
                                "Position" => Str::limit($detalle->puesto_historico ?? "EMPLEADO", 50),
                                "PositionRisk" => $aplicaImss ? "1" : "99", 
                                "BaseSalary" => $baseSalaryFormatted,
                                "DailySalary" => $dailySalaryFormatted,
                                "FederalEntityKey" => "MEX" 
                            ],
                            "Perceptions" => [
                                "Details" => $perceptionsDetails
                            ]
                        ]
                    ]
                ];

                if (count($deductionsDetails) > 0) {
                    $payloadFacturama["Complemento"]["Payroll"]["Deductions"] = [
                        "Details" => $deductionsDetails
                    ];
                }

                if (count($otherPayments) > 0) {
                    $payloadFacturama["Complemento"]["Payroll"]["OtherPayments"] = $otherPayments;
                }

                $response = $this->facturama->createInvoice($payloadFacturama);

                if ($response->successful()) {
                    $facturamaData = $response->json();
                    
                    NominaTimbrada::updateOrCreate(
                        ['id_detalle_lista' => $detalle->id_detalle_lista],
                        [
                            'id_empleado' => $emp->id_empleado,
                            'uuid_cfdi' => $facturamaData['Complement']['TaxStamp']['Uuid'] ?? $facturamaData['Id'],
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
    }

    /**
     * Descargar PDF del Recibo de Nómina
     */
    public function descargarPdf($id_detalle)
    {
        $nomina = NominaTimbrada::where('id_detalle_lista', $id_detalle)->firstOrFail();

        if (!$nomina->facturama_id) {
            return back()->with('error', 'No se encontró el ID de Facturama para este recibo.');
        }

        // Llamamos al servicio de Facturama (Asegúrate de que tu servicio tenga un método para bajar archivos, o usa el endpoint directo)
        // El endpoint clásico de Facturama para obtener el archivo es: GET /cfdi/{id}?type=pdf
        $response = $this->facturama->getFile($nomina->facturama_id, 'pdf'); 

        if ($response->successful()) {
            $data = $response->json();
            // Facturama devuelve el contenido en base64 en la llave 'Content'
            $pdfDecoded = base64_decode($data['Content']);
            
            return response($pdfDecoded)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="Nomina_' . $nomina->uuid_cfdi . '.pdf"');
        }

        return back()->with('error', 'Error al descargar el PDF desde el PAC.');
    }

    /**
     * Descargar XML del Recibo de Nómina
     */
    public function descargarXml($id_detalle)
    {
        $nomina = NominaTimbrada::where('id_detalle_lista', $id_detalle)->firstOrFail();

        if (!$nomina->facturama_id) {
            return back()->with('error', 'No se encontró el ID de Facturama para este recibo.');
        }

        $response = $this->facturama->getFile($nomina->facturama_id, 'xml'); 

        if ($response->successful()) {
            $data = $response->json();
            $xmlDecoded = base64_decode($data['Content']);
            
            return response($xmlDecoded)
                ->header('Content-Type', 'application/xml')
                ->header('Content-Disposition', 'attachment; filename="Nomina_' . $nomina->uuid_cfdi . '.xml"');
        }

        return back()->with('error', 'Error al descargar el XML desde el PAC.');
    }
}
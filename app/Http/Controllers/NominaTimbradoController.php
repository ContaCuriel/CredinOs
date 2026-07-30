<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ListaRayaPeriodo;
use App\Models\ListaRayaDetalle;
use App\Models\NominaTimbrada;
use App\Models\Sucursal;
use App\Services\CalculadoraImpuestosService;
use Carbon\Carbon;

class NominaTimbradoController extends Controller
{
    protected $calculadoraImpuestos;

    public function __construct(CalculadoraImpuestosService $calculadoraImpuestos)
    {
        $this->calculadoraImpuestos = $calculadoraImpuestos;
    }

    /**
     * Muestra la vista principal del Módulo de Nómina y Timbrado.
     */
    public function index(Request $request)
    {
        // 1. Opciones Genéricas de Periodo (Homologado con Lista de Raya)
        $opcionesPeriodo = $this->getOpcionesPeriodo();

        // 2. Cargar Sucursales Activas
        $sucursales = Sucursal::where('status', 'Activa')->orderBy('nombre_sucursal')->get();

        $resultados = collect();
        $sucursalSeleccionada = null;

        $periodoRango = $request->input('periodo');
        $sucursalId = $request->input('id_sucursal');
        $modoTrabajo = $request->input('modo_trabajo', 'interna'); 
        $baseCalculo = $request->input('base_calculo', 'bruto');  

        // 3. Buscar si se seleccionaron filtros
        if ($periodoRango && $sucursalId) {
            
            // Buscar los encabezados de periodo que coincidan con la quincena elegida
            $queryPeriodos = ListaRayaPeriodo::where('periodo_rango', $periodoRango);

            if ($sucursalId !== 'todas') {
                $queryPeriodos->where('id_sucursal', $sucursalId);
                $sucursalSeleccionada = Sucursal::find($sucursalId);
            } else {
                $sucursalSeleccionada = (object)['nombre_sucursal' => 'Todas las Sucursales (Procesamiento Masivo)'];
            }

            // Obtenemos los IDs de los periodos guardados que coinciden
            $periodosIds = $queryPeriodos->pluck('id_periodo_lista');

            if ($periodosIds->isNotEmpty()) {
                // Buscamos los detalles de esos periodos
                $detalles = ListaRayaDetalle::with(['empleado', 'nominaTimbrada', 'periodo'])
                    ->whereIn('id_periodo_lista', $periodosIds)
                    ->get();

                // 4. Mapear y procesar cada registro
                $resultados = $detalles->map(function ($det) use ($modoTrabajo, $baseCalculo) {
                    $emp = $det->empleado;
                    $montoNetoInterno = floatval($det->total_neto);

                    $datos = [
                        'id_detalle_lista'    => $det->id_detalle_lista,
                        'id_empleado'         => $det->id_empleado,
                        // Corregido a nombre_completo según el modelo Empleado
                        'empleado_nombre'     => $emp ? $emp->nombre_completo : 'Empleado no encontrado',
                        'puesto'              => $det->puesto_historico ?? ($emp->puesto->nombre_puesto ?? 'Sin puesto'),
                        'tipo_contrato'       => $emp->tipo_contrato ?? 'Indeterminado',
                        
                        // 🔥 DATOS FISCALES AÑADIDOS PARA EL MODAL 🔥
                        'nombre_fiscal'       => $emp->nombre_fiscal ?? null,
                        'rfc'                 => $emp->rfc ?? null,
                        'curp'                => $emp->curp ?? null,
                        'nss'                 => $emp->nss ?? null,
                        'cp_fiscal'           => $emp->cp_fiscal ?? $emp->codigo_postal ?? null,
                        'regimen_fiscal'      => $emp->regimen_fiscal ?? '605',
                        
                        'retardos_reporte'    => $det->retardos_acumulados ?? 0,
                        'faltas_reporte'      => $det->faltas_directas ?? 0,
                        'sueldo_quincenal'    => $det->sueldo_mensual_historico ? ($det->sueldo_mensual_historico / 2) : 0,
                        
                        // Campos desglosados de la nueva estructura
                        'bono_permanencia'    => $det->bono_permanencia ?? 0,
                        'bono_cumpleanos'     => $det->bono_cumpleanos ?? 0,
                        'prima_vacacional'    => $det->prima_vacacional ?? 0,
                        'deduccion_faltas'    => $det->descuento_por_faltas ?? 0,
                        'deduccion_prestamo'  => $det->deduccion_prestamo ?? 0,
                        'deduccion_caja_ahorro' => $det->deduccion_caja_ahorro ?? 0,
                        'deduccion_infonavit' => $det->deduccion_infonavit ?? 0,
                        
                        'neto_a_pagar'        => $montoNetoInterno,
                        
                        // Estado de Timbrado
                        'estado_timbrado'     => $det->nominaTimbrada->estado_timbrado ?? 'pendiente',
                        'uuid_cfdi'           => $det->nominaTimbrada->uuid_cfdi ?? null,
                        'xml_path'            => $det->nominaTimbrada->xml_path ?? null,
                        'pdf_path'            => $det->nominaTimbrada->pdf_path ?? null,
                        'mensaje_error_sat'   => $det->nominaTimbrada->mensaje_error_sat ?? null,
                    ];

                    // Si estamos en Modo Fiscal, aplicamos la calculadora de impuestos
                    if ($modoTrabajo === 'fiscal') {
                        $aplicaImss = !in_array(strtolower($emp->tipo_contrato ?? ''), ['honorarios', 'asimilados']);

                        if ($baseCalculo === 'neto') {
                            $calculoFiscal = $this->calculadoraImpuestos->calcularDesdeNeto($montoNetoInterno, $aplicaImss);
                        } else {
                            $sueldoBrutoBase = floatval($det->sueldo_mensual_historico) / 2;
                            $calculoFiscal = $this->calculadoraImpuestos->calcularDesdeBruto($sueldoBrutoBase, $aplicaImss);
                        }

                        $datos['sueldo_bruto']   = $calculoFiscal['bruto'];
                        $datos['deduccion_isr']  = $calculoFiscal['isr'];
                        $datos['deduccion_imss'] = $calculoFiscal['imss'];
                        $datos['neto_a_pagar']   = $calculoFiscal['neto'];
                    } else {
                        $datos['sueldo_bruto']   = 0;
                        $datos['deduccion_isr']  = 0;
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

    /**
     * Helper para generar las opciones de periodo idéntico a Lista de Raya.
     */
    private function getOpcionesPeriodo(): array
    {
        $opcionesPeriodo = [];
        $fechaActual = Carbon::now();
        for ($i = 0; $i < 13; $i++) {
            $fecha = $fechaActual->copy()->subMonths($i);
            
            // 1ra Quincena
            $inicioQuincena1 = $fecha->copy()->startOfMonth();
            $finQuincena1 = $fecha->copy()->startOfMonth()->addDays(14);
            $valor1 = $inicioQuincena1->toDateString() . '_' . $finQuincena1->toDateString();
            $texto1 = '1ra Quincena ' . $inicioQuincena1->translatedFormat('F Y');
            if(!in_array($texto1, array_column($opcionesPeriodo, 'texto'))) {
                $opcionesPeriodo[] = ['valor' => $valor1, 'texto' => $texto1];
            }
            
            // 2da Quincena
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
}
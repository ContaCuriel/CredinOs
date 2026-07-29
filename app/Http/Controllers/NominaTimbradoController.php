<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ListaRayaPeriodo;
use App\Models\ListaRayaDetalle;
use App\Models\NominaTimbrada;
use App\Models\Sucursal;
use App\Services\CalculadoraImpuestosService;
use Illuminate\Support\Facades\DB;

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
        // 1. Cargar Opciones de Periodos (Fotografías Guardadas)
        $periodosGuardados = ListaRayaPeriodo::orderBy('id_periodo_lista', 'desc')->get();
        $opcionesPeriodo = $periodosGuardados->map(function ($p) {
            return [
                'valor' => $p->id_periodo_lista,
                'texto' => $p->periodo_rango . ' (' . ($p->sucursal->nombre_sucursal ?? 'Global') . ')'
            ];
        });

        // 2. Cargar Sucursales
        $sucursales = Sucursal::all();

        // Variables de respuesta para la vista
        $resultados = collect();
        $sucursalSeleccionada = null;

        $periodoId = $request->input('periodo');
        $sucursalId = $request->input('id_sucursal');
        $modoTrabajo = $request->input('modo_trabajo', 'interna'); // 'interna' o 'fiscal'
        $baseCalculo = $request->input('base_calculo', 'bruto');  // 'bruto' o 'neto'

        // 3. Si se seleccionó un periodo e id_sucursal, cargamos los datos
        if ($periodoId) {
            $queryDetalles = ListaRayaDetalle::with(['empleado', 'nominaTimbrada', 'periodo'])
                ->where('id_periodo_lista', $periodoId);

            if ($sucursalId && $sucursalId !== 'todas') {
                $queryDetalles->whereHas('empleado', function ($q) use ($sucursalId) {
                    $q->where('id_sucursal', $sucursalId);
                });
                $sucursalSeleccionada = Sucursal::find($sucursalId);
            }

            $detalles = $queryDetalles->get();

            // 4. Mapear y procesar cada registro según los switches
            $resultados = $detalles->map(function ($det) use ($modoTrabajo, $baseCalculo) {
                $emp = $det->empleado;
                $montoNetoInterno = floatval($det->total_neto);

                // Datos base del empleado
                $datos = [
                    'id_detalle_lista'    => $det->id_detalle_lista,
                    'id_empleado'         => $det->id_empleado,
                    'empleado_nombre'     => $emp ? ($emp->nombre . ' ' . $emp->apellido_paterno . ' ' . $emp->apellido_materno) : 'Empleado no encontrado',
                    'puesto'              => $det->puesto_historico ?? ($emp->puesto ?? 'Sin puesto'),
                    'tipo_contrato'       => $emp->tipo_contrato ?? 'Indeterminado',
                    'rfc'                 => $emp->rfc ?? null,
                    'cp_fiscal'           => $emp->cp_fiscal ?? $emp->codigo_postal ?? null,
                    'retardos_reporte'    => $det->retardos_acumulados ?? 0,
                    'faltas_reporte'      => $det->faltas_directas ?? 0,
                    'sueldo_quincenal'    => $det->sueldo_mensual_historico ? ($det->sueldo_mensual_historico / 2) : 0,
                    'bono_permanencia'    => 0,
                    'bono_cumpleanos'     => 0,
                    'prima_vacacional'    => 0,
                    'deduccion_faltas'    => $det->descuento_por_faltas ?? 0,
                    'deduccion_prestamo'  => 0,
                    'deduccion_caja_ahorro' => 0,
                    'neto_a_pagar'        => $montoNetoInterno,
                    
                    // Estado de Timbrado
                    'estado_timbrado'     => $det->nominaTimbrada->estado_timbrado ?? 'pendiente',
                    'uuid_cfdi'           => $det->nominaTimbrada->uuid_cfdi ?? null,
                    'xml_path'            => $det->nominaTimbrada->xml_path ?? null,
                    'pdf_path'            => $det->nominaTimbrada->pdf_path ?? null,
                    'mensaje_error_sat'   => $det->nominaTimbrada->mensaje_error_sat ?? null,
                ];

                // 5. Si estamos en Modo Fiscal, aplicamos la calculadora de impuestos
                if ($modoTrabajo === 'fiscal') {
                    $aplicaImss = !in_array(strtolower($emp->tipo_contrato ?? ''), ['honorarios', 'asimilados']);

                    if ($baseCalculo === 'neto') {
                        // Búsqueda inversa: Parte del Neto que recibió en la lista de raya
                        $calculoFiscal = $this->calculadoraImpuestos->calcularDesdeNeto($montoNetoInterno, $aplicaImss);
                    } else {
                        // Búsqueda directa: Toma el Sueldo Quincenal como Bruto
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

        return view('nomina.timbrado.index', compact(
            'opcionesPeriodo',
            'sucursales',
            'resultados',
            'sucursalSeleccionada'
        ));
    }
}
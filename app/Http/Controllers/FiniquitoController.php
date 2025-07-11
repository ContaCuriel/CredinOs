<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empleado;
use App\Models\Patron;
use App\Models\DeduccionEmpleado;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Throwable;
use PDF;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FiniquitoExport;
use Illuminate\Support\Str;

class FiniquitoController extends Controller
{
    public function index()
    {
        $empleados = Empleado::with(['puesto', 'sucursal'])->orderBy('nombre_completo')->get();
        $patrones = Patron::orderBy('nombre_comercial')->get();
        return view('finiquitos.index', compact('empleados', 'patrones'));
    }

    /**
     * Realiza el cálculo inicial y lo devuelve para la tabla editable.
     */
    public function calcular(Request $request)
    {
        try {
            $resultados = $this->obtenerCalculoInicial($request);
            return response()->json($resultados);
        } catch (Throwable $e) {
            return response()->json(['error_fatal' => 'Ha ocurrido un error.', 'mensaje' => $e->getMessage()], 500);
        }
    }

    /**
     * Prepara los datos (editados) y genera el PDF.
     */
    public function exportarPDF(Request $request)
    {
        try {
            $data = $this->prepararDatosParaExportacion($request);
            
            $pdf = PDF::loadView('finiquitos.pdf', $data);
            $nombreArchivo = str_replace(' ', '_', $data['titulo_documento']) . '_' . str_replace(' ', '_', $data['empleado']->nombre_completo) . '.pdf';
            return $pdf->stream($nombreArchivo);

        } catch (Throwable $e) {
            return response("Error al generar el PDF: " . $e->getMessage(), 500);
        }
    }
    
    /**
     * Prepara los datos (editados) y genera el Excel.
     */
    public function exportarExcel(Request $request)
    {
        try {
            $data = $this->prepararDatosParaExportacion($request);
            $nombreArchivo = str_replace(' ', '_', $data['titulo_documento']) . '_' . str_replace(' ', '_', $data['empleado']->nombre_completo) . '.xlsx';

            return Excel::download(new FiniquitoExport($data), $nombreArchivo);

        } catch (Throwable $e) {
            return response("Error al generar el archivo de Excel: " . $e->getMessage(), 500);
        }
    }

    /**
     * Método que toma los datos editados y los prepara para la exportación.
     */
    private function prepararDatosParaExportacion(Request $request): array
    {
        $request->validate([
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'fecha_final' => 'required|date',
            'tipo_calculo' => 'required',
            'dias_vacaciones_manuales' => 'required|numeric',
            'id_patron' => 'sometimes|exists:patrones,id_patron',
            'gratificacion_monto' => 'nullable|numeric|min:0',
            '*_monto' => 'sometimes|numeric'
        ]);

        $data = $request->all();
        
        $empleado = Empleado::with('puesto')->find($data['id_empleado']);
        $patron = Patron::find($data['id_patron'] ?? null);

        $fechaBaja = Carbon::parse($data['fecha_final']);
        $diaDeBaja = $fechaBaja->day;
        $diasLaboradosPeriodo = ($diaDeBaja <= 15) ? $diaDeBaja : ($diaDeBaja - 15);

        $totalPercepciones = 0;
        $percepcionesKeys = ['dias_laborados_monto', 'aguinaldo_monto', 'vacaciones_monto', 'prima_vacacional_monto', 'monto_3_meses', 'monto_prima_antiguedad', 'caja_ahorro_monto', 'gratificacion_monto'];
        foreach($percepcionesKeys as $key){
            $totalPercepciones += (float)($data[$key] ?? 0);
        }
        
        $totalDeducciones = (float)($data['prestamo_saldo'] ?? 0);
        $netoAPagar = $totalPercepciones - $totalDeducciones;

        $exportData = $data;
        $exportData['empleado'] = $empleado;
        $exportData['patron'] = $patron;
        $exportData['salarioDiario'] = $empleado->puesto ? ($empleado->puesto->salario_mensual / 30) : 0;
        $exportData['total_percepciones'] = $totalPercepciones;
        $exportData['total_deducciones'] = $totalDeducciones;
        $exportData['neto_a_pagar'] = $netoAPagar;
        $exportData['fecha_final_formateada'] = $fechaBaja->format('d/m/Y');
        $exportData['dias_laborados_dias'] = $diasLaboradosPeriodo;
        $exportData['vacaciones_dias_restantes'] = $data['dias_vacaciones_manuales'];
        
        $titulos = ['dias_laborados' => 'PAGO DE DÍAS LABORADOS', 'finiquito' => 'RECIBO DE FINIQUITO', 'liquidacion' => 'RECIBO DE LIQUIDACIÓN'];
        $exportData['titulo_documento'] = $titulos[$data['tipo_calculo']] ?? 'RECIBO DE PAGO';

        $exportData['logo_base64'] = null;
        if ($patron && $patron->logo_path) {
            $logoPath = storage_path('app/public/' . $patron->logo_path); 
            if (File::exists($logoPath)) {
                $logoData = File::get($logoPath);
                $logoMimeType = File::mimeType($logoPath);
                $exportData['logo_base64'] = 'data:' . $logoMimeType . ';base64,' . base64_encode($logoData);
            }
        }
        
        return $exportData;
    }
    
    /**
     * Realiza el cálculo inicial desde cero, incluyendo la gratificación.
     */
   private function obtenerCalculoInicial(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'fecha_final' => 'required|date',
            'tipo_calculo' => 'required|string|in:dias_laborados,finiquito,liquidacion',
            'dias_vacaciones_manuales' => 'required_if:tipo_calculo,finiquito,liquidacion|numeric|min:0',
            'gratificacion_monto' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        $empleado = Empleado::with('puesto')->findOrFail($request->id_empleado);
        $fechaIngreso = Carbon::parse($empleado->fecha_ingreso);
        $fechaBaja = Carbon::parse($request->fecha_final);
        
        $salarioMensual = $empleado->puesto ? $empleado->puesto->salario_mensual : 0;
        $salarioDiario = $salarioMensual > 0 ? ($salarioMensual / 30) : 0;
        
        $resultados = [];
        $anosCompletos = $fechaIngreso->diffInYears($fechaBaja);

        $conceptos = [
            'dias_laborados_monto', 'vacaciones_monto', 'prima_vacacional_monto', 'aguinaldo_monto',
            'monto_3_meses', 'monto_prima_antiguedad', 'caja_ahorro_monto', 'prestamo_saldo', 
            'dias_laborados_dias', 'vacaciones_dias_restantes', 'gratificacion_monto'
        ];
        foreach ($conceptos as $concepto) {
            $resultados[$concepto] = 0;
        }

        $resultados['gratificacion_monto'] = (float) $request->input('gratificacion_monto', 0);

        $diaDeBaja = $fechaBaja->day;
        $diasLaboradosPeriodo = ($diaDeBaja <= 15) ? $diaDeBaja : ($diaDeBaja - 15);
        $resultados['dias_laborados_monto'] = $diasLaboradosPeriodo * $salarioDiario;
        $resultados['dias_laborados_dias'] = $diasLaboradosPeriodo;

        $deducciones = DeduccionEmpleado::where('id_empleado', $empleado->id_empleado)->where('status', 'Activo')->get();
        $resultados['caja_ahorro_monto'] = $deducciones->where('tipo_deduccion', 'Caja de Ahorro')->sum('monto_acumulado');
        $resultados['prestamo_saldo'] = $deducciones->where('tipo_deduccion', 'Préstamo')->sum('saldo_pendiente');
        
        if (in_array($request->tipo_calculo, ['finiquito', 'liquidacion'])) {
            $diasTotalesAPagar = (float) $request->dias_vacaciones_manuales;
            $resultados['vacaciones_monto'] = $diasTotalesAPagar * $salarioDiario;
            $resultados['prima_vacacional_monto'] = $resultados['vacaciones_monto'] * 0.25;
            $resultados['vacaciones_dias_restantes'] = $diasTotalesAPagar;
            
            // Obtener el inicio del año de la fecha de baja
            $inicioAnoActual = Carbon::parse($fechaBaja->format('Y-01-01'));

            // Determinar la fecha de inicio para el cálculo del aguinaldo:
            // Si la fecha de ingreso es posterior al inicio del año actual, se usa la fecha de ingreso.
            // De lo contrario, se usa el inicio del año actual (1 de enero).
            $fechaInicioAguinaldo = $fechaIngreso->greaterThan($inicioAnoActual) ? $fechaIngreso : $inicioAnoActual;

            // Calcular los días trabajados desde la fecha de inicio del aguinaldo hasta la fecha de baja
            // Se suma 1 para incluir el día de la baja.
            $diasTrabajadosParaAguinaldo = $fechaInicioAguinaldo->diffInDays($fechaBaja) + 1;

            $aguinaldoProporcional = ($salarioDiario * 15 / 365) * $diasTrabajadosParaAguinaldo;
            $resultados['aguinaldo_monto'] = $aguinaldoProporcional;
        }
        
        if ($request->tipo_calculo === 'liquidacion') {
            $resultados['monto_3_meses'] = $salarioDiario * 90;
            $resultados['monto_prima_antiguedad'] = ($salarioDiario * 12) * $anosCompletos;
        }

        return $resultados;
    }

public function exportarRenunciaPdf(Request $request)
    {
        // 1. Validar los datos necesarios
        $validatedData = $request->validate([
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'id_patron' => 'required|exists:patrones,id_patron',
            'fecha_final' => 'required|date',
        ]);

        // 2. Obtener los modelos con sus relaciones
        $empleado = Empleado::with(['puesto', 'ultimoContrato'])->findOrFail($validatedData['id_empleado']);
        $patron = Patron::findOrFail($validatedData['id_patron']);
        $fechaIngreso = Carbon::parse($empleado->fecha_ingreso);
        $fechaFin = Carbon::parse($validatedData['fecha_final']);

        // 3. ======== LÓGICA DE VERIFICACIÓN CENTRALIZADA ========
        $esContratoDeHonorarios = false;
        // Verificamos que exista un último contrato y que el tipo no esté vacío
        if ($empleado->ultimoContrato && !empty($empleado->ultimoContrato->tipo_contrato)) {
            // Comprobamos si la palabra 'honorarios' existe en el tipo de contrato
            if (Str::contains(strtolower($empleado->ultimoContrato->tipo_contrato), 'honorarios')) {
                $esContratoDeHonorarios = true;
            }
        }
        // ==========================================================

        // 4. Formatear fechas a texto largo en español
        $fecha_ingreso_letra = $fechaIngreso->translatedFormat('l, d \de F \de Y');
        $fecha_fin_letra = $fechaFin->translatedFormat('l, d \de F \de Y');

        // 5. Preparar los datos para la vista, incluyendo nuestra nueva bandera
        $data = [
            'empleado' => $empleado,
            'patron' => $patron,
            'fecha_ingreso_letra' => $fecha_ingreso_letra,
            'fecha_fin_letra' => $fecha_fin_letra,
            'esContratoDeHonorarios' => $esContratoDeHonorarios, // <-- Se pasa la bandera a la vista
        ];

        // 6. Generar el PDF
        $nombreArchivo = 'carta_renuncia_' . Str::slug($empleado->nombre_completo) . '.pdf';
        $pdf = Pdf::loadView('finiquitos.pdf_renuncia', $data);

        return $pdf->stream($nombreArchivo);
    }
}




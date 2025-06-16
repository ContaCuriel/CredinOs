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
     * CORREGIDO: Método que toma los datos editados, añade la información faltante
     * y los prepara para la exportación.
     */
    private function prepararDatosParaExportacion(Request $request): array
    {
        $request->validate([
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'fecha_final' => 'required|date',
            'tipo_calculo' => 'required',
            'dias_vacaciones_manuales' => 'required|numeric',
            'id_patron' => 'sometimes|exists:patrones,id_patron',
            '*_monto' => 'sometimes|numeric'
        ]);

        $data = $request->all();
        
        $empleado = Empleado::with('puesto')->find($data['id_empleado']);
        $patron = Patron::find($data['id_patron'] ?? null);

        // --- INICIO DE LA CORRECCIÓN ---
        // Se calcula y añade la variable que faltaba para la vista del PDF/Excel.
        $fechaBaja = Carbon::parse($data['fecha_final']);
        $diaDeBaja = $fechaBaja->day;
        $diasLaboradosPeriodo = ($diaDeBaja <= 15) ? $diaDeBaja : ($diaDeBaja - 15);
        // --- FIN DE LA CORRECCIÓN ---

        $totalPercepciones = 0;
        $percepcionesKeys = ['dias_laborados_monto', 'aguinaldo_monto', 'vacaciones_monto', 'prima_vacacional_monto', 'monto_3_meses', 'monto_prima_antiguedad', 'caja_ahorro_monto'];
        foreach($percepcionesKeys as $key){
            $totalPercepciones += (float)($data[$key] ?? 0);
        }
        
        $totalDeducciones = (float)($data['prestamo_saldo'] ?? 0);
        $netoAPagar = $totalPercepciones - $totalDeducciones;

        // Preparar el array final para la vista
        $exportData = $data;
        $exportData['empleado'] = $empleado;
        $exportData['patron'] = $patron;
        $exportData['salarioDiario'] = $empleado->puesto ? ($empleado->puesto->salario_mensual / 30) : 0;
        $exportData['total_percepciones'] = $totalPercepciones;
        $exportData['total_deducciones'] = $totalDeducciones;
        $exportData['neto_a_pagar'] = $netoAPagar;
        $exportData['fecha_final_formateada'] = $fechaBaja->format('d/m/Y');
        $exportData['dias_laborados_dias'] = $diasLaboradosPeriodo; // Se añade la variable
        $exportData['vacaciones_dias_restantes'] = $data['dias_vacaciones_manuales']; // Se asegura que el dato se pase
        
        $titulos = ['dias_laborados' => 'PAGO DE DÍAS LABORADOS', 'finiquito' => 'RECIBO DE FINIQUITO', 'liquidacion' => 'RECIBO DE LIQUIDACIÓN'];
        $exportData['titulo_documento'] = $titulos[$data['tipo_calculo']] ?? 'RECIBO DE PAGO';

        // Preparar el logo (solo para PDF)
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
    
    private function obtenerCalculoInicial(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'fecha_final' => 'required|date',
            'tipo_calculo' => 'required|string|in:dias_laborados,finiquito,liquidacion',
            'dias_vacaciones_manuales' => 'required_if:tipo_calculo,finiquito,liquidacion|numeric|min:0'
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
            'dias_laborados_dias', 'vacaciones_dias_restantes'
        ];
        foreach ($conceptos as $concepto) {
            $resultados[$concepto] = 0;
        }

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
            $diasTrabajadosAno = $fechaBaja->dayOfYear;
            $aguinaldoProporcional = ($salarioDiario * 15 / 365) * $diasTrabajadosAno;
            $resultados['aguinaldo_monto'] = $aguinaldoProporcional;
        }
        
        if ($request->tipo_calculo === 'liquidacion') {
            $resultados['monto_3_meses'] = $salarioDiario * 90;
            $resultados['monto_prima_antiguedad'] = ($salarioDiario * 12) * $anosCompletos;
        }

        return $resultados;
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empleado;
use App\Models\Patron;
use App\Models\DeduccionEmpleado;
use App\Models\Asistencia; // 🔥 IMPORTAMOS EL MODELO DE ASISTENCIA
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FiniquitoExport;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class FiniquitoController extends Controller
{
    public function index()
    {
        $empleados = Empleado::with(['puesto', 'sucursal'])->orderBy('nombre_completo')->get();
        $patrones = Patron::orderBy('nombre_comercial')->get();
        return view('finiquitos.index', compact('empleados', 'patrones'));
    }

    public function calcular(Request $request)
    {
        try {
            $resultados = $this->obtenerCalculoInicial($request);
            return response()->json($resultados);
        } catch (Throwable $e) {
            return response()->json(['error_fatal' => 'Ha ocurrido un error.', 'mensaje' => $e->getMessage()], 500);
        }
    }

    public function exportarPDF(Request $request)
    {
        try {
            $data = $this->prepararDatosParaExportacion($request);
            
            $pdf = Pdf::loadView('finiquitos.pdf', $data);
            $nombreArchivo = str_replace(' ', '_', $data['titulo_documento']) . '_' . str_replace(' ', '_', $data['empleado']->nombre_completo) . '.pdf';
            return $pdf->stream($nombreArchivo);

        } catch (Throwable $e) {
            return response("Error al generar el PDF: " . $e->getMessage(), 500);
        }
    }
    
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

    private function prepararDatosParaExportacion(Request $request): array
    {
        $data = $request->all();
        $empleado = Empleado::with(['puesto', 'ultimoContrato'])->find($data['id_empleado']);
        $patron = Patron::find($data['id_patron'] ?? null);

        $salarioMensual = $empleado->puesto ? $empleado->puesto->salario_mensual : 0;
        $salarioDiario = $salarioMensual > 0 ? ($salarioMensual / 30) : 0;

        $montoLaboradosEditado = (float)($data['dias_laborados_monto'] ?? 0);
        
        if ($salarioDiario > 0 && $montoLaboradosEditado > 0) {
            $diasEditados = round($montoLaboradosEditado / $salarioDiario, 1);
        } else {
            $diasEditados = 0;
        }

        $totalPercepciones = 0;
        $percepcionesKeys = ['dias_laborados_monto', 'aguinaldo_monto', 'vacaciones_monto', 'prima_vacacional_monto', 'monto_3_meses', 'monto_prima_antiguedad', 'caja_ahorro_monto', 'gratificacion_monto'];
        
        foreach($percepcionesKeys as $key){
            $totalPercepciones += (float)($data[$key] ?? 0);
        }
        
        $totalDeducciones = (float)($data['prestamo_saldo'] ?? 0);

        // =========================================================================
        // 🔥 LÓGICA DE CONCEPTOS EXTRAS (Agregados manualmente desde la tabla) 🔥
        // =========================================================================
        $conceptosExtras = json_decode($data['conceptos_extras_json'] ?? '[]', true);
        if (!is_array($conceptosExtras)) {
            $conceptosExtras = [];
        }

        foreach ($conceptosExtras as $extra) {
            $montoExtra = (float)($extra['monto'] ?? 0);
            if (isset($extra['tipo']) && $extra['tipo'] === 'percepcion') {
                $totalPercepciones += $montoExtra;
            } elseif (isset($extra['tipo']) && $extra['tipo'] === 'deduccion') {
                $totalDeducciones += $montoExtra;
            }
        }
        // =========================================================================

        $netoAPagar = $totalPercepciones - $totalDeducciones;

        $exportData = $data;
        $exportData['empleado'] = $empleado;
        $exportData['patron'] = $patron;
        $exportData['salarioDiario'] = $salarioDiario;
        $exportData['total_percepciones'] = $totalPercepciones;
        $exportData['total_deducciones'] = $totalDeducciones;
        $exportData['neto_a_pagar'] = $netoAPagar;
        $exportData['fecha_final_formateada'] = Carbon::parse($data['fecha_final'])->format('d/m/Y');
        
        $exportData['dias_laborados_dias'] = $diasEditados; 
        $exportData['vacaciones_dias_restantes'] = $data['dias_vacaciones_manuales'];

        foreach($percepcionesKeys as $key) {
            $exportData[$key] = (float)($data[$key] ?? 0);
        }
        $exportData['prestamo_saldo'] = (float)($data['prestamo_saldo'] ?? 0);

        // Pasamos el arreglo de los conceptos extra a las Vistas de PDF y Excel
        $exportData['conceptos_extras'] = $conceptosExtras;

        $titulos = ['dias_laborados' => 'PAGO DE DÍAS LABORADOS', 'finiquito' => 'RECIBO DE FINIQUITO', 'liquidacion' => 'RECIBO DE LIQUIDACIÓN'];
        $exportData['titulo_documento'] = $titulos[$data['tipo_calculo']] ?? 'RECIBO DE PAGO';

        $exportData['esContratoDeHonorarios'] = ($empleado->ultimoContrato && Str::contains(strtolower($empleado->ultimoContrato->tipo_contrato), 'honorarios'));

        $exportData['logo_base64'] = null;
        if ($patron && $patron->logo_path) {
            $logoPath = storage_path('app/public/' . $patron->logo_path); 
            if (File::exists($logoPath)) {
                $exportData['logo_base64'] = 'data:' . File::mimeType($logoPath) . ';base64,' . base64_encode(File::get($logoPath));
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
            'dias_vacaciones_manuales' => 'required_if:tipo_calculo,finiquito,liquidacion|numeric|min:0',
            'gratificacion_monto' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        // 🔥 OBTENEMOS TAMBIÉN EL HORARIO PARA VER REGLAS DE RETARDOS
        $empleado = Empleado::with(['puesto', 'horario'])->findOrFail($request->id_empleado);
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

        $inicioQuincenaTeorico = null;
        if ($fechaBaja->day <= 15) {
            $inicioQuincenaTeorico = $fechaBaja->copy()->startOfMonth();
        } else {
            $inicioQuincenaTeorico = $fechaBaja->copy()->day(16);
        }

        $fechaInicioCalculo = $fechaIngreso->greaterThan($inicioQuincenaTeorico) ? $fechaIngreso : $inicioQuincenaTeorico;

        $diasLaboradosPeriodo = $fechaInicioCalculo->diffInDays($fechaBaja) + 1;

        $resultados['dias_laborados_monto'] = $diasLaboradosPeriodo * $salarioDiario;
        $resultados['dias_laborados_dias'] = $diasLaboradosPeriodo;

        // =========================================================================
        // 🔥 ANÁLISIS DE ASISTENCIAS DE LA ÚLTIMA QUINCENA PARA LA ALERTA "i"
        // =========================================================================
        $asistenciasFinales = Asistencia::where('id_empleado', $empleado->id_empleado)
            ->whereBetween('fecha', [$inicioQuincenaTeorico->format('Y-m-d'), $fechaBaja->format('Y-m-d')])
            ->get();

        $faltasCrudas = 0;
        $retardosCrudos = 0;
        $mediosDias = 0;

        foreach($asistenciasFinales as $asis) {
            // 🔥 CORRECCIÓN: Buscamos en status_asistencia y lo convertimos a minúsculas
            $estado = strtolower($asis->status_asistencia ?? '');

            if (in_array($estado, ['falta', 'falta_por_retardo_extremo'])) {
                // Si tienes un campo penalizacion en DB úsalo, sino asume 1
                $faltasCrudas += ($asis->penalizacion ?? 1);
            } elseif (in_array($estado, ['medio_dia', 'medio día', 'mediodia'])) {
                $mediosDias += 0.5;
            } elseif ($estado == 'retardo') {
                $retardosCrudos += 1;
            }
        }

        // Calculamos faltas generadas por retardos si existe la regla
        $reglaRetardos = $empleado->horario ? ($empleado->horario->retardos_por_falta ?? 0) : 0;
        $faltasPorRetardos = $reglaRetardos > 0 ? floor($retardosCrudos / $reglaRetardos) : 0;
        
        $totalFaltasSugeridas = $faltasCrudas + $mediosDias + $faltasPorRetardos;

        // Lo mandamos al JSON para que la vista decida qué hacer
        $resultados['info_asistencia'] = [
            'faltas_directas' => $faltasCrudas,
            'retardos' => $retardosCrudos,
            'medios_dias' => $mediosDias * 2, // Para mostrar "1 medio día" en vez de 0.5
            'total_dias_descontar' => $totalFaltasSugeridas,
            'monto_sugerido_descuento' => $totalFaltasSugeridas * $salarioDiario
        ];
        
        // El debug ya no es necesario, pero lo dejamos vacío para limpiar el JSON
        $resultados['debug_asistencias'] = [];
        // =========================================================================

        $deducciones = DeduccionEmpleado::where('id_empleado', $empleado->id_empleado)->where('status', 'Activo')->get();
        $resultados['caja_ahorro_monto'] = $deducciones->where('tipo_deduccion', 'Caja de Ahorro')->sum('monto_acumulado');
        $resultados['prestamo_saldo'] = $deducciones->where('tipo_deduccion', 'Préstamo')->sum('saldo_pendiente');
        
        if (in_array($request->tipo_calculo, ['finiquito', 'liquidacion'])) {
            $diasTotalesAPagar = (float) $request->dias_vacaciones_manuales;
            $resultados['vacaciones_monto'] = $diasTotalesAPagar * $salarioDiario;
            $resultados['prima_vacacional_monto'] = $resultados['vacaciones_monto'] * 0.25;
            $resultados['vacaciones_dias_restantes'] = $diasTotalesAPagar;
            
            $inicioAnoActual = Carbon::parse($fechaBaja->format('Y-01-01'));
            $fechaInicioAguinaldo = $fechaIngreso->greaterThan($inicioAnoActual) ? $fechaIngreso : $inicioAnoActual;
            $diasTrabajadosParaAguinaldo = $fechaInicioAguinaldo->diffInDays($fechaBaja) + 1;
            $aguinaldoProporcional = ($salarioDiario * 15 / 365) * $diasTrabajadosParaAguinaldo;
            $resultados['aguinaldo_monto'] = $aguinaldoProporcional;
        }
        
        if ($request->tipo_calculo === 'liquidacion') {
            $resultados['monto_3_meses'] = $salarioDiario * 90;
            $resultados['monto_prima_antiguedad'] = ($salarioDiario * 12) * $anosCompletos;
        }
        $resultados['salario_diario'] = $salarioDiario;

        return $resultados;
    }

    public function exportarRenunciaPdf(Request $request)
    {
        $validatedData = $request->validate([
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'id_patron' => 'required|exists:patrones,id_patron',
            'fecha_final' => 'required|date',
        ]);

        $empleado = Empleado::with(['puesto', 'ultimoContrato'])->findOrFail($validatedData['id_empleado']);
        $patron = Patron::findOrFail($validatedData['id_patron']);
        $fechaIngreso = Carbon::parse($empleado->fecha_ingreso);
        $fechaFin = Carbon::parse($validatedData['fecha_final']);

        $esContratoDeHonorarios = false;
        if ($empleado->ultimoContrato && !empty($empleado->ultimoContrato->tipo_contrato)) {
            if (Str::contains(strtolower($empleado->ultimoContrato->tipo_contrato), 'honorarios')) {
                $esContratoDeHonorarios = true;
            }
        }

        $fecha_ingreso_letra = $fechaIngreso->translatedFormat('l, d \de F \de Y');
        $fecha_fin_letra = $fechaFin->translatedFormat('l, d \de F \de Y');

        $data = [
            'empleado' => $empleado,
            'patron' => $patron,
            'fecha_ingreso_letra' => $fecha_ingreso_letra,
            'fecha_fin_letra' => $fecha_fin_letra,
            'esContratoDeHonorarios' => $esContratoDeHonorarios, 
        ];

        $nombreArchivo = 'carta_renuncia_' . Str::slug($empleado->nombre_completo) . '.pdf';
        $pdf = Pdf::loadView('finiquitos.pdf_renuncia', $data);

        return $pdf->stream($nombreArchivo);
    }

    public function uploadSigned(Request $request, Empleado $empleado)
    {
        $request->validate([
            'documento_firmado' => 'required|file|mimes:pdf|max:2048', 
        ], [
            'documento_firmado.required' => 'Debes seleccionar un archivo.',
            'documento_firmado.mimes' => 'El archivo debe ser un PDF.',
            'documento_firmado.max' => 'El archivo no debe pesar más de 2MB.',
        ]);

        if ($empleado->finiquito_firmado_path && Storage::disk('public')->exists($empleado->finiquito_firmado_path)) {
            Storage::disk('public')->delete($empleado->finiquito_firmado_path);
        }

        $path = $request->file('documento_firmado')->store('finiquitos_firmados', 'public');
        $empleado->update(['finiquito_firmado_path' => $path]);

        return back()->with('success', '¡Documento firmado subido exitosamente!');
    }

    public function viewSigned(Empleado $empleado)
    {
        if (!$empleado->finiquito_firmado_path || !Storage::disk('public')->exists($empleado->finiquito_firmado_path)) {
            abort(404, 'Documento no encontrado.');
        }
        return Storage::disk('public')->response($empleado->finiquito_firmado_path);
    }

    public function generarAvisoTerminacion($id_empleado)
    {
        $empleado = \App\Models\Empleado::with(['sucursal', 'puesto', 'contratos' => function($query) {
            $query->latest('fecha_inicio'); 
        }, 'contratos.patron'])->findOrFail($id_empleado);

        $contrato = $empleado->contratos->first();

        if (!$contrato) {
            return back()->with('error', 'El empleado no tiene contratos registrados en el sistema.');
        }

        $patron = $contrato->patron;

        if (!$patron) {
            return back()->with('error', 'El contrato del empleado no tiene un patrón (empresa) asignado.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('documentos.generales.aviso_terminacion', compact('empleado', 'contrato', 'patron'));
        
        return $pdf->stream("Aviso_Terminacion_" . \Illuminate\Support\Str::slug($empleado->nombre_completo) . ".pdf");
    }

    /**
     * Llama a la API de IA para redactar documentos legales/RH a partir de contexto crudo.
     */
    public function redactarDocumentoIA(Request $request)
    {
        try {
            $data =$request->validate([
                'id_empleado' => 'required|exists:empleados,id_empleado',
                'id_patron' => 'required|exists:patrones,id_patron',
                'fecha_final' => 'required|date',
                'contexto_crudo' => 'required|string|min:10',
                'tipo_documento' => 'required|string'
            ]);

            $empleado = Empleado::with('ultimoContrato')->findOrFail($data['id_empleado']);
            $patron = Patron::findOrFail($data['id_patron']);

            $fechaIngreso = Carbon::parse($empleado->fecha_ingreso)->translatedFormat('d \d\e F \d\e Y');$fechaBaja = Carbon::parse($data['fecha_final'])->translatedFormat('d \d\e F \d\e Y');$vencimientoContrato = 'No especificado';
            if ($empleado->ultimoContrato &&$empleado->ultimoContrato->fecha_fin) {
                $vencimientoContrato = Carbon::parse($empleado->ultimoContrato->fecha_fin)->translatedFormat('d \d\e F \d\e Y');
            }

            $prompt = "Actúa como un abogado laboral corporativo en México. Tu tarea es redactar un(a) '{$data['tipo_documento']}' formal, profesional y legalmente blindado.
            
            DATOS DUROS OBLIGATORIOS A INCLUIR:
            - Empresa / Patrón: {$patron->razon_social}
            - Empleado: {$empleado->nombre_completo}
            - Fecha de Ingreso del empleado: {$fechaIngreso}
            - Fecha de Baja / Emisión del documento: {$fechaBaja}
            - Fecha de vencimiento de su contrato actual: {$vencimientoContrato}

            CONTEXTO DE LOS HECHOS (Mensajes o notas crudas):
            \"{$data['contexto_crudo']}\"

            INSTRUCCIONES DE FORMATO ESTRICTAS:
            1. Transforma el contexto crudo en lenguaje legal y estructurado (Antecedentes, Hechos, Determinación).
            2. Devuelve la respuesta ÚNICAMENTE en formato HTML válido (usa etiquetas <p>, <strong>, <ul>, <li>, <h3>, <br>).
            3. NO incluyas las etiquetas <html>, <head> o <body>, solo el contenido interior para ser inyectado en un editor de texto.
            4. NO uses Markdown como ```html ni asteriscos. Solo etiquetas HTML puras.
            5. Al final, incluye líneas de firma para la empresa y para el empleado.";

            $apiKey = env('GEMINI_API_KEY', '');
            if (empty($apiKey)) {
                return response()->json(['error' => 'API Key de Gemini no configurada.'], 500);
            }

            // URL 100% limpia sin corchetes de Markdown
            $apiUrl = '[https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key=](https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key=)' . $apiKey;
            
            $response = \Illuminate\Support\Facades\Http::timeout(60)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($apiUrl, [
                    'contents' => [
                        [
                            'role' => 'user', 
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $htmlRedactado = $response->json('candidates.0.content.parts.0.text', '<p>No se pudo generar el documento.</p>');
                
                // Limpieza de Markdown residual en caso de que la IA responda con comillas de código
                $htmlRedactado = str_replace(['```html', '```'], '', $htmlRedactado);
                
                return response()->json(['documento_html' => trim($htmlRedactado)]);
            } else {
                return response()->json(['error' => 'Google API Error: ' . $response->body()], 500);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Recibe el HTML del editor mágico y lo imprime en PDF con membrete.
     */
    public function exportarDocumentoIAPdf(Request $request)
    {
        $request->validate([
            'html_content' => 'required|string',
            'id_patron' => 'required|exists:patrones,id_patron',
            'tipo_documento' => 'required|string'
        ]);

        $patron = Patron::findOrFail($request->id_patron);

        $logo_base64 = null;
        if ($patron->logo_path && File::exists(storage_path('app/public/' . $patron->logo_path))) {
            $logoPath = storage_path('app/public/' . $patron->logo_path);
            $logo_base64 = 'data:' . File::mimeType($logoPath) . ';base64,' . base64_encode(File::get($logoPath));
        }

        $data = [
            'html_content' => $request->html_content,
            'patron' => $patron,
            'logo_base64' => $logo_base64,
            'titulo_documento' => mb_strtoupper($request->tipo_documento, 'UTF-8')
        ];

        // Crearemos una vista súper sencilla para este PDF en el siguiente paso
        $pdf = Pdf::loadView('finiquitos.pdf_ia', $data);
        return $pdf->stream(Str::slug($request->tipo_documento) . '_' . date('Ymd_His') . '.pdf');
    }
}
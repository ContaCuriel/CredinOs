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
            $data = $request->validate([
                'id_empleado'    => 'required|exists:empleados,id_empleado',
                'id_patron'      => 'required|exists:patrones,id_patron',
                'fecha_final'    => 'required|date',
                'contexto_crudo' => 'required|string|min:10',
                'tipo_documento' => 'required|string',
                'representante_legal' => 'nullable|string',
                'esquema'        => 'nullable|in:HONORARIOS,LABORAL' // Opcional: Si el front lo manda, se respeta
            ]);

            $empleado = Empleado::with(['ultimoContrato', 'sucursal'])->findOrFail($data['id_empleado']);
            $patron = Patron::findOrFail($data['id_patron']);

            $fechaIngreso = $empleado->fecha_ingreso 
                ? $empleado->fecha_ingreso->translatedFormat('d \d\e F \d\e Y') 
                : 'No especificada';
                
            $fechaBaja = Carbon::parse($data['fecha_final'])->translatedFormat('d \d\e F \d\e Y');
            $tipoContrato = $empleado->ultimoContrato->tipo_contrato ?? 'No especificado';

            // FIX DEL LUGAR: Formato "Municipio, Estado"
            $municipio = $empleado->sucursal->municipio ?? $empleado->sucursal->nombre_sucursal ?? 'Ciudad';
            $estado = $empleado->sucursal->estado ?? 'México';
            $lugarEmision = "{$municipio}, {$estado}";
            
            $representante = $data['representante_legal'] ?? $patron->representante_legal ?? 'Representante Legal';

            // 🔥 DEDUCCIÓN AUTOMÁTICA O MANUAL DEL ESQUEMA 🔥
            if (!empty($data['esquema'])) {
                $esHonorarios = ($data['esquema'] === 'HONORARIOS');
            } else {
                // Si el empleado está en Alta en el IMSS, NUNCA es Honorarios
                if ($empleado->estado_imss === 'Alta') {
                    $esHonorarios = false;
                } else {
                    $esHonorarios = ($tipoContrato === 'Honorarios');
                }
            }

            $tipoDoc = strtolower($data['tipo_documento']);

            if (str_contains($tipoDoc, 'recomendaci') || str_contains($tipoDoc, 'constancia')) {
                // PROMPT PARA CARTA DE RECOMENDACIÓN / CONSTANCIA
                $prompt = "Actúa como el departamento de Recursos Humanos de {$patron->razon_social}. 
                Tu tarea es redactar una '{$data['tipo_documento']}' formal, positiva y profesional.

                DATOS OBLIGATORIOS:
                - Empresa / Patrón: {$patron->razon_social}
                - Empleado / Ex-empleado: {$empleado->nombre_completo}
                - Fecha de Ingreso: {$fechaIngreso}
                - Fecha de Salida: {$fechaBaja}

                CONTEXTO / NOTAS ADICIONALES:
                \"{$data['contexto_crudo']}\"

                ESTRUCTURA REQUERIDA:
                - Encabezado: 'A QUIEN CORRESPONDA:'
                - Un párrafo formal certificando que {$empleado->nombre_completo} prestó sus servicios en {$patron->razon_social} durante el periodo del {$fechaIngreso} al {$fechaBaja}.
                - Un párrafo destacando su buen desempeño, responsabilidad y valores observados.
                - Párrafo de cierre: 'Se extiende la presente en {$lugarEmision}, a {$fechaBaja}, para los fines legales o laborales que al interesado convengan.'

                FORMATO Y FIRMAS:
                - Devuelve ÚNICAMENTE HTML (<p>, <strong>, <br>, <table>, <tr>, <td>). Sin comillas ```html ni <body>.
                - Al final, incluye esta tabla de firma centrada:
                <br><br>
                <table style=\"width: 100%; border: none; text-align: center; margin-top: 40px;\">
                    <tr>
                        <td style=\"width: 100%;\">___________________________________<br><strong>{$representante}</strong><br>{$patron->razon_social}<br>Departamento de Recursos Humanos</td>
                    </tr>
                </table>";
            } else {
                
                // CONFIGURACIÓN DE VOCABULARIO SEGÚN EL ESQUEMA
                if ($esHonorarios) {
                    $terminoRol = 'PRESTADOR DE SERVICIOS';
                    $reglasVocabulario = "REGLA DE ORO (ANTISIMULACIÓN): El contrato es puramente CIVIL/MERCANTIL. TIENES ESTRICTAMENTE PROHIBIDO usar las siguientes palabras: 'laboral', 'trabajador', 'empleado', 'patrón', 'relación de trabajo', 'despido', 'prestaciones', 'liquidación', 'finiquito' o 'Ley Federal del Trabajo'. Transforma cualquier nota del contexto crudo al lenguaje civil. Usa exclusivamente: 'prestador de servicios', 'empresa', 'honorarios', 'servicios independientes', 'incumplimiento contractual' y 'Código Civil'.";
                    
                    // 🔥 APLICAMOS TU OPCIÓN 1 (BLINDAJE CIVIL PERFECTION)
                    $terminoCierre = 'el pago total de los honorarios devengados a la fecha. Con la recepción de dicho pago, el PRESTADOR DE SERVICIOS manifiesta encontrarse enteramente satisfecho, otorgando el más amplio deslinde de responsabilidad civil, comercial y mercantil a favor de la empresa, declarando que no subsiste ninguna obligación, adeudo ni reclamación pendiente entre las partes.';
                } else {
                    $terminoRol = 'TRABAJADOR';
                    $reglasVocabulario = "REGLA DE ORO: El contrato es de naturaleza LABORAL. Adapta la terminología estrictamente a la Ley Federal del Trabajo. Usa: 'trabajador', 'patrón', 'relación laboral', 'rescisión', 'despido justificado', 'prestaciones' y 'finiquito'. TIENES ESTRICTAMENTE PROHIBIDO usar palabras como 'honorarios', 'servicios profesionales' o 'prestador de servicios'.";
                    $terminoCierre = 'el pago del finiquito y/o liquidación laboral de las prestaciones irrenunciables generadas, conforme a la Ley Federal del Trabajo.';
                }

                // PROMPT CORPORATIVO / EJECUTIVO
                $prompt = "Actúa como un abogado corporativo experto. Tu tarea es redactar un(a) '{$data['tipo_documento']}' con rigor jurídico y estilo ejecutivo, sin inconsistencias.
                
                TONO Y ESTILO:
                - Directo al grano, objetivo, contundente y estrictamente profesional.
                - {$reglasVocabulario}
                - Purifica el contexto crudo: Si las notas mencionan términos prohibidos, tradúcelos obligatoriamente al régimen legal correcto.
                
                DATOS OBLIGATORIOS (PROHIBIDO DEJAR ESPACIOS EN BLANCO):
                - Empresa: {$patron->razon_social}
                - {$terminoRol}: {$empleado->nombre_completo}
                - Fecha de Ingreso: {$fechaIngreso}
                - Fecha de Baja: {$fechaBaja}

                HECHOS / CONTEXTO A PROCESAR:
                \"{$data['contexto_crudo']}\"

                ESTRUCTURA EXACTA QUE DEBES SEGUIR (Usa números romanos y viñetas):
                Párrafo inicial literal: 'En {$lugarEmision}, a {$fechaBaja}, {$patron->razon_social} hace entrega del(a) presente {$data['tipo_documento']} a {$empleado->nombre_completo}, con base en las siguientes consideraciones:'
                
                <strong>I. ANTECEDENTES:</strong> (Un párrafo breve sobre el inicio del vínculo legal y la naturaleza del servicio/funciones, respetando la regla de vocabulario).
                
                <strong>II. DE LOS HECHOS:</strong> (Convierte el contexto crudo en una lista con viñetas <ul><li> de forma enteramente objetiva, detallando los incumplimientos sin exagerar y respetando la regla de vocabulario).
                
                <strong>III. DETERMINACIÓN:</strong> (Un párrafo breve anunciando la terminación anticipada, rescisión o sanción fundamentada en el régimen correcto).
                
                <strong>IV. CIERRE Y PAGOS:</strong> (Un párrafo indicando {$terminoCierre}).

                INSTRUCCIONES FINALES:
                1. Devuelve la respuesta ÚNICAMENTE en HTML (<p>, <strong>, <ul>, <li>, <br>, <table>, <tr>, <td>). Sin comillas ```html ni <body>.
                2. PARA LAS FIRMAS: Inserta EXACTAMENTE esta tabla de firmas al final:
                <br><br>
                <table style=\"width: 100%; border: none; text-align: center; margin-top: 50px;\">
                    <tr>
                        <td style=\"width: 50%;\">___________________________________<br><strong>{$representante}</strong><br>{$patron->razon_social}</td>
                        <td style=\"width: 50%;\">___________________________________<br><strong>Recibí de conformidad:<br>{$empleado->nombre_completo}</strong></td>
                    </tr>
                </table>";
            }

            $apiKey = env('GEMINI_API_KEY', '');
            if (empty($apiKey)) {
                return response()->json(['error' => 'API Key de Gemini no configurada.'], 500);
            }

            // TRAMPA ANTI-MARKDOWN Y MODELO 2.5-PRO
            $protocolo = 'http' . 's://';
            $dominio = 'generativelanguage' . '.googleapis' . '.com';
            $ruta = '/v1beta/models/gemini-2.5-pro:generateContent?key=';
            $apiUrl = $protocolo . $dominio . $ruta . trim($apiKey);
            
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
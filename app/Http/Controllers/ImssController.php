<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empleado;
use App\Models\Sucursal;
use App\Models\Patron;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;


class ImssController extends Controller
{
    /**
     * Display a listing of the employees with their IMSS status.
     */
    public function index(Request $request)
    {
        // Obtener filtros del request
        $search_nombre = $request->input('search_nombre');
        $id_sucursal_filter = $request->input('id_sucursal_filter');
        $estado_imss_filter = $request->input('estado_imss_filter', 'todos'); // Por defecto muestra 'Alta'

        // Iniciar la consulta para Empleados
        $query = Empleado::query()
                ->where('status', 'Alta');

        // Cargar relaciones necesarias para mostrar y filtrar eficientemente
        $query->with(['sucursal', 'puesto', 'patronImss']);

        // Filtrar por nombre de empleado
        if (!empty($search_nombre)) {
            $query->where('nombre_completo', 'like', '%' . $search_nombre . '%');
        }

        // Filtrar por sucursal del empleado
        if (!empty($id_sucursal_filter)) {
            $query->where('id_sucursal', $id_sucursal_filter);
        }

        // Filtrar por estado IMSS
        if (!empty($estado_imss_filter) && $estado_imss_filter != 'todos') {
            if ($estado_imss_filter == 'sin_registro') {
                $query->where(function ($q) {
                    $q->whereNull('estado_imss')->orWhere('estado_imss', '');
                });
            } else {
                $query->where('estado_imss', $estado_imss_filter);
            }
        } else if (empty($estado_imss_filter) && $estado_imss_filter !== 'todos' && $estado_imss_filter !== 'sin_registro') {
            // No se aplica filtro adicional si es 'todos' o si no se especificó y no es una de las opciones explícitas
        }

        // Ordenar y paginar
        $empleados = $query->orderBy('nombre_completo', 'asc')
                            ->paginate(15)
                            ->withQueryString();

        // Para los desplegables de los filtros
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $estados_imss_disponibles = [
            'Alta' => 'Alta en IMSS',
            'Baja' => 'Baja en IMSS',
            'sin_registro' => 'Sin Registro IMSS',
            'todos' => 'Todos (con y sin IMSS)',
        ];

        // OBTENER PATRONES
        $patrones = Patron::orderBy('razon_social')->get();

        return view('imss.index', compact(
            'empleados',
            'sucursales',
            'estados_imss_disponibles',
            'patrones',
            'search_nombre',
            'id_sucursal_filter',
            'estado_imss_filter'
        ));
    }

    /**
     * Procesa el registro de alta en IMSS para un empleado.
     */
   public function registrarAlta(Request $request, Empleado $empleado)
{
    // Usamos Validator::make() para tener control manual sobre la respuesta
    $validator = Validator::make($request->all(), [
        'id_patron_imss' => 'required|exists:patrones,id_patron', // Corregido: el nombre de tu tabla es 'patrones'
        'sdi' => 'required|numeric|min:0',
        'fecha_alta_imss' => 'required|date|before_or_equal:today',
    ], [
        'id_patron_imss.required' => 'Debes seleccionar un Patrón para el alta en IMSS.',
        'sdi.required' => 'El Salario Diario Integrado (SDI) es obligatorio.',
        'sdi.numeric' => 'El SDI debe ser un valor numérico.',
        'fecha_alta_imss.required' => 'La fecha de alta en IMSS es obligatoria.',
        'fecha_alta_imss.before_or_equal' => 'La fecha de alta no puede ser futura.',
    ]);

    // Si la validación falla, devolvemos los errores como JSON
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    try {
        $empleado->update([
            'id_patron_imss' => $request->id_patron_imss,
            'fecha_alta_imss' => $request->fecha_alta_imss,
            'sdi' => $request->sdi,
            'estado_imss' => 'Alta',
            'fecha_baja_imss' => null,
        ]);

        // Preparamos la URL de redirección con los filtros
        // NOTA: Asegúrate de que los nombres de los filtros coinciden con los que envías desde el formulario
        $queryParams = $request->only(['search_nombre', 'id_sucursal_filter', 'estado_imss_filter']);
        $redirectUrl = route('imss.index', array_filter($queryParams)); // array_filter para limpiar valores vacíos

        // Guardamos el mensaje de éxito en la sesión para que se muestre después de recargar
        session()->flash('success', '¡Alta en IMSS para ' . $empleado->nombre_completo . ' registrada exitosamente!');

        // Devolvemos una respuesta JSON de éxito con la URL para redirigir
        return response()->json([
            'message' => 'Alta registrada con éxito.',
            'redirect' => $redirectUrl
        ]);

    } catch (\Exception $e) {
        Log::error('Error al registrar alta IMSS para empleado ' . $empleado->id_empleado . ': ' . $e->getMessage());
        
        // Si hay un error de servidor, devolvemos un JSON de error
        return response()->json(['message' => 'Ocurrió un error inesperado en el servidor.'], 500);
    }
}

    public function registrarBaja(Request $request, Empleado $empleado)
    {
        // 1. Validación de los datos del modal
        $reglasValidacion = [
            'fecha_baja_imss' => 'required|date|before_or_equal:today',
            // Campos de filtro para redirección
            'id_sucursal_seleccionada' => 'nullable|exists:sucursales,id_sucursal',
            'search_nombre' => 'nullable|string|max:255',
            'estado_imss_filter' => 'nullable|string',
        ];

        $mensajesValidacion = [
            'fecha_baja_imss.required' => 'La fecha de baja del IMSS es obligatoria.',
            'fecha_baja_imss.date' => 'La fecha de baja del IMSS no es válida.',
            'fecha_baja_imss.before_or_equal' => 'La fecha de baja del IMSS no puede ser una fecha futura.',
        ];

        // Añadir validación de que la fecha de baja sea posterior o igual a la de alta, si existe alta
        if ($empleado->fecha_alta_imss) {
            $reglasValidacion['fecha_baja_imss'] .= '|after_or_equal:' . $empleado->fecha_alta_imss->toDateString();
            $mensajesValidacion['fecha_baja_imss.after_or_equal'] = 'La fecha de baja del IMSS debe ser posterior o igual a la fecha de alta en IMSS del empleado (' . $empleado->fecha_alta_imss->format('d/m/Y') . ').';
        }

        $validatedData = $request->validate($reglasValidacion, $mensajesValidacion);

        // 2. Verificar que el empleado esté actualmente de Alta en IMSS
        if ($empleado->estado_imss !== 'Alta') {
            return redirect()->route('imss.index', $this->getRedirectParams($request))
                                ->with('error', 'El empleado ' . $empleado->nombre_completo . ' no se encuentra actualmente de Alta en IMSS.');
        }

        // 3. Actualizar los datos del empleado
        $empleado->estado_imss = 'Baja';
        $empleado->fecha_baja_imss = $validatedData['fecha_baja_imss'];
        // La fecha_alta_imss y el id_patron_imss se conservan para el historial
        $empleado->save();

        // 4. Redirección con Mensaje de Éxito
        return redirect()->route('imss.index', $this->getRedirectParams($request))
                            ->with('success', '¡Baja de IMSS para ' . $empleado->nombre_completo . ' registrada exitosamente!');
    }

    // Helper para obtener parámetros de redirección (opcional, para no repetir código)
    private function getRedirectParams(Request $request)
    {
        $params = [];
        if ($request->filled('id_sucursal_seleccionada')) {
            $params['id_sucursal_seleccionada'] = $request->input('id_sucursal_seleccionada');
        }
        if ($request->filled('search_nombre')) {
            $params['search_nombre'] = $request->input('search_nombre');
        }
        if ($request->filled('estado_imss_filter')) {
            $params['estado_imss_filter'] = $request->input('estado_imss_filter');
        }
        return $params;
    }

    /**
     * Genera el PDF del acuse de alta en IMSS para un empleado.
     */
    public function generarAcuseAltaPdf(Empleado $empleado)
    {
        // Validar que el empleado esté realmente de alta y tenga los datos necesarios
        if ($empleado->estado_imss != 'Alta' || !$empleado->fecha_alta_imss || !$empleado->id_patron_imss) {
            return redirect()->route('imss.index')->with('error', 'El empleado no tiene un alta en IMSS válida para generar el acuse.');
        }

        // Cargar las relaciones necesarias para el PDF
        $empleado->load(['puesto', 'sucursal', 'patronImss']);

        $data = [
            'empleado' => $empleado,
            'patronImss' => $empleado->patronImss, // El patrón bajo el cual se dio de alta
        ];

        $nombreEmpleadoFormateado = Str::slug($empleado->nombre_completo, '_');
        $nombrePdf = 'acuse_alta_imss_' . $nombreEmpleadoFormateado . '_' . $empleado->id_empleado . '.pdf';

        $pdf = Pdf::loadView('imss.pdf.acuse_alta', $data);

        return $pdf->stream($nombrePdf); // Muestra el PDF en el navegador
    }

    public function generarCartaPatronal(Empleado $empleado)
    {
        // 1. Validar que el empleado esté de alta en IMSS y tenga un patrón asignado
        if ($empleado->estado_imss !== 'Alta' || !$empleado->id_patron_imss) {
            return redirect()->route('imss.index')->with('error', 'El empleado no tiene un alta en IMSS válida para generar la carta patronal.');
        }

        // 2. Cargar todas las relaciones necesarias para el PDF
        $empleado->load(['puesto', 'sucursal', 'patronImss', 'horario']);
        $patron = $empleado->patronImss;

        // 3. Calcular el SDI a partir del salario del puesto (se mantiene descomentado como lo pediste)
        $sdiCalculado = 0; // Valor por defecto si no hay puesto o salario
        if ($empleado->puesto && $empleado->puesto->salario_mensual) {
            $sdiCalculado = $empleado->puesto->salario_mensual / 30;
        }

        // 4. Obtener y codificar el logo en Base64
        $logo_base64 = null;
        if ($patron && $patron->logo_path) {
            // ***** ESTA ES LA LÍNEA CLAVE PARA TU CONFIGURACIÓN *****
            // Asume que $patron->logo_path contiene 'patron_logos/nombre_archivo.png'
            // y que 'public/storage' es un symlink a 'storage/app/public'.
            $logoPath = public_path('storage/' . $patron->logo_path); 
            // *******************************************************

            if (File::exists($logoPath)) {
                $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                $data = File::get($logoPath);
                $logo_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        // 5. Construir el texto del horario según tus reglas
        $horarioTexto = 'No especificado';
        if ($empleado->horario) {
            $h = $empleado->horario;
            $entradaLV = Carbon::parse($h->lunes_entrada)->format('H:i');
            $salidaLV = Carbon::parse($h->lunes_salida)->format('H:i');
            $entradaS = Carbon::parse($h->sabado_entrada)->format('H:i');
            $salidaS = Carbon::parse($h->sabado_salida)->format('H:i');

            $horarioTexto = "Lunes a Viernes de {$entradaLV} a {$salidaLV} hrs., Sábados de {$entradaS} a {$salidaS} hrs.";
        }
        
        // 6. Preparar los datos para la vista
        $data = [
            'empleado' => $empleado,
            'patron' => $patron,
            'horarioTexto' => $horarioTexto,
            'logo_base64' => $logo_base64, // Aseguramos que esta variable se pase
            'sdiCalculado' => $sdiCalculado, // Incluido como lo solicitaste, aunque la carta usa $empleado->sdi directamente.
        ];

        // 7. Generar el nombre del archivo
        $nombreEmpleadoFormateado = Str::slug($empleado->nombre_completo, '_');
        $nombrePdf = 'carta_patronal_' . $nombreEmpleadoFormateado . '.pdf';

        // 8. Cargar la vista y generar el PDF
        $pdf = Pdf::loadView('pdf_templates.carta_patronal', $data);

        return $pdf->stream($nombrePdf);
    }
}
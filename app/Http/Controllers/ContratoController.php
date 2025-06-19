<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Empleado;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ContratosPanoramaExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use App\Models\Patron;
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;
use Illuminate\Support\Facades\File; 
 use Illuminate\Http\Response; 
 use ZipArchive;

class ContratoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search_nombre_empleado = $request->input('search_nombre_empleado');
        $id_sucursal_filter = $request->input('id_sucursal_filter');

        $query = Empleado::query()->where('status', 'Alta');

        if (!empty($search_nombre_empleado)) {
            $query->where('nombre_completo', 'like', '%' . $search_nombre_empleado . '%');
        }

        if (!empty($id_sucursal_filter)) {
            $query->where('id_sucursal', $id_sucursal_filter);
        }

        $empleados = $query->with(['puesto', 'sucursal', 'ultimoContrato.patron'])
            ->withCount('contratos')
            ->orderBy('nombre_completo', 'asc')
            ->paginate(15);

        $todasLasSucursales = Sucursal::orderBy('nombre_sucursal')->get();

        return view('contratos.index', compact(
            'empleados',
            'todasLasSucursales',
            'search_nombre_empleado',
            'id_sucursal_filter'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $empleados = Empleado::where('status', 'Alta')->orderBy('nombre_completo')->get();
        $patrones = Patron::orderBy('razon_social')->get();

        // --- CORRECCIÓN AQUÍ ---
        // Se asegura que todos los elementos del array, excepto el último, tengan una coma.
        $tipos_contrato = [
            'Determinado' => 'Contrato por Tiempo Determinado',
            'Indeterminado' => 'Contrato por Tiempo Indeterminado',
            'Sueldo Variable' => 'Contrato por Sueldo Variable',
            'Honorarios' => 'Prestación de Servicios (Honorarios)',
        ];
        // --- FIN DE LA CORRECCIÓN ---

        $prefill_empleado_id = old('id_empleado', $request->query('id_empleado'));
        $prefill_patron_id = old('id_patron');
        $prefill_tipo_contrato = old('tipo_contrato');

        if ($prefill_empleado_id && !old('id_patron')) {
            $empleadoSeleccionado = Empleado::find($prefill_empleado_id);
            if ($empleadoSeleccionado && $ultimoContrato = $empleadoSeleccionado->ultimoContrato) {
                $prefill_patron_id = old('id_patron', $ultimoContrato->id_patron);
                $prefill_tipo_contrato = old('tipo_contrato', $ultimoContrato->tipo_contrato);
            }
        }

        return view('contratos.create', compact(
            'empleados', 
            'patrones',
            'tipos_contrato',
            'prefill_empleado_id',
            'prefill_patron_id',
            'prefill_tipo_contrato'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'id_patron' => 'required|exists:patrones,id_patron',
            'tipo_contrato' => 'required|string|max:50',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $patronSeleccionado = Patron::find($validatedData['id_patron']);
        
        $datosContrato = $validatedData;
        $datosContrato['patron_tipo'] = $patronSeleccionado->tipo_persona;

        Contrato::create($datosContrato);

        return redirect()->route('contratos.index')->with('success', '¡Contrato registrado exitosamente!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contrato $contrato)
    {
        $contrato->load('empleado');
        $empleadoDelContrato = $contrato->empleado;

$tipos_contrato = [
    'Determinado' => 'Contrato por Tiempo Determinado',
    'Indeterminado' => 'Contrato por Tiempo Indeterminado',
    'Sueldo Variable' => 'Contrato por Sueldo Variable',
    'Honorarios' => 'Prestación de Servicios (Honorarios)',
];
        
        $tipos_patron = [
            'fisica' => 'Persona Física',
            'moral' => 'Persona Moral',
        ];

        return view('contratos.edit', compact(
            'contrato',
            'empleadoDelContrato',
            'tipos_contrato',
            'tipos_patron'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contrato $contrato)
    {
        $validatedData = $request->validate([
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'patron_tipo' => 'required|string|in:fisica,moral',
            'tipo_contrato' => 'required|string|max:50',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $contrato->fill($validatedData); 
        $contrato->save();

        return redirect()->route('contratos.index')
                         ->with('success', '¡Contrato de ' . $contrato->empleado->nombre_completo . ' actualizado exitosamente!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contrato $contrato)
    {
        $nombreEmpleado = $contrato->empleado ? $contrato->empleado->nombre_completo : 'Empleado Desconocido';
        $tipoContrato = $contrato->tipo_contrato;

        $contrato->delete();

        return redirect()->route('contratos.index')
                         ->with('success', 'Contrato tipo '.$tipoContrato.' de '.$nombreEmpleado.' eliminado exitosamente.');
    }

    /**
     * Exporta la vista actual del panorama contractual a un archivo Excel.
     */
    public function exportarExcel(Request $request)
    {
        $search_nombre_empleado = $request->query('search_nombre_empleado');
        $id_sucursal_filter = $request->query('id_sucursal_filter');
        
        $fileName = 'panorama_contractual_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new ContratosPanoramaExport($search_nombre_empleado, $id_sucursal_filter), $fileName);
    }

    /**
     * MÉTODO CORREGIDO Y FINAL: Genera el PDF asegurándose de cargar los datos más recientes.
     */
     public function generarPdf(Contrato $contrato)
    {
        $contrato->refresh();
        $contrato->load('empleado.puesto', 'empleado.sucursal', 'patron', 'empleado.contratos');

        if (!$contrato->patron) {
            return back()->with('error', 'Error: El contrato no tiene un patrón asociado y no se puede generar el PDF.');
        }

        $empleado = $contrato->empleado;
        $contratos_totales_empleado = $empleado->contratos->count();

        $data = [
            'contrato' => $contrato,
            'empleado' => $empleado,
            'patron'   => $contrato->patron,
        ];
        
        $nombreEmpleadoFormateado = Str::slug($contrato->empleado->nombre_completo, '_');

        if ($contratos_totales_empleado === 1) {
            // --- ES EL PRIMER CONTRATO: GENERAR ZIP CON AMBOS DOCUMENTOS ---

            // 1. Asegurar que el directorio temporal exista
            $tempPath = storage_path('app/temp_contratos'); // Usamos una carpeta diferente para más orden
            if (!File::isDirectory($tempPath)) {
                File::makeDirectory($tempPath, 0755, true, true);
            }

            // 2. Definir nombres de archivo
            $nombrePdfPrincipal = 'contrato-' . $nombreEmpleadoFormateado . '.pdf';
            $nombrePdfConfidencialidad = 'contrato-confidencialidad-' . $nombreEmpleadoFormateado . '.pdf';
            $nombreZip = 'paquete-contrato-' . $nombreEmpleadoFormateado . '.zip';
            
            $rutaPdfPrincipal = $tempPath . '/' . $nombrePdfPrincipal;
            $rutaPdfConfidencialidad = $tempPath . '/' . $nombrePdfConfidencialidad;
            $rutaZip = $tempPath . '/' . $nombreZip;

            // 3. Generar y GUARDAR los dos PDFs
            $mapaDePlantillas = [
                'Determinado'     => 'generico', 'Indeterminado'   => 'generico',
                'Honorarios'      => 'generico', 'Sueldo Variable' => 'sueldo_variable',
            ];
            $nombrePlantilla = $mapaDePlantillas[$contrato->tipo_contrato] ?? 'generico';
            Pdf::loadView('contratos.pdf_templates.' . $nombrePlantilla, $data)->save($rutaPdfPrincipal);
            Pdf::loadView('contratos.pdf_templates.confidencialidad', $data)->save($rutaPdfConfidencialidad);

            // 4. Crear el archivo ZIP
            $zip = new ZipArchive;
            if ($zip->open($rutaZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                $zip->addFile($rutaPdfPrincipal, $nombrePdfPrincipal);
                $zip->addFile($rutaPdfConfidencialidad, $nombrePdfConfidencialidad);
                $zip->close();
            }

            // 5. Borrar los PDFs individuales que ya están dentro del ZIP
            File::delete($rutaPdfPrincipal);
            File::delete($rutaPdfConfidencialidad);

            // 6. Descargar el ZIP y decirle a Laravel que lo borre del servidor después de la descarga
            return response()->download($rutaZip)->deleteFileAfterSend(true);

        } else {
            // --- NO ES EL PRIMER CONTRATO: DESCARGAR PDF ÚNICO ---
            $mapaDePlantillas = [
                'Determinado'     => 'generico', 'Indeterminado'   => 'generico',
                'Honorarios'      => 'generico', 'Sueldo Variable' => 'sueldo_variable',
            ];
            $nombrePlantilla = $mapaDePlantillas[$contrato->tipo_contrato] ?? 'generico';
            $vistaPdf = 'contratos.pdf_templates.' . $nombrePlantilla;
            
            $nombrePdf = 'contrato-' . $nombreEmpleadoFormateado . '-' . $contrato->id_contrato . '.pdf';
            
            $pdf = Pdf::loadView($vistaPdf, $data);

            // Volvemos al método .stream() original, que sabemos que funciona para PDFs únicos
            return $pdf->stream($nombrePdf);
        }
    }
}
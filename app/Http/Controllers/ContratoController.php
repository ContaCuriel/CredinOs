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
use Illuminate\Support\Facades\Storage; // Aseguramos que Storage esté importado

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
        
        $tipos_contrato = [
            'Determinado' => 'Contrato por Tiempo Determinado',
            'Indeterminado' => 'Contrato por Tiempo Indeterminado',
            'Sueldo Variable' => 'Contrato por Sueldo Variable',
            'Honorarios' => 'Prestación de Servicios (Honorarios)',
        ];

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
        
        // No necesitas cargar patrones aquí, ya que el patrón no se edita, solo su tipo.
        // Si necesitaras editar el patrón, tendrías que pasar la lista de patrones aquí.
        
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
     * ================== MÉTODO MODIFICADO ==================
     */
    public function update(Request $request, Contrato $contrato)
    {
        // 1. Validación de datos, incluyendo el archivo opcional
        $validatedData = $request->validate([
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'patron_tipo' => 'required|string|in:fisica,moral',
            'tipo_contrato' => 'required|string|max:50',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            // El archivo es opcional, debe ser PDF, y no pesar más de 5MB.
            'contrato_firmado_file' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        // 2. Preparamos los datos a actualizar, excluyendo el archivo por ahora.
        $dataToUpdate = $request->except(['_token', '_method', 'contrato_firmado_file']);

        // 3. Lógica para manejar la subida del archivo
        if ($request->hasFile('contrato_firmado_file')) {
            // Si ya existía un archivo para este contrato, lo borramos para no acumular basura.
            if ($contrato->ruta_contrato_firmado) {
                Storage::disk('public')->delete($contrato->ruta_contrato_firmado);
            }

            // Guardamos el nuevo archivo en 'storage/app/public/contratos_firmados'
            // y obtenemos la ruta para guardarla en la base de datos.
            $path = $request->file('contrato_firmado_file')->store('contratos_firmados', 'public');
            $dataToUpdate['ruta_contrato_firmado'] = $path;
        }

        // 4. Actualizamos el contrato en la base de datos con todos los datos.
        $contrato->update($dataToUpdate);

        return redirect()->route('contratos.index')
                         ->with('success', '¡Contrato de ' . $contrato->empleado->nombre_completo . ' actualizado exitosamente!');
    }

    /**
     * Remove the specified resource from storage.
     * ================== MÉTODO MEJORADO ==================
     */
    public function destroy(Contrato $contrato)
    {
        $nombreEmpleado = $contrato->empleado ? $contrato->empleado->nombre_completo : 'Empleado Desconocido';
        $tipoContrato = $contrato->tipo_contrato;

        // --- MEJORA ---
        // Antes de borrar el registro, borramos el archivo físico si existe.
        if ($contrato->ruta_contrato_firmado) {
            Storage::disk('public')->delete($contrato->ruta_contrato_firmado);
        }
        // --- FIN DE LA MEJORA ---

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
     * Genera el PDF del contrato. (Este método no se modifica)
     */
    public function generarPdf(Contrato $contrato)
    {
        // ... (Tu lógica de generación de PDF y ZIP se mantiene igual)
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
            $tempPath = storage_path('app/temp_contratos');
            if (!File::isDirectory($tempPath)) {
                File::makeDirectory($tempPath, 0755, true, true);
            }

            $nombrePdfPrincipal = 'contrato-' . $nombreEmpleadoFormateado . '.pdf';
            $nombrePdfConfidencialidad = 'contrato-confidencialidad-' . $nombreEmpleadoFormateado . '.pdf';
            $nombreZip = 'paquete-contrato-' . $nombreEmpleadoFormateado . '.zip';
            
            $rutaPdfPrincipal = $tempPath . '/' . $nombrePdfPrincipal;
            $rutaPdfConfidencialidad = $tempPath . '/' . $nombrePdfConfidencialidad;
            $rutaZip = $tempPath . '/' . $nombreZip;

            $mapaDePlantillas = [
                'Determinado'     => 'generico', 'Indeterminado'    => 'generico',
                'Honorarios'      => 'generico', 'Sueldo Variable' => 'sueldo_variable',
            ];
            $nombrePlantilla = $mapaDePlantillas[$contrato->tipo_contrato] ?? 'generico';
            Pdf::loadView('contratos.pdf_templates.' . $nombrePlantilla, $data)->save($rutaPdfPrincipal);
            Pdf::loadView('contratos.pdf_templates.confidencialidad', $data)->save($rutaPdfConfidencialidad);

            $zip = new ZipArchive;
            if ($zip->open($rutaZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                $zip->addFile($rutaPdfPrincipal, $nombrePdfPrincipal);
                $zip->addFile($rutaPdfConfidencialidad, $nombrePdfConfidencialidad);
                $zip->close();
            }

            File::delete($rutaPdfPrincipal);
            File::delete($rutaPdfConfidencialidad);

            return response()->download($rutaZip)->deleteFileAfterSend(true);
        } else {
            $mapaDePlantillas = [
                'Determinado'     => 'generico', 'Indeterminado'    => 'generico',
                'Honorarios'      => 'generico', 'Sueldo Variable' => 'sueldo_variable',
            ];
            $nombrePlantilla = $mapaDePlantillas[$contrato->tipo_contrato] ?? 'generico';
            $vistaPdf = 'contratos.pdf_templates.' . $nombrePlantilla;
            
            $nombrePdf = 'contrato-' . $nombreEmpleadoFormateado . '-' . $contrato->id_contrato . '.pdf';
            
            $pdf = Pdf::loadView($vistaPdf, $data);
            return $pdf->stream($nombrePdf);
        }
    }
    
    /**
     * ================== MÉTODO NUEVO AÑADIDO ==================
     * Permite ver/descargar el contrato firmado que ha sido subido.
     */
    public function verContratoFirmado($id)
    {
        $contrato = Contrato::findOrFail($id);

        // Verifica que el contrato tenga una ruta guardada y que el archivo exista físicamente
        if ($contrato->ruta_contrato_firmado && Storage::disk('public')->exists($contrato->ruta_contrato_firmado)) {
            // Devuelve el archivo para que el navegador lo muestre (inline)
            return Storage::disk('public')->response($contrato->ruta_contrato_firmado);
        }

        // Si no se encuentra el archivo, redirige a la página anterior con un mensaje de error.
        return back()->with('error', 'El archivo del contrato firmado no se encontró o ha sido eliminado.');
    }
}
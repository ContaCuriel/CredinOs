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
        // 1. Forzar la recarga de datos frescos desde la base de datos
        $contrato->refresh();
        
        // 2. Cargar las relaciones necesarias para el PDF
        $contrato->load('empleado.puesto', 'empleado.sucursal', 'patron');

        // 3. Verificar que el contrato tiene un patrón asociado
        if (!$contrato->patron) {
            return back()->with('error', 'Error: El contrato no tiene un patrón asociado y no se puede generar el PDF.');
        }

        // 4. Definir el mapeo de tipos de contrato a plantillas de PDF
        $mapaDePlantillas = [
            'Determinado'     => 'generico',
            'Indeterminado'   => 'generico',
            'Honorarios'      => 'generico',
            'Sueldo Variable' => 'sueldo_variable',
        ];

        // 5. Seleccionar la plantilla correcta. Si no se encuentra, usar 'generico' por defecto.
        $nombrePlantilla = $mapaDePlantillas[$contrato->tipo_contrato] ?? 'generico';
        $vistaPdf = 'contratos.pdf_templates.' . $nombrePlantilla;
        
        // 6. Preparar los datos que se enviarán a la vista del PDF
        $data = [
            'contrato' => $contrato,
            'empleado' => $contrato->empleado,
            'patron'   => $contrato->patron,
        ];

        // 7. Generar el nombre del archivo PDF
        $nombreEmpleadoFormateado = str_replace(' ', '_', $contrato->empleado->nombre_completo);
        $nombrePdf = 'contrato-' . $nombreEmpleadoFormateado . '-' . $contrato->id_contrato . '.pdf';
        
        // 8. Cargar la vista y generar el PDF
        $pdf = Pdf::loadView($vistaPdf, $data);

        // 9. Descargar el archivo
        return $pdf->download($nombrePdf);
    }
}

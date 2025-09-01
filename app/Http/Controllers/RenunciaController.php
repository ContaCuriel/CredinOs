<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empleado;
use App\Models\Patron;
use Carbon\Carbon;
use Illuminate\Support\Str;
use PDF;

class RenunciaController extends Controller
{
    /**
     * Muestra el formulario para generar la carta de renuncia.
     */
    public function create()
    {
        // Obtenemos los mismos datos que en la vista de finiquitos
        $empleados = Empleado::orderBy('nombre_completo')->get();
        $patrones = Patron::orderBy('nombre_comercial')->get();

        // Devolvemos una nueva vista dedicada a las renuncias
        return view('renuncias.create', compact('empleados', 'patrones'));
    }

    /**
     * Genera el PDF de la carta de renuncia.
     * (Esta es la lógica que movimos desde FiniquitoController)
     */
    public function exportarPdf(Request $request)
    {
        // 1. Validar los datos necesarios
        $validatedData = $request->validate([
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'id_patron' => 'required|exists:patrones,id_patron',
            'fecha_baja' => 'required|date', // Usamos 'fecha_baja' como el nombre del campo en el nuevo form
        ]);

        // 2. Obtener los modelos con sus relaciones
        $empleado = Empleado::with(['puesto', 'sucursal', 'ultimoContrato'])->findOrFail($validatedData['id_empleado']);
        $patron = Patron::findOrFail($validatedData['id_patron']);
        
        // Usamos las fechas del empleado y la proporcionada en el formulario
        $fechaIngreso = Carbon::parse($empleado->fecha_ingreso);
        $fechaBaja = Carbon::parse($validatedData['fecha_baja']);

        // 3. Lógica de verificación de contrato
        $esContratoDeHonorarios = false;
        if ($empleado->ultimoContrato && Str::contains(strtolower($empleado->ultimoContrato->tipo_contrato), 'honorarios')) {
            $esContratoDeHonorarios = true;
        }

        // 4. Formatear fechas a texto largo en español
        $fecha_ingreso_letra = $fechaIngreso->translatedFormat('d \de F \de Y');
        $fecha_baja_letra = $fechaBaja->translatedFormat('d \de F \de Y');
        $lugar_y_fecha_emision = ($empleado->sucursal->municipio ?? 'Ciudad') . ', ' . ($empleado->sucursal->estado ?? 'Estado') . ' a ' . $fechaBaja->translatedFormat('d \de F \de Y');


        // 5. Preparar los datos para la vista
        $data = [
            'empleado' => $empleado,
            'patron' => $patron,
            'fecha_ingreso_letra' => $fecha_ingreso_letra,
            'fecha_baja_letra' => $fecha_baja_letra,
            'lugar_y_fecha_emision' => $lugar_y_fecha_emision,
            'esContratoDeHonorarios' => $esContratoDeHonorarios,
        ];

        // 6. Generar el PDF
        $nombreArchivo = 'carta_renuncia_' . Str::slug($empleado->nombre_completo) . '.pdf';
        $pdf = PDF::loadView('renuncias.pdf_renuncia', $data); // Usaremos una vista de renuncia separada

        return $pdf->stream($nombreArchivo);
    }
}
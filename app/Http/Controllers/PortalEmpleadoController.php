<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empleado;
use App\Models\NominaTimbrada;
use App\Services\FacturamaService;

class PortalEmpleadoController extends Controller
{
    protected $facturama;

    public function __construct(FacturamaService $facturama)
    {
        $this->facturama = $facturama;
    }

    public function login()
    {
        // Si ya hay sesión activa, entra directo
        if (session()->has('empleado_id')) {
            return redirect()->route('portal.dashboard');
        }
        return view('portal_empleado.login');
    }

    public function acceder(Request $request)
    {
        $request->validate([
            'rfc' => 'required|string',
            'id_empleado' => 'required|integer',
        ]);

        // Buscamos empleado por RFC (sin importar mayúsculas) y su ID
        $empleado = Empleado::where('rfc', strtoupper(trim($request->rfc)))
                            ->where('id_empleado', $request->id_empleado)
                            ->first();

        if ($empleado) {
            session(['empleado_id' => $empleado->id_empleado]);
            return redirect()->route('portal.dashboard');
        }

        // Mensaje genérico por seguridad
        return back()->with('error', 'El RFC o el Número de Empleado son incorrectos.');
    }

    public function dashboard()
    {
        $empleadoId = session('empleado_id');
        $empleado = Empleado::findOrFail($empleadoId);

        // Traemos las nóminas del empleado ordenadas por fecha de timbrado
        $todasLasNominas = NominaTimbrada::with(['detalle', 'detalle.periodo'])
                            ->where('id_empleado', $empleadoId)
                            ->where('estado_timbrado', 'timbrado')
                            ->orderBy('fecha_timbrado', 'desc')
                            ->get();

        // "La Foto" (El recibo más nuevo)
        $quincenaActual = $todasLasNominas->first();

        // El Historial (Todos los demás)
        $historial = $todasLasNominas->skip(1);

        return view('portal_empleado.dashboard', compact('empleado', 'quincenaActual', 'historial'));
    }

    public function salir()
    {
        session()->forget('empleado_id');
        return redirect()->route('portal.login')->with('success', 'Sesión cerrada correctamente.');
    }

    public function descargarPdf($id_detalle)
    {
        $empleadoId = session('empleado_id');
        
        // Bloqueo de seguridad: Validamos que el recibo sea del empleado logueado
        $nomina = NominaTimbrada::where('id_detalle_lista', $id_detalle)
                                ->where('id_empleado', $empleadoId) 
                                ->firstOrFail();

        if (!$nomina->facturama_id) {
            return back()->with('error', 'El archivo no está disponible en este momento.');
        }

        $response = $this->facturama->getFile($nomina->facturama_id, 'pdf'); 

        if ($response->successful()) {
            $data = $response->json();
            $pdfDecoded = base64_decode($data['Content']);
            
            return response($pdfDecoded)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="Nomina_' . $nomina->uuid_cfdi . '.pdf"');
        }

        return back()->with('error', 'Error al descargar el PDF.');
    }

    public function descargarXml($id_detalle)
    {
        $empleadoId = session('empleado_id');
        
        $nomina = NominaTimbrada::where('id_detalle_lista', $id_detalle)
                                ->where('id_empleado', $empleadoId)
                                ->firstOrFail();

        if (!$nomina->facturama_id) {
            return back()->with('error', 'El archivo no está disponible en este momento.');
        }

        $response = $this->facturama->getFile($nomina->facturama_id, 'xml'); 

        if ($response->successful()) {
            $data = $response->json();
            $xmlDecoded = base64_decode($data['Content']);
            
            return response($xmlDecoded)
                ->header('Content-Type', 'application/xml')
                ->header('Content-Disposition', 'attachment; filename="Nomina_' . $nomina->uuid_cfdi . '.xml"');
        }

        return back()->with('error', 'Error al descargar el XML.');
    }
}
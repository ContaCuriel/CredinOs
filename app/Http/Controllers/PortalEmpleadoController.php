<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empleado;
use App\Models\ListaRayaDetalle;
use App\Models\NominaTimbrada;
use App\Models\Patron;
use App\Services\FacturamaService;
use Illuminate\Support\Facades\Schema;

class PortalEmpleadoController extends Controller
{
    protected $facturama;

    public function __construct(FacturamaService $facturama)
    {
        $this->facturama = $facturama;
    }

    public function login()
    {
        if (session()->has('empleado_id')) {
            return redirect()->route('portal.dashboard');
        }

        $logos = collect();
        if (Schema::hasTable('patrones')) {
            $columnaLogo = null;

            if (Schema::hasColumn('patrones', 'logo_path')) {
                $columnaLogo = 'logo_path';
            } elseif (Schema::hasColumn('patrones', 'ruta_logo')) {
                $columnaLogo = 'ruta_logo';
            } elseif (Schema::hasColumn('patrones', 'logo')) {
                $columnaLogo = 'logo';
            }

            if ($columnaLogo) {
                $logos = Patron::whereNotNull($columnaLogo)
                               ->where($columnaLogo, '!=', '')
                               ->pluck($columnaLogo);
            }
        }

        return view('portal_empleado.login', compact('logos'));
    }

    public function acceder(Request $request)
    {
        $request->validate([
            'rfc' => 'required|string',
            'id_empleado' => 'required|integer',
        ]);

        $empleado = Empleado::where('rfc', strtoupper(trim($request->rfc)))
                            ->where('id_empleado', $request->id_empleado)
                            ->first();

        if ($empleado) {
            session(['empleado_id' => $empleado->id_empleado]);
            return redirect()->route('portal.dashboard');
        }

        return back()->with('error', 'El RFC o el Número de Usuario son incorrectos.');
    }

    public function dashboard()
    {
        if (!session()->has('empleado_id')) {
            return redirect()->route('portal.login')->with('error', 'Por favor, ingrese sus datos para continuar.');
        }

        $empleadoId = session('empleado_id');
        $empleado = Empleado::findOrFail($empleadoId);

        // Obtenemos los detalles de lista de raya cargando su periodo y su timbrado
        $registros = ListaRayaDetalle::with(['periodo', 'nominaTimbrada'])
                        ->where('id_empleado', $empleadoId)
                        ->latest('created_at')
                        ->get();

        $todasLasNominas = $registros->map(function ($det) {
            // Formato de texto del periodo
            $rangoOriginal = $det->periodo->periodo_rango 
                            ?? $det->periodo->nombre 
                            ?? $det->periodo_rango 
                            ?? '';

            $det->periodo_formateado = $this->formatearPeriodo($rangoOriginal);

            // Mapeo directo contra los atributos reales de ListaRayaDetalle
            $sueldoCalculado = ($det->sueldo_diario_historico && $det->dias_periodo) 
                ? ($det->sueldo_diario_historico * $det->dias_periodo) 
                : (($det->sueldo_mensual_historico) ? ($det->sueldo_mensual_historico / 2) : 0);

            $det->val_sueldo = $sueldoCalculado;
            $det->val_caja = $det->deduccion_caja_ahorro ?? 0;
            $det->val_infonavit = $det->deduccion_infonavit ?? 0;
            $det->val_isr = $det->deduccion_isr ?? 0;
            $det->val_imss = $det->deduccion_imss ?? 0;
            $det->val_neto = $det->total_neto ?? 0;

            if (!$det->relationLoaded('nominaTimbrada') || !$det->nominaTimbrada) {
                $det->setRelation('nominaTimbrada', NominaTimbrada::where('id_detalle_lista', $det->id_detalle_lista)->first());
            }

            return $det;
        });

        $quincenaActual = $todasLasNominas->first();
        $historial = $todasLasNominas->skip(1);

        return view('portal_empleado.dashboard', compact('empleado', 'quincenaActual', 'historial'));
    }

    private function formatearPeriodo($texto)
    {
        if (empty($texto)) return 'Periodo Actual';

        // Convierte el formato 2026-07-16_2026-07-31 a "2da Quincena julio 2026"
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})_(\d{4})-(\d{2})-(\d{2})$/', trim($texto), $matches)) {
            $year = $matches[1];
            $month = (int)$matches[2];
            $dayStart = (int)$matches[3];

            $meses = [
                1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
                5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
                9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
            ];

            $quincena = ($dayStart > 1) ? '2da Quincena' : '1ra Quincena';
            $nombreMes = $meses[$month] ?? '';

            return "{$quincena} {$nombreMes} {$year}";
        }

        return $texto;
    }

    public function salir()
    {
        session()->forget('empleado_id');
        return redirect()->route('portal.login')->with('success', 'Sesión cerrada correctamente.');
    }

    public function descargarPdf($id_detalle)
    {
        if (!session()->has('empleado_id')) {
            return redirect()->route('portal.login')->with('error', 'Por favor, ingrese sus datos para continuar.');
        }

        $empleadoId = session('empleado_id');
        
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
                ->header('Content-Disposition', 'inline; filename="Comprobante_' . $nomina->uuid_cfdi . '.pdf"');
        }

        return back()->with('error', 'Error al descargar el PDF.');
    }

    public function descargarXml($id_detalle)
    {
        if (!session()->has('empleado_id')) {
            return redirect()->route('portal.login')->with('error', 'Por favor, ingrese sus datos para continuar.');
        }

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
                ->header('Content-Disposition', 'attachment; filename="Comprobante_' . $nomina->uuid_cfdi . '.xml"');
        }

        return back()->with('error', 'Error al descargar el XML.');
    }
}
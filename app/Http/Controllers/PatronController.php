<?php

namespace App\Http\Controllers;

use App\Models\Patron;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Para manejar archivos (logo)
use Illuminate\Support\Str;              // Para generar nombres de archivo
use App\Services\FacturamaService;
use Carbon\Carbon;

class PatronController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtenemos todos los patrones, ordenados por razón social
        $patrones = Patron::orderBy('razon_social', 'asc')->paginate(10);

        // Pasamos la colección de patrones a la vista
        return view('patrones.index', compact('patrones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tipos_persona = [
            'fisica' => 'Persona Física',
            'moral' => 'Persona Moral',
        ];

        return view('patrones.create', compact('tipos_persona'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre_comercial' => 'required|string|max:255',
            'razon_social' => 'required|string|max:255|unique:patrones,razon_social',
            'tipo_persona' => 'required|string|in:fisica,moral',
            'rfc' => 'required|string|max:13|unique:patrones,rfc',
            'direccion_fiscal' => 'nullable|string|max:1000',
            'actividad_principal' => 'nullable|string|max:500',
            'representante_legal' => 'nullable|string|max:255',
            'logo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            
            // 🔥 NUEVOS CAMPOS FISCALES OBLIGATORIOS PARA CFDI 4.0
            'registro_patronal' => 'nullable|string|max:20',
            'regimen_fiscal' => 'required|string|max:5',
            'codigo_postal' => 'required|string|size:5',
        ]);

        if ($request->hasFile('logo_path')) {
            $logoNombre = Str::slug($validatedData['razon_social']) . '_' . time() . '.' . $request->file('logo_path')->getClientOriginalExtension();
            $path = $request->file('logo_path')->storeAs('patron_logos', $logoNombre, 'public');
            $validatedData['logo_path'] = $path;
        }

        Patron::create($validatedData);

        return redirect()->route('patrones.index')
                         ->with('success', '¡Patrón registrado exitosamente!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Patron $patron)
    {
        // Por ahora, no se usa.
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patron $patron)
    {
        return view('patrones.edit', compact('patron'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Patron $patron)
    {
        $validatedData = $request->validate([
            'nombre_comercial' => 'required|string|max:255',
            // OJO: Le decimos a Laravel que ignore el RFC y Razón Social de ESTE mismo patrón para que no marque error de duplicado
            'razon_social' => 'required|string|max:255|unique:patrones,razon_social,' . $patron->id_patron . ',id_patron',
            'tipo_persona' => 'required|string|in:fisica,moral',
            'rfc' => 'required|string|max:13|unique:patrones,rfc,' . $patron->id_patron . ',id_patron',
            'direccion_fiscal' => 'nullable|string|max:1000',
            'actividad_principal' => 'nullable|string|max:500',
            'representante_legal' => 'nullable|string|max:255',
            'logo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            
            // CAMPOS FISCALES CFDI 4.0
            'registro_patronal' => 'nullable|string|max:20',
            'regimen_fiscal' => 'required|string|max:5',
            'codigo_postal' => 'required|string|size:5',
        ]);

        // Manejo del reemplazo de logo
        if ($request->hasFile('logo_path')) {
            // Si ya tenía un logo, lo borramos del servidor para no dejar basura
            if ($patron->logo_path) {
                Storage::disk('public')->delete($patron->logo_path);
            }

            $logoNombre = Str::slug($validatedData['razon_social']) . '_' . time() . '.' . $request->file('logo_path')->getClientOriginalExtension();
            $path = $request->file('logo_path')->storeAs('patron_logos', $logoNombre, 'public');
            $validatedData['logo_path'] = $path;
        }

        // Actualizamos en base de datos
        $patron->update($validatedData);

        return redirect()->route('patrones.index')
                         ->with('success', '¡Patrón actualizado exitosamente!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patron $patron)
    {
        try {
            // Si tiene un logo, lo borramos de la carpeta
            if ($patron->logo_path) {
                Storage::disk('public')->delete($patron->logo_path);
            }
            
            $patron->delete();
            
            return redirect()->route('patrones.index')
                             ->with('success', '¡Patrón eliminado exitosamente!');
        } catch (\Exception $e) {
            // Protegemos el sistema: si el patrón ya tiene contratos o empleados, la BD no dejará borrarlo.
            return redirect()->route('patrones.index')
                             ->with('error', 'No se puede eliminar el patrón porque tiene contratos, recibos o empleados asociados.');
        }
    }

    // --- NUEVOS MÉTODOS PARA MANEJAR EL LOGO ---

    /**
     * Muestra el formulario para editar únicamente el logo de un patrón.
     */
    public function editLogo(Patron $patron)
    {
        return view('patrones.logo', compact('patron'));
    }

    /**
     * Actualiza únicamente el logo de un patrón en la base de datos.
     */
    public function updateLogo(Request $request, Patron $patron)
    {
        $request->validate([
            'logo_path' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ],[
            'logo_path.required' => 'Debes seleccionar un archivo de imagen.',
            'logo_path.image' => 'El archivo debe ser una imagen.',
            'logo_path.mimes' => 'El logo debe ser un archivo de tipo: jpeg, png, jpg, gif.',
            'logo_path.max' => 'El logo no debe pesar más de 2MB.',
        ]);

        // Borrar el logo antiguo si existe
        if ($patron->logo_path) {
            Storage::disk('public')->delete($patron->logo_path);
        }

        // Subir el nuevo logo
        $logoNombre = Str::slug($patron->razon_social) . '_' . time() . '.' . $request->file('logo_path')->getClientOriginalExtension();
        $path = $request->file('logo_path')->storeAs('patron_logos', $logoNombre, 'public');

        // Actualizar el registro en la base de datos
        $patron->logo_path = $path;
        $patron->save();

        return redirect()->route('patrones.index')->with('success', 'Logo actualizado exitosamente.');
    }

   /**
     * Sube el CSD de un Patrón a Facturama y lo guarda en el servidor.
     */
    public function storeCsd(Request $request, Patron $patron, FacturamaService $facturama)
    {
        $request->validate([
            'csd_cer' => 'required|file|extensions:cer',
            'csd_key' => 'required|file|extensions:key',
            'csd_password' => 'required|string',
        ]);

        $folder = "csd/patron_{$patron->id_patron}";
        
        $cerPath = $request->file('csd_cer')->store($folder, 'private');
        $keyPath = $request->file('csd_key')->store($folder, 'private');
        $csdPassword = $request->csd_password;

        // Lectura de la vigencia del certificado
        $cerContentRaw = file_get_contents($request->file('csd_cer')->getRealPath());
        $pemContent = "-----BEGIN CERTIFICATE-----\n" . chunk_split(base64_encode($cerContentRaw), 64, "\n") . "-----END CERTIFICATE-----\n";
        
        $certInfo = openssl_x509_parse($pemContent);
        $expiresAt = null;
        if ($certInfo && isset($certInfo['validTo_time_t'])) {
            $expiresAt = Carbon::createFromTimestamp($certInfo['validTo_time_t']);
        }

        $patron->update([
            'csd_cer_path' => $cerPath,
            'csd_key_path' => $keyPath,
            'csd_password' => $csdPassword,
            'csd_expires_at' => $expiresAt,
        ]);

        $cerContent = Storage::disk('private')->get($cerPath);
        $keyContent = Storage::disk('private')->get($keyPath);

        // Envío a Facturama API-Lite
        $response = $facturama->uploadCsd($patron->rfc, $cerContent, $keyContent, $csdPassword);

        if ($response->failed()) {
            return back()->with('error', 'Falló la sincronización con Facturama. Detalles: ' . $response->body());
        }

        return redirect()->route('patrones.index')
                         ->with('success', '¡Certificados (CSD) sincronizados con Facturama! Caducidad: ' . ($expiresAt ? $expiresAt->format('d/m/Y') : 'Desconocida'));
    }
}


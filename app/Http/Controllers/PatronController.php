<?php

namespace App\Http\Controllers;

use App\Models\Patron;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Para manejar archivos (logo)
use Illuminate\Support\Str;             // Para generar nombres de archivo

class PatronController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtenemos todos los patrones, ordenados por razón social
        // Usamos paginación por si tienes muchos
        $patrones = Patron::orderBy('razon_social', 'asc')->paginate(10); // Muestra 10 por página

        // Pasamos la colección de patrones a la vista
        return view('patrones.index', compact('patrones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Definimos las opciones para el tipo de persona
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
        // 1. Validación de los datos del formulario
        $validatedData = $request->validate([
            'nombre_comercial' => 'required|string|max:255',
            'razon_social' => 'required|string|max:255|unique:patrones,razon_social',
            'tipo_persona' => 'required|string|in:fisica,moral',
            'rfc' => 'required|string|max:13|unique:patrones,rfc',
            'direccion_fiscal' => 'nullable|string|max:1000',
            'actividad_principal' => 'nullable|string|max:500',
            'representante_legal' => 'nullable|string|max:255',
            'logo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Logo opcional, imagen, tipos y tamaño máx 2MB
        ],[
            'nombre_comercial.required' => 'El nombre comercial es obligatorio.',
            'razon_social.required' => 'La razón social es obligatoria.',
            'razon_social.unique' => 'Esta razón social ya está registrada.',
            'tipo_persona.required' => 'Debe seleccionar el tipo de persona.',
            'rfc.required' => 'El RFC es obligatorio.',
            'rfc.unique' => 'Este RFC ya está registrado.',
            'logo_path.image' => 'El archivo del logo debe ser una imagen.',
            'logo_path.mimes' => 'El logo debe ser un archivo de tipo: jpeg, png, jpg, gif.',
            'logo_path.max' => 'El logo no debe pesar más de 2MB.',
        ]);

        // 2. Manejo de la Subida del Logo (si se proporcionó)
        if ($request->hasFile('logo_path')) {
            // Generar un nombre único para el archivo
            $logoNombre = Str::slug($validatedData['razon_social']) . '_' . time() . '.' . $request->file('logo_path')->getClientOriginalExtension();
            // Guardar el archivo en storage/app/public/patron_logos
            // Asegúrate de que la carpeta 'patron_logos' exista o créala
            $path = $request->file('logo_path')->storeAs('patron_logos', $logoNombre, 'public');
            $validatedData['logo_path'] = $path; // Guardamos la ruta del archivo en los datos a crear
        }

        // 3. Creación del Patrón
        Patron::create($validatedData);

        // 4. Redirección con Mensaje de Éxito
        return redirect()->route('patrones.index')
                         ->with('success', '¡Patrón registrado exitosamente!');
    } // <--- ESTA ES LA LLAVE DE CIERRE DEL MÉTODO store()

    /**
     * Display the specified resource.
     */
    public function show(Patron $patron)
    {
        // Lo implementaremos después si es necesario (ej: una vista de detalle del patrón)
        // Por ahora, puedes redirigir a la lista o a la edición:
        // return redirect()->route('patrones.edit', $patron->id_patron);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patron $patron)
{
    // Laravel nos pasa automáticamente la instancia del Patrón a editar.

    $tipos_persona = [
        'fisica' => 'Persona Física',
        'moral' => 'Persona Moral',
    ];

    return view('patrones.edit', compact('patron', 'tipos_persona'));
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Patron $patron)
    {
        // Lo implementaremos después
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patron $patron)
    {
        // Lo implementaremos después
    }

} // <--- ESTA ES LA LLAVE DE CIERRE FINAL DE LA CLASE PatronController
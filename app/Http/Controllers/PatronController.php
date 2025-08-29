<?php

namespace App\Http\Controllers;

use App\Models\Patron;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Para manejar archivos (logo)
use Illuminate\Support\Str;              // Para generar nombres de archivo

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
        // Esta función está actualmente deshabilitada por el problema de las rutas.
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Patron $patron)
    {
        // Esta función está actualmente deshabilitada.
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patron $patron)
    {
        // Esta función está actualmente deshabilitada.
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
}


<?php

namespace App\Http\Controllers;

use App\Models\Patron;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\PatronRequest; // Usaremos un Form Request para la validación

class PatronController extends Controller
{
    // Define los tipos de persona para no repetirlos en create y edit
    private function getTiposPersona()
    {
        return [
            'fisica' => 'Persona Física',
            'moral' => 'Persona Moral',
        ];
    }

    /**
     * Muestra una lista de todos los patrones.
     */
    public function index()
    {
        // Usamos paginate para manejar grandes cantidades de registros
        $patrones = Patron::orderBy('razon_social', 'asc')->paginate(10);
        return view('patrones.index', compact('patrones'));
    }

    /**
     * Muestra el formulario para crear un nuevo patrón.
     */
    public function create()
    {
        $tipos_persona = $this->getTiposPersona();
        return view('patrones.create', compact('tipos_persona'));
    }

    /**
     * Guarda un nuevo patrón en la base de datos.
     */
    public function store(PatronRequest $request)
    {
        $validatedData = $request->validated();

        if ($request->hasFile('logo_path')) {
            // Guarda el logo en 'public/logos' y guarda la ruta en la BD
            $path = $request->file('logo_path')->store('logos', 'public');
            $validatedData['logo_path'] = $path;
        }

        Patron::create($validatedData);

        return redirect()->route('patrones.index')
                         ->with('success', 'Patrón registrado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un patrón existente.
     */
    public function edit(Patron $patron)
    {
        $tipos_persona = $this->getTiposPersona();
        return view('patrones.edit', compact('patron', 'tipos_persona'));
    }

    /**
     * Actualiza un patrón existente en la base de datos.
     */
    public function update(PatronRequest $request, Patron $patron)
    {
        $validatedData = $request->validated();

        if ($request->hasFile('logo_path')) {
            // Si se sube un nuevo logo, eliminamos el anterior si existe
            if ($patron->logo_path) {
                Storage::disk('public')->delete($patron->logo_path);
            }
            // Guardamos el nuevo logo
            $path = $request->file('logo_path')->store('logos', 'public');
            $validatedData['logo_path'] = $path;
        }

        $patron->update($validatedData);

        return redirect()->route('patrones.index')
                         ->with('success', 'Patrón actualizado exitosamente.');
    }

    /**
     * Elimina un patrón de la base de datos.
     */
    public function destroy(Patron $patron)
    {
        try {
            // Si el patrón tiene un logo, lo eliminamos del almacenamiento
            if ($patron->logo_path) {
                Storage::disk('public')->delete($patron->logo_path);
            }

            $patron->delete();

            return redirect()->route('patrones.index')
                             ->with('success', 'Patrón eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Capturamos el error si el patrón no se puede eliminar por tener empleados asociados
            if ($e->getCode() === '23503') { // Código de error de PostgreSQL para violación de llave foránea
                return redirect()->route('patrones.index')
                                 ->with('error', 'No se puede eliminar el patrón porque tiene empleados asociados.');
            }
            // Para cualquier otro error de base de datos
            return redirect()->route('patrones.index')
                             ->with('error', 'Ocurrió un error al intentar eliminar el patrón.');
        }
    }
}


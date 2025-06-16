<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use Illuminate\Http\Request;

class SucursalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtenemos todas las sucursales, ordenadas por nombre
        // y usamos paginación por si tienes muchas
        $sucursales = Sucursal::orderBy('nombre_sucursal', 'asc')->paginate(10); // Muestra 10 por página

        // Pasamos la colección de sucursales a la vista
        return view('sucursales.index', compact('sucursales'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
{
    // Simplemente retornamos la vista del formulario de creación
    return view('sucursales.create');
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // 1. Validación de los datos del formulario
    $validatedData = $request->validate([
        'nombre_sucursal' => 'required|string|max:255|unique:sucursales,nombre_sucursal',
        'direccion_sucursal' => 'nullable|string|max:500',
        // 'telefono_sucursal' => 'nullable|string|max:20', // Eliminada validación
        // 'gerente_sucursal' => 'nullable|string|max:255', // Eliminada validación
    ],[
        'nombre_sucursal.required' => 'El nombre de la sucursal es obligatorio.',
        'nombre_sucursal.unique' => 'Este nombre de sucursal ya existe.',
    ]);

    // 2. Creación de la Sucursal
    Sucursal::create($validatedData);

    // 3. Redirección con Mensaje de Éxito
    return redirect()->route('sucursales.index')
                     ->with('success', '¡Sucursal registrada exitosamente!');
}

    /**
     * Display the specified resource.
     */
    public function show(Sucursal $sucursal)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sucursal $sucursal)
{
    // Para depurar, puedes añadir esto temporalmente:
    // dd($sucursal); // Esto detendrá la ejecución y te mostrará el contenido de $sucursal.
    // Asegúrate de que $sucursal sea un objeto del modelo Sucursal y tenga un id_sucursal.
    // Si ves esto, borra o comenta el dd($sucursal) para continuar.

    return view('sucursales.edit', compact('sucursal'));
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sucursal $sucursal)
{
    $validatedData = $request->validate([
        'nombre_sucursal' => 'required|string|max:255|unique:sucursales,nombre_sucursal,' . $sucursal->id_sucursal . ',id_sucursal',
        'direccion_sucursal' => 'nullable|string|max:500',
    ],[
        'nombre_sucursal.required' => 'El nombre de la sucursal es obligatorio.',
        'nombre_sucursal.unique' => 'Este nombre de sucursal ya existe.',
    ]);

    $sucursal->update($validatedData);

    return redirect()->route('sucursales.index')
                     ->with('success', '¡Sucursal "' . $sucursal->nombre_sucursal . '" actualizada exitosamente!');
}

    /**
     * Remove the specified resource from storage.
     */
/**
     * Remove the specified resource from storage.
     */
    public function destroy(Sucursal $sucursal)
    {
        try {
            $nombreSucursalEliminada = $sucursal->nombre_sucursal;
            $fueEliminada = $sucursal->delete(); // Intentamos eliminar

            if ($fueEliminada) {
                return redirect()->route('sucursales.index')
                                 ->with('success', '¡Sucursal "' . $nombreSucursalEliminada . '" eliminada exitosamente!');
            } else {
                // Si $fueEliminada es false, la eliminación fue prevenida silenciosamente
                return redirect()->route('sucursales.index')
                                 ->with('error', 'La sucursal no pudo ser eliminada. Verifique que no tenga empleados asignados o que no haya otra restricción interna.');
            }

        } catch (\Illuminate\Database\QueryException $e) {
            // Manejo de errores de base de datos (ej: restricciones de llave foránea)
            $errorCode = $e->errorInfo[1] ?? null; // Usar null coalescing para evitar error si errorInfo no está completo
            if ($errorCode == 1451) { // Error específico de restricción de llave foránea
                return redirect()->route('sucursales.index')
                                 ->with('error', 'No se pudo eliminar la sucursal. Asegúrate de que no tenga empleados u otros registros asociados activos.');
            }
            // Para otros errores de base de datos
            \Log::error("Error al eliminar sucursal: " . $e->getMessage()); // Guardar el error en logs
            return redirect()->route('sucursales.index')
                             ->with('error', 'Error de base de datos al intentar eliminar la sucursal. Consulte los logs para más detalles.');
        } catch (\Exception $e) {
            // Otro tipo de error inesperado
            \Log::error("Error inesperado al eliminar sucursal: " . $e->getMessage()); // Guardar el error en logs
            return redirect()->route('sucursales.index')
                             ->with('error', 'Ocurrió un error inesperado al intentar eliminar la sucursal. Consulte los logs para más detalles.');
        }
    }
}

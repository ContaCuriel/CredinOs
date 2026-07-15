<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use Illuminate\Http\Request;

class SucursalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
    {
        // Añadimos withoutGlobalScope para que el administrador ignore el bloqueo global
        $query = Sucursal::withoutGlobalScope('activa')->where('status', 'Activa');

        // (Opcional) Puedes añadir un filtro en el futuro para ver las inactivas
        // if ($request->input('status_filter') === 'inactivas') {
        //      $query = Sucursal::withoutGlobalScope('activa')->where('status', 'Inactiva');
        // }

        $sucursales = $query->orderBy('nombre_sucursal', 'asc')->paginate(10);
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
        $validatedData = $request->validate([
            'nombre_sucursal' => 'required|string|max:255|unique:sucursales,nombre_sucursal',
            'calle' => 'required|string|max:255',
            'numero' => 'required|string|max:50',
            'colonia' => 'required|string|max:255',
            'municipio' => 'required|string|max:255',
            'estado' => 'required|string|max:255',
        ]);
        Sucursal::create($validatedData);
        return redirect()->route('sucursales.index')->with('success', '¡Sucursal registrada exitosamente!');
    }


    /**
     * Display the specified resource.
     */
    public function show(Sucursal $sucursale) // <-- CORREGIDO: $sucursal -> $sucursale
    {
        return view('sucursales.show', compact('sucursale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
     public function edit(Sucursal $sucursale) // <-- CORREGIDO: $sucursal -> $sucursale
    {
        // Ahora la variable $sucursale tendrá el modelo correcto
        return view('sucursales.edit', compact('sucursale'));
    }

    /**
     * Update the specified resource in storage.
     */
     public function update(Request $request, Sucursal $sucursale) // <-- CORREGIDO: $sucursal -> $sucursale
    {
        $validatedData = $request->validate([
            'nombre_sucursal' => 'required|string|max:255|unique:sucursales,nombre_sucursal,' . $sucursale->id_sucursal . ',id_sucursal',
            'calle' => 'required|string|max:255',
            'numero' => 'required|string|max:50',
            'colonia' => 'required|string|max:255',
            'municipio' => 'required|string|max:255',
            'estado' => 'required|string|max:255',
        ]);

        $sucursale->update($validatedData);

        return redirect()->route('sucursales.index')
                         ->with('success', '¡Sucursal "' . $sucursale->nombre_sucursal . '" actualizada exitosamente!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sucursal $sucursale)
    {
        // Lógica de negocio: Verificamos si aún tiene empleados ACTIVOS
        $empleadosActivos = $sucursale->empleados()->where('status', 'Alta')->count();

        if ($empleadosActivos > 0) {
            return redirect()->route('sucursales.index')
                             ->with('error', 'No se puede desactivar la sucursal "' . $sucursale->nombre_sucursal . '" porque aún tiene empleados activos.');
        }

        // Cambiamos el estado en lugar de eliminar
        $sucursale->update(['status' => 'Inactiva']);

        return redirect()->route('sucursales.index')
                         ->with('success', '¡Sucursal "' . $sucursale->nombre_sucursal . '" desactivada exitosamente!');
    }

     public function reactivar(Sucursal $sucursale)
    {
        $sucursale->update(['status' => 'Activa']);

        return redirect()->route('sucursales.index')
                         ->with('success', '¡Sucursal "' . $sucursale->nombre_sucursal . '" reactivada exitosamente!');
    }
}
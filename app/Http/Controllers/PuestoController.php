<?php

namespace App\Http\Controllers;

use App\Models\Puesto; // Asegúrate de que este use esté presente
use Illuminate\Http\Request; // Request no se usa directamente aquí, pero es bueno tenerlo

class PuestoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtenemos todos los puestos, ordenados por nombre
        // y usamos paginación por si tienes muchos
        $puestos = Puesto::orderBy('nombre_puesto', 'asc')->paginate(10); // Muestra 10 por página

        // Pasamos la colección de puestos a la vista
        return view('puestos.index', compact('puestos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
{
    // Simplemente retornamos la vista del formulario de creación
    return view('puestos.create');
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // 1. Validación de los datos del formulario
    $validatedData = $request->validate([
        'nombre_puesto' => 'required|string|max:255|unique:puestos,nombre_puesto', // Nombre requerido, único en la tabla 'puestos'
        'salario_mensual' => 'required|numeric|min:0', // Salario requerido, numérico y no negativo
    ],[
        // Mensajes de error personalizados (opcional)
        'nombre_puesto.required' => 'El nombre del puesto es obligatorio.',
        'nombre_puesto.unique' => 'Este nombre de puesto ya existe.',
        'salario_mensual.required' => 'El salario mensual es obligatorio.',
        'salario_mensual.numeric' => 'El salario debe ser un número.',
        'salario_mensual.min' => 'El salario no puede ser negativo.'
    ]);

    // 2. Creación del Puesto
    // Si la validación pasa, creamos el puesto usando los datos validados.
    // Esto asume que tienes los campos 'nombre_puesto', 'descripcion_puesto', 'salario_mensual'
    // en la propiedad $fillable de tu modelo Puesto.php
    Puesto::create($validatedData);

    // 3. Redirección con Mensaje de Éxito
    return redirect()->route('puestos.index')
                     ->with('success', '¡Puesto registrado exitosamente!');
}

    /**
     * Display the specified resource.
     */
    public function show(Puesto $puesto)
    {
        // Lo implementaremos después
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Puesto $puesto)
{
    // Laravel nos pasa automáticamente la instancia del Puesto a editar
    // gracias al Route-Model Binding.
    return view('puestos.edit', compact('puesto'));
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Puesto $puesto)
{
    // 1. Validación de los datos del formulario
    // La regla 'unique' para nombre_puesto necesita ignorar el puesto actual que se está editando.
    $validatedData = $request->validate([
        'nombre_puesto' => 'required|string|max:255|unique:puestos,nombre_puesto,' . $puesto->id_puesto . ',id_puesto',
        'salario_mensual' => 'required|numeric|min:0',
        // 'descripcion_puesto' ya no se usa
    ],[
        // Mensajes de error personalizados (opcional)
        'nombre_puesto.required' => 'El nombre del puesto es obligatorio.',
        'nombre_puesto.unique' => 'Este nombre de puesto ya existe.',
        'salario_mensual.required' => 'El salario mensual es obligatorio.',
        'salario_mensual.numeric' => 'El salario debe ser un número.',
        'salario_mensual.min' => 'El salario no puede ser negativo.'
    ]);

    // 2. Actualización del Puesto
    // El objeto $puesto ya es la instancia que queremos actualizar
    // gracias al Route-Model Binding.
    // Usamos update() que también utiliza la asignación masiva (propiedad $fillable en el modelo Puesto).
    $puesto->update($validatedData);

    // 3. Redirección con Mensaje de Éxito
    return redirect()->route('puestos.index')
                     ->with('success', '¡Puesto "' . $puesto->nombre_puesto . '" actualizado exitosamente!');
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Puesto $puesto)
{
    // El objeto $puesto ya es la instancia que queremos eliminar
    // gracias al Route-Model Binding.

    // Antes de eliminar, podrías considerar qué sucede con los empleados
    // que tienen este puesto asignado.
    // En tu migración de empleados, para la llave foránea id_puesto,
    // si definiste onDelete('set null'), los empleados asociados pasarán a tener id_puesto = null.
    // Si definiste onDelete('restrict'), no podrás eliminar el puesto si hay empleados usándolo.
    // Si definiste onDelete('cascade'), se borrarían los empleados (¡peligroso!).
    // Asumiremos por ahora que onDelete('set null') o una lógica que permita la eliminación es la adecuada.

    $nombrePuestoEliminado = $puesto->nombre_puesto; // Guardamos el nombre para el mensaje
    $puesto->delete(); // Elimina el puesto de la base de datos

    return redirect()->route('puestos.index')
                     ->with('success', '¡Puesto "' . $nombrePuestoEliminado . '" eliminado exitosamente!');
}
}
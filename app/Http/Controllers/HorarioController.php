<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use Illuminate\Http\Request;

class HorarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $horarios = Horario::orderBy('nombre_horario', 'asc')->paginate(15);
        return view('horarios.index', compact('horarios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('horarios.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre_horario' => 'required|string|max:100|unique:horarios,nombre_horario',
            'descripcion' => 'nullable|string|max:1000',
            'lunes' => 'nullable|string',
            'lunes_entrada' => 'required_if:lunes,on|nullable',
            'lunes_salida' => 'required_if:lunes,on|nullable|after:lunes_entrada',
            'martes' => 'nullable|string',
            'martes_entrada' => 'required_if:martes,on|nullable',
            'martes_salida' => 'required_if:martes,on|nullable|after:martes_entrada',
            'miercoles' => 'nullable|string',
            'miercoles_entrada' => 'required_if:miercoles,on|nullable',
            'miercoles_salida' => 'required_if:miercoles,on|nullable|after:miercoles_entrada',
            'jueves' => 'nullable|string',
            'jueves_entrada' => 'required_if:jueves,on|nullable',
            'jueves_salida' => 'required_if:jueves,on|nullable|after:jueves_entrada',
            'viernes' => 'nullable|string',
            'viernes_entrada' => 'required_if:viernes,on|nullable',
            'viernes_salida' => 'required_if:viernes,on|nullable|after:viernes_entrada',
            'sabado' => 'nullable|string',
            'sabado_entrada' => 'required_if:sabado,on|nullable',
            'sabado_salida' => 'required_if:sabado,on|nullable|after:sabado_entrada',
            'domingo' => 'nullable|string',
            'domingo_entrada' => 'required_if:domingo,on|nullable',
            'domingo_salida' => 'required_if:domingo,on|nullable|after:domingo_entrada',
        ],[
            'nombre_horario.required' => 'El nombre del horario es obligatorio.',
            'nombre_horario.unique' => 'Este nombre de horario ya existe.',
            '*.required_if' => 'La hora de :attribute es requerida si el día está activado.',
            '*.after' => 'La hora de salida para el :attribute debe ser posterior a la hora de entrada.',
        ]);

        $datosParaGuardar = [
            'nombre_horario' => $request->input('nombre_horario'),
            'descripcion' => $request->input('descripcion'),
        ];
        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
        foreach ($dias as $dia) {
            if ($request->has($dia)) {
                $datosParaGuardar[$dia] = true;
                $datosParaGuardar[$dia.'_entrada'] = $request->input($dia.'_entrada');
                $datosParaGuardar[$dia.'_salida'] = $request->input($dia.'_salida');
            } else {
                $datosParaGuardar[$dia] = false;
                $datosParaGuardar[$dia.'_entrada'] = null;
                $datosParaGuardar[$dia.'_salida'] = null;
            }
        }
        Horario::create($datosParaGuardar);
        return redirect()->route('horarios.index')->with('success', '¡Horario registrado exitosamente!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Horario $horario)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Horario $horario)
    {
        return view('horarios.edit', compact('horario'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Horario $horario)
    {
        $request->validate([
            'nombre_horario' => 'required|string|max:100|unique:horarios,nombre_horario,' . $horario->id_horario . ',id_horario',
            'descripcion' => 'nullable|string|max:1000',
            'lunes' => 'nullable|string',
            'lunes_entrada' => 'required_if:lunes,on|nullable',
            'lunes_salida' => 'required_if:lunes,on|nullable|after:lunes_entrada',
            'martes' => 'nullable|string',
            'martes_entrada' => 'required_if:martes,on|nullable',
            'martes_salida' => 'required_if:martes,on|nullable|after:martes_entrada',
            'miercoles' => 'nullable|string',
            'miercoles_entrada' => 'required_if:miercoles,on|nullable',
            'miercoles_salida' => 'required_if:miercoles,on|nullable|after:miercoles_entrada',
            'jueves' => 'nullable|string',
            'jueves_entrada' => 'required_if:jueves,on|nullable',
            'jueves_salida' => 'required_if:jueves,on|nullable|after:jueves_entrada',
            'viernes' => 'nullable|string',
            'viernes_entrada' => 'required_if:viernes,on|nullable',
            'viernes_salida' => 'required_if:viernes,on|nullable|after:viernes_entrada',
            'sabado' => 'nullable|string',
            'sabado_entrada' => 'required_if:sabado,on|nullable',
            'sabado_salida' => 'required_if:sabado,on|nullable|after:sabado_entrada',
            'domingo' => 'nullable|string',
            'domingo_entrada' => 'required_if:domingo,on|nullable',
            'domingo_salida' => 'required_if:domingo,on|nullable|after:domingo_entrada',
        ],[
            'nombre_horario.required' => 'El nombre del horario es obligatorio.',
            'nombre_horario.unique' => 'Este nombre de horario ya existe.',
            '*.required_if' => 'La hora de :attribute es requerida si el día está activado.',
            '*.after' => 'La hora de salida para el :attribute debe ser posterior a la hora de entrada.',
        ]);

        $datosParaActualizar = [
            'nombre_horario' => $request->input('nombre_horario'),
            'descripcion' => $request->input('descripcion'),
        ];
        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
        foreach ($dias as $dia) {
            if ($request->has($dia)) {
                $datosParaActualizar[$dia] = true;
                $datosParaActualizar[$dia.'_entrada'] = $request->input($dia.'_entrada');
                $datosParaActualizar[$dia.'_salida'] = $request->input($dia.'_salida');
            } else {
                $datosParaActualizar[$dia] = false;
                $datosParaActualizar[$dia.'_entrada'] = null;
                $datosParaActualizar[$dia.'_salida'] = null;
            }
        }
        $horario->update($datosParaActualizar);
        return redirect()->route('horarios.index')->with('success', '¡Horario "' . $horario->nombre_horario . '" actualizado exitosamente!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Horario $horario)
{
    try {
        // Guardamos el nombre del horario para usarlo en el mensaje de éxito
        $nombreHorarioEliminado = $horario->nombre_horario;

        // Antes de eliminar, Laravel aplicará la regla de la llave foránea.
        // Como la definimos con onDelete('set null'), cualquier empleado que tuviera
        // este horario asignado, ahora tendrá su id_horario como NULL.
        // Esto es seguro y no borra a los empleados.

        $horario->delete(); // Elimina el horario de la base de datos

        return redirect()->route('horarios.index')
                         ->with('success', '¡Horario "' . $nombreHorarioEliminado . '" eliminado exitosamente!');

    } catch (\Exception $e) {
        // Manejo de errores por si algo inesperado falla
        return redirect()->route('horarios.index')
                         ->with('error', 'No se pudo eliminar el horario. Error: ' . $e->getMessage());
    }
}
}
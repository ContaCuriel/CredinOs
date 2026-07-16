<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use Illuminate\Http\Request;

class HorarioController extends Controller
{
    public function index()
    {
        $horarios = Horario::paginate(15);
        return view('horarios.index', compact('horarios'));
    }

    public function create()
    {
        return view('horarios.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_horario' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'lunes_entrada' => 'nullable', 'lunes_salida' => 'nullable',
            'martes_entrada' => 'nullable', 'martes_salida' => 'nullable',
            'miercoles_entrada' => 'nullable', 'miercoles_salida' => 'nullable',
            'jueves_entrada' => 'nullable', 'jueves_salida' => 'nullable',
            'viernes_entrada' => 'nullable', 'viernes_salida' => 'nullable',
            'sabado_entrada' => 'nullable', 'sabado_salida' => 'nullable',
            'domingo_entrada' => 'nullable', 'domingo_salida' => 'nullable',
            
            // Regla simplificada de tolerancia
            'tiene_tolerancia' => 'nullable',
            'tolerancia_minutos' => 'nullable|numeric|min:0',
        ]);

        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
        foreach ($dias as $dia) {
            $data[$dia] = $request->has($dia);
            if (!$data[$dia]) {
                $data[$dia.'_entrada'] = null;
                $data[$dia.'_salida'] = null;
            }
        }

        // Reutilizamos tu campo anterior en la base de datos para no tener que hacer migraciones nuevas
        $data['aplicar_reglas_avanzadas'] = $request->has('tiene_tolerancia');
        $data['tolerancia_minutos'] = $data['aplicar_reglas_avanzadas'] ? ($request->tolerancia_minutos ?? 0) : 0;

        Horario::create($data);

        return redirect()->route('horarios.index')->with('success', 'Horario creado exitosamente.');
    }

    public function edit(Horario $horario)
    {
        return view('horarios.edit', compact('horario'));
    }

    public function update(Request $request, Horario $horario)
    {
        $data = $request->validate([
            'nombre_horario' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'lunes_entrada' => 'nullable', 'lunes_salida' => 'nullable',
            'martes_entrada' => 'nullable', 'martes_salida' => 'nullable',
            'miercoles_entrada' => 'nullable', 'miercoles_salida' => 'nullable',
            'jueves_entrada' => 'nullable', 'jueves_salida' => 'nullable',
            'viernes_entrada' => 'nullable', 'viernes_salida' => 'nullable',
            'sabado_entrada' => 'nullable', 'sabado_salida' => 'nullable',
            'domingo_entrada' => 'nullable', 'domingo_salida' => 'nullable',
            
            // Regla simplificada de tolerancia
            'tiene_tolerancia' => 'nullable',
            'tolerancia_minutos' => 'nullable|numeric|min:0',
        ]);

        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
        foreach ($dias as $dia) {
            $data[$dia] = $request->has($dia);
            if (!$data[$dia]) {
                $data[$dia.'_entrada'] = null;
                $data[$dia.'_salida'] = null;
            }
        }

        $data['aplicar_reglas_avanzadas'] = $request->has('tiene_tolerancia');
        $data['tolerancia_minutos'] = $data['aplicar_reglas_avanzadas'] ? ($request->tolerancia_minutos ?? 0) : 0;

        $horario->update($data);

        return redirect()->route('horarios.index')->with('success', 'Horario actualizado exitosamente.');
    }

    public function destroy(Horario $horario)
    {
        if ($horario->empleados()->count() > 0) {
            return back()->with('error', 'No se puede eliminar el horario porque tiene empleados asignados.');
        }

        $horario->delete();
        return redirect()->route('horarios.index')->with('success', 'Horario eliminado exitosamente.');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use Illuminate\Http\Request;

class HorarioController extends Controller
{
    /**
     * Muestra la lista de horarios.
     */
    public function index()
    {
        $horarios = Horario::paginate(15);
        return view('horarios.index', compact('horarios'));
    }

    /**
     * Muestra el formulario para crear un nuevo horario.
     */
    public function create()
    {
        return view('horarios.create');
    }

    /**
     * Guarda un nuevo horario con sus reglas de asistencia.
     */
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
            // Reglas avanzadas
            'aplicar_reglas_avanzadas' => 'nullable',
            'tolerancia_minutos' => 'nullable|numeric',
            'retardo_menor_minutos_inicio' => 'nullable|numeric',
            'retardo_menor_minutos_fin' => 'nullable|numeric',
            'retardos_para_falta' => 'nullable|numeric',
            'medio_dia_minutos_inicio' => 'nullable|numeric',
            'medio_dia_minutos_fin' => 'nullable|numeric',
            'falta_minutos_inicio' => 'nullable|numeric',
            'castigo_falta_lun_vie' => 'nullable|numeric',
            'castigo_falta_mar_jue_sab' => 'nullable|numeric',
        ]);

        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
        foreach ($dias as $dia) {
            $data[$dia] = $request->has($dia);
            if (!$data[$dia]) {
                $data[$dia.'_entrada'] = null;
                $data[$dia.'_salida'] = null;
            }
        }

        $data['aplicar_reglas_avanzadas'] = $request->has('aplicar_reglas_avanzadas');

        Horario::create($data);

        return redirect()->route('horarios.index')->with('success', 'Horario creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un horario existente.
     */
    public function edit(Horario $horario)
    {
        return view('horarios.edit', compact('horario'));
    }

    /**
     * Actualiza el horario y sus reglas.
     */
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
            // Reglas avanzadas
            'aplicar_reglas_avanzadas' => 'nullable',
            'tolerancia_minutos' => 'nullable|numeric',
            'retardo_menor_minutos_inicio' => 'nullable|numeric',
            'retardo_menor_minutos_fin' => 'nullable|numeric',
            'retardos_para_falta' => 'nullable|numeric',
            'medio_dia_minutos_inicio' => 'nullable|numeric',
            'medio_dia_minutos_fin' => 'nullable|numeric',
            'falta_minutos_inicio' => 'nullable|numeric',
            'castigo_falta_lun_vie' => 'nullable|numeric',
            'castigo_falta_mar_jue_sab' => 'nullable|numeric',
        ]);

        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
        foreach ($dias as $dia) {
            $data[$dia] = $request->has($dia);
            if (!$data[$dia]) {
                $data[$dia.'_entrada'] = null;
                $data[$dia.'_salida'] = null;
            }
        }

        $data['aplicar_reglas_avanzadas'] = $request->has('aplicar_reglas_avanzadas');

        $horario->update($data);

        return redirect()->route('horarios.index')->with('success', 'Horario actualizado exitosamente.');
    }

    /**
     * Elimina un horario.
     */
    public function destroy(Horario $horario)
    {
        if ($horario->empleados()->count() > 0) {
            return back()->with('error', 'No se puede eliminar el horario porque tiene empleados asignados.');
        }

        $horario->delete();
        return redirect()->route('horarios.index')->with('success', 'Horario eliminado exitosamente.');
    }
}
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
        $data = $this->validarYProcesarDatos($request);
        Horario::create($data);

        return redirect()->route('horarios.index')->with('success', 'Horario creado exitosamente con reglas de disciplina.');
    }

    public function edit(Horario $horario)
    {
        return view('horarios.edit', compact('horario'));
    }

    public function update(Request $request, Horario $horario)
    {
        $data = $this->validarYProcesarDatos($request);
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

    /**
     * Centralizamos la validación para Store y Update con las Nuevas Reglas
     */
    private function validarYProcesarDatos(Request $request)
    {
        $data = $request->validate([
            'nombre_horario' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            // Días y horas
            'lunes_entrada' => 'nullable', 'lunes_salida' => 'nullable',
            'martes_entrada' => 'nullable', 'martes_salida' => 'nullable',
            'miercoles_entrada' => 'nullable', 'miercoles_salida' => 'nullable',
            'jueves_entrada' => 'nullable', 'jueves_salida' => 'nullable',
            'viernes_entrada' => 'nullable', 'viernes_salida' => 'nullable',
            'sabado_entrada' => 'nullable', 'sabado_salida' => 'nullable',
            'domingo_entrada' => 'nullable', 'domingo_salida' => 'nullable',
            
            // ⚙️ REGLAS DE DISCIPLINA (Prompt Maestro)
            'tiene_tolerancia' => 'nullable',
            'tolerancia_minutos' => 'nullable|numeric|min:0',
            
            'minutos_limite_retardo' => 'nullable|numeric|min:0',
            'retardos_por_falta' => 'nullable|numeric|min:0',
            
            'aplica_medio_dia' => 'nullable',
            'minutos_limite_medio_dia' => 'nullable|numeric|min:0',
            
            'aplica_castigo_multiplicador' => 'nullable',
            'multiplicador_lunes_viernes' => 'nullable|numeric|min:1',
            'multiplicador_dias_regulares' => 'nullable|numeric|min:1',
        ]);

        // 1. Procesar días de la semana (Forzando true/false para PostgreSQL)
        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
        foreach ($dias as $dia) {
            $data[$dia] = $request->has($dia) ? true : false;
            if (!$data[$dia]) {
                $data[$dia.'_entrada'] = null;
                $data[$dia.'_salida'] = null;
            }
        }

        // 2. Procesar Reglas Básicas de Tolerancia
        $data['aplicar_reglas_avanzadas'] = $request->has('tiene_tolerancia') ? true : false;
        $data['tolerancia_minutos'] = $data['aplicar_reglas_avanzadas'] ? (int)($request->tolerancia_minutos ?? 0) : 0;
        
        // 3. Procesar Retardos
        $data['minutos_limite_retardo'] = (int)($request->minutos_limite_retardo ?? 15);
        $data['retardos_por_falta'] = (int)($request->retardos_por_falta ?? 3); // Por defecto 3 retardos = 1 falta

        // 4. Procesar Medio Día (Evitamos enviar null para prevenir fallos en BD)
        $data['aplica_medio_dia'] = $request->has('aplica_medio_dia') ? true : false;
        $data['minutos_limite_medio_dia'] = $data['aplica_medio_dia'] ? (int)($request->minutos_limite_medio_dia ?? 30) : 30;

        // 5. Procesar Multiplicadores
        $data['aplica_castigo_multiplicador'] = $request->has('aplica_castigo_multiplicador') ? true : false;
        if ($data['aplica_castigo_multiplicador']) {
            $data['multiplicador_lunes_viernes'] = (int)($request->multiplicador_lunes_viernes ?? 3);
            $data['multiplicador_dias_regulares'] = (int)($request->multiplicador_dias_regulares ?? 2);
        } else {
            $data['multiplicador_lunes_viernes'] = 1;
            $data['multiplicador_dias_regulares'] = 1;
        }

        return $data;
    }
}
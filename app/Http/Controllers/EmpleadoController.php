<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empleado;
use App\Models\Puesto;
use App\Models\Sucursal;
use App\Models\Horario; // AÑADIDO: Importar el modelo Horario
use Carbon\Carbon;

class EmpleadoController extends Controller
{
    /**
     * Muestra una lista de los empleados, aplicando el filtro de status.
     */
    public function index(Request $request)
    {
        $status_filter = $request->input('status_filter', 'alta');
        $id_sucursal_filter = $request->input('id_sucursal_filter');
        $search_term = $request->input('search_term');

        $query = Empleado::with(['puesto', 'sucursal']);

        if ($status_filter == 'baja') {
            $query->where('status', 'Baja');
        } elseif ($status_filter == 'todos') {
            $query->whereIn('status', ['Alta', 'Baja']);
        } else { 
            $query->where('status', 'Alta');
        }

        if (!empty($id_sucursal_filter)) {
            $query->where('id_sucursal', $id_sucursal_filter);
        }

        if (!empty($search_term)) {
            $query->where(function ($q) use ($search_term) {
                $q->where('nombre_completo', 'like', '%' . $search_term . '%')
                  ->orWhere('curp', 'like', '%' . $search_term . '%')
                  ->orWhere('rfc', 'like', '%' . $search_term . '%');
            });
        }

        $empleados = $query->orderBy(function ($subQuery) {
                                 $subQuery->select('nombre_sucursal')
                                       ->from('sucursales')
                                       ->whereColumn('sucursales.id_sucursal', 'empleados.id_sucursal');
                            })
                            ->orderBy('nombre_completo')
                            ->get(); // Cambiado de paginate a get si no estás usando paginación en empleados.index
        
        $puestos = Puesto::orderBy('nombre_puesto')->get();
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();

        return view('empleados.index', compact(
            'empleados', 
            'status_filter', 
            'puestos',
            'sucursales',
            'id_sucursal_filter',
            'search_term'
        ));
    }

    /**
     * Muestra el formulario para crear un nuevo empleado.
     */
     public function create()
    {
        $puestos = Puesto::orderBy('nombre_puesto')->get();
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $horarios = Horario::orderBy('nombre_horario')->get(); // Se obtienen los horarios

        return view('empleados.create', compact('puestos', 'sucursales', 'horarios')); // Se pasan a la vista
    }

    /**
     * Guarda un nuevo empleado en la base de datos.
     */
     public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'id_puesto' => 'required|exists:puestos,id_puesto',
            'id_sucursal' => 'required|exists:sucursales,id_sucursal',
            'id_horario' => 'required|exists:horarios,id_horario', // Nueva validación
            'fecha_ingreso' => 'required|date',
            'fecha_nacimiento' => 'required|date',
            'nacionalidad' => 'nullable|string|max:100',
            'sexo' => 'nullable|string|max:20',
            'estado_civil' => 'nullable|string|max:50',
            'direccion' => 'required|string|max:500',
            'curp' => 'required|string|size:18|unique:empleados,curp',
            'rfc' => 'nullable|string|max:13|unique:empleados,rfc', 
            'nss' => 'nullable|string|max:11|unique:empleados,nss', 
            'cuenta_bancaria' => 'nullable|string|max:20',
            'banco' => 'nullable|string|max:100',
            'contacto_emerg_nombre' => 'nullable|string|max:255',
            'contacto_emerg_telefono' => 'nullable|string|max:15',
            'telefono' => 'nullable|string|max:20',
            'info_cartas_recomendacion' => 'nullable|string',
        ],[
            'curp.unique' => 'Esta CURP ya está registrada.',
            'rfc.unique' => 'Este RFC ya está registrado (si se proporciona).',
            'nss.unique' => 'Este NSS ya está registrado (si se proporciona).',
            'id_horario.required' => 'Debe seleccionar un horario para el empleado.',
        ]);

        $empleado = new Empleado();
        $empleado->fill($validatedData); 
        $empleado->status = 'Alta'; 
        // El campo `imss` parece estar ausente en el fillable del modelo, si existe, añadirlo.
        // $empleado->imss = false;  
        $empleado->save();

        return redirect()->route('empleados.index')->with('success', '¡Empleado registrado exitosamente!');
    }

    /**
     * Muestra un empleado específico.
     * Implementaremos esto para la "Ficha del Empleado".
     */
    public function show(Empleado $empleado) // Route-Model Binding
    {
        // Por ahora, solo un placeholder o redirigir a la edición
        // return view('empleados.show', compact('empleado')); 
        // O, si aún no tienes empleados.show:
        // return redirect()->route('empleados.edit', $empleado->id_empleado);
    }

    /**
     * Muestra el formulario para editar un empleado.
     */
    public function edit(Empleado $empleado)
    {
        $puestos = Puesto::orderBy('nombre_puesto')->get();
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $horarios = Horario::orderBy('nombre_horario')->get(); // Se obtienen los horarios

        return view('empleados.edit', compact('empleado', 'puestos', 'sucursales', 'horarios')); // Se pasan a la vista
    }

    /**
     * Actualiza un empleado específico en la base de datos.
     */
    public function update(Request $request, Empleado $empleado)
    {
        $validatedData = $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'id_puesto' => 'required|exists:puestos,id_puesto',
            'id_sucursal' => 'required|exists:sucursales,id_sucursal',
            'id_horario' => 'required|exists:horarios,id_horario', // Nueva validación
            'fecha_ingreso' => 'required|date',
            'fecha_nacimiento' => 'required|date',
            'nacionalidad' => 'nullable|string|max:100',
            'sexo' => 'nullable|string|max:20',
            'estado_civil' => 'nullable|string|max:50',
            'direccion' => 'required|string|max:500',
            'curp' => 'required|string|size:18|unique:empleados,curp,' . $empleado->id_empleado . ',id_empleado',
            'rfc' => 'nullable|string|max:13|unique:empleados,rfc,' . $empleado->id_empleado . ',id_empleado',
            'nss' => 'nullable|string|max:11|unique:empleados,nss,' . $empleado->id_empleado . ',id_empleado',
            'cuenta_bancaria' => 'nullable|string|max:20',
            'banco' => 'nullable|string|max:100',
            'contacto_emerg_nombre' => 'nullable|string|max:255',
            'contacto_emerg_telefono' => 'nullable|string|max:15',
            'telefono' => 'nullable|string|max:20',
            'info_cartas_recomendacion' => 'nullable|string',
        ],[
            'id_horario.required' => 'Debe seleccionar un horario para el empleado.',
        ]);

        $empleado->fill($validatedData);
        $empleado->save();

        return redirect()->route('empleados.index')->with('success', '¡Empleado actualizado exitosamente!');
    }

    /**
     * "Elimina" (da de baja) un empleado específico.
     */
    public function destroy(Request $request, Empleado $empleado)
    {
        $request->validate([
            'fecha_baja' => 'required|date|before_or_equal:today',
            'motivo_baja' => 'nullable|string|max:500',
        ]);

        if ($empleado->status === 'Baja') {
            return redirect()->route('empleados.index')
                             ->with('error', 'Este empleado ya ha sido dado de baja anteriormente.');
        }

        $empleado->status = 'Baja';
        $empleado->fecha_baja = $request->input('fecha_baja');
        $empleado->motivo_baja = $request->input('motivo_baja');
        
        $empleado->save();

        return redirect()->route('empleados.index')
                         ->with('success', '¡Empleado '. $empleado->nombre_completo .' dado de baja exitosamente!');
    }

    /**
     * Reactiva un empleado que estaba dado de baja.
     */
    public function reactivar(Request $request, Empleado $empleado)
    {
        $request->validate([
            'fecha_ingreso_reingreso' => 'required|date|after_or_equal:' . ($empleado->fecha_baja ? Carbon::parse($empleado->fecha_baja)->toDateString() : '1900-01-01'),
            'id_puesto_reingreso' => 'required|exists:puestos,id_puesto',
            'id_sucursal_reingreso' => 'required|exists:sucursales,id_sucursal',
        ], [
            'fecha_ingreso_reingreso.after_or_equal' => 'La nueva fecha de ingreso debe ser posterior o igual a la fecha de baja anterior del empleado.'
        ]);

        if ($empleado->status !== 'Baja') {
            return redirect()->route('empleados.index')
                             ->with('error', 'Este empleado no está actualmente dado de baja y no puede ser reactivado.');
        }

        $empleado->status = 'Alta';
        $empleado->fecha_ingreso = $request->input('fecha_ingreso_reingreso');
        $empleado->id_puesto = $request->input('id_puesto_reingreso');
        $empleado->id_sucursal = $request->input('id_sucursal_reingreso');
        $empleado->fecha_baja = null;
        $empleado->motivo_baja = null;
        
        $empleado->save();

        return redirect()->route('empleados.index')
                         ->with('success', '¡Empleado '. $empleado->nombre_completo .' reactivado exitosamente!');
    }

    /**
     * Muestra el historial de contratos de un empleado específico.
     */
    public function historialContratos(Empleado $empleado)
    {
        $contratos = $empleado->contratos()
                              ->orderBy('fecha_inicio', 'desc')
                              ->paginate(10); 

        return view('empleados.historial_contratos', compact('empleado', 'contratos'));
    }
}
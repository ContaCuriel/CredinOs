<?php

namespace App\Http\Controllers;

use App\Models\DeduccionEmpleado;
use App\Models\Empleado;
use Illuminate\Http\Request;
use App\Models\Sucursal;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DeduccionesExport; 

class DeduccionController extends Controller
{
    public function index(Request $request)
    {
        // 1. Obtener los valores de los filtros del request (INCLUYENDO STATUS)
        $search_nombre = $request->input('search_nombre');
        $id_sucursal_filter = $request->input('id_sucursal_filter');
        $tipo_deduccion_filter = $request->input('tipo_deduccion_filter');
        $status_filter = $request->input('status_filter', 'Activo'); // Nuevo filtro: por defecto Activo

        // 2. Iniciar la consulta con eager loading de las relaciones
        $query = DeduccionEmpleado::with(['empleado.sucursal']);

        // 3. Aplicar filtros si existen
        if (!empty($search_nombre)) {
            $query->whereHas('empleado', function ($q_empleado) use ($search_nombre) {
                $q_empleado->where('nombre_completo', 'like', '%' . $search_nombre . '%');
            });
        }

        if (!empty($id_sucursal_filter)) {
            $query->whereHas('empleado', function ($q_empleado) use ($id_sucursal_filter) {
                $q_empleado->where('id_sucursal', $id_sucursal_filter);
            });
        }

        if (!empty($tipo_deduccion_filter)) {
            $query->where('tipo_deduccion', $tipo_deduccion_filter);
        }

        // NUEVO: Filtro por Status (Historial)
        if ($status_filter !== 'Todas') {
            $query->where('status', $status_filter);
        }

        // 4. Ordenar y paginar los resultados
        $deducciones = $query->orderBy('fecha_solicitud', 'desc')
                             ->paginate(15)
                             ->withQueryString(); 

        // 5. Obtener datos para los menús desplegables de los filtros
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $tipos_deduccion = ['Préstamo', 'Caja de Ahorro', 'Infonavit', 'ISR', 'IMSS', 'Otro'];

        // 6. Pasar todos los datos a la vista (agregando status_filter)
        return view('deducciones.index', compact(
            'deducciones',
            'sucursales',
            'tipos_deduccion',
            'search_nombre',
            'id_sucursal_filter',
            'tipo_deduccion_filter',
            'status_filter'
        ));
    }

    public function create()
    {
        $empleados = Empleado::where('status', 'Alta')->orderBy('nombre_completo')->get();

        $tipos_deduccion = [
            'Préstamo' => 'Préstamo (con plazo)',
            'Caja de Ahorro' => 'Caja de Ahorro',
            'Infonavit' => 'Infonavit',
            'ISR' => 'ISR (Manual)',
            'IMSS' => 'IMSS (Manual)',
            'Otro' => 'Otro Descuento Fijo',
        ];

        return view('deducciones.create', compact('empleados', 'tipos_deduccion'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'tipo_deduccion' => 'required|string|in:Préstamo,Caja de Ahorro,Infonavit,ISR,IMSS,Otro',
            'fecha_solicitud' => 'required|date', 
            'monto_quincenal' => 'required|numeric|min:0.01',
            'plazo_quincenas' => 'required_if:tipo_deduccion,Préstamo|nullable|integer|min:1',
            'descripcion' => 'nullable|string|max:1000',
        ],[
            'fecha_solicitud.required' => 'La fecha de inicio de la deducción es obligatoria.',
        ]);

        $datosParaGuardar = [
            'id_empleado' => $validatedData['id_empleado'],
            'tipo_deduccion' => $validatedData['tipo_deduccion'],
            'descripcion' => $validatedData['descripcion'],
            'monto_quincenal' => $validatedData['monto_quincenal'],
            'fecha_solicitud' => $validatedData['fecha_solicitud'],
            'status' => 'Activo',
        ];

        if ($validatedData['tipo_deduccion'] === 'Préstamo') {
            $montoTotal = $validatedData['monto_quincenal'] * $validatedData['plazo_quincenas'];
            $datosParaGuardar['plazo_quincenas'] = $validatedData['plazo_quincenas'];
            $datosParaGuardar['monto_total_prestamo'] = $montoTotal;
            $datosParaGuardar['saldo_pendiente'] = $montoTotal;
            $datosParaGuardar['quincenas_pagadas'] = 0;
        }

        DeduccionEmpleado::create($datosParaGuardar);

        return redirect()->route('deducciones.index')
                         ->with('success', '¡Deducción registrada exitosamente!');
    }

    public function edit(DeduccionEmpleado $deduccione)
    {
        $deduccion = $deduccione;
        $deduccion->load('empleado');

        if (!$deduccion->empleado) {
            return redirect()->route('deducciones.index')
                             ->with('error', 'No se puede editar la deducción. El empleado asociado ya no existe.');
        }

        $tipos_deduccion = [
            'Préstamo' => 'Préstamo (con plazo)',
            'Caja de Ahorro' => 'Caja de Ahorro',
            'Infonavit' => 'Infonavit',
            'ISR' => 'ISR (Manual)',
            'IMSS' => 'IMSS (Manual)',
            'Otro' => 'Otro Descuento Fijo',
        ];

        return view('deducciones.edit', compact('deduccion', 'tipos_deduccion'));
    }

    public function update(Request $request, DeduccionEmpleado $deduccione)
    {
        $deduccion = $deduccione;

        $validatedData = $request->validate([
            'tipo_deduccion' => 'required|string|in:Préstamo,Caja de Ahorro,Infonavit,ISR,IMSS,Otro',
            'fecha_solicitud' => 'required|date',
            'monto_quincenal' => 'required|numeric|min:0.01',
            'plazo_quincenas' => 'required_if:tipo_deduccion,Préstamo|nullable|integer|min:1',
            'status' => 'required|string|in:Activo,Pagado,Finalizado,Cancelado',
            'descripcion' => 'nullable|string|max:1000',
        ]);

        $datosParaActualizar = [
            'tipo_deduccion' => $validatedData['tipo_deduccion'],
            'fecha_solicitud' => $validatedData['fecha_solicitud'],
            'monto_quincenal' => $validatedData['monto_quincenal'],
            'status' => $validatedData['status'],
            'descripcion' => $validatedData['descripcion'],
        ];

        if ($validatedData['tipo_deduccion'] === 'Préstamo') {
            $montoTotalActualizado = $validatedData['monto_quincenal'] * $validatedData['plazo_quincenas'];
            $saldoPendienteActualizado = $montoTotalActualizado - ($deduccion->quincenas_pagadas * $validatedData['monto_quincenal']);

            $datosParaActualizar['plazo_quincenas'] = $validatedData['plazo_quincenas'];
            $datosParaActualizar['monto_total_prestamo'] = $montoTotalActualizado;
            $datosParaActualizar['saldo_pendiente'] = max(0, $saldoPendienteActualizado);
        } else {
            $datosParaActualizar['plazo_quincenas'] = null;
            $datosParaActualizar['monto_total_prestamo'] = null;
            $datosParaActualizar['saldo_pendiente'] = null;
        }

        $deduccion->update($datosParaActualizar);

        return redirect()->route('deducciones.index')
                         ->with('success', '¡Deducción actualizada exitosamente!');
    }

    /**
     * MODIFICADO: Ahora finaliza la deducción en lugar de eliminarla.
     */
    public function destroy(DeduccionEmpleado $deduccione)
    {
        try {
            $deduccion = $deduccione;
            
            // Lógica: Si es un préstamo y el saldo es 0, lo marcamos como 'Pagado', 
            // de lo contrario simplemente 'Finalizado'.
            $nuevoStatus = ($deduccion->tipo_deduccion == 'Préstamo' && $deduccion->saldo_pendiente <= 0) 
                           ? 'Pagado' 
                           : 'Finalizado';

            $deduccion->update(['status' => $nuevoStatus]);

            return redirect()->route('deducciones.index')
                             ->with('success', 'La deducción ha sido marcada como "'.$nuevoStatus.'" y se mantiene en el historial.');

        } catch (\Exception $e) {
            return redirect()->route('deducciones.index')
                             ->with('error', 'No se pudo finalizar la deducción. Error: ' . $e->getMessage());
        }
    }

    public function exportarExcel(Request $request)
    {
        $search_nombre = $request->input('search_nombre');
        $id_sucursal_filter = $request->input('id_sucursal_filter');
        $tipo_deduccion_filter = $request->input('tipo_deduccion_filter');
        $status_filter = $request->input('status_filter', 'Activo'); // Agregamos status al Excel

        $query = DeduccionEmpleado::with(['empleado.sucursal']);

        if (!empty($search_nombre)) {
            $query->whereHas('empleado', function ($q_empleado) use ($search_nombre) {
                $q_empleado->where('nombre_completo', 'like', '%' . $search_nombre . '%');
            });
        }

        if (!empty($id_sucursal_filter)) {
            $query->whereHas('empleado', function ($q_empleado) use ($id_sucursal_filter) {
                $q_empleado->where('id_sucursal', $id_sucursal_filter);
            });
        }

        if (!empty($tipo_deduccion_filter)) {
            $query->where('tipo_deduccion', $tipo_deduccion_filter);
        }

        // Filtro de Status en Excel
        if ($status_filter !== 'Todas') {
            $query->where('status', $status_filter);
        }

        $deducciones = $query->orderBy('fecha_solicitud', 'desc')->get();

        return Excel::download(new DeduccionesExport($deducciones), 'Reporte_Deducciones.xlsx');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\DeduccionEmpleado; // <-- CAMBIADO
use App\Models\Empleado;
use Illuminate\Http\Request;
use App\Models\Sucursal;

class DeduccionController extends Controller // <-- CAMBIADO
{
    public function index(Request $request)
{
    // 1. Obtener los valores de los filtros del request
    $search_nombre = $request->input('search_nombre');
    $id_sucursal_filter = $request->input('id_sucursal_filter');
    $tipo_deduccion_filter = $request->input('tipo_deduccion_filter');

    // 2. Iniciar la consulta con eager loading de las relaciones
    $query = DeduccionEmpleado::with(['empleado.sucursal']);

    // 3. Aplicar filtros si existen
    if (!empty($search_nombre)) {
        // Filtrar por nombre del empleado a través de la relación
        $query->whereHas('empleado', function ($q_empleado) use ($search_nombre) {
            $q_empleado->where('nombre_completo', 'like', '%' . $search_nombre . '%');
        });
    }

    if (!empty($id_sucursal_filter)) {
        // Filtrar por sucursal del empleado a través de la relación
        $query->whereHas('empleado', function ($q_empleado) use ($id_sucursal_filter) {
            $q_empleado->where('id_sucursal', $id_sucursal_filter);
        });
    }

    if (!empty($tipo_deduccion_filter)) {
        // Filtrar directamente en la tabla de deducciones
        $query->where('tipo_deduccion', $tipo_deduccion_filter);
    }

    // 4. Ordenar y paginar los resultados
    $deducciones = $query->orderBy('fecha_solicitud', 'desc')
                         ->paginate(15)
                         ->withQueryString(); // Para que la paginación conserve los filtros

    // 5. Obtener datos para los menús desplegables de los filtros
    $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
    $tipos_deduccion = ['Préstamo', 'Caja de Ahorro', 'Infonavit', 'ISR', 'IMSS', 'Otro'];

    // 6. Pasar todos los datos a la vista
    return view('deducciones.index', compact(
        'deducciones',
        'sucursales',
        'tipos_deduccion',
        'search_nombre',
        'id_sucursal_filter',
        'tipo_deduccion_filter'
    ));
}

    public function create()
{
    $empleados = Empleado::where('status', 'Alta')->orderBy('nombre_completo')->get();

    // Definimos los tipos de deducción que el usuario puede crear
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
    // 1. Validación de los datos del formulario
    $validatedData = $request->validate([
        'id_empleado' => 'required|exists:empleados,id_empleado',
        'tipo_deduccion' => 'required|string|in:Préstamo,Caja de Ahorro,Infonavit,ISR,IMSS,Otro',
        'fecha_solicitud' => 'required|date', // <-- AÑADIDO A LA VALIDACIÓN
        'monto_quincenal' => 'required|numeric|min:0.01',
        'plazo_quincenas' => 'required_if:tipo_deduccion,Préstamo|nullable|integer|min:1',
        'descripcion' => 'nullable|string|max:1000',
    ],[
        // ... tus mensajes de error existentes ...
        'fecha_solicitud.required' => 'La fecha de inicio de la deducción es obligatoria.', // <-- Mensaje de error
    ]);

    // 2. Preparamos el array completo con los datos para guardar
    $datosParaGuardar = [
        'id_empleado' => $validatedData['id_empleado'],
        'tipo_deduccion' => $validatedData['tipo_deduccion'],
        'descripcion' => $validatedData['descripcion'],
        'monto_quincenal' => $validatedData['monto_quincenal'],
        'fecha_solicitud' => $validatedData['fecha_solicitud'], // <-- AÑADIDO AL ARRAY
        'status' => 'Activo',
    ];

    // 3. Si es un Préstamo, calculamos y añadimos los campos específicos
    if ($validatedData['tipo_deduccion'] === 'Préstamo') {
        $montoTotal = $validatedData['monto_quincenal'] * $validatedData['plazo_quincenas'];

        $datosParaGuardar['plazo_quincenas'] = $validatedData['plazo_quincenas'];
        $datosParaGuardar['monto_total_prestamo'] = $montoTotal;
        $datosParaGuardar['saldo_pendiente'] = $montoTotal;
        $datosParaGuardar['quincenas_pagadas'] = 0;
    }

    // 4. Creación de la Deducción
    DeduccionEmpleado::create($datosParaGuardar);

    // 5. Redirección con Mensaje de Éxito
    return redirect()->route('deducciones.index')
                     ->with('success', '¡Deducción registrada exitosamente!');
}
    // Dejaremos los otros métodos (edit, update, destroy) para después
    // ya que necesitarán una lógica similarmente ajustada.
    public function edit(DeduccionEmpleado $deduccione)
{
    // Renombramos la variable para que sea más simple: $deduccion
    $deduccion = $deduccione;

    // Cargamos la relación del empleado para mostrar su nombre
    $deduccion->load('empleado');

    if (!$deduccion->empleado) {
        return redirect()->route('deducciones.index')
                         ->with('error', 'No se puede editar la deducción. El empleado asociado ya no existe.');
    }

    // Pasamos los tipos de deducción al formulario
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

    public function update(Request $request, DeduccionEmpleado $deduccione) // La variable es $deduccione
{
    // Renombramos para claridad
    $deduccion = $deduccione;

    // 1. Validación de los datos del formulario de edición
    $validatedData = $request->validate([
        'tipo_deduccion' => 'required|string|in:Préstamo,Caja de Ahorro,Infonavit,ISR,IMSS,Otro',
        'fecha_solicitud' => 'required|date',
        'monto_quincenal' => 'required|numeric|min:0.01',
        'plazo_quincenas' => 'required_if:tipo_deduccion,Préstamo|nullable|integer|min:1',
        'status' => 'required|string|in:Activo,Pagado,Cancelado', // Habíamos añadido este campo al edit form, lo validamos
        'descripcion' => 'nullable|string|max:1000',
    ]);

    // 2. Preparamos el array base con los datos para actualizar
    $datosParaActualizar = [
        'tipo_deduccion' => $validatedData['tipo_deduccion'],
        'fecha_solicitud' => $validatedData['fecha_solicitud'],
        'monto_quincenal' => $validatedData['monto_quincenal'],
        'status' => $validatedData['status'], // 'status' en lugar de 'status_prestamo'
        'descripcion' => $validatedData['descripcion'],
    ];

    // 3. Si es un Préstamo, calculamos y añadimos los campos específicos de préstamo
    if ($validatedData['tipo_deduccion'] === 'Préstamo') {
        $montoTotalActualizado = $validatedData['monto_quincenal'] * $validatedData['plazo_quincenas'];
        $saldoPendienteActualizado = $montoTotalActualizado - ($deduccion->quincenas_pagadas * $validatedData['monto_quincenal']);

        $datosParaActualizar['plazo_quincenas'] = $validatedData['plazo_quincenas'];
        $datosParaActualizar['monto_total_prestamo'] = $montoTotalActualizado;
        $datosParaActualizar['saldo_pendiente'] = max(0, $saldoPendienteActualizado);
    } else {
        // Si el tipo de deducción cambió de "Préstamo" a otro tipo,
        // es buena idea limpiar los campos que ya no aplican.
        $datosParaActualizar['plazo_quincenas'] = null;
        $datosParaActualizar['monto_total_prestamo'] = null;
        $datosParaActualizar['saldo_pendiente'] = null;
        // quincenas_pagadas podría mantenerse o resetearse a 0
    }

    // 4. Actualización del registro
    $deduccion->update($datosParaActualizar);

    // 5. Redirección con Mensaje de Éxito
    return redirect()->route('deducciones.index')
                     ->with('success', '¡Deducción actualizada exitosamente!');
}

    public function destroy(DeduccionEmpleado $deduccione)
{
    try {
        // Renombramos para claridad
        $deduccion = $deduccione;
        $tipoDeduccion = $deduccion->tipo_deduccion;

        $deduccion->delete();

        return redirect()->route('deducciones.index')
                         ->with('success', '¡Deducción de tipo "'.$tipoDeduccion.'" eliminada exitosamente!');

    } catch (\Exception $e) {
        return redirect()->route('deducciones.index')
                         ->with('error', 'No se pudo eliminar la deducción. Error: ' . $e->getMessage());
    }
}
}   
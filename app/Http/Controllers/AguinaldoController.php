<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // ¡Importante! Añadimos la clase para interactuar con la Base de Datos
use App\Exports\AguinaldoExport;
use Maatwebsite\Excel\Facades\Excel;


class AguinaldoController extends Controller
{
    /**
     * Muestra la vista principal del módulo de aguinaldo.
     */
    public function index()
{
    return view('aguinaldo.index');
}

    /**
     * Realiza el cálculo del aguinaldo y muestra los resultados.
     */
    public function calcular(Request $request)
{
    // --- 1. VALIDACIÓN DE DATOS DE ENTRADA ---
    // Nos aseguramos de que los datos del formulario lleguen correctamente.
    $datosValidados = $request->validate([
        'fecha_fin_anio' => 'required|date',
        'dias_aguinaldo' => 'required|integer|min:1'
    ]);

    $fechaFinAnio = $datosValidados['fecha_fin_anio'];
    $diasAguinaldoAPagar = $datosValidados['dias_aguinaldo'];
    $anioDeCalculo = date('Y', strtotime($fechaFinAnio));

    // --- 2. CONSULTA A LA BASE DE DATOS ---
    // Ejecutamos la consulta que planeamos, uniendo empleados, puestos y sucursales.
    $empleados = DB::table('empleados as e')
        ->join('puestos as p', 'e.id_puesto', '=', 'p.id_puesto')
        ->join('sucursales as s', 'e.id_sucursal', '=', 's.id_sucursal')
        ->select(
            'e.nombre_completo',
            'p.nombre_puesto',
            's.nombre_sucursal',
            'e.fecha_ingreso',
            DB::raw('p.salario_mensual / 30 AS salario_diario')
        )
        ->where('e.status', '=', 'Alta')
        ->orderBy('s.nombre_sucursal')
        ->orderBy('e.nombre_completo')
        ->get();

    // --- 3. PROCESAMIENTO Y CÁLCULO DEL AGUINALDO ---
    $resultados = [];
    $totalAguinaldoGeneral = 0;

    foreach ($empleados as $empleado) {
        // Cálculo de días trabajados en el año
        $fechaIngreso = new \DateTime($empleado->fecha_ingreso);
        $inicioAnio = new \DateTime($anioDeCalculo . '-01-01');

        // Si el empleado ingresó antes de este año, se toma el 1 de enero como inicio
        $fechaInicioCalculo = ($fechaIngreso > $inicioAnio) ? $fechaIngreso : $inicioAnio;
        $fechaFinCalculo = new \DateTime($fechaFinAnio);
        
        // Calculamos la diferencia de días
        $diasTrabajados = $fechaFinCalculo->diff($fechaInicioCalculo)->days + 1;
        // Se limita a un máximo de 365 días para el cálculo
        $diasParaCalculo = min($diasTrabajados, 365);
        
        // Fórmula del aguinaldo
        $aguinaldoCalculado = ($empleado->salario_diario * $diasAguinaldoAPagar / 365) * $diasParaCalculo;
        
        $totalAguinaldoGeneral += $aguinaldoCalculado;
        
        // Guardamos los resultados para este empleado
        $resultados[] = [
            'nombre_completo' => $empleado->nombre_completo,
            'nombre_puesto' => $empleado->nombre_puesto,
            'nombre_sucursal' => $empleado->nombre_sucursal,
            'fecha_ingreso' => $empleado->fecha_ingreso,
            'salario_diario' => $empleado->salario_diario,
            'dias_trabajados' => $diasParaCalculo,
            'aguinaldo_a_pagar' => $aguinaldoCalculado,
        ];
    }

    // --- 4. DEVOLVER LA VISTA CON LOS RESULTADOS ---
    // Pasamos los resultados calculados a una nueva vista que crearemos a continuación.
    return view('aguinaldo.resultados', [
        'resultados' => $resultados,
        'totalAguinaldoGeneral' => $totalAguinaldoGeneral
    ]);
}

public function exportar(Request $request)
    {
        // --- Reutilizamos EXACTAMENTE la misma lógica de cálculo de la función calcular() ---
        $datosValidados = $request->validate([
            'fecha_fin_anio' => 'required|date',
            'dias_aguinaldo' => 'required|integer|min:1'
        ]);

        $fechaFinAnio = $datosValidados['fecha_fin_anio'];
        $diasAguinaldoAPagar = $datosValidados['dias_aguinaldo'];
        $anioDeCalculo = date('Y', strtotime($fechaFinAnio));

        $empleados = DB::table('empleados as e')
            ->join('puestos as p', 'e.id_puesto', '=', 'p.id_puesto')
            ->join('sucursales as s', 'e.id_sucursal', '=', 's.id_sucursal')
            ->select('e.nombre_completo', 'p.nombre_puesto', 's.nombre_sucursal', 'e.fecha_ingreso', DB::raw('p.salario_mensual / 30 AS salario_diario'))
            ->where('e.status', '=', 'Alta')
            ->orderBy('s.nombre_sucursal')->orderBy('e.nombre_completo')->get();

        $resultados = [];
        foreach ($empleados as $empleado) {
            $fechaIngreso = new \DateTime($empleado->fecha_ingreso);
            $inicioAnio = new \DateTime($anioDeCalculo . '-01-01');
            $fechaInicioCalculo = ($fechaIngreso > $inicioAnio) ? $fechaIngreso : $inicioAnio;
            $fechaFinCalculo = new \DateTime($fechaFinAnio);
            $diasTrabajados = $fechaFinCalculo->diff($fechaInicioCalculo)->days + 1;
            $diasParaCalculo = min($diasTrabajados, 365);
            $aguinaldoCalculado = ($empleado->salario_diario * $diasAguinaldoAPagar / 365) * $diasParaCalculo;
            
            $resultados[] = [
                'nombre_completo' => $empleado->nombre_completo, 'nombre_puesto' => $empleado->nombre_puesto,
                'nombre_sucursal' => $empleado->nombre_sucursal, 'fecha_ingreso' => $empleado->fecha_ingreso,
                'salario_diario' => $empleado->salario_diario, 'dias_trabajados' => $diasParaCalculo,
                'aguinaldo_a_pagar' => $aguinaldoCalculado,
            ];
        }

        // --- La magia de la exportación ---
        // Se crea una instancia de nuestra clase AguinaldoExport y se le pasan los resultados
        return Excel::download(new AguinaldoExport($resultados), 'Reporte_de_Aguinaldo_'.date('Y-m-d').'.xlsx');
    }

}
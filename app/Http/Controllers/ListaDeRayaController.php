<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sucursal;
use App\Models\Empleado;
use App\Models\Asistencia;
use App\Models\DeduccionEmpleado;
use Carbon\Carbon;
use App\Exports\ListaDeRayaSheetExport;
use App\Exports\ListaDeRayaMultiSucursalExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use App\Models\Tenant;
use App\Models\ConfiguracionNomina;
use Illuminate\Support\Facades\DB;

class ListaDeRayaController extends Controller
{
    /**
     * Muestra la interfaz para generar la lista de raya.
     */
    public function index(Request $request)
    {
        $opcionesPeriodo = $this->getOpcionesPeriodo();
        
        // CORREGIDO: Obtenemos solo las sucursales con estado 'Activa'
        $sucursales = Sucursal::where('status', 'Activa')->orderBy('nombre_sucursal')->get();
        
        $resultados = collect();
        $sucursalSeleccionada = null;
        $esHistorico = false; // <-- AGREGADO PARA DETECTAR FOTOGRAFÍA

        if ($request->filled('periodo') && $request->filled('id_sucursal')) {
            $periodoSeleccionado = $request->input('periodo');
            $idSucursal = $request->input('id_sucursal');
            
            if ($idSucursal == 'todas') {
                $sucursalSeleccionada = (object)['nombre_sucursal' => 'Todas las Sucursales (Solo para Exportar)'];
            } else {
                $sucursalSeleccionada = Sucursal::find($idSucursal);
                
                // <-- VERIFICAMOS SI YA EXISTE UN HISTÓRICO GUARDADO EN BD
                $esHistorico = \App\Models\ListaRayaPeriodo::where('periodo_rango', $periodoSeleccionado)
                    ->where('id_sucursal', $idSucursal)
                    ->exists();

                // Usamos la misma clase de exportación para obtener los resultados para la vista,
                // asegurando que la lógica sea idéntica.
                $export = new ListaDeRayaSheetExport($periodoSeleccionado, (int)$idSucursal);
                $resultados = $export->collection();
            }
        }

        // 🔥 MODIFICACIÓN: Traer la configuración actual para que la vista no salga en blanco
        $configuracion = ConfiguracionNomina::first() ?? new ConfiguracionNomina([
            'descontar_septimo_dia' => 1,
            'metodo_calculo_dias' => 'exactos',
            'pagar_dia_31' => 'todos',
            'redondear_neto' => 1,
        ]);

        return view('lista-de-raya.index', compact(
            'sucursales',
            'opcionesPeriodo',
            'resultados',
            'sucursalSeleccionada',
            'esHistorico', // <-- PASAMOS LA VARIABLE A LA VISTA
            'configuracion' // <-- NUEVA VARIABLE ENVIADA A LA VISTA
        ));
    }

   /**
     * Toma el cálculo dinámico existente y lo guarda en el historial (Borrador).
     */
    public function guardarHistorico(Request $request)
    {
        $request->validate([
            'periodo' => 'required|string',
            'id_sucursal' => 'required|numeric',
        ]);

        $periodoRango = $request->input('periodo');
        $idSucursal = $request->input('id_sucursal');

        DB::beginTransaction();

        try {
            // 1. Ejecutamos tu motor actual para obtener la quincena calculada
            $export = new \App\Exports\ListaDeRayaSheetExport($periodoRango, (int)$idSucursal);
            $resultados = $export->collection();

            if ($resultados->isEmpty()) {
                return back()->with('error', 'No hay datos calculados para guardar en este periodo.');
            }

            // 2. Revisar si ya existe este periodo
            $existe = \App\Models\ListaRayaPeriodo::where('periodo_rango', $periodoRango)
                        ->where('id_sucursal', $idSucursal)
                        ->first();
            
            if ($existe) {
                // Si la nómina ya fue pagada/cerrada, bloqueamos la edición
                if ($existe->status_periodo !== 'Borrador') {
                    return back()->with('error', 'Esta nómina ya está cerrada o pagada y no se puede sobrescribir.');
                }
                
                // Si es Borrador, "rompemos la foto vieja" para tomar una nueva
                $existe->delete(); 
            }

            // 3. Crear el Encabezado del periodo (La nueva foto)
            $periodo = \App\Models\ListaRayaPeriodo::create([
                'periodo_rango'  => $periodoRango,
                'id_sucursal'    => $idSucursal,
                'status_periodo' => 'Borrador'
            ]);

            // 4. Guardar los nuevos detalles actualizados
            foreach ($resultados as $fila) {
                $percepcionesExtra = ($fila['bono_permanencia'] ?? 0) + 
                                     ($fila['bono_cumpleanos'] ?? 0) + 
                                     ($fila['prima_vacacional'] ?? 0);

                $otrasDeducciones = ($fila['deduccion_prestamo'] ?? 0) + 
                                    ($fila['deduccion_prevision'] ?? 0) + 
                                    ($fila['deduccion_caja_ahorro'] ?? 0) + 
                                    ($fila['deduccion_infonavit'] ?? 0) + 
                                    ($fila['deduccion_isr'] ?? 0) + 
                                    ($fila['deduccion_imss'] ?? 0) + 
                                    ($fila['deduccion_otro'] ?? 0);

                \App\Models\ListaRayaDetalle::create([
                    'id_periodo_lista'         => $periodo->id_periodo_lista,
                    'id_empleado'              => $fila['id_empleado'] ?? 0, 
                    'sueldo_mensual_historico' => ($fila['sueldo_quincenal'] ?? 0) * 2, 
                    'sueldo_diario_historico'  => ($fila['sueldo_quincenal'] ?? 0) / 15,
                    'puesto_historico'         => $fila['puesto'] ?? 'General',
                    'dias_periodo'             => $fila['dias_periodo'] ?? 15, 
                    'faltas_directas'          => $fila['faltas_directas'] ?? 0, 
                    'retardos_acumulados'      => 0, 
                    'faltas_por_retardos'      => 0, 
                    'descuento_por_faltas'     => $fila['deduccion_faltas'] ?? 0,
                    'otras_deducciones'        => $otrasDeducciones,
                    'percepciones_extra'       => $percepcionesExtra,
                    'total_neto'               => $fila['neto_a_pagar'] ?? 0,
                ]);
            }

            DB::commit();

            return back()->with('success', 'Histórico actualizado y guardado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error guardando histórico de nómina: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al guardar el histórico: ' . $e->getMessage());
        }
    }

    /**
     * Genera y descarga un reporte de la lista de raya en formato Excel.
     */
    public function exportarExcel(Request $request)
    {
        $request->validate([
            'periodo' => 'required|string',
            'id_sucursal' => 'required|string', // Acepta el valor 'todas'
        ]);

        $periodo = $request->input('periodo');
        $idSucursal = $request->input('id_sucursal');

        list($fechaInicioStr, $fechaFinStr) = explode('_', $periodo);
        $nombrePeriodo = Carbon::parse($fechaInicioStr)->format('Y_m_d') . '_al_' . Carbon::parse($fechaFinStr)->format('Y_m_d');
        
        if ($idSucursal == 'todas') {
            // Exportar todas las sucursales en hojas separadas
            $fileName = "lista_de_raya_todas_{$nombrePeriodo}.xlsx";
            return Excel::download(new ListaDeRayaMultiSucursalExport($periodo), $fileName);
        } else {
            // Exportar una sola sucursal
            $sucursal = Sucursal::findOrFail($idSucursal);
            $nombreSucursal = Str::slug($sucursal->nombre_sucursal);
            $fileName = "lista_de_raya_{$nombreSucursal}_{$nombrePeriodo}.xlsx";
            return Excel::download(new ListaDeRayaSheetExport($periodo, (int)$idSucursal), $fileName);
        }
    }


    /**
     * Guarda la configuración del motor de nómina en la BD del Tenant activo.
     */
    public function guardarConfiguracion(Request $request)
    {
        // 🔥 MODIFICACIÓN: Ya no pedimos retardos_para_falta aquí, se movió a Horarios
        $request->validate([
            'descontar_septimo_dia' => 'required|boolean',
            'metodo_calculo_dias' => 'required|in:exactos,factor,fijos_15',
            'pagar_dia_31' => 'required|in:todos,nuevos,nadie',
            'redondear_neto' => 'required|boolean',
        ]);

        // Buscamos la configuración o creamos una nueva si es la primera vez
        $configuracion = ConfiguracionNomina::first() ?? new ConfiguracionNomina();
        
        $configuracion->descontar_septimo_dia = $request->descontar_septimo_dia;
        $configuracion->metodo_calculo_dias = $request->metodo_calculo_dias;
        $configuracion->pagar_dia_31 = $request->pagar_dia_31;
        $configuracion->redondear_neto = $request->redondear_neto;
        
        $configuracion->save();

        return back()->with('success', 'Configuración de nómina guardada correctamente.');
    }

    /**
     * Elimina el histórico (Borrador) para forzar un nuevo cálculo en vivo.
     */
    public function eliminarBorrador(Request $request)
    {
        $request->validate([
            'periodo' => 'required|string',
            'id_sucursal' => 'required|numeric',
        ]);

        $existe = \App\Models\ListaRayaPeriodo::where('periodo_rango', $request->periodo)
                    ->where('id_sucursal', $request->id_sucursal)
                    ->first();

        if ($existe) {
            if ($existe->status_periodo !== 'Borrador') {
                return back()->with('error', 'No puedes eliminar esta nómina porque ya está Cerrada/Pagada.');
            }
            
            $existe->delete(); // Esto borra la carpeta y todos sus detalles automáticamente
            return back()->with('success', 'Borrador eliminado correctamente. El sistema ha vuelto a calcular los datos en vivo.');
        }

        return back()->with('error', 'No se encontró un borrador para eliminar.');
    }

    /**
     * Helper para generar las opciones de periodo.
     */
    private function getOpcionesPeriodo(): array
    {
        $opcionesPeriodo = [];
        $fechaActual = Carbon::now();
        for ($i = 0; $i < 13; $i++) {
            $fecha = $fechaActual->copy()->subMonths($i);
            // 1ra Quincena
            $inicioQuincena1 = $fecha->copy()->startOfMonth();
            $finQuincena1 = $fecha->copy()->startOfMonth()->addDays(14);
            $valor1 = $inicioQuincena1->toDateString() . '_' . $finQuincena1->toDateString();
            $texto1 = '1ra Quincena ' . $inicioQuincena1->translatedFormat('F Y');
            if(!in_array($texto1, array_column($opcionesPeriodo, 'texto'))) {
                $opcionesPeriodo[] = ['valor' => $valor1, 'texto' => $texto1];
            }
            // 2da Quincena
            $inicioQuincena2 = $fecha->copy()->startOfMonth()->addDays(15);
            $finQuincena2 = $fecha->copy()->endOfMonth();
            $valor2 = $inicioQuincena2->toDateString() . '_' . $finQuincena2->toDateString();
            $texto2 = '2da Quincena ' . $inicioQuincena2->translatedFormat('F Y');
            if(!in_array($texto2, array_column($opcionesPeriodo, 'texto'))) {
                 $opcionesPeriodo[] = ['valor' => $valor2, 'texto' => $texto2];
            }
        }
        return $opcionesPeriodo;
    }
}
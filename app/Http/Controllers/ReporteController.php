<?php

namespace App\Http\Controllers;

// --- LÍNEAS DE IMPORTACIÓN CONSOLIDADAS ---
use App\Exports\GastosPorSucursalExport;
use App\Exports\IncomeStatementExport;
use App\Exports\TrialBalanceExport;
use App\Models\Account;
use App\Models\Categoria;
use App\Models\Gasto;
use App\Models\Patron;
use App\Models\Recovery;
use App\Models\Sucursal;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BalanceSheetExport;

class ReporteController extends Controller
{
    /**
     * Muestra el reporte de gastos pivoteado por sucursal y categoría.
     */
    public function gastosPorSucursal(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', now()->endOfMonth()->toDateString());
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $gastos = Gasto::with(['categoria', 'sucursal'])
                          ->whereBetween('fecha_gasto', [$fechaInicio, $fechaFin])
                          ->get();
        $datosPivoteados = $gastos->groupBy('categoria.nombre')->map(function ($gastosPorCategoria) {
            return $gastosPorCategoria->groupBy('sucursal.nombre_sucursal')->map(function ($gastos) {
                return $gastos->sum('monto_total');
            });
        });
        $categoriasConGastos = Categoria::whereIn('id', $gastos->pluck('categoria_id'))->orderBy('nombre')->get();
        
        return view('reportes.gastos_por_sucursal', [
            'sucursales' => $sucursales,
            'categorias' => $categoriasConGastos,
            'datosPivoteados' => $datosPivoteados,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
        ]);
    }

    public function exportarGastosPorSucursal(Request $request)
    {
        $fechaInicio = $request->query('fecha_inicio', now()->startOfMonth()->toDateString());
        $fechaFin = $request->query('fecha_fin', now()->endOfMonth()->toDateString());
        $nombreArchivo = 'ReporteGastosPorSucursal_' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new GastosPorSucursalExport($fechaInicio, $fechaFin), $nombreArchivo);
    }

    /**
     * Muestra la Balanza de Comprobación.
     */
    public function trialBalance(Request $request)
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $accounts = Account::with('children')->whereNull('parent_id')->orderBy('code')->get();
        return view('reportes.trial_balance', compact('accounts', 'startDate', 'endDate', 'sucursales'));
    }

    /**
     * Muestra el Estado de Resultados.
     */
    public function incomeStatement(Request $request)
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $selectedSucursalId = $request->input('sucursal_id');
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // Extraemos mes y año para filtrar la tabla Recovery correctamente
        $startMonth = Carbon::parse($startDate)->month;
        $startYear = Carbon::parse($startDate)->year;
        $endMonth = Carbon::parse($endDate)->month;
        $endYear = Carbon::parse($endDate)->year;

        // 1. Ingresos (Cambiamos 'created_at' por las fechas reales del negocio: year y month)
        $incomeQuery = Recovery::whereBetween('year', [$startYear, $endYear])
                               ->whereBetween('month', [$startMonth, $endMonth]);
        if ($selectedSucursalId) {
            $incomeQuery->where('sucursal_id', $selectedSucursalId);
        }
        $totalInterest = $incomeQuery->sum('interest_collected');
        
        // 2. Gastos Operativos (Cambiamos 'updated_at' por 'fecha_gasto')
        $opExpensesQuery = Gasto::where('estado', 'Aprobado')->whereBetween('fecha_gasto', [$startDate, $endDate]);
        if ($selectedSucursalId) {
            $opExpensesQuery->where('sucursal_id', $selectedSucursalId);
        }
        $totalOperationalExpenses = $opExpensesQuery->sum('monto_total');

        // 3. Castigos (Usamos la misma lógica de fechas reales)
        $unrecoverableQuery = Recovery::whereBetween('year', [$startYear, $endYear])
                                      ->whereBetween('month', [$startMonth, $endMonth]);
        if ($selectedSucursalId) {
            $unrecoverableQuery->where('sucursal_id', $selectedSucursalId);
        }
        $totalUnrecoverable = $unrecoverableQuery->sum('unrecoverable_amount');

        $operatingProfit = $totalInterest - $totalOperationalExpenses;
        $netIncome = $operatingProfit - $totalUnrecoverable;

        return view('reportes.income_statement', compact(
            'sucursales', 'selectedSucursalId', 'totalInterest', 'totalOperationalExpenses',
            'totalUnrecoverable', 'operatingProfit', 'netIncome', 'startDate', 'endDate'
        ));
    }
    
    /**
     * Exporta la Balanza de Comprobación a Excel.
     */
    public function exportTrialBalance(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->endOfMonth()->toDateString());
        $fileName = "Balanza_de_Comprobacion_{$startDate}_a_{$endDate}.xlsx";
        return Excel::download(new TrialBalanceExport($startDate, $endDate), $fileName);
    }
    
    /**
     * Exporta el Estado de Resultados a Excel.
     */
    public function exportIncomeStatement(Request $request)
    {
        $selectedSucursalId = $request->query('sucursal_id');
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->endOfMonth()->toDateString());

        $startMonth = Carbon::parse($startDate)->month;
        $startYear = Carbon::parse($startDate)->year;
        $endMonth = Carbon::parse($endDate)->month;
        $endYear = Carbon::parse($endDate)->year;

        $incomeQuery = Recovery::whereBetween('year', [$startYear, $endYear])->whereBetween('month', [$startMonth, $endMonth]);
        if ($selectedSucursalId) { $incomeQuery->where('sucursal_id', $selectedSucursalId); }
        $totalInterest = $incomeQuery->sum('interest_collected');
        
        $opExpensesQuery = Gasto::where('estado', 'Aprobado')->whereBetween('fecha_gasto', [$startDate, $endDate]);
        if ($selectedSucursalId) { $opExpensesQuery->where('sucursal_id', $selectedSucursalId); }
        $totalOperationalExpenses = $opExpensesQuery->sum('monto_total');

        $unrecoverableQuery = Recovery::whereBetween('year', [$startYear, $endYear])->whereBetween('month', [$startMonth, $endMonth]);
        if ($selectedSucursalId) { $unrecoverableQuery->where('sucursal_id', $selectedSucursalId); }
        $totalUnrecoverable = $unrecoverableQuery->sum('unrecoverable_amount');

        $operatingProfit = $totalInterest - $totalOperationalExpenses;
        $netIncome = $operatingProfit - $totalUnrecoverable;
        
        $data = compact(
            'totalInterest', 'totalOperationalExpenses', 'totalUnrecoverable', 'operatingProfit', 'netIncome', 'startDate', 'endDate'
        );

        $fileName = "Estado_de_Resultados_{$startDate}_a_{$endDate}.xlsx";
        return Excel::download(new IncomeStatementExport($data), $fileName);
    }
    /**
     * Exporta el Estado de Resultados a PDF.
     */
    public function exportIncomeStatementPDF(Request $request)
    {
        $selectedSucursalId = $request->query('sucursal_id');
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->endOfMonth()->toDateString());
        
        $startMonth = Carbon::parse($startDate)->month;
        $startYear = Carbon::parse($startDate)->year;
        $endMonth = Carbon::parse($endDate)->month;
        $endYear = Carbon::parse($endDate)->year;

        $incomeQuery = Recovery::whereBetween('year', [$startYear, $endYear])->whereBetween('month', [$startMonth, $endMonth]);
        if ($selectedSucursalId) { $incomeQuery->where('sucursal_id', $selectedSucursalId); }
        $totalInterest = $incomeQuery->sum('interest_collected');
        
        $opExpensesQuery = Gasto::where('estado', 'Aprobado')->whereBetween('fecha_gasto', [$startDate, $endDate]);
        if ($selectedSucursalId) { $opExpensesQuery->where('sucursal_id', $selectedSucursalId); }
        $totalOperationalExpenses = $opExpensesQuery->sum('monto_total');
        
        $unrecoverableQuery = Recovery::whereBetween('year', [$startYear, $endYear])->whereBetween('month', [$startMonth, $endMonth]);
        if ($selectedSucursalId) { $unrecoverableQuery->where('sucursal_id', $selectedSucursalId); }
        $totalUnrecoverable = $unrecoverableQuery->sum('unrecoverable_amount');
        
        $operatingProfit = $totalInterest - $totalOperationalExpenses;
        $netIncome = $operatingProfit - $totalUnrecoverable;
        
        $companyName = $request->query('company_name', 'Nombre de Empresa no Especificado');
        $legalRepresentative = $request->query('legal_representative', 'Nombre del Representante no Especificado');
        
        $data = compact(
            'companyName',
            'legalRepresentative',
            'totalInterest', 'totalOperationalExpenses', 'totalUnrecoverable',
            'operatingProfit', 'netIncome', 'startDate', 'endDate'
        );
        
        $pdf = Pdf::loadView('reportes.pdfs.income_statement_pdf', $data);
        $fileName = "Estado_de_Resultados_{$startDate}_a_{$endDate}.pdf";
        return $pdf->stream($fileName);
    }

    /**
     * Llama a la API de IA para generar un análisis financiero.
     */
    public function generateAnalysis(Request $request)
    {
        try {
            // 1. Cambiamos 'numeric' por 'string' para que acepte comas y signos de pesos
            $data = $request->validate([
                'ingresos' => 'required|string', 'gastos' => 'required|string',
                'castigos' => 'required|string', 'utilidad' => 'required|string',
                'inicio' => 'required|date', 'fin' => 'required|date',
            ]);

            $prompt = "Actúa como un asesor financiero profesional para una pequeña financiera en México. Analiza el siguiente resumen de un Estado de Resultados para el periodo del {$data['inicio']} al {$data['fin']}. Los datos son: Ingresos Totales por Intereses: {$data['ingresos']}, Gastos Operativos Totales: {$data['gastos']}, Gastos por Cuentas Incobrables (Castigos): {$data['castigos']}, y una Utilidad Neta de: {$data['utilidad']}. Proporciona un análisis breve y claro en 2 o 3 párrafos. Explica qué significan estos números, destaca un punto positivo, un punto a vigilar, y ofrece una recomendación general. Utiliza un lenguaje fácil de entender para alguien que no es contador. Estructura la respuesta con los siguientes títulos en negritas: **Análisis General**, **Punto Clave Positivo**, **Foco de Atención**, y **Recomendación**.";

            $apiKey = env('GEMINI_API_KEY', '');
            if (empty($apiKey)) {
                return response()->json(['error' => 'API Key no configurada en el servidor.'], 500);
            }
            
            // Usamos la versión estable y ultrarrápida 1.5-flash
            $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";
            
            $response = Http::timeout(30)->post($apiUrl, [
                'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]]
            ]);

            if ($response->successful()) {
                $analysisText = $response->json('candidates.0.content.parts.0.text', 'No se pudo generar el texto del análisis.');
                return response()->json(['analysis' => $analysisText]);
            } else {
                Log::error('Error en API de IA:', ['status' => $response->status(), 'body' => $response->body()]);
                return response()->json(['error' => 'La API de IA rechazó la solicitud. Detalles en logs.'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Excepción en generateAnalysis:', ['message' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



// --- NUEVOS MÉTODOS PARA BALANCE GENERAL ---

    // ... dentro de ReporteController ...

    public function balanceSheet(Request $request)
    {
        $endDate = $request->input('end_date', Carbon::now()->toDateString());
        $selectedSucursalId = $request->input('sucursal_id'); // <--- NUEVO: Captura sucursal
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();

        // Buscamos las cuentas raíz (asegúrate que estos códigos existan)
        $assetAccount = Account::where('code', '100')->first();
        $liabilityAccount = Account::where('code', '200')->first();
        $equityAccount = Account::where('code', '300')->first();
        
        // Cuentas para calcular la Utilidad del Periodo (Ingresos 400 vs Gastos 600)
        $incomeAccount = Account::where('code', '400')->first();
        $expenseAccounts = Account::whereIn('code', ['600', '800'])->get();

        return view('reportes.balance_sheet', compact(
            'endDate', 'selectedSucursalId', 'sucursales', 
            'assetAccount', 'liabilityAccount', 'equityAccount', 
            'incomeAccount', 'expenseAccounts'
        ));
    }

    private function getBalanceSheetData(Request $request)
    {
        $endDate = $request->input('end_date', $request->query('end_date', now()->toDateString()));
        $sucursalId = $request->input('sucursal_id', $request->query('sucursal_id'));

        $assetAccount = Account::where('code', '100')->first();
        $liabilityAccount = Account::where('code', '200')->first();
        $equityAccount = Account::where('code', '300')->first();
        $incomeAccount = Account::where('code', '400')->first();
        $expenseAccounts = Account::whereIn('code', ['600', '800'])->get();

        $totalAssets = $assetAccount ? $assetAccount->getInitialBalance($endDate, $sucursalId) : 0;
        $totalLiabilities = $liabilityAccount ? $liabilityAccount->getInitialBalance($endDate, $sucursalId) : 0;
        
        // --- INICIO DE LA CORRECCIÓN ---
        // Obtenemos el 1 de enero del año de la fecha final seleccionada
        $startOfYear = Carbon::parse($endDate)->startOfYear()->toDateString();

        // Cálculo de Utilidad Neta del Ejercicio (SOLO suma lo del año en curso)
        $incomeMovements = $incomeAccount ? $incomeAccount->getMovements($startOfYear, $endDate, $sucursalId) : ['debits' => 0, 'credits' => 0];
        $totalIncome = $incomeMovements['credits'] - $incomeMovements['debits'];
        
        $totalExpenses = 0;
        foreach($expenseAccounts as $expenseAccount) {
            $expenseMovements = $expenseAccount->getMovements($startOfYear, $endDate, $sucursalId);
            $totalExpenses += $expenseMovements['debits'] - $expenseMovements['credits'];
        }
        // --- FIN DE LA CORRECCIÓN ---

        $netIncomeForPeriod = $totalIncome - $totalExpenses;
        $equityBalance = $equityAccount ? $equityAccount->getInitialBalance($endDate, $sucursalId) : 0;
        $totalEquity = $equityBalance + $netIncomeForPeriod;
        
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;
        $companyName = $request->query('company_name', 'Nombre de Empresa no Especificado');

        return compact(
            'endDate', 'companyName', 'sucursalId', 'assetAccount', 'liabilityAccount', 
            'equityAccount', 'incomeAccount', 'expenseAccounts',
            'totalAssets', 'totalLiabilities', 'netIncomeForPeriod', 'totalEquity', 'totalLiabilitiesAndEquity'
        );
    }
    
    public function exportBalanceSheet(Request $request)
    {
        $data = $this->getBalanceSheetData($request);
        $fileName = "Balance_General_al_{$data['endDate']}.xlsx";
        // La clase de exportación ahora se encuentra correctamente.
        return Excel::download(new BalanceSheetExport($data['endDate'], $data), $fileName);
    }


    public function exportBalanceSheetPDF(Request $request)
    {
        $data = $this->getBalanceSheetData($request);
        $pdf = Pdf::loadView('reportes.pdfs.balance_sheet_pdf', $data);
        $fileName = "Balance_General_al_{$data['endDate']}.pdf";
        return $pdf->stream($fileName);
    }
    
    public function generateBalanceSheetAnalysis(Request $request)
    {
        try {
            // Cambiamos 'numeric' por 'string'
            $data = $request->validate([
                'activos' => 'required|string',
                'pasivos' => 'required|string',
                'capital' => 'required|string',
            ]);
            
            $prompt = "Actúa como un asesor financiero para una pyme en México. Analiza este Balance General resumido: Total de Activos: {$data['activos']}, Total de Pasivos: {$data['pasivos']}, Total de Capital Contable: {$data['capital']}. Explica en 2 o 3 párrafos qué significa esta 'fotografía' financiera. Menciona la solvencia de la empresa (si los activos cubren las deudas) y su estructura de capital (qué tanto se financia con deuda vs. recursos propios). Ofrece una recomendación general. Usa un lenguaje claro y fácil de entender.";
            
            $apiKey = env('GEMINI_API_KEY', '');
            if (empty($apiKey)) return response()->json(['error' => 'La clave de API no está configurada.'], 500);
            
            $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";
            $response = Http::timeout(30)->post($apiUrl, ['contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]]]);

            if ($response->successful()) {
                $analysisText = $response->json('candidates.0.content.parts.0.text', 'No se pudo generar el texto del análisis.');
                return response()->json(['analysis' => $analysisText]);
            } else {
                // AQUÍ ESTÁ LA MAGIA: Le decimos que nos devuelva el error real de Google
                return response()->json([
                    'error' => 'Google dice: ' . $response->body()
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function dashboardGerencial(Request $request)
    {
        // Por defecto vemos el mes actual
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        $startMonth = \Carbon\Carbon::parse($startDate)->month;
        $startYear = \Carbon\Carbon::parse($startDate)->year;
        $endMonth = \Carbon\Carbon::parse($endDate)->month;
        $endYear = \Carbon\Carbon::parse($endDate)->year;

        $sucursales = \App\Models\Sucursal::all();
        $rentabilidad = [];
        $totalIngresosEmpresa = 0; // Se refiere a intereses
        $totalCapitalEmpresa = 0;   // Nueva variable para capital
        $totalGastosEmpresa = 0;

        foreach ($sucursales as $sucursal) {
            // 1. Obtener Capital e Interés de la tabla Recovery
            // Usamos selectRaw para traer ambos montos en una sola consulta por sucursal
            $datosRecovery = \App\Models\Recovery::where('sucursal_id', $sucursal->id_sucursal)
                ->whereBetween('year', [$startYear, $endYear])
                ->whereBetween('month', [$startMonth, $endMonth])
                ->selectRaw('SUM(capital_recovered) as cap, SUM(interest_collected) as int')
                ->first();

            $capitalRecuperado = $datosRecovery->cap ?? 0;
            $ingresosInteres = $datosRecovery->int ?? 0;

            // 2. Gastos de esta sucursal
            $gastos = \App\Models\Gasto::where('sucursal_id', $sucursal->id_sucursal)
                ->where('estado', 'Aprobado')
                ->whereBetween('fecha_gasto', [$startDate, $endDate])
                ->sum('monto_total');

            // 3. Utilidad Real (Interés - Gastos)
            $utilidad = $ingresosInteres - $gastos;
            
            // 4. Margen de Rentabilidad (%)
            $margen = $ingresosInteres > 0 ? round(($utilidad / $ingresosInteres) * 100, 2) : 0;

            $rentabilidad[] = [
                'nombre' => $sucursal->nombre_sucursal,
                'capital' => (float)$capitalRecuperado,
                'ingresos' => (float)$ingresosInteres,
                'gastos' => (float)$gastos,
                'utilidad' => (float)$utilidad,
                'margen' => $margen,
                'estatus' => $utilidad > 0 ? 'Rentable' : ($ingresosInteres == 0 && $gastos == 0 ? 'Sin Movimientos' : 'Pérdida')
            ];

            $totalIngresosEmpresa += $ingresosInteres;
            $totalCapitalEmpresa += $capitalRecuperado;
            $totalGastosEmpresa += $gastos;
        }

        // Ordenamos el arreglo para el Ranking (de la que más gana a la que más pierde)
        usort($rentabilidad, function($a, $b) {
            return $b['utilidad'] <=> $a['utilidad'];
        });

        // Para la gráfica de Gasto Promedio (Pastel) de toda la empresa
        $gastosPorCategoria = \App\Models\Gasto::with('categoria')
            ->where('estado', 'Aprobado')
            ->whereBetween('fecha_gasto', [$startDate, $endDate])
            ->get()
            ->groupBy('categoria.nombre')
            ->map(function ($row) {
                return $row->sum('monto_total');
            });

        return view('reportes.dashboard', compact(
            'rentabilidad', 
            'totalIngresosEmpresa', 
            'totalCapitalEmpresa', 
            'totalGastosEmpresa', 
            'gastosPorCategoria', 
            'startDate', 
            'endDate'
        ));
    }

    public function reporteEjecutivoPDF(Request $request)
    {
        // 1. Verificación de seguridad
        if (!auth()->user()->can('descargar-reporte-ejecutivo-ia')) {
            abort(403, 'No tienes permiso para generar este análisis estratégico.');
        }

        set_time_limit(90);

        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        $startCarbon = Carbon::parse($startDate);
        $endCarbon = Carbon::parse($endDate);
        
        $periodoMeses = [];
        $tempDate = $startCarbon->copy()->startOfMonth();
        while ($tempDate <= $endCarbon) {
            $periodoMeses[] = [
                'month' => $tempDate->month,
                'year' => $tempDate->year,
                'label' => $tempDate->translatedFormat('F Y')
            ];
            $tempDate->addMonth();
        }

        $sucursales = \App\Models\Sucursal::all();
        $statsGlobales = [];

        foreach ($sucursales as $s) {
            $historialMeses = [];
            $totalSucurInt = 0;
            $totalSucurCap = 0;
            $totalSucurCol = 0;
            $totalSucurGas = 0;

            foreach ($periodoMeses as $mes) {
                $rec = \App\Models\Recovery::where('sucursal_id', $s->id_sucursal)
                    ->where('year', $mes['year'])
                    ->where('month', $mes['month'])
                    ->selectRaw('SUM(capital_recovered) as cap, SUM(interest_collected) as int')->first();

                $col = \App\Models\Placement::where('sucursal_id', $s->id_sucursal)
                    ->where('year', $mes['year'])
                    ->where('month', $mes['month'])
                    ->sum('amount');

                $gas = \App\Models\Gasto::where('sucursal_id', $s->id_sucursal)
                    ->where('estado', 'Aprobado')
                    ->whereYear('fecha_gasto', $mes['year'])
                    ->whereMonth('fecha_gasto', $mes['month'])
                    ->sum('monto_total');

                $historialMeses[$mes['label']] = [
                    'colocacion' => (float)$col,
                    'intereses' => (float)($rec->int ?? 0),
                    'gastos' => (float)$gas,
                    'utilidad' => (float)(($rec->int ?? 0) - $gas)
                ];

                $totalSucurInt += ($rec->int ?? 0);
                $totalSucurCap += ($rec->cap ?? 0);
                $totalSucurCol += $col;
                $totalSucurGas += $gas;
            }

            $esAdministrativa = str_contains(strtolower($s->nombre_sucursal), 'ejecutiva');

            $statsGlobales[] = [
                'sucursal' => $s->nombre_sucursal,
                'colocacion' => $totalSucurCol,
                'intereses' => $totalSucurInt,
                'gastos' => $totalSucurGas,
                'utilidad' => $totalSucurInt - $totalSucurGas,
                'tipo' => $esAdministrativa ? 'Administrativa' : 'Operativa',
                'evolucion' => $historialMeses 
            ];
        }

        $jsonIA = json_encode($statsGlobales);
        $esMultimes = count($periodoMeses) > 1;

        // Base de la personalidad: Carlos Curiel & Facturame.org
        $contextoPersonal = "Te llamas Carlos Curiel y representas a Facturame.org. Estás entregando un informe de resultados al dueño de la financiera.";

        if ($esMultimes) {
            $prompt = "$contextoPersonal Analiza estos datos de " . count($periodoMeses) . " meses: $jsonIA. 
                       REGLAS:
                       1. Habla de forma clara, profesional pero sencilla. Evita palabras en inglés como 'deep dive' o 'burn rate'.
                       2. La sucursal 'EJECUTIVA' es el gasto de oficina central; no la critiques por no vender, solo di si el gasto es razonable.
                       ESTRUCTURA:
                       * **Resumen de la Operación:** ¿Cómo va el negocio en general? (Máximo 3 líneas).
                       * **Sucursales que más aportan:** Cuáles son las que están dando más dinero real.
                       * **Puntos a revisar:** Donde hay gastos altos o poca actividad.
                       * **Qué hacer ahora:** 3 consejos prácticos y sencillos para el dueño.";
        } else {
            $prompt = "$contextoPersonal Analiza el rendimiento de este mes: $jsonIA.
                       REGLAS:
                       1. Prohibido decir que faltan datos históricos. Analiza lo que hay.
                       2. Usa un lenguaje que un dueño de negocio entienda a la primera (Ingresos, Gastos, Ganancia).
                       3. La sucursal 'EJECUTIVA' es tu oficina central. Solo menciona si su costo se puede cubrir bien con las otras sucursales.
                       ESTRUCTURA:
                       * **Resultado del Mes:** Una frase poderosa sobre la utilidad total.
                       * **Mejores Sucursales:** Quiénes hicieron mejor el trabajo este mes.
                       * **Atención Urgente:** Qué sucursales están inactivas o gastando de más.
                       * **Plan de Acción:** 3 pasos claros para mejorar la próxima semana.";
        }

        $analysis = $this->llamarGemini($prompt, 'gemini-2.5-pro');
        
        $data = [
            'stats' => $statsGlobales,
            'periodoMeses' => $periodoMeses,
            'analysis' => $analysis,
            'rangoFechas' => "del $startDate al $endDate",
            'esMultimes' => count($periodoMeses) > 1,
            'fecha' => now()->format('d/m/Y')
        ];

        return Pdf::loadView('reportes.pdfs.ejecutivo_evolutivo', $data)
                  ->setPaper('a4', 'landscape')
                  ->stream("Reporte_BI_Evolutivo.pdf");
    }

    // Helper privado usando EXACTAMENTE la versión que a ti te funciona
    // Añadimos el parámetro $modelo, por defecto usamos flash para que no rompa lo demás
    private function llamarGemini($prompt, $modelo = 'gemini-1.5-flash') {
        try {
            // Inyectamos la variable $modelo en la URL
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelo}:generateContent?key=" . env('GEMINI_API_KEY');
            
            $response = Http::timeout(90) // Subimos a 90s porque el Pro piensa más profundo
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]]
                ]);
                
            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text');
            } else {
                return "Aviso: No se pudo generar el análisis. Error: " . $response->status();
            }
        } catch (\Exception $e) { 
            return "Aviso: Error de conexión con el servidor de IA."; 
        }
    }
}

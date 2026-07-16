<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\PuestoController;
use App\Http\Controllers\PatronController;
use App\Http\Controllers\VacacionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ImssController;
use App\Http\Controllers\DeduccionController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\ListaDeRayaController;
use App\Http\Controllers\FiniquitoController;
use App\Http\Controllers\AguinaldoController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\PlacementController;
use App\Http\Controllers\RecoveryController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CreditoController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReconciliationController;
use App\Http\Controllers\Api\CodigoPostalController;
use App\Models\CodigoPostal;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request; 

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/test-cp-model', function () {
    try {
        $modelo = new CodigoPostal();
        $conexionDelModelo = $modelo->getConnectionName();

        echo "La conexión por defecto de la aplicación en este momento es: <b>" . DB::getDefaultConnection() . "</b><br>";
        echo "El modelo 'CodigoPostal' está configurado para usar la conexión: <b>" . $conexionDelModelo . "</b><br><br>";

        if ($conexionDelModelo === 'pgsql') {
            echo "<h2><span style='color:green;'>¡CONFIGURACIÓN CORRECTA!</span></h2> Tu modelo <u>app/Models/CodigoPostal.php</u> está bien configurado en el servidor. El problema es 100% la caché de Laravel.";
        } else {
            echo "<h2><span style='color:red;'>¡PROBLEMA ENCONTRADO!</span></h2> Tu modelo sigue usando la conexión '{$conexionDelModelo}'. El cambio `protected \$connection = 'pgsql';` <b>NO</b> está presente o no se está leyendo en el código que se ejecuta en el servidor.";
        }
    } catch (\Exception $e) {
        return "Ocurrió un error al probar el modelo: " . $e->getMessage();
    }
});

// --- RUTAS PÚBLICAS ---
Route::get('/', function () {
    return view('auth.login');
});

// Rutas de Asistencia (parte pública - SIMPLIFICADA)
Route::get('/asistencia', [AsistenciaController::class, 'index'])->name('asistencia.index');
Route::post('/asistencia/registrar-entrada', [AsistenciaController::class, 'registrarEntrada'])->name('asistencia.registrarEntrada');

// --- RUTAS QUE REQUIEREN AUTENTICACIÓN ---
Route::middleware('auth')->group(function () {

    // --- DASHBOARD Y PERFIL ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- MÓDULO DE PRUEBA ---
    Route::get('/prueba', [PruebaController::class, 'index'])
        ->name('prueba.index')
        ->middleware('can:ver-modulo-prueba');

    // --- SECCIÓN DE CRÉDITOS ---
    Route::resource('clientes', ClienteController::class);
    Route::resource('groups', GroupController::class);
    Route::post('/groups/{group}/add-member', [GroupController::class, 'addMember'])->name('groups.members.add');
    Route::post('/groups/{group}/remove-member/{client}', [GroupController::class, 'removeMember'])->name('groups.members.remove');

    Route::resource('creditos', CreditoController::class);

    // --- RECURSOS HUMANOS ---
    Route::middleware('can:ver-menu-rh')->group(function () {
        // Empleados
        Route::resource('empleados', EmpleadoController::class)->middleware('can:ver-empleados');
        Route::get('/empleados/{empleado}/historial-contratos', [EmpleadoController::class, 'historialContratos'])->name('empleados.contratos.historial')->middleware('can:ver-contratos');
        Route::put('/empleados/{empleado}/reactivar', [EmpleadoController::class, 'reactivar'])->name('empleados.reactivar')->middleware('can:editar-empleados');

        // Contratos
        Route::resource('contratos', ContratoController::class)->middleware('can:ver-contratos');
        Route::get('/contratos/{contrato}/pdf', [ContratoController::class, 'generarPdf'])->name('contratos.pdf')->middleware('can:imprimir-contratos');
        Route::get('contratos/{id}/ver-firmado', [ContratoController::class, 'verContratoFirmado'])->name('contratos.verFirmado')->middleware('can:ver-contratos');
        Route::get('/contratos/exportar/excel', [ContratoController::class, 'exportarExcel'])->name('contratos.exportarExcel')->middleware('can:exportar-contratos');

        // Asistencias (vistas de admin - CORREGIDAS)
        Route::get('/asistencia/vista-periodo', [AsistenciaController::class, 'index'])->name('asistencia.vistaPeriodo')->middleware('can:ver-asistencias');
        Route::get('/asistencia/resumen-incidencias', [AsistenciaController::class, 'resumenIncidencias'])->name('asistencia.resumenIncidencias')->middleware('can:ver-asistencias');
        Route::get('/asistencia/exportar-pdf', [AsistenciaController::class, 'exportarResumenPDF'])->name('asistencia.exportarPDF')->middleware('can:ver-asistencias');

        // Vacaciones
        Route::resource('vacaciones', VacacionController::class)->only(['index', 'create', 'store'])->middleware('can:ver-vacaciones');
        Route::get('/vacaciones/historial/{empleado}', [VacacionController::class, 'historialPorEmpleado'])->name('vacaciones.historial')->middleware('can:ver-vacaciones');

        // Deducciones
        Route::resource('deducciones', DeduccionController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
            ->middleware('can:ver-deducciones');
        Route::get('/deducciones/exportar', [DeduccionController::class, 'exportarExcel'])->name('deducciones.exportar');

        // Lista de Raya
        Route::get('/lista-de-raya', [ListaDeRayaController::class, 'index'])->name('lista_de_raya.index')->middleware('can:ver-lista-raya');
        Route::get('/lista-de-raya/exportar', [ListaDeRayaController::class, 'exportarExcel'])->name('lista_de_raya.exportar')->middleware('can:exportar-lista-raya');

        // Finiquitos y Liquidaciones
        Route::get('/finiquitos', [FiniquitoController::class, 'index'])->name('finiquitos.index')->middleware('can:ver-finiquitos');
        Route::post('/finiquitos/calcular', [FiniquitoController::class, 'calcular'])->name('finiquitos.calcular')->middleware('can:calcular-finiquitos');
        Route::post('/finiquitos/exportar-pdf', [FiniquitoController::class, 'exportarPDF'])->name('finiquitos.export.pdf')->middleware('can:exportar-finiquitos');
        Route::post('/finiquitos/exportar-excel', [FiniquitoController::class, 'exportarExcel'])->name('finiquitos.export.excel')->middleware('can:exportar-finiquitos');
        Route::post('/finiquitos/exportar-renuncia-pdf', [FiniquitoController::class, 'exportarRenunciaPdf'])->name('finiquitos.export.renuncia.pdf')->middleware('can:exportar-finiquitos');
        Route::post('/finiquitos/{empleado}/upload-signed', [FiniquitoController::class, 'uploadSigned'])->name('finiquitos.uploadSigned');
        Route::get('/finiquitos/{empleado}/view-signed', [FiniquitoController::class, 'viewSigned'])->name('finiquitos.viewSigned');
        Route::get('/finiquitos/aviso-terminacion/{id_empleado}', [App\Http\Controllers\FiniquitoController::class, 'generarAvisoTerminacion'])->name('finiquitos.avisoTerminacion');
        Route::get('/vacaciones/historial-json/{id}', [VacacionController::class, 'historialJson']);

        // --- Módulo de Renuncia Voluntaria ---
        Route::get('/renuncias/crear', [App\Http\Controllers\RenunciaController::class, 'create'])
             ->name('renuncias.create')
             ->middleware('can:ver-renuncias');

        // Gestión IMSS
        Route::post('/renuncias/exportar-pdf', [App\Http\Controllers\RenunciaController::class, 'exportarPdf'])
             ->name('renuncias.exportar.pdf')
             ->middleware('can:generar-renuncias');

        Route::get('/imss', [ImssController::class, 'index'])->name('imss.index')->middleware('can:ver-gestion-imss');
        Route::post('/imss/{empleado}/registrar-alta', [ImssController::class, 'registrarAlta'])->name('imss.registrarAlta')->middleware('can:tramitar-imss');
        Route::post('/imss/{empleado}/registrar-baja', [ImssController::class, 'registrarBaja'])->name('imss.registrarBaja')->middleware('can:tramitar-imss');
        Route::get('/imss/{empleado}/acuse-alta-pdf', [ImssController::class, 'generarAcuseAltaPdf'])->name('imss.acuseAltaPdf')->middleware('can:tramitar-imss');
        Route::get('/imss/{empleado}/carta-patronal-pdf', [ImssController::class, 'generarCartaPatronal'])->name('imss.cartaPatronalPdf')->middleware('can:tramitar-imss');
    });

    // --- CONTABILIDAD ---
    Route::middleware('can:ver-menu-contabilidad')->group(function () {
        // Aguinaldo
        Route::get('/aguinaldo', [AguinaldoController::class, 'index'])->name('aguinaldo.index')->middleware('can:ver-aguinaldo');
        Route::post('/aguinaldo/calcular', [AguinaldoController::class, 'calcular'])->name('aguinaldo.calcular')->middleware('can:calcular-aguinaldo');
        Route::post('/aguinaldo/exportar', [AguinaldoController::class, 'exportar'])->name('aguinaldo.exportar')->middleware('can:exportar-aguinaldo');

        // Gastos
        Route::get('/gastos/aprobaciones', [GastoController::class, 'approvalIndex'])->name('gastos.approvals')->middleware('can:aprobar-gastos');
        Route::get('/gastos/crear', [GastoController::class, 'create'])->name('gastos.create');

        Route::post('/gastos/{gasto}/aprobar', [GastoController::class, 'approve'])->name('gastos.approve')->middleware('can:aprobar-gastos');
        Route::post('/gastos/{gasto}/rechazar', [GastoController::class, 'reject'])->name('gastos.reject')->middleware('can:aprobar-gastos');
        Route::get('/gastos/{gasto}/comprobante', [GastoController::class, 'verComprobante'])->name('gastos.verComprobante');

        Route::resource('gastos', GastoController::class)->except(['create', 'show']);

        // --- REPORTES ---
        Route::get('/reportes/gastos-por-sucursal', [ReporteController::class, 'gastosPorSucursal'])->name('reportes.gastos.sucursal')->middleware('can:ver-reportes');
        Route::get('/reportes/gastos-por-sucursal/exportar', [ReporteController::class, 'exportarGastosPorSucursal'])->name('reportes.gastos.sucursal.exportar')->middleware('can:ver-reportes');
        Route::get('/reportes/ejecutivo-pdf', [ReporteController::class, 'reporteEjecutivoPDF'])->name('reportes.ejecutivo.pdf');

        Route::get('/reportes/export/trial-balance', [ReporteController::class, 'exportTrialBalance'])->name('reportes.export_trial_balance')->middleware('can:ver-reportes');
        Route::get('/reportes/export/income-statement', [ReporteController::class, 'exportIncomeStatement'])->name('reportes.export_income_statement')->middleware('can:ver-reportes');
        Route::get('/reportes/export/income-statement/pdf', [ReporteController::class, 'exportIncomeStatementPDF'])->name('reportes.export_income_statement_pdf')->middleware('can:ver-reportes');

        Route::post('/creditos/{credito}/disburse', [CreditoController::class, 'disburse'])->name('creditos.disburse');
        Route::post('/installments/{installment}/pay', [PaymentController::class, 'store'])->name('payments.store');

        Route::get('/reconciliation/upload', [ReconciliationController::class, 'create'])->name('reconciliation.create');
        Route::post('/reconciliation/upload', [ReconciliationController::class, 'store'])->name('reconciliation.store');
        Route::get('/reconciliation/confirm', [ReconciliationController::class, 'confirm'])->name('reconciliation.confirm');
        Route::post('/reconciliation/process', [ReconciliationController::class, 'process'])->name('reconciliation.process');

        // APIs Internas
        Route::get('/api/groups/{group}/members', [App\Http\Controllers\GroupController::class, 'getMembers'])->name('groups.members')->middleware(['auth']);
        Route::get('/api/clientes/search', [App\Http\Controllers\ClienteController::class, 'search'])->name('clientes.search')->middleware(['auth']);

        Route::post('/reportes/generate-analysis', [ReporteController::class, 'generateAnalysis'])->name('reports.generate_analysis')->middleware('can:ver-reportes');
        Route::get('/reportes/balance-general', [ReporteController::class, 'balanceSheet'])->name('reportes.balance_sheet')->middleware('can:ver-reportes');

        Route::get('/reportes/export/balance-sheet', [ReporteController::class, 'exportBalanceSheet'])->name('reportes.export_balance_sheet')->middleware('can:ver-reportes');
        Route::get('/reportes/export/balance-sheet/pdf', [ReporteController::class, 'exportBalanceSheetPDF'])->name('reportes.export_balance_sheet_pdf')->middleware('can:ver-reportes');
        Route::post('/reportes/generate-balance-sheet-analysis', [ReporteController::class, 'generateBalanceSheetAnalysis'])->name('reportes.generate_balance_sheet_analysis')->middleware('can:ver-reportes');

        Route::resource('accounts', AccountController::class)->middleware([
             'can:ver-cuentas',
             'can:crear-cuentas',
             'can:editar-cuentas',
             'can:eliminar-cuentas',
        ]);

        // Pólizas Contables
        Route::get('journals', [JournalController::class, 'index'])->name('journals.index')->middleware('can:ver-polizas');
        Route::resource('grupos', GroupController::class)->middleware(['auth']);
        Route::get('journals/{journal}', [JournalController::class, 'show'])->name('journals.show')->middleware('can:ver-detalle-polizas');

        Route::get('/reportes/balanza-comprobacion', [ReporteController::class, 'trialBalance'])->name('reportes.balanza_comprobacion')->middleware('can:ver-reportes');
        Route::get('/reportes/estado-resultados', [ReporteController::class, 'incomeStatement'])->name('reportes.income_statement')->middleware('can:ver-reportes');

        Route::resource('placements', PlacementController::class)->only(['index', 'create', 'store'])->middleware('can:ver-colocaciones');
        Route::resource('recoveries', RecoveryController::class)->only(['index', 'create', 'store'])->middleware('can:ver-recuperaciones');
    });

    // --- ADMINISTRACIÓN ---
    Route::middleware('can:ver-menu-administracion')->group(function () {
        Route::resource('users', UserController::class)->middleware('can:ver-usuarios');
        Route::resource('roles', RoleController::class)->middleware('can:ver-roles');
    });

    // --- CONFIGURACIÓN ---
    Route::middleware('can:ver-menu-configuracion')->group(function () {
        Route::resource('sucursales', SucursalController::class)->middleware('can:ver-sucursales');
        Route::resource('puestos', PuestoController::class)->middleware('can:ver-puestos');

        Route::get('/api/cp/{cp}', [CodigoPostalController::class, 'getInfo'])->name('api.cp.info');

        Route::get('/patrones/{patron}/logo', [PatronController::class, 'editLogo'])->name('patrones.logo.edit');
        Route::post('/patrones/{patron}/logo', [PatronController::class, 'updateLogo'])->name('patrones.logo.update');

        Route::resource('patrones', PatronController::class)->middleware([
            'can:ver-patrones',
        ]);
        
        Route::resource('horarios', HorarioController::class)->middleware('can:ver-horarios');
        Route::resource('categorias', CategoriaController::class)->except(['show']);
    });
});

// --- RUTA DE LIMPIEZA TOTAL ---
Route::get('/super-admin/clear-everything/{secret_key}', function ($secret_key) {
    if ($secret_key !== 'Mexico97') {
        abort(403, 'Acceso no autorizado.');
    }

    $output = [];
    $errors = [];

    try {
        if (function_exists('opcache_reset')) {
            opcache_reset();
            $output[] = 'OPcache reseteado.';
        } else {
            $output[] = 'La función opcache_reset() no está disponible.';
        }

        Artisan::call('permission:cache-reset');
        $output[] = 'permission:cache-reset -> ' . Artisan::output();

        Artisan::call('cache:clear');
        $output[] = 'cache:clear -> ' . Artisan::output();

        Artisan::call('config:clear');
        $output[] = 'config:clear -> ' . Artisan::output();

        Artisan::call('view:clear');
        $output[] = 'view:clear -> ' . Artisan::output();

        Artisan::call('route:clear');
        $output[] = 'route:clear -> ' . Artisan::output();

    } catch (\Exception $e) {
        Log::error("Error en la ruta de limpieza total: " . $e->getMessage());
        $errors[] = "SE PRODUJO UN ERROR: " . $e->getMessage();
    }

    $html = "<h1>Resultados de la Limpieza Total</h1>";
    if (!empty($output)) {
        $html .= "<h2>Comandos Ejecutados:</h2><pre>" . implode("\n", $output) . "</pre>";
    }
    if (!empty($errors)) {
        $html .= "<h2>Errores:</h2><pre style='color:red;'>" . implode("\n", $errors) . "</pre>";
    }

    return $html;
});

// Incluir rutas de autenticación de Laravel
require __DIR__.'/auth.php';
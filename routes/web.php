<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- RUTAS PÚBLICAS ---
Route::get('/', function () {
    return view('auth.login');
});

// Rutas de Asistencia (parte pública)
Route::get('/asistencia', [AsistenciaController::class, 'index'])->name('asistencia.index');
Route::post('/asistencia/registrar-entrada', [AsistenciaController::class, 'registrarEntrada'])->name('asistencia.registrarEntrada');
Route::post('/asistencia/registrar-falta', [AsistenciaController::class, 'registrarFalta'])->name('asistencia.registrarFalta');
Route::post('/asistencia/registrar-baja-dia', [AsistenciaController::class, 'registrarBajaDia'])->name('asistencia.registrarBajaDia');
Route::post('/asistencia/registrar-incidencia', [AsistenciaController::class, 'registrarIncidencia'])->name('asistencia.registrarIncidencia');

// --- RUTAS QUE REQUIEREN AUTENTICACIÓN ---
Route::middleware('auth')->group(function () {
    
    // --- DASHBOARD Y PERFIL ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
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

        // Asistencias (vistas de admin)
        Route::get('/asistencia/vista-periodo', [AsistenciaController::class, 'vistaPeriodo'])->name('asistencia.vistaPeriodo')->middleware('can:ver-asistencias');
        Route::post('/asistencia/guardar-dia', [AsistenciaController::class, 'guardarAsistenciaDia'])->name('asistencia.guardarDia')->middleware('can:editar-asistencias');

        // Vacaciones
        Route::resource('vacaciones', VacacionController::class)->only(['index', 'create', 'store'])->middleware('can:ver-vacaciones');
        Route::get('/vacaciones/historial/{empleado}', [VacacionController::class, 'historialPorEmpleado'])->name('vacaciones.historial')->middleware('can:ver-vacaciones');
        
        // Deducciones
        Route::resource('deducciones', DeduccionController::class)->middleware('can:ver-deducciones');

        // Lista de Raya
        Route::get('/lista-de-raya', [ListaDeRayaController::class, 'index'])->name('lista_de_raya.index')->middleware('can:ver-lista-raya');
        Route::get('/lista-de-raya/exportar', [ListaDeRayaController::class, 'exportarExcel'])->name('lista_de_raya.exportar')->middleware('can:exportar-lista-raya');

        // Finiquitos y Liquidaciones
        Route::get('/finiquitos', [FiniquitoController::class, 'index'])->name('finiquitos.index')->middleware('can:ver-finiquitos');
        Route::post('/finiquitos/calcular', [FiniquitoController::class, 'calcular'])->name('finiquitos.calcular')->middleware('can:calcular-finiquitos');
        Route::post('/finiquitos/exportar-pdf', [FiniquitoController::class, 'exportarPDF'])->name('finiquitos.export.pdf')->middleware('can:exportar-finiquitos');
        Route::post('/finiquitos/exportar-excel', [FiniquitoController::class, 'exportarExcel'])->name('finiquitos.export.excel')->middleware('can:exportar-finiquitos');
        Route::post('/finiquitos/exportar-renuncia-pdf', [FiniquitoController::class, 'exportarRenunciaPdf'])->name('finiquitos.export.renuncia.pdf')->middleware('can:exportar-finiquitos');

        // Gestión IMSS
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
        
 // 1. Rutas específicas primero para que no choquen con el resource.
        Route::get('/gastos/aprobaciones', [GastoController::class, 'approvalIndex'])->name('gastos.approvals')->middleware('can:aprobar-gastos');
        Route::get('/gastos/crear', [GastoController::class, 'create'])->name('gastos.create'); // El 'create' de resource funciona, pero así es más explícito.
        
        // 2. Rutas con parámetros
        Route::post('/gastos/{gasto}/aprobar', [GastoController::class, 'approve'])->name('gastos.approve')->middleware('can:aprobar-gastos');
        Route::post('/gastos/{gasto}/rechazar', [GastoController::class, 'reject'])->name('gastos.reject')->middleware('can:aprobar-gastos');
        Route::get('/gastos/{gasto}/comprobante', [GastoController::class, 'verComprobante'])->name('gastos.verComprobante');
        
        // 3. La ruta resource al final, para que maneje las rutas estándar restantes.
        // He excluido 'create' y 'show' para evitar conflictos.
        Route::resource('gastos', GastoController::class)->except(['create', 'show']);

        // --- REPORTES ---
    Route::get('/reportes/gastos-por-sucursal', [ReporteController::class, 'gastosPorSucursal'])->name('reportes.gastos.sucursal')->middleware('can:ver-reportes');
    
    Route::get('/reportes/gastos-por-sucursal/exportar', [ReporteController::class, 'exportarGastosPorSucursal'])->name('reportes.gastos.sucursal.exportar')->middleware('can:ver-reportes');


    }); // Cierre del grupo de Contabilidad

    // --- ADMINISTRACIÓN ---
    Route::middleware('can:ver-menu-administracion')->group(function () {
        // Usuarios y Roles
        Route::resource('users', UserController::class)->middleware('can:ver-usuarios');
        Route::resource('roles', RoleController::class)->middleware('can:ver-roles');
    });

    // --- CONFIGURACIÓN ---
    Route::middleware('can:ver-menu-configuracion')->group(function () {
        Route::resource('sucursales', SucursalController::class)->only(['index', 'create', 'store'])->middleware('can:ver-sucursales');
        Route::resource('puestos', PuestoController::class)->middleware('can:ver-puestos');
        Route::resource('patrones', PatronController::class)->only(['index', 'create', 'store'])->middleware('can:ver-patrones');
        Route::resource('horarios', HorarioController::class)->middleware('can:ver-horarios');
        // --- AÑADE ESTA LÍNEA PARA GESTIONAR CATEGORÍAS ---
    Route::resource('categorias', CategoriaController::class)->except(['show']);
    });
});

// Incluir rutas de autenticación de Laravel
require __DIR__.'/auth.php';
<?php

use Illuminate\Support\Facades\Route;
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
use App\Models\User;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('auth.login');
});

// --- RUTA MÁGICA (CORREGIDA) ---
// Ahora tiene una URL única para evitar conflictos.
Route::get('/magic-login/{user}', function (User $user) {
    Auth::login($user);
    return redirect('/dashboard');
})->name('login.magic')->middleware('signed'); // <-- Nota el nuevo nombre 'login.magic'



// Rutas de Asistencia (públicas o con su propio control de acceso si es necesario)
Route::get('/asistencia', [AsistenciaController::class, 'index'])->name('asistencia.index');
Route::post('/asistencia/registrar-entrada', [AsistenciaController::class, 'registrarEntrada'])->name('asistencia.registrarEntrada');
Route::post('/asistencia/registrar-falta', [AsistenciaController::class, 'registrarFalta'])->name('asistencia.registrarFalta');
Route::post('/asistencia/registrar-baja-dia', [AsistenciaController::class, 'registrarBajaDia'])->name('asistencia.registrarBajaDia');
Route::post('/asistencia/registrar-incidencia', [AsistenciaController::class, 'registrarIncidencia'])->name('asistencia.registrarIncidencia');

// =====> AÑADE ESTA NUEVA RUTA PARA LA VISTA DE PERIODO <=====
Route::get('/asistencia/vista-periodo', [AsistenciaController::class, 'vistaPeriodo'])->name('asistencia.vistaPeriodo');
// ===========================================================

// =====> AÑADE ESTA NUEVA RUTA PARA GUARDAR/ACTUALIZAR ASISTENCIA DE UN DÍA ESPECÍFICO <=====
Route::post('/asistencia/guardar-dia', [AsistenciaController::class, 'guardarAsistenciaDia'])->name('asistencia.guardarDia');
// =======================================================================================

// =====> AÑADE ESTA NUEVA RUTA PARA LA LISTA DE IMSS <=====
    Route::get('/imss', [ImssController::class, 'index'])->name('imss.index');
    // ========================================================
// =====> AÑADE ESTA NUEVA RUTA <=====
Route::post('/imss/{empleado}/registrar-alta', [ImssController::class, 'registrarAlta'])->name('imss.registrarAlta');
// ===================================
// =====> AÑADE ESTA NUEVA RUTA <=====
Route::post('/imss/{empleado}/registrar-baja', [ImssController::class, 'registrarBaja'])->name('imss.registrarBaja');
// ===================================

// =====> AÑADE ESTA NUEVA RUTA PARA EL ACUSE PDF <=====
Route::get('/imss/{empleado}/acuse-alta-pdf', [ImssController::class, 'generarAcuseAltaPdf'])->name('imss.acuseAltaPdf');
// =====================================================

// =====> AÑADE ESTA NUEVA RUTA PARA PRÉSTAMOS A EMPLEADOS <=====
    Route::resource('deducciones', DeduccionController::class);
    // =============================================================

 // =====> AÑADE ESTA NUEVA RUTA PARA USUARIOS <=====
    Route::resource('users', UserController::class); 
    // Por ahora, generaremos todas las rutas CRUD, luego podemos restringirlas con ->only() o ->except() si es necesario.
    // =================================================

// Rutas que requieren autenticación
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('empleados', EmpleadoController::class);
    Route::get('/empleados/{empleado}/historial-contratos', [EmpleadoController::class, 'historialContratos'])->name('empleados.contratos.historial');
    Route::put('/empleados/{empleado}/reactivar', [EmpleadoController::class, 'reactivar'])->name('empleados.reactivar');
    
    Route::resource('contratos', ContratoController::class);
    Route::get('/contratos/{contrato}/pdf', [ContratoController::class, 'generarPdf'])->name('contratos.pdf');

// =====> AÑADE ESTA NUEVA RUTA PARA EXPORTAR CONTRATOS A EXCEL <=====
Route::get('/contratos/exportar/excel', [ContratoController::class, 'exportarExcel'])->name('contratos.exportarExcel');
// ===================================================================

  // =====> AÑADE ESTA NUEVA RUTA PARA LISTA DE RAYA <=====
    Route::get('/lista-de-raya', [ListaDeRayaController::class, 'index'])->name('lista_de_raya.index');
    // =======================================================

// =====> AÑADE ESTA NUEVA RUTA PARA LA EXPORTACIÓN <=====
Route::get('/lista-de-raya/exportar', [ListaDeRayaController::class, 'exportarExcel'])->name('lista_de_raya.exportar');
// ========================================================

 // =====> AÑADE ESTA NUEVA RUTA PARA PATRONES <=====
    Route::resource('patrones', PatronController::class)->only(['index', 'create', 'store']);
    // =================================================
    
 // =====> AÑADE ESTA NUEVA RUTA PARA FINIQUITOS <=====
    Route::get('/finiquitos', [FiniquitoController::class, 'index'])->name('finiquitos.index');
    // =====================================================
// =====> AÑADE ESTA NUEVA RUTA <=====
    Route::post('/finiquitos/calcular', [FiniquitoController::class, 'calcular'])->name('finiquitos.calcular');
    // ===================================
    
Route::post('/finiquitos/exportar-pdf', [FiniquitoController::class, 'exportarPDF'])->name('finiquitos.export.pdf');
    

// RUTA AÑADIDA: Para la exportación a Excel
Route::post('/finiquitos/exportar-excel', [FiniquitoController::class, 'exportarExcel'])->name('finiquitos.export.excel');

// =====> AÑADE ESTA NUEVA RUTA PARA VACACIONES <=====
    // Por ahora solo necesitamos create (mostrar formulario) y store (guardar)
    // Más adelante podemos añadir 'index' (para listar), 'edit', 'update', 'destroy'.
    Route::resource('vacaciones', VacacionController::class)->only(['index', 'create', 'store']);
    // Si quieres una URL más específica para el formulario de captura, podrías hacer:
    // Route::get('/vacaciones/capturar', [VacacionController::class, 'create'])->name('vacaciones.create');
    // Route::post('/vacaciones', [VacacionController::class, 'store'])->name('vacaciones.store');
    // Pero Route::resource con ->only() es más estándar.
    // =====================================================

// =====> AÑADE ESTA NUEVA RUTA PARA EL HISTORIAL DE VACACIONES POR EMPLEADO <=====
Route::get('/vacaciones/historial/{empleado}', [VacacionController::class, 'historialPorEmpleado'])->name('vacaciones.historial');
// ===============================================================================




Route::resource('sucursales', SucursalController::class)->only(['index', 'create', 'store']);
    Route::resource('puestos', PuestoController::class);

    
 // =====> AÑADE ESTA NUEVA RUTA PARA HORARIOS <=====
    Route::resource('horarios', HorarioController::class);
    // ===============================================
    
});

require __DIR__.'/auth.php';
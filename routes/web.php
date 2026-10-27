<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — SIGVOS
|--------------------------------------------------------------------------
| Grupos:
|   auth          → cualquier usuario autenticado
|   auth + role:admin  → solo administradores
*/

// ── Raíz: redirige según estado de autenticación ──────────────────────────
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('welcome');
});

Route::get('/desarrollador', function () {
    return view('desarrollador');
})->name('desarrollador');

// ── Rutas autenticadas (admin y trabajador) ───────────────────────────────
Route::middleware(['auth', 'verified', 'role:admin,worker'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');

    // Perfil (Breeze)
    Route::get('/profile',          [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',        [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar',  [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile',       [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notificaciones
    Route::get('/notificaciones', [\App\Http\Controllers\NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::post('/notificaciones/{notificacion}/leida', [\App\Http\Controllers\NotificacionController::class, 'marcarLeida'])->name('notificaciones.leida');
    Route::post('/notificaciones/todas-leidas', [\App\Http\Controllers\NotificacionController::class, 'marcarTodasLeidas'])->name('notificaciones.marcar_todas');

    // Lotes
    Route::resource('lotes', \App\Http\Controllers\LoteController::class);

    // Cultivos
    Route::resource('cultivos', \App\Http\Controllers\CultivoController::class);
    Route::get('/cultivo/{cultivo}', [\App\Http\Controllers\CultivoController::class, 'detalles'])->name('cultivo.show'); 
    // Actividades
    Route::post('actividades/realizada', [\App\Http\Controllers\ActividadController::class, 'registrarRealizada'])->name('actividades.registrar-realizada');
    Route::post('actividades/{actividade}/completar', [\App\Http\Controllers\ActividadController::class, 'completar'])->name('actividades.completar');
    Route::post('actividades/{actividade}/cancelar', [\App\Http\Controllers\ActividadController::class, 'cancelar'])->name('actividades.cancelar');
    Route::resource('actividades', \App\Http\Controllers\ActividadController::class);

    // Fotos de Cultivo
    Route::get('/fotografias', [\App\Http\Controllers\FotoCultivoController::class, 'index'])->name('fotografias.index');
    Route::post('cultivos/{cultivo}/fotos', [\App\Http\Controllers\FotoCultivoController::class, 'store'])->name('fotos.store');
    Route::delete('fotos/{foto}', [\App\Http\Controllers\FotoCultivoController::class, 'destroy'])->name('fotos.destroy');

    // ── Solo Administradores ──────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {

        // Asignaciones
        Route::get('/asignaciones', [\App\Http\Controllers\AsignacionController::class, 'index'])->name('asignaciones.index');
        Route::post('/asignaciones/lotes', [\App\Http\Controllers\AsignacionController::class, 'asignarLote'])->name('asignaciones.lotes.store');
        Route::post('/asignaciones/lotes/desactivar', [\App\Http\Controllers\AsignacionController::class, 'desactivarLote'])->name('asignaciones.lotes.desactivar');
        Route::post('/asignaciones/lotes/reasignar', [\App\Http\Controllers\AsignacionController::class, 'reasignarLote'])->name('asignaciones.lotes.reasignar');

        // Cosecha
        Route::get('/cosecha', [\App\Http\Controllers\CosechaController::class, 'index'])->name('cosecha.index');
        Route::post('/cosecha', [\App\Http\Controllers\CosechaController::class, 'store'])->name('cosecha.store');

        // Calendario
        Route::get('/calendario', [\App\Http\Controllers\CalendarioController::class, 'index'])->name('calendario.index');
        Route::get('/calendario/eventos', [\App\Http\Controllers\CalendarioController::class, 'eventos'])->name('calendario.eventos');

        // Usuarios
        Route::resource('usuarios', \App\Http\Controllers\UsuarioController::class);

        // Reportes
        Route::get('/reportes', [\App\Http\Controllers\ReporteController::class, 'index'])
             ->name('reportes.index');

        // Búsqueda Global y Filtros Avanzados (HU DP-9)
        Route::get('/busqueda', [\App\Http\Controllers\BusquedaController::class, 'index'])->name('busqueda.index');
        Route::get('/busqueda/rapida', [\App\Http\Controllers\BusquedaController::class, 'rapida'])->name('busqueda.rapida');
    });
});

require __DIR__.'/auth.php';

<?php

use App\Http\Controllers\Api\ActividadController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogoController;
use App\Http\Controllers\Api\CosechaController;
use App\Http\Controllers\Api\CultivoController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FotoCultivoController;
use App\Http\Controllers\Api\LoteController;
use App\Http\Controllers\Api\NotificacionController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReporteController;
use App\Http\Controllers\Api\UsuarioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - SIGVOS
|--------------------------------------------------------------------------
|
| Todas las rutas de este archivo tienen el prefijo automático "/api".
| Diseñadas para consumir desde clientes móviles Flutter y servicios externos.
|
*/

// ── 1. Rutas de Autenticación ──────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    // Públicas
    Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');
    Route::post('/register', [AuthController::class, 'register'])->name('api.auth.register');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('api.auth.forgot-password');

    // Protegidas por Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me'])->name('api.auth.me');
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
    });
});

// ── 2. Rutas del Sistema (Protegidas con Sanctum) ──────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    // Dashboard principal con KPIs, actividades próximas y cosechas
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('api.dashboard');

    // Catálogos auxiliares para selectores y filtros
    Route::prefix('catalogos')->group(function () {
        Route::get('/tipos-cultivo', [CatalogoController::class, 'tiposCultivo'])->name('api.catalogos.tipos-cultivo');
        Route::get('/lotes-disponibles', [CatalogoController::class, 'lotesDisponibles'])->name('api.catalogos.lotes-disponibles');
        Route::get('/tipos-actividad', [CatalogoController::class, 'tiposActividad'])->name('api.catalogos.tipos-actividad');
        Route::get('/trabajadores', [CatalogoController::class, 'trabajadores'])->name('api.catalogos.trabajadores');
        Route::get('/cultivos-activos', [CatalogoController::class, 'cultivosActivos'])->name('api.catalogos.cultivos-activos');
    });

    // Módulo de Lotes (CRUD completo)
    Route::apiResource('lotes', LoteController::class)->names('api.lotes');

    // Módulo de Cultivos (CRUD completo + finalización de cosecha)
    Route::post('/cultivos/{cultivo}/finalizar', [CultivoController::class, 'finalizar'])->name('api.cultivos.finalizar');
    Route::apiResource('cultivos', CultivoController::class)->names('api.cultivos');

    // Módulo de Actividades & Calendario
    Route::get('/calendario/eventos', [ActividadController::class, 'calendarioEventos'])->name('api.calendario.eventos');
    Route::post('/actividades/{actividad}/completar', [ActividadController::class, 'marcarCompletada'])->name('api.actividades.completar');
    Route::apiResource('actividades', ActividadController::class)->names('api.actividades');

    // Módulo de Cosechas
    Route::get('/cosechas', [CosechaController::class, 'index'])->name('api.cosechas.index');
    Route::post('/cosechas', [CosechaController::class, 'store'])->name('api.cosechas.store');

    // Módulo de Fotografías y Evidencias
    Route::get('/fotografias', [FotoCultivoController::class, 'index'])->name('api.fotografias.index');
    Route::post('/cultivos/{cultivo}/fotos', [FotoCultivoController::class, 'store'])->name('api.cultivos.fotos.store');
    Route::delete('/fotos/{foto}', [FotoCultivoController::class, 'destroy'])->name('api.fotos.destroy');

    // Módulo de Notificaciones
    Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('api.notificaciones.index');
    Route::post('/notificaciones/{id}/leida', [NotificacionController::class, 'marcarLeida'])->name('api.notificaciones.leida');
    Route::post('/notificaciones/marcar-todas', [NotificacionController::class, 'marcarTodasLeidas'])->name('api.notificaciones.marcar-todas');

    // Módulo de Usuarios & Equipo
    Route::get('/roles', [UsuarioController::class, 'roles'])->name('api.roles.index');
    Route::apiResource('usuarios', UsuarioController::class)->names('api.usuarios');

    // Módulo de Reportes & Analíticas
    Route::get('/reportes', [ReporteController::class, 'index'])->name('api.reportes.index');

    // Módulo de Perfil & Cuenta
    Route::prefix('perfil')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('api.perfil.show');
        Route::put('/', [ProfileController::class, 'update'])->name('api.perfil.update');
        Route::put('/password', [ProfileController::class, 'changePassword'])->name('api.perfil.password');
        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->name('api.perfil.avatar');
    });

    // Usuario autenticado
    Route::get('/user', function (Request $request) {
        return $request->user()->loadMissing('rol');
    });
});

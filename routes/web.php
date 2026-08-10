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
        : redirect()->route('login');
});

// ── Rutas autenticadas (admin y trabajador) ───────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil (Breeze)
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Lotes
    Route::resource('lotes', \App\Http\Controllers\LoteController::class);

    // Cultivos
    Route::resource('cultivos', \App\Http\Controllers\CultivoController::class);

    // Actividades
    Route::resource('actividades', \App\Http\Controllers\ActividadController::class);

    // Fotos de Cultivo
    Route::post('cultivos/{cultivo}/fotos', [\App\Http\Controllers\FotoCultivoController::class, 'store'])->name('fotos.store');
    Route::delete('fotos/{foto}', [\App\Http\Controllers\FotoCultivoController::class, 'destroy'])->name('fotos.destroy');

    // ── Solo Administradores ──────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {

        // Usuarios
        Route::resource('usuarios', \App\Http\Controllers\UsuarioController::class);

        // Reportes
        Route::get('/reportes', [\App\Http\Controllers\ReporteController::class, 'index'])
             ->name('reportes.index');
    });
});

require __DIR__.'/auth.php';

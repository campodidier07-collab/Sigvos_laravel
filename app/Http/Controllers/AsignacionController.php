<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Actividad;
use Illuminate\Http\Request;

class AsignacionController extends Controller
{
    public function index(Request $request)
    {
        // Solo obtener usuarios que tengan rol de trabajador o que tengan actividades asignadas/completadas
        $usuarios = Usuario::with([
            'actividadesAsignadas' => function($query) {
                $query->whereIn('estado', ['pendiente', 'en_progreso'])
                      ->with(['cultivo.lote', 'tipoActividad'])
                      ->orderBy('fecha_programada', 'asc');
            },
            'actividadesCompletadas' => function($query) {
                $query->with(['cultivo.lote', 'tipoActividad'])
                      ->orderBy('fecha_programada', 'desc');
            }
        ])->where('activo', true)->get();

        // Para simplificar, filtramos en PHP los que tengan el método esTrabajador() 
        // o directamente asumimos que todos los usuarios mostrados aquí son sujetos a asignación.
        // Mejor filtramos por rol trabajador, asumiendo que el ID de rol o el método esTrabajador() existe.
        $trabajadores = $usuarios->filter(function($user) {
            return $user->esTrabajador() || $user->actividadesAsignadas->count() > 0 || $user->actividadesCompletadas->count() > 0;
        });

        return view('asignaciones.index', compact('trabajadores'));
    }
}

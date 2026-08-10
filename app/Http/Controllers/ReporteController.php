<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Cultivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    /**
     * Muestra el panel principal de reportes y estadísticas.
     */
    public function index(Request $request)
    {
        // Solo administradores pueden ver reportes detallados (asumido por el middleware en web.php)

        // 1. Estadísticas de Cultivos por Estado
        $cultivosPorEstado = Cultivo::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();
            
        $estadosCultivo = ['sembrado', 'creciendo', 'cosechado', 'perdido'];
        $datosCultivoEstado = [];
        foreach ($estadosCultivo as $est) {
            $datosCultivoEstado[] = $cultivosPorEstado[$est] ?? 0;
        }

        // 2. Rendimiento (Kg Cosechados) por Variedad
        $rendimientoVariedad = Cultivo::where('estado', 'cosechado')
            ->join('variedades', 'cultivos.id_variedad', '=', 'variedades.id')
            ->select('variedades.nombre', DB::raw('SUM(cantidad_cosechada_kg) as total_kg'))
            ->groupBy('variedades.nombre')
            ->orderByDesc('total_kg')
            ->limit(10)
            ->get();
            
        $nombresVariedades = $rendimientoVariedad->pluck('nombre')->toArray();
        $kgVariedades = $rendimientoVariedad->pluck('total_kg')->toArray();

        // 3. Actividades por Estado (últimos 30 días)
        $actividadesPorEstado = Actividad::where('fecha_programada', '>=', now()->subDays(30))
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();
            
        $estadosActividad = ['pendiente', 'completada', 'cancelada'];
        $datosActividadEstado = [];
        foreach ($estadosActividad as $est) {
            $datosActividadEstado[] = $actividadesPorEstado[$est] ?? 0;
        }

        return view('reportes.index', compact(
            'estadosCultivo', 'datosCultivoEstado',
            'nombresVariedades', 'kgVariedades',
            'estadosActividad', 'datosActividadEstado'
        ));
    }
}

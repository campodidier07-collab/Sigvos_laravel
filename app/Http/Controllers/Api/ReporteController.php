<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\Cultivo;
use App\Models\Lote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    /**
     * Muestra métricas agregadas y analíticas de la finca para la app móvil.
     */
    public function index(Request $request): JsonResponse
    {
        // 1. Estadísticas de Cultivos por Estado
        $cultivosPorEstado = Cultivo::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();

        $estadosCultivo = ['sembrado', 'creciendo', 'cosechado', 'perdido'];
        $distribucionCultivos = [];
        $totalCultivos = 0;
        foreach ($estadosCultivo as $est) {
            $cant = $cultivosPorEstado[$est] ?? 0;
            $distribucionCultivos[$est] = $cant;
            $totalCultivos += $cant;
        }

        // 2. Rendimiento (Kg Cosechados) por Variedad
        $rendimientoVariedad = Cultivo::where('estado', 'cosechado')
            ->join('variedades', 'cultivos.id_variedad', '=', 'variedades.id')
            ->select('variedades.nombre', DB::raw('SUM(cultivos.cantidad_cosechada_kg) as total_kg'))
            ->groupBy('variedades.nombre')
            ->orderByDesc('total_kg')
            ->limit(8)
            ->get();

        $variedadesData = $rendimientoVariedad->map(function ($item) {
            return [
                'variedad' => $item->nombre,
                'total_kg' => (float) ($item->total_kg ?? 0),
            ];
        });

        $totalKgGeneral = (float) Cultivo::where('estado', 'cosechado')->sum('cantidad_cosechada_kg');

        // 3. Actividades por Estado (últimos 30 días)
        $actividades30Dias = Actividad::where('fecha_programada', '>=', now()->subDays(30))->get();
        $completadas = $actividades30Dias->where('estado', 'completada')->count();
        $pendientes = $actividades30Dias->where('estado', 'pendiente')->count();
        $canceladas = $actividades30Dias->where('estado', 'cancelada')->count();
        $totalActividades = $actividades30Dias->count();

        $tasaCumplimiento = $totalActividades > 0
            ? round(($completadas / $totalActividades) * 100, 1)
            : 0.0;

        // 4. Ocupación de Suelos / Lotes
        $totalLotes = Lote::where('activo', true)->count();
        $lotesOcupados = Lote::where('activo', true)->where('estado', 'ocupado')->count();
        $hectareasTotales = (float) Lote::where('activo', true)->sum('area_ha');
        $hectareasEnUso = (float) Lote::where('activo', true)->where('estado', 'ocupado')->sum('area_ha');

        $porcentajeUsoHa = $hectareasTotales > 0
            ? round(($hectareasEnUso / $hectareasTotales) * 100, 1)
            : 0.0;

        return response()->json([
            'resumen' => [
                'total_cultivos_historicos' => $totalCultivos,
                'total_kg_cosechados'       => $totalKgGeneral,
                'tasa_cumplimiento_labores' => $tasaCumplimiento,
                'porcentaje_uso_suelo'      => $porcentajeUsoHa,
            ],
            'cultivos_por_estado'      => $distribucionCultivos,
            'rendimiento_por_variedad' => $variedadesData,
            'labores_30_dias'          => [
                'total'             => $totalActividades,
                'completadas'       => $completadas,
                'pendientes'        => $pendientes,
                'canceladas'        => $canceladas,
                'tasa_cumplimiento' => $tasaCumplimiento,
            ],
            'suelo_lotes'              => [
                'total_lotes'       => $totalLotes,
                'lotes_ocupados'    => $lotesOcupados,
                'hectareas_totales' => $hectareasTotales,
                'hectareas_en_uso'  => $hectareasEnUso,
                'porcentaje_uso'    => $porcentajeUsoHa,
            ],
        ]);
    }
}

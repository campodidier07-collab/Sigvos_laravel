<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Cultivo;
use Illuminate\Http\Request;

class CalendarioController extends Controller
{
    public function index()
    {
        return view('calendario.index');
    }

    public function eventos(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');

        $eventos = [];

        // 1. Cargar actividades en el rango
        $actividades = Actividad::with(['tipoActividad', 'cultivo.lote'])
            ->whereNotNull('fecha_programada')
            ->whereBetween('fecha_programada', [$start, $end])
            ->get();

        foreach ($actividades as $act) {
            $color = '#3b82f6'; // blue default
            if ($act->estado === 'completada') $color = '#10b981'; // green
            if ($act->estado === 'cancelada') $color = '#ef4444'; // red

            $eventos[] = [
                'id' => 'act_' . $act->id,
                'title' => ($act->tipoActividad->nombre ?? 'Actividad') . ' (' . ($act->cultivo->codigo ?? '') . ')',
                'start' => $act->fecha_programada,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'allDay' => true,
                'url' => route('actividades.edit', $act->id)
            ];
        }

        // 2. Cargar estimaciones de cosecha
        $cultivos = Cultivo::with('lote')
            ->whereNotNull('fecha_cosecha_estimada')
            ->whereBetween('fecha_cosecha_estimada', [$start, $end])
            ->get();

        foreach ($cultivos as $cultivo) {
            $color = '#f59e0b'; // amber
            if ($cultivo->estado === 'cosechado') $color = '#15803d'; // dark green

            $eventos[] = [
                'id' => 'cosecha_' . $cultivo->id,
                'title' => '🌱 Cosecha Estimada: ' . $cultivo->codigo,
                'start' => $cultivo->fecha_cosecha_estimada,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'allDay' => true,
                'url' => route('cultivos.edit', $cultivo->id)
            ];
        }

        return response()->json($eventos);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Cultivo;
use Illuminate\Http\Request;

class CalendarioController extends Controller
{
    public function index(Request $request)
    {
        $usuario = $request->user();

        $tiposActividad = \App\Models\TipoActividad::where('activo', true)->orderBy('nombre')->get();

        $cultivosQuery = Cultivo::with(['lote.trabajadores', 'variedad'])
            ->whereNotNull('activo_en_lote')
            ->orderBy('codigo');

        if ($usuario && $usuario->esTrabajador()) {
            $cultivosQuery->whereHas('lote.trabajadores', function($q) use ($usuario) {
                $q->where('id_usuario', $usuario->id);
            });
        }
        $cultivos = $cultivosQuery->get();

        $trabajadores = \App\Models\Usuario::with('lotes')
            ->where('id_rol', 2)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('calendario.index', compact('tiposActividad', 'cultivos', 'trabajadores'));
    }

    public function eventos(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');
        $idTipoActividad = $request->query('id_tipo_actividad');

        $eventos = [];
        $hoy = \Carbon\Carbon::today()->toDateString();

        // 1. Cargar actividades en el rango
        $queryActividades = Actividad::with(['tipoActividad', 'cultivo.lote'])
            ->whereNotNull('fecha_programada');

        if ($start && $end) {
            $queryActividades->whereBetween('fecha_programada', [$start, $end]);
        }

        // Criterio 6: Filtrado de calendario por tipo de actividad
        if (!empty($idTipoActividad)) {
            $queryActividades->where('id_tipo_actividad', $idTipoActividad);
        }

        $actividades = $queryActividades->get();

        foreach ($actividades as $act) {
            $fechaAct = \Carbon\Carbon::parse($act->fecha_programada)->toDateString();
            $esHoy = ($fechaAct === $hoy);

            $color = '#3b82f6'; // blue default
            if ($act->estado === 'completada') $color = '#10b981'; // green
            if ($act->estado === 'cancelada') $color = '#ef4444'; // red

            // Criterio 4: Indicadores visuales para actividades del día actual
            $borderColor = $esHoy ? '#f59e0b' : $color;

            $eventos[] = [
                'id'              => 'act_' . $act->id,
                'title'           => ($esHoy ? '⭐ HOY: ' : '') . ($act->tipoActividad->nombre ?? 'Actividad') . ' (' . ($act->cultivo->codigo ?? '') . ')',
                'start'           => $fechaAct,
                'backgroundColor' => $color,
                'borderColor'     => $borderColor,
                'textColor'       => '#ffffff',
                'allDay'          => true,
                'className'       => $esHoy ? 'fc-event-today-highlight' : '',
                'url'             => route('actividades.show', $act->id),
                'extendedProps'   => [
                    'tipo_evento'     => 'actividad',
                    'tipo_actividad'  => $act->id_tipo_actividad,
                    'nombre_tipo'     => $act->tipoActividad->nombre ?? 'Actividad',
                    'cultivo'         => $act->cultivo->codigo ?? '',
                    'lote'            => $act->cultivo->lote->nombre ?? '',
                    'estado'          => $act->estado,
                    'descripcion'     => $act->descripcion,
                    'es_hoy'          => $esHoy,
                ]
            ];
        }

        // 2. Cargar estimaciones de cosecha (Criterio 5)
        // Si el usuario filtró por un tipo específico de actividad diferente a Cosecha, omitir cosechas
        $mostrarCosechas = true;
        if (!empty($idTipoActividad)) {
            $tipoSeleccionado = \App\Models\TipoActividad::find($idTipoActividad);
            if ($tipoSeleccionado && strtoupper($tipoSeleccionado->codigo) !== 'COSECHA') {
                $mostrarCosechas = false;
            }
        }

        if ($mostrarCosechas) {
            $queryCultivos = Cultivo::with(['lote', 'variedad'])
                ->whereNotNull('fecha_cosecha_estimada');

            if ($start && $end) {
                $queryCultivos->whereBetween('fecha_cosecha_estimada', [$start, $end]);
            }

            $cultivos = $queryCultivos->get();

            foreach ($cultivos as $cultivo) {
                $fechaCos = \Carbon\Carbon::parse($cultivo->fecha_cosecha_estimada)->toDateString();
                $esHoyCos = ($fechaCos === $hoy);

                $color = '#f59e0b'; // amber
                if ($cultivo->estado === 'cosechado') $color = '#15803d'; // dark green

                $eventos[] = [
                    'id'              => 'cosecha_' . $cultivo->id,
                    'title'           => '🌾 Cosecha Estimada: ' . ($cultivo->variedad->nombre ?? $cultivo->codigo),
                    'start'           => $fechaCos,
                    'backgroundColor' => $color,
                    'borderColor'     => $esHoyCos ? '#d97706' : $color,
                    'textColor'       => '#ffffff',
                    'allDay'          => true,
                    'className'       => 'fc-event-cosecha',
                    'url'             => route('cultivos.show', $cultivo->id),
                    'extendedProps'   => [
                        'tipo_evento' => 'cosecha',
                        'cultivo'     => $cultivo->codigo,
                        'variedad'    => $cultivo->variedad->nombre ?? '',
                        'lote'        => $cultivo->lote->nombre ?? '',
                        'estado'      => $cultivo->estado,
                    ]
                ];
            }
        }

        return response()->json($eventos);
    }
}

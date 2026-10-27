<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\Cultivo;
use App\Models\Lote;
use App\Models\Notificacion;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Devuelve los KPIs, actividades próximas y cosechas estimadas para el dashboard.
     * Adapta los datos según si el usuario es Administrador o Trabajador.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        // ── 1. Estadísticas / KPIs ─────────────────────────────────────────
        $actividadesQuery = Actividad::query();
        if ($usuario->esTrabajador()) {
            $actividadesQuery->where('asignado_a', $usuario->id);
        }

        $estadisticas = [
            'total_lotes'             => Lote::where('activo', true)->count(),
            'cultivos_activos'        => Cultivo::whereNotNull('activo_en_lote')->count(),
            'actividades_pendientes'  => (clone $actividadesQuery)->where('estado', 'pendiente')->count(),
            'actividades_completadas' => (clone $actividadesQuery)->where('estado', 'completada')->count(),
        ];

        if ($usuario->isAdmin()) {
            $estadisticas['total_usuarios']     = Usuario::where('activo', true)->count();
            $estadisticas['total_trabajadores'] = Usuario::where('id_rol', 2)->where('activo', true)->count();
        }

        // ── 2. Actividades próximas (próximos 7 días) ──────────────────────
        $actividadesProximas = Actividad::with([
                'cultivo.lote',
                'tipoActividad',
                'asignadoA',
            ])
            ->where('estado', 'pendiente')
            ->whereNotNull('fecha_programada')
            ->whereBetween('fecha_programada', [now()->startOfDay(), now()->addDays(7)->endOfDay()])
            ->when($usuario->esTrabajador(), fn ($q) => $q->where('asignado_a', $usuario->id))
            ->orderBy('fecha_programada')
            ->limit(6)
            ->get()
            ->map(function ($act) {
                return [
                    'id'               => $act->id,
                    'nombre'           => $act->nombre ?? $act->tipoActividad?->nombre ?? 'Actividad agrícola',
                    'tipo'             => $act->tipoActividad?->nombre ?? 'General',
                    'fecha_programada' => $act->fecha_programada ? $act->fecha_programada->format('Y-m-d') : null,
                    'estado'           => $act->estado,
                    'prioridad'        => $act->prioridad ?? 'media',
                    'cultivo'          => $act->cultivo?->nombre ?? 'Sin cultivo asociado',
                    'lote'             => $act->cultivo?->lote?->nombre ?? 'Sin lote',
                    'asignado_a'       => $act->asignadoA?->nombre ?? 'Sin asignar',
                ];
            });

        // ── 3. Cosechas próximas (próximos 30 días) ────────────────────────
        $cosechasProximas = Cultivo::with(['lote', 'variedad.tipoCultivo'])
            ->whereNotNull('activo_en_lote')
            ->where('estado', 'sembrado')
            ->whereBetween('fecha_cosecha_estimada', [now()->startOfDay(), now()->addDays(30)->endOfDay()])
            ->orderBy('fecha_cosecha_estimada')
            ->limit(6)
            ->get()
            ->map(function ($cultivo) {
                $diasRestantes = $cultivo->fecha_cosecha_estimada
                    ? (int) now()->startOfDay()->diffInDays($cultivo->fecha_cosecha_estimada, false)
                    : null;

                return [
                    'id'                     => $cultivo->id,
                    'cultivo'                => $cultivo->nombre ?? ($cultivo->variedad?->nombre . ' - ' . $cultivo->lote?->nombre),
                    'lote'                   => $cultivo->lote?->nombre ?? 'Sin lote',
                    'variedad'               => $cultivo->variedad?->nombre ?? 'Variedad estándar',
                    'tipo_cultivo'           => $cultivo->variedad?->tipoCultivo?->nombre ?? 'Agrícola',
                    'fecha_cosecha_estimada' => $cultivo->fecha_cosecha_estimada ? $cultivo->fecha_cosecha_estimada->format('Y-m-d') : null,
                    'dias_restantes'         => $diasRestantes,
                ];
            });

        // ── 4. Notificaciones recientes no leídas ─────────────────────────
        $notificaciones = Notificacion::where('id_usuario', $usuario->id)
            ->where('leida', false)
            ->orderByDesc('creado_en')
            ->limit(5)
            ->get()
            ->map(function ($notif) {
                return [
                    'id'        => $notif->id,
                    'titulo'    => $notif->titulo,
                    'mensaje'   => $notif->mensaje,
                    'tipo'      => $notif->tipo ?? 'info',
                    'creado_en' => $notif->creado_en ? $notif->creado_en->toIso8601String() : null,
                ];
            });

        return response()->json([
            'usuario'                 => [
                'id'     => $usuario->id,
                'name'   => $usuario->nombre,
                'role'   => $usuario->role,
                'es_admin' => $usuario->isAdmin(),
            ],
            'estadisticas'            => $estadisticas,
            'actividades_proximas'    => $actividadesProximas,
            'cosechas_proximas'       => $cosechasProximas,
            'notificaciones_recientes'=> $notificaciones,
        ]);
    }
}

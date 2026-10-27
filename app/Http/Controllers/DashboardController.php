<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Cultivo;
use App\Models\Lote;
use App\Models\Notificacion;
use App\Models\Usuario;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard con los KPIs principales del sistema.
     */
    public function index(Request $request)
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        // ── KPIs generales (Segmentados por rol según HU DP-8) ─────────────
        $actividadesQuery = Actividad::query();
        
        if ($usuario->esTrabajador()) {
            $actividadesQuery->where('asignado_a', $usuario->id);
            $totalLotes = $usuario->lotes()->count();
            $cultivosActivos = Cultivo::whereNotNull('activo_en_lote')
                ->whereHas('lote.trabajadores', fn($q) => $q->where('id_usuario', $usuario->id))
                ->count();
        } else {
            $totalLotes = Lote::where('activo', true)->count();
            $cultivosActivos = Cultivo::whereNotNull('activo_en_lote')->count();
        }

        $estadisticas = [
            'total_lotes'              => $totalLotes,
            'cultivos_activos'         => $cultivosActivos,
            'actividades_pendientes'   => (clone $actividadesQuery)->where('estado', 'pendiente')->count(),
            'actividades_completadas'  => (clone $actividadesQuery)->where('estado', 'completada')->count(),
        ];

        // ── Solo admin ve stats de usuarios ──────────────────────────────
        if ($usuario->isAdmin()) {
            $estadisticas['total_usuarios']    = Usuario::where('activo', true)->count();
            $estadisticas['total_trabajadores'] = Usuario::where('id_rol', 2)->where('activo', true)->count();
        }

        // ── Actividades próximas (7 días) y del día de hoy ────────────────
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
            ->limit(10)
            ->get();

        $actividadesHoy = Actividad::with([
                'cultivo.lote',
                'tipoActividad',
                'asignadoA',
            ])
            ->where('estado', 'pendiente')
            ->whereNotNull('fecha_programada')
            ->whereDate('fecha_programada', today())
            ->when($usuario->esTrabajador(), fn ($q) => $q->where('asignado_a', $usuario->id))
            ->orderBy('fecha_programada')
            ->get();

        // ── Cultivos con cosecha próxima (30 días) ────────────────────────
        $cosechasProximas = Cultivo::with(['lote', 'variedad.tipoCultivo'])
            ->whereNotNull('activo_en_lote')
            ->where('estado', 'sembrado')
            ->whereBetween('fecha_cosecha_estimada', [now()->startOfDay(), now()->addDays(30)])
            ->when($usuario->esTrabajador(), fn ($q) => $q->whereHas('lote.trabajadores', fn ($t) => $t->where('id_usuario', $usuario->id)))
            ->orderBy('fecha_cosecha_estimada')
            ->limit(5)
            ->get();

        // ── Notificaciones no leídas ──────────────────────────────────────
        $notificaciones = Notificacion::where('id_usuario', $usuario->id)
            ->where('leida', false)
            ->orderByDesc('creado_en')
            ->limit(5)
            ->get();

        if ($usuario->esTrabajador()) {
            $lotesAsignados = $usuario->lotes()->with('cultivoActivo.variedad')->get();
            return view('dashboard_worker', compact(
                'estadisticas',
                'actividadesProximas',
                'actividadesHoy',
                'cosechasProximas',
                'notificaciones',
                'lotesAsignados',
            ));
        }

        return view('dashboard', compact(
            'estadisticas',
            'actividadesProximas',
            'cosechasProximas',
            'notificaciones',
        ));
    }

    /**
     * Devuelve los datos del dashboard en JSON para sincronización en tiempo real y offline.
     */
    public function data(Request $request)
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        $actividadesQuery = Actividad::query();
        if ($usuario->esTrabajador()) {
            $actividadesQuery->where('asignado_a', $usuario->id);
            $totalLotes = $usuario->lotes()->count();
            $cultivosActivos = Cultivo::whereNotNull('activo_en_lote')
                ->whereHas('lote.trabajadores', fn($q) => $q->where('id_usuario', $usuario->id))
                ->count();
        } else {
            $totalLotes = Lote::where('activo', true)->count();
            $cultivosActivos = Cultivo::whereNotNull('activo_en_lote')->count();
        }

        $estadisticas = [
            'total_lotes'              => $totalLotes,
            'cultivos_activos'         => $cultivosActivos,
            'actividades_pendientes'   => (clone $actividadesQuery)->where('estado', 'pendiente')->count(),
            'actividades_completadas'  => (clone $actividadesQuery)->where('estado', 'completada')->count(),
        ];

        if ($usuario->isAdmin()) {
            $estadisticas['total_usuarios']     = Usuario::where('activo', true)->count();
            $estadisticas['total_trabajadores'] = Usuario::where('id_rol', 2)->where('activo', true)->count();
        }

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
            ->limit(10)
            ->get()
            ->map(fn ($a) => [
                'id'               => $a->id,
                'nombre'           => $a->tipoActividad?->nombre ?? 'Actividad',
                'lote'             => $a->cultivo?->lote?->identificador ?? $a->cultivo?->lote?->nombre ?? '—',
                'lote_id'          => $a->cultivo?->lote?->id,
                'fecha_programada' => $a->fecha_programada?->format('d M'),
                'es_hoy'           => $a->fecha_programada?->isToday() ?? false,
                'es_vencida'       => $a->fecha_programada && $a->fecha_programada->isPast(),
                'prioridad'        => $a->prioridad ?? 'media',
                'url'              => route('actividades.edit', $a),
            ]);

        $actividadesHoy = Actividad::with([
                'cultivo.lote',
                'tipoActividad',
            ])
            ->where('estado', 'pendiente')
            ->whereNotNull('fecha_programada')
            ->whereDate('fecha_programada', today())
            ->when($usuario->esTrabajador(), fn ($q) => $q->where('asignado_a', $usuario->id))
            ->orderBy('fecha_programada')
            ->get()
            ->map(fn ($a) => [
                'id'               => $a->id,
                'nombre'           => $a->tipoActividad?->nombre ?? 'Actividad',
                'lote'             => $a->cultivo?->lote?->nombre ?? '—',
                'lote_id'          => $a->cultivo?->lote?->id,
                'codigo_cultivo'   => $a->cultivo?->codigo ?? '—',
                'fecha_programada' => $a->fecha_programada?->format('d/m/Y'),
            ]);

        $cosechasProximas = Cultivo::with(['lote', 'variedad.tipoCultivo'])
            ->whereNotNull('activo_en_lote')
            ->where('estado', 'sembrado')
            ->whereBetween('fecha_cosecha_estimada', [now()->startOfDay(), now()->addDays(30)])
            ->when($usuario->esTrabajador(), fn ($q) => $q->whereHas('lote.trabajadores', fn ($t) => $t->where('id_usuario', $usuario->id)))
            ->orderBy('fecha_cosecha_estimada')
            ->limit(5)
            ->get()
            ->map(fn ($c) => [
                'id'            => $c->id,
                'codigo'        => $c->codigo,
                'variedad'      => $c->variedad?->nombre ?? '—',
                'lote'          => $c->lote?->nombre ?? '—',
                'lote_id'       => $c->lote?->id,
                'fecha_cosecha' => $c->fecha_cosecha_estimada?->format('d/m/Y'),
                'estado'        => $c->estado,
                'url'           => route('cultivo.show', $c),
            ]);

        $lotesAsignados = $usuario->esTrabajador()
            ? $usuario->lotes()->with('cultivoActivo.variedad')->get()->map(fn ($l) => [
                'id'            => $l->id,
                'identificador' => $l->identificador,
                'nombre'        => $l->nombre,
                'estado'        => $l->estado,
                'area_ha'       => (float)$l->area_ha,
                'cultivo'       => $l->cultivoActivo ? [
                    'id'            => $l->cultivoActivo->id,
                    'codigo'        => $l->cultivoActivo->codigo,
                    'variedad'      => $l->cultivoActivo->variedad?->nombre ?? 'N/A',
                    'fecha_siembra' => $l->cultivoActivo->fecha_siembra?->format('d/m/Y'),
                ] : null,
            ])
            : [];

        $notificaciones = Notificacion::where('id_usuario', $usuario->id)
            ->where('leida', false)
            ->orderByDesc('creado_en')
            ->limit(5)
            ->get()
            ->map(fn ($n) => [
                'id'      => $n->id,
                'titulo'  => $n->titulo,
                'mensaje' => $n->mensaje,
                'tipo'    => $n->tipo,
                'tiempo'  => $n->creado_en?->diffForHumans() ?? 'Reciente',
                'url'     => $n->url ?? route('notificaciones.index'),
            ]);

        // HU DP-19 Criterio 6: Resolución de conflictos de datos más recientes
        // Si el cliente envía registros pendientes con timestamp local, el sistema compara y actualiza si es más reciente
        $conflictosResueltos = 0;
        if ($request->has('cambios_locales') && is_array($request->cambios_locales)) {
            foreach ($request->cambios_locales as $cambio) {
                if (isset($cambio['modelo'], $cambio['id'], $cambio['updated_at'])) {
                    if ($cambio['modelo'] === 'actividad') {
                        $act = Actividad::find($cambio['id']);
                        if ($act && isset($cambio['estado'])) {
                            $localTime = \Carbon\Carbon::parse($cambio['updated_at']);
                            if (!$act->updated_at || $localTime->greaterThan($act->updated_at)) {
                                $obs = $cambio['observaciones'] ?? $act->observaciones;
                                if ($act->estado === 'cancelada' && $cambio['estado'] === 'completada') {
                                    $obs = trim(($obs ?? '') . ' [Conflicto resuelto: labor completada offline tras cancelación administrativa]');
                                }
                                $act->update([
                                    'estado'        => $cambio['estado'],
                                    'observaciones' => $obs,
                                ]);
                                $conflictosResueltos++;
                            }
                        }
                    }
                }
            }
        }

        // HU DP-19 Criterio 8: Sincronización selectiva de fotografías según tipo de red
        // Si la conexión es móvil o limitada, se restringen/posponen las descargas pesadas de fotografías
        $tipoRed = $request->header('X-Connection-Type', $request->query('tipo_red', 'wifi'));
        $fotosIncluidas = ($tipoRed === 'wifi');

        return response()->json([
            'estadisticas'        => $estadisticas,
            'actividadesProximas' => $actividadesProximas,
            'actividadesHoy'      => $actividadesHoy,
            'cosechasProximas'    => $cosechasProximas,
            'lotesAsignados'      => $lotesAsignados,
            'notificaciones'      => $notificaciones,
            'conflictos_resueltos'=> $conflictosResueltos,
            'fotos_sincronizadas' => $fotosIncluidas,
            'tipo_red'            => $tipoRed,
            'timestamp'           => now()->format('H:i:s'),
        ]);
    }
}

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

        // ── KPIs generales ────────────────────────────────────────────────
        $actividadesQuery = Actividad::query();
        
        if ($usuario->esTrabajador()) {
            $actividadesQuery->where('asignado_a', $usuario->id);
        }

        $estadisticas = [
            'total_lotes'              => Lote::where('activo', true)->count(),
            'cultivos_activos'         => Cultivo::whereNotNull('activo_en_lote')->count(),
            'actividades_pendientes'   => (clone $actividadesQuery)->where('estado', 'pendiente')->count(),
            'actividades_completadas'  => (clone $actividadesQuery)->where('estado', 'completada')->count(),
        ];

        // ── Solo admin ve stats de usuarios ──────────────────────────────
        if ($usuario->isAdmin()) {
            $estadisticas['total_usuarios']    = Usuario::where('activo', true)->count();
            $estadisticas['total_trabajadores'] = Usuario::where('id_rol', 2)->where('activo', true)->count();
        }

        // ── Actividades próximas (7 días) ─────────────────────────────────
        $actividadesProximas = Actividad::with([
                'cultivo.lote',
                'tipoActividad',
                'asignadoA',
            ])
            ->where('estado', 'pendiente')
            ->whereNotNull('fecha_programada')
            ->whereBetween('fecha_programada', [now(), now()->addDays(7)])
            ->when($usuario->esTrabajador(), fn ($q) => $q->where('asignado_a', $usuario->id))
            ->orderBy('fecha_programada')
            ->limit(5)
            ->get();

        // ── Cultivos con cosecha próxima (30 días) ────────────────────────
        $cosechasProximas = Cultivo::with(['lote', 'variedad.tipoCultivo'])
            ->whereNotNull('activo_en_lote')
            ->where('estado', 'sembrado')
            ->whereBetween('fecha_cosecha_estimada', [now(), now()->addDays(30)])
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
            return view('dashboard_worker', compact(
                'estadisticas',
                'actividadesProximas',
                'cosechasProximas',
                'notificaciones',
            ));
        }

        return view('dashboard', compact(
            'estadisticas',
            'actividadesProximas',
            'cosechasProximas',
            'notificaciones',
        ));
    }
}

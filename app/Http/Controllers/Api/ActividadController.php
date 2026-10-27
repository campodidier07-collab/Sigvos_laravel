<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActividadResource;
use App\Models\Actividad;
use App\Models\Cultivo;
use App\Models\Notificacion;
use App\Models\TipoActividad;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ActividadController extends Controller
{
    /**
     * Listar actividades con filtros de estado, búsqueda y paginación.
     */
    public function index(Request $request): JsonResponse
    {
        $usuario = $request->user();

        $query = Actividad::with([
            'cultivo.lote',
            'cultivo.variedad',
            'tipoActividad',
            'asignadoA',
            'ejecutadoPor',
            'creadoPor',
        ]);

        // Si es trabajador, solo ve las actividades asignadas a él
        if ($usuario->esTrabajador()) {
            $query->where('asignado_a', $usuario->id);
        }

        // Filtro por estado
        if ($request->filled('estado') && $request->estado !== 'todos') {
            $query->where('estado', $request->estado);
        }

        // Filtro por cultivo
        if ($request->filled('id_cultivo')) {
            $query->where('id_cultivo', $request->id_cultivo);
        }

        // Búsqueda por descripción o código de cultivo
        if ($request->filled('buscar')) {
            $busqueda = $request->buscar;
            $query->where(function ($q) use ($busqueda) {
                $q->where('descripcion', 'like', "%{$busqueda}%")
                  ->orWhereHas('cultivo', fn ($c) => $c->where('codigo', 'like', "%{$busqueda}%"));
            });
        }

        // Conteos rápidos para tabs
        $statsQuery = clone $query;
        $conteos = [
            'total'       => (clone $query)->count(),
            'pendientes'  => (clone $statsQuery)->where('estado', 'pendiente')->count(),
            'completadas' => (clone $statsQuery)->where('estado', 'completada')->count(),
            'canceladas'  => (clone $statsQuery)->where('estado', 'cancelada')->count(),
        ];

        $perPage = (int) $request->input('per_page', 15);
        $actividades = $query->orderBy('fecha_programada')->paginate($perPage);

        return response()->json([
            'conteos' => $conteos,
            'data'    => ActividadResource::collection($actividades)->response()->getData(true),
        ]);
    }

    /**
     * Programar una nueva actividad agrícola.
     */
    public function store(Request $request): JsonResponse
    {
        $usuario = $request->user();

        $validated = $request->validate([
            'id_cultivo'        => ['required', 'exists:cultivos,id'],
            'id_tipo_actividad' => ['required', 'exists:tipos_actividad,id'],
            'asignado_a'        => ['nullable', 'exists:usuarios,id'],
            'fecha_programada'  => ['required', 'date'],
            'descripcion'       => ['required', 'string', 'max:500'],
            'observaciones'     => ['nullable', 'string', 'max:1000'],
            'fotografia'        => ['nullable', 'image', 'max:10240'],
        ]);

        // Si es trabajador, se asigna a sí mismo por defecto
        if ($usuario->esTrabajador()) {
            $validated['asignado_a'] = $usuario->id;
        } elseif (empty($validated['asignado_a'])) {
            $validated['asignado_a'] = $usuario->id;
        }

        $validated['creado_por'] = $usuario->id;
        $validated['estado']     = 'pendiente';

        if ($request->hasFile('fotografia')) {
            $validated['fotografia'] = $request->file('fotografia')->store('actividades_evidencia', 'public');
        }

        $actividad = Actividad::create($validated);
        $actividad->load(['cultivo.lote', 'tipoActividad', 'asignadoA', 'creadoPor']);

        // Notificar si aplica
        if ($usuario->esTrabajador()) {
            // Notificar administradores
            $admins = Usuario::where('id_rol', 1)->where('activo', true)->get();
            foreach ($admins as $adm) {
                Notificacion::create([
                    'id_usuario'   => $adm->id,
                    'id_actividad' => $actividad->id,
                    'tipo'         => 'actividad',
                    'titulo'       => 'Nueva actividad registrada',
                    'mensaje'      => "El trabajador {$usuario->nombre} programó una nueva labor: {$actividad->descripcion}",
                ]);
            }
        } elseif ($actividad->asignado_a !== $usuario->id) {
            // Notificar al trabajador asignado
            Notificacion::create([
                'id_usuario'   => $actividad->asignado_a,
                'id_actividad' => $actividad->id,
                'tipo'         => 'actividad',
                'titulo'       => 'Nueva actividad asignada',
                'mensaje'      => "Se te ha programado una nueva labor: {$actividad->descripcion}",
            ]);
        }

        return response()->json([
            'message' => 'Actividad programada correctamente.',
            'data'    => new ActividadResource($actividad),
        ], 201);
    }

    /**
     * Ver detalle de una actividad.
     */
    public function show(Request $request, Actividad $actividade): JsonResponse
    {
        $actividad = $actividade;
        $usuario = $request->user();

        if ($usuario->esTrabajador() && $actividad->asignado_a !== $usuario->id) {
            return response()->json(['message' => 'No tienes permisos para ver esta actividad.'], 403);
        }

        $actividad->load([
            'cultivo.lote',
            'cultivo.variedad',
            'tipoActividad',
            'asignadoA',
            'ejecutadoPor',
            'creadoPor',
        ]);

        return response()->json([
            'data' => new ActividadResource($actividad),
        ]);
    }

    /**
     * Actualizar datos o estado de la actividad.
     */
    public function update(Request $request, Actividad $actividade): JsonResponse
    {
        $actividad = $actividade;
        $usuario = $request->user();

        if ($usuario->esTrabajador() && $actividad->asignado_a !== $usuario->id) {
            return response()->json(['message' => 'No puedes modificar esta actividad.'], 403);
        }

        $validated = $request->validate([
            'estado'            => ['required', 'string', Rule::in(['pendiente', 'completada', 'cancelada'])],
            'descripcion'       => ['nullable', 'string', 'max:500'],
            'observaciones'     => ['nullable', 'string', 'max:1000'],
            'fecha_ejecucion'   => ['nullable', 'date'],
            'fotografia'        => ['nullable', 'image', 'max:10240'],
        ]);

        if ($usuario->isAdmin()) {
            $adminValidations = $request->validate([
                'id_tipo_actividad' => ['nullable', 'exists:tipos_actividad,id'],
                'asignado_a'        => ['nullable', 'exists:usuarios,id'],
                'fecha_programada'  => ['nullable', 'date'],
            ]);
            $validated = array_merge($validated, $adminValidations);
        }

        if ($validated['estado'] === 'completada') {
            $validated['ejecutado_por'] = $usuario->id;
            $validated['fecha_ejecucion'] = $validated['fecha_ejecucion'] ?? now()->format('Y-m-d');
        } elseif (in_array($validated['estado'], ['pendiente', 'cancelada'])) {
            $validated['ejecutado_por'] = null;
            $validated['fecha_ejecucion'] = null;
        }

        if ($request->hasFile('fotografia')) {
            if ($actividad->fotografia && Storage::disk('public')->exists($actividad->fotografia)) {
                Storage::disk('public')->delete($actividad->fotografia);
            }
            $validated['fotografia'] = $request->file('fotografia')->store('actividades_evidencia', 'public');
        }

        $actividad->update($validated);
        $actividad->load(['cultivo.lote', 'tipoActividad', 'asignadoA', 'ejecutadoPor']);

        return response()->json([
            'message' => 'Actividad actualizada exitosamente.',
            'data'    => new ActividadResource($actividad),
        ]);
    }

    /**
     * Endpoint express para marcar una actividad como completada.
     */
    public function marcarCompletada(Request $request, Actividad $actividad): JsonResponse
    {
        $usuario = $request->user();

        if ($usuario->esTrabajador() && $actividad->asignado_a !== $usuario->id) {
            return response()->json(['message' => 'No tienes asignada esta actividad.'], 403);
        }

        $actividad->update([
            'estado'          => 'completada',
            'ejecutado_por'   => $usuario->id,
            'fecha_ejecucion' => now()->format('Y-m-d'),
            'observaciones'   => $request->input('observaciones', $actividad->observaciones),
        ]);

        $actividad->load(['cultivo.lote', 'tipoActividad', 'asignadoA']);

        return response()->json([
            'message' => 'Actividad marcada como completada.',
            'data'    => new ActividadResource($actividad),
        ]);
    }

    /**
     * Eliminar actividad (solo admin).
     */
    public function destroy(Request $request, Actividad $actividade): JsonResponse
    {
        $actividad = $actividade;
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'No tienes permisos para eliminar actividades.'], 403);
        }

        $actividad->delete();

        return response()->json([
            'message' => 'Actividad eliminada correctamente.',
        ]);
    }

    /**
     * Obtener eventos unificados para el calendario (actividades y cosechas estimadas).
     */
    public function calendarioEventos(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $start = $request->query('start', now()->startOfMonth()->format('Y-m-d'));
        $end = $request->query('end', now()->endOfMonth()->addMonths(1)->format('Y-m-d'));

        $eventos = [];

        // 1. Actividades
        $actividadesQuery = Actividad::with(['tipoActividad', 'cultivo.lote'])
            ->whereNotNull('fecha_programada')
            ->whereBetween('fecha_programada', [$start, $end]);

        if ($usuario->esTrabajador()) {
            $actividadesQuery->where('asignado_a', $usuario->id);
        }

        foreach ($actividadesQuery->get() as $act) {
            $color = match ($act->estado) {
                'completada' => '#10b981',
                'cancelada'  => '#ef4444',
                default      => '#3b82f6',
            };

            $eventos[] = [
                'id'       => 'act_' . $act->id,
                'tipo'     => 'actividad',
                'titulo'   => ($act->tipoActividad?->nombre ?? 'Actividad') . ' (' . ($act->cultivo?->codigo ?? '') . ')',
                'fecha'    => $act->fecha_programada?->format('Y-m-d'),
                'estado'   => $act->estado,
                'color'    => $color,
                'cultivo'  => $act->cultivo?->codigo,
                'lote'     => $act->cultivo?->lote?->nombre,
            ];
        }

        // 2. Cosechas estimadas
        $cultivos = Cultivo::with(['lote', 'variedad'])
            ->whereNotNull('activo_en_lote')
            ->whereNotNull('fecha_cosecha_estimada')
            ->whereBetween('fecha_cosecha_estimada', [$start, $end])
            ->get();

        foreach ($cultivos as $c) {
            $eventos[] = [
                'id'       => 'cos_' . $c->id,
                'tipo'     => 'cosecha',
                'titulo'   => '🌾 Cosecha: ' . ($c->variedad?->nombre ?? $c->codigo),
                'fecha'    => $c->fecha_cosecha_estimada?->format('Y-m-d'),
                'estado'   => $c->estado,
                'color'    => '#f59e0b',
                'cultivo'  => $c->codigo,
                'lote'     => $c->lote?->nombre,
            ];
        }

        return response()->json([
            'data' => $eventos,
        ]);
    }
}
